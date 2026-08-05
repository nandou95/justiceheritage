<?php

namespace Modules\Administration\Models;

use CodeIgniter\Model;

class UtilisateurModel extends Model
{
    protected $table         = 'administration.utilisateur';
    protected $primaryKey    = 'utilisateur_id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'nom_utilisateur',
        'prenom_utilisateur',
        'numero_cni',
        'numero_matricule',
        'telephone',
        'email',
        'date_naissance',
        'profil_id',
        'statut_compte_id',
        'juridiction_id',
        'sexe_id',
        'province_naissance_id',
        'commune_naissance_id',
        'zone_naissance_id',
        'colline_naissance_id',
        'user_name',
        'mot_de_passe_hash',
        'derniere_connexion',
        'code_authentification',
    ];

    /**
     * @param array{
     *   province_id?: int|null,
     *   commune_id?: int|null,
     *   niveau_juridiction_id?: int|null,
     *   juridiction_id?: int|null,
     *   account_active?: bool|null
     * } $filters
     * @return list<array<string, mixed>>
     */
    public function listWithRelations(array $filters = []): array
    {
        $sql = <<<'SQL'
            SELECT
                u.utilisateur_id,
                u.nom_utilisateur,
                u.prenom_utilisateur,
                u.numero_cni,
                u.numero_matricule,
                u.telephone,
                u.email,
                u.date_naissance,
                u.profil_id,
                u.statut_compte_id,
                u.juridiction_id,
                u.sexe_id,
                u.province_naissance_id,
                u.commune_naissance_id,
                u.zone_naissance_id,
                u.colline_naissance_id,
                u.derniere_connexion,
                u.created_at,
                u.updated_at,
                p.libelle_profil,
                sc.desc_statut_compte,
                j.nom_juridiction,
                j.code_juridiction,
                j.province_id AS juridiction_province_id,
                j.commune_id AS juridiction_commune_id,
                j.niveau_juridiction_id,
                nj.desc_niveau_juridiction,
                sx.description_sexe,
                lp.province_name AS province_naissance_name,
                lc.commune_name AS commune_naissance_name,
                lz.zone_name AS zone_naissance_name,
                lcol.colline_name AS colline_naissance_name
            FROM administration.utilisateur AS u
            LEFT JOIN administration.profil AS p
                ON p.profil_id = u.profil_id
            LEFT JOIN administration.statut_compte AS sc
                ON sc.statut_compte_id = u.statut_compte_id
            LEFT JOIN juridiction.juridiction AS j
                ON j.juridiction_id = u.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj
                ON nj.niveau_juridiction_id = j.niveau_juridiction_id
            LEFT JOIN plaignant.sexe AS sx
                ON sx.sexe_id = u.sexe_id
            LEFT JOIN localite.localite_province AS lp
                ON lp.province_id = u.province_naissance_id
            LEFT JOIN localite.localite_commune AS lc
                ON lc.commune_id = u.commune_naissance_id
            LEFT JOIN localite.localite_zone AS lz
                ON lz.zone_id = u.zone_naissance_id
            LEFT JOIN localite.localite_colline AS lcol
                ON lcol.colline_id = u.colline_naissance_id
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

        if (! empty($filters['juridiction_id'])) {
            $sql .= ' AND u.juridiction_id = ?';
            $params[] = (int) $filters['juridiction_id'];
        }

        if (array_key_exists('account_active', $filters) && $filters['account_active'] !== null) {
            if ($filters['account_active'] === true) {
                $sql .= " AND LOWER(COALESCE(sc.desc_statut_compte, '')) ~ '(^|[^a-z])(actif|active)([^a-z]|$)'
                          AND LOWER(COALESCE(sc.desc_statut_compte, '')) !~ '(inactif|inactive)'";
            } else {
                $sql .= " AND (
                    LOWER(COALESCE(sc.desc_statut_compte, '')) ~ '(inactif|inactive)'
                    OR LOWER(COALESCE(sc.desc_statut_compte, '')) !~ '(actif|active)'
                )";
            }
        }

        $sql .= ' ORDER BY u.nom_utilisateur ASC, u.prenom_utilisateur ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findWithRelations(int $id): ?array
    {
        $rows = $this->listWithRelations(['utilisateur_id' => $id]);
        // listWithRelations doesn't filter by id — fetch dedicated
        $sql = <<<'SQL'
            SELECT
                u.utilisateur_id,
                u.nom_utilisateur,
                u.prenom_utilisateur,
                u.numero_cni,
                u.numero_matricule,
                u.telephone,
                u.email,
                u.date_naissance,
                u.profil_id,
                u.statut_compte_id,
                u.juridiction_id,
                u.sexe_id,
                u.province_naissance_id,
                u.commune_naissance_id,
                u.zone_naissance_id,
                u.colline_naissance_id,
                u.derniere_connexion,
                u.created_at,
                u.updated_at,
                p.libelle_profil,
                sc.desc_statut_compte,
                j.nom_juridiction,
                j.code_juridiction,
                j.niveau_juridiction_id,
                nj.desc_niveau_juridiction,
                sx.description_sexe,
                lp.province_name AS province_naissance_name,
                lc.commune_name AS commune_naissance_name,
                lz.zone_name AS zone_naissance_name,
                lcol.colline_name AS colline_naissance_name
            FROM administration.utilisateur AS u
            LEFT JOIN administration.profil AS p ON p.profil_id = u.profil_id
            LEFT JOIN administration.statut_compte AS sc ON sc.statut_compte_id = u.statut_compte_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = u.juridiction_id
            LEFT JOIN juridiction.niveau_juridiction AS nj ON nj.niveau_juridiction_id = j.niveau_juridiction_id
            LEFT JOIN plaignant.sexe AS sx ON sx.sexe_id = u.sexe_id
            LEFT JOIN localite.localite_province AS lp ON lp.province_id = u.province_naissance_id
            LEFT JOIN localite.localite_commune AS lc ON lc.commune_id = u.commune_naissance_id
            LEFT JOIN localite.localite_zone AS lz ON lz.zone_id = u.zone_naissance_id
            LEFT JOIN localite.localite_colline AS lcol ON lcol.colline_id = u.colline_naissance_id
            WHERE u.utilisateur_id = ?
            LIMIT 1
        SQL;

        $row = $this->db->query($sql, [$id])->getRowArray();

        return $row ?: null;
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()->where('LOWER(email) =', mb_strtolower($email), false);
        if ($ignoreId) {
            $builder->where('utilisateur_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    public function cniExists(string $cni, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()->where('numero_cni', $cni);
        if ($ignoreId) {
            $builder->where('utilisateur_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    public function matriculeExists(string $matricule, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()->where('numero_matricule', $matricule);
        if ($ignoreId) {
            $builder->where('utilisateur_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }
}
