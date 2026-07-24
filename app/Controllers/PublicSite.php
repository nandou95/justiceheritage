<?php

namespace App\Controllers;

class PublicSite extends BaseController
{
    public function index(): string
    {
        return view('public/home', [
            'title'           => lang('Site.home_title'),
            'metaDescription' => lang('Site.home_meta'),
            'active'          => 'home',
        ]);
    }

    public function disputeManagement(): string
    {
        return view('public/dispute_management', [
            'title'           => lang('Site.dispute_title'),
            'metaDescription' => lang('Site.dispute_meta'),
            'active'          => 'dispute',
        ]);
    }

    public function register(): string
    {
        return view('public/register', [
            'title'           => lang('Site.register_title'),
            'metaDescription' => lang('Site.register_meta'),
            'active'          => 'register',
            'validation'      => service('validation'),
        ]);
    }

    public function registerSubmit()
    {
        $rules = [
            'first_name'       => 'required|min_length[2]|max_length[80]',
            'last_name'        => 'required|min_length[2]|max_length[80]',
            'national_id'      => 'required|min_length[5]|max_length[40]',
            'phone'            => 'required|min_length[8]|max_length[30]',
            'email'            => 'required|valid_email|max_length[120]',
            'province'         => 'required|max_length[80]',
            'commune'          => 'required|min_length[2]|max_length[80]',
            'password'         => 'required|min_length[8]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
            'consent'          => 'required',
        ];

        if (! $this->validate($rules)) {
            return view('public/register', [
                'title'           => lang('Site.register_title'),
                'metaDescription' => lang('Site.register_meta'),
                'active'          => 'register',
                'validation'      => $this->validator,
            ]);
        }

        return view('public/register_success', [
            'title'  => lang('Site.success_title'),
            'active' => 'register',
            'name'   => $this->request->getPost('first_name') . ' ' . $this->request->getPost('last_name'),
        ]);
    }

    public function login(): string
    {
        return view('public/login', [
            'title'           => lang('Site.login_title'),
            'metaDescription' => lang('Site.login_meta'),
            'active'          => 'login',
        ]);
    }

    public function loginSubmit()
    {
        // Design preview: open the complainant portal until real auth is wired.
        session()->set('portal_user_name', 'Aline Ndayishimiye');
        session()->set('portal_demo', true);

        return redirect()->to('/portal');
    }

    public function switchLanguage(string $locale)
    {
        $supported = config('App')->supportedLocales;

        if (in_array($locale, $supported, true)) {
            session()->set('locale', $locale);
        }

        $referer = $this->request->getServer('HTTP_REFERER');

        if (is_string($referer) && $referer !== '') {
            return redirect()->to($referer);
        }

        return redirect()->to('/');
    }
}
