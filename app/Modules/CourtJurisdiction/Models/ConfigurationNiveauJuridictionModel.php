<?php

namespace Modules\CourtJurisdiction\Models;

use CodeIgniter\Model;

class ConfigurationNiveauJuridictionModel extends Model
{
    protected $table         = 'juridiction.configuration_niveau_juridiction';
    protected $primaryKey    = 'configuration_niveau_juridiction_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'niveau_juridiction_id',
        'niveau_juridiction_parent_id',
        'is_active',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listWithRelations(?bool $isActive = null): array
    {
        $sql = <<<'SQL'
            SELECT
                cn.configuration_niveau_juridiction_id,
                cn.niveau_juridiction_id,
                cn.niveau_juridiction_parent_id,
                cn.is_active,
                n.desc_niveau_juridiction,
                np.desc_niveau_juridiction AS parent_desc_niveau
            FROM juridiction.configuration_niveau_juridiction AS cn
            LEFT JOIN juridiction.niveau_juridiction AS n
                ON n.niveau_juridiction_id = cn.niveau_juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS np
                ON np.niveau_juridiction_id = cn.niveau_juridiction_parent_id
            WHERE 1 = 1
        SQL;

        $params = [];

        if ($isActive === true) {
            $sql .= ' AND cn.is_active = TRUE';
        } elseif ($isActive === false) {
            $sql .= ' AND (cn.is_active = FALSE OR cn.is_active IS NULL)';
        }

        $sql .= ' ORDER BY cn.niveau_juridiction_id ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function parentLevelId(int $niveauId): ?int
    {
        $row = $this->builder()
            ->select('niveau_juridiction_parent_id')
            ->where('niveau_juridiction_id', $niveauId)
            ->where('(is_active IS NULL OR is_active = TRUE)', null, false)
            ->orderBy('configuration_niveau_juridiction_id', 'ASC')
            ->get(1)
            ->getRowArray();

        if (! $row || empty($row['niveau_juridiction_parent_id'])) {
            return null;
        }

        return (int) $row['niveau_juridiction_parent_id'];
    }

    public function relationshipExists(int $niveauId, int $parentId, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()
            ->where('niveau_juridiction_id', $niveauId)
            ->where('niveau_juridiction_parent_id', $parentId);

        if ($ignoreId) {
            $builder->where('configuration_niveau_juridiction_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }
}
