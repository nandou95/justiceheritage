<?php



namespace Modules\Notification\Commands;



use CodeIgniter\CLI\BaseCommand;

use CodeIgniter\CLI\CLI;

use Config\Email as EmailConfig;

use Modules\Notification\Services\NotificationMailer;



class EmailTest extends BaseCommand

{

    protected $group       = 'Email';

    protected $name        = 'email:test';

    protected $description = 'Send a test SMTP email to verify notification delivery.';

    protected $usage       = 'email:test <recipient_email> [recipient_name]';

    protected $arguments   = [

        'recipient_email' => 'Email address that should receive the test message.',

        'recipient_name'  => 'Optional display name for the recipient.',

    ];



    public function run(array $params)

    {

        $toEmail = $params[0] ?? null;

        $toName  = $params[1] ?? 'JusticeHeritage tester';



        if (! is_string($toEmail) || $toEmail === '') {

            CLI::error('Usage: php spark email:test <recipient_email> [recipient_name]');



            return EXIT_ERROR;

        }



        if (! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {

            CLI::error('Please provide a valid recipient email.');



            return EXIT_ERROR;

        }



        $config = config(EmailConfig::class);

        $mailer = new NotificationMailer();



        CLI::write('SMTP host : ' . ($config->SMTPHost ?: '(empty)'), 'yellow');

        CLI::write('SMTP port : ' . $config->SMTPPort . ' / ' . ($config->SMTPCrypto ?: 'none'), 'yellow');

        CLI::write('SMTP user : ' . ($config->SMTPUser ?: '(empty)'), 'yellow');

        CLI::write('From      : ' . ($config->fromEmail ?: '(empty)'), 'yellow');

        CLI::write('To        : ' . $toEmail, 'yellow');



        if (! $mailer->isConfigured()) {

            CLI::error('SMTP is not configured. Set email.* values in your .env file.');



            return EXIT_ERROR;

        }



        $ok = $mailer->send(

            type: 'smtp_test',

            toEmail: $toEmail,

            toName: $toName,

            subject: lang('Mail.test_subject'),

            view: 'Modules\Notification\Views\emails\test',

            data: ['name' => $toName]

        );



        if (! $ok) {

            CLI::error('Test email failed: ' . $mailer->getLastError());

            if ($mailer->lastDebugger() !== '') {

                CLI::write($mailer->lastDebugger());

            }



            return EXIT_ERROR;

        }



        CLI::write('Test email sent successfully.', 'green');



        return EXIT_SUCCESS;

    }

}

