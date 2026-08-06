<?php

namespace Modules\Administration\Models;

use CodeIgniter\Model;

class ProfilPermissionModel extends Model
{
    protected $table         = 'administration.profil_permission';
    protected $primaryKey    = 'profil_permission_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'profil_id',
        'permission_id',
        'is_active',
    ];

    /**
     * @return list<int>
     */
    public function activePermissionIds(int $profilId): array
    {
        $rows = $this->builder()
            ->select('permission_id')
            ->where('profil_id', $profilId)
            ->where('is_active', true)
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): int => (int) $row['permission_id'], $rows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listAssignedPermissions(int $profilId): array
    {
        $sql = <<<'SQL'
            SELECT
                p.permission_id,
                p.description_permission,
                p.url_route,
                p.is_active AS permission_is_active,
                pp.is_active AS assignment_is_active
            FROM administration.profil_permission AS pp
            INNER JOIN administration.permission AS p
                ON p.permission_id = pp.permission_id
            WHERE pp.profil_id = ?
              AND pp.is_active = TRUE
              AND p.is_active = TRUE
            ORDER BY p.description_permission ASC
        SQL;

        return $this->db->query($sql, [$profilId])->getResultArray();
    }

    /**
     * Sync assigned permissions for a profile.
     *
     * @param list<int> $permissionIds
     */
    public function syncPermissions(int $profilId, array $permissionIds): void
    {
        $permissionIds = array_values(array_unique(array_filter(array_map('intval', $permissionIds))));

        // Only enabled catalogue permissions may be assigned.
        if ($permissionIds !== []) {
            $enabledRows = $this->db->table('administration.permission')
                ->select('permission_id')
                ->whereIn('permission_id', $permissionIds)
                ->where('is_active', true)
                ->get()
                ->getResultArray();
            $permissionIds = array_map(
                static fn (array $row): int => (int) $row['permission_id'],
                $enabledRows
            );
        }

        $existing = $this->builder()
            ->select('profil_permission_id, permission_id, is_active')
            ->where('profil_id', $profilId)
            ->get()
            ->getResultArray();

        $byPermission = [];
        foreach ($existing as $row) {
            $byPermission[(int) $row['permission_id']] = $row;
        }

        $selected = array_fill_keys($permissionIds, true);

        foreach ($selected as $permissionId => $_true) {
            if (isset($byPermission[$permissionId])) {
                if (! db_bool($byPermission[$permissionId]['is_active'] ?? false)) {
                    $this->update((int) $byPermission[$permissionId]['profil_permission_id'], ['is_active' => true]);
                }
                unset($byPermission[$permissionId]);
                continue;
            }

            $this->insert([
                'profil_id'     => $profilId,
                'permission_id' => $permissionId,
                'is_active'     => true,
            ]);
        }

        foreach ($byPermission as $row) {
            if (db_bool($row['is_active'] ?? false)) {
                $this->update((int) $row['profil_permission_id'], ['is_active' => false]);
            }
        }
    }
}
