<?php

namespace Modules\Administration\Services;

use Modules\Administration\Models\PermissionModel;

class PermissionService
{
    private PermissionModel $permissions;

    public function __construct(?PermissionModel $permissions = null)
    {
        $this->permissions = $permissions ?? new PermissionModel();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?bool $isActive = null): array
    {
        try {
            $rows = $this->permissions->listFiltered($isActive);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list permissions: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $active = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return [
                'id'          => (int) $row['permission_id'],
                'description' => $row['description_permission'] ?? '',
                'url_route'   => $row['url_route'] ?? '',
                'is_active'   => $active,
                'status'      => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $row = $this->permissions->find($id);

        return $row ?: null;
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

        $data = [
            'description_permission' => trim((string) $input['description_permission']),
            'url_route'              => trim((string) $input['url_route']),
            'is_active'              => true,
        ];

        try {
            $id = $this->permissions->insert($data, true);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create permission: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.perm_err_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.perm_err_save')]];
        }

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input): array
    {
        if (! $this->permissions->find($id)) {
            return ['ok' => false, 'errors' => [lang('Backoffice.perm_err_not_found')]];
        }

        $errors = $this->validate($input, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = [
            'description_permission' => trim((string) $input['description_permission']),
            'url_route'              => trim((string) $input['url_route']),
        ];

        try {
            $ok = $this->permissions->update($id, $data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update permission {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'errors' => [lang('Backoffice.perm_err_save')]];
        }

        if (! $ok) {
            return ['ok' => false, 'errors' => [lang('Backoffice.perm_err_save')]];
        }

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleStatus(int $id): array
    {
        $row = $this->permissions->find($id);
        if (! $row) {
            return ['ok' => false, 'errors' => [lang('Backoffice.perm_err_not_found')]];
        }

        $isActive   = filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $activating = ! $isActive;

        try {
            $this->permissions->update($id, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to toggle permission {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'errors' => [lang('Backoffice.perm_err_save')]];
        }

        return ['ok' => true, 'activated' => $activating];
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validate(array $input, ?int $ignoreId = null): array
    {
        $errors = [];
        $description = trim((string) ($input['description_permission'] ?? ''));
        $route       = trim((string) ($input['url_route'] ?? ''));

        if ($description === '') {
            $errors[] = lang('Backoffice.perm_err_required_description');
        }
        if ($route === '') {
            $errors[] = lang('Backoffice.perm_err_required_route');
        }

        if ($route !== '' && $this->permissions->routeExists($route, $ignoreId)) {
            $errors[] = lang('Backoffice.perm_err_route_taken');
        }

        return $errors;
    }
}
