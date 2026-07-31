<?php



namespace Modules\CourtJurisdiction\Models;



use CodeIgniter\Model;



class ZoneModel extends Model

{

    protected $table         = 'localite.localite_zone';

    protected $primaryKey    = 'zone_id';

    protected $returnType    = 'array';

    protected $allowedFields = [];

    protected $useTimestamps = false;



    /**

     * @return list<array{id:int|string,label:string}>

     */

    public function optionsByCommune(int $communeId): array

    {

        if ($communeId < 1) {

            return [];

        }



        $rows = $this->builder()

            ->select('zone_id, zone_name')

            ->where('commune_id', $communeId)

            ->orderBy('zone_name', 'ASC')

            ->get()

            ->getResultArray();



        return array_map(static fn (array $row): array => [

            'id'    => $row['zone_id'],

            'label' => $row['zone_name'],

        ], $rows);

    }

}


