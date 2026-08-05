<?php

namespace Modules\CourtJurisdiction\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Modules\CourtJurisdiction\Models\CollineModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\CourtJurisdiction\Models\ZoneModel;
use Modules\CourtJurisdiction\Services\CourtJurisdictionService;

class CourtJurisdictions extends \App\Controllers\BaseController
{
    private CourtJurisdictionService $service;

    public function __construct()
    {
        $this->service = new CourtJurisdictionService();
    }

    public function index()
    {
        $filters = [
            'province_id'           => $this->request->getGet('province_id'),
            'commune_id'            => $this->request->getGet('commune_id'),
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id'),
            'status'                => $this->request->getGet('status'),
        ];
        $provinceId = (int) ($filters['province_id'] ?? 0);

        return view('Modules\CourtJurisdiction\Views\jurisdictions\index', [
            'title'         => lang('Backoffice.cj_title'),
            'active'        => 'court-jurisdictions',
            'items'         => $this->service->list($filters),
            'filters'       => $filters,
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'niveaux'       => (new NiveauJuridictionModel())->options(),
            'user'          => $this->sampleUser(),
        ]);
    }

    public function create()
    {
        return $this->formView('create', $this->emptyRecord());
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cj_err_save')]);
        }

        return redirect()->to(site_url('backoffice/court-jurisdictions'))->with('success', lang('Backoffice.cj_created'));
    }

    public function edit(int $id)
    {
        $record = $this->service->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/court-jurisdictions'))->with('error', lang('Backoffice.cj_err_not_found'));
        }

        return $this->formView('edit', $record);
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cj_err_save')]);
        }

        return redirect()->to(site_url('backoffice/court-jurisdictions'))->with('success', lang('Backoffice.cj_updated'));
    }

    public function show(int $id)
    {
        $record = $this->service->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/court-jurisdictions'))->with('error', lang('Backoffice.cj_err_not_found'));
        }

        return view('Modules\CourtJurisdiction\Views\jurisdictions\show', [
            'title'  => lang('Backoffice.cj_details_title'),
            'active' => 'court-jurisdictions',
            'record' => $record,
            'user'   => $this->sampleUser(),
        ]);
    }

    public function toggleStatus(int $id)
    {
        $result = $this->service->toggleStatus($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/court-jurisdictions'))->with('error', $result['errors'][0] ?? lang('Backoffice.cj_err_save'));
        }

        return redirect()->to(site_url('backoffice/court-jurisdictions'))->with(
            'success',
            ($result['activated'] ?? false) ? lang('Backoffice.cj_activated') : lang('Backoffice.cj_deactivated')
        );
    }

    public function options(): ResponseInterface
    {
        $options = (new JuridictionModel())->options([
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id') ? (int) $this->request->getGet('niveau_juridiction_id') : null,
            'province_id'           => $this->request->getGet('province_id') ? (int) $this->request->getGet('province_id') : null,
            'commune_id'            => $this->request->getGet('commune_id') ? (int) $this->request->getGet('commune_id') : null,
            'active_only'           => true,
        ]);

        return $this->response->setJSON(['ok' => true, 'options' => $options]);
    }

    /**
     * @param array<string, mixed> $record
     */
    private function formView(string $mode, array $record)
    {
        $provinceId = (int) (old('province_id') ?: ($record['province_id'] ?? 0));
        $communeId  = (int) (old('commune_id') ?: ($record['commune_id'] ?? 0));
        $zoneId     = (int) (old('zone_id') ?: ($record['zone_id'] ?? 0));

        return view('Modules\CourtJurisdiction\Views\jurisdictions\form', [
            'title'     => $mode === 'edit' ? lang('Backoffice.cj_edit_title') : lang('Backoffice.cj_create_title'),
            'active'    => 'court-jurisdictions',
            'mode'      => $mode,
            'record'    => $record,
            'niveaux'   => (new NiveauJuridictionModel())->options(),
            'provinces' => (new ProvinceModel())->options(),
            'communes'  => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'zones'     => $communeId ? (new ZoneModel())->optionsByCommune($communeId) : [],
            'collines'  => $zoneId ? (new CollineModel())->optionsByZone($zoneId) : [],
            'user'      => $this->sampleUser(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRecord(): array
    {
        return [
            'juridiction_id'        => null,
            'code_juridiction'      => old('code_juridiction'),
            'nom_juridiction'       => old('nom_juridiction'),
            'niveau_juridiction_id' => old('niveau_juridiction_id'),
            'adresse'               => old('adresse'),
            'telephone'             => old('telephone'),
            'email'                 => old('email'),
            'province_id'           => old('province_id'),
            'commune_id'            => old('commune_id'),
            'zone_id'               => old('zone_id'),
            'colline_id'            => old('colline_id'),
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
