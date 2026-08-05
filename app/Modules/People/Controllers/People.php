<?php

namespace Modules\People\Controllers;

use App\Models\SexeModel;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\CourtJurisdiction\Models\CollineModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\CourtJurisdiction\Models\ZoneModel;
use Modules\People\Services\PersonService;

class People extends \App\Controllers\BaseController
{
    private PersonService $people;

    public function __construct()
    {
        $this->people = new PersonService();
    }

    public function index()
    {
        $filters = [
            'province_naissance_id' => $this->request->getGet('province_naissance_id'),
            'commune_naissance_id'  => $this->request->getGet('commune_naissance_id'),
            'zone_naissance_id'     => $this->request->getGet('zone_naissance_id'),
            'colline_naissance_id'  => $this->request->getGet('colline_naissance_id'),
            'sexe_id'               => $this->request->getGet('sexe_id'),
            'date_naissance'        => $this->request->getGet('date_naissance'),
        ];

        $provinceId = (int) ($filters['province_naissance_id'] ?? 0);
        $communeId  = (int) ($filters['commune_naissance_id'] ?? 0);
        $zoneId     = (int) ($filters['zone_naissance_id'] ?? 0);

        return view('Modules\People\Views\people\index', [
            'title'     => lang('Backoffice.people_title'),
            'active'    => 'people',
            'people'    => $this->people->listPeople($filters),
            'filters'   => $filters,
            'sexes'     => (new SexeModel())->options(),
            'provinces' => (new ProvinceModel())->options(),
            'communes'  => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'zones'     => $communeId ? (new ZoneModel())->optionsByCommune($communeId) : [],
            'collines'  => $zoneId ? (new CollineModel())->optionsByZone($zoneId) : [],
            'user'      => $this->sampleUser(),
        ]);
    }

    public function create()
    {
        $provinceId = (int) (old('province_naissance_id') ?: 0);
        $communeId  = (int) (old('commune_naissance_id') ?: 0);
        $zoneId     = (int) (old('zone_naissance_id') ?: 0);

        return view('Modules\People\Views\people\form', [
            'title'     => lang('Backoffice.people_create_title'),
            'active'    => 'people',
            'mode'      => 'create',
            'record'    => $this->emptyRecord(),
            'sexes'     => (new SexeModel())->options(),
            'provinces' => (new ProvinceModel())->options(),
            'communes'  => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'zones'     => $communeId ? (new ZoneModel())->optionsByCommune($communeId) : [],
            'collines'  => $zoneId ? (new CollineModel())->optionsByZone($zoneId) : [],
            'user'      => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $result = $this->people->create(
            $this->request->getPost(),
            $this->request->getFile('upload_cni')
        );

        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.people_err_save')]);
        }

        return redirect()
            ->to(site_url('backoffice/people'))
            ->with('success', lang('Backoffice.people_created'));
    }

    public function edit(int $id)
    {
        $record = $this->people->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/people'))->with('error', lang('Backoffice.people_err_not_found'));
        }

        $provinceId = (int) (old('province_naissance_id') ?: ($record['province_naissance_id'] ?? 0));
        $communeId  = (int) (old('commune_naissance_id') ?: ($record['commune_naissance_id'] ?? 0));
        $zoneId     = (int) (old('zone_naissance_id') ?: ($record['zone_naissance_id'] ?? 0));

        return view('Modules\People\Views\people\form', [
            'title'     => lang('Backoffice.people_edit_title'),
            'active'    => 'people',
            'mode'      => 'edit',
            'record'    => $record,
            'sexes'     => (new SexeModel())->options(),
            'provinces' => (new ProvinceModel())->options(),
            'communes'  => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'zones'     => $communeId ? (new ZoneModel())->optionsByCommune($communeId) : [],
            'collines'  => $zoneId ? (new CollineModel())->optionsByZone($zoneId) : [],
            'user'      => $this->sampleUser(),
        ]);
    }

    public function update(int $id)
    {
        $result = $this->people->update(
            $id,
            $this->request->getPost(),
            $this->request->getFile('upload_cni')
        );

        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.people_err_save')]);
        }

        return redirect()
            ->to(site_url('backoffice/people'))
            ->with('success', lang('Backoffice.people_updated'));
    }

    public function show(int $id)
    {
        $record = $this->people->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/people'))->with('error', lang('Backoffice.people_err_not_found'));
        }

        return view('Modules\People\Views\people\show', [
            'title'      => lang('Backoffice.people_details_title'),
            'active'     => 'people',
            'record'     => $record,
            'complaints' => $this->people->listComplaints($id),
            'user'       => $this->sampleUser(),
        ]);
    }

    public function viewCni(int $id): ResponseInterface
    {
        return $this->serveCni($id, false);
    }

    public function downloadCni(int $id): ResponseInterface
    {
        return $this->serveCni($id, true);
    }

    private function serveCni(int $id, bool $download): ResponseInterface
    {
        $record = $this->people->find($id);
        if (! $record || empty($record['upload_cni'])) {
            return redirect()->to(site_url('backoffice/people'))->with('error', lang('Backoffice.people_err_cni_missing'));
        }

        $absolute = $this->people->resolveCniAbsolutePath((string) $record['upload_cni']);
        if ($absolute === null) {
            return redirect()->to(site_url('backoffice/people'))->with('error', lang('Backoffice.people_err_cni_missing'));
        }

        $mime     = mime_content_type($absolute) ?: 'application/octet-stream';
        $filename = basename($absolute);
        $display  = 'CNI_' . preg_replace('/\s+/', '_', trim(($record['prenom_personne'] ?? '') . '_' . ($record['nom_personne'] ?? ''))) . '_' . $filename;

        if ($download) {
            return $this->response->download($absolute, null)->setFileName($display);
        }

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $display . '"')
            ->setBody((string) file_get_contents($absolute));
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRecord(): array
    {
        return [
            'personne_id'           => null,
            'nom_personne'          => old('nom_personne'),
            'prenom_personne'       => old('prenom_personne'),
            'sexe_id'               => old('sexe_id'),
            'date_naissance'        => old('date_naissance'),
            'email'                 => old('email'),
            'telephone'             => old('telephone'),
            'province_naissance_id' => old('province_naissance_id'),
            'commune_naissance_id'  => old('commune_naissance_id'),
            'zone_naissance_id'     => old('zone_naissance_id'),
            'colline_naissance_id'  => old('colline_naissance_id'),
            'numero_cni'            => old('numero_cni'),
            'upload_cni'            => null,
            'adresse_residence'     => old('adresse_residence'),
        ];
    }

    /**
     * @return array{name:string,role:string}
     */
    private function sampleUser(): array
    {
        return [
            'name' => lang('Backoffice.user_sample'),
            'role' => lang('Backoffice.role_sample'),
        ];
    }
}
