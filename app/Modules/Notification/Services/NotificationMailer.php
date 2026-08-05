<?php

namespace Modules\Notification\Services;

use CodeIgniter\Email\Email;
use Config\Email as EmailConfig;
use Config\Services;
use Throwable;

/**
 * Central SMTP notification sender for JusticeHeritage.
 */
class NotificationMailer
{
    private EmailConfig $config;
    private string $lastError = '';
    private string $lastDebugger = '';

    public function __construct(?EmailConfig $config = null)
    {
        $this->config = $config ?? config(EmailConfig::class);
    }

    public function sendAccountRegistration(
        string $toEmail,
        string $toName,
        string $username = '',
        string $password = '',
        ?string $loginUrl = null
    ): bool {
        return $this->send(
            type: 'account_registration',
            toEmail: $toEmail,
            toName: $toName,
            subject: lang('Mail.subject_registration'),
            view: 'Modules\Notification\Views\emails\account_registration',
            data: [
                'name'     => $toName,
                'username' => $username,
                'password' => $password,
                'loginUrl' => $loginUrl ?: site_url('login'),
            ]
        );
    }

    public function sendEmailVerification(string $toEmail, string $toName, string $verifyUrl): bool
    {
        if (! $this->config->emailVerificationEnabled) {
            log_message('info', 'Email verification skipped (disabled in config) for {email}', ['email' => $toEmail]);

            return true;
        }

        return $this->send(
            type: 'email_verification',
            toEmail: $toEmail,
            toName: $toName,
            subject: lang('Mail.subject_verification'),
            view: 'Modules\Notification\Views\emails\email_verification',
            data: [
                'name'      => $toName,
                'verifyUrl' => $verifyUrl,
            ]
        );
    }

    /**
     * Send a 2FA verification code.
     *
     * Accepts the code inside $payload so transient SMTP exceptions do not
     * print the raw code in stack-trace argument lists.
     *
     * @param array{code:string,ttl?:int} $payload
     */
    public function sendTwoFactorCode(string $toEmail, string $toName, array $payload): bool
    {
        $code = preg_replace('/\D+/', '', (string) ($payload['code'] ?? '')) ?? '';
        $ttl  = max(1, (int) ($payload['ttl'] ?? 60));

        if (strlen($code) !== 6) {
            $this->lastError = 'Invalid verification code payload.';
            log_message('error', 'Email notification "two_factor_code" aborted: invalid code payload for {email}.', [
                'email' => $toEmail,
            ]);

            return false;
        }

        return $this->send(
            type: 'two_factor_code',
            toEmail: $toEmail,
            toName: $toName,
            subject: lang('Mail.subject_2fa'),
            view: 'Modules\Notification\Views\emails\two_factor_code',
            data: [
                'name'       => $toName,
                'code'       => $code,
                'ttlSeconds' => $ttl,
                'ttlMinutes' => max(1, (int) ceil($ttl / 60)),
            ]
        );
    }

    public function sendComplaintSubmitted(string $toEmail, string $toName, string $complaintNumber, string $complaintTitle): bool
    {
        return $this->send(
            type: 'complaint_submitted',
            toEmail: $toEmail,
            toName: $toName,
            subject: lang('Mail.subject_complaint_submitted', [$complaintNumber]),
            view: 'Modules\Notification\Views\emails\complaint_submitted',
            data: [
                'name'            => $toName,
                'complaintNumber' => $complaintNumber,
                'complaintTitle'  => $complaintTitle,
                'portalUrl'       => site_url('portal/complaints'),
            ]
        );
    }

    public function sendComplaintStatusUpdate(
        string $toEmail,
        string $toName,
        string $complaintNumber,
        string $complaintTitle,
        string $statusLabel
    ): bool {
        return $this->send(
            type: 'complaint_status_update',
            toEmail: $toEmail,
            toName: $toName,
            subject: lang('Mail.subject_complaint_status', [$complaintNumber]),
            view: 'Modules\Notification\Views\emails\complaint_status_update',
            data: [
                'name'            => $toName,
                'complaintNumber' => $complaintNumber,
                'complaintTitle'  => $complaintTitle,
                'statusLabel'     => $statusLabel,
                'portalUrl'       => site_url('portal/complaints/' . rawurlencode($complaintNumber)),
            ]
        );
    }

    public function sendAppealSubmitted(
        string $toEmail,
        string $toName,
        string $appealLevel,
        string $caseNumber
    ): bool {
        return $this->send(
            type: 'appeal_submitted',
            toEmail: $toEmail,
            toName: $toName,
            subject: lang('Mail.subject_appeal_submitted', [$appealLevel]),
            view: 'Modules\Notification\Views\emails\appeal_submitted',
            data: [
                'name'        => $toName,
                'appealLevel' => $appealLevel,
                'caseNumber'  => $caseNumber,
                'portalUrl'   => site_url('portal'),
            ]
        );
    }

    public function sendSummonsIssued(
        string $toEmail,
        string $toName,
        string $caseNumber,
        string $complaintTitle,
        string $hearingDate,
        string $hearingTime,
        string $venue
    ): bool {
        return $this->send(
            type: 'summons_issued',
            toEmail: $toEmail,
            toName: $toName,
            subject: lang('Mail.subject_summons_issued', [$caseNumber]),
            view: 'Modules\Notification\Views\emails\summons_issued',
            data: [
                'name'            => $toName,
                'complaintNumber' => $caseNumber,
                'complaintTitle'  => $complaintTitle,
                'hearingDate'     => $hearingDate,
                'hearingTime'     => $hearingTime,
                'venue'           => $venue,
                'portalUrl'       => site_url('portal/complaints'),
            ]
        );
    }

    public function sendPasswordReset(string $toEmail, string $toName, string $resetUrl): bool
    {
        return $this->send(
            type: 'password_reset',
            toEmail: $toEmail,
            toName: $toName,
            subject: lang('Mail.subject_password_reset'),
            view: 'Modules\Notification\Views\emails\password_reset',
            data: [
                'name'     => $toName,
                'resetUrl' => $resetUrl,
            ]
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function send(string $type, string $toEmail, string $toName, string $subject, string $view, array $data = []): bool
    {
        $this->lastError    = '';
        $this->lastDebugger = '';

        if (! $this->isConfigured()) {
            $this->lastError = 'SMTP is not configured. Check email.* settings in the .env file.';
            log_message('error', 'Email notification "{type}" aborted: {error}', [
                'type'  => $type,
                'error' => $this->lastError,
            ]);

            return false;
        }

        if ($toEmail === '' || ! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $this->lastError = 'Invalid recipient email address.';
            log_message('error', 'Email notification "{type}" aborted: invalid recipient "{email}".', [
                'type'  => $type,
                'email' => $toEmail,
            ]);

            return false;
        }

        $html = view($view, array_merge($data, [
            'appName'   => 'JusticeHeritage',
            'recipient' => $toName !== '' ? $toName : $toEmail,
        ]));

        $attempts = $this->transportAttempts();
        $errors   = [];

        foreach ($attempts as $index => $transport) {
            try {
                $email = $this->buildEmail($transport);
                $email->setTo($toEmail);
                $email->setSubject($subject);
                $email->setMessage($html);

                if ($email->send(false)) {
                    if ($index > 0) {
                        log_message('info', 'Email notification "{type}" sent to {email} using fallback SMTP {host}:{port}/{crypto}.', [
                            'type'   => $type,
                            'email'  => $toEmail,
                            'host'   => $transport['host'],
                            'port'   => (string) $transport['port'],
                            'crypto' => $transport['crypto'],
                        ]);
                    } else {
                        log_message('info', 'Email notification "{type}" sent to {email}.', [
                            'type'  => $type,
                            'email' => $toEmail,
                        ]);
                    }

                    return true;
                }

                $debugger = $email->printDebugger(['headers', 'subject']);
                $error    = $this->extractSmtpError($debugger);
                $errors[] = sprintf(
                    '%s:%d/%s → %s',
                    $transport['host'],
                    $transport['port'],
                    $transport['crypto'] ?: 'none',
                    $error
                );
                $this->lastDebugger = $debugger;
            } catch (Throwable $e) {
                $errors[] = sprintf(
                    '%s:%d/%s → exception: %s',
                    $transport['host'],
                    $transport['port'],
                    $transport['crypto'] ?: 'none',
                    $e->getMessage()
                );
            }
        }

        $this->lastError = implode(' | ', $errors) ?: 'Unable to send email using SMTP.';
        log_message('error', 'Email notification "{type}" failed for {email}. Complete error: {error}', [
            'type'  => $type,
            'email' => $toEmail,
            'error' => $this->lastError,
        ]);

        if ($this->lastDebugger !== '') {
            log_message('debug', 'Email debugger for "{type}" (headers/subject only): {debug}', [
                'type'  => $type,
                'debug' => $this->truncate($this->lastDebugger),
            ]);
        }

        return false;
    }

    public function isConfigured(): bool
    {
        return strtolower($this->config->protocol) === 'smtp'
            && trim($this->config->SMTPHost) !== ''
            && trim($this->config->SMTPUser) !== ''
            && trim($this->config->SMTPPass) !== ''
            && trim($this->config->fromEmail) !== '';
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    public function lastDebugger(): string
    {
        return $this->lastDebugger;
    }

    /**
     * Preferred transport first, then Gmail-friendly fallbacks.
     *
     * @return list<array{host:string,port:int,crypto:string,timeout:int}>
     */
    private function transportAttempts(): array
    {
        $host    = trim($this->config->SMTPHost);
        $primary = [
            'host'    => $host,
            'port'    => (int) $this->config->SMTPPort,
            'crypto'  => strtolower((string) $this->config->SMTPCrypto),
            'timeout' => max(15, (int) $this->config->SMTPTimeout),
        ];

        $attempts = [$primary];

        if (! ($primary['port'] === 465 && $primary['crypto'] === 'ssl')) {
            $attempts[] = [
                'host'    => $host,
                'port'    => 465,
                'crypto'  => 'ssl',
                'timeout' => max(30, (int) $this->config->SMTPTimeout),
            ];
        }

        if (! ($primary['port'] === 587 && $primary['crypto'] === 'tls')) {
            $attempts[] = [
                'host'    => $host,
                'port'    => 587,
                'crypto'  => 'tls',
                'timeout' => max(30, (int) $this->config->SMTPTimeout),
            ];
        }

        $unique = [];
        $seen   = [];
        foreach ($attempts as $attempt) {
            $key = $attempt['host'] . '|' . $attempt['port'] . '|' . $attempt['crypto'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[]   = $attempt;
        }

        return $unique;
    }

    /**
     * @param array{host:string,port:int,crypto:string,timeout:int} $transport
     */
    private function buildEmail(array $transport): Email
    {
        $config              = clone $this->config;
        $config->protocol    = 'smtp';
        $config->SMTPHost    = $transport['host'];
        $config->SMTPPort    = $transport['port'];
        $config->SMTPCrypto  = $transport['crypto'];
        $config->SMTPTimeout = $transport['timeout'];
        $config->mailType    = 'html';
        $config->charset     = $config->charset ?: 'UTF-8';
        $config->newline     = "\r\n";
        $config->CRLF        = "\r\n";

        $email = Services::email($config, false);
        $email->clear(true);
        $email->setFrom($config->fromEmail, $config->fromName ?: 'JusticeHeritage');
        $email->setReplyTo($config->fromEmail, $config->fromName ?: 'JusticeHeritage');
        $email->setMailType('html');

        return $email;
    }

    private function extractSmtpError(string $debugger): string
    {
        $plain = trim(html_entity_decode(strip_tags($debugger)));

        if (preg_match('/Handshake timed out/i', $plain)) {
            return 'TLS/SSL handshake timed out while connecting to SMTP.';
        }

        if (preg_match('/535[^\r\n]*/', $plain, $m)) {
            $msg = trim($m[0]);
            if (str_contains($msg, '5.7.8') || str_contains(strtolower($msg), 'password')) {
                return 'SMTP authentication failed (535). For Gmail, use a Google App Password instead of the normal account password. Details: ' . $msg;
            }

            return 'SMTP authentication failed: ' . $msg;
        }

        if (preg_match('/Unable to send email using SMTP[^\r\n]*/i', $plain, $m)) {
            return trim($m[0]) . ' Check host/port/encryption and that the mailbox allows SMTP access.';
        }

        if ($plain !== '') {
            return $this->truncate($plain, 400);
        }

        return 'Unable to send email using SMTP. Check logs for details.';
    }

    private function truncate(string $text, int $max = 2000): string
    {
        $text = trim(strip_tags($text));

        if (strlen($text) <= $max) {
            return $text;
        }

        return substr($text, 0, $max) . '…';
    }
}
