<?php

namespace Modules\Administration\Models;

use CodeIgniter\Model;

class NiveauJuridictionModel extends Model
{
    protected $table         = 'juridiction.niveau_juridiction';
    protected $primaryKey    = 'niveau_juridiction_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [];

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
