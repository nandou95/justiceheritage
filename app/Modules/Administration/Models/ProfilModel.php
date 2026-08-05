<?php

namespace Modules\Administration\Models;

use CodeIgniter\Model;

class ProfilModel extends Model
{
    protected $table         = 'administration.profil';
    protected $primaryKey    = 'profil_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'code_profil',
        'libelle_profil',
        'description_profil',
        'is_active',
        'created_at',
    ];

    /**
     * @return list<array{id:int|string,label:string}>
     */
    public function options(): array
    {
        $rows = $this->builder()
            ->select('profil_id, libelle_profil')
            ->where('(is_active IS NULL OR is_active = TRUE)', null, false)
            ->orderBy('libelle_profil', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): array => [
            'id'    => $row['profil_id'],
            'label' => $row['libelle_profil'],
        ], $rows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listWithPermissionCounts(?bool $isActive = null): array
    {
        $sql = <<<'SQL'
            SELECT
                pr.profil_id,
                pr.code_profil,
                pr.libelle_profil,
                pr.description_profil,
                pr.is_active,
                pr.created_at,
                COUNT(CASE WHEN pp.is_active = TRUE THEN pp.profil_permission_id END) AS permissions_count
            FROM administration.profil AS pr
            LEFT JOIN administration.profil_permission AS pp
                ON pp.profil_id = pr.profil_id
            WHERE 1 = 1
        SQL;

        $params = [];

        if ($isActive === true) {
            $sql .= ' AND pr.is_active = TRUE';
        } elseif ($isActive === false) {
            $sql .= ' AND (pr.is_active = FALSE OR pr.is_active IS NULL)';
        }

        $sql .= ' GROUP BY pr.profil_id, pr.code_profil, pr.libelle_profil, pr.description_profil, pr.is_active, pr.created_at';
        $sql .= ' ORDER BY pr.libelle_profil ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()->where('LOWER(code_profil) =', mb_strtolower($code), false);
        if ($ignoreId) {
            $builder->where('profil_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }
}
