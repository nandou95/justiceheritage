<?php

namespace Modules\CourtJurisdiction\Controllers;

use Modules\CourtJurisdiction\Services\JurisdictionLevelService;

class JurisdictionLevels extends \App\Controllers\BaseController
{
    private JurisdictionLevelService $service;

    public function __construct()
    {
        $this->service = new JurisdictionLevelService();
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

        return view('Modules\CourtJurisdiction\Views\levels\index', [
            'title'  => lang('Backoffice.jl_title'),
            'active' => 'jurisdiction-levels',
            'items'  => $this->service->list($isActive),
            'status' => $status,
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
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.jl_err_save')]);
        }

        return redirect()->to(site_url('backoffice/jurisdiction-levels'))->with('success', lang('Backoffice.jl_created'));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.jl_err_save')]);
        }

        return redirect()->to(site_url('backoffice/jurisdiction-levels'))->with('success', lang('Backoffice.jl_updated'));
    }

    public function toggleStatus(int $id)
    {
        $result = $this->service->toggleStatus($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/jurisdiction-levels'))->with('error', $result['errors'][0] ?? lang('Backoffice.jl_err_save'));
        }

        return redirect()->to(site_url('backoffice/jurisdiction-levels'))->with(
            'success',
            ($result['activated'] ?? false) ? lang('Backoffice.jl_activated') : lang('Backoffice.jl_deactivated')
        );
    }
}
