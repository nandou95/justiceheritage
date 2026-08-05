<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class StatutPlainteModel extends Model
{
    protected $table            = 'plainte.statut_plainte';
    protected $primaryKey       = 'statut_plainte_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'description_statut_plainte',
        'is_active',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listFiltered(?bool $isActive = null): array
    {
        $builder = $this->builder()
            ->select('statut_plainte_id, description_statut_plainte, is_active')
            ->orderBy('description_statut_plainte', 'ASC');

        if ($isActive === true) {
            $builder->where('is_active', true);
        } elseif ($isActive === false) {
            $builder->groupStart()
                ->where('is_active', false)
                ->orWhere('is_active', null)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * @return list<array{id:int|string,label:string}>
     */
    public function options(bool $activeOnly = true): array
    {
        $builder = $this->builder()
            ->select('statut_plainte_id, description_statut_plainte')
            ->orderBy('description_statut_plainte', 'ASC');
        if ($activeOnly) {
            $builder->where('is_active', true);
        }

        return array_map(static fn (array $row): array => [
            'id'    => $row['statut_plainte_id'],
            'label' => $row['description_statut_plainte'],
        ], $builder->get()->getResultArray());
    }

    public function findDefaultId(): ?int
    {
        $row = $this->builder()
            ->select('statut_plainte_id')
            ->where('is_active', true)
            ->orderBy('statut_plainte_id', 'ASC')
            ->get()
            ->getRowArray();

        return $row ? (int) $row['statut_plainte_id'] : null;
    }
}
