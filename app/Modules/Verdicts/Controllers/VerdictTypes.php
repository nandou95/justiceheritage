<?php

namespace Modules\Verdicts\Controllers;

use Modules\Verdicts\Services\VerdictTypeService;

class VerdictTypes extends \App\Controllers\BaseController
{
    private VerdictTypeService $service;

    public function __construct()
    {
        $this->service = new VerdictTypeService();
    }

    public function index()
    {
        return view('Modules\Verdicts\Views\types\index', [
            'title'  => lang('Backoffice.vrd_type_title'),
            'active' => 'verdict-types',
            'items'  => $this->service->list(),
            'user'   => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.vrd_type_err_save')]);
        }

        return redirect()->to(site_url('backoffice/verdict-types'))->with('success', lang('Backoffice.vrd_type_created'));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.vrd_type_err_save')]);
        }

        return redirect()->to(site_url('backoffice/verdict-types'))->with('success', lang('Backoffice.vrd_type_updated'));
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
