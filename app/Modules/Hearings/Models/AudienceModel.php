<?php

namespace Modules\Hearings\Models;

use CodeIgniter\Model;

class AudienceModel extends Model
{
    protected $table            = 'audience.audience';
    protected $primaryKey       = 'audience_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'niveau_juridiction_id',
        'date_audience',
        'heure_audience',
        'juridiction_audience_id',
        'province_audience_id',
        'commune_audience_id',
        'zone_audience_id',
        'colline_audience_id',
        'lieu_audience',
        'date_tenue',
        'heure_debut',
        'heure_fin',
        'statut_audience_id',
        'motif_report',
        'rapport',
        'rapport_valide',
        'created_at',
        'updated_at',
    ];

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listFiltered(array $filters = []): array
    {
        $sql = <<<'SQL'
            SELECT
                a.audience_id,
                a.date_audience,
                a.heure_audience,
                a.heure_debut,
                a.heure_fin,
                a.lieu_audience,
                a.statut_audience_id,
                a.niveau_juridiction_id,
                a.juridiction_audience_id,
                a.province_audience_id,
                a.commune_audience_id,
                sa.description_statut_audience,
                nj.desc_niveau_juridiction,
                j.nom_juridiction,
                (
                    SELECT COUNT(*) FROM audience.audience_plainte ap
                    WHERE ap.audience_id = a.audience_id
                ) AS complaints_count
            FROM audience.audience AS a
            LEFT JOIN audience.statut_audience AS sa ON sa.statut_audience_id = a.statut_audience_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = a.niveau_juridiction_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = a.juridiction_audience_id
            WHERE 1 = 1
        SQL;

        $params = [];
        if (! empty($filters['niveau_juridiction_id'])) {
            $sql .= ' AND a.niveau_juridiction_id = ?';
            $params[] = (int) $filters['niveau_juridiction_id'];
        }
        if (! empty($filters['province_id'])) {
            $sql .= ' AND a.province_audience_id = ?';
            $params[] = (int) $filters['province_id'];
        }
        if (! empty($filters['commune_id'])) {
            $sql .= ' AND a.commune_audience_id = ?';
            $params[] = (int) $filters['commune_id'];
        }
        if (! empty($filters['juridiction_id'])) {
            $sql .= ' AND a.juridiction_audience_id = ?';
            $params[] = (int) $filters['juridiction_id'];
        }
        if (! empty($filters['date_audience'])) {
            $sql .= ' AND a.date_audience::date = ?::date';
            $params[] = $filters['date_audience'];
        }
        if (! empty($filters['statut_audience_id'])) {
            $sql .= ' AND a.statut_audience_id = ?';
            $params[] = (int) $filters['statut_audience_id'];
        }

        $sql .= ' ORDER BY a.date_audience DESC NULLS LAST, a.heure_audience DESC NULLS LAST, a.audience_id DESC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findDetailed(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                a.*,
                sa.description_statut_audience,
                nj.desc_niveau_juridiction,
                j.nom_juridiction,
                lp.province_name,
                lc.commune_name,
                lz.zone_name,
                lcol.colline_name
            FROM audience.audience AS a
            LEFT JOIN audience.statut_audience AS sa ON sa.statut_audience_id = a.statut_audience_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = a.niveau_juridiction_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = a.juridiction_audience_id
            LEFT JOIN localite.localite_province AS lp ON lp.province_id = a.province_audience_id
            LEFT JOIN localite.localite_commune AS lc ON lc.commune_id = a.commune_audience_id
            LEFT JOIN localite.localite_zone AS lz ON lz.zone_id = a.zone_audience_id
            LEFT JOIN localite.localite_colline AS lcol ON lcol.colline_id = a.colline_audience_id
            WHERE a.audience_id = ?
            LIMIT 1
        SQL;

        $row = $this->db->query($sql, [$id])->getRowArray();

        return is_array($row) ? $row : null;
    }

    /**
     * Complaints with an active summons not already linked to a hearing for that summons.
     *
     * @return list<array<string, mixed>>
     */
    public function listEligibleComplaints(?int $juridictionId = null): array
    {
        $sql = <<<'SQL'
            SELECT DISTINCT ON (p.plainte_id)
                p.plainte_id,
                p.numero_dossier,
                p.objet,
                p.juridiction_id,
                p.niveau_juridiction_id,
                p.date_depot,
                j.nom_juridiction,
                nj.desc_niveau_juridiction,
                c.convocation_id,
                c.date_audience AS summons_date,
                c.juridiction_lieu_audience_id
            FROM plainte.plainte AS p
            JOIN convocation.convocation AS c ON c.plainte_id = p.plainte_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = p.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = p.niveau_juridiction_id
            WHERE NOT EXISTS (
                SELECT 1
                FROM audience.audience_plainte ap
                WHERE ap.plainte_id = p.plainte_id
                  AND ap.convocation_id = c.convocation_id
            )
        SQL;

        $params = [];
        if ($juridictionId) {
            $sql .= ' AND p.juridiction_id = ?';
            $params[] = $juridictionId;
        }

        $sql .= ' ORDER BY p.plainte_id, c.date_audience DESC NULLS LAST, c.convocation_id DESC';

        return $this->db->query($sql, $params)->getResultArray();
    }
}
