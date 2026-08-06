<?php

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;
use Config\Services;
use Modules\Administration\Models\AuditLogModel;
use Modules\Administration\Models\StatutCompteModel;
use Modules\Administration\Models\UtilisateurModel;

/**
 * Back-office password + email 2FA authentication.
 * Username may be CNI, employee number (matricule), or email.
 */
class BackofficeAuth
{
    public const CODE_TTL_SECONDS      = 60;
    public const MAX_CODE_ATTEMPTS     = 5;
    public const RESEND_COOLDOWN_SEC   = 60;
    public const MAX_RESENDS_WINDOW    = 5;
    public const RESEND_WINDOW_SEC     = 900;
    public const MAX_PASSWORD_ATTEMPTS = 8;
    public const PASSWORD_LOCK_SEC     = 300;

    public const SESSION_PENDING_ID    = 'bo_2fa_utilisateur_id';
    public const SESSION_EXPIRES_AT    = 'bo_2fa_expires_at';
    public const SESSION_ATTEMPTS      = 'bo_2fa_attempts';
    public const SESSION_EMAIL_MASKED  = 'bo_2fa_email_masked';
    public const SESSION_LAST_RESEND   = 'bo_2fa_last_resend_at';
    public const SESSION_RESEND_COUNT  = 'bo_2fa_resend_count';
    public const SESSION_RESEND_WINDOW = 'bo_2fa_resend_window_start';
    public const SESSION_PWD_FAILS     = 'bo_login_pwd_fails';
    public const SESSION_PWD_LOCK_UNTIL = 'bo_login_pwd_lock_until';
    public const SESSION_USER_ID       = 'backoffice_user_id';
    public const SESSION_USER          = 'backoffice_user';

    private UtilisateurModel $users;
    private StatutCompteModel $statuses;
    private RequestInterface $request;
    private AuditLogModel $audit;

    public function __construct(
        ?UtilisateurModel $users = null,
        ?StatutCompteModel $statuses = null,
        ?RequestInterface $request = null,
        ?AuditLogModel $audit = null
    ) {
        $this->users    = $users ?? new UtilisateurModel();
        $this->statuses = $statuses ?? new StatutCompteModel();
        $this->request  = $request ?? Services::request();
        $this->audit    = $audit ?? new AuditLogModel();
    }

    /**
     * @return array{ok:bool,error?:string,email_masked?:string}
     */
    public function beginLogin(string $username, string $password): array
    {
        $username = trim($username);
        $session  = session();

        $lockUntil = (int) ($session->get(self::SESSION_PWD_LOCK_UNTIL) ?? 0);
        if ($lockUntil > time()) {
            $this->recordAudit('LOGIN', null, false, $username, 'rate_limited');

            return ['ok' => false, 'error' => lang('Backoffice.login_err_rate_limited')];
        }

        if ($username === '' || $password === '') {
            $this->recordAudit('LOGIN', null, false, $username, 'missing_credentials');

            return ['ok' => false, 'error' => lang('Backoffice.login_err_credentials')];
        }

        $user = $this->users->findByLoginIdentifier($username);
        $hash = is_array($user) ? (string) ($user['mot_de_passe_hash'] ?? '') : '';

        if ($user === null || $hash === '' || ! password_verify($password, $hash)) {
            $this->registerPasswordFailure($username, isset($user['utilisateur_id']) ? (int) $user['utilisateur_id'] : null);

            return ['ok' => false, 'error' => lang('Backoffice.login_err_credentials')];
        }

        $userId = (int) $user['utilisateur_id'];

        if (! $this->statuses->isActiveStatus(isset($user['statut_compte_id']) ? (int) $user['statut_compte_id'] : null)) {
            $this->recordAudit('LOGIN', $userId, false, $username, 'account_inactive');

            // Same generic message to avoid account-status enumeration.
            return ['ok' => false, 'error' => lang('Backoffice.login_err_credentials')];
        }

        $email = trim((string) ($user['email'] ?? ''));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->recordAudit('LOGIN', $userId, false, $username, 'missing_email');

            return ['ok' => false, 'error' => lang('Backoffice.login_err_code_send')];
        }

        $session->remove([self::SESSION_PWD_FAILS, self::SESSION_PWD_LOCK_UNTIL]);

        $issued = $this->issueAndSendCode($user);
        if (! ($issued['ok'] ?? false)) {
            return $issued;
        }

        $session->remove([self::SESSION_USER_ID, self::SESSION_USER]);
        $session->set([
            self::SESSION_PENDING_ID    => $userId,
            self::SESSION_EXPIRES_AT    => $issued['expires_at'],
            self::SESSION_ATTEMPTS      => 0,
            self::SESSION_EMAIL_MASKED  => $issued['email_masked'],
            self::SESSION_LAST_RESEND   => time(),
            self::SESSION_RESEND_COUNT  => 1,
            self::SESSION_RESEND_WINDOW => time(),
        ]);

        $this->recordAudit('LOGIN', $userId, true, $username, 'code_sent');

        return [
            'ok'           => true,
            'email_masked' => $issued['email_masked'],
        ];
    }

    /**
     * @return array{ok:bool,error?:string,expired?:bool}
     */
    public function verifyCode(string $inputCode): array
    {
        $session = session();
        $userId = (int) ($session->get(self::SESSION_PENDING_ID) ?? 0);
        $attempts = (int) ($session->get(self::SESSION_ATTEMPTS) ?? 0);

        if ($userId < 1) {
            $this->recordAudit('LOGIN_2FA', null, false, null, 'no_pending_challenge');

            return ['ok' => false, 'error' => lang('Backoffice.login_err_session'), 'expired' => true];
        }

        if ($attempts >= self::MAX_CODE_ATTEMPTS) {
            $this->users->clearAuthenticationCode($userId);
            $this->clearPendingChallenge();
            $this->recordAudit('LOGIN_2FA', $userId, false, null, 'max_attempts');

            return ['ok' => false, 'error' => lang('Backoffice.login_err_max_attempts'), 'expired' => true];
        }

        $user = $this->users->find($userId);
        if (! is_array($user)) {
            $this->clearPendingChallenge();
            $this->recordAudit('LOGIN_2FA', $userId, false, null, 'user_missing');

            return ['ok' => false, 'error' => lang('Backoffice.login_err_session'), 'expired' => true];
        }

        $sessionExpires = (int) ($session->get(self::SESSION_EXPIRES_AT) ?? 0);
        if ($sessionExpires > 0 && time() > $sessionExpires) {
            $this->users->clearAuthenticationCode($userId);
            $this->recordAudit('LOGIN_2FA', $userId, false, null, 'code_expired');

            return [
                'ok'      => false,
                'expired' => true,
                'error'   => lang('Backoffice.login_err_code_expired'),
            ];
        }

        if ($this->users->purgeExpiredAuthenticationCode($userId)) {
            $this->recordAudit('LOGIN_2FA', $userId, false, null, 'code_expired');

            return [
                'ok'      => false,
                'expired' => true,
                'error'   => lang('Backoffice.login_err_code_expired'),
            ];
        }

        $user = $this->users->find($userId);
        if (! is_array($user) || empty($user['code_authentification'])) {
            $this->users->clearAuthenticationCode($userId);
            $this->recordAudit('LOGIN_2FA', $userId, false, null, 'code_expired');

            return [
                'ok'      => false,
                'expired' => true,
                'error'   => lang('Backoffice.login_err_code_expired'),
            ];
        }

        $inputCode = preg_replace('/\D+/', '', $inputCode) ?? '';
        if ($inputCode === '' || ! hash_equals((string) $user['code_authentification'], $inputCode)) {
            $session->set(self::SESSION_ATTEMPTS, $attempts + 1);
            $this->recordAudit('LOGIN_2FA', $userId, false, null, 'code_invalid');

            if ($attempts + 1 >= self::MAX_CODE_ATTEMPTS) {
                $this->users->clearAuthenticationCode($userId);
                $this->clearPendingChallenge();

                return ['ok' => false, 'error' => lang('Backoffice.login_err_max_attempts'), 'expired' => true];
            }

            return ['ok' => false, 'error' => lang('Backoffice.login_err_code_incorrect')];
        }

        // One-time use: clear code + expiry.
        $this->users->clearAuthenticationCode($userId);
        $this->clearPendingChallenge();

        try {
            $this->users->update($userId, [
                'derniere_connexion' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update derniere_connexion for user {id}: {message}', [
                'id'      => $userId,
                'message' => $e->getMessage(),
            ]);
        }

        $profile = [
            'id'         => $userId,
            'first_name' => (string) ($user['prenom_utilisateur'] ?? ''),
            'last_name'  => (string) ($user['nom_utilisateur'] ?? ''),
            'name'       => trim(($user['prenom_utilisateur'] ?? '') . ' ' . ($user['nom_utilisateur'] ?? '')),
            'email'      => (string) ($user['email'] ?? ''),
            'cni'        => (string) ($user['numero_cni'] ?? ''),
            'matricule'  => (string) ($user['numero_matricule'] ?? ''),
            'profil_id'  => (int) ($user['profil_id'] ?? 0),
        ];

        // Rotate the session id on successful 2FA (web only; CLI has no active native session).
        if (! is_cli()) {
            $session->regenerate(true);
        }
        $session->set([
            self::SESSION_USER_ID => $userId,
            self::SESSION_USER    => $profile,
        ]);

        BackofficeAccess::hydrateSessionPermissions($userId, (int) ($profile['profil_id'] ?? 0));

        $this->recordAudit('LOGIN_2FA', $userId, true, $profile['email'], 'authenticated');

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,error?:string,email_masked?:string}
     */
    public function resendCode(): array
    {
        $session = session();
        $userId = (int) ($session->get(self::SESSION_PENDING_ID) ?? 0);
        if ($userId < 1) {
            return ['ok' => false, 'error' => lang('Backoffice.login_err_session')];
        }

        $lastResend = (int) ($session->get(self::SESSION_LAST_RESEND) ?? 0);
        if ($lastResend > 0 && (time() - $lastResend) < self::RESEND_COOLDOWN_SEC) {
            $this->recordAudit('LOGIN_2FA_RESEND', $userId, false, null, 'cooldown');

            return ['ok' => false, 'error' => lang('Backoffice.login_err_resend_cooldown')];
        }

        $windowStart = (int) ($session->get(self::SESSION_RESEND_WINDOW) ?? 0);
        $resendCount = (int) ($session->get(self::SESSION_RESEND_COUNT) ?? 0);
        if ($windowStart < 1 || (time() - $windowStart) > self::RESEND_WINDOW_SEC) {
            $windowStart = time();
            $resendCount = 0;
        }
        if ($resendCount >= self::MAX_RESENDS_WINDOW) {
            $this->recordAudit('LOGIN_2FA_RESEND', $userId, false, null, 'rate_limited');

            return ['ok' => false, 'error' => lang('Backoffice.login_err_rate_limited')];
        }

        $user = $this->users->find($userId);
        if (! is_array($user)) {
            $this->clearPendingChallenge();

            return ['ok' => false, 'error' => lang('Backoffice.login_err_session')];
        }

        // Invalidate previous code before issuing a new one.
        $this->users->clearAuthenticationCode($userId);

        $issued = $this->issueAndSendCode($user);
        if (! ($issued['ok'] ?? false)) {
            $this->recordAudit('LOGIN_2FA_RESEND', $userId, false, null, 'code_email_failed');

            return $issued;
        }

        $session->set([
            self::SESSION_EXPIRES_AT    => $issued['expires_at'],
            self::SESSION_ATTEMPTS      => 0,
            self::SESSION_EMAIL_MASKED  => $issued['email_masked'],
            self::SESSION_LAST_RESEND   => time(),
            self::SESSION_RESEND_COUNT  => $resendCount + 1,
            self::SESSION_RESEND_WINDOW => $windowStart,
        ]);

        $this->recordAudit('LOGIN_2FA_RESEND', $userId, true, null, 'code_sent');

        return [
            'ok'           => true,
            'email_masked' => $issued['email_masked'],
        ];
    }

    public function isAuthenticated(): bool
    {
        return (int) (session()->get(self::SESSION_USER_ID) ?? 0) > 0
            && is_array(session()->get(self::SESSION_USER));
    }

    public function hasPendingChallenge(): bool
    {
        return (int) (session()->get(self::SESSION_PENDING_ID) ?? 0) > 0;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        $user = session()->get(self::SESSION_USER);

        return is_array($user) ? $user : null;
    }

    public function logout(): void
    {
        $userId = (int) (session()->get(self::SESSION_USER_ID) ?? 0);
        $this->recordAudit('LOGOUT', $userId > 0 ? $userId : null, true, null, 'logout');
        session()->remove([
            self::SESSION_USER_ID,
            self::SESSION_USER,
            self::SESSION_PENDING_ID,
            self::SESSION_EXPIRES_AT,
            self::SESSION_ATTEMPTS,
            self::SESSION_EMAIL_MASKED,
            self::SESSION_LAST_RESEND,
            self::SESSION_RESEND_COUNT,
            self::SESSION_RESEND_WINDOW,
            BackofficeAccess::SESSION_PERMISSIONS,
            BackofficeAccess::SESSION_PERM_HASH,
            BackofficeAccess::SESSION_PROFIL_ID,
        ]);
        if (! is_cli()) {
            session()->regenerate(true);
        }
    }

    public function clearPendingChallenge(): void
    {
        session()->remove([
            self::SESSION_PENDING_ID,
            self::SESSION_EXPIRES_AT,
            self::SESSION_ATTEMPTS,
            self::SESSION_EMAIL_MASKED,
            self::SESSION_LAST_RESEND,
            self::SESSION_RESEND_COUNT,
            self::SESSION_RESEND_WINDOW,
        ]);
    }

    /**
     * @param array<string, mixed> $user
     * @return array{ok:bool,error?:string,email_masked?:string,expires_at?:int}
     */
    private function issueAndSendCode(array $user): array
    {
        $userId = (int) ($user['utilisateur_id'] ?? 0);
        $email  = trim((string) ($user['email'] ?? ''));
        $name   = trim(($user['prenom_utilisateur'] ?? '') . ' ' . ($user['nom_utilisateur'] ?? ''));
        $code   = $this->generateCode();
        $expiresAt = time() + self::CODE_TTL_SECONDS;

        if (! $this->users->setAuthenticationCode($userId, $code, $expiresAt)) {
            $this->recordAudit('LOGIN', $userId, false, $email, 'code_persist_failed');

            return ['ok' => false, 'error' => lang('Backoffice.login_err_code_send')];
        }

        $mailer = service('notifications');
        $mailOk = false;

        log_message('info', 'Back-office 2FA: invoking email send for utilisateur_id={id} email={email} smtp_configured={configured}', [
            'id'          => (string) $userId,
            'email'       => $email,
            'configured'  => $mailer->isConfigured() ? '1' : '0',
        ]);

        try {
            $mailOk = $mailer->sendTwoFactorCode($email, $name !== '' ? $name : $email, [
                'code' => $code,
                'ttl'  => self::CODE_TTL_SECONDS,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Back-office 2FA email threw exception for utilisateur_id={id}: {error}', [
                'id'    => (string) $userId,
                'error' => $e->getMessage(),
            ]);
            $mailOk = false;
        }

        if (! $mailOk) {
            $smtpError = $mailer->getLastError() ?: 'unknown';
            $this->users->clearAuthenticationCode($userId);
            log_message('error', 'Back-office 2FA email failed for utilisateur_id={id} email={email}. Complete SMTP error: {error}', [
                'id'    => (string) $userId,
                'email' => $email,
                'error' => $smtpError,
            ]);
            $this->recordAudit('LOGIN', $userId, false, $email, 'code_email_failed');

            return ['ok' => false, 'error' => lang('Backoffice.login_err_code_send')];
        }

        return [
            'ok'           => true,
            'email_masked' => $this->maskEmail($email),
            'expires_at'   => $expiresAt,
        ];
    }

    private function registerPasswordFailure(string $username, ?int $userId): void
    {
        $session = session();
        $fails   = (int) ($session->get(self::SESSION_PWD_FAILS) ?? 0) + 1;
        $session->set(self::SESSION_PWD_FAILS, $fails);

        if ($fails >= self::MAX_PASSWORD_ATTEMPTS) {
            $session->set(self::SESSION_PWD_LOCK_UNTIL, time() + self::PASSWORD_LOCK_SEC);
            $session->set(self::SESSION_PWD_FAILS, 0);
            $this->recordAudit('LOGIN', $userId, false, $username, 'locked_out');
        } else {
            $this->recordAudit('LOGIN', $userId, false, $username, 'invalid_credentials');
        }
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return '***';
        }

        $name    = $parts[0];
        $visible = substr($name, 0, min(2, strlen($name)));

        return $visible . str_repeat('*', max(1, strlen($name) - strlen($visible))) . '@' . $parts[1];
    }

    private function recordAudit(string $action, ?int $userId, bool $success, ?string $identifier, string $reason): void
    {
        log_message('info', 'BO auth event={event} success={success} utilisateur_id={id} identifier={user} reason={reason} ip={ip}', [
            'event'  => $action,
            'success'=> $success ? '1' : '0',
            'id'     => $userId !== null ? (string) $userId : '-',
            'user'   => $identifier !== null && $identifier !== '' ? $identifier : '-',
            'reason' => $reason,
            'ip'     => $this->request->getIPAddress(),
        ]);

        // Never store OTP / password material. Always attach the known user id
        // (including failed login / failed 2FA email) so audit_log FK/columns stay valid.
        $this->audit->record(
            $action,
            'administration.utilisateur',
            $userId,
            null,
            [
                'success'    => $success,
                'reason'     => $reason,
                'identifier' => $identifier !== null && $identifier !== '' ? $this->maskIdentifier($identifier) : null,
            ],
            $userId
        );
    }

    private function maskIdentifier(string $identifier): string
    {
        if (str_contains($identifier, '@')) {
            return $this->maskEmail($identifier);
        }

        $len = mb_strlen($identifier);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return mb_substr($identifier, 0, 2) . str_repeat('*', $len - 4) . mb_substr($identifier, -2);
    }
}
