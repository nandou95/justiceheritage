<?php

namespace Modules\Verdicts\Models;

use CodeIgniter\Model;

class VerdictAffectationJugeModel extends Model
{
    protected $table            = 'verdict.verdict_affectation_juge';
    protected $primaryKey       = 'verdict_affectation_juge_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'verdict_id',
        'utilisateur_id',
        'profil_id',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listByVerdict(int $verdictId): array
    {
        $sql = <<<'SQL'
            SELECT
                vaj.verdict_affectation_juge_id,
                vaj.verdict_id,
                vaj.utilisateur_id,
                vaj.profil_id,
                pr.code_profil,
                pr.libelle_profil,
                TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS full_name,
                j.nom_juridiction
            FROM verdict.verdict_affectation_juge AS vaj
            LEFT JOIN administration.utilisateur AS u ON u.utilisateur_id = vaj.utilisateur_id
            LEFT JOIN administration.profil AS pr ON pr.profil_id = vaj.profil_id
            LEFT JOIN juridiction.juridiction AS j ON j.juridiction_id = u.juridiction_id
            WHERE vaj.verdict_id = ?
            ORDER BY u.nom_utilisateur ASC, u.prenom_utilisateur ASC
        SQL;

        return $this->db->query($sql, [$verdictId])->getResultArray();
    }

    public function deleteByVerdict(int $verdictId): void
    {
        $this->where('verdict_id', $verdictId)->delete();
    }
}
