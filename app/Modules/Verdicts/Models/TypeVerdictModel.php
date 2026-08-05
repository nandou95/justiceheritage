<?php

namespace Modules\Verdicts\Models;

use CodeIgniter\Model;

class TypeVerdictModel extends Model
{
    protected $table            = 'verdict.type_verdict';
    protected $primaryKey       = 'type_verdict_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = ['description_type_verdict'];

    /**
     * @return list<array<string, mixed>>
     */
    public function listAll(): array
    {
        return $this->builder()
            ->orderBy('description_type_verdict', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array{id:int,label:string}>
     */
    public function options(): array
    {
        return array_map(static fn (array $row): array => [
            'id'    => (int) $row['type_verdict_id'],
            'label' => (string) ($row['description_type_verdict'] ?? ''),
        ], $this->listAll());
    }
}
