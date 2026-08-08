<?php

namespace Modules\Complaint\Controllers;

use Modules\Complaint\Models\EtapePlainteModel;
use Modules\Complaint\Models\PlainteParcelleModel;
use Modules\Complaint\Models\PlainteRolePersonneModel;
use Modules\Complaint\Models\RolePersonneModel;
use Modules\Complaint\Models\StatutPlainteModel;
use Modules\Complaint\Models\TypeDocumentModel;
use Modules\Complaint\Services\BackofficeComplaintService;
use Modules\CourtJurisdiction\Models\CollineModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\CourtJurisdiction\Models\ZoneModel;
use Modules\People\Models\PersonneModel;

class Complaints extends \App\Controllers\BaseController
{
    private BackofficeComplaintService $service;

    public function __construct()
    {
        $this->service = new BackofficeComplaintService();
    }

    public function index()
    {
        $filters = [
            'province_id'           => $this->request->getGet('province_id'),
            'commune_id'            => $this->request->getGet('commune_id'),
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id'),
            'juridiction_id'        => $this->request->getGet('juridiction_id'),
            'statut_plainte_id'     => $this->request->getGet('statut_plainte_id'),
            'date_depot'            => $this->request->getGet('date_depot'),
        ];

        $provinceId = (int) ($filters['province_id'] ?? 0);
        $niveauId   = (int) ($filters['niveau_juridiction_id'] ?? 0);

        return view('Modules\Complaint\Views\complaints\index', [
            'title'         => lang('Backoffice.cmp_title'),
            'active'        => 'complaints-list',
            'items'         => $this->service->list($filters),
            'filters'       => $filters,
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'levels'        => (new NiveauJuridictionModel())->options(),
            'jurisdictions' => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => ! empty($filters['commune_id']) ? (int) $filters['commune_id'] : null,
            ]),
            'statuses' => (new StatutPlainteModel())->options($niveauId > 0 ? $niveauId : null, false),
            'user'     => $this->sampleUser(),
        ]);
    }

    public function create()
    {
        return view('Modules\Complaint\Views\complaints\form', array_merge($this->formLookups(), [
            'title'    => lang('Backoffice.cmp_create_title'),
            'active'   => 'complaints-list',
            'mode'     => 'create',
            'record'   => $this->emptyRecord(),
            'parcels'  => old('parcels') ?: [['localisation_parcelle' => '', 'superficie_maitre_carreau' => '', 'province_parcelle_id' => '', 'commune_parcelle_id' => '', 'zone_parcelle_id' => '', 'colline_parcelle_id' => '']],
            'docTypes' => [],
            'user'     => $this->sampleUser(),
        ]));
    }

    public function store()
    {
        $filesByType = $this->collectDocuments();
        $result      = $this->service->create($this->request->getPost(), $filesByType);
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cmp_err_save')]);
        }

        return redirect()->to(site_url('backoffice/complaints'))->with('success', lang('Backoffice.cmp_created'));
    }

    public function edit(int $id)
    {
        $record = $this->service->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/complaints'))->with('error', lang('Backoffice.cmp_err_not_found'));
        }

        $roles = new PlainteRolePersonneModel();
        $witnessId = (new RolePersonneModel())->findWitnessId();
        $parcels = old('parcels') ?: (new PlainteParcelleModel())->listByPlainte($id);
        if ($parcels === []) {
            $parcels = [['localisation_parcelle' => '', 'superficie_maitre_carreau' => '', 'province_parcelle_id' => '', 'commune_parcelle_id' => '', 'zone_parcelle_id' => '', 'colline_parcelle_id' => '']];
        }

        $record['complainant_ids'] = old('complainant_ids') ?: array_column($roles->listByPlainte($id, RolePersonneModel::ROLE_PLAIGNANT), 'personne_id');
        $record['defendant_ids']   = old('defendant_ids') ?: array_column($roles->listByPlainte($id, RolePersonneModel::ROLE_DEFENDANT), 'personne_id');
        $record['witness_ids']     = old('witness_ids') ?: ($witnessId ? array_column($roles->listByPlainte($id, $witnessId), 'personne_id') : []);
        $record['province_id']     = old('province_id') ?: ($record['province_id'] ?? '');
        $record['commune_id']      = old('commune_id') ?: ($record['commune_id'] ?? '');

        $niveauId = (int) (old('niveau_juridiction_id') ?: ($record['niveau_juridiction_id'] ?? 0));

        return view('Modules\Complaint\Views\complaints\form', array_merge($this->formLookups($record), [
            'title'    => lang('Backoffice.cmp_edit_title'),
            'active'   => 'complaints-list',
            'mode'     => 'edit',
            'record'   => $record,
            'parcels'  => $parcels,
            'docTypes' => $niveauId ? (new TypeDocumentModel())->listByNiveau($niveauId, true) : [],
            'user'     => $this->sampleUser(),
        ]));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost(), $this->collectDocuments());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.cmp_err_save')]);
        }

        return redirect()->to(site_url('backoffice/complaints'))->with('success', lang('Backoffice.cmp_updated'));
    }

    public function show(int $id)
    {
        $details = $this->service->details($id);
        if (! $details) {
            return redirect()->to(site_url('backoffice/complaints'))->with('error', lang('Backoffice.cmp_err_not_found'));
        }

        return view('Modules\Complaint\Views\complaints\show', array_merge($details, [
            'title'  => lang('Backoffice.cmp_details_title'),
            'active' => 'complaints-list',
            'user'   => $this->sampleUser(),
        ]));
    }

    public function documentTypes()
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

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private function formLookups(array $record = []): array
    {
        $provinceId = (int) (old('province_id') ?: ($record['province_id'] ?? 0));
        $communeId  = (int) (old('commune_id') ?: ($record['commune_id'] ?? 0));
        $niveauId   = (int) (old('niveau_juridiction_id') ?: ($record['niveau_juridiction_id'] ?? 0));

        // Complaints may only be filed at first-instance levels (is_recours = false).
        $levels = (new NiveauJuridictionModel())->options(false);
        if ($niveauId > 0) {
            $levelIds = array_map(static fn (array $opt): int => (int) $opt['id'], $levels);
            if (! in_array($niveauId, $levelIds, true)) {
                $current = (new NiveauJuridictionModel())->find($niveauId);
                if (is_array($current)) {
                    $levels[] = [
                        'id'    => $current['niveau_juridiction_id'],
                        'label' => $current['desc_niveau_juridiction'],
                    ];
                }
            }
        }

        return [
            'levels'        => $levels,
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'jurisdictions' => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => $communeId ?: null,
            ]),
            'people'     => (new PersonneModel())->options(),
            'hasWitness' => (new RolePersonneModel())->findWitnessId() !== null,
            'allZones'   => [],
            'allCollines'=> [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRecord(): array
    {
        return [
            'plainte_id'            => null,
            'objet'                 => old('objet'),
            'description'           => old('description'),
            'niveau_juridiction_id' => old('niveau_juridiction_id'),
            'province_id'           => old('province_id'),
            'commune_id'            => old('commune_id'),
            'juridiction_id'        => old('juridiction_id'),
            'complainant_ids'       => old('complainant_ids') ?: [],
            'defendant_ids'         => old('defendant_ids') ?: [],
            'witness_ids'           => old('witness_ids') ?: [],
        ];
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
