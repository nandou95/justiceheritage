<?php



namespace Modules\CourtJurisdiction\Models;



use CodeIgniter\Model;



class CollineModel extends Model

{

    protected $table         = 'localite.localite_colline';

    protected $primaryKey    = 'colline_id';

    protected $returnType    = 'array';

    protected $allowedFields = [];

    protected $useTimestamps = false;



    /**

     * @return list<array{id:int|string,label:string}>

     */

    public function optionsByZone(int $zoneId): array

    {

        if ($zoneId < 1) {

            return [];

        }



        $rows = $this->builder()

            ->select('colline_id, colline_name')

            ->where('zone_id', $zoneId)

            ->orderBy('colline_name', 'ASC')

            ->get()

            ->getResultArray();



        return array_map(static fn (array $row): array => [

            'id'    => $row['colline_id'],

            'label' => $row['colline_name'],

        ], $rows);

    }

}


