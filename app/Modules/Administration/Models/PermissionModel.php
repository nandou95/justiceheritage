<?php

namespace Modules\Administration\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table         = 'administration.permission';
    protected $primaryKey    = 'permission_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'description_permission',
        'url_route',
        'is_active',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listFiltered(?bool $isActive = null, ?string $search = null): array
    {
        $sql = <<<'SQL'
            SELECT permission_id, description_permission, url_route, is_active
            FROM administration.permission
            WHERE 1 = 1
        SQL;

        $params = [];

        if ($isActive === true) {
            $sql .= ' AND is_active = TRUE';
        } elseif ($isActive === false) {
            $sql .= ' AND is_active = FALSE';
        }

        $search = $search !== null ? trim($search) : '';
        if ($search !== '') {
            $sql .= ' AND (
                description_permission ILIKE ?
                OR url_route ILIKE ?
            )';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY description_permission ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function routeExists(string $route, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM administration.permission WHERE LOWER(url_route) = LOWER(?)';
        $params = [$route];

        if ($ignoreId) {
            $sql .= ' AND permission_id != ?';
            $params[] = $ignoreId;
        }

        $sql .= ' LIMIT 1';

        return $this->db->query($sql, $params)->getFirstRow() !== null;
    }
}
