<?php

namespace App\Controllers;

class ComplainantPortal extends BaseController
{
    private function user(): array
    {
        return [
            'name'  => session()->get('portal_user_name') ?? 'Aline Ndayishimiye',
            'email' => session()->get('portal_user_email') ?? 'aline.ndayishimiye@example.bi',
            'phone' => '+257 79 000 000',
            'id'    => 'CNI-2018-004512',
        ];
    }

    private function sampleCases(): array
    {
        return [
            [
                'id'           => 'JH-2026-0142',
                'subject'      => lang('Portal.sample_subject'),
                'court'        => 'communal',
                'court_label'  => lang('Portal.court_communal'),
                'status'       => 'hearing',
                'status_label' => lang('Portal.status_hearing'),
                'updated'      => '2026-07-20',
                'hearing'      => '2026-08-05 · 09:30',
                'hearing_place'=> 'Communal Court of Gitega',
                'magistrate'   => 'Hon. Claire Habonimana',
                'filed'        => '2026-06-12',
                'location'     => 'Gitega · Giheta · Colline Nyabihanga',
                'respondents'  => 'Eric Ndayishimiye, Jeanne Barakamfitiye',
                'summary'      => 'Dispute over the division of ancestral land parcels among heirs following the death of the family patriarch.',
                'appeal_days'  => null,
                'timeline'     => [
                    ['label' => lang('Portal.status_submitted'), 'date' => '2026-06-12', 'note' => lang('Portal.case_note_submitted'), 'done' => true],
                    ['label' => lang('Portal.status_verified'), 'date' => '2026-06-18', 'note' => lang('Portal.case_note_verified'), 'done' => true],
                    ['label' => lang('Portal.sample_activity_2'), 'date' => '2026-06-22', 'note' => lang('Portal.case_note_assigned'), 'done' => true],
                    ['label' => lang('Portal.status_hearing'), 'date' => '2026-07-20', 'note' => lang('Portal.case_note_hearing'), 'done' => true, 'current' => true],
                    ['label' => lang('Portal.status_judgment'), 'date' => null, 'note' => lang('Portal.case_note_pending'), 'done' => false],
                ],
                'documents'    => [
                    ['name' => lang('Portal.sample_doc_1'), 'type' => 'PDF', 'size' => '240 KB'],
                    ['name' => lang('Portal.sample_doc_2'), 'type' => 'JPG', 'size' => '1.1 MB'],
                    ['name' => lang('Portal.sample_doc_3'), 'type' => 'PDF', 'size' => '520 KB'],
                ],
            ],
            [
                'id'           => 'JH-2026-0098',
                'subject'      => lang('Portal.sample_subject'),
                'court'        => 'provincial',
                'court_label'  => lang('Portal.court_provincial'),
                'status'       => 'judgment',
                'status_label' => lang('Portal.status_judgment'),
                'updated'      => '2026-07-10',
                'hearing'      => null,
                'hearing_place'=> null,
                'magistrate'   => 'Hon. Pacifique Nkurunziza',
                'filed'        => '2026-03-02',
                'location'     => 'Gitega · Makebuko · Colline Gasenyi',
                'respondents'  => 'Josephine Hakizimana',
                'summary'      => 'Provincial appeal concerning the allocation of two inheritance land parcels after communal judgment.',
                'appeal_days'  => 8,
                'timeline'     => [
                    ['label' => lang('Portal.status_submitted'), 'date' => '2026-03-02', 'note' => lang('Portal.case_note_submitted'), 'done' => true],
                    ['label' => lang('Portal.status_judgment'), 'date' => '2026-05-14', 'note' => lang('Portal.case_note_communal_judgment'), 'done' => true],
                    ['label' => lang('Portal.status_appeal'), 'date' => '2026-05-28', 'note' => lang('Portal.case_note_appeal_filed'), 'done' => true],
                    ['label' => lang('Portal.status_judgment'), 'date' => '2026-07-10', 'note' => lang('Portal.case_note_provincial_judgment'), 'done' => true, 'current' => true],
                ],
                'documents'    => [
                    ['name' => lang('Portal.sample_doc_1'), 'type' => 'PDF', 'size' => '210 KB'],
                    ['name' => lang('Portal.sample_doc_2'), 'type' => 'PDF', 'size' => '880 KB'],
                ],
            ],
        ];
    }

    private function findCase(string $id): ?array
    {
        foreach ($this->sampleCases() as $case) {
            if ($case['id'] === $id) {
                return $case;
            }
        }

        return null;
    }

    public function index(): string
    {
        $cases = $this->sampleCases();

        return view('portal/dashboard', [
            'title'  => lang('Portal.dash_title'),
            'active' => 'overview',
            'user'   => $this->user(),
            'cases'  => $cases,
            'activeCase' => $cases[0] ?? null,
            'appealCase' => $cases[1] ?? null,
            'activity' => [
                ['text' => lang('Portal.sample_activity_3'), 'date' => '2026-07-20', 'ref' => 'JH-2026-0142'],
                ['text' => lang('Portal.sample_activity_2'), 'date' => '2026-06-22', 'ref' => 'JH-2026-0142'],
                ['text' => lang('Portal.sample_activity_1'), 'date' => '2026-06-18', 'ref' => 'JH-2026-0142'],
            ],
        ]);
    }

    public function complaints(): string
    {
        return view('portal/complaints', [
            'title'  => lang('Portal.list_title'),
            'active' => 'complaints',
            'user'   => $this->user(),
            'cases'  => $this->sampleCases(),
        ]);
    }

    public function createComplaint(): string
    {
        return view('portal/complaint_new', [
            'title'     => lang('Portal.new_title'),
            'active'    => 'new',
            'user'      => $this->user(),
            'locations' => $this->sampleLocations(),
            'courts'    => [
                'communal_gitega'   => lang('Portal.court_opt_communal_gitega'),
                'communal_giheta'   => lang('Portal.court_opt_communal_giheta'),
                'communal_makebuko' => lang('Portal.court_opt_communal_makebuko'),
                'communal_bujumbura'=> lang('Portal.court_opt_communal_bujumbura'),
                'provincial_gitega' => lang('Portal.court_opt_provincial_gitega'),
            ],
            'docTypes'  => [
                'national_id'  => lang('Portal.doc_type_id'),
                'parcel_sketch'=> lang('Portal.doc_type_sketch'),
                'land_title'   => lang('Portal.doc_type_title'),
                'succession'   => lang('Portal.doc_type_succession'),
                'other'        => lang('Portal.doc_type_other'),
            ],
        ]);
    }

    private function sampleLocations(): array
    {
        return [
            'Gitega' => [
                'Giheta' => [
                    'Zone Giheta' => ['Nyabihanga', 'Mubuga', 'Rwingoma'],
                    'Zone Mugera' => ['Mugera', 'Gasenyi', 'Kirundo'],
                ],
                'Makebuko' => [
                    'Zone Makebuko' => ['Gasenyi', 'Nyamugari', 'Rutoke'],
                    'Zone Bukirasazi' => ['Bukirasazi', 'Nyabikere'],
                ],
                'Gitega' => [
                    'Zone Urbaine' => ['Nyamugari', 'Musinzira', 'Bwoga'],
                    'Zone Rurengera' => ['Rurengera', 'Kibimba'],
                ],
            ],
            'Bujumbura Mairie' => [
                'Mukaza' => [
                    'Zone Rohero' => ['Rohero I', 'Rohero II', 'Kabondo'],
                    'Zone Buyenzi' => ['Buyenzi I', 'Buyenzi II'],
                ],
                'Ntahangwa' => [
                    'Zone Cibitoke' => ['Cibitoke', 'Kinama'],
                    'Zone Kamenge' => ['Kamenge Nord', 'Kamenge Sud'],
                ],
            ],
            'Ngozi' => [
                'Ngozi' => [
                    'Zone Urbaine' => ['Kiganda', 'Gatabo'],
                    'Zone Busiga' => ['Busiga', 'Mwumba'],
                ],
                'Kayanza' => [
                    'Zone Kayanza' => ['Gatabo', 'Rango'],
                ],
            ],
        ];
    }

    public function showComplaint(string $id)
    {
        $case = $this->findCase($id);

        if ($case === null) {
            return redirect()->to('/portal/complaints');
        }

        return view('portal/complaint_show', [
            'title'  => lang('Portal.case_title'),
            'active' => 'complaints',
            'user'   => $this->user(),
            'case'   => $case,
        ]);
    }

    public function provincialAppeal(): string
    {
        return view('portal/appeal_provincial', [
            'title'  => lang('Portal.prov_title'),
            'active' => 'provincial',
            'user'   => $this->user(),
            'cases'  => $this->sampleCases(),
        ]);
    }

    public function regionalAppeal(): string
    {
        return view('portal/appeal_regional', [
            'title'  => lang('Portal.reg_title'),
            'active' => 'regional',
            'user'   => $this->user(),
            'cases'  => $this->sampleCases(),
        ]);
    }

    public function profile(): string
    {
        return view('portal/profile', [
            'title'  => lang('Portal.profile_title'),
            'active' => 'profile',
            'user'   => $this->user(),
        ]);
    }

    public function enterDemo()
    {
        session()->set('portal_user_name', 'Aline Ndayishimiye');
        session()->set('portal_demo', true);

        return redirect()->to('/portal');
    }
}
