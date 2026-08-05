<?php

namespace Modules\Hearings\Models;

use CodeIgniter\Model;

class DocumentAudienceModel extends Model
{
    protected $table            = 'audience.document_audience';
    protected $primaryKey       = 'document_audience_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'observation',
        'audience_plainte_id',
        'apporte_par_partie',
        'enregistre_par',
        'enregistre_le',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listByAudience(int $audienceId): array
    {
        $sql = <<<'SQL'
            SELECT
                d.document_audience_id,
                d.observation,
                d.audience_plainte_id,
                d.apporte_par_partie,
                d.enregistre_par,
                d.enregistre_le,
                p.numero_dossier,
                TRIM(CONCAT(COALESCE(pe.prenom_personne, ''), ' ', COALESCE(pe.nom_personne, ''))) AS party_name,
                TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS uploaded_by_name
            FROM audience.document_audience AS d
            JOIN audience.audience_plainte AS ap ON ap.audience_plainte_id = d.audience_plainte_id
            JOIN plainte.plainte AS p ON p.plainte_id = ap.plainte_id
            LEFT JOIN plaignant.plainte_role_personne AS prp ON prp.plainte_role_personne_id = d.apporte_par_partie
            LEFT JOIN plaignant.personne AS pe ON pe.personne_id = prp.personne_id
            LEFT JOIN administration.utilisateur AS u ON u.utilisateur_id = d.enregistre_par
            WHERE ap.audience_id = ?
            ORDER BY d.enregistre_le DESC NULLS LAST, d.document_audience_id DESC
        SQL;

        return $this->db->query($sql, [$audienceId])->getResultArray();
    }
}
