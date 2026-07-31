<?php

namespace Modules\Administration\Controllers;

class Backoffice extends \App\Controllers\BaseController
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

    /**
     * Map module slugs to navigation language keys for page titles / active state.
     */
    private function moduleTitles(): array
    {
        return [
            'users'                       => 'Backoffice.nav_users',
            'roles'                       => 'Backoffice.nav_roles',
            'permissions'                 => 'Backoffice.nav_permissions',
            'court-jurisdictions'         => 'Backoffice.nav_court_jurisdictions',
            'court-jurisdiction-config'   => 'Backoffice.nav_court_jurisdiction_config',
            'jurisdiction-levels'         => 'Backoffice.nav_jurisdiction_levels',
            'jurisdiction-level-config'   => 'Backoffice.nav_jurisdiction_level_config',
            'people'                      => 'Backoffice.nav_people',
            'complaint-stages'            => 'Backoffice.nav_complaint_stages',
            'complaint-stage-config'      => 'Backoffice.nav_complaint_stage_config',
            'complaint-statuses'          => 'Backoffice.nav_complaint_statuses',
            'document-types'              => 'Backoffice.nav_document_types',
            'complaints-list'             => 'Backoffice.nav_complaints_list',
            'provincial-appeals'          => 'Backoffice.nav_provincial_appeals',
            'regional-appeals'            => 'Backoffice.nav_regional_appeals',
            'ministry-appeals'            => 'Backoffice.nav_ministry_appeals',
            'summons-list'                => 'Backoffice.nav_summons_list',
            'summons-statuses'            => 'Backoffice.nav_summons_statuses',
            'summons-communal'            => 'Backoffice.nav_summons_communal',
            'summons-provincial'          => 'Backoffice.nav_summons_provincial',
            'summons-ministry'            => 'Backoffice.nav_summons_ministry',
            'hearings-list'               => 'Backoffice.nav_hearings_list',
            'hearing-statuses'            => 'Backoffice.nav_hearing_statuses',
            'hearings-communal'           => 'Backoffice.nav_hearings_communal',
            'hearings-provincial'         => 'Backoffice.nav_hearings_provincial',
            'hearings-ministry'           => 'Backoffice.nav_hearings_ministry',
            'verdict-types'               => 'Backoffice.nav_verdict_types',
            'verdicts-list'               => 'Backoffice.nav_verdicts_list',
            'verdicts-communal'           => 'Backoffice.nav_verdicts_communal',
            'verdicts-provincial'         => 'Backoffice.nav_verdicts_provincial',
            'verdicts-ministry'           => 'Backoffice.nav_verdicts_ministry',
            'transfer-statuses'           => 'Backoffice.nav_transfer_statuses',
            'case-transfers-list'         => 'Backoffice.nav_case_transfers_list',
            'notifications'               => 'Backoffice.nav_notifications',
            'logs'                        => 'Backoffice.nav_logs',
            // Legacy dashboard service keys
            'reception'                   => 'Backoffice.svc_reception_title',
            'summons'                     => 'Backoffice.svc_summons_title',
            'hearing'                     => 'Backoffice.svc_hearing_title',
            'assign'                      => 'Backoffice.svc_assign_title',
            'evidence'                    => 'Backoffice.svc_evidence_title',
            'judgment'                    => 'Backoffice.svc_judgment_title',
            'appeal'                      => 'Backoffice.svc_appeal_title',
            'ministry'                    => 'Backoffice.svc_ministry_title',
            'jurisdictions'               => 'Backoffice.svc_juris_title',
            'notify'                      => 'Backoffice.svc_notify_title',
        ];
    }

    public function index()
    {
        $tab = $this->request->getGet('tab') === 'admin' ? 'admin' : 'court';

        return view('Modules\Administration\Views\portal', [
            'title'    => lang('Backoffice.page_title'),
            'active'   => 'dashboard',
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
        $titles = $this->moduleTitles();
        $titleKey = $titles[$key] ?? null;
        $moduleTitle = $titleKey ? lang($titleKey) : ucfirst(str_replace(['_', '-'], ' ', $key));

        return view('Modules\Administration\Views\module', [
            'title'        => $moduleTitle . ' — ' . lang('Backoffice.app_name'),
            'active'       => $key,
            'key'          => $key,
            'moduleTitle'  => $moduleTitle,
            'user'         => [
                'name' => lang('Backoffice.user_sample'),
                'role' => lang('Backoffice.role_sample'),
            ],
        ]);
    }
}
