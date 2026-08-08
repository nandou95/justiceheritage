<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class EtapePlainteModel extends Model
{
    protected $table            = 'plainte.etape_plainte';
    protected $primaryKey       = 'etape_plainte_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'description_etape_plainte',
        'niveau_juridiction_id',
        'is_active',
        'is_convocation',
        'is_audience',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listWithCounts(?bool $isActive = null, ?int $niveauId = null): array
    {
        $sql = <<<'SQL'
            SELECT
                e.etape_plainte_id,
                e.description_etape_plainte,
                e.niveau_juridiction_id,
                e.is_active,
                e.is_convocation,
                e.is_audience,
                nj.desc_niveau_juridiction,
                COUNT(DISTINCT epp.etape_plainte_profil_id) FILTER (WHERE epp.is_active IS DISTINCT FROM FALSE) AS profiles_count,
                COUNT(DISTINCT epa.etape_plainte_action_id) AS actions_count
            FROM plainte.etape_plainte AS e
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = e.niveau_juridiction_id
            LEFT JOIN plainte.etape_plainte_profil AS epp
                ON epp.etape_plainte_id = e.etape_plainte_id
            LEFT JOIN plainte.etape_plainte_action AS epa
                ON epa.etape_plainte_id = e.etape_plainte_id
            WHERE 1 = 1
        SQL;

        $params = [];
        if ($niveauId !== null && $niveauId > 0) {
            $sql .= ' AND e.niveau_juridiction_id = ?';
            $params[] = $niveauId;
        }
        if ($isActive === true) {
            $sql .= ' AND e.is_active = TRUE';
        } elseif ($isActive === false) {
            $sql .= ' AND (e.is_active = FALSE OR e.is_active IS NULL)';
        }

        $sql .= ' GROUP BY e.etape_plainte_id, e.description_etape_plainte, e.niveau_juridiction_id, e.is_active, e.is_convocation, e.is_audience, nj.desc_niveau_juridiction';
        $sql .= ' ORDER BY e.niveau_juridiction_id ASC NULLS LAST, e.description_etape_plainte ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * @return list<array{id:int|string,label:string,niveau_juridiction_id:int|string|null}>
     */
    public function options(?int $niveauId = null, bool $activeOnly = true): array
    {
        $builder = $this->builder()
            ->select('etape_plainte_id, description_etape_plainte, niveau_juridiction_id')
            ->orderBy('description_etape_plainte', 'ASC');

        if ($activeOnly) {
            $builder->where('is_active', true);
        }
        if ($niveauId) {
            $builder->where('niveau_juridiction_id', $niveauId);
        }

        return array_map(static fn (array $row): array => [
            'id'                    => $row['etape_plainte_id'],
            'label'                 => $row['description_etape_plainte'],
            'niveau_juridiction_id' => $row['niveau_juridiction_id'],
        ], $builder->get()->getResultArray());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listProfiles(int $etapeId): array
    {
        $sql = <<<'SQL'
            SELECT
                pr.profil_id,
                pr.code_profil,
                pr.libelle_profil,
                pr.description_profil,
                pr.is_active
            FROM plainte.etape_plainte_profil AS epp
            JOIN administration.profil AS pr
                ON pr.profil_id = epp.profil_id
            WHERE epp.etape_plainte_id = ?
              AND (epp.is_active IS NULL OR epp.is_active = TRUE)
            ORDER BY pr.libelle_profil ASC
        SQL;

        return $this->db->query($sql, [$etapeId])->getResultArray();
    }

    /**
     * @return list<int>
     */
    public function profileIds(int $etapeId): array
    {
        $rows = $this->db->table('plainte.etape_plainte_profil')
            ->select('profil_id')
            ->where('etape_plainte_id', $etapeId)
            ->where('(is_active IS NULL OR is_active = TRUE)', null, false)
            ->get()
            ->getResultArray();

        return array_map(static fn (array $r): int => (int) $r['profil_id'], $rows);
    }
}
