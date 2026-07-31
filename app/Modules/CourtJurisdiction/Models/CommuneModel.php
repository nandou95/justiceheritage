<?php



namespace Modules\CourtJurisdiction\Models;



use CodeIgniter\Model;



class CommuneModel extends Model

{

    protected $table         = 'localite.localite_commune';

    protected $primaryKey    = 'commune_id';

    protected $returnType    = 'array';

    protected $allowedFields = [];

    protected $useTimestamps = false;



    /**

     * @return list<array{id:int|string,label:string}>

     */

    public function optionsByProvince(int $provinceId): array

    {

        if ($provinceId < 1) {

            return [];

        }



        $rows = $this->builder()

            ->select('commune_id, commune_name')

            ->where('province_id', $provinceId)

            ->orderBy('commune_name', 'ASC')

            ->get()

            ->getResultArray();



        return array_map(static fn (array $row): array => [

            'id'    => $row['commune_id'],

            'label' => $row['commune_name'],

        ], $rows);

    }

}


