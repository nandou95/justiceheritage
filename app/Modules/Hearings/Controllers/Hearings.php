<?php

namespace Modules\Hearings\Controllers;

use Modules\Administration\Models\ProfilModel;
use Modules\Complaint\Models\PlainteRolePersonneModel;
use Modules\CourtJurisdiction\Models\CollineModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\CourtJurisdiction\Models\ZoneModel;
use Modules\Hearings\Models\StatutAudienceModel;
use Modules\Hearings\Services\BackofficeHearingService;

class Hearings extends \App\Controllers\BaseController
{
    private BackofficeHearingService $service;

    public function __construct()
    {
        $this->service = new BackofficeHearingService();
    }

    public function index()
    {
        $filters = [
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id'),
            'province_id'           => $this->request->getGet('province_id'),
            'commune_id'            => $this->request->getGet('commune_id'),
            'juridiction_id'        => $this->request->getGet('juridiction_id'),
            'date_audience'         => $this->request->getGet('date_audience'),
            'statut_audience_id'    => $this->request->getGet('statut_audience_id'),
        ];

        $provinceId = (int) ($filters['province_id'] ?? 0);
        $niveauId   = (int) ($filters['niveau_juridiction_id'] ?? 0);

        return view('Modules\Hearings\Views\hearings\index', [
            'title'         => lang('Backoffice.hrg_title'),
            'active'        => 'hearings-list',
            'items'         => $this->service->list($filters),
            'filters'       => $filters,
            'levels'        => (new NiveauJuridictionModel())->options(),
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'jurisdictions' => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => ! empty($filters['commune_id']) ? (int) $filters['commune_id'] : null,
            ]),
            'statuses' => (new StatutAudienceModel())->options(),
            'user'     => $this->sampleUser(),
        ]);
    }

    public function create()
    {
        $juridictionId = (int) (old('juridiction_audience_id') ?: 0);
        $provinceId    = (int) (old('province_audience_id') ?: 0);
        $communeId     = (int) (old('commune_audience_id') ?: 0);
        $zoneId        = (int) (old('zone_audience_id') ?: 0);
        $niveauId      = (int) (old('niveau_juridiction_id') ?: 0);

        return view('Modules\Hearings\Views\hearings\form', [
            'title'         => lang('Backoffice.hrg_create_title'),
            'active'        => 'hearings-list',
            'record'        => [
                'niveau_juridiction_id'   => old('niveau_juridiction_id'),
                'province_audience_id'    => old('province_audience_id'),
                'commune_audience_id'     => old('commune_audience_id'),
                'zone_audience_id'        => old('zone_audience_id'),
                'colline_audience_id'     => old('colline_audience_id'),
                'juridiction_audience_id' => old('juridiction_audience_id'),
                'lieu_audience'           => old('lieu_audience'),
                'date_audience'           => old('date_audience'),
                'heure_audience'          => old('heure_audience'),
                'plainte_ids'             => old('plainte_ids') ?: [],
            ],
            'levels'        => (new NiveauJuridictionModel())->options(),
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'zones'         => $communeId ? (new ZoneModel())->optionsByCommune($communeId) : [],
            'collines'      => $zoneId ? (new CollineModel())->optionsByZone($zoneId) : [],
            'jurisdictions' => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => $communeId ?: null,
            ]),
            'complaints' => $this->service->eligibleComplaintOptions($juridictionId ?: null),
            'user'       => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.hrg_err_save')]);
        }

        return redirect()->to(site_url('backoffice/hearings'))->with('success', lang('Backoffice.hrg_created'));
    }

    public function show(int $id)
    {
        $details = $this->service->details($id);
        if (! $details) {
            return redirect()->to(site_url('backoffice/hearings'))->with('error', lang('Backoffice.hrg_err_not_found'));
        }

        return view('Modules\Hearings\Views\hearings\show', array_merge($details, [
            'title'  => lang('Backoffice.hrg_details_title'),
            'active' => 'hearings-list',
            'user'   => $this->sampleUser(),
        ]));
    }

    public function assignments(int $id)
    {
        $hearing = $this->service->find($id);
        if (! $hearing) {
            return redirect()->to(site_url('backoffice/hearings'))->with('error', lang('Backoffice.hrg_err_not_found'));
        }

        return view('Modules\Hearings\Views\hearings\assignments', [
            'title'       => lang('Backoffice.hrg_assign_title'),
            'active'      => 'hearings-list',
            'hearing'     => $hearing,
            'items'       => $this->service->listAssignments($id),
            'users'       => $this->service->courtUserOptions((int) ($hearing['juridiction_audience_id'] ?? 0)),
            'profiles'    => (new ProfilModel())->options(),
            'canProcess'  => $this->service->canProcess($id),
            'user'        => $this->sampleUser(),
        ]);
    }

    public function storeAssignment(int $id)
    {
        $result = $this->service->createAssignment($id, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.hrg_err_assignment_save')]);
        }

        return redirect()->to(site_url('backoffice/hearings/' . $id . '/assignments'))->with('success', lang('Backoffice.hrg_assign_created'));
    }

    public function updateAssignment(int $id, int $assignmentId)
    {
        $result = $this->service->updateAssignment($id, $assignmentId, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.hrg_err_assignment_save')]);
        }

        return redirect()->to(site_url('backoffice/hearings/' . $id . '/assignments'))->with('success', lang('Backoffice.hrg_assign_updated'));
    }

    public function toggleAssignment(int $id, int $assignmentId)
    {
        $result = $this->service->toggleAssignment($id, $assignmentId);
        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/hearings/' . $id . '/assignments'))
                ->with('error', $result['errors'][0] ?? lang('Backoffice.hrg_err_assignment_save'));
        }

        return redirect()->to(site_url('backoffice/hearings/' . $id . '/assignments'))->with(
            'success',
            ($result['activated'] ?? false) ? lang('Backoffice.hrg_assign_activated') : lang('Backoffice.hrg_assign_deactivated')
        );
    }

    public function process(int $id)
    {
        $hearing = $this->service->find($id);
        if (! $hearing) {
            return redirect()->to(site_url('backoffice/hearings'))->with('error', lang('Backoffice.hrg_err_not_found'));
        }

        if (! $this->service->canProcess($id)) {
            return redirect()->to(site_url('backoffice/hearings/' . $id . '/assignments'))
                ->with('warning', lang('Backoffice.hrg_err_staff_required'));
        }

        $complaints = $this->service->details($id)['complaints'] ?? [];
        $partiesByComplaint = [];
        $roles = new PlainteRolePersonneModel();
        foreach ($complaints as $c) {
            $partiesByComplaint[(int) $c['audience_plainte_id']] = $roles->listByPlainte((int) $c['plainte_id']);
        }

        return view('Modules\Hearings\Views\hearings\process', [
            'title'              => lang('Backoffice.hrg_process_title'),
            'active'             => 'hearings-list',
            'hearing'            => $hearing,
            'complaints'         => $complaints,
            'partiesByComplaint' => $partiesByComplaint,
            'user'               => $this->sampleUser(),
        ]);
    }

    public function storeProcess(int $id)
    {
        $files = $this->request->getFiles();
        $result = $this->service->process($id, $this->request->getPost(), $files);
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.hrg_err_process')]);
        }

        return redirect()->to(site_url('backoffice/hearings/' . $id))->with('success', lang('Backoffice.hrg_processed'));
    }

    public function eligibleComplaints()
    {
        $juridictionId = (int) ($this->request->getGet('juridiction_id') ?? 0);

        return $this->response->setJSON([
            'ok'      => true,
            'options' => $this->service->eligibleComplaintOptions($juridictionId ?: null),
        ]);
    }

    /**
     * @return array{name:string,role:string}
     */
    private function sampleUser(): array
    {
        return [
            'name' => lang('Backoffice.user_sample'),
            'role' => lang('Backoffice.role_sample'),
        ];
    }
}
