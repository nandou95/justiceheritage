<?php

namespace App\Controllers;

class Backoffice extends BaseController
{
    private function services(string $tab): array
    {
        $court = [
            [
                'key'      => 'reception',
                'title'    => lang('Backoffice.svc_reception_title'),
                'desc'     => lang('Backoffice.svc_reception_desc'),
                'rating'   => '4.9',
                'users'    => '128',
                'traffic'  => 'high',
                'pending'  => 14,
                'icon'     => 'inbox',
            ],
            [
                'key'      => 'summons',
                'title'    => lang('Backoffice.svc_summons_title'),
                'desc'     => lang('Backoffice.svc_summons_desc'),
                'rating'   => '4.7',
                'users'    => '96',
                'traffic'  => 'medium',
                'pending'  => 8,
                'icon'     => 'mail',
            ],
            [
                'key'      => 'hearing',
                'title'    => lang('Backoffice.svc_hearing_title'),
                'desc'     => lang('Backoffice.svc_hearing_desc'),
                'rating'   => '4.8',
                'users'    => '112',
                'traffic'  => 'high',
                'pending'  => 11,
                'icon'     => 'calendar',
            ],
            [
                'key'      => 'assign',
                'title'    => lang('Backoffice.svc_assign_title'),
                'desc'     => lang('Backoffice.svc_assign_desc'),
                'rating'   => '4.6',
                'users'    => '54',
                'traffic'  => 'medium',
                'pending'  => 5,
                'icon'     => 'users',
            ],
            [
                'key'      => 'evidence',
                'title'    => lang('Backoffice.svc_evidence_title'),
                'desc'     => lang('Backoffice.svc_evidence_desc'),
                'rating'   => '4.8',
                'users'    => '87',
                'traffic'  => 'medium',
                'pending'  => 9,
                'icon'     => 'folder',
            ],
            [
                'key'      => 'judgment',
                'title'    => lang('Backoffice.svc_judgment_title'),
                'desc'     => lang('Backoffice.svc_judgment_desc'),
                'rating'   => '4.9',
                'users'    => '73',
                'traffic'  => 'low',
                'pending'  => 3,
                'icon'     => 'scale',
            ],
            [
                'key'      => 'appeal',
                'title'    => lang('Backoffice.svc_appeal_title'),
                'desc'     => lang('Backoffice.svc_appeal_desc'),
                'rating'   => '4.7',
                'users'    => '61',
                'traffic'  => 'high',
                'pending'  => 7,
                'icon'     => 'appeal',
            ],
            [
                'key'      => 'ministry',
                'title'    => lang('Backoffice.svc_ministry_title'),
                'desc'     => lang('Backoffice.svc_ministry_desc'),
                'rating'   => '4.5',
                'users'    => '22',
                'traffic'  => 'low',
                'pending'  => 2,
                'icon'     => 'building',
            ],
        ];

        $admin = [
            [
                'key'      => 'users',
                'title'    => lang('Backoffice.svc_users_title'),
                'desc'     => lang('Backoffice.svc_users_desc'),
                'rating'   => '4.8',
                'users'    => '18',
                'traffic'  => 'medium',
                'pending'  => 4,
                'icon'     => 'shield',
            ],
            [
                'key'      => 'jurisdictions',
                'title'    => lang('Backoffice.svc_juris_title'),
                'desc'     => lang('Backoffice.svc_juris_desc'),
                'rating'   => '4.6',
                'users'    => '12',
                'traffic'  => 'low',
                'pending'  => 1,
                'icon'     => 'map',
            ],
            [
                'key'      => 'logs',
                'title'    => lang('Backoffice.svc_logs_title'),
                'desc'     => lang('Backoffice.svc_logs_desc'),
                'rating'   => '4.9',
                'users'    => '9',
                'traffic'  => 'medium',
                'pending'  => 0,
                'icon'     => 'list',
            ],
            [
                'key'      => 'notify',
                'title'    => lang('Backoffice.svc_notify_title'),
                'desc'     => lang('Backoffice.svc_notify_desc'),
                'rating'   => '4.7',
                'users'    => '31',
                'traffic'  => 'high',
                'pending'  => 6,
                'icon'     => 'bell',
            ],
        ];

        return $tab === 'admin' ? $admin : $court;
    }

    public function index()
    {
        $tab = $this->request->getGet('tab') === 'admin' ? 'admin' : 'court';

        return view('backoffice/portal', [
            'title'    => lang('Backoffice.page_title'),
            'active'   => 'portal',
            'tab'      => $tab,
            'services' => $this->services($tab),
            'user'     => [
                'name' => lang('Backoffice.user_sample'),
                'role' => lang('Backoffice.role_sample'),
            ],
        ]);
    }

    public function module(string $key)
    {
        return view('backoffice/module', [
            'title'  => lang('Backoffice.page_title'),
            'active' => 'portal',
            'key'    => $key,
            'user'   => [
                'name' => lang('Backoffice.user_sample'),
                'role' => lang('Backoffice.role_sample'),
            ],
        ]);
    }
}
