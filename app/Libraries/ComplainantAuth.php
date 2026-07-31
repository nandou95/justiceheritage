<?php

namespace App\Libraries;

use App\Models\PersonneModel;
use CodeIgniter\HTTP\IncomingRequest;
use Config\Services;

/**
 * Complainant password + email 2FA authentication.
 */
class ComplainantAuth
{
    public const CODE_TTL_SECONDS     = 60;
    public const MAX_CODE_ATTEMPTS    = 5;
    public const SESSION_PENDING_ID   = '2fa_personne_id';
    public const SESSION_EXPIRES_AT   = '2fa_expires_at';
    public const SESSION_ATTEMPTS     = '2fa_attempts';
    public const SESSION_EMAIL_MASKED = '2fa_email_masked';
    public const SESSION_AUTH         = 'complainant_authenticated';
    public const SESSION_USER         = 'complainant_user';

    private PersonneModel $personnes;
    private IncomingRequest $request;

    public function __construct(?PersonneModel $personnes = null, ?IncomingRequest $request = null)
    {
        $this->personnes = $personnes ?? new PersonneModel();
        $this->request   = $request ?? Services::request();
    }

    /**
     * @return array{ok:bool,error?:string,email_masked?:string}
     */
    public function beginLogin(string $username, string $password): array
    {
        $username = trim($username);

        if ($username === '' || $password === '') {
            $this->audit('login_password', false, null, $username, 'missing_credentials');

            return ['ok' => false, 'error' => lang('Site.login_err_credentials')];
        }

        $person = $this->personnes->findByUsername($username);
        if ($person === null || ! password_verify($password, (string) $person['mot_de_passe_hash'])) {
            $this->audit('login_password', false, $person['personne_id'] ?? null, $username, 'invalid_credentials');

            return ['ok' => false, 'error' => lang('Site.login_err_credentials')];
        }

        $code = $this->generateCode();
        $expiresAt = time() + self::CODE_TTL_SECONDS;

        if (! $this->personnes->setAuthenticationCode((int) $person['personne_id'], $code, $expiresAt)) {
            $this->audit('login_password', false, (int) $person['personne_id'], $username, 'code_persist_failed');

            return ['ok' => false, 'error' => lang('Site.login_err_code_send')];
        }

        $mailer = service('notifications');
        $mailOk = $mailer->sendTwoFactorCode(
            (string) $person['email'],
            trim($person['prenom_personne'] . ' ' . $person['nom_personne']),
            ['code' => $code, 'ttl' => self::CODE_TTL_SECONDS]
        );

        if (! $mailOk) {
            $this->personnes->clearAuthenticationCode((int) $person['personne_id']);
            $smtpError = $mailer->getLastError();
            log_message('error', '2FA email failed for personne_id={pid} email={email}. Complete error: {error}', [
                'pid'   => (string) $person['personne_id'],
                'email' => (string) $person['email'],
                'error' => $smtpError !== '' ? $smtpError : 'unknown',
            ]);
            $this->audit('login_password', false, (int) $person['personne_id'], $username, 'code_email_failed');

            return ['ok' => false, 'error' => lang('Site.login_err_code_send')];
        }

        $emailMasked = $this->maskEmail((string) $person['email']);

        $session = session();
        $session->remove([self::SESSION_AUTH, self::SESSION_USER]);
        $session->set([
            self::SESSION_PENDING_ID   => (int) $person['personne_id'],
            self::SESSION_EXPIRES_AT   => $expiresAt,
            self::SESSION_ATTEMPTS     => 0,
            self::SESSION_EMAIL_MASKED => $emailMasked,
        ]);

        $this->audit('login_password', true, (int) $person['personne_id'], $username, 'code_sent');

        return [
            'ok'           => true,
            'email_masked' => $emailMasked,
        ];
    }

    /**
     * @return array{ok:bool,error?:string,expired?:bool}
     */
    public function verifyCode(string $inputCode): array
    {
        $session    = session();
        $personneId = (int) ($session->get(self::SESSION_PENDING_ID) ?? 0);
        $attempts   = (int) ($session->get(self::SESSION_ATTEMPTS) ?? 0);

        if ($personneId < 1) {
            $this->audit('login_2fa', false, null, null, 'no_pending_challenge');

            return ['ok' => false, 'error' => lang('Site.login_err_session'), 'expired' => true];
        }

        if ($attempts >= self::MAX_CODE_ATTEMPTS) {
            $this->personnes->clearAuthenticationCode($personneId);
            $this->clearPendingChallenge();
            $this->audit('login_2fa', false, $personneId, null, 'max_attempts');

            return ['ok' => false, 'error' => lang('Site.login_err_max_attempts'), 'expired' => true];
        }

        $person = $this->personnes->find($personneId);
        if ($person === null) {
            $this->clearPendingChallenge();
            $this->audit('login_2fa', false, $personneId, null, 'person_missing');

            return ['ok' => false, 'error' => lang('Site.login_err_session'), 'expired' => true];
        }

        // Auto-null expired codes before validating the submitted value.
        if ($this->personnes->purgeExpiredAuthenticationCode($personneId)) {
            $this->audit('login_2fa', false, $personneId, null, 'code_expired');

            return [
                'ok'      => false,
                'expired' => true,
                'error'   => lang('Site.login_err_code_expired'),
            ];
        }

        $person = $this->personnes->find($personneId);
        if ($person === null || empty($person['code_authentification'])) {
            $this->personnes->clearAuthenticationCode($personneId);
            $this->audit('login_2fa', false, $personneId, null, 'code_expired');

            return [
                'ok'      => false,
                'expired' => true,
                'error'   => lang('Site.login_err_code_expired'),
            ];
        }

        $inputCode = preg_replace('/\D+/', '', $inputCode) ?? '';
        if ($inputCode === '' || ! hash_equals((string) $person['code_authentification'], $inputCode)) {
            $session->set(self::SESSION_ATTEMPTS, $attempts + 1);
            $this->audit('login_2fa', false, $personneId, null, 'code_invalid');

            if ($attempts + 1 >= self::MAX_CODE_ATTEMPTS) {
                $this->personnes->clearAuthenticationCode($personneId);
                $this->clearPendingChallenge();

                return ['ok' => false, 'error' => lang('Site.login_err_max_attempts'), 'expired' => true];
            }

            return ['ok' => false, 'error' => lang('Site.login_err_code_incorrect')];
        }

        // One-time use
        $this->personnes->clearAuthenticationCode($personneId);
        $this->clearPendingChallenge();

        $user = [
            'id'         => (int) $person['personne_id'],
            'username'   => (string) $person['user_name'],
            'email'      => (string) $person['email'],
            'first_name' => (string) $person['prenom_personne'],
            'last_name'  => (string) $person['nom_personne'],
            'name'       => trim($person['prenom_personne'] . ' ' . $person['nom_personne']),
            'phone'      => (string) $person['telephone'],
            'national_id'=> (string) ($person['numero_cni'] ?? ''),
        ];

        $session->regenerate(true);
        $session->set([
            self::SESSION_AUTH => true,
            self::SESSION_USER => $user,
            'portal_user_name'  => $user['name'],
            'portal_user_email' => $user['email'],
            'portal_demo'       => false,
        ]);

        $this->audit('login_2fa', true, $personneId, $user['username'], 'authenticated');

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,error?:string,email_masked?:string}
     */
    public function resendCode(): array
    {
        $session    = session();
        $personneId = (int) ($session->get(self::SESSION_PENDING_ID) ?? 0);
        if ($personneId < 1) {
            return ['ok' => false, 'error' => lang('Site.login_err_session')];
        }

        $person = $this->personnes->find($personneId);
        if ($person === null) {
            $this->clearPendingChallenge();

            return ['ok' => false, 'error' => lang('Site.login_err_session')];
        }

        $code      = $this->generateCode();
        $expiresAt = time() + self::CODE_TTL_SECONDS;

        // Invalidate any previous code, then store the new one.
        $this->personnes->clearAuthenticationCode($personneId);

        if (! $this->personnes->setAuthenticationCode($personneId, $code, $expiresAt)) {
            return ['ok' => false, 'error' => lang('Site.login_err_code_send')];
        }

        $mailer = service('notifications');
        $mailOk = $mailer->sendTwoFactorCode(
            (string) $person['email'],
            trim($person['prenom_personne'] . ' ' . $person['nom_personne']),
            ['code' => $code, 'ttl' => self::CODE_TTL_SECONDS]
        );

        if (! $mailOk) {
            $this->personnes->clearAuthenticationCode($personneId);
            log_message('error', '2FA resend email failed for personne_id={pid} email={email}. Complete error: {error}', [
                'pid'   => (string) $personneId,
                'email' => (string) $person['email'],
                'error' => $mailer->getLastError() !== '' ? $mailer->getLastError() : 'unknown',
            ]);
            $this->audit('login_2fa_resend', false, $personneId, null, 'code_email_failed');

            return ['ok' => false, 'error' => lang('Site.login_err_code_send')];
        }

        $emailMasked = $this->maskEmail((string) $person['email']);

        $session->set([
            self::SESSION_EXPIRES_AT   => $expiresAt,
            self::SESSION_ATTEMPTS     => 0,
            self::SESSION_EMAIL_MASKED => $emailMasked,
        ]);

        $this->audit('login_2fa_resend', true, $personneId, null, 'code_sent');

        return [
            'ok'           => true,
            'email_masked' => $emailMasked,
        ];
    }

    public function isAuthenticated(): bool
    {
        return (bool) session()->get(self::SESSION_AUTH) && is_array(session()->get(self::SESSION_USER));
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
        $user = $this->user();
        $this->audit('logout', true, $user['id'] ?? null, $user['username'] ?? null, 'logout');
        session()->remove([
            self::SESSION_AUTH,
            self::SESSION_USER,
            self::SESSION_PENDING_ID,
            self::SESSION_EXPIRES_AT,
            self::SESSION_ATTEMPTS,
            self::SESSION_EMAIL_MASKED,
            'portal_user_name',
            'portal_user_email',
            'portal_demo',
        ]);
        session()->regenerate(true);
    }

    public function clearPendingChallenge(): void
    {
        session()->remove([
            self::SESSION_PENDING_ID,
            self::SESSION_EXPIRES_AT,
            self::SESSION_ATTEMPTS,
            self::SESSION_EMAIL_MASKED,
        ]);
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

        $name = $parts[0];
        $visible = substr($name, 0, min(2, strlen($name)));

        return $visible . str_repeat('*', max(1, strlen($name) - strlen($visible))) . '@' . $parts[1];
    }

    private function audit(string $event, bool $success, ?int $personneId, ?string $username, string $reason): void
    {
        // Never log verification codes.
        log_message('info', 'Auth event={event} success={success} personne_id={pid} username={user} reason={reason} ip={ip}', [
            'event'  => $event,
            'success'=> $success ? '1' : '0',
            'pid'    => $personneId !== null ? (string) $personneId : '-',
            'user'   => $username !== null && $username !== '' ? $username : '-',
            'reason' => $reason,
            'ip'     => $this->request->getIPAddress(),
        ]);
    }
}
