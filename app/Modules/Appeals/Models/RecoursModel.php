<?php

namespace Modules\Appeals\Models;

use CodeIgniter\Model;

class RecoursModel extends Model
{
    protected $table            = 'recours.recours';
    protected $primaryKey       = 'recours_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'verdict_conteste_id',
        'nouvelle_plainte_id',
        'date_recours',
        'dans_les_delais',
        'enregistre_par',
        'created_at',
        'niveau_juridiction_id',
        'plainte_parent_id',
        'juridiction_id',
    ];

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listFiltered(array $filters = []): array
    {
        $sql = <<<'SQL'
            SELECT
                r.recours_id,
                r.date_recours,
                r.dans_les_delais,
                r.niveau_juridiction_id,
                r.juridiction_id,
                r.plainte_parent_id,
                r.nouvelle_plainte_id,
                r.verdict_conteste_id,
                nj.desc_niveau_juridiction,
                j.nom_juridiction,
                j.province_id,
                j.commune_id,
                np.numero_dossier AS appeal_number,
                np.objet AS appeal_subject,
                pp.numero_dossier AS parent_case_number,
                pp.objet AS parent_subject,
                v.date_verdict,
                v.resume AS verdict_resume,
                v.date_limite_recours,
                tv.description_type_verdict
            FROM recours.recours AS r
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = r.niveau_juridiction_id
            LEFT JOIN juridiction.juridiction AS j
                ON j.juridiction_id = r.juridiction_id
            LEFT JOIN plainte.plainte AS np
                ON np.plainte_id = r.nouvelle_plainte_id
            LEFT JOIN plainte.plainte AS pp
                ON pp.plainte_id = r.plainte_parent_id
            LEFT JOIN verdict.verdict AS v
                ON v.verdict_id = r.verdict_conteste_id
            LEFT JOIN verdict.type_verdict AS tv
                ON tv.type_verdict_id = v.type_verdict_id
            WHERE 1 = 1
        SQL;

        $params = [];
        if (! empty($filters['niveau_juridiction_id'])) {
            $sql .= ' AND r.niveau_juridiction_id = ?';
            $params[] = (int) $filters['niveau_juridiction_id'];
        }
        if (! empty($filters['province_id'])) {
            $sql .= ' AND j.province_id = ?';
            $params[] = (int) $filters['province_id'];
        }
        if (! empty($filters['commune_id'])) {
            $sql .= ' AND j.commune_id = ?';
            $params[] = (int) $filters['commune_id'];
        }
        if (! empty($filters['juridiction_id'])) {
            $sql .= ' AND r.juridiction_id = ?';
            $params[] = (int) $filters['juridiction_id'];
        }
        if (! empty($filters['date_recours'])) {
            $sql .= ' AND r.date_recours::date = ?::date';
            $params[] = $filters['date_recours'];
        }
        if (($filters['dans_les_delais'] ?? '') === '1' || ($filters['dans_les_delais'] ?? '') === 'true') {
            $sql .= ' AND r.dans_les_delais = TRUE';
        } elseif (($filters['dans_les_delais'] ?? '') === '0' || ($filters['dans_les_delais'] ?? '') === 'false') {
            $sql .= ' AND (r.dans_les_delais = FALSE OR r.dans_les_delais IS NULL)';
        }

        $sql .= ' ORDER BY r.date_recours DESC NULLS LAST, r.recours_id DESC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findDetailed(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                r.*,
                nj.desc_niveau_juridiction,
                j.nom_juridiction,
                j.province_id,
                j.commune_id,
                np.numero_dossier AS appeal_number,
                np.objet AS appeal_objet,
                np.description AS appeal_description,
                np.date_depot AS appeal_date_depot,
                np.statut_plainte_id,
                np.etape_plainte_id,
                stpl.description_statut_plainte,
                etape.description_etape_plainte,
                pp.plainte_id AS parent_plainte_id,
                pp.numero_dossier AS parent_case_number,
                pp.objet AS parent_objet,
                pp.description AS parent_description,
                pp.date_depot AS parent_date_depot,
                pp.niveau_juridiction_id AS parent_niveau_id,
                pp.juridiction_id AS parent_juridiction_id,
                njp.desc_niveau_juridiction AS parent_niveau,
                jp.nom_juridiction AS parent_juridiction,
                v.verdict_id,
                v.date_verdict,
                v.resume AS verdict_resume,
                v.dispositif AS verdict_dispositif,
                v.date_limite_recours,
                v.recours_exerce,
                tv.description_type_verdict
            FROM recours.recours AS r
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = r.niveau_juridiction_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = r.juridiction_id
            LEFT JOIN plainte.plainte AS np ON np.plainte_id = r.nouvelle_plainte_id
            LEFT JOIN plainte.statut_plainte AS stpl ON stpl.statut_plainte_id = np.statut_plainte_id
            LEFT JOIN plainte.etape_plainte AS etape ON etape.etape_plainte_id = np.etape_plainte_id
            LEFT JOIN plainte.plainte AS pp ON pp.plainte_id = r.plainte_parent_id
            LEFT JOIN juridiction.niveau_juridiction AS njp ON njp.niveau_juridiction_id = pp.niveau_juridiction_id
            LEFT JOIN juridiction.juridiction AS jp ON jp.juridiction_id = pp.juridiction_id
            LEFT JOIN verdict.verdict AS v ON v.verdict_id = r.verdict_conteste_id
            LEFT JOIN verdict.type_verdict AS tv ON tv.type_verdict_id = v.type_verdict_id
            WHERE r.recours_id = ?
            LIMIT 1
        SQL;

        $row = $this->db->query($sql, [$id])->getRowArray();

        return is_array($row) ? $row : null;
    }

    /**
     * Complaints with a verdict eligible for appeal.
     *
     * @return list<array<string, mixed>>
     */
    public function listEligibleParents(): array
    {
        $sql = <<<'SQL'
            SELECT DISTINCT ON (p.plainte_id)
                p.plainte_id,
                p.numero_dossier,
                p.objet,
                p.niveau_juridiction_id,
                p.juridiction_id,
                j.nom_juridiction,
                j.province_id,
                j.commune_id,
                nj.desc_niveau_juridiction,
                v.verdict_id,
                v.date_verdict,
                v.date_limite_recours,
                v.resume
            FROM plainte.plainte AS p
            JOIN audience.audience_plainte AS ap ON ap.plainte_id = p.plainte_id
            JOIN verdict.verdict AS v ON v.audience_plainte_id = ap.audience_plainte_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = p.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = p.niveau_juridiction_id
            WHERE (v.recours_exerce IS NULL OR v.recours_exerce = FALSE)
              AND v.date_limite_recours IS NOT NULL
              AND NOT EXISTS (
                  SELECT 1 FROM recours.recours r
                  WHERE r.verdict_conteste_id = v.verdict_id
              )
            ORDER BY p.plainte_id, v.date_verdict DESC NULLS LAST, v.verdict_id DESC
        SQL;

        return $this->db->query($sql)->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findEligibleParent(int $plainteId): ?array
    {
        foreach ($this->listEligibleParents() as $row) {
            if ((int) $row['plainte_id'] === $plainteId) {
                return $row;
            }
        }

        // Also allow already-linked parent when editing
        $sql = <<<'SQL'
            SELECT
                p.plainte_id,
                p.numero_dossier,
                p.objet,
                p.niveau_juridiction_id,
                p.juridiction_id,
                j.nom_juridiction,
                j.province_id,
                j.commune_id,
                nj.desc_niveau_juridiction,
                v.verdict_id,
                v.date_verdict,
                v.date_limite_recours,
                v.resume
            FROM plainte.plainte AS p
            JOIN audience.audience_plainte AS ap ON ap.plainte_id = p.plainte_id
            JOIN verdict.verdict AS v ON v.audience_plainte_id = ap.audience_plainte_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = p.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = p.niveau_juridiction_id
            WHERE p.plainte_id = ?
            ORDER BY v.date_verdict DESC NULLS LAST, v.verdict_id DESC
            LIMIT 1
        SQL;

        $row = $this->db->query($sql, [$plainteId])->getRowArray();

        return is_array($row) ? $row : null;
    }
}
