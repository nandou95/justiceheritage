<?php

namespace Modules\CourtJurisdiction\Models;

use CodeIgniter\Model;

class NiveauJuridictionModel extends Model
{
    protected $table         = 'juridiction.niveau_juridiction';
    protected $primaryKey    = 'niveau_juridiction_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'desc_niveau_juridiction',
        'is_active',
        'is_recours',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function listFiltered(?bool $isActive = null): array
    {
        $builder = $this->builder()
            ->select('niveau_juridiction_id, desc_niveau_juridiction, is_active, is_recours')
            ->orderBy('niveau_juridiction_id', 'ASC');

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
    public function options(): array
    {
        $rows = $this->builder()
            ->select('niveau_juridiction_id, desc_niveau_juridiction')
            ->where('(is_active IS NULL OR is_active = TRUE)', null, false)
            ->orderBy('niveau_juridiction_id', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): array => [
            'id'    => $row['niveau_juridiction_id'],
            'label' => $row['desc_niveau_juridiction'],
        ], $rows);
    }
}
