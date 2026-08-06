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
     * Active profiles for select lists.
     * Optionally include specific IDs (e.g. already assigned inactive profiles on edit).
     *
     * @param list<int> $alsoIncludeIds
     * @return list<array{id:int|string,label:string}>
     */
    public function options(array $alsoIncludeIds = []): array
    {
        $alsoIncludeIds = array_values(array_unique(array_filter(
            array_map('intval', $alsoIncludeIds),
            static fn (int $id): bool => $id > 0
        )));

        $builder = $this->builder()
            ->select('profil_id, libelle_profil, is_active')
            ->orderBy('libelle_profil', 'ASC');

        if ($alsoIncludeIds === []) {
            $builder->where('(is_active IS NULL OR is_active = TRUE)', null, false);
        } else {
            $ids = implode(',', $alsoIncludeIds);
            $builder->where(
                "(is_active IS NULL OR is_active = TRUE OR profil_id IN ({$ids}))",
                null,
                false
            );
        }

        $rows = $builder->get()->getResultArray();

        return array_map(static fn (array $row): array => [
            'id'    => $row['profil_id'],
            'label' => $row['libelle_profil'],
        ], $rows);
    }

    public function isActiveProfile(int $profilId): bool
    {
        if ($profilId < 1) {
            return false;
        }

        $row = $this->find($profilId);
        if (! $row) {
            return false;
        }

        return db_bool($row['is_active'] ?? false);
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
            $sql .= ' AND pr.is_active = FALSE';
        }

        $sql .= ' GROUP BY pr.profil_id, pr.code_profil, pr.libelle_profil, pr.description_profil, pr.is_active, pr.created_at';
        $sql .= ' ORDER BY pr.libelle_profil ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM administration.profil WHERE LOWER(code_profil) = LOWER(?)';
        $params = [$code];
        if ($ignoreId) {
            $sql .= ' AND profil_id != ?';
            $params[] = $ignoreId;
        }
        $sql .= ' LIMIT 1';

        return $this->db->query($sql, $params)->getFirstRow() !== null;
    }
}
