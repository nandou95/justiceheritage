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
