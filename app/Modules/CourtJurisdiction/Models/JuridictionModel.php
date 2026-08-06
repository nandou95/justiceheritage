<?php

namespace Modules\CourtJurisdiction\Models;

use CodeIgniter\Model;

class JuridictionModel extends Model
{
    protected $table         = 'juridiction.juridiction';
    protected $primaryKey    = 'juridiction_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'code_juridiction',
        'nom_juridiction',
        'niveau_juridiction_id',
        'adresse',
        'telephone',
        'email',
        'province_id',
        'commune_id',
        'zone_id',
        'colline_id',
        'is_active',
        'created_at',
        'est_dernier',
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
                j.juridiction_id,
                j.code_juridiction,
                j.nom_juridiction,
                j.niveau_juridiction_id,
                j.adresse,
                j.telephone,
                j.email,
                j.province_id,
                j.commune_id,
                j.zone_id,
                j.colline_id,
                j.is_active,
                j.created_at,
                j.est_dernier,
                nj.desc_niveau_juridiction,
                lp.province_name,
                lc.commune_name,
                lz.zone_name,
                lcol.colline_name
            FROM juridiction.juridiction AS j
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = j.niveau_juridiction_id
            LEFT JOIN localite.localite_province AS lp
                ON lp.province_id = j.province_id
            LEFT JOIN localite.localite_commune AS lc
                ON lc.commune_id = j.commune_id
            LEFT JOIN localite.localite_zone AS lz
                ON lz.zone_id = j.zone_id
            LEFT JOIN localite.localite_colline AS lcol
                ON lcol.colline_id = j.colline_id
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
                ? ' AND j.is_active = TRUE'
                : ' AND j.is_active = FALSE';
        }

        $sql .= ' ORDER BY j.nom_juridiction ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findWithRelations(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                j.juridiction_id,
                j.code_juridiction,
                j.nom_juridiction,
                j.niveau_juridiction_id,
                j.adresse,
                j.telephone,
                j.email,
                j.province_id,
                j.commune_id,
                j.zone_id,
                j.colline_id,
                j.is_active,
                j.created_at,
                j.est_dernier,
                nj.desc_niveau_juridiction,
                lp.province_name,
                lc.commune_name,
                lz.zone_name,
                lcol.colline_name
            FROM juridiction.juridiction AS j
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = j.niveau_juridiction_id
            LEFT JOIN localite.localite_province AS lp
                ON lp.province_id = j.province_id
            LEFT JOIN localite.localite_commune AS lc
                ON lc.commune_id = j.commune_id
            LEFT JOIN localite.localite_zone AS lz
                ON lz.zone_id = j.zone_id
            LEFT JOIN localite.localite_colline AS lcol
                ON lcol.colline_id = j.colline_id
            WHERE j.juridiction_id = ?
            LIMIT 1
        SQL;

        $row = $this->db->query($sql, [$id])->getRowArray();

        return $row ?: null;
    }

    /**
     * @param array{
     *   niveau_juridiction_id?: int|null,
     *   province_id?: int|null,
     *   commune_id?: int|null,
     *   active_only?: bool
     * } $filters
     * @return list<array{id:int|string,label:string,niveau_juridiction_id?:mixed,province_id?:mixed,commune_id?:mixed}>
     */
    public function options(array $filters = []): array
    {
        $builder = $this->builder()
            ->select('juridiction_id, code_juridiction, nom_juridiction, niveau_juridiction_id, province_id, commune_id')
            ->orderBy('nom_juridiction', 'ASC');

        if (($filters['active_only'] ?? true) === true) {
            $builder->where('(is_active IS NULL OR is_active = TRUE)', null, false);
        }
        if (! empty($filters['niveau_juridiction_id'])) {
            $builder->where('niveau_juridiction_id', (int) $filters['niveau_juridiction_id']);
        }
        if (! empty($filters['province_id'])) {
            $builder->where('province_id', (int) $filters['province_id']);
        }
        if (! empty($filters['commune_id'])) {
            $builder->where('commune_id', (int) $filters['commune_id']);
        }

        $rows = $builder->get()->getResultArray();

        return array_map(static fn (array $row): array => [
            'id'                    => $row['juridiction_id'],
            'label'                 => trim(($row['code_juridiction'] ?? '') . ' — ' . ($row['nom_juridiction'] ?? ''), ' —'),
            'niveau_juridiction_id' => $row['niveau_juridiction_id'],
            'province_id'           => $row['province_id'],
            'commune_id'            => $row['commune_id'],
        ], $rows);
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM juridiction.juridiction WHERE LOWER(code_juridiction) = LOWER(?)';
        $params = [$code];
        if ($ignoreId) {
            $sql .= ' AND juridiction_id != ?';
            $params[] = $ignoreId;
        }
        $sql .= ' LIMIT 1';

        return $this->db->query($sql, $params)->getFirstRow() !== null;
    }
}
