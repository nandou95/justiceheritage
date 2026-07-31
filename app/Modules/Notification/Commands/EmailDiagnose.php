<?php



/**

 * Quick env/config dump for email (no full password).

 * Run: php spark email:diagnose

 */



namespace Modules\Notification\Commands;



use CodeIgniter\CLI\BaseCommand;

use CodeIgniter\CLI\CLI;

use Config\Email as EmailConfig;

use Modules\Notification\Services\NotificationMailer;



class EmailDiagnose extends BaseCommand

{

    protected $group       = 'Email';

    protected $name        = 'email:diagnose';

    protected $description = 'Diagnose SMTP configuration and connectivity.';

    protected $usage       = 'email:diagnose';



    public function run(array $params)

    {

        $config = config(EmailConfig::class);

        $mailer = new NotificationMailer();



        CLI::write('=== Email configuration (.env → Config\\Email) ===', 'yellow');

        CLI::write('protocol   : ' . $config->protocol);

        CLI::write('SMTPHost   : ' . ($config->SMTPHost ?: '(empty)'));

        CLI::write('SMTPPort   : ' . $config->SMTPPort);

        CLI::write('SMTPCrypto : ' . ($config->SMTPCrypto ?: '(none)'));

        CLI::write('SMTPUser   : ' . ($config->SMTPUser ?: '(empty)'));

        CLI::write('SMTPPass   : ' . $this->mask($config->SMTPPass));

        CLI::write('fromEmail  : ' . ($config->fromEmail ?: '(empty)'));

        CLI::write('fromName   : ' . ($config->fromName ?: '(empty)'));

        CLI::write('configured : ' . ($mailer->isConfigured() ? 'yes' : 'no'));



        if (! $mailer->isConfigured()) {

            CLI::error('SMTP is incomplete. Set email.SMTPHost, email.SMTPUser, email.SMTPPass, email.fromEmail in .env');



            return EXIT_ERROR;

        }



        CLI::newLine();

        CLI::write('=== Connectivity ===', 'yellow');

        $this->probe($config->SMTPHost, (int) $config->SMTPPort, $config->SMTPCrypto);



        CLI::newLine();

        CLI::write('=== AUTH probe (no message body) ===', 'yellow');

        $auth = $this->authProbe($config);

        CLI::write($auth['summary'], $auth['ok'] ? 'green' : 'red');

        if (! $auth['ok']) {

            CLI::write($auth['detail']);

            if (str_contains($auth['detail'], '5.7.8') || str_contains(strtolower($auth['detail']), 'password')) {

                CLI::newLine();

                CLI::write('Gmail tip: use a Google App Password (not your normal account password).', 'yellow');

                CLI::write('Create one at: https://myaccount.google.com/apppasswords', 'yellow');

            }



            return EXIT_ERROR;

        }



        CLI::write('SMTP authentication succeeded.', 'green');



        return EXIT_SUCCESS;

    }



    private function mask(string $value): string

    {

        if ($value === '') {

            return '(empty)';

        }



        $len = strlen($value);



        return str_repeat('*', max(0, $len - 4)) . substr($value, -4) . " (len={$len})";

    }



    private function probe(string $host, int $port, string $crypto): void

    {

        $targets = [

            ['label' => "tcp://{$host}:{$port}", 'remote' => $host, 'port' => $port, 'ssl' => false],

        ];



        if ($crypto === 'ssl' || $port === 465) {

            $targets[] = ['label' => "ssl://{$host}:465", 'remote' => 'ssl://' . $host, 'port' => 465, 'ssl' => true];

        }



        foreach ($targets as $target) {

            $errno  = 0;

            $errstr = '';

            $fp     = @fsockopen($target['remote'], $target['port'], $errno, $errstr, 12);

            if ($fp) {

                CLI::write($target['label'] . ' → reachable', 'green');

                fclose($fp);

            } else {

                CLI::write($target['label'] . " → FAIL ({$errno} {$errstr})", 'red');

            }

        }

    }



    /**

     * @return array{ok:bool,summary:string,detail:string}

     */

    private function authProbe(EmailConfig $config): array

    {

        $host   = $config->SMTPHost;

        $port   = (int) $config->SMTPPort;

        $crypto = strtolower($config->SMTPCrypto);

        $user   = $config->SMTPUser;

        $pass   = $config->SMTPPass;



        $remote = ($crypto === 'ssl' || $port === 465) ? 'ssl://' . $host : $host;

        $fp     = @fsockopen($remote, $port === 465 ? 465 : $port, $errno, $errstr, 20);

        if (! $fp) {

            return ['ok' => false, 'summary' => 'Connection failed', 'detail' => "{$errno} {$errstr}"];

        }



        stream_set_timeout($fp, 20);

        $banner = trim((string) fgets($fp, 512));



        $ehlo = $this->smtpMultiline($fp, 'EHLO localhost');

        if ($crypto === 'tls' && $port !== 465) {

            fwrite($fp, "STARTTLS\r\n");

            $starttls = trim((string) fgets($fp, 512));

            if (! str_starts_with($starttls, '220')) {

                fclose($fp);



                return ['ok' => false, 'summary' => 'STARTTLS failed', 'detail' => $starttls];

            }

            $tlsOk = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

            if ($tlsOk !== true) {

                fclose($fp);



                return ['ok' => false, 'summary' => 'TLS handshake failed', 'detail' => $starttls];

            }

            $ehlo = $this->smtpMultiline($fp, 'EHLO localhost');

        }



        fwrite($fp, "AUTH LOGIN\r\n");

        $auth1 = trim((string) fgets($fp, 512));

        fwrite($fp, base64_encode($user) . "\r\n");

        $auth2 = trim((string) fgets($fp, 512));

        fwrite($fp, base64_encode($pass) . "\r\n");

        $auth3 = trim((string) fgets($fp, 512));

        fwrite($fp, "QUIT\r\n");

        fclose($fp);



        $detail = implode("\n", array_filter([$banner, trim($ehlo), $auth1, $auth2, $auth3]));

        $ok     = str_starts_with($auth3, '235');



        return [

            'ok'      => $ok,

            'summary' => $ok ? 'AUTH LOGIN accepted' : 'AUTH LOGIN rejected',

            'detail'  => $detail,

        ];

    }



    /**

     * @param resource $fp

     */

    private function smtpMultiline($fp, string $command): string

    {

        fwrite($fp, $command . "\r\n");

        $out = '';

        while ($line = fgets($fp, 512)) {

            $out .= $line;

            if (isset($line[3]) && $line[3] === ' ') {

                break;

            }

        }



        return $out;

    }

}

