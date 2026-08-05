<?php

namespace Modules\Summons\Models;

use CodeIgniter\Model;

class ConvocationDestinataireModel extends Model
{
    protected $table            = 'convocation.convocation_destinataire';
    protected $primaryKey       = 'convocation_destinataire_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'convocation_id',
        'plainte_role_personne_id',
        'date_remise',
        'remis_par',
        'statut_convocation_id',
        'created_at',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listByConvocation(int $convocationId): array
    {
        $sql = <<<'SQL'
            SELECT
                cd.convocation_destinataire_id,
                cd.convocation_id,
                cd.plainte_role_personne_id,
                cd.date_remise,
                cd.statut_convocation_id,
                sc.description_statut_convocation,
                pe.nom_personne,
                pe.prenom_personne,
                pe.email,
                pe.telephone,
                pe.numero_cni,
                rp.description_role_personne
            FROM convocation.convocation_destinataire AS cd
            LEFT JOIN convocation.statut_convocation AS sc
                ON sc.statut_convocation_id = cd.statut_convocation_id
            LEFT JOIN plaignant.plainte_role_personne AS prp
                ON prp.plainte_role_personne_id = cd.plainte_role_personne_id
            LEFT JOIN plaignant.personne AS pe ON pe.personne_id = prp.personne_id
            LEFT JOIN plaignant.role_personne AS rp ON rp.role_personne_id = prp.role_personne_id
            WHERE cd.convocation_id = ?
            ORDER BY rp.role_personne_id ASC, pe.nom_personne ASC
        SQL;

        return $this->db->query($sql, [$convocationId])->getResultArray();
    }
}
