<?php



namespace Modules\Notification\Commands;



use CodeIgniter\CLI\BaseCommand;

use CodeIgniter\CLI\CLI;

use Modules\Notification\Services\NotificationMailer;



/**

 * Sends one message for each notification template.

 */

class EmailTestSuite extends BaseCommand

{

    protected $group       = 'Email';

    protected $name        = 'email:test-suite';

    protected $description = 'Send one test message for each notification type.';

    protected $usage       = 'email:test-suite <recipient_email> [recipient_name]';

    protected $arguments   = [

        'recipient_email' => 'Recipient email address',

        'recipient_name'  => 'Optional recipient name',

    ];



    public function run(array $params)

    {

        $toEmail = $params[0] ?? null;

        $toName  = $params[1] ?? 'JusticeHeritage tester';



        if (! is_string($toEmail) || ! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {

            CLI::error('Usage: php spark email:test-suite <recipient_email> [recipient_name]');



            return EXIT_ERROR;

        }



        $mailer = new NotificationMailer();

        if (! $mailer->isConfigured()) {

            CLI::error('SMTP is not configured.');



            return EXIT_ERROR;

        }



        $cases = [

            'registration' => static fn () => $mailer->sendAccountRegistration($toEmail, $toName, 'demo_user', 'DemoPass123!', site_url('login')),

            'verification' => static fn () => $mailer->sendEmailVerification($toEmail, $toName, site_url('login')),

            'password_reset' => static fn () => $mailer->sendPasswordReset($toEmail, $toName, site_url('login')),

            'complaint_submitted' => static fn () => $mailer->sendComplaintSubmitted($toEmail, $toName, 'JH-TEST-0001', 'Sample inheritance land complaint'),

            'complaint_status' => static fn () => $mailer->sendComplaintStatusUpdate($toEmail, $toName, 'JH-TEST-0001', 'Sample inheritance land complaint', 'Hearing scheduled'),

            'appeal_submitted' => static fn () => $mailer->sendAppealSubmitted($toEmail, $toName, 'Provincial', 'JH-TEST-0001'),

        ];



        $failed = 0;

        foreach ($cases as $label => $callback) {

            CLI::write('Sending ' . $label . '…');

            $ok = $callback();

            if ($ok) {

                CLI::write('  OK', 'green');

            } else {

                $failed++;

                CLI::write('  FAIL: ' . $mailer->getLastError(), 'red');

            }

        }



        if ($failed > 0) {

            CLI::error("{$failed} notification type(s) failed.");



            return EXIT_ERROR;

        }



        CLI::write('All notification types sent successfully.', 'green');



        return EXIT_SUCCESS;

    }

}

