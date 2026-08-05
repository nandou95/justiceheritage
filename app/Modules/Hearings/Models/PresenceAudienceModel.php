<?php

namespace Modules\Hearings\Models;

use CodeIgniter\Model;

class PresenceAudienceModel extends Model
{
    protected $table            = 'audience.presence_audience';
    protected $primaryKey       = 'presence_audience_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'audience_plainte_id',
        'plainte_role_personne_id',
        'utilisateur_id',
        'present',
        'observations',
        'created_at',
        'personne_id',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listByAudience(int $audienceId): array
    {
        $sql = <<<'SQL'
            SELECT
                pa.presence_audience_id,
                pa.audience_plainte_id,
                pa.plainte_role_personne_id,
                pa.present,
                pa.observations,
                pa.personne_id,
                p.numero_dossier,
                pe.nom_personne,
                pe.prenom_personne,
                rp.description_role_personne
            FROM audience.presence_audience AS pa
            JOIN audience.audience_plainte AS ap ON ap.audience_plainte_id = pa.audience_plainte_id
            JOIN plainte.plainte AS p ON p.plainte_id = ap.plainte_id
            LEFT JOIN plaignant.plainte_role_personne AS prp
                ON prp.plainte_role_personne_id = pa.plainte_role_personne_id
            LEFT JOIN plaignant.personne AS pe
                ON pe.personne_id = COALESCE(pa.personne_id, prp.personne_id)
            LEFT JOIN plaignant.role_personne AS rp ON rp.role_personne_id = prp.role_personne_id
            WHERE ap.audience_id = ?
            ORDER BY p.numero_dossier ASC, rp.role_personne_id ASC
        SQL;

        return $this->db->query($sql, [$audienceId])->getResultArray();
    }

    public function deleteByAudiencePlainte(int $audiencePlainteId): void
    {
        $this->where('audience_plainte_id', $audiencePlainteId)->delete();
    }
}
