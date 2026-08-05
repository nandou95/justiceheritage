<?php

namespace Modules\Administration\Controllers;

use Modules\Administration\Services\PermissionService;

class Permissions extends \App\Controllers\BaseController
{
    private PermissionService $permissions;

    public function __construct()
    {
        $this->permissions = new PermissionService();
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

        return view('Modules\Administration\Views\permissions\index', [
            'title'       => lang('Backoffice.perm_title'),
            'active'      => 'permissions',
            'permissions' => $this->permissions->list($isActive),
            'status'      => $status,
            'user'        => [
                'name' => lang('Backoffice.user_sample'),
                'role' => lang('Backoffice.role_sample'),
            ],
        ]);
    }

    public function store()
    {
        $result = $this->permissions->create($this->request->getPost());

        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.perm_err_save')]);
        }

        return redirect()->to(site_url('backoffice/permissions'))->with('success', lang('Backoffice.perm_created'));
    }

    public function update(int $id)
    {
        $result = $this->permissions->update($id, $this->request->getPost());

        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.perm_err_save')]);
        }

        return redirect()->to(site_url('backoffice/permissions'))->with('success', lang('Backoffice.perm_updated'));
    }

    public function toggleStatus(int $id)
    {
        $result = $this->permissions->toggleStatus($id);

        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/permissions'))->with('error', $result['errors'][0] ?? lang('Backoffice.perm_err_save'));
        }

        $message = ($result['activated'] ?? false)
            ? lang('Backoffice.perm_activated')
            : lang('Backoffice.perm_deactivated');

        return redirect()->to(site_url('backoffice/permissions'))->with('success', $message);
    }
}
