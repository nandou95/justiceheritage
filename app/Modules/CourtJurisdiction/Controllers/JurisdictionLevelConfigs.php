<?php

namespace Modules\CourtJurisdiction\Controllers;

use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\CourtJurisdiction\Services\JurisdictionLevelConfigService;

class JurisdictionLevelConfigs extends \App\Controllers\BaseController
{
    private JurisdictionLevelConfigService $service;

    public function __construct()
    {
        $this->service = new JurisdictionLevelConfigService();
    }

    public function index()
    {
        $status = $this->request->getGet('status');
        $isActive = null;
        if ($status === '1' || $status === 'true') {
            $isActive = true;
        } elseif ($status === '0' || $status === 'false') {
            $isActive = false;
        }

        return view('Modules\CourtJurisdiction\Views\level_configs\index', [
            'title'   => lang('Backoffice.jlc_title'),
            'active'  => 'jurisdiction-level-config',
            'items'   => $this->service->list($isActive),
            'status'  => $status,
            'niveaux' => (new NiveauJuridictionModel())->options(),
            'user'    => [
                'name' => lang('Backoffice.user_sample'),
                'role' => lang('Backoffice.role_sample'),
            ],
        ]);
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.jlc_err_save')]);
        }

        return redirect()->to(site_url('backoffice/jurisdiction-level-configs'))->with('success', lang('Backoffice.jlc_created'));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.jlc_err_save')]);
        }

        return redirect()->to(site_url('backoffice/jurisdiction-level-configs'))->with('success', lang('Backoffice.jlc_updated'));
    }

    public function toggleStatus(int $id)
    {
        $result = $this->service->toggleStatus($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/jurisdiction-level-configs'))->with('error', $result['errors'][0] ?? lang('Backoffice.jlc_err_save'));
        }

        return redirect()->to(site_url('backoffice/jurisdiction-level-configs'))->with(
            'success',
            ($result['activated'] ?? false) ? lang('Backoffice.jlc_activated') : lang('Backoffice.jlc_deactivated')
        );
    }
}
