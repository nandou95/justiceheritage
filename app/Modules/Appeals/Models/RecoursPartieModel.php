<?php

namespace Modules\Appeals\Models;

use CodeIgniter\Model;

class RecoursPartieModel extends Model
{
    protected $table            = 'recours.recours_partie';
    protected $primaryKey       = 'recours_partie_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'recours_id',
        'role_personne_id',
        'created_at',
    ];

    public function deleteByRecours(int $recoursId): void
    {
        $this->where('recours_id', $recoursId)->delete();
    }

    /**
     * @param list<int> $roleIds
     */
    public function syncRoles(int $recoursId, array $roleIds): void
    {
        $this->deleteByRecours($recoursId);
        $now = date('Y-m-d H:i:s');
        foreach (array_unique($roleIds) as $roleId) {
            if ($roleId < 1) {
                continue;
            }
            $this->insert([
                'recours_id'       => $recoursId,
                'role_personne_id' => $roleId,
                'created_at'       => $now,
            ]);
        }
    }
}
