<?php

namespace Modules\Summons\Models;

use CodeIgniter\Model;

class StatutConvocationModel extends Model
{
    protected $table            = 'convocation.statut_convocation';
    protected $primaryKey       = 'statut_convocation_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'description_statut_convocation',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listAll(): array
    {
        return $this->builder()
            ->orderBy('description_statut_convocation', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function findDefaultId(): ?int
    {
        $row = $this->builder()
            ->select('statut_convocation_id')
            ->orderBy('statut_convocation_id', 'ASC')
            ->get(1)
            ->getRowArray();

        return $row ? (int) $row['statut_convocation_id'] : null;
    }

    /**
     * @return list<array{id:int,label:string}>
     */
    public function options(): array
    {
        return array_map(static fn (array $row): array => [
            'id'    => (int) $row['statut_convocation_id'],
            'label' => (string) ($row['description_statut_convocation'] ?? ''),
        ], $this->listAll());
    }
}
