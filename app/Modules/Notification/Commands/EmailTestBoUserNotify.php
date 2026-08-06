<?php

namespace Modules\Notification\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Notification\Models\StatutNotificationModel;
use Modules\Notification\Services\NotificationMailer;

/**
 * Full back-office welcome email probe: SMTP send + notification_utilisateur insert.
 */
class EmailTestBoUserNotify extends BaseCommand
{
    protected $group       = 'Email';
    protected $name        = 'email:test-bo-user-notify';
    protected $description = 'Send BO welcome email and persist notification_utilisateur (Sent/Failed).';
    protected $usage       = 'email:test-bo-user-notify <recipient_email> [recipient_name]';
    protected $arguments   = [
        'recipient_email' => 'Recipient email address',
        'recipient_name'  => 'Optional recipient name',
    ];

    public function run(array $params)
    {
        $toEmail = $params[0] ?? null;
        $toName  = $params[1] ?? 'Utilisateur Back Office';

        if (! is_string($toEmail) || ! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Usage: php spark email:test-bo-user-notify <recipient_email> [recipient_name]');

            return EXIT_ERROR;
        }

        /** @var NotificationMailer $mailer */
        $mailer = service('notifications');
        if (! $mailer->isConfigured()) {
            CLI::error('SMTP is not configured.');

            return EXIT_ERROR;
        }

        CLI::write('SMTP configured: yes');
        CLI::write('Sending French backoffice_user_registration to ' . $toEmail . '…');

        $plainPassword = 'TempTest' . random_int(1000, 9999);
        $loginUrl      = site_url('backoffice');
        $cni           = 'CNI-TEST-001';
        $matricule     = 'MAT-TEST-001';

        $sentOk = $mailer->sendBackofficeUserRegistration(
            $toEmail,
            $toName,
            $plainPassword,
            [
                'cni'       => $cni,
                'matricule' => $matricule,
                'email'     => $toEmail,
                'login_id'  => $cni,
            ],
            $loginUrl
        );

        if (! $sentOk) {
            CLI::error('EMAIL FAIL: ' . $mailer->getLastError());
            log_message('error', 'email:test-bo-user-notify SMTP failure: {error}', [
                'error' => $mailer->getLastError(),
            ]);
        } else {
            CLI::write('EMAIL: OK', 'green');
        }

        $db = db_connect();

        $user = $db->query(
            'SELECT utilisateur_id FROM administration.utilisateur WHERE LOWER(email) = LOWER(?) LIMIT 1',
            [$toEmail]
        )->getRowArray();

        if (! $user) {
            $user = $db->query(
                'SELECT utilisateur_id FROM administration.utilisateur ORDER BY utilisateur_id ASC LIMIT 1'
            )->getRowArray();
            CLI::write('No user with that email — linking notification to utilisateur_id=' . ($user['utilisateur_id'] ?? 'none'));
        } else {
            CLI::write('Linking notification to utilisateur_id=' . $user['utilisateur_id']);
        }

        if (! $user) {
            CLI::error('No administration.utilisateur row found — cannot insert notification_utilisateur.');

            return $sentOk ? EXIT_SUCCESS : EXIT_ERROR;
        }

        $utilisateurId = (int) $user['utilisateur_id'];
        $canalId       = $this->lookupCanalId();
        $statuses      = new StatutNotificationModel();
        $statusId      = $sentOk
            ? ($statuses->idByKeywords(['envoy', 'sent', 'deliver', 'succès', 'success']) ?? 2)
            : ($statuses->idByKeywords(['échec', 'echec', 'fail', 'error', 'erreur']) ?? 4);

        if (! $canalId || ! $statusId) {
            CLI::error("Cannot resolve canal ({$canalId}) or status ({$statusId}).");

            return EXIT_ERROR;
        }

        $language = service('language');
        $previous = $language->getLocale();
        $language->setLocale('fr');
        $subject = lang('Mail.subject_bo_user_registration');
        $language->setLocale($previous);

        $body = implode("\n", [
            'Bonjour ' . $toName . ',',
            '',
            'Bienvenue sur JusticeHeritage. Votre compte utilisateur Back Office a été créé avec succès.',
            '',
            'Identifiant de connexion : ' . $cni,
            'Numéro CNI : ' . $cni,
            'Numéro matricule : ' . $matricule,
            'Adresse e-mail : ' . $toEmail,
            'Mot de passe temporaire : ' . $plainPassword,
            '',
            'Connexion Back Office : ' . $loginUrl,
            '',
            'Pour votre sécurité, changez votre mot de passe dès la première connexion.',
            '',
            '[Test automatisé email:test-bo-user-notify]',
        ]);

        $now = date('Y-m-d H:i:s');

        try {
            $db->table('notification.notification_utilisateur')->insert([
                'utilisateur_id'         => $utilisateurId,
                'canal_notification_id'  => $canalId,
                'sujet'                  => $subject,
                'corps'                  => $body,
                'statut_notification_id' => $statusId,
                'envoye_le'              => $now,
                'created_at'             => $now,
            ]);
            $newId = (int) $db->insertID();
        } catch (\Throwable $e) {
            CLI::error('DB INSERT FAIL: ' . $e->getMessage());
            log_message('error', 'email:test-bo-user-notify DB insert failed: {message}', [
                'message' => $e->getMessage(),
            ]);

            return EXIT_ERROR;
        }

        $row = $db->query(
            <<<'SQL'
                SELECT n.notification_utilisateur_id,
                       n.sujet,
                       n.envoye_le,
                       s.description_statut_notification AS status_label,
                       c.description_canal_notification AS channel
                FROM notification.notification_utilisateur n
                LEFT JOIN notification.statut_notification s
                  ON s.statut_notification_id = n.statut_notification_id
                LEFT JOIN notification.canal_notification c
                  ON c.canal_notification_id = n.canal_notification_id
                WHERE n.notification_utilisateur_id = ?
            SQL,
            [$newId]
        )->getRowArray();

        CLI::write('NOTIFICATION ID: ' . ($row['notification_utilisateur_id'] ?? $newId));
        CLI::write('SUBJECT: ' . ($row['sujet'] ?? $subject));
        CLI::write('CHANNEL: ' . ($row['channel'] ?? '—'));
        CLI::write('STATUS: ' . ($row['status_label'] ?? '—'));
        CLI::write('SENT AT: ' . ($row['envoye_le'] ?? $now));

        if (! $sentOk) {
            return EXIT_ERROR;
        }

        $statusLabel = mb_strtolower((string) ($row['status_label'] ?? ''));
        if (
            ! str_contains($statusLabel, 'envoy')
            && ! str_contains($statusLabel, 'sent')
            && ! str_contains($statusLabel, 'deliver')
            && ! str_contains($statusLabel, 'succès')
        ) {
            CLI::error('Email sent but notification status is not Sent: ' . ($row['status_label'] ?? 'unknown'));

            return EXIT_ERROR;
        }

        CLI::write('Verification complete: email Sent + notification_utilisateur recorded.', 'green');

        return EXIT_SUCCESS;
    }

    private function lookupCanalId(): ?int
    {
        try {
            $rows = db_connect()
                ->table('notification.canal_notification')
                ->select('canal_notification_id, description_canal_notification')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return null;
        }

        foreach ($rows as $row) {
            $label = mb_strtolower(trim((string) ($row['description_canal_notification'] ?? '')));
            foreach (['email', 'e-mail', 'mail', 'courriel'] as $needle) {
                if ($label !== '' && str_contains($label, $needle)) {
                    return (int) $row['canal_notification_id'];
                }
            }
        }

        $first = $rows[0] ?? null;

        return $first ? (int) $first['canal_notification_id'] : null;
    }
}
