<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class PlainteRolePersonneModel extends Model
{
    protected $table            = 'plaignant.plainte_role_personne';
    protected $primaryKey       = 'plainte_role_personne_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'plainte_id',
        'personne_id',
        'role_personne_id',
        'est_recourant',
        'utilisateur_id',
        'date_ajout',
        'created_at',
    ];

    public function deleteByPlainte(int $plainteId): void
    {
        $this->where('plainte_id', $plainteId)->delete();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listByPlainte(int $plainteId, ?int $roleId = null): array
    {
        $sql = <<<'SQL'
            SELECT
                prp.plainte_role_personne_id,
                prp.personne_id,
                prp.role_personne_id,
                rp.description_role_personne,
                pe.nom_personne,
                pe.prenom_personne,
                pe.numero_cni,
                pe.telephone,
                pe.email
            FROM plaignant.plainte_role_personne AS prp
            JOIN plaignant.role_personne AS rp ON rp.role_personne_id = prp.role_personne_id
            JOIN plaignant.personne AS pe ON pe.personne_id = prp.personne_id
            WHERE prp.plainte_id = ?
        SQL;
        $params = [$plainteId];
        if ($roleId) {
            $sql .= ' AND prp.role_personne_id = ?';
            $params[] = $roleId;
        }
        $sql .= ' ORDER BY rp.role_personne_id ASC, pe.nom_personne ASC';

        return $this->db->query($sql, $params)->getResultArray();
    }
}
