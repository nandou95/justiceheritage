<?php

namespace App\Filters;

use App\Libraries\BackofficeAccess;
use App\Libraries\BackofficeAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Require an authenticated back-office session and authorize every protected route
 * against the user's profile permissions (session + live fingerprint check).
 */
class BackofficeAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $path = trim($request->getUri()->getPath(), '/');

        // Allow index.php prefix if present.
        if (str_starts_with($path, 'index.php/')) {
            $path = substr($path, strlen('index.php/'));
        }

        // Strip install subdirectory so checks match /backoffice/...
        if (preg_match('#(?:^|/)(backoffice(?:/.*)?)$#', $path, $m)) {
            $path = $m[1];
        }

        $publicPrefixes = [
            'backoffice/login',
        ];

        foreach ($publicPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return null;
            }
        }

        // Logout must be reachable while authenticated (handled by controller).
        if ($path === 'backoffice/logout') {
            return null;
        }

        $auth = new BackofficeAuth();
        if (! $auth->isAuthenticated()) {
            return redirect()
                ->to(site_url('backoffice/login'))
                ->with('error', lang('Backoffice.login_err_required'));
        }

        return BackofficeAccess::authorizeRequest($request);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
