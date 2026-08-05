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
    public function listFiltered(?bool $isActive = null): array
    {
        $builder = $this->builder()
            ->select('permission_id, description_permission, url_route, is_active')
            ->orderBy('description_permission', 'ASC');

        if ($isActive === true) {
            $builder->where('is_active', true);
        } elseif ($isActive === false) {
            $builder->groupStart()
                ->where('is_active', false)
                ->orWhere('is_active', null)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function routeExists(string $route, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()->where('LOWER(url_route) =', mb_strtolower($route), false);
        if ($ignoreId) {
            $builder->where('permission_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }
}
