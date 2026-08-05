<?php

namespace Modules\Verdicts\Models;

use CodeIgniter\Model;

class VerdictModel extends Model
{
    protected $table            = 'verdict.verdict';
    protected $primaryKey       = 'verdict_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'audience_plainte_id',
        'niveau_juridiction_id',
        'type_verdict_id',
        'date_verdict',
        'resume',
        'dispositif',
        'date_limite_recours',
        'recours_exerce',
        'created_at',
        'juridiction_id',
        'upload_rapport_verdict',
    ];

    /** Default appeal window in days (judicial rule used across the app). */
    public const APPEAL_DEADLINE_DAYS = 15;

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listFiltered(array $filters = []): array
    {
        $sql = <<<'SQL'
            SELECT
                v.verdict_id,
                v.date_verdict,
                v.type_verdict_id,
                v.niveau_juridiction_id,
                v.juridiction_id,
                v.audience_plainte_id,
                tv.description_type_verdict,
                p.numero_dossier,
                p.objet,
                nj.desc_niveau_juridiction,
                j.nom_juridiction,
                j.province_id,
                j.commune_id,
                sa.description_statut_audience,
                a.statut_audience_id
            FROM verdict.verdict AS v
            LEFT JOIN verdict.type_verdict AS tv ON tv.type_verdict_id = v.type_verdict_id
            JOIN audience.audience_plainte AS ap ON ap.audience_plainte_id = v.audience_plainte_id
            JOIN plainte.plainte AS p ON p.plainte_id = ap.plainte_id
            LEFT JOIN audience.audience AS a ON a.audience_id = ap.audience_id
            LEFT JOIN audience.statut_audience AS sa
                ON sa.statut_audience_id = COALESCE(ap.statut_audience_id, a.statut_audience_id)
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = v.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = v.niveau_juridiction_id
            WHERE 1 = 1
        SQL;

        $params = [];
        if (! empty($filters['niveau_juridiction_id'])) {
            $sql .= ' AND v.niveau_juridiction_id = ?';
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
            $sql .= ' AND v.juridiction_id = ?';
            $params[] = (int) $filters['juridiction_id'];
        }
        if (! empty($filters['date_verdict'])) {
            $sql .= ' AND v.date_verdict::date = ?::date';
            $params[] = $filters['date_verdict'];
        }
        if (! empty($filters['type_verdict_id'])) {
            $sql .= ' AND v.type_verdict_id = ?';
            $params[] = (int) $filters['type_verdict_id'];
        }
        if (! empty($filters['statut_audience_id'])) {
            $sql .= ' AND COALESCE(ap.statut_audience_id, a.statut_audience_id) = ?';
            $params[] = (int) $filters['statut_audience_id'];
        }

        $sql .= ' ORDER BY v.date_verdict DESC NULLS LAST, v.verdict_id DESC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findDetailed(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                v.*,
                tv.description_type_verdict,
                p.plainte_id,
                p.numero_dossier,
                p.objet,
                p.description AS plainte_description,
                p.date_depot,
                p.etape_plainte_id,
                p.statut_plainte_id,
                etape.description_etape_plainte,
                stpl.description_statut_plainte,
                nj.desc_niveau_juridiction,
                j.nom_juridiction,
                j.province_id,
                j.commune_id,
                ap.audience_id,
                ap.rapport AS audience_plainte_rapport,
                a.date_audience,
                a.heure_audience,
                a.lieu_audience,
                a.date_tenue,
                a.rapport AS audience_rapport,
                sa.description_statut_audience
            FROM verdict.verdict AS v
            LEFT JOIN verdict.type_verdict AS tv ON tv.type_verdict_id = v.type_verdict_id
            JOIN audience.audience_plainte AS ap ON ap.audience_plainte_id = v.audience_plainte_id
            JOIN plainte.plainte AS p ON p.plainte_id = ap.plainte_id
            LEFT JOIN audience.audience AS a ON a.audience_id = ap.audience_id
            LEFT JOIN audience.statut_audience AS sa
                ON sa.statut_audience_id = COALESCE(ap.statut_audience_id, a.statut_audience_id)
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = v.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = v.niveau_juridiction_id
            LEFT JOIN plainte.etape_plainte AS etape ON etape.etape_plainte_id = p.etape_plainte_id
            LEFT JOIN plainte.statut_plainte AS stpl ON stpl.statut_plainte_id = p.statut_plainte_id
            WHERE v.verdict_id = ?
            LIMIT 1
        SQL;

        $row = $this->db->query($sql, [$id])->getRowArray();

        return is_array($row) ? $row : null;
    }

    /**
     * Heard complaints eligible for a new verdict at their jurisdiction level.
     *
     * @return list<array<string, mixed>>
     */
    public function listEligibleAudiencePlaintes(?int $juridictionId = null, ?int $niveauId = null): array
    {
        $sql = <<<'SQL'
            SELECT
                ap.audience_plainte_id,
                ap.audience_id,
                ap.plainte_id,
                ap.rapport,
                p.numero_dossier,
                p.objet,
                p.juridiction_id,
                p.niveau_juridiction_id,
                a.date_audience,
                a.date_tenue,
                a.juridiction_audience_id,
                j.nom_juridiction,
                nj.desc_niveau_juridiction
            FROM audience.audience_plainte AS ap
            JOIN audience.audience AS a ON a.audience_id = ap.audience_id
            JOIN plainte.plainte AS p ON p.plainte_id = ap.plainte_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = p.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = p.niveau_juridiction_id
            WHERE a.date_tenue IS NOT NULL
              AND (
                    (ap.rapport IS NOT NULL AND BTRIM(ap.rapport) <> '')
                 OR (a.rapport IS NOT NULL AND BTRIM(a.rapport) <> '')
              )
              AND NOT EXISTS (
                  SELECT 1 FROM verdict.verdict v
                  WHERE v.audience_plainte_id = ap.audience_plainte_id
              )
              AND NOT EXISTS (
                  SELECT 1
                  FROM verdict.verdict v2
                  JOIN audience.audience_plainte ap2 ON ap2.audience_plainte_id = v2.audience_plainte_id
                  WHERE ap2.plainte_id = p.plainte_id
                    AND v2.niveau_juridiction_id = p.niveau_juridiction_id
              )
        SQL;

        $params = [];
        if ($juridictionId) {
            $sql .= ' AND p.juridiction_id = ?';
            $params[] = $juridictionId;
        }
        if ($niveauId) {
            $sql .= ' AND p.niveau_juridiction_id = ?';
            $params[] = $niveauId;
        }

        $sql .= ' ORDER BY a.date_audience DESC NULLS LAST, p.numero_dossier ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function existsForLevel(int $plainteId, int $niveauId, ?int $ignoreVerdictId = null): bool
    {
        $sql = <<<'SQL'
            SELECT v.verdict_id
            FROM verdict.verdict AS v
            JOIN audience.audience_plainte AS ap ON ap.audience_plainte_id = v.audience_plainte_id
            WHERE ap.plainte_id = ?
              AND v.niveau_juridiction_id = ?
        SQL;
        $params = [$plainteId, $niveauId];
        if ($ignoreVerdictId) {
            $sql .= ' AND v.verdict_id <> ?';
            $params[] = $ignoreVerdictId;
        }
        $sql .= ' LIMIT 1';

        return (bool) $this->db->query($sql, $params)->getRowArray();
    }
}
