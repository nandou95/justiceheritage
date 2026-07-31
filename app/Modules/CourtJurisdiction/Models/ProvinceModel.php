<?php



namespace Modules\CourtJurisdiction\Models;



use CodeIgniter\Model;



class ProvinceModel extends Model

{

    protected $table         = 'localite.localite_province';

    protected $primaryKey    = 'province_id';

    protected $returnType    = 'array';

    protected $allowedFields = [];

    protected $useTimestamps = false;



    /**

     * @return list<array{id:int|string,label:string}>

     */

    public function options(): array

    {

        $rows = $this->builder()

            ->select('province_id, province_name')

            ->where('(is_active IS NULL OR is_active = TRUE)', null, false)

            ->orderBy('province_name', 'ASC')

            ->get()

            ->getResultArray();



        return array_map(static fn (array $row): array => [

            'id'    => $row['province_id'],

            'label' => $row['province_name'],

        ], $rows);

    }

}


