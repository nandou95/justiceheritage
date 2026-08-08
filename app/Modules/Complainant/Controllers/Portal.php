<?php

namespace Modules\Complainant\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Appeals\Services\AppealService;
use Modules\Complainant\Services\CaseOverviewService;
use Modules\Complaint\Models\TypeDocumentModel;
use Modules\Complaint\Services\BackofficeComplaintService;
use Modules\Complaint\Services\ComplaintService;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\People\Models\PersonneModel;

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
        $user       = $this->overview->user();
        $personneId = (int) ($user['personne_id'] ?? 0);

        if ($this->request->is('post')) {
            if ($personneId < 1) {
                return redirect()->back()->withInput()->with('errors', [lang('Portal.new_err_session')]);
            }

            $filesByType = $this->collectDocuments();
            $result      = (new BackofficeComplaintService())->create(
                $this->request->getPost(),
                $filesByType,
                [
                    'source'      => 'portal',
                    'personne_id' => $personneId,
                ]
            );

            if (! ($result['ok'] ?? false)) {
                return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cmp_err_save')]);
            }

            $numero  = (string) ($result['numero'] ?? '');
            $subject = trim((string) $this->request->getPost('objet'));
            if ($user['email'] !== '') {
                service('notifications')->sendComplaintSubmitted(
                    (string) $user['email'],
                    (string) $user['name'],
                    $numero !== '' ? $numero : ('#' . (int) ($result['id'] ?? 0)),
                    $subject !== '' ? $subject : lang('Portal.sample_subject')
                );
            }

            return redirect()->to('portal/complaints')->with(
                'success',
                lang('Portal.new_success', [$numero !== '' ? $numero : (string) ($result['id'] ?? '')])
            );
        }

        $provinceId = (int) (old('province_id') ?: 0);
        $communeId  = (int) (old('commune_id') ?: 0);
        $niveauId   = (int) (old('niveau_juridiction_id') ?: 0);

        $people = (new PersonneModel())->options();
        // Creator is always a complainant; keep them out of the optional co-plaintiff picker.
        $peopleForParties = array_values(array_filter(
            $people,
            static fn (array $opt): bool => (int) $opt['id'] !== $personneId
        ));

        return view('Modules\Complainant\Views\complaint_new', [
            'title'         => lang('Portal.new_title'),
            'active'        => 'new',
            'user'          => $user,
            'record'        => [
                'objet'                 => old('objet'),
                'description'           => old('description'),
                'niveau_juridiction_id' => old('niveau_juridiction_id'),
                'province_id'           => old('province_id'),
                'commune_id'            => old('commune_id'),
                'juridiction_id'        => old('juridiction_id'),
                'complainant_ids'       => old('complainant_ids') ?: [],
                'defendant_ids'         => old('defendant_ids') ?: [],
                'witness_ids'           => old('witness_ids') ?: [],
            ],
            'parcels' => old('parcels') ?: [[
                'localisation_parcelle'     => '',
                'superficie_maitre_carreau' => '',
                'province_parcelle_id'      => '',
                'commune_parcelle_id'       => '',
                'zone_parcelle_id'          => '',
                'colline_parcelle_id'       => '',
            ]],
            'levels'        => (new NiveauJuridictionModel())->options(false),
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'jurisdictions' => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => $communeId ?: null,
                'active_only'           => true,
            ]),
            'people'     => $peopleForParties,
            'hasWitness' => false,
            'docTypes'   => $niveauId ? (new TypeDocumentModel())->listByNiveau($niveauId, true) : [],
        ]);
    }

    public function courtJurisdictionOptions(): ResponseInterface
    {
        $options = (new JuridictionModel())->options([
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id') ? (int) $this->request->getGet('niveau_juridiction_id') : null,
            'province_id'           => $this->request->getGet('province_id') ? (int) $this->request->getGet('province_id') : null,
            'commune_id'            => $this->request->getGet('commune_id') ? (int) $this->request->getGet('commune_id') : null,
            'active_only'           => true,
        ]);

        return $this->response->setJSON(['ok' => true, 'options' => $options]);
    }

    public function documentTypes(): ResponseInterface
    {
        $niveauId = (int) ($this->request->getGet('niveau_juridiction_id') ?? 0);

        return $this->response->setJSON([
            'ok'    => true,
            'types' => $niveauId ? (new TypeDocumentModel())->listByNiveau($niveauId, true) : [],
        ]);
    }

    /**
     * @return array<int, list<\CodeIgniter\HTTP\Files\UploadedFile|null>>
     */
    private function collectDocuments(): array
    {
        $files = $this->request->getFiles();
        $docs  = $files['documents'] ?? [];
        if (! is_array($docs)) {
            return [];
        }

        $out = [];
        foreach ($docs as $typeId => $fileOrList) {
            $list = is_array($fileOrList) ? $fileOrList : [$fileOrList];
            $out[(int) $typeId] = $list;
        }

        return $out;
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
