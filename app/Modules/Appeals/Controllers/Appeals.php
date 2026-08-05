<?php

namespace Modules\Appeals\Controllers;

use Modules\Appeals\Services\BackofficeAppealService;
use Modules\Complaint\Models\PlainteParcelleModel;
use Modules\Complaint\Models\PlainteRolePersonneModel;
use Modules\Complaint\Models\RolePersonneModel;
use Modules\Complaint\Models\TypeDocumentModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\People\Models\PersonneModel;

class Appeals extends \App\Controllers\BaseController
{
    private BackofficeAppealService $service;

    public function __construct()
    {
        $this->service = new BackofficeAppealService();
    }

    public function index()
    {
        $filters = [
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id'),
            'province_id'           => $this->request->getGet('province_id'),
            'commune_id'            => $this->request->getGet('commune_id'),
            'juridiction_id'        => $this->request->getGet('juridiction_id'),
            'date_recours'          => $this->request->getGet('date_recours'),
            'dans_les_delais'       => $this->request->getGet('dans_les_delais'),
        ];

        $provinceId = (int) ($filters['province_id'] ?? 0);
        $niveauId   = (int) ($filters['niveau_juridiction_id'] ?? 0);

        return view('Modules\Appeals\Views\appeals\index', [
            'title'         => lang('Backoffice.apl_title'),
            'active'        => 'appeals',
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

    public function create()
    {
        return view('Modules\Appeals\Views\appeals\form', array_merge($this->formLookups(), [
            'title'    => lang('Backoffice.apl_create_title'),
            'active'   => 'appeals',
            'mode'     => 'create',
            'record'   => $this->emptyRecord(),
            'parcels'  => old('parcels') ?: [[
                'localisation_parcelle' => '', 'superficie_maitre_carreau' => '',
                'province_parcelle_id' => '', 'commune_parcelle_id' => '',
                'zone_parcelle_id' => '', 'colline_parcelle_id' => '',
            ]],
            'docTypes' => [],
            'parents'  => $this->service->eligibleParentOptions(),
            'user'     => $this->sampleUser(),
        ]));
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost(), $this->collectDocuments());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.apl_err_save')]);
        }

        return redirect()->to(site_url('backoffice/appeals'))->with('success', lang('Backoffice.apl_created'));
    }

    public function edit(int $id)
    {
        $record = $this->service->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/appeals'))->with('error', lang('Backoffice.apl_err_not_found'));
        }

        $nouvelleId = (int) ($record['nouvelle_plainte_id'] ?? 0);
        $roles      = new PlainteRolePersonneModel();
        $witnessId  = (new RolePersonneModel())->findWitnessId();
        $parcels    = old('parcels') ?: (new PlainteParcelleModel())->listByPlainte($nouvelleId);
        if ($parcels === []) {
            $parcels = [[
                'localisation_parcelle' => '', 'superficie_maitre_carreau' => '',
                'province_parcelle_id' => '', 'commune_parcelle_id' => '',
                'zone_parcelle_id' => '', 'colline_parcelle_id' => '',
            ]];
        }

        $record['objet']            = old('objet') ?: ($record['appeal_objet'] ?? '');
        $record['description']      = old('description') ?: ($record['appeal_description'] ?? '');
        $record['province_id']      = old('province_id') ?: ($record['province_id'] ?? '');
        $record['commune_id']       = old('commune_id') ?: ($record['commune_id'] ?? '');
        $record['complainant_ids']  = old('complainant_ids') ?: array_column($roles->listByPlainte($nouvelleId, RolePersonneModel::ROLE_PLAIGNANT), 'personne_id');
        $record['defendant_ids']    = old('defendant_ids') ?: array_column($roles->listByPlainte($nouvelleId, RolePersonneModel::ROLE_DEFENDANT), 'personne_id');
        $record['witness_ids']      = old('witness_ids') ?: ($witnessId ? array_column($roles->listByPlainte($nouvelleId, $witnessId), 'personne_id') : []);

        $niveauId = (int) (old('niveau_juridiction_id') ?: ($record['niveau_juridiction_id'] ?? 0));
        $parents  = $this->service->eligibleParentOptions();
        // Ensure current parent remains in options when editing
        $parentId = (int) ($record['plainte_parent_id'] ?? 0);
        if ($parentId && ! array_filter($parents, static fn ($p) => (int) $p['id'] === $parentId)) {
            $parents[] = [
                'id'                    => $parentId,
                'label'                 => trim(($record['parent_case_number'] ?? '') . ' — ' . ($record['parent_objet'] ?? '')),
                'niveau_juridiction_id' => (int) ($record['parent_niveau_id'] ?? 0),
                'verdict_id'            => (int) ($record['verdict_id'] ?? 0),
                'date_limite_recours'   => $record['date_limite_recours'] ?? null,
                'next_niveau_id'        => $niveauId,
            ];
        }

        return view('Modules\Appeals\Views\appeals\form', array_merge($this->formLookups($record), [
            'title'    => lang('Backoffice.apl_edit_title'),
            'active'   => 'appeals',
            'mode'     => 'edit',
            'record'   => $record,
            'parcels'  => $parcels,
            'docTypes' => $niveauId ? (new TypeDocumentModel())->listByNiveau($niveauId, true) : [],
            'parents'  => $parents,
            'user'     => $this->sampleUser(),
        ]));
    }

    public function update(int $id)
    {
        $result = $this->service->update($id, $this->request->getPost(), $this->collectDocuments());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.apl_err_save')]);
        }

        return redirect()->to(site_url('backoffice/appeals'))->with('success', lang('Backoffice.apl_updated'));
    }

    public function show(int $id)
    {
        $details = $this->service->details($id);
        if (! $details) {
            return redirect()->to(site_url('backoffice/appeals'))->with('error', lang('Backoffice.apl_err_not_found'));
        }

        return view('Modules\Appeals\Views\appeals\show', array_merge($details, [
            'title'  => lang('Backoffice.apl_details_title'),
            'active' => 'appeals',
            'user'   => $this->sampleUser(),
        ]));
    }

    public function eligibleParents()
    {
        return $this->response->setJSON([
            'ok'      => true,
            'options' => $this->service->eligibleParentOptions(),
        ]);
    }

    public function parentInfo(int $plainteId)
    {
        $parent = null;
        foreach ($this->service->eligibleParentOptions() as $opt) {
            if ((int) $opt['id'] === $plainteId) {
                $parent = $opt;
                break;
            }
        }

        return $this->response->setJSON([
            'ok'     => (bool) $parent,
            'parent' => $parent,
        ]);
    }

    public function documentTypes()
    {
        $niveauId = (int) ($this->request->getGet('niveau_juridiction_id') ?? 0);

        return $this->response->setJSON([
            'ok'    => true,
            'types' => $niveauId ? (new TypeDocumentModel())->listByNiveau($niveauId, true) : [],
        ]);
    }

    public function viewDocument(int $documentId)
    {
        return $this->serveDocument($documentId, false);
    }

    public function downloadDocument(int $documentId)
    {
        return $this->serveDocument($documentId, true);
    }

    private function serveDocument(int $documentId, bool $download)
    {
        $doc = (new \Modules\Complaint\Models\DocumentPlainteModel())->find($documentId);
        if (! $doc || empty($doc['fichier_chemin_stockage'])) {
            return redirect()->back()->with('error', lang('Backoffice.apl_err_doc_missing'));
        }

        $relative = ltrim(str_replace('\\', '/', (string) $doc['fichier_chemin_stockage']), '/');
        if (str_contains($relative, '..') || (! str_starts_with($relative, 'uploads/appeals/') && ! str_starts_with($relative, 'uploads/complaints/'))) {
            return redirect()->back()->with('error', lang('Backoffice.apl_err_doc_missing'));
        }

        $absolute = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if (! is_file($absolute)) {
            return redirect()->back()->with('error', lang('Backoffice.apl_err_doc_missing'));
        }

        $name = $doc['nom_fichier'] ?? basename($absolute);
        if ($download) {
            return $this->response->download($absolute, null)->setFileName($name);
        }

        $mime = mime_content_type($absolute) ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $name . '"')
            ->setBody((string) file_get_contents($absolute));
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
            $out[(int) $typeId] = is_array($fileOrList) ? $fileOrList : [$fileOrList];
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

        return [
            'levels'        => (new NiveauJuridictionModel())->options(),
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'jurisdictions' => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => $communeId ?: null,
            ]),
            'people'     => (new PersonneModel())->options(),
            'hasWitness' => (new RolePersonneModel())->findWitnessId() !== null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRecord(): array
    {
        return [
            'recours_id'            => null,
            'objet'                 => old('objet'),
            'description'           => old('description'),
            'niveau_juridiction_id' => old('niveau_juridiction_id'),
            'province_id'           => old('province_id'),
            'commune_id'            => old('commune_id'),
            'juridiction_id'        => old('juridiction_id'),
            'plainte_parent_id'     => old('plainte_parent_id'),
            'complainant_ids'       => old('complainant_ids') ?: [],
            'defendant_ids'         => old('defendant_ids') ?: [],
            'witness_ids'           => old('witness_ids') ?: [],
            'dans_les_delais'       => null,
            'date_limite_recours'   => null,
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
