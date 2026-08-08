<?php

namespace Modules\Administration\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Administration\Models\ProfilPermissionModel;
use Modules\Administration\Models\UtilisateurModel;

class AccountService
{
    private UtilisateurModel $users;
    private ProfilPermissionModel $profilePermissions;
    private AuditLogModel $audit;

    public function __construct(
        ?UtilisateurModel $users = null,
        ?ProfilPermissionModel $profilePermissions = null,
        ?AuditLogModel $audit = null
    ) {
        $this->users              = $users ?? new UtilisateurModel();
        $this->profilePermissions = $profilePermissions ?? new ProfilPermissionModel();
        $this->audit              = $audit ?? new AuditLogModel();
    }

    /**
     * Full payload for the "My Profile" page.
     *
     * @return array{
     *   record: array<string, mixed>,
     *   permission_groups: list<array{module_key:string,module:string,permissions:list<array{description:string,url_route:string}>}>,
     *   password_changed_at: string|null,
     *   two_factor_enabled: bool
     * }|null
     */
    public function profilePageData(int $utilisateurId): ?array
    {
        $record = $this->users->findWithRelations($utilisateurId);
        if (! is_array($record)) {
            return null;
        }

        return [
            'record'              => $record,
            'permission_groups'   => $this->permissionGroupsForProfil((int) ($record['profil_id'] ?? 0)),
            'password_changed_at' => $this->lastPasswordChangedAt($utilisateurId),
            // Back-office login always challenges with an email OTP code.
            'two_factor_enabled'  => true,
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function updateProfile(int $utilisateurId, array $input): array
    {
        $existing = $this->users->find($utilisateurId);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.account_err_not_found')]];
        }

        $email = trim((string) ($input['email'] ?? ''));
        $phone = trim((string) ($input['telephone'] ?? ''));
        $errors = [];

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = lang('Backoffice.account_err_email');
        } elseif ($this->users->emailExists($email, $utilisateurId)) {
            $errors[] = lang('Backoffice.users_err_email_taken');
        }

        if ($phone === '') {
            $errors[] = lang('Backoffice.account_err_phone');
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $payload = [
            'email'     => $email,
            'telephone' => $phone,
        ];

        try {
            $this->users->update($utilisateurId, $payload);
        } catch (\Throwable $e) {
            log_message('error', 'Account profile update failed: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.account_err_save') . ' ' . $e->getMessage()]];
        }

        $sessionUser = session('backoffice_user');
        if (is_array($sessionUser)) {
            $sessionUser['email'] = $email;
            session()->set('backoffice_user', $sessionUser);
        }

        $this->audit->record('UPDATE', 'administration.utilisateur', $utilisateurId, $existing, $payload, $utilisateurId);

        return ['ok' => true];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function changePassword(int $utilisateurId, array $input): array
    {
        $existing = $this->users->find($utilisateurId);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.account_err_not_found')]];
        }

        $current = (string) ($input['current_password'] ?? '');
        $next    = (string) ($input['new_password'] ?? '');
        $confirm = (string) ($input['new_password_confirm'] ?? '');
        $hash    = (string) ($existing['mot_de_passe_hash'] ?? '');

        $errors = [];
        if ($current === '' || $hash === '' || ! password_verify($current, $hash)) {
            $errors[] = lang('Backoffice.account_err_current_password');
        }
        if (strlen($next) < 8 || strlen($next) > 72) {
            $errors[] = lang('Backoffice.account_err_password_min');
        }
        if ($next !== $confirm) {
            $errors[] = lang('Backoffice.account_err_password_match');
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        try {
            $this->users->update($utilisateurId, [
                'mot_de_passe_hash' => password_hash($next, PASSWORD_DEFAULT),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Account password update failed: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.account_err_password') . ' ' . $e->getMessage()]];
        }

        $this->audit->record('UPDATE', 'administration.utilisateur', $utilisateurId, null, [
            'password_changed' => true,
        ], $utilisateurId);

        return ['ok' => true];
    }

    /**
     * @return list<array{module_key:string,module:string,permissions:list<array{description:string,url_route:string}>}>
     */
    private function permissionGroupsForProfil(int $profilId): array
    {
        if ($profilId < 1) {
            return [];
        }

        try {
            $rows = $this->profilePermissions->listAssignedPermissions($profilId);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load account permissions: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        $groups = [];
        foreach ($rows as $row) {
            $route     = (string) ($row['url_route'] ?? '');
            $moduleKey = $this->moduleKeyFromRoute($route);
            $groups[$moduleKey][] = [
                'description' => (string) ($row['description_permission'] ?? ''),
                'url_route'   => $route,
            ];
        }

        $result = [];
        foreach ($groups as $moduleKey => $permissions) {
            usort($permissions, static fn (array $a, array $b): int => strcasecmp($a['description'], $b['description']));
            $result[] = [
                'module_key'  => $moduleKey,
                'module'      => $this->moduleTitle($moduleKey),
                'permissions' => $permissions,
            ];
        }

        usort($result, static fn (array $a, array $b): int => strcasecmp($a['module'], $b['module']));

        return $result;
    }

    private function lastPasswordChangedAt(int $utilisateurId): ?string
    {
        try {
            $row = db_connect()->table('audit_log.audit_log')
                ->select('created_at, nouvelles_valeurs')
                ->where('utilisateur_id', $utilisateurId)
                ->where('table_cible', 'administration.utilisateur')
                ->where('enregistrement_id', $utilisateurId)
                ->where('action', 'UPDATE')
                ->orderBy('created_at', 'DESC')
                ->limit(30)
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return null;
        }

        foreach ($row as $entry) {
            $payload = json_decode((string) ($entry['nouvelles_valeurs'] ?? ''), true);
            if (is_array($payload) && ! empty($payload['password_changed'])) {
                return (string) ($entry['created_at'] ?? '');
            }
        }

        return null;
    }

    private function moduleKeyFromRoute(string $route): string
    {
        $path = trim((string) (parse_url($route, PHP_URL_PATH) ?: $route), '/');
        if ($path === '') {
            return 'general';
        }

        $parts = explode('/', $path);
        if (($parts[0] ?? '') === 'backoffice' && isset($parts[1]) && $parts[1] !== '') {
            return strtolower((string) $parts[1]);
        }

        return strtolower((string) ($parts[0] ?: 'general'));
    }

    private function moduleTitle(string $moduleKey): string
    {
        $key        = 'Backoffice.perm_group_' . str_replace('-', '_', $moduleKey);
        $translated = lang($key);

        if ($translated === $key || $translated === 'perm_group_' . str_replace('-', '_', $moduleKey)) {
            return ucfirst(str_replace(['-', '_'], ' ', $moduleKey));
        }

        return $translated;
    }
}
