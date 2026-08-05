<?php

namespace Modules\Transfer\Controllers;

use Modules\Transfer\Services\TransferStatusService;

class TransferStatuses extends \App\Controllers\BaseController
{
    private TransferStatusService $service;

    public function __construct()
    {
        $this->service = new TransferStatusService();
    }

    public function index()
    {
        return view('Modules\Transfer\Views\statuses\index', [
            'title'  => lang('Backoffice.trf_st_title'),
            'active' => 'transfer-statuses',
            'items'  => $this->service->list(),
            'user'   => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.trf_st_err_save')]);
        }

        return redirect()->to(site_url('backoffice/transfer-statuses'))->with('success', lang('Backoffice.trf_st_created'));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.trf_st_err_save')]);
        }

        return redirect()->to(site_url('backoffice/transfer-statuses'))->with('success', lang('Backoffice.trf_st_updated'));
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
