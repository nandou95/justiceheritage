<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\App;

class LocaleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        /** @var App $config */
        $config  = config('App');
        $session = session();
        $locale  = (string) ($session->get('locale') ?? $config->defaultLocale);

        if (! in_array($locale, $config->supportedLocales, true)) {
            $locale = $config->defaultLocale;
        }

        $request->setLocale($locale);
        service('language')->setLocale($locale);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
