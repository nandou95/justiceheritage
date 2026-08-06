<?php

namespace Modules\Notification\Commands;

use App\Libraries\BackofficeAuth;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Administration\Models\UtilisateurModel;
use Modules\Notification\Services\NotificationMailer;

/**
 * End-to-end back-office 2FA: persist code, send French email, verify, clear.
 *
 * Usage: php spark email:bo-2fa-smoke <login>
 *   login = CNI, matricule, or email of an administration.utilisateur
 */
class BackofficeTwoFactorSmoke extends BaseCommand
{
    protected $group       = 'Email';
    protected $name        = 'email:bo-2fa-smoke';
    protected $description = 'Generate, store, and email a back-office 2FA code.';
    protected $usage       = 'email:bo-2fa-smoke <login>';
    protected $arguments   = [
        'login' => 'Back-office login (CNI, matricule, or email).',
    ];

    public function run(array $params)
    {
        $login = trim((string) ($params[0] ?? ''));
        $users = new UtilisateurModel();
        $db    = db_connect();

        if ($login === '') {
            $sample = $db->query(
                "SELECT email FROM administration.utilisateur
                 WHERE email IS NOT NULL AND BTRIM(email) <> ''
                 ORDER BY utilisateur_id LIMIT 5"
            )->getResultArray();
            CLI::error('Usage: php spark email:bo-2fa-smoke <login>');
            foreach ($sample as $row) {
                CLI::write('  example login: ' . ($row['email'] ?? ''));
            }

            return EXIT_ERROR;
        }

        $user = $users->findByLoginIdentifier($login);
        if (! is_array($user)) {
            CLI::error('Back-office user not found for login: ' . $login);

            return EXIT_ERROR;
        }

        $userId = (int) $user['utilisateur_id'];
        $email  = trim((string) ($user['email'] ?? ''));
        $name   = trim(($user['prenom_utilisateur'] ?? '') . ' ' . ($user['nom_utilisateur'] ?? ''));
        $code   = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $ttl    = BackofficeAuth::CODE_TTL_SECONDS;
        $expiresAt = time() + $ttl;

        CLI::write('utilisateur_id : ' . $userId);
        CLI::write('email          : ' . $email);
        CLI::write('Saving authentication code (TTL ' . $ttl . 's)…');

        if (! $users->setAuthenticationCode($userId, $code, $expiresAt)) {
            CLI::error('Failed to save code_authentification.');

            return EXIT_ERROR;
        }

        $row = $users->find($userId);
        $savedCode = is_array($row) && hash_equals((string) ($row['code_authentification'] ?? ''), $code);
        $hasExpiry = is_array($row) && ! empty($row['code_authentification_expire_at']);
        CLI::write('DB code saved  : ' . ($savedCode ? 'OK' : 'FAIL'), $savedCode ? 'green' : 'red');
        CLI::write(
            'DB expiry saved: ' . ($hasExpiry ? 'OK (' . ($row['code_authentification_expire_at'] ?? '') . ')' : 'MISSING COLUMN / NULL'),
            $hasExpiry ? 'green' : 'yellow'
        );

        if (! $savedCode) {
            return EXIT_ERROR;
        }

        /** @var NotificationMailer $mailer */
        $mailer = service('notifications');
        CLI::write('SMTP configured: ' . ($mailer->isConfigured() ? 'yes' : 'no'));
        CLI::write('SMTP transport : ' . trim((string) config('Email')->SMTPHost)
            . ':' . (int) config('Email')->SMTPPort
            . '/' . (string) config('Email')->SMTPCrypto);
        CLI::write('Sending French 2FA email…');

        $sent = $mailer->sendTwoFactorCode($email, $name !== '' ? $name : $email, [
            'code' => $code,
            'ttl'  => $ttl,
        ]);

        if (! $sent) {
            CLI::error('Email failed: ' . $mailer->getLastError());

            return EXIT_ERROR;
        }

        CLI::write('2FA email sent successfully to ' . $email, 'green');

        // Simulate the pending challenge + verify path used by the controller.
        $session = session();
        $session->set([
            BackofficeAuth::SESSION_PENDING_ID   => $userId,
            BackofficeAuth::SESSION_EXPIRES_AT   => $expiresAt,
            BackofficeAuth::SESSION_ATTEMPTS     => 0,
            BackofficeAuth::SESSION_EMAIL_MASKED => '***',
        ]);

        $auth   = new BackofficeAuth($users);
        $result = $auth->verifyCode($code);

        if (! ($result['ok'] ?? false)) {
            CLI::error('verifyCode failed: ' . ($result['error'] ?? 'unknown'));

            return EXIT_ERROR;
        }

        $after = $users->find($userId);
        $cleared = is_array($after)
            && ($after['code_authentification'] === null || $after['code_authentification'] === '');
        $authenticated = $auth->isAuthenticated();

        CLI::write('Code verified  : OK', 'green');
        CLI::write('Code cleared   : ' . ($cleared ? 'OK' : 'FAIL'), $cleared ? 'green' : 'red');
        CLI::write('Session auth   : ' . ($authenticated ? 'OK' : 'FAIL'), $authenticated ? 'green' : 'red');

        $auth->logout();

        if (! $cleared || ! $authenticated) {
            return EXIT_ERROR;
        }

        CLI::write('Back-office 2FA end-to-end smoke passed.', 'green');

        return EXIT_SUCCESS;
    }
}
