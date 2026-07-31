<?php

namespace App\Filters;

use App\Libraries\ComplainantAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ComplainantAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = new ComplainantAuth();
        if ($auth->isAuthenticated()) {
            return null;
        }

        return redirect()->to(site_url('login'))->with('error', lang('Site.login_err_required'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
