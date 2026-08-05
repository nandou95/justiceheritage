<?php

namespace Modules\Hearings\Controllers;

use Modules\Hearings\Services\HearingStatusService;

class HearingStatuses extends \App\Controllers\BaseController
{
    private HearingStatusService $service;

    public function __construct()
    {
        $this->service = new HearingStatusService();
    }

    public function index()
    {
        return view('Modules\Hearings\Views\statuses\index', [
            'title'  => lang('Backoffice.hrg_st_title'),
            'active' => 'hearing-statuses',
            'items'  => $this->service->list(),
            'user'   => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.hrg_st_err_save')]);
        }

        return redirect()->to(site_url('backoffice/hearing-statuses'))->with('success', lang('Backoffice.hrg_st_created'));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.hrg_st_err_save')]);
        }

        return redirect()->to(site_url('backoffice/hearing-statuses'))->with('success', lang('Backoffice.hrg_st_updated'));
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
