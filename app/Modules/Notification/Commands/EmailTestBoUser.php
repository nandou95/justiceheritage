<?php

namespace Modules\Notification\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Notification\Services\NotificationMailer;

/**
 * Sends only the back-office user welcome email template.
 */
class EmailTestBoUser extends BaseCommand
{
    protected $group       = 'Email';
    protected $name        = 'email:test-bo-user';
    protected $description = 'Send the back-office user welcome email template.';
    protected $usage       = 'email:test-bo-user <recipient_email> [recipient_name]';
    protected $arguments   = [
        'recipient_email' => 'Recipient email address',
        'recipient_name'  => 'Optional recipient name',
    ];

    public function run(array $params)
    {
        $toEmail = $params[0] ?? null;
        $toName  = $params[1] ?? 'Utilisateur Back Office';

        if (! is_string($toEmail) || ! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Usage: php spark email:test-bo-user <recipient_email> [recipient_name]');

            return EXIT_ERROR;
        }

        $mailer = service('notifications');
        if (! $mailer instanceof NotificationMailer || ! $mailer->isConfigured()) {
            CLI::error('SMTP is not configured.');

            return EXIT_ERROR;
        }

        CLI::write('Sending backoffice_user_registration…');
        $ok = $mailer->sendBackofficeUserRegistration(
            $toEmail,
            $toName,
            'DemoPass123!',
            [
                'cni'       => 'CNI-DEMO-001',
                'matricule' => 'MAT-DEMO-001',
                'email'     => $toEmail,
            ],
            site_url('backoffice')
        );

        if (! $ok) {
            CLI::error('FAIL: ' . $mailer->getLastError());

            return EXIT_ERROR;
        }

        CLI::write('OK', 'green');

        return EXIT_SUCCESS;
    }
}
