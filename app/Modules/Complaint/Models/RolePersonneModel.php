<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class RolePersonneModel extends Model
{
    protected $table         = 'plaignant.role_personne';
    protected $primaryKey    = 'role_personne_id';
    protected $returnType    = 'array';
    protected $allowedFields = [];
    protected $useTimestamps = false;

    public const ROLE_PLAIGNANT = 1;
    public const ROLE_DEFENDANT = 2;

    /**
     * @return list<array{id:int,label:string}>
     */
    public function options(): array
    {
        $rows = $this->builder()
            ->select('role_personne_id, description_role_personne')
            ->orderBy('role_personne_id', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): array => [
            'id'    => (int) $row['role_personne_id'],
            'label' => $row['description_role_personne'],
        ], $rows);
    }

    public function findWitnessId(): ?int
    {
        $row = $this->builder()
            ->select('role_personne_id')
            ->groupStart()
                ->like('UPPER(description_role_personne)', 'TEMOIN')
                ->orLike('UPPER(description_role_personne)', 'WITNESS')
            ->groupEnd()
            ->get()
            ->getRowArray();

        return $row ? (int) $row['role_personne_id'] : null;
    }
}
