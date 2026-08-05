<?php

namespace Modules\Summons\Models;

use CodeIgniter\Model;

class ConvocationModel extends Model
{
    protected $table            = 'convocation.convocation';
    protected $primaryKey       = 'convocation_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'plainte_id',
        'niveau_juridiction_id',
        'date_audience',
        'heure_audience',
        'province_lieu_audience_id',
        'commune_lieu_audience_id',
        'zone_lieu_audience_id',
        'colline_lieu_audience_id',
        'lieu_audience',
        'emise_le',
        'emise_par',
        'statut_convocation_id',
        'observations',
        'created_at',
        'juridiction_lieu_audience_id',
    ];

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listFiltered(array $filters = []): array
    {
        $sql = <<<'SQL'
            SELECT
                c.convocation_id,
                c.plainte_id,
                c.date_audience,
                c.heure_audience,
                c.lieu_audience,
                c.emise_le,
                c.statut_convocation_id,
                c.niveau_juridiction_id,
                c.juridiction_lieu_audience_id,
                sc.description_statut_convocation,
                p.numero_dossier,
                p.objet,
                nj.desc_niveau_juridiction,
                j.nom_juridiction,
                lp.province_name,
                lc.commune_name,
                lz.zone_name,
                lcol.colline_name
            FROM convocation.convocation AS c
            LEFT JOIN convocation.statut_convocation AS sc
                ON sc.statut_convocation_id = c.statut_convocation_id
            LEFT JOIN plainte.plainte AS p ON p.plainte_id = c.plainte_id
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = c.niveau_juridiction_id
            LEFT JOIN juridiction.juridiction AS j
                ON j.juridiction_id = c.juridiction_lieu_audience_id
            LEFT JOIN localite.localite_province AS lp ON lp.province_id = c.province_lieu_audience_id
            LEFT JOIN localite.localite_commune AS lc ON lc.commune_id = c.commune_lieu_audience_id
            LEFT JOIN localite.localite_zone AS lz ON lz.zone_id = c.zone_lieu_audience_id
            LEFT JOIN localite.localite_colline AS lcol ON lcol.colline_id = c.colline_lieu_audience_id
            WHERE 1 = 1
        SQL;

        $params = [];
        if (! empty($filters['niveau_juridiction_id'])) {
            $sql .= ' AND c.niveau_juridiction_id = ?';
            $params[] = (int) $filters['niveau_juridiction_id'];
        }
        if (! empty($filters['province_id'])) {
            $sql .= ' AND (c.province_lieu_audience_id = ? OR j.province_id = ?)';
            $params[] = (int) $filters['province_id'];
            $params[] = (int) $filters['province_id'];
        }
        if (! empty($filters['commune_id'])) {
            $sql .= ' AND (c.commune_lieu_audience_id = ? OR j.commune_id = ?)';
            $params[] = (int) $filters['commune_id'];
            $params[] = (int) $filters['commune_id'];
        }
        if (! empty($filters['juridiction_id'])) {
            $sql .= ' AND c.juridiction_lieu_audience_id = ?';
            $params[] = (int) $filters['juridiction_id'];
        }
        if (! empty($filters['date_audience'])) {
            $sql .= ' AND c.date_audience::date = ?::date';
            $params[] = $filters['date_audience'];
        }

        $sql .= ' ORDER BY c.date_audience DESC NULLS LAST, c.heure_audience DESC NULLS LAST, c.convocation_id DESC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findDetailed(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                c.*,
                sc.description_statut_convocation,
                p.numero_dossier,
                p.objet,
                p.description AS plainte_description,
                p.date_depot,
                p.juridiction_id AS plainte_juridiction_id,
                p.niveau_juridiction_id AS plainte_niveau_id,
                p.etape_plainte_id,
                p.statut_plainte_id,
                nj.desc_niveau_juridiction,
                j.nom_juridiction,
                jp.nom_juridiction AS plainte_juridiction,
                njp.desc_niveau_juridiction AS plainte_niveau,
                etape.description_etape_plainte,
                stpl.description_statut_plainte,
                lp.province_name,
                lc.commune_name,
                lz.zone_name,
                lcol.colline_name,
                TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS issued_by_name
            FROM convocation.convocation AS c
            LEFT JOIN convocation.statut_convocation AS sc
                ON sc.statut_convocation_id = c.statut_convocation_id
            LEFT JOIN plainte.plainte AS p ON p.plainte_id = c.plainte_id
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = c.niveau_juridiction_id
            LEFT JOIN juridiction.juridiction AS j
                ON j.juridiction_id = c.juridiction_lieu_audience_id
            LEFT JOIN juridiction.juridiction AS jp
                ON jp.juridiction_id = p.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS njp
                ON njp.niveau_juridiction_id = p.niveau_juridiction_id
            LEFT JOIN plainte.etape_plainte AS etape ON etape.etape_plainte_id = p.etape_plainte_id
            LEFT JOIN plainte.statut_plainte AS stpl ON stpl.statut_plainte_id = p.statut_plainte_id
            LEFT JOIN localite.localite_province AS lp ON lp.province_id = c.province_lieu_audience_id
            LEFT JOIN localite.localite_commune AS lc ON lc.commune_id = c.commune_lieu_audience_id
            LEFT JOIN localite.localite_zone AS lz ON lz.zone_id = c.zone_lieu_audience_id
            LEFT JOIN localite.localite_colline AS lcol ON lcol.colline_id = c.colline_lieu_audience_id
            LEFT JOIN administration.utilisateur AS u ON u.utilisateur_id = c.emise_par
            WHERE c.convocation_id = ?
            LIMIT 1
        SQL;

        $row = $this->db->query($sql, [$id])->getRowArray();

        return is_array($row) ? $row : null;
    }

    /**
     * Complaints currently at a stage that requires summons generation.
     *
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listComplaintsRequiringSummons(array $filters = []): array
    {
        $sql = <<<'SQL'
            SELECT
                p.plainte_id,
                p.numero_dossier,
                p.objet,
                p.date_depot,
                p.juridiction_id,
                p.niveau_juridiction_id,
                p.etape_plainte_id,
                p.statut_plainte_id,
                j.nom_juridiction,
                j.province_id,
                j.commune_id,
                nj.desc_niveau_juridiction,
                etape.description_etape_plainte,
                etape.is_convocation,
                stpl.description_statut_plainte,
                (
                    SELECT COUNT(*) FROM plaignant.plainte_role_personne prp
                    WHERE prp.plainte_id = p.plainte_id
                ) AS people_count,
                (
                    SELECT COUNT(*) FROM plainte.plainte_parcelle pp
                    WHERE pp.plainte_id = p.plainte_id
                ) AS parcels_count
            FROM plainte.plainte AS p
            JOIN plainte.etape_plainte AS etape ON etape.etape_plainte_id = p.etape_plainte_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = p.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = p.niveau_juridiction_id
            LEFT JOIN plainte.statut_plainte AS stpl ON stpl.statut_plainte_id = p.statut_plainte_id
            WHERE etape.is_convocation = TRUE
              AND (etape.is_active IS NULL OR etape.is_active = TRUE)
        SQL;

        $params = [];
        if (! empty($filters['niveau_juridiction_id'])) {
            $sql .= ' AND p.niveau_juridiction_id = ?';
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
            $sql .= ' AND p.juridiction_id = ?';
            $params[] = (int) $filters['juridiction_id'];
        }
        if (! empty($filters['date_depot'])) {
            $sql .= ' AND p.date_depot::date = ?::date';
            $params[] = $filters['date_depot'];
        }

        $sql .= ' ORDER BY p.date_depot DESC NULLS LAST, p.plainte_id DESC';

        return $this->db->query($sql, $params)->getResultArray();
    }
}
