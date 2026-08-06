<?php

namespace Modules\Transfer\Controllers;

use Modules\Administration\Models\JuridictionModel;
use Modules\Administration\Models\NiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\Transfer\Models\StatutTransfertDossierModel;
use Modules\Transfer\Services\BackofficeTransferService;

class Transfers extends \App\Controllers\BaseController
{
    private BackofficeTransferService $service;

    public function __construct()
    {
        $this->service = new BackofficeTransferService();
    }

    public function index()
    {
        $filters = [
            'niveau_juridiction_source_id' => $this->request->getGet('niveau_juridiction_source_id'),
            'province_source_id'           => $this->request->getGet('province_source_id'),
            'commune_source_id'            => $this->request->getGet('commune_source_id'),
            'juridiction_source_id'        => $this->request->getGet('juridiction_source_id'),
            'niveau_juridiction_dest_id'   => $this->request->getGet('niveau_juridiction_dest_id'),
            'province_dest_id'             => $this->request->getGet('province_dest_id'),
            'commune_dest_id'              => $this->request->getGet('commune_dest_id'),
            'juridiction_dest_id'          => $this->request->getGet('juridiction_dest_id'),
            'date_transfert'               => $this->request->getGet('date_transfert'),
            'date_reception'               => $this->request->getGet('date_reception'),
            'statut_transfert_dossier_id'  => $this->request->getGet('statut_transfert_dossier_id'),
        ];

        $srcProvince = (int) ($filters['province_source_id'] ?? 0);
        $srcNiveau   = (int) ($filters['niveau_juridiction_source_id'] ?? 0);
        $dstProvince = (int) ($filters['province_dest_id'] ?? 0);
        $dstNiveau   = (int) ($filters['niveau_juridiction_dest_id'] ?? 0);
        $courts      = new JuridictionModel();

        return view('Modules\Transfer\Views\transfers\index', [
            'title'           => lang('Backoffice.trf_title'),
            'active'          => 'case-transfers-list',
            'items'           => $this->service->list($filters),
            'filters'         => $filters,
            'levels'          => (new NiveauJuridictionModel())->options(),
            'provinces'       => (new ProvinceModel())->options(),
            'sourceCommunes'  => $srcProvince ? (new CommuneModel())->optionsByProvince($srcProvince) : [],
            'destCommunes'    => $dstProvince ? (new CommuneModel())->optionsByProvince($dstProvince) : [],
            'sourceCourts'    => $courts->options([
                'niveau_juridiction_id' => $srcNiveau ?: null,
                'province_id'           => $srcProvince ?: null,
                'commune_id'            => ! empty($filters['commune_source_id']) ? (int) $filters['commune_source_id'] : null,
            ]),
            'destCourts'      => $courts->options([
                'niveau_juridiction_id' => $dstNiveau ?: null,
                'province_id'           => $dstProvince ?: null,
                'commune_id'            => ! empty($filters['commune_dest_id']) ? (int) $filters['commune_dest_id'] : null,
            ]),
            'statuses'        => (new StatutTransfertDossierModel())->options(),
            'user'            => $this->sampleUser(),
        ]);
    }

    public function create()
    {
        $sourceNiveau   = (int) (old('niveau_juridiction_source_id') ?: 0);
        $sourceProvince = (int) (old('province_source_id') ?: 0);
        $sourceCommune  = (int) (old('commune_source_id') ?: 0);
        $sourceCourt    = (int) (old('juridiction_source_id') ?: 0);
        $destProvince   = (int) (old('province_dest_id') ?: 0);
        $destCommune    = (int) (old('commune_dest_id') ?: 0);
        $destNiveau     = null;
        $destCourts     = [];
        $complaints     = [];

        if ($sourceCourt) {
            $pack       = $this->service->destinationCourtsFor($sourceCourt, [
                'province_id' => $destProvince ?: null,
                'commune_id'  => $destCommune ?: null,
            ]);
            $destNiveau = $pack['next_niveau_id'];
            $destCourts = $pack['options'];
            $complaints = $this->service->eligibleComplaints($sourceCourt);
        }

        $courts = new JuridictionModel();

        return view('Modules\Transfer\Views\transfers\form', [
            'title'           => lang('Backoffice.trf_create_title'),
            'active'          => 'case-transfers-list',
            'record'          => [
                'niveau_juridiction_source_id' => old('niveau_juridiction_source_id'),
                'province_source_id'           => old('province_source_id'),
                'commune_source_id'            => old('commune_source_id'),
                'juridiction_source_id'        => old('juridiction_source_id'),
                'plainte_id'                   => old('plainte_id'),
                'niveau_juridiction_dest_id'   => old('niveau_juridiction_dest_id') ?: $destNiveau,
                'province_dest_id'             => old('province_dest_id'),
                'commune_dest_id'              => old('commune_dest_id'),
                'juridiction_dest_id'          => old('juridiction_dest_id'),
                'observations'                 => old('observations'),
            ],
            'levels'          => (new NiveauJuridictionModel())->options(),
            'provinces'       => (new ProvinceModel())->options(),
            'sourceCommunes'  => $sourceProvince ? (new CommuneModel())->optionsByProvince($sourceProvince) : [],
            'destCommunes'    => $destProvince ? (new CommuneModel())->optionsByProvince($destProvince) : [],
            'sourceCourts'    => $courts->options([
                'niveau_juridiction_id' => $sourceNiveau ?: null,
                'province_id'           => $sourceProvince ?: null,
                'commune_id'            => $sourceCommune ?: null,
            ]),
            'destCourts'      => $destCourts,
            'complaints'      => $complaints,
            'nextNiveauId'    => $destNiveau,
            'user'            => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $result = $this->service->create($this->request->getPost());
        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.trf_err_save')]);
        }

        return redirect()
            ->to(site_url('backoffice/transfers/' . (int) $result['id']))
            ->with('success', lang('Backoffice.trf_created'));
    }

    public function show(int $id)
    {
        $record = $this->service->details($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/transfers'))->with('error', lang('Backoffice.trf_err_not_found'));
        }

        return view('Modules\Transfer\Views\transfers\show', [
            'title'  => lang('Backoffice.trf_details_title'),
            'active' => 'case-transfers-list',
            'record' => $record,
            'user'   => $this->sampleUser(),
        ]);
    }

    public function process(int $id)
    {
        $record = $this->service->details($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/transfers'))->with('error', lang('Backoffice.trf_err_not_found'));
        }

        return view('Modules\Transfer\Views\transfers\process', [
            'title'  => lang('Backoffice.trf_process_title'),
            'active' => 'case-transfers-list',
            'record' => $record,
            'user'   => $this->sampleUser(),
        ]);
    }

    public function receive(int $id)
    {
        $result = $this->service->receive($id);
        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->to(site_url('backoffice/transfers/' . $id . '/process'))
                ->with('errors', $result['errors'] ?? [lang('Backoffice.trf_err_receive')]);
        }

        return redirect()
            ->to(site_url('backoffice/transfers/' . $id))
            ->with('success', lang('Backoffice.trf_received'));
    }

    public function eligibleComplaints()
    {
        $courtId = (int) $this->request->getGet('juridiction_id');

        return $this->response->setJSON([
            'options' => $this->service->eligibleComplaints($courtId),
        ]);
    }

    public function destinationCourts()
    {
        $sourceId = (int) $this->request->getGet('juridiction_source_id');
        $pack     = $this->service->destinationCourtsFor($sourceId, [
            'province_id' => $this->request->getGet('province_id') ?: null,
            'commune_id'  => $this->request->getGet('commune_id') ?: null,
        ]);

        return $this->response->setJSON($pack);
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
            return redirect()->back()->with('error', lang('Backoffice.trf_err_doc_missing'));
        }

        $relative = ltrim(str_replace('\\', '/', (string) $doc['fichier_chemin_stockage']), '/');
        if (str_contains($relative, '..') || (! str_starts_with($relative, 'uploads/appeals/') && ! str_starts_with($relative, 'uploads/complaints/'))) {
            return redirect()->back()->with('error', lang('Backoffice.trf_err_doc_missing'));
        }

        $absolute = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if (! is_file($absolute)) {
            return redirect()->back()->with('error', lang('Backoffice.trf_err_doc_missing'));
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
