<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class PlainteModel extends Model
{
    protected $table         = 'plainte.plainte';
    protected $primaryKey    = 'plainte_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'numero_dossier',
        'juridiction_id',
        'niveau_juridiction_id',
        'statut_plainte_id',
        'objet',
        'description',
        'date_depot',
        'enregistre_par',
        'created_at',
        'updated_at',
        'etape_plainte_id',
        'est_cree_par_plaigant',
        'is_recours',
    ];

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listForBackoffice(array $filters = []): array
    {
        $sql = <<<'SQL'
            SELECT
                p.plainte_id,
                p.numero_dossier,
                p.objet,
                p.description,
                p.date_depot,
                p.juridiction_id,
                p.niveau_juridiction_id,
                p.statut_plainte_id,
                p.etape_plainte_id,
                j.nom_juridiction,
                j.province_id,
                j.commune_id,
                nj.desc_niveau_juridiction,
                etape.description_etape_plainte,
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
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = p.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = p.niveau_juridiction_id
            LEFT JOIN plainte.etape_plainte AS etape ON etape.etape_plainte_id = p.etape_plainte_id
            LEFT JOIN plainte.statut_plainte AS stpl ON stpl.statut_plainte_id = p.statut_plainte_id
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
            $sql .= ' AND p.niveau_juridiction_id = ?';
            $params[] = (int) $filters['niveau_juridiction_id'];
        }
        if (! empty($filters['juridiction_id'])) {
            $sql .= ' AND p.juridiction_id = ?';
            $params[] = (int) $filters['juridiction_id'];
        }
        if (! empty($filters['statut_plainte_id'])) {
            $sql .= ' AND p.statut_plainte_id = ?';
            $params[] = (int) $filters['statut_plainte_id'];
        }
        if (! empty($filters['date_depot'])) {
            $sql .= ' AND p.date_depot::date = ?::date';
            $params[] = $filters['date_depot'];
        }

        $sql .= ' ORDER BY p.date_depot DESC NULLS LAST, p.plainte_id DESC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForBackoffice(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                p.*,
                j.nom_juridiction,
                j.province_id,
                j.commune_id,
                nj.desc_niveau_juridiction,
                etape.description_etape_plainte,
                stpl.description_statut_plainte
            FROM plainte.plainte AS p
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = p.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = p.niveau_juridiction_id
            LEFT JOIN plainte.etape_plainte AS etape ON etape.etape_plainte_id = p.etape_plainte_id
            LEFT JOIN plainte.statut_plainte AS stpl ON stpl.statut_plainte_id = p.statut_plainte_id
            WHERE p.plainte_id = ?
            LIMIT 1
        SQL;

        $row = $this->db->query($sql, [$id])->getRowArray();

        return is_array($row) ? $row : null;
    }

    public function nextCaseNumber(): string
    {
        $year = date('Y');
        $row  = $this->db->query(
            "SELECT COUNT(*)::int AS total FROM plainte.plainte WHERE numero_dossier LIKE ?",
            ['JH-' . $year . '-%']
        )->getRowArray();
        $seq = ((int) ($row['total'] ?? 0)) + 1;

        return sprintf('JH-%s-%05d', $year, $seq);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function relatedSummons(int $plainteId): array
    {
        $sql = <<<'SQL'
            SELECT
                c.convocation_id,
                c.date_audience,
                c.heure_audience,
                c.lieu_audience,
                c.emise_le,
                sc.description_statut_convocation,
                j.nom_juridiction
            FROM convocation.convocation AS c
            LEFT JOIN convocation.statut_convocation AS sc ON sc.statut_convocation_id = c.statut_convocation_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = c.juridiction_lieu_audience_id
            WHERE c.plainte_id = ?
            ORDER BY c.emise_le DESC NULLS LAST, c.convocation_id DESC
        SQL;

        return $this->db->query($sql, [$plainteId])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function relatedHearings(int $plainteId): array
    {
        $sql = <<<'SQL'
            SELECT
                ap.audience_plainte_id,
                a.date_audience,
                a.heure_audience,
                a.lieu_audience,
                sa.description_statut_audience,
                j.nom_juridiction
            FROM audience.audience_plainte AS ap
            JOIN audience.audience AS a ON a.audience_id = ap.audience_id
            LEFT JOIN audience.statut_audience AS sa ON sa.statut_audience_id = COALESCE(ap.statut_audience_id, a.statut_audience_id)
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = a.juridiction_audience_id
            WHERE ap.plainte_id = ?
            ORDER BY a.date_audience DESC NULLS LAST, ap.audience_plainte_id DESC
        SQL;

        return $this->db->query($sql, [$plainteId])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function relatedVerdicts(int $plainteId): array
    {
        $sql = <<<'SQL'
            SELECT
                v.verdict_id,
                v.date_verdict,
                v.resume,
                v.dispositif,
                v.date_limite_recours,
                v.recours_exerce,
                tv.description_type_verdict,
                j.nom_juridiction,
                nj.desc_niveau_juridiction
            FROM verdict.verdict AS v
            JOIN audience.audience_plainte AS ap ON ap.audience_plainte_id = v.audience_plainte_id
            LEFT JOIN verdict.type_verdict AS tv ON tv.type_verdict_id = v.type_verdict_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = v.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = v.niveau_juridiction_id
            WHERE ap.plainte_id = ?
            ORDER BY v.date_verdict DESC NULLS LAST, v.verdict_id DESC
        SQL;

        return $this->db->query($sql, [$plainteId])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function relatedAppeals(int $plainteId): array
    {
        $sql = <<<'SQL'
            SELECT
                r.recours_id,
                r.date_recours,
                r.dans_les_delais,
                r.nouvelle_plainte_id,
                nj.desc_niveau_juridiction,
                j.nom_juridiction,
                p.numero_dossier AS nouvelle_plainte_numero
            FROM recours.recours AS r
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = r.niveau_juridiction_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = r.juridiction_id
            LEFT JOIN plainte.plainte AS p ON p.plainte_id = r.nouvelle_plainte_id
            WHERE r.plainte_parent_id = ?
               OR r.nouvelle_plainte_id = ?
            ORDER BY r.date_recours DESC NULLS LAST, r.recours_id DESC
        SQL;

        return $this->db->query($sql, [$plainteId, $plainteId])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function relatedTransfers(int $plainteId): array
    {
        $sql = <<<'SQL'
            SELECT
                t.transfert_dossier_id,
                t.numero_dossier_dest,
                t.date_transfert,
                t.date_reception,
                t.observations,
                js.nom_juridiction AS juridiction_source,
                jd.nom_juridiction AS juridiction_dest,
                st.description_statut_transfert_dossier
            FROM transfert.transfert_dossier AS t
            LEFT JOIN juridiction.juridiction AS js ON js.juridiction_id = t.juridiction_source_id
            LEFT JOIN juridiction.juridiction AS jd ON jd.juridiction_id = t.juridiction_dest_id
            LEFT JOIN transfert.statut_transfert_dossier AS st ON st.statut_transfert_dossier_id = t.statut_transfert_dossier_id
            WHERE t.plainte_id = ?
            ORDER BY t.date_transfert DESC NULLS LAST, t.transfert_dossier_id DESC
        SQL;

        try {
            return $this->db->query($sql, [$plainteId])->getResultArray();
        } catch (\Throwable $e) {
            // Fallback without status join if column name differs
            $sql2 = <<<'SQL'
                SELECT
                    t.transfert_dossier_id,
                    t.numero_dossier_dest,
                    t.date_transfert,
                    t.date_reception,
                    t.observations,
                    js.nom_juridiction AS juridiction_source,
                    jd.nom_juridiction AS juridiction_dest
                FROM transfert.transfert_dossier AS t
                LEFT JOIN juridiction.juridiction AS js ON js.juridiction_id = t.juridiction_source_id
                LEFT JOIN juridiction.juridiction AS jd ON jd.juridiction_id = t.juridiction_dest_id
                WHERE t.plainte_id = ?
                ORDER BY t.date_transfert DESC NULLS LAST
            SQL;

            return $this->db->query($sql2, [$plainteId])->getResultArray();
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function workflowHistory(int $plainteId): array
    {
        $sql = <<<'SQL'
            SELECT
                a.audit_log_id,
                a.action,
                a.table_cible,
                a.anciennes_valeurs,
                a.nouvelles_valeurs,
                a.created_at
            FROM audit_log.audit_log AS a
            WHERE a.table_cible LIKE 'plainte.%'
              AND a.enregistrement_id = ?
            ORDER BY a.created_at DESC NULLS LAST, a.audit_log_id DESC
        SQL;

        try {
            return $this->db->query($sql, [$plainteId])->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Communal court complaints (niveau_juridiction_id = 1).
     *
     * @return list<array<string, mixed>>
     */
    public function listByJurisdictionLevel(int $niveauJuridictionId = 1): array
    {
        $sql = <<<'SQL'
            SELECT
                p.plainte_id,
                p.numero_dossier,
                p.objet,
                p.description,
                j.nom_juridiction,
                p.date_depot,
                etape.description_etape_plainte,
                stpl.description_statut_plainte,
                p.created_at
            FROM plainte.plainte AS p
            JOIN juridiction.juridiction AS j
                ON j.juridiction_id = p.juridiction_id
            JOIN plainte.etape_plainte AS etape
                ON etape.etape_plainte_id = p.etape_plainte_id
            JOIN plainte.statut_plainte AS stpl
                ON stpl.statut_plainte_id = p.statut_plainte_id
            WHERE p.niveau_juridiction_id = ?
            ORDER BY p.date_depot DESC NULLS LAST, p.created_at DESC NULLS LAST
        SQL;

        return $this->db->query($sql, [$niveauJuridictionId])->getResultArray();
    }

    /**
     * Complaints with parent case at a given jurisdiction level (2 = provincial, 3 = regional).
     *
     * @return list<array<string, mixed>>
     */
    public function listWithParentByLevel(int $niveauJuridictionId): array
    {
        $sql = <<<'SQL'
            SELECT
                p.plainte_id,
                p.numero_dossier,
                p.objet,
                p.description,
                j.nom_juridiction,
                p.date_depot,
                etape.description_etape_plainte,
                stpl.description_statut_plainte,
                p.created_at,
                p_parent.numero_dossier AS numero_dossier_ancien,
                p_parent.description AS description_ancien
            FROM plainte.plainte AS p
            JOIN juridiction.juridiction AS j
                ON j.juridiction_id = p.juridiction_id
            JOIN plainte.etape_plainte AS etape
                ON etape.etape_plainte_id = p.etape_plainte_id
            JOIN plainte.statut_plainte AS stpl
                ON stpl.statut_plainte_id = p.statut_plainte_id
            JOIN plainte.plainte AS p_parent
                ON p_parent.plainte_id = p.plainte_parent_id
            WHERE p.niveau_juridiction_id = ?
            ORDER BY p.date_depot DESC NULLS LAST, p.created_at DESC NULLS LAST
        SQL;

        return $this->db->query($sql, [$niveauJuridictionId])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listProvincialWithParent(): array
    {
        return $this->listWithParentByLevel(2);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listRegionalWithParent(): array
    {
        return $this->listWithParentByLevel(3);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listMinistryWithParent(): array
    {
        return $this->listWithParentByLevel(4);
    }

    /**
     * Status counts for a jurisdiction level (used by list-page statistics).
     *
     * @return list<array{statut_plainte_id:mixed,description_statut_plainte:string,total:int}>
     */
    public function statusCountsByJurisdictionLevel(int $niveauJuridictionId): array
    {
        $sql = <<<'SQL'
            SELECT
                p.statut_plainte_id,
                stpl.description_statut_plainte,
                COUNT(*)::int AS total
            FROM plainte.plainte AS p
            JOIN plainte.statut_plainte AS stpl
                ON stpl.statut_plainte_id = p.statut_plainte_id
            WHERE p.niveau_juridiction_id = ?
            GROUP BY p.statut_plainte_id, stpl.description_statut_plainte
            ORDER BY total DESC
        SQL;

        return $this->db->query($sql, [$niveauJuridictionId])->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findCommunalById(int $plainteId): ?array
    {
        $sql = <<<'SQL'
            SELECT
                p.plainte_id,
                p.numero_dossier,
                p.objet,
                p.description,
                j.nom_juridiction,
                p.date_depot,
                etape.description_etape_plainte,
                stpl.description_statut_plainte,
                p.created_at
            FROM plainte.plainte AS p
            JOIN juridiction.juridiction AS j
                ON j.juridiction_id = p.juridiction_id
            JOIN plainte.etape_plainte AS etape
                ON etape.etape_plainte_id = p.etape_plainte_id
            JOIN plainte.statut_plainte AS stpl
                ON stpl.statut_plainte_id = p.statut_plainte_id
            WHERE p.niveau_juridiction_id = 1
              AND p.plainte_id = ?
            LIMIT 1
        SQL;

        $row = $this->db->query($sql, [$plainteId])->getRowArray();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findWithParentById(int $plainteId, int $niveauJuridictionId): ?array
    {
        $sql = <<<'SQL'
            SELECT
                p.plainte_id,
                p.numero_dossier,
                p.objet,
                p.description,
                j.nom_juridiction,
                p.date_depot,
                etape.description_etape_plainte,
                stpl.description_statut_plainte,
                p.created_at,
                p_parent.numero_dossier AS numero_dossier_ancien,
                p_parent.description AS description_ancien
            FROM plainte.plainte AS p
            JOIN juridiction.juridiction AS j
                ON j.juridiction_id = p.juridiction_id
            JOIN plainte.etape_plainte AS etape
                ON etape.etape_plainte_id = p.etape_plainte_id
            JOIN plainte.statut_plainte AS stpl
                ON stpl.statut_plainte_id = p.statut_plainte_id
            JOIN plainte.plainte AS p_parent
                ON p_parent.plainte_id = p.plainte_parent_id
            WHERE p.niveau_juridiction_id = ?
              AND p.plainte_id = ?
            LIMIT 1
        SQL;

        $row = $this->db->query($sql, [$niveauJuridictionId, $plainteId])->getRowArray();

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findProvincialById(int $plainteId): ?array
    {
        return $this->findWithParentById($plainteId, 2);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findRegionalById(int $plainteId): ?array
    {
        return $this->findWithParentById($plainteId, 3);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findMinistryById(int $plainteId): ?array
    {
        return $this->findWithParentById($plainteId, 4);
    }
}
