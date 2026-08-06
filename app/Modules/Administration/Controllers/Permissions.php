<?php

namespace Modules\Administration\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
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
        return view('Modules\Administration\Views\permissions\index', [
            'title'       => lang('Backoffice.perm_title'),
            'active'      => 'permissions',
            'permissions' => $this->permissions->list(null),
            'status'      => '',
            'user'        => [
                'name' => lang('Backoffice.user_sample'),
                'role' => lang('Backoffice.role_sample'),
            ],
        ]);
    }

    public function apiList(): ResponseInterface
    {
        [$isActive, $search] = $this->parseListFilters();

        return $this->response->setJSON([
            'ok'    => true,
            'items' => $this->permissions->list($isActive, $search),
        ]);
    }

    public function csrfToken(): ResponseInterface
    {
        return $this->response->setJSON([
            'ok'       => true,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
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

    public function toggleStatus(int $id): ResponseInterface
    {
        $result = $this->permissions->toggleStatus($id);
        $wantsJson = $this->request->isAJAX()
            || str_contains((string) $this->request->getHeaderLine('Accept'), 'application/json');

        if ($wantsJson) {
            if (! ($result['ok'] ?? false)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'ok'       => false,
                    'message'  => $result['errors'][0] ?? lang('Backoffice.perm_err_save'),
                    'csrfName' => csrf_token(),
                    'csrfHash' => csrf_hash(),
                ]);
            }

            return $this->response->setJSON([
                'ok'        => true,
                'activated' => (bool) ($result['activated'] ?? false),
                'message'   => ($result['activated'] ?? false)
                    ? lang('Backoffice.perm_activated')
                    : lang('Backoffice.perm_deactivated'),
                'csrfName'  => csrf_token(),
                'csrfHash'  => csrf_hash(),
            ]);
        }

        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/permissions'))->with('error', $result['errors'][0] ?? lang('Backoffice.perm_err_save'));
        }

        $message = ($result['activated'] ?? false)
            ? lang('Backoffice.perm_activated')
            : lang('Backoffice.perm_deactivated');

        return redirect()->to(site_url('backoffice/permissions'))->with('success', $message);
    }

    /**
     * @return array{0:?bool,1:?string}
     */
    private function parseListFilters(): array
    {
        $status = (string) ($this->request->getGet('status') ?? '');
        $isActive = null;
        if ($status === '1' || $status === 'true') {
            $isActive = true;
        } elseif ($status === '0' || $status === 'false') {
            $isActive = false;
        }

        $search = trim((string) ($this->request->getGet('q') ?? ''));

        return [$isActive, $search !== '' ? $search : null];
    }
}
