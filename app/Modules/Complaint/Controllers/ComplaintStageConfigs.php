<?php

namespace Modules\Complaint\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Modules\Complaint\Models\EtapePlainteModel;
use Modules\Complaint\Services\ComplaintStageConfigService;
use Modules\Complaint\Services\ComplaintStageService;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;

class ComplaintStageConfigs extends \App\Controllers\BaseController
{
    private ComplaintStageConfigService $service;

    public function __construct()
    {
        $this->service = new ComplaintStageConfigService();
    }

    public function index()
    {
        $niveauId = $this->request->getGet('niveau_juridiction_id');
        $status   = $this->request->getGet('status');
        $isActive = null;
        if ($status === '1' || $status === 'true') {
            $isActive = true;
        } elseif ($status === '0' || $status === 'false') {
            $isActive = false;
        }

        return view('Modules\Complaint\Views\stage_configs\index', [
            'title'   => lang('Backoffice.csc_title'),
            'active'  => 'complaint-stage-config',
            'items'   => $this->service->list($niveauId ? (int) $niveauId : null, $isActive),
            'filters' => [
                'niveau_juridiction_id' => $niveauId,
                'status'                => $status,
            ],
            'levels' => (new NiveauJuridictionModel())->options(),
            'stages' => (new EtapePlainteModel())->options(null, false),
            'user'   => [
                'name' => lang('Backoffice.user_sample'),
                'role' => lang('Backoffice.role_sample'),
            ],
        ]);
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.csc_err_save')]);
        }

        return redirect()->to(site_url('backoffice/complaint-stage-configs'))->with('success', lang('Backoffice.csc_created'));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.csc_err_save')]);
        }

        return redirect()->to(site_url('backoffice/complaint-stage-configs'))->with('success', lang('Backoffice.csc_updated'));
    }

    public function toggleStatus(int $id)
    {
        $result = $this->service->toggleStatus($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/complaint-stage-configs'))->with('error', $result['errors'][0] ?? lang('Backoffice.csc_err_save'));
        }

        return redirect()->to(site_url('backoffice/complaint-stage-configs'))->with(
            'success',
            ($result['activated'] ?? false) ? lang('Backoffice.csc_activated') : lang('Backoffice.csc_deactivated')
        );
    }

    public function stages(): ResponseInterface
    {
        $niveauId = (int) ($this->request->getGet('niveau_juridiction_id') ?? 0);

        return $this->response->setJSON([
            'ok'      => true,
            'options' => (new EtapePlainteModel())->options($niveauId ?: null, true),
        ]);
    }

    public function profiles(int $etapeId): ResponseInterface
    {
        return $this->response->setJSON([
            'ok'       => true,
            'profiles' => (new ComplaintStageService())->profiles($etapeId),
        ]);
    }
}
