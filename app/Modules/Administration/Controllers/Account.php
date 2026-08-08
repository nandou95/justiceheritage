<?php

namespace Modules\Administration\Controllers;

use Modules\Administration\Models\UtilisateurModel;
use Modules\Administration\Services\AccountService;

class Account extends \App\Controllers\BaseController
{
    private AccountService $accounts;

    public function __construct()
    {
        $this->accounts = new AccountService();
    }

    public function profile()
    {
        $userId = (int) (session('backoffice_user_id') ?? 0);
        if ($userId < 1) {
            return redirect()->to(site_url('backoffice/login'));
        }

        $page = $this->accounts->profilePageData($userId);
        if ($page === null) {
            return redirect()->to(site_url('backoffice/login'))->with('error', lang('Backoffice.account_err_not_found'));
        }

        return view('Modules\Administration\Views\account\profile', [
            'title'               => lang('Backoffice.account_profile_title'),
            'active'              => 'my-profile',
            'record'              => $page['record'],
            'permissionGroups'    => $page['permission_groups'],
            'passwordChangedAt'   => $page['password_changed_at'],
            'twoFactorEnabled'    => $page['two_factor_enabled'],
        ]);
    }

    public function edit()
    {
        $record = $this->requireProfile();
        if (! is_array($record)) {
            return $record;
        }

        return view('Modules\Administration\Views\account\edit', [
            'title'  => lang('Backoffice.account_edit_title'),
            'active' => 'my-profile',
            'record' => $record,
        ]);
    }

    public function update()
    {
        $userId = (int) (session('backoffice_user_id') ?? 0);
        if ($userId < 1) {
            return redirect()->to(site_url('backoffice/login'));
        }

        $result = $this->accounts->updateProfile($userId, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.account_err_save')]);
        }

        return redirect()
            ->to(site_url('backoffice/my/profile'))
            ->with('success', lang('Backoffice.account_updated'));
    }

    public function password()
    {
        $record = $this->requireProfile();
        if (! is_array($record)) {
            return $record;
        }

        return view('Modules\Administration\Views\account\password', [
            'title'  => lang('Backoffice.account_password_title'),
            'active' => 'my-profile',
            'record' => $record,
        ]);
    }

    public function updatePassword()
    {
        $userId = (int) (session('backoffice_user_id') ?? 0);
        if ($userId < 1) {
            return redirect()->to(site_url('backoffice/login'));
        }

        $result = $this->accounts->changePassword($userId, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->with('errors', $result['errors'] ?? [lang('Backoffice.account_err_password')]);
        }

        return redirect()
            ->to(site_url('backoffice/my/profile'))
            ->with('success', lang('Backoffice.account_password_updated'));
    }

    public function notificationPreferences()
    {
        $record = $this->requireProfile();
        if (! is_array($record)) {
            return $record;
        }

        return view('Modules\Administration\Views\account\preferences', [
            'title'  => lang('Backoffice.account_preferences_title'),
            'active' => 'my-profile',
            'record' => $record,
        ]);
    }

    /**
     * @return array<string, mixed>|\CodeIgniter\HTTP\RedirectResponse
     */
    private function requireProfile()
    {
        $userId = (int) (session('backoffice_user_id') ?? 0);
        if ($userId < 1) {
            return redirect()->to(site_url('backoffice/login'));
        }

        $record = (new UtilisateurModel())->findWithRelations($userId);
        if (! $record) {
            return redirect()->to(site_url('backoffice/login'))->with('error', lang('Backoffice.account_err_not_found'));
        }

        return $record;
    }
}
