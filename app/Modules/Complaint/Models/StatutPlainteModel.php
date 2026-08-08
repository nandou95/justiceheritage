<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class StatutPlainteModel extends Model
{
    protected $table            = 'plainte.statut_plainte';
    protected $primaryKey       = 'statut_plainte_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'description_statut_plainte',
        'is_active',
        'niveau_juridiction_id',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listFiltered(?int $niveauId = null, ?bool $isActive = null): array
    {
        $sql = <<<'SQL'
            SELECT
                s.statut_plainte_id,
                s.description_statut_plainte,
                s.is_active,
                s.niveau_juridiction_id,
                nj.desc_niveau_juridiction
            FROM plainte.statut_plainte AS s
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = s.niveau_juridiction_id
            WHERE 1 = 1
        SQL;

        $params = [];
        if ($niveauId !== null && $niveauId > 0) {
            $sql .= ' AND s.niveau_juridiction_id = ?';
            $params[] = $niveauId;
        }
        if ($isActive === true) {
            $sql .= ' AND s.is_active = TRUE';
        } elseif ($isActive === false) {
            $sql .= ' AND (s.is_active = FALSE OR s.is_active IS NULL)';
        }

        $sql .= ' ORDER BY s.niveau_juridiction_id ASC NULLS LAST, s.description_statut_plainte ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * @return list<array{id:int|string,label:string,niveau_juridiction_id:int|string|null}>
     */
    public function options(?int $niveauId = null, bool $activeOnly = true): array
    {
        $builder = $this->builder()
            ->select('statut_plainte_id, description_statut_plainte, niveau_juridiction_id')
            ->orderBy('description_statut_plainte', 'ASC');

        if ($activeOnly) {
            $builder->where('is_active', true);
        }
        if ($niveauId !== null && $niveauId > 0) {
            $builder->where('niveau_juridiction_id', $niveauId);
        }

        return array_map(static fn (array $row): array => [
            'id'                    => $row['statut_plainte_id'],
            'label'                 => $row['description_statut_plainte'],
            'niveau_juridiction_id' => $row['niveau_juridiction_id'],
        ], $builder->get()->getResultArray());
    }

    public function findDefaultId(?int $niveauId = null): ?int
    {
        $builder = $this->builder()
            ->select('statut_plainte_id')
            ->where('is_active', true)
            ->orderBy('statut_plainte_id', 'ASC');

        if ($niveauId !== null && $niveauId > 0) {
            $builder->where('niveau_juridiction_id', $niveauId);
        }

        $row = $builder->get()->getRowArray();

        return $row ? (int) $row['statut_plainte_id'] : null;
    }

    public function descriptionExists(string $description, int $niveauId, ?int $ignoreId = null): bool
    {
        $sql = <<<'SQL'
            SELECT 1
            FROM plainte.statut_plainte
            WHERE LOWER(TRIM(description_statut_plainte)) = LOWER(TRIM(?))
              AND niveau_juridiction_id = ?
        SQL;
        $params = [$description, $niveauId];

        if ($ignoreId) {
            $sql .= ' AND statut_plainte_id != ?';
            $params[] = $ignoreId;
        }

        $sql .= ' LIMIT 1';

        return $this->db->query($sql, $params)->getFirstRow() !== null;
    }
}
