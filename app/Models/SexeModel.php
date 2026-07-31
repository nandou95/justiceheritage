<?php

namespace App\Models;

use CodeIgniter\Model;

class SexeModel extends Model
{
    protected $table         = 'plaignant.sexe';
    protected $primaryKey    = 'sexe_id';
    protected $returnType    = 'array';
    protected $allowedFields = [];
    protected $useTimestamps = false;

    /**
     * @return list<array{id:int|string,label:string}>
     */
    public function options(): array
    {
        $rows = $this->builder()
            ->select('sexe_id, description_sexe')
            ->orderBy('sexe_id', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): array => [
            'id'    => $row['sexe_id'],
            'label' => $row['description_sexe'],
        ], $rows);
    }
}
