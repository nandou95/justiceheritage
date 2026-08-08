<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class HistoriquePlainteModel extends Model
{
    protected $table            = 'plainte.historique_plainte';
    protected $primaryKey       = 'historique_plainte_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'plainte_id',
        'etape_plainte_id',
        'etape_plainte_action_id',
        'statut_plainte_id',
        'is_utilisateur',
        'utilisateur_id',
        'personne_id',
        'create_at',
    ];

    /**
     * @param array{
     *   plainte_id:int,
     *   etape_plainte_id:int,
     *   etape_plainte_action_id:int,
     *   statut_plainte_id:int,
     *   is_utilisateur:bool,
     *   utilisateur_id?:int|null,
     *   personne_id?:int|null
     * } $data
     */
    public function recordEvent(array $data): bool
    {
        return (bool) $this->insert([
            'plainte_id'             => (int) $data['plainte_id'],
            'etape_plainte_id'       => (int) $data['etape_plainte_id'],
            'etape_plainte_action_id'=> (int) $data['etape_plainte_action_id'],
            'statut_plainte_id'      => (int) $data['statut_plainte_id'],
            'is_utilisateur'         => (bool) ($data['is_utilisateur'] ?? false),
            'utilisateur_id'         => isset($data['utilisateur_id']) && $data['utilisateur_id'] !== null
                ? (int) $data['utilisateur_id']
                : null,
            'personne_id'            => isset($data['personne_id']) && $data['personne_id'] !== null
                ? (int) $data['personne_id']
                : null,
            'create_at'              => date('Y-m-d H:i:s'),
        ], true);
    }
}
