<?php



namespace Modules\CourtJurisdiction\Controllers;



use App\Models\SexeModel;

use CodeIgniter\HTTP\ResponseInterface;

use Modules\CourtJurisdiction\Models\CollineModel;

use Modules\CourtJurisdiction\Models\CommuneModel;

use Modules\CourtJurisdiction\Models\ProvinceModel;

use Modules\CourtJurisdiction\Models\ZoneModel;



class LocationApi extends \App\Controllers\BaseController

{

    public function sexes(): ResponseInterface

    {

        return $this->respondOptions((new SexeModel())->options());

    }



    public function provinces(): ResponseInterface

    {

        return $this->respondOptions((new ProvinceModel())->options());

    }



    public function communes(): ResponseInterface

    {

        $provinceId = (int) $this->request->getGet('province_id');



        return $this->respondOptions((new CommuneModel())->optionsByProvince($provinceId));

    }



    public function zones(): ResponseInterface

    {

        $communeId = (int) $this->request->getGet('commune_id');



        return $this->respondOptions((new ZoneModel())->optionsByCommune($communeId));

    }



    public function collines(): ResponseInterface

    {

        $zoneId = (int) $this->request->getGet('zone_id');



        return $this->respondOptions((new CollineModel())->optionsByZone($zoneId));

    }



    /**

     * @param list<array{id:int|string,label:string}> $options

     */

    private function respondOptions(array $options): ResponseInterface

    {

        return $this->response

            ->setHeader('Cache-Control', 'no-store')

            ->setJSON([

                'ok'      => true,

                'options' => $options,

            ]);

    }

}


