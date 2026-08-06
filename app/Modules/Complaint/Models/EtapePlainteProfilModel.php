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
     * Synchronize profile links: insert newly selected, delete deselected.
     *
     * @param list<int> $profilIds
     */
    public function syncForEtape(int $etapeId, array $profilIds): void
    {
        $desired = array_values(array_unique(array_filter(
            array_map('intval', $profilIds),
            static fn (int $id): bool => $id > 0
        )));

        $existingRows = $this->where('etape_plainte_id', $etapeId)->findAll();
        $existingByProfil = [];
        foreach ($existingRows as $row) {
            $existingByProfil[(int) $row['profil_id']] = $row;
        }

        foreach ($desired as $profilId) {
            if (isset($existingByProfil[$profilId])) {
                $row = $existingByProfil[$profilId];
                if (! db_bool($row['is_active'] ?? true)) {
                    $this->update((int) $row['etape_plainte_profil_id'], ['is_active' => true]);
                }
                unset($existingByProfil[$profilId]);
                continue;
            }

            $this->insert([
                'etape_plainte_id' => $etapeId,
                'profil_id'       => $profilId,
                'is_active'       => true,
            ]);
        }

        foreach ($existingByProfil as $row) {
            $this->delete((int) $row['etape_plainte_profil_id']);
        }
    }
}
