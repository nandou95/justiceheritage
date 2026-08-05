<?php

namespace Modules\Administration\Models;

use CodeIgniter\Model;

class JuridictionModel extends Model
{
    protected $table         = 'juridiction.juridiction';
    protected $primaryKey    = 'juridiction_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [];

    /**
     * @param array{
     *   niveau_juridiction_id?: int|null,
     *   province_id?: int|null,
     *   commune_id?: int|null
     * } $filters
     * @return list<array{id:int|string,label:string,niveau_juridiction_id?:mixed,province_id?:mixed,commune_id?:mixed}>
     */
    public function options(array $filters = []): array
    {
        $builder = $this->builder()
            ->select('juridiction_id, code_juridiction, nom_juridiction, niveau_juridiction_id, province_id, commune_id')
            ->where('(is_active IS NULL OR is_active = TRUE)', null, false)
            ->orderBy('nom_juridiction', 'ASC');

        if (! empty($filters['niveau_juridiction_id'])) {
            $builder->where('niveau_juridiction_id', (int) $filters['niveau_juridiction_id']);
        }
        if (! empty($filters['province_id'])) {
            $builder->where('province_id', (int) $filters['province_id']);
        }
        if (! empty($filters['commune_id'])) {
            $builder->where('commune_id', (int) $filters['commune_id']);
        }

        $rows = $builder->get()->getResultArray();

        return array_map(static fn (array $row): array => [
            'id'                     => $row['juridiction_id'],
            'label'                  => trim(($row['code_juridiction'] ?? '') . ' — ' . ($row['nom_juridiction'] ?? ''), ' —'),
            'niveau_juridiction_id'  => $row['niveau_juridiction_id'],
            'province_id'            => $row['province_id'],
            'commune_id'             => $row['commune_id'],
        ], $rows);
    }
}
