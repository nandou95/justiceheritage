<?php

namespace Modules\Complaint\Controllers;

use Modules\Complaint\Services\ComplaintStatusService;

class ComplaintStatuses extends \App\Controllers\BaseController
{
    private ComplaintStatusService $service;

    public function __construct()
    {
        $this->service = new ComplaintStatusService();
    }

    public function index()
    {
        $status   = $this->request->getGet('status');
        $isActive = null;
        if ($status === '1' || $status === 'true') {
            $isActive = true;
        } elseif ($status === '0' || $status === 'false') {
            $isActive = false;
        }

        return view('Modules\Complaint\Views\statuses\index', [
            'title'  => lang('Backoffice.cst_title'),
            'active' => 'complaint-statuses',
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
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cst_err_save')]);
        }

        return redirect()->to(site_url('backoffice/complaint-statuses'))->with('success', lang('Backoffice.cst_created'));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cst_err_save')]);
        }

        return redirect()->to(site_url('backoffice/complaint-statuses'))->with('success', lang('Backoffice.cst_updated'));
    }

    public function toggleStatus(int $id)
    {
        $result = $this->service->toggleStatus($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/complaint-statuses'))->with('error', $result['errors'][0] ?? lang('Backoffice.cst_err_save'));
        }

        return redirect()->to(site_url('backoffice/complaint-statuses'))->with(
            'success',
            ($result['activated'] ?? false) ? lang('Backoffice.cst_activated') : lang('Backoffice.cst_deactivated')
        );
    }
}
