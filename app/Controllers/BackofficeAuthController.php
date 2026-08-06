<?php

namespace App\Controllers;

use App\Libraries\BackofficeAuth;

class BackofficeAuthController extends BaseController
{
    public function login()
    {
        $auth = new BackofficeAuth();
        if ($auth->isAuthenticated()) {
            return redirect()->to(site_url('backoffice'));
        }

        return view('backoffice/auth/login', [
            'title'  => lang('Backoffice.login_title'),
            'active' => 'bo-login',
        ]);
    }

    public function loginSubmit()
    {
        $auth = new BackofficeAuth();
        if ($auth->isAuthenticated()) {
            return redirect()->to(site_url('backoffice'));
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');
        $result   = $auth->beginLogin($username, $password);

        if (! ($result['ok'] ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['error'] ?? lang('Backoffice.login_err_credentials'));
        }

        return redirect()->to(site_url('backoffice/login/2fa'))
            ->with('email_masked', $result['email_masked'] ?? '');
    }

    public function twoFactor()
    {
        $auth = new BackofficeAuth();
        if ($auth->isAuthenticated()) {
            return redirect()->to(site_url('backoffice'));
        }
        if (! $auth->hasPendingChallenge()) {
            return redirect()->to(site_url('backoffice/login'))
                ->with('error', lang('Backoffice.login_err_session'));
        }

        $emailMasked = session()->getFlashdata('email_masked')
            ?: session(BackofficeAuth::SESSION_EMAIL_MASKED)
            ?: '***';

        return view('backoffice/auth/login_2fa', [
            'title'       => lang('Backoffice.login_2fa_title'),
            'active'      => 'bo-login',
            'emailMasked' => $emailMasked,
        ]);
    }

    public function twoFactorSubmit()
    {
        $auth = new BackofficeAuth();
        if ($auth->isAuthenticated()) {
            return redirect()->to(site_url('backoffice'));
        }
        if (! $auth->hasPendingChallenge()) {
            return redirect()->to(site_url('backoffice/login'))
                ->with('error', lang('Backoffice.login_err_session'));
        }

        $code   = (string) $this->request->getPost('code');
        $result = $auth->verifyCode($code);

        if (! ($result['ok'] ?? false)) {
            $redirect = ! empty($result['expired'])
                ? redirect()->to(site_url('backoffice/login'))
                : redirect()->back();

            return $redirect->with('error', $result['error'] ?? lang('Backoffice.login_err_code_incorrect'));
        }

        return redirect()->to(site_url('backoffice'))
            ->with('success', lang('Backoffice.login_success'));
    }

    public function twoFactorResend()
    {
        $auth = new BackofficeAuth();
        if ($auth->isAuthenticated()) {
            return redirect()->to(site_url('backoffice'));
        }
        if (! $auth->hasPendingChallenge()) {
            return redirect()->to(site_url('backoffice/login'))
                ->with('error', lang('Backoffice.login_err_session'));
        }

        $result = $auth->resendCode();
        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/login/2fa'))
                ->with('error', $result['error'] ?? lang('Backoffice.login_err_code_send'));
        }

        return redirect()->to(site_url('backoffice/login/2fa'))
            ->with('success', lang('Backoffice.login_2fa_resent'))
            ->with('email_masked', $result['email_masked'] ?? '');
    }

    public function logout()
    {
        $auth = new BackofficeAuth();
        $auth->logout();

        return redirect()->to(site_url('backoffice/login'))
            ->with('success', lang('Backoffice.logout_success'));
    }
}
