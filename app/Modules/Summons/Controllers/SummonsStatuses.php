<?php

namespace Modules\Summons\Controllers;

use Modules\Summons\Services\SummonsStatusService;

class SummonsStatuses extends \App\Controllers\BaseController
{
    private SummonsStatusService $service;

    public function __construct()
    {
        $this->service = new SummonsStatusService();
    }

    public function index()
    {
        return view('Modules\Summons\Views\statuses\index', [
            'title'  => lang('Backoffice.sum_st_title'),
            'active' => 'summons-statuses',
            'items'  => $this->service->list(),
            'user'   => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.sum_st_err_save')]);
        }

        return redirect()->to(site_url('backoffice/summons-statuses'))->with('success', lang('Backoffice.sum_st_created'));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.sum_st_err_save')]);
        }

        return redirect()->to(site_url('backoffice/summons-statuses'))->with('success', lang('Backoffice.sum_st_updated'));
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
