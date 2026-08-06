<?php

namespace Modules\Complaint\Models;

use CodeIgniter\Model;

class EtapePlainteActionModel extends Model
{
    protected $table            = 'plainte.etape_plainte_action';
    protected $primaryKey       = 'etape_plainte_action_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'etape_plainte_id',
        'desc_etape_plainte_action',
        'is_active',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listForEtape(int $etapeId, ?bool $activeOnly = null): array
    {
        $builder = $this->builder()
            ->select('etape_plainte_action_id, etape_plainte_id, desc_etape_plainte_action, is_active')
            ->where('etape_plainte_id', $etapeId)
            ->orderBy('desc_etape_plainte_action', 'ASC');

        if ($activeOnly === true) {
            $builder->where('is_active', true);
        } elseif ($activeOnly === false) {
            $builder->where('is_active', false);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * @return list<array{id:int|string,label:string}>
     */
    public function optionsForEtape(int $etapeId, bool $activeOnly = true): array
    {
        $rows = $this->listForEtape($etapeId, $activeOnly ? true : null);

        return array_map(static fn (array $row): array => [
            'id'    => $row['etape_plainte_action_id'],
            'label' => $row['desc_etape_plainte_action'],
        ], $rows);
    }

    public function countForEtape(int $etapeId): int
    {
        return $this->where('etape_plainte_id', $etapeId)->countAllResults();
    }

    public function belongsToEtape(int $actionId, int $etapeId): bool
    {
        if ($actionId < 1 || $etapeId < 1) {
            return false;
        }

        return $this->where('etape_plainte_action_id', $actionId)
            ->where('etape_plainte_id', $etapeId)
            ->countAllResults() > 0;
    }
}
