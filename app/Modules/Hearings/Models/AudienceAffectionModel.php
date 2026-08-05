<?php

namespace Modules\Hearings\Models;

use CodeIgniter\Model;

class AudienceAffectionModel extends Model
{
    protected $table            = 'audience.audience_affection';
    protected $primaryKey       = 'audience_affection_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'audience_id',
        'profil_id',
        'utilisateur_affecte_id',
        'utilisateur_id',
        'is_active',
        'create_at',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listByAudience(int $audienceId): array
    {
        $sql = <<<'SQL'
            SELECT
                aa.audience_affection_id,
                aa.audience_id,
                aa.profil_id,
                aa.utilisateur_affecte_id,
                aa.utilisateur_id,
                aa.is_active,
                aa.create_at,
                pr.code_profil,
                pr.libelle_profil,
                TRIM(CONCAT(COALESCE(ua.prenom_utilisateur, ''), ' ', COALESCE(ua.nom_utilisateur, ''))) AS assignee_name,
                TRIM(CONCAT(COALESCE(ub.prenom_utilisateur, ''), ' ', COALESCE(ub.nom_utilisateur, ''))) AS assigned_by_name
            FROM audience.audience_affection AS aa
            LEFT JOIN administration.profil AS pr ON pr.profil_id = aa.profil_id
            LEFT JOIN administration.utilisateur AS ua ON ua.utilisateur_id = aa.utilisateur_affecte_id
            LEFT JOIN administration.utilisateur AS ub ON ub.utilisateur_id = aa.utilisateur_id
            WHERE aa.audience_id = ?
            ORDER BY pr.libelle_profil ASC, ua.nom_utilisateur ASC
        SQL;

        return $this->db->query($sql, [$audienceId])->getResultArray();
    }

    public function pairExists(int $audienceId, int $userId, int $profilId, ?int $ignoreId = null): bool
    {
        $builder = $this->builder()
            ->where('audience_id', $audienceId)
            ->where('utilisateur_affecte_id', $userId)
            ->where('profil_id', $profilId);
        if ($ignoreId) {
            $builder->where('audience_affection_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }
}
