<?php

namespace App\Libraries;

use CodeIgniter\Email\Email;

/**
 * SMTP client that tolerates broken local DNS and blocked implicit-TLS (465)
 * by resolving A records (system DNS + DNS-over-HTTPS) and connecting with
 * stream_socket_client + SNI/peer_name.
 */
class ReliableSmtpEmail extends Email
{
    protected function SMTPConnect()
    {
        if ($this->isSMTPConnected()) {
            return true;
        }

        $hostname = trim($this->SMTPHost);
        $port     = (int) $this->SMTPPort;
        $crypto   = strtolower((string) $this->SMTPCrypto);
        $timeout  = max(10, (int) $this->SMTPTimeout);
        $useSsl   = ($port === 465) || ($crypto === 'ssl');

        $targets = $this->connectionTargets($hostname);
        $errors  = [];

        foreach ($targets as $target) {
            $errno  = 0;
            $errstr = '';

            if ($useSsl) {
                $context = stream_context_create([
                    'ssl' => [
                        'peer_name'        => $hostname,
                        'verify_peer'      => true,
                        'verify_peer_name' => true,
                        'crypto_method'    => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
                    ],
                ]);
                $remote = 'ssl://' . $target;
                $this->SMTPConnect = @stream_socket_client(
                    $remote . ':' . $port,
                    $errno,
                    $errstr,
                    $timeout,
                    STREAM_CLIENT_CONNECT,
                    $context
                );
            } else {
                $this->SMTPConnect = @stream_socket_client(
                    'tcp://' . $target . ':' . $port,
                    $errno,
                    $errstr,
                    $timeout,
                    STREAM_CLIENT_CONNECT
                );
            }

            if ($this->isSMTPConnected()) {
                log_message('debug', 'SMTP connected via {target}:{port} (hostname={host}, crypto={crypto})', [
                    'target' => $target,
                    'port'   => (string) $port,
                    'host'   => $hostname,
                    'crypto' => $useSsl ? 'ssl' : ($crypto ?: 'none'),
                ]);
                break;
            }

            $errors[] = "{$target}:{$port} → {$errno} {$errstr}";
            $this->SMTPConnect = null;
        }

        if (! $this->isSMTPConnected()) {
            $this->setErrorMessage(lang('Email.SMTPError', [implode(' | ', $errors) ?: 'connection failed']));

            return false;
        }

        stream_set_timeout($this->SMTPConnect, $timeout);
        $this->setErrorMessage($this->getSMTPData());

        if ($crypto === 'tls' && ! $useSsl) {
            $this->sendCommand('hello');
            $this->sendCommand('starttls');

            // Ensure certificate is validated against the configured hostname (not the IP).
            stream_context_set_option($this->SMTPConnect, 'ssl', 'peer_name', $hostname);
            stream_context_set_option($this->SMTPConnect, 'ssl', 'verify_peer', true);
            stream_context_set_option($this->SMTPConnect, 'ssl', 'verify_peer_name', true);

            $cryptoOk = @stream_socket_enable_crypto(
                $this->SMTPConnect,
                true,
                STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT
            );

            if ($cryptoOk !== true) {
                $this->setErrorMessage(lang('Email.SMTPError', ['TLS handshake failed after STARTTLS']));

                return false;
            }
        }

        return $this->sendCommand('hello');
    }

    /**
     * @return list<string> Hostnames/IPs to try (hostname first, then IPv4s)
     */
    private function connectionTargets(string $hostname): array
    {
        $targets = [$hostname];
        $ips     = [];

        $resolved = gethostbyname($hostname);
        if (is_string($resolved) && filter_var($resolved, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ips[] = $resolved;
        }

        $records = @dns_get_record($hostname, DNS_A);
        if (is_array($records)) {
            foreach ($records as $row) {
                if (! empty($row['ip']) && filter_var($row['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ips[] = $row['ip'];
                }
            }
        }

        foreach ($this->resolveViaDoh($hostname) as $ip) {
            $ips[] = $ip;
        }

        foreach (array_values(array_unique($ips)) as $ip) {
            if (! in_array($ip, $targets, true)) {
                $targets[] = $ip;
            }
        }

        return $targets;
    }

    /**
     * @return list<string>
     */
    private function resolveViaDoh(string $hostname): array
    {
        $endpoints = [
            'https://cloudflare-dns.com/dns-query?name=' . rawurlencode($hostname) . '&type=A',
            'https://dns.google/resolve?name=' . rawurlencode($hostname) . '&type=A',
        ];

        foreach ($endpoints as $url) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout'       => 6,
                    'header'        => "Accept: application/dns-json\r\n",
                    'ignore_errors' => true,
                ],
            ]);
            $raw = @file_get_contents($url, false, $ctx);
            if ($raw === false || $raw === '') {
                continue;
            }

            $data = json_decode($raw, true);
            if (! is_array($data)) {
                continue;
            }

            $ips = [];
            foreach (($data['Answer'] ?? []) as $ans) {
                $ip = $ans['data'] ?? '';
                if (is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ips[] = $ip;
                }
            }

            if ($ips !== []) {
                return array_values(array_unique($ips));
            }
        }

        return [];
    }
}
