<?php

namespace Modules\Complaint\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Modules\Administration\Models\AuditLogModel;
use Modules\Complaint\Models\ConfigurationEtapePlainteModel;
use Modules\Complaint\Models\DocumentPlainteModel;
use Modules\Complaint\Models\EtapePlainteModel;
use Modules\Complaint\Models\HistoriquePlainteModel;
use Modules\Complaint\Models\PlainteModel;
use Modules\Complaint\Models\PlainteParcelleModel;
use Modules\Complaint\Models\PlainteRolePersonneModel;
use Modules\Complaint\Models\RolePersonneModel;
use Modules\Complaint\Models\StatutPlainteModel;
use Modules\Complaint\Models\TypeDocumentModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\People\Models\PersonneModel;

class BackofficeComplaintService
{
    private const DOC_MAX_KB = 10240;
    private const DOC_EXTS   = ['pdf', 'jpg', 'jpeg', 'png'];

    /** Initial filing workflow keys used when creating a complaint. */
    private const FILING_STAGE_ID  = 1;
    private const FILING_ACTION_ID = 1;
    private const FILING_STATUS_ID = 1;

    private PlainteModel $plaintes;
    private PlainteParcelleModel $parcelles;
    private PlainteRolePersonneModel $roles;
    private DocumentPlainteModel $documents;
    private EtapePlainteModel $stages;
    private StatutPlainteModel $statuses;
    private TypeDocumentModel $docTypes;
    private RolePersonneModel $roleTypes;
    private JuridictionModel $courts;
    private NiveauJuridictionModel $levels;
    private PersonneModel $people;
    private AuditLogModel $audit;
    private ConfigurationEtapePlainteModel $stageConfig;
    private HistoriquePlainteModel $history;

    public function __construct()
    {
        $this->plaintes    = new PlainteModel();
        $this->parcelles   = new PlainteParcelleModel();
        $this->roles       = new PlainteRolePersonneModel();
        $this->documents   = new DocumentPlainteModel();
        $this->stages      = new EtapePlainteModel();
        $this->statuses    = new StatutPlainteModel();
        $this->docTypes    = new TypeDocumentModel();
        $this->roleTypes   = new RolePersonneModel();
        $this->courts      = new JuridictionModel();
        $this->levels      = new NiveauJuridictionModel();
        $this->people      = new PersonneModel();
        $this->audit       = new AuditLogModel();
        $this->stageConfig = new ConfigurationEtapePlainteModel();
        $this->history     = new HistoriquePlainteModel();
    }

    /**
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    public function list(array $query = []): array
    {
        try {
            $rows = $this->plaintes->listForBackoffice([
                'province_id'           => ! empty($query['province_id']) ? (int) $query['province_id'] : null,
                'commune_id'            => ! empty($query['commune_id']) ? (int) $query['commune_id'] : null,
                'niveau_juridiction_id' => ! empty($query['niveau_juridiction_id']) ? (int) $query['niveau_juridiction_id'] : null,
                'juridiction_id'        => ! empty($query['juridiction_id']) ? (int) $query['juridiction_id'] : null,
                'statut_plainte_id'     => ! empty($query['statut_plainte_id']) ? (int) $query['statut_plainte_id'] : null,
                'date_depot'            => ! empty($query['date_depot']) ? (string) $query['date_depot'] : null,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list complaints: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $level = $row['desc_niveau_juridiction'] ?? '';
            $court = $row['nom_juridiction'] ?? '';

            return [
                'id'           => (int) $row['plainte_id'],
                'case_number'  => $row['numero_dossier'] ?? '',
                'subject'      => $row['objet'] ?? '',
                'people_count' => (int) ($row['people_count'] ?? 0),
                'parcels_count'=> (int) ($row['parcels_count'] ?? 0),
                'court'        => trim(($level ? $level . ' / ' : '') . $court, ' /'),
                'filing_date'  => $row['date_depot'] ?? '',
                'stage'        => $row['description_etape_plainte'] ?? '—',
                'status'       => $row['description_statut_plainte'] ?? '—',
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        try {
            $row = $this->plaintes->findForBackoffice($id);
        } catch (\Throwable $e) {
            return null;
        }

        return $row ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function details(int $id): ?array
    {
        $record = $this->find($id);
        if (! $record) {
            return null;
        }

        $witnessId = $this->roleTypes->findWitnessId();

        return [
            'record'     => $record,
            'parcels'    => $this->safe(fn () => $this->parcelles->listByPlainte($id)),
            'complainants' => $this->safe(fn () => $this->roles->listByPlainte($id, RolePersonneModel::ROLE_PLAIGNANT)),
            'defendants' => $this->safe(fn () => $this->roles->listByPlainte($id, RolePersonneModel::ROLE_DEFENDANT)),
            'witnesses'  => $witnessId ? $this->safe(fn () => $this->roles->listByPlainte($id, $witnessId)) : [],
            'documents'  => $this->safe(fn () => $this->documents->listByPlainte($id)),
            'summons'    => $this->safe(fn () => $this->plaintes->relatedSummons($id)),
            'hearings'   => $this->safe(fn () => $this->plaintes->relatedHearings($id)),
            'verdicts'   => $this->safe(fn () => $this->plaintes->relatedVerdicts($id)),
            'appeals'    => $this->safe(fn () => $this->plaintes->relatedAppeals($id)),
            'transfers'  => $this->safe(fn () => $this->plaintes->relatedTransfers($id)),
            'history'    => $this->safe(fn () => $this->plaintes->workflowHistory($id)),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, list<UploadedFile|null>> $filesByType
     * @param array{source?:string,personne_id?:int|null} $context
     * @return array{ok:bool,errors?:list<string>,id?:int,numero?:string}
     */
    public function create(array $input, array $filesByType = [], array $context = []): array
    {
        $source     = ($context['source'] ?? 'backoffice') === 'portal' ? 'portal' : 'backoffice';
        $fromPortal = $source === 'portal';
        $personneId = isset($context['personne_id']) ? (int) $context['personne_id'] : 0;

        if ($fromPortal && $personneId > 0) {
            $complainants = $this->ids($input['complainant_ids'] ?? []);
            if (! in_array($personneId, $complainants, true)) {
                $complainants[] = $personneId;
            }
            $input['complainant_ids'] = $complainants;
        }

        $errors = $this->validate($input, $filesByType, true, ! $fromPortal);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $niveauId = (int) $input['niveau_juridiction_id'];
        $statusId = self::FILING_STATUS_ID;
        $stageId  = $this->stageConfig->findNextStageId(self::FILING_STAGE_ID, self::FILING_ACTION_ID);

        if (! $stageId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cmp_err_stage_config')]];
        }

        if (! $this->statuses->find($statusId)) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cmp_err_defaults')]];
        }

        $actorUserId = $fromPortal ? null : $this->actorId();

        $data = [
            'numero_dossier'         => $this->plaintes->nextCaseNumber(),
            'objet'                  => trim((string) $input['objet']),
            'description'            => trim((string) $input['description']),
            'niveau_juridiction_id'  => $niveauId,
            'juridiction_id'         => (int) $input['juridiction_id'],
            'statut_plainte_id'      => $statusId,
            'etape_plainte_id'       => $stageId,
            'date_depot'             => date('Y-m-d'),
            'enregistre_par'         => $actorUserId,
            'est_cree_par_plaigant'  => $fromPortal,
            'is_recours'             => false,
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s'),
        ];

        $db = db_connect();
        $db->transStart();
        $storedFiles = [];

        try {
            $id = $this->plaintes->insert($data, true);
            if (! $id) {
                throw new \RuntimeException('plainte insert failed');
            }
            $this->syncParties((int) $id, $input, $actorUserId);
            $this->syncParcels((int) $id, $input);
            $storedFiles = $this->storeDocuments((int) $id, $niveauId, $filesByType);

            if (! $this->history->recordEvent([
                'plainte_id'              => (int) $id,
                'etape_plainte_id'        => self::FILING_STAGE_ID,
                'etape_plainte_action_id' => self::FILING_ACTION_ID,
                'statut_plainte_id'       => self::FILING_STATUS_ID,
                'is_utilisateur'          => ! $fromPortal,
                'utilisateur_id'          => $fromPortal ? null : $actorUserId,
                'personne_id'             => $fromPortal ? ($personneId > 0 ? $personneId : null) : null,
            ])) {
                throw new \RuntimeException('historique_plainte insert failed');
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            foreach ($storedFiles as $path) {
                $this->deleteFile($path);
            }
            log_message('error', 'Failed to create complaint: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.cmp_err_save')]];
        }

        if ($db->transStatus() === false) {
            foreach ($storedFiles as $path) {
                $this->deleteFile($path);
            }

            return ['ok' => false, 'errors' => [lang('Backoffice.cmp_err_save')]];
        }

        if (! $fromPortal) {
            $this->audit->record('CREATE', 'plainte.plainte', (int) $id, null, $data, $this->actorId());
        }

        return [
            'ok'     => true,
            'id'     => (int) $id,
            'numero' => (string) $data['numero_dossier'],
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, list<UploadedFile|null>> $filesByType
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input, array $filesByType = []): array
    {
        $existing = $this->plaintes->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.cmp_err_not_found')]];
        }

        $errors = $this->validate($input, $filesByType, false);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $niveauId = (int) $input['niveau_juridiction_id'];
        $data     = [
            'objet'                 => trim((string) $input['objet']),
            'description'           => trim((string) $input['description']),
            'niveau_juridiction_id' => $niveauId,
            'juridiction_id'        => (int) $input['juridiction_id'],
            'updated_at'            => date('Y-m-d H:i:s'),
        ];

        $db = db_connect();
        $db->transStart();
        $storedFiles = [];

        try {
            $this->plaintes->update($id, $data);
            $this->roles->deleteByPlainte($id);
            $this->syncParties($id, $input, $this->actorId());
            $this->parcelles->deleteByPlainte($id);
            $this->syncParcels($id, $input);
            $storedFiles = $this->storeDocuments($id, $niveauId, $filesByType);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            foreach ($storedFiles as $path) {
                $this->deleteFile($path);
            }
            log_message('error', 'Failed to update complaint: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.cmp_err_save')]];
        }

        if ($db->transStatus() === false) {
            foreach ($storedFiles as $path) {
                $this->deleteFile($path);
            }

            return ['ok' => false, 'errors' => [lang('Backoffice.cmp_err_save')]];
        }

        $this->audit->record('UPDATE', 'plainte.plainte', $id, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, list<UploadedFile|null>> $filesByType
     * @return list<string>
     */
    private function validate(array $input, array $filesByType, bool $requireMandatoryDocs, bool $requireParties = true): array
    {
        $errors = [];
        $requiredLabels = [
            'objet'                 => lang('Backoffice.cmp_field_objet'),
            'description'           => lang('Backoffice.cmp_field_description'),
            'niveau_juridiction_id' => lang('Backoffice.cmp_field_level'),
            'province_id'           => lang('Backoffice.cmp_field_province'),
            'commune_id'            => lang('Backoffice.cmp_field_commune'),
            'juridiction_id'        => lang('Backoffice.cmp_field_court'),
        ];
        foreach ($requiredLabels as $key => $label) {
            if (trim((string) ($input[$key] ?? '')) === '') {
                $errors[] = lang('Backoffice.cmp_err_required', [$label]);
            }
        }

        $niveauId = (int) ($input['niveau_juridiction_id'] ?? 0);
        $courtId  = (int) ($input['juridiction_id'] ?? 0);
        $level    = $niveauId > 0 ? $this->levels->find($niveauId) : null;
        if ($niveauId && ! $level) {
            $errors[] = lang('Backoffice.cmp_err_level');
        } elseif ($requireMandatoryDocs && $level && db_bool($level['is_recours'] ?? false)) {
            $errors[] = lang('Backoffice.cmp_err_level_filing');
        }
        if ($courtId && ! $this->courts->find($courtId)) {
            $errors[] = lang('Backoffice.cmp_err_court');
        }

        if ($requireParties) {
            $complainants = $this->ids($input['complainant_ids'] ?? []);
            $defendants   = $this->ids($input['defendant_ids'] ?? []);
            if ($complainants === []) {
                $errors[] = lang('Backoffice.cmp_err_complainants');
            }
            if ($defendants === []) {
                $errors[] = lang('Backoffice.cmp_err_defendants');
            }
        }

        $parcels = $input['parcels'] ?? [];
        if (! is_array($parcels) || $parcels === []) {
            $errors[] = lang('Backoffice.cmp_err_parcels');
        }

        if ($requireMandatoryDocs && $niveauId > 0) {
            foreach ($this->docTypes->listByNiveau($niveauId, true) as $type) {
                if (! db_bool($type['is_obligatoire'] ?? false)) {
                    continue;
                }
                $typeId = (int) $type['type_document_id'];
                $files  = $filesByType[$typeId] ?? [];
                $hasValid = false;
                foreach ($files as $file) {
                    if ($file instanceof UploadedFile && $file->isValid() && ! $file->hasMoved()) {
                        $hasValid = true;
                        break;
                    }
                }
                if (! $hasValid) {
                    $errors[] = lang('Backoffice.cmp_err_doc_required', [$type['libelle_type_document'] ?? $type['code_type_document']]);
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $input
     */
    private function syncParties(int $plainteId, array $input, ?int $utilisateurId = null): void
    {
        $now = date('Y-m-d H:i:s');
        $map = [
            RolePersonneModel::ROLE_PLAIGNANT => $this->ids($input['complainant_ids'] ?? []),
            RolePersonneModel::ROLE_DEFENDANT => $this->ids($input['defendant_ids'] ?? []),
        ];
        $witnessId = $this->roleTypes->findWitnessId();
        if ($witnessId) {
            $map[$witnessId] = $this->ids($input['witness_ids'] ?? []);
        }

        foreach ($map as $roleId => $personneIds) {
            foreach (array_values(array_unique($personneIds)) as $personneId) {
                if (! $this->people->find($personneId)) {
                    continue;
                }
                $this->roles->insert([
                    'plainte_id'       => $plainteId,
                    'personne_id'      => $personneId,
                    'role_personne_id' => $roleId,
                    'est_recourant'    => false,
                    'utilisateur_id'   => $utilisateurId,
                    'date_ajout'       => $now,
                    'created_at'       => $now,
                ]);
            }
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    private function syncParcels(int $plainteId, array $input): void
    {
        $parcels = $input['parcels'] ?? [];
        if (! is_array($parcels)) {
            return;
        }

        foreach ($parcels as $parcel) {
            if (! is_array($parcel)) {
                continue;
            }
            $localisation = trim((string) ($parcel['localisation_parcelle'] ?? ''));
            $provinceId   = (int) ($parcel['province_parcelle_id'] ?? 0);
            $communeId    = (int) ($parcel['commune_parcelle_id'] ?? 0);
            if ($localisation === '' || $provinceId < 1 || $communeId < 1) {
                continue;
            }
            $this->parcelles->insert([
                'plainte_id'                => $plainteId,
                'localisation_parcelle'     => $localisation,
                'superficie_maitre_carreau' => $parcel['superficie_maitre_carreau'] !== '' && $parcel['superficie_maitre_carreau'] !== null
                    ? (float) $parcel['superficie_maitre_carreau']
                    : null,
                'province_parcelle_id'      => $provinceId,
                'commune_parcelle_id'       => $communeId,
                'zone_parcelle_id'          => ! empty($parcel['zone_parcelle_id']) ? (int) $parcel['zone_parcelle_id'] : null,
                'colline_parcelle_id'       => ! empty($parcel['colline_parcelle_id']) ? (int) $parcel['colline_parcelle_id'] : null,
                'created_at'                => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @param array<string, list<UploadedFile|null>> $filesByType
     * @return list<string>
     */
    private function storeDocuments(int $plainteId, int $niveauId, array $filesByType): array
    {
        $stored = [];
        foreach ($filesByType as $typeId => $files) {
            foreach ($files as $file) {
                if (! $file instanceof UploadedFile || ! $file->isValid() || $file->hasMoved()) {
                    continue;
                }
                $ext = strtolower((string) $file->getExtension());
                if (! in_array($ext, self::DOC_EXTS, true) || $file->getSize() > self::DOC_MAX_KB * 1024) {
                    continue;
                }

                $dir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'complaints';
                if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
                    continue;
                }
                $name = $file->getRandomName();
                if (! $file->move($dir, $name)) {
                    continue;
                }
                $relative = 'uploads/complaints/' . $name;
                $stored[] = $relative;
                $full     = $dir . DIRECTORY_SEPARATOR . $name;

                $this->documents->insert([
                    'plainte_id'              => $plainteId,
                    'type_document_id'        => (int) $typeId,
                    'nom_fichier'             => $file->getClientName() ?: $name,
                    'fichier_chemin_stockage' => $relative,
                    'taille_octets'           => is_file($full) ? filesize($full) : null,
                    'hash_sha256'             => is_file($full) ? hash_file('sha256', $full) : null,
                    'niveau_juridiction_id'   => $niveauId,
                    'date_depot'              => date('Y-m-d'),
                    'depose_par_utilisateur'  => $this->actorId(),
                    'created_at'              => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return $stored;
    }

    /**
     * @param mixed $raw
     * @return list<int>
     */
    private function ids(mixed $raw): array
    {
        if (! is_array($raw)) {
            $raw = [$raw];
        }

        return array_values(array_unique(array_filter(array_map('intval', $raw), static fn (int $id): bool => $id > 0)));
    }

    /**
     * @param callable():mixed $callback
     * @return mixed
     */
    private function safe(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            log_message('error', 'Complaint detail query failed: {message}', ['message' => $e->getMessage()]);

            return [];
        }
    }

    private function deleteFile(string $relative): void
    {
        $full = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relative, '/\\'));
        if (is_file($full)) {
            @unlink($full);
        }
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
