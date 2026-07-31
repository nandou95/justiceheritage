<?php

namespace Modules\Complainant\Controllers;

use App\Controllers\BaseController;
use Modules\Appeals\Services\AppealService;
use Modules\Complainant\Services\CaseOverviewService;
use Modules\Complaint\Services\ComplaintService;

class Portal extends BaseController
{
    private CaseOverviewService $overview;

    public function __construct()
    {
        $this->overview = \Modules\Complainant\Config\Services::caseOverview();
    }

    public function index(): string
    {
        $complaintService     = new ComplaintService();
        $communalComplaints   = $complaintService->listCommunalComplaints();
        $provincialComplaints = $complaintService->listProvincialComplaints();
        $regionalComplaints   = $complaintService->listRegionalComplaints();
        $ministryComplaints   = $complaintService->listMinistryComplaints();
        $cases                = $this->overview->sampleCases();
        $byLevel              = [
            'communal'   => [],
            'provincial' => [],
            'regional'   => [],
        ];

        $stats               = $this->overview->dashboardStats($cases);
        $stats['communal']   = count($communalComplaints);
        $stats['provincial'] = count($provincialComplaints);
        $stats['regional']   = count($regionalComplaints);
        $stats['ministry']   = count($ministryComplaints);
        $stats['total']      = $stats['communal'] + $stats['provincial'] + $stats['regional'] + $stats['ministry'];

        return view('Modules\Complainant\Views\dashboard', [
            'title'                => lang('Portal.dash_title'),
            'active'               => 'overview',
            'user'                 => $this->overview->user(),
            'cases'                => $cases,
            'casesByLevel'         => $byLevel,
            'communalComplaints'   => $communalComplaints,
            'provincialComplaints' => $provincialComplaints,
            'regionalComplaints'   => $regionalComplaints,
            'ministryComplaints'   => $ministryComplaints,
            'stats'                => $stats,
            'activity'             => $this->overview->recentActivities($cases),
            'activeCase'           => $cases[0] ?? null,
            'appealCase'           => $cases[1] ?? null,
        ]);
    }

    public function complaints(): string
    {
        $complaintService = new ComplaintService();

        return view('Modules\Complainant\Views\complaints', [
            'title'      => lang('Portal.list_title'),
            'active'     => 'complaints',
            'user'       => $this->overview->user(),
            'complaints' => $complaintService->listCommunalComplaints(),
            'listStats'  => $complaintService->statsForJurisdictionLevel(1),
        ]);
    }

    public function createComplaint()
    {
        if ($this->request->is('post')) {
            $user = $this->overview->user();
            $subject = (string) ($this->request->getPost('parcel')
                ?: $this->request->getPost('subject')
                ?: lang('Portal.sample_subject'));
            $complaintNumber = 'JH-' . date('Y') . '-' . random_int(1000, 9999);

            (new ComplaintService())->submitComplaint([
                'subject' => $subject,
                'user'    => $user,
                'number'  => $complaintNumber,
            ]);

            service('notifications')->sendComplaintSubmitted(
                (string) $user['email'],
                (string) $user['name'],
                $complaintNumber,
                $subject
            );

            return redirect()->to('portal/complaints')->with('success', lang('Portal.new_demo'));
        }

        return view('Modules\Complainant\Views\complaint_new', [
            'title'     => lang('Portal.new_title'),
            'active'    => 'new',
            'user'      => $this->overview->user(),
            'locations' => $this->overview->sampleLocations(),
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

    public function showComplaint(string $id)
    {
        $case = $this->overview->findCase($id);

        if ($case === null) {
            $service = new ComplaintService();
            $court   = 'communal';
            $row     = $service->findCommunalComplaint($id);

            if ($row === null) {
                $row = $service->findProvincialComplaint($id);
                $court = 'provincial';
            }

            if ($row === null) {
                $row = $service->findRegionalComplaint($id);
                $court = 'regional';
            }

            if ($row === null) {
                $row = $service->findMinistryComplaint($id);
                $court = 'ministry';
            }

            if ($row === null) {
                return redirect()->to('/portal/complaints');
            }

            $case = [
                'id'           => $row['case_number'] !== '' ? $row['case_number'] : ('#' . $row['id']),
                'subject'      => $row['subject'],
                'summary'      => $row['description'],
                'status'       => 'submitted',
                'status_label' => $row['status'],
                'filed'        => $row['submission_date'],
                'updated'      => $row['created_at'],
                'court'        => $court,
                'court_label'  => $row['court_jurisdiction'],
                'magistrate'   => '—',
                'hearing'      => '',
                'location'     => $row['court_jurisdiction'],
                'respondents'  => [],
                'documents'    => [],
                'timeline'     => [
                    [
                        'label'   => $row['stage'] !== '' ? $row['stage'] : $row['status'],
                        'date'    => $row['submission_date'],
                        'note'    => $row['description'],
                        'done'    => true,
                        'current' => true,
                    ],
                ],
            ];
        }

        return view('Modules\Complainant\Views\complaint_show', [
            'title'  => lang('Portal.case_title'),
            'active' => 'complaints',
            'user'   => $this->overview->user(),
            'case'   => $case,
        ]);
    }

    public function provincialAppeal()
    {
        $complaintService     = new ComplaintService();
        $provincialComplaints = $complaintService->listProvincialComplaints();

        $cases = array_values(array_filter(
            $this->overview->sampleCases(),
            static fn (array $case): bool => $case['court'] === 'communal' && $case['status'] === 'judgment'
        ));

        if ($this->request->is('post')) {
            $user = $this->overview->user();
            $caseId = (string) $this->request->getPost('case_id');

            (new AppealService())->submitProvincial([
                'case_id' => $caseId,
                'user'    => $user,
            ]);

            service('notifications')->sendAppealSubmitted(
                (string) $user['email'],
                (string) $user['name'],
                lang('Portal.nav_provincial'),
                $caseId !== '' ? $caseId : 'N/A'
            );

            return redirect()->to('portal/appeals/provincial')->with('success', lang('Portal.prov_submit'));
        }

        return view('Modules\Complainant\Views\appeal_provincial', [
            'title'                => lang('Portal.prov_title'),
            'active'               => 'provincial',
            'user'                 => $this->overview->user(),
            'cases'                => $cases,
            'provincialComplaints' => $provincialComplaints,
            'listStats'            => $complaintService->statsForJurisdictionLevel(2),
        ]);
    }

    public function regionalAppeal()
    {
        $complaintService   = new ComplaintService();
        $regionalComplaints = $complaintService->listRegionalComplaints();

        $cases = array_values(array_filter(
            $this->overview->sampleCases(),
            static fn (array $case): bool => $case['court'] === 'provincial'
                && in_array($case['status'], ['judgment', 'hearing'], true)
        ));

        if ($this->request->is('post')) {
            $user = $this->overview->user();
            $caseId = (string) $this->request->getPost('case_id');

            (new AppealService())->submitRegional([
                'case_id' => $caseId,
                'user'    => $user,
            ]);

            service('notifications')->sendAppealSubmitted(
                (string) $user['email'],
                (string) $user['name'],
                lang('Portal.nav_regional'),
                $caseId !== '' ? $caseId : 'N/A'
            );

            return redirect()->to('portal/appeals/regional')->with('success', lang('Portal.reg_submit'));
        }

        return view('Modules\Complainant\Views\appeal_regional', [
            'title'              => lang('Portal.reg_title'),
            'active'             => 'regional',
            'user'               => $this->overview->user(),
            'cases'              => $cases,
            'regionalComplaints' => $regionalComplaints,
            'listStats'          => $complaintService->statsForJurisdictionLevel(3),
        ]);
    }

    public function ministry(): string
    {
        $complaintService = new ComplaintService();

        return view('Modules\Complainant\Views\ministry', [
            'title'              => lang('Portal.ministry_title'),
            'active'             => 'ministry',
            'user'               => $this->overview->user(),
            'ministryComplaints' => $complaintService->listMinistryComplaints(),
            'listStats'          => $complaintService->statsForJurisdictionLevel(4),
        ]);
    }

    public function profile(): string
    {
        return view('Modules\Complainant\Views\profile', [
            'title'  => lang('Portal.profile_title'),
            'active' => 'profile',
            'user'   => $this->overview->user(),
        ]);
    }
}
