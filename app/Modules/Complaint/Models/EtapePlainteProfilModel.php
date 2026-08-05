<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class EtapePlainteProfilModel extends Model
{
    protected $table            = 'plainte.etape_plainte_profil';
    protected $primaryKey       = 'etape_plainte_profil_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'etape_plainte_id',
        'profil_id',
        'is_active',
    ];

    public function deleteByEtape(int $etapeId): void
    {
        $this->where('etape_plainte_id', $etapeId)->delete();
    }

    /**
     * @param list<int> $profilIds
     */
    public function syncForEtape(int $etapeId, array $profilIds): void
    {
        $this->deleteByEtape($etapeId);
        foreach (array_unique($profilIds) as $profilId) {
            if ($profilId < 1) {
                continue;
            }
            $this->insert([
                'etape_plainte_id' => $etapeId,
                'profil_id'       => $profilId,
                'is_active'       => true,
            ]);
        }
    }
}
