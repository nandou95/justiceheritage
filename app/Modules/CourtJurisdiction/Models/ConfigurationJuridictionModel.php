<?php

namespace Modules\CourtJurisdiction\Models;

use CodeIgniter\Model;

class ConfigurationJuridictionModel extends Model
{
    protected $table         = 'juridiction.configuration_juridiction';
    protected $primaryKey    = 'configuration_juridiction_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'juridiction_id',
        'juridiction_parent_id',
        'is_active',
    ];

    /**
     * @param array{
     *   province_id?: int|null,
     *   commune_id?: int|null,
     *   niveau_juridiction_id?: int|null,
     *   is_active?: bool|null
     * } $filters
     * @return list<array<string, mixed>>
     */
    public function listWithRelations(array $filters = []): array
    {
        $sql = <<<'SQL'
            SELECT
                cj.configuration_juridiction_id,
                cj.juridiction_id,
                cj.juridiction_parent_id,
                cj.is_active,
                j.code_juridiction,
                j.nom_juridiction,
                j.province_id,
                j.commune_id,
                j.niveau_juridiction_id,
                nj.desc_niveau_juridiction,
                jp.code_juridiction AS parent_code_juridiction,
                jp.nom_juridiction AS parent_nom_juridiction,
                jp.province_id AS parent_province_id,
                jp.commune_id AS parent_commune_id,
                jp.niveau_juridiction_id AS parent_niveau_juridiction_id,
                njp.desc_niveau_juridiction AS parent_desc_niveau
            FROM juridiction.configuration_juridiction AS cj
            INNER JOIN juridiction.juridiction AS j
                ON j.juridiction_id = cj.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = j.niveau_juridiction_id
            LEFT JOIN juridiction.juridiction AS jp
                ON jp.juridiction_id = cj.juridiction_parent_id
            LEFT JOIN juridiction.niveau_juridiction AS njp
                ON njp.niveau_juridiction_id = jp.niveau_juridiction_id
            WHERE 1 = 1
        SQL;

        $params = [];

        if (! empty($filters['province_id'])) {
            $sql .= ' AND j.province_id = ?';
            $params[] = (int) $filters['province_id'];
        }
        if (! empty($filters['commune_id'])) {
            $sql .= ' AND j.commune_id = ?';
            $params[] = (int) $filters['commune_id'];
        }
        if (! empty($filters['niveau_juridiction_id'])) {
            $sql .= ' AND j.niveau_juridiction_id = ?';
            $params[] = (int) $filters['niveau_juridiction_id'];
        }
        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $sql .= $filters['is_active'] === true
                ? ' AND cj.is_active = TRUE'
                : ' AND cj.is_active = FALSE';
        }

        $sql .= ' ORDER BY j.nom_juridiction ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function relationshipExists(int $juridictionId, int $parentId, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()
            ->where('juridiction_id', $juridictionId)
            ->where('juridiction_parent_id', $parentId);

        if ($ignoreId) {
            $builder->where('configuration_juridiction_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }
}
