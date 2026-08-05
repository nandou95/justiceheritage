<?php

namespace Modules\CourtJurisdiction\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\CourtJurisdiction\Services\CourtJurisdictionConfigService;

class CourtJurisdictionConfigs extends \App\Controllers\BaseController
{
    private CourtJurisdictionConfigService $service;

    public function __construct()
    {
        $this->service = new CourtJurisdictionConfigService();
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

        return view('Modules\CourtJurisdiction\Views\jurisdiction_configs\index', [
            'title'     => lang('Backoffice.cjc_title'),
            'active'    => 'court-jurisdiction-config',
            'items'     => $this->service->list($filters),
            'filters'   => $filters,
            'provinces' => (new ProvinceModel())->options(),
            'communes'  => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'niveaux'   => (new NiveauJuridictionModel())->options(),
            'courts'    => (new JuridictionModel())->options(['active_only' => true]),
            'user'      => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cjc_err_save')]);
        }

        return redirect()->to(site_url('backoffice/court-jurisdiction-configs'))->with('success', lang('Backoffice.cjc_created'));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cjc_err_save')]);
        }

        return redirect()->to(site_url('backoffice/court-jurisdiction-configs'))->with('success', lang('Backoffice.cjc_updated'));
    }

    public function toggleStatus(int $id)
    {
        $result = $this->service->toggleStatus($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/court-jurisdiction-configs'))->with('error', $result['errors'][0] ?? lang('Backoffice.cjc_err_save'));
        }

        return redirect()->to(site_url('backoffice/court-jurisdiction-configs'))->with(
            'success',
            ($result['activated'] ?? false) ? lang('Backoffice.cjc_activated') : lang('Backoffice.cjc_deactivated')
        );
    }

    public function parentLevel(): ResponseInterface
    {
        $niveauId = (int) $this->request->getGet('niveau_juridiction_id');
        $parentId = $niveauId ? $this->service->parentLevelFor($niveauId) : null;

        return $this->response->setJSON([
            'ok'                           => true,
            'niveau_juridiction_parent_id' => $parentId,
        ]);
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
