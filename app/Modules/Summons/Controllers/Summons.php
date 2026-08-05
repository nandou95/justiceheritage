<?php

namespace Modules\Summons\Controllers;

use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\CourtJurisdiction\Models\ZoneModel;
use Modules\CourtJurisdiction\Models\CollineModel;
use Modules\Summons\Services\BackofficeSummonsService;

class Summons extends \App\Controllers\BaseController
{
    private BackofficeSummonsService $service;

    public function __construct()
    {
        $this->service = new BackofficeSummonsService();
    }

    public function index()
    {
        $filters = [
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id'),
            'province_id'           => $this->request->getGet('province_id'),
            'commune_id'            => $this->request->getGet('commune_id'),
            'juridiction_id'        => $this->request->getGet('juridiction_id'),
            'date_audience'         => $this->request->getGet('date_audience'),
        ];

        $provinceId = (int) ($filters['province_id'] ?? 0);
        $niveauId   = (int) ($filters['niveau_juridiction_id'] ?? 0);

        return view('Modules\Summons\Views\summons\index', [
            'title'         => lang('Backoffice.sum_title'),
            'active'        => 'summons-list',
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
            'user' => $this->sampleUser(),
        ]);
    }

    public function pending()
    {
        $filters = [
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id'),
            'province_id'           => $this->request->getGet('province_id'),
            'commune_id'            => $this->request->getGet('commune_id'),
            'juridiction_id'        => $this->request->getGet('juridiction_id'),
            'date_depot'            => $this->request->getGet('date_depot'),
        ];

        $provinceId = (int) ($filters['province_id'] ?? 0);
        $niveauId   = (int) ($filters['niveau_juridiction_id'] ?? 0);

        return view('Modules\Summons\Views\summons\pending', [
            'title'         => lang('Backoffice.sum_pending_title'),
            'active'        => 'summons-list',
            'items'         => $this->service->listPendingComplaints($filters),
            'filters'       => $filters,
            'levels'        => (new NiveauJuridictionModel())->options(),
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'jurisdictions' => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => ! empty($filters['commune_id']) ? (int) $filters['commune_id'] : null,
            ]),
            'user' => $this->sampleUser(),
        ]);
    }

    public function create(int $plainteId)
    {
        $complaint = $this->service->findEligibleComplaint($plainteId);
        if (! $complaint) {
            return redirect()->to(site_url('backoffice/summons/pending'))
                ->with('error', lang('Backoffice.sum_err_not_eligible'));
        }

        $provinceId = (int) (old('province_lieu_audience_id') ?: ($complaint['province_id'] ?? 0));
        $communeId  = (int) (old('commune_lieu_audience_id') ?: ($complaint['commune_id'] ?? 0));
        $zoneId     = (int) (old('zone_lieu_audience_id') ?: 0);
        $niveauId   = (int) ($complaint['niveau_juridiction_id'] ?? 0);

        return view('Modules\Summons\Views\summons\form', [
            'title'         => lang('Backoffice.sum_create_title'),
            'active'        => 'summons-list',
            'complaint'     => $complaint,
            'record'        => [
                'date_audience'                => old('date_audience'),
                'heure_audience'               => old('heure_audience'),
                'juridiction_lieu_audience_id' => old('juridiction_lieu_audience_id') ?: ($complaint['juridiction_id'] ?? ''),
                'province_lieu_audience_id'    => old('province_lieu_audience_id') ?: ($complaint['province_id'] ?? ''),
                'commune_lieu_audience_id'     => old('commune_lieu_audience_id') ?: ($complaint['commune_id'] ?? ''),
                'zone_lieu_audience_id'        => old('zone_lieu_audience_id'),
                'colline_lieu_audience_id'     => old('colline_lieu_audience_id'),
                'lieu_audience'                => old('lieu_audience'),
                'observations'                 => old('observations'),
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
            'user' => $this->sampleUser(),
        ]);
    }

    public function store(int $plainteId)
    {
        $result = $this->service->create($plainteId, $this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.sum_err_save')]);
        }

        return redirect()->to(site_url('backoffice/summons'))->with('success', lang('Backoffice.sum_created'));
    }

    public function show(int $id)
    {
        $details = $this->service->details($id);
        if (! $details) {
            return redirect()->to(site_url('backoffice/summons'))->with('error', lang('Backoffice.sum_err_not_found'));
        }

        return view('Modules\Summons\Views\summons\show', array_merge($details, [
            'title'  => lang('Backoffice.sum_details_title'),
            'active' => 'summons-list',
            'user'   => $this->sampleUser(),
        ]));
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
