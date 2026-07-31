<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class PlainteModel extends Model
{
    protected $table         = 'plainte.plainte';
    protected $primaryKey    = 'plainte_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [];

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
