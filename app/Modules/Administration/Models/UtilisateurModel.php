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
        'mot_de_passe_hash',
        'derniere_connexion',
        'code_authentification',
        'code_authentification_expire_at',
    ];

    /**
     * @param array{
     *   province_id?: int|null,      // maps to u.province_naissance_id
     *   commune_id?: int|null,       // maps to u.commune_naissance_id
     *   juridiction_id?: int|null,   // maps to u.juridiction_id
     *   statut_compte_id?: int|null  // maps to u.statut_compte_id
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

        // Birthplace filters (administration.utilisateur columns).
        if (! empty($filters['province_id'])) {
            $sql .= ' AND u.province_naissance_id = ?';
            $params[] = (int) $filters['province_id'];
        }

        if (! empty($filters['commune_id'])) {
            $sql .= ' AND u.commune_naissance_id = ?';
            $params[] = (int) $filters['commune_id'];
        }

        if (! empty($filters['juridiction_id'])) {
            $sql .= ' AND u.juridiction_id = ?';
            $params[] = (int) $filters['juridiction_id'];
        }

        if (! empty($filters['statut_compte_id'])) {
            $sql .= ' AND u.statut_compte_id = ?';
            $params[] = (int) $filters['statut_compte_id'];
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
        $sql = 'SELECT 1 FROM administration.utilisateur WHERE LOWER(email) = LOWER(?)';
        $params = [$email];
        if ($ignoreId) {
            $sql .= ' AND utilisateur_id != ?';
            $params[] = $ignoreId;
        }
        $sql .= ' LIMIT 1';

        return $this->db->query($sql, $params)->getFirstRow() !== null;
    }

    public function cniExists(string $cni, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM administration.utilisateur WHERE numero_cni = ?';
        $params = [$cni];
        if ($ignoreId) {
            $sql .= ' AND utilisateur_id != ?';
            $params[] = $ignoreId;
        }
        $sql .= ' LIMIT 1';

        return $this->db->query($sql, $params)->getFirstRow() !== null;
    }

    public function matriculeExists(string $matricule, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM administration.utilisateur WHERE numero_matricule = ?';
        $params = [$matricule];
        if ($ignoreId) {
            $sql .= ' AND utilisateur_id != ?';
            $params[] = $ignoreId;
        }
        $sql .= ' LIMIT 1';

        return $this->db->query($sql, $params)->getFirstRow() !== null;
    }

    /**
     * Find a back-office user by CNI, employee number, or email (case-insensitive for email).
     *
     * @return array<string, mixed>|null
     */
    public function findByLoginIdentifier(string $identifier): ?array
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $sql = <<<'SQL'
            SELECT *
            FROM administration.utilisateur
            WHERE numero_cni = ?
               OR numero_matricule = ?
               OR LOWER(email) = LOWER(?)
            LIMIT 1
        SQL;

        $row = $this->db->query($sql, [$identifier, $identifier, $identifier])->getRowArray();

        return $row ?: null;
    }

    public function setAuthenticationCode(int $utilisateurId, string $code, int $expiresAtUnix): bool
    {
        $expiresAt = (new \DateTimeImmutable('@' . $expiresAtUnix))
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
            ->format('Y-m-d H:i:s');

        $db = $this->db;

        try {
            $ok = $db->table($this->table)
                ->where('utilisateur_id', $utilisateurId)
                ->update([
                    'code_authentification'           => $code,
                    'code_authentification_expire_at' => $expiresAt,
                ]);

            if ($ok !== false) {
                return true;
            }
        } catch (\Throwable $e) {
            log_message('warning', 'Saving 2FA code without expire column: {message}', [
                'message' => $e->getMessage(),
            ]);
        }

        try {
            $ok = $db->table($this->table)
                ->where('utilisateur_id', $utilisateurId)
                ->update(['code_authentification' => $code]);

            return $ok !== false;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to persist 2FA code for utilisateur_id={id}: {message}', [
                'id'      => (string) $utilisateurId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function clearAuthenticationCode(int $utilisateurId): bool
    {
        $db = $this->db;

        try {
            $ok = $db->table($this->table)
                ->where('utilisateur_id', $utilisateurId)
                ->update([
                    'code_authentification'           => null,
                    'code_authentification_expire_at' => null,
                ]);

            if ($ok !== false) {
                return true;
            }
        } catch (\Throwable $e) {
            // Column may not exist yet.
        }

        try {
            $ok = $db->table($this->table)
                ->where('utilisateur_id', $utilisateurId)
                ->update(['code_authentification' => null]);

            return $ok !== false;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to clear 2FA code for utilisateur_id={id}: {message}', [
                'id'      => (string) $utilisateurId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * If the stored code has expired, clear it and return true.
     * When the expiry column is missing or null, returns false (session TTL still applies).
     */
    public function purgeExpiredAuthenticationCode(int $utilisateurId): bool
    {
        $user = $this->find($utilisateurId);
        if (! is_array($user) || empty($user['code_authentification'])) {
            return false;
        }

        if (! array_key_exists('code_authentification_expire_at', $user)) {
            return false;
        }

        $rawExpire = $user['code_authentification_expire_at'] ?? null;
        if ($rawExpire === null || $rawExpire === '') {
            return false;
        }

        $expires = strtotime((string) $rawExpire);
        if ($expires === false || time() > $expires) {
            $this->clearAuthenticationCode($utilisateurId);

            return true;
        }

        return false;
    }
}
