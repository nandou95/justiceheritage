<?php

namespace Modules\Administration\Controllers;

use Modules\Administration\Services\ProfileService;

class Profiles extends \App\Controllers\BaseController
{
    private ProfileService $profiles;

    public function __construct()
    {
        $this->profiles = new ProfileService();
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

        return view('Modules\Administration\Views\profiles\index', [
            'title'    => lang('Backoffice.profiles_title'),
            'active'   => 'profiles',
            'profiles' => $this->profiles->list($isActive),
            'status'   => $status,
            'user'     => $this->sampleUser(),
        ]);
    }

    public function create()
    {
        $oldPermissions = old('permission_ids');
        if (! is_array($oldPermissions)) {
            $oldPermissions = [];
        }

        return view('Modules\Administration\Views\profiles\form', [
            'title'              => lang('Backoffice.profiles_create_title'),
            'active'             => 'profiles',
            'mode'               => 'create',
            'record'             => [
                'code_profil'        => old('code_profil'),
                'libelle_profil'     => old('libelle_profil'),
                'description_profil' => old('description_profil'),
                'permission_ids'     => array_map('intval', $oldPermissions),
            ],
            'permissionGroups'   => $this->profiles->permissionsGrouped(),
            'user'               => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $result = $this->profiles->create($this->request->getPost());

        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.profiles_err_save')]);
        }

        return redirect()->to(site_url('backoffice/profiles'))->with('success', lang('Backoffice.profiles_created'));
    }

    public function edit(int $id)
    {
        $record = $this->profiles->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/profiles'))->with('error', lang('Backoffice.profiles_err_not_found'));
        }

        $oldPermissions = old('permission_ids');
        if (is_array($oldPermissions)) {
            $record['permission_ids'] = array_map('intval', $oldPermissions);
            $record['code_profil'] = old('code_profil', $record['code_profil']);
            $record['libelle_profil'] = old('libelle_profil', $record['libelle_profil']);
            $record['description_profil'] = old('description_profil', $record['description_profil']);
        }

        return view('Modules\Administration\Views\profiles\form', [
            'title'            => lang('Backoffice.profiles_edit_title'),
            'active'           => 'profiles',
            'mode'             => 'edit',
            'record'           => $record,
            'permissionGroups' => $this->profiles->permissionsGrouped(),
            'user'             => $this->sampleUser(),
        ]);
    }

    public function update(int $id)
    {
        $result = $this->profiles->update($id, $this->request->getPost());

        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.profiles_err_save')]);
        }

        return redirect()->to(site_url('backoffice/profiles'))->with('success', lang('Backoffice.profiles_updated'));
    }

    public function show(int $id)
    {
        $record = $this->profiles->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/profiles'))->with('error', lang('Backoffice.profiles_err_not_found'));
        }

        return view('Modules\Administration\Views\profiles\show', [
            'title'  => lang('Backoffice.profiles_details_title'),
            'active' => 'profiles',
            'record' => $record,
            'user'   => $this->sampleUser(),
        ]);
    }

    public function toggleStatus(int $id)
    {
        $result = $this->profiles->toggleStatus($id);

        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/profiles'))->with('error', $result['errors'][0] ?? lang('Backoffice.profiles_err_save'));
        }

        $message = ($result['activated'] ?? false)
            ? lang('Backoffice.profiles_activated')
            : lang('Backoffice.profiles_deactivated');

        return redirect()->to(site_url('backoffice/profiles'))->with('success', $message);
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
