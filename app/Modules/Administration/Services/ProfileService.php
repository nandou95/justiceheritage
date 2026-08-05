<?php

namespace Modules\Administration\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Administration\Models\PermissionModel;
use Modules\Administration\Models\ProfilModel;
use Modules\Administration\Models\ProfilPermissionModel;

class ProfileService
{
    private ProfilModel $profiles;
    private ProfilPermissionModel $profilePermissions;
    private PermissionModel $permissions;
    private AuditLogModel $audit;

    public function __construct(
        ?ProfilModel $profiles = null,
        ?ProfilPermissionModel $profilePermissions = null,
        ?PermissionModel $permissions = null,
        ?AuditLogModel $audit = null
    ) {
        $this->profiles           = $profiles ?? new ProfilModel();
        $this->profilePermissions = $profilePermissions ?? new ProfilPermissionModel();
        $this->permissions        = $permissions ?? new PermissionModel();
        $this->audit              = $audit ?? new AuditLogModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?bool $isActive = null): array
    {
        try {
            $rows = $this->profiles->listWithPermissionCounts($isActive);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list profiles: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $active = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return [
                'id'                => (int) $row['profil_id'],
                'code'              => $row['code_profil'] ?? '',
                'name'              => $row['libelle_profil'] ?? '',
                'description'       => $row['description_profil'] ?? '',
                'permissions_count' => (int) ($row['permissions_count'] ?? 0),
                'is_active'         => $active,
                'status'            => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
                'created_at'        => $row['created_at'] ?? null,
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $row = $this->profiles->find($id);
        if (! $row) {
            return null;
        }

        $assigned = [];
        try {
            $assigned = $this->profilePermissions->listAssignedPermissions($id);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load profile permissions {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);
        }

        $active = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return [
            'profil_id'           => (int) $row['profil_id'],
            'code_profil'         => $row['code_profil'] ?? '',
            'libelle_profil'      => $row['libelle_profil'] ?? '',
            'description_profil'  => $row['description_profil'] ?? '',
            'is_active'           => $active,
            'status'              => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            'created_at'          => $row['created_at'] ?? null,
            'permission_ids'      => array_map(static fn (array $p): int => (int) $p['permission_id'], $assigned),
            'permissions'         => array_map(static function (array $p): array {
                $permActive = filter_var($p['permission_is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

                return [
                    'id'          => (int) $p['permission_id'],
                    'description' => $p['description_permission'] ?? '',
                    'url_route'   => $p['url_route'] ?? '',
                    'is_active'   => $permActive,
                    'status'      => $permActive ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
                ];
            }, $assigned),
            'permissions_count'   => count($assigned),
        ];
    }

    /**
     * Permissions grouped for assignment UI.
     *
     * @return list<array{module:string,permissions:list<array<string,mixed>>}>
     */
    public function permissionsGrouped(): array
    {
        try {
            $rows = $this->permissions->listFiltered(null);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load permissions for profile form: {message}', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }

        $groups = [];
        foreach ($rows as $row) {
            $active = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $route  = (string) ($row['url_route'] ?? '');
            $module = $this->moduleFromRoute($route);

            $groups[$module][] = [
                'id'          => (int) $row['permission_id'],
                'description' => $row['description_permission'] ?? '',
                'url_route'   => $route,
                'is_active'   => $active,
                'status'      => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            ];
        }

        ksort($groups, SORT_NATURAL | SORT_FLAG_CASE);

        $result = [];
        foreach ($groups as $module => $permissions) {
            usort($permissions, static fn (array $a, array $b): int => strcasecmp($a['description'], $b['description']));
            $result[] = [
                'module'      => $module,
                'permissions' => $permissions,
            ];
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function create(array $input): array
    {
        $errors = $this->validate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $permissionIds = $this->normalizePermissionIds($input['permission_ids'] ?? []);
        $data = [
            'code_profil'        => trim((string) $input['code_profil']),
            'libelle_profil'     => trim((string) $input['libelle_profil']),
            'description_profil' => trim((string) ($input['description_profil'] ?? '')),
            'is_active'          => true,
            'created_at'         => date('Y-m-d H:i:s'),
        ];

        $db = db_connect();
        $db->transStart();

        try {
            $id = $this->profiles->insert($data, true);
            if (! $id) {
                $db->transRollback();

                return ['ok' => false, 'errors' => [lang('Backoffice.profiles_err_save')]];
            }

            $this->profilePermissions->syncPermissions((int) $id, $permissionIds);
            $db->transComplete();

            if (! $db->transStatus()) {
                return ['ok' => false, 'errors' => [lang('Backoffice.profiles_err_save')]];
            }

            $this->audit->record(
                'CREATE',
                'administration.profil',
                (int) $id,
                null,
                $data + ['permission_ids' => $permissionIds],
                $this->actorId()
            );
            $this->audit->record(
                'ASSIGN_PERMISSIONS',
                'administration.profil_permission',
                (int) $id,
                null,
                ['permission_ids' => $permissionIds],
                $this->actorId()
            );

            return ['ok' => true, 'id' => (int) $id];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to create profile: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.profiles_err_save')]];
        }
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input): array
    {
        $existing = $this->profiles->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.profiles_err_not_found')]];
        }

        $errors = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $oldPermissionIds = $this->profilePermissions->activePermissionIds($id);
        $permissionIds    = $this->normalizePermissionIds($input['permission_ids'] ?? []);
        $data = [
            'code_profil'        => trim((string) $input['code_profil']),
            'libelle_profil'     => trim((string) $input['libelle_profil']),
            'description_profil' => trim((string) ($input['description_profil'] ?? '')),
        ];

        $db = db_connect();
        $db->transStart();

        try {
            $this->profiles->update($id, $data);
            $this->profilePermissions->syncPermissions($id, $permissionIds);
            $db->transComplete();

            if (! $db->transStatus()) {
                return ['ok' => false, 'errors' => [lang('Backoffice.profiles_err_save')]];
            }

            $this->audit->record(
                'UPDATE',
                'administration.profil',
                $id,
                [
                    'code_profil'        => $existing['code_profil'] ?? null,
                    'libelle_profil'     => $existing['libelle_profil'] ?? null,
                    'description_profil' => $existing['description_profil'] ?? null,
                ],
                $data,
                $this->actorId()
            );
            $this->audit->record(
                'ASSIGN_PERMISSIONS',
                'administration.profil_permission',
                $id,
                ['permission_ids' => $oldPermissionIds],
                ['permission_ids' => $permissionIds],
                $this->actorId()
            );

            return ['ok' => true];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to update profile {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'errors' => [lang('Backoffice.profiles_err_save')]];
        }
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $row = $this->profiles->find($id);
        if (! $row) {
            return ['ok' => false, 'errors' => [lang('Backoffice.profiles_err_not_found')]];
        }

        $isActive   = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $activating = ! $isActive;

        try {
            $this->profiles->update($id, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to toggle profile {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'errors' => [lang('Backoffice.profiles_err_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'administration.profil',
            $id,
            ['is_active' => $isActive],
            ['is_active' => $activating],
            $this->actorId()
        );

        return ['ok' => true, 'activated' => $activating];
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validate(array $input, ?int $ignoreId = null): array
    {
        $errors = [];
        $code   = trim((string) ($input['code_profil'] ?? ''));
        $name   = trim((string) ($input['libelle_profil'] ?? ''));

        if ($code === '') {
            $errors[] = lang('Backoffice.profiles_err_required_code');
        }
        if ($name === '') {
            $errors[] = lang('Backoffice.profiles_err_required_name');
        }

        if ($code !== '' && ! preg_match('/^[A-Za-z0-9_\-.]+$/', $code)) {
            $errors[] = lang('Backoffice.profiles_err_code_format');
        }

        if ($code !== '' && $this->profiles->codeExists($code, $ignoreId)) {
            $errors[] = lang('Backoffice.profiles_err_code_taken');
        }

        $permissionIds = $this->normalizePermissionIds($input['permission_ids'] ?? []);
        foreach ($permissionIds as $permissionId) {
            if (! $this->permissions->find($permissionId)) {
                $errors[] = lang('Backoffice.profiles_err_permission');
                break;
            }
        }

        return $errors;
    }

    /**
     * @param mixed $raw
     * @return list<int>
     */
    private function normalizePermissionIds($raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $raw))));
    }

    private function moduleFromRoute(string $route): string
    {
        $path = trim(parse_url($route, PHP_URL_PATH) ?: $route, '/');
        if ($path === '') {
            return lang('Backoffice.profiles_module_general');
        }

        $parts = explode('/', $path);
        if (($parts[0] ?? '') === 'backoffice' && isset($parts[1]) && $parts[1] !== '') {
            return ucfirst(str_replace(['-', '_'], ' ', $parts[1]));
        }

        return ucfirst(str_replace(['-', '_'], ' ', $parts[0]));
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
