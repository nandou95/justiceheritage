<?php

namespace Modules\Appeals\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use DateTimeImmutable;
use Modules\Administration\Models\AuditLogModel;
use Modules\Appeals\Models\RecoursModel;
use Modules\Appeals\Models\RecoursPartieModel;
use Modules\Appeals\Models\VerdictModel;
use Modules\Complaint\Models\DocumentPlainteModel;
use Modules\Complaint\Models\EtapePlainteModel;
use Modules\Complaint\Models\PlainteModel;
use Modules\Complaint\Models\PlainteParcelleModel;
use Modules\Complaint\Models\PlainteRolePersonneModel;
use Modules\Complaint\Models\RolePersonneModel;
use Modules\Complaint\Models\StatutPlainteModel;
use Modules\Complaint\Models\TypeDocumentModel;
use Modules\CourtJurisdiction\Models\ConfigurationNiveauJuridictionModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\CourtJurisdiction\Models\NiveauJuridictionModel;
use Modules\People\Models\PersonneModel;

class BackofficeAppealService
{
    private const DOC_MAX_KB = 10240;
    private const DOC_EXTS   = ['pdf', 'jpg', 'jpeg', 'png'];

    private RecoursModel $appeals;
    private RecoursPartieModel $appealRoles;
    private VerdictModel $verdicts;
    private PlainteModel $plaintes;
    private PlainteParcelleModel $parcelles;
    private PlainteRolePersonneModel $parties;
    private DocumentPlainteModel $documents;
    private EtapePlainteModel $stages;
    private StatutPlainteModel $statuses;
    private TypeDocumentModel $docTypes;
    private RolePersonneModel $roleTypes;
    private JuridictionModel $courts;
    private NiveauJuridictionModel $levels;
    private ConfigurationNiveauJuridictionModel $levelHierarchy;
    private PersonneModel $people;
    private AuditLogModel $audit;

    public function __construct()
    {
        $this->appeals         = new RecoursModel();
        $this->appealRoles     = new RecoursPartieModel();
        $this->verdicts        = new VerdictModel();
        $this->plaintes        = new PlainteModel();
        $this->parcelles       = new PlainteParcelleModel();
        $this->parties         = new PlainteRolePersonneModel();
        $this->documents       = new DocumentPlainteModel();
        $this->stages          = new EtapePlainteModel();
        $this->statuses        = new StatutPlainteModel();
        $this->docTypes        = new TypeDocumentModel();
        $this->roleTypes       = new RolePersonneModel();
        $this->courts          = new JuridictionModel();
        $this->levels          = new NiveauJuridictionModel();
        $this->levelHierarchy  = new ConfigurationNiveauJuridictionModel();
        $this->people          = new PersonneModel();
        $this->audit           = new AuditLogModel();
    }

    /**
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    public function list(array $query = []): array
    {
        try {
            $rows = $this->appeals->listFiltered([
                'niveau_juridiction_id' => ! empty($query['niveau_juridiction_id']) ? (int) $query['niveau_juridiction_id'] : null,
                'province_id'           => ! empty($query['province_id']) ? (int) $query['province_id'] : null,
                'commune_id'            => ! empty($query['commune_id']) ? (int) $query['commune_id'] : null,
                'juridiction_id'        => ! empty($query['juridiction_id']) ? (int) $query['juridiction_id'] : null,
                'date_recours'          => ! empty($query['date_recours']) ? (string) $query['date_recours'] : null,
                'dans_les_delais'       => $query['dans_les_delais'] ?? '',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list appeals: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $level = $row['desc_niveau_juridiction'] ?? '';
            $court = $row['nom_juridiction'] ?? '';
            $within = filter_var($row['dans_les_delais'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $verdict = trim(($row['description_type_verdict'] ?? '') . ' — ' . ($row['verdict_resume'] ?? ''), ' —');

            return [
                'id'              => (int) $row['recours_id'],
                'appeal_number'   => $row['appeal_number'] ?? '',
                'subject'         => $row['appeal_subject'] ?? ($row['parent_subject'] ?? ''),
                'challenged_verdict' => $verdict !== '' ? $verdict : '—',
                'previous_complaint' => trim(($row['parent_case_number'] ?? '') . ' / ' . ($row['parent_subject'] ?? ''), ' /'),
                'court'           => trim(($level ? $level . ' / ' : '') . $court, ' /'),
                'appeal_date'     => $row['date_recours'] ?? '',
                'within_deadline' => $within,
                'within_label'    => $within ? lang('Backoffice.yes') : lang('Backoffice.no'),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        try {
            return $this->appeals->findDetailed($id);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load appeal {id}: {message}', ['id' => $id, 'message' => $e->getMessage()]);

            return null;
        }
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

        $plainteId = (int) ($record['nouvelle_plainte_id'] ?? 0);
        $witnessId = $this->roleTypes->findWitnessId();

        return [
            'record'       => $record,
            'parcels'      => $plainteId ? $this->safe(fn () => $this->parcelles->listByPlainte($plainteId)) : [],
            'complainants' => $plainteId ? $this->safe(fn () => $this->parties->listByPlainte($plainteId, RolePersonneModel::ROLE_PLAIGNANT)) : [],
            'defendants'   => $plainteId ? $this->safe(fn () => $this->parties->listByPlainte($plainteId, RolePersonneModel::ROLE_DEFENDANT)) : [],
            'witnesses'    => ($plainteId && $witnessId) ? $this->safe(fn () => $this->parties->listByPlainte($plainteId, $witnessId)) : [],
            'documents'    => $plainteId ? $this->safe(fn () => $this->documents->listByPlainte($plainteId)) : [],
            'summons'      => $plainteId ? $this->safe(fn () => $this->plaintes->relatedSummons($plainteId)) : [],
            'hearings'     => $plainteId ? $this->safe(fn () => $this->plaintes->relatedHearings($plainteId)) : [],
            'attendance'   => $plainteId ? $this->safe(fn () => $this->listAttendance($plainteId)) : [],
            'verdicts'     => $plainteId ? $this->safe(fn () => $this->plaintes->relatedVerdicts($plainteId)) : [],
            'notifications'=> $plainteId ? $this->safe(fn () => $this->listNotifications($plainteId)) : [],
            'transfers'    => $plainteId ? $this->safe(fn () => $this->plaintes->relatedTransfers($plainteId)) : [],
        ];
    }

    /**
     * @return list<array{id:int,label:string,niveau_juridiction_id:int,verdict_id:int,date_limite_recours:?string,province_id:mixed,commune_id:mixed}>
     */
    public function eligibleParentOptions(): array
    {
        try {
            $rows = $this->appeals->listEligibleParents();
        } catch (\Throwable $e) {
            return [];
        }

            return array_map(function (array $row): array {
            $next = $this->nextLevelFor((int) ($row['niveau_juridiction_id'] ?? 0));

            return [
                'id'                    => (int) $row['plainte_id'],
                'label'                 => trim(($row['numero_dossier'] ?? '') . ' — ' . ($row['objet'] ?? '')),
                'niveau_juridiction_id' => (int) ($row['niveau_juridiction_id'] ?? 0),
                'verdict_id'            => (int) ($row['verdict_id'] ?? 0),
                'date_limite_recours'   => $row['date_limite_recours'] ?? null,
                'province_id'           => $row['province_id'] ?? null,
                'commune_id'            => $row['commune_id'] ?? null,
                'next_niveau_id'        => $next,
            ];
        }, $rows);
    }

    public function nextLevelFor(int $currentNiveauId): ?int
    {
        return $this->levelHierarchy->parentLevelId($currentNiveauId);
    }

    public function isWithinDeadline(?string $deadline, ?string $appealDate = null): bool
    {
        if ($deadline === null || $deadline === '') {
            return false;
        }

        try {
            $limit = new DateTimeImmutable(substr($deadline, 0, 10));
            $filed = new DateTimeImmutable($appealDate ? substr($appealDate, 0, 10) : 'today');
        } catch (\Throwable) {
            return false;
        }

        return $filed <= $limit;
    }

    /**
     * @param array<string, mixed> $input
     * @param array<int, list<UploadedFile|null>> $filesByType
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function create(array $input, array $filesByType = []): array
    {
        $errors = $this->validate($input, $filesByType, true);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $parentId = (int) $input['plainte_parent_id'];
        $parent   = $this->appeals->findEligibleParent($parentId);
        if (! $parent) {
            return ['ok' => false, 'errors' => [lang('Backoffice.apl_err_parent')]];
        }

        $nextLevel = $this->nextLevelFor((int) $parent['niveau_juridiction_id']);
        $niveauId  = (int) $input['niveau_juridiction_id'];
        if (! $nextLevel || $niveauId !== $nextLevel) {
            return ['ok' => false, 'errors' => [lang('Backoffice.apl_err_level_hierarchy')]];
        }

        $court = $this->courts->find((int) $input['juridiction_id']);
        if (! $court || (int) ($court['niveau_juridiction_id'] ?? 0) !== $niveauId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.apl_err_court_level')]];
        }

        $statusId  = $this->statuses->findDefaultId();
        $stageOpts = $this->stages->options($niveauId, true);
        $stageId   = $stageOpts[0]['id'] ?? null;
        if (! $statusId || ! $stageId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.apl_err_defaults')]];
        }

        $appealDate = date('Y-m-d');
        $within     = $this->isWithinDeadline($parent['date_limite_recours'] ?? null, $appealDate);

        $plainteData = [
            'numero_dossier'        => $this->plaintes->nextCaseNumber(),
            'objet'                 => trim((string) $input['objet']),
            'description'           => trim((string) $input['description']),
            'niveau_juridiction_id' => $niveauId,
            'juridiction_id'        => (int) $input['juridiction_id'],
            'statut_plainte_id'     => $statusId,
            'etape_plainte_id'      => (int) $stageId,
            'date_depot'            => $appealDate,
            'enregistre_par'        => $this->actorId(),
            'est_cree_par_plaigant' => false,
            'is_recours'            => true,
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ];

        $db = db_connect();
        $db->transStart();
        $storedFiles = [];

        try {
            $nouvelleId = $this->plaintes->insert($plainteData, true);
            if (! $nouvelleId) {
                throw new \RuntimeException('nouvelle plainte insert failed');
            }

            $this->syncParties((int) $nouvelleId, $input, true);
            $this->syncParcels((int) $nouvelleId, $input);
            $storedFiles = $this->storeDocuments((int) $nouvelleId, $niveauId, $filesByType);

            $recoursData = [
                'verdict_conteste_id'   => (int) $parent['verdict_id'],
                'nouvelle_plainte_id'   => (int) $nouvelleId,
                'date_recours'          => $appealDate,
                'dans_les_delais'       => $within,
                'enregistre_par'        => $this->actorId(),
                'created_at'            => date('Y-m-d H:i:s'),
                'niveau_juridiction_id' => $niveauId,
                'plainte_parent_id'     => $parentId,
                'juridiction_id'        => (int) $input['juridiction_id'],
            ];

            $recoursId = $this->appeals->insert($recoursData, true);
            if (! $recoursId) {
                throw new \RuntimeException('recours insert failed');
            }

            $this->syncAppealRoles((int) $recoursId, $input);
            $this->verdicts->update((int) $parent['verdict_id'], ['recours_exerce' => true]);

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            foreach ($storedFiles as $path) {
                $this->deleteFile($path);
            }
            log_message('error', 'Failed to create appeal: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.apl_err_save')]];
        }

        if ($db->transStatus() === false) {
            foreach ($storedFiles as $path) {
                $this->deleteFile($path);
            }

            return ['ok' => false, 'errors' => [lang('Backoffice.apl_err_save')]];
        }

        $this->audit->record('CREATE', 'recours.recours', (int) $recoursId, null, $recoursData + ['nouvelle_plainte' => $plainteData], $this->actorId());

        return ['ok' => true, 'id' => (int) $recoursId];
    }

    /**
     * @param array<string, mixed> $input
     * @param array<int, list<UploadedFile|null>> $filesByType
     * @return array{ok:bool,errors?:list<string>}
     */
    public function update(int $id, array $input, array $filesByType = []): array
    {
        $existing = $this->appeals->find($id);
        if (! $existing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.apl_err_not_found')]];
        }

        $errors = $this->validate($input, $filesByType, false, $id);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $parentId = (int) ($input['plainte_parent_id'] ?: $existing['plainte_parent_id']);
        $parent   = $this->appeals->findEligibleParent($parentId);
        if (! $parent) {
            return ['ok' => false, 'errors' => [lang('Backoffice.apl_err_parent')]];
        }

        $nextLevel = $this->nextLevelFor((int) $parent['niveau_juridiction_id']);
        $niveauId  = (int) $input['niveau_juridiction_id'];
        if (! $nextLevel || $niveauId !== $nextLevel) {
            return ['ok' => false, 'errors' => [lang('Backoffice.apl_err_level_hierarchy')]];
        }

        $nouvelleId = (int) $existing['nouvelle_plainte_id'];
        $appealDate = substr((string) ($existing['date_recours'] ?? date('Y-m-d')), 0, 10);
        $within     = $this->isWithinDeadline($parent['date_limite_recours'] ?? null, $appealDate);

        $plainteData = [
            'objet'                 => trim((string) $input['objet']),
            'description'           => trim((string) $input['description']),
            'niveau_juridiction_id' => $niveauId,
            'juridiction_id'        => (int) $input['juridiction_id'],
            'updated_at'            => date('Y-m-d H:i:s'),
        ];

        $recoursData = [
            'niveau_juridiction_id' => $niveauId,
            'juridiction_id'        => (int) $input['juridiction_id'],
            'plainte_parent_id'     => $parentId,
            'verdict_conteste_id'   => (int) $parent['verdict_id'],
            'dans_les_delais'       => $within,
        ];

        $db = db_connect();
        $db->transStart();
        $storedFiles = [];

        try {
            $this->plaintes->update($nouvelleId, $plainteData);
            $this->parties->deleteByPlainte($nouvelleId);
            $this->syncParties($nouvelleId, $input, true);
            $this->parcelles->deleteByPlainte($nouvelleId);
            $this->syncParcels($nouvelleId, $input);
            $storedFiles = $this->storeDocuments($nouvelleId, $niveauId, $filesByType);
            $this->appeals->update($id, $recoursData);
            $this->syncAppealRoles($id, $input);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            foreach ($storedFiles as $path) {
                $this->deleteFile($path);
            }
            log_message('error', 'Failed to update appeal: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.apl_err_save')]];
        }

        if ($db->transStatus() === false) {
            foreach ($storedFiles as $path) {
                $this->deleteFile($path);
            }

            return ['ok' => false, 'errors' => [lang('Backoffice.apl_err_save')]];
        }

        $this->audit->record('UPDATE', 'recours.recours', $id, $existing, $recoursData + ['nouvelle_plainte' => $plainteData], $this->actorId());

        return ['ok' => true];
    }

    /**
     * @param array<string, mixed> $input
     * @param array<int, list<UploadedFile|null>> $filesByType
     * @return list<string>
     */
    private function validate(array $input, array $filesByType, bool $requireDocs, ?int $ignoreId = null): array
    {
        $errors = [];
        $labels = [
            'objet'                 => lang('Backoffice.apl_field_objet'),
            'description'           => lang('Backoffice.apl_field_description'),
            'niveau_juridiction_id' => lang('Backoffice.apl_field_level'),
            'province_id'           => lang('Backoffice.apl_field_province'),
            'commune_id'            => lang('Backoffice.apl_field_commune'),
            'juridiction_id'        => lang('Backoffice.apl_field_court'),
            'plainte_parent_id'     => lang('Backoffice.apl_field_parent'),
        ];
        foreach ($labels as $key => $label) {
            if (trim((string) ($input[$key] ?? '')) === '') {
                $errors[] = lang('Backoffice.apl_err_required', [$label]);
            }
        }

        if ($this->ids($input['complainant_ids'] ?? []) === []) {
            $errors[] = lang('Backoffice.apl_err_complainants');
        }
        if ($this->ids($input['defendant_ids'] ?? []) === []) {
            $errors[] = lang('Backoffice.apl_err_defendants');
        }

        $parcels = $input['parcels'] ?? [];
        if (! is_array($parcels) || $parcels === []) {
            $errors[] = lang('Backoffice.apl_err_parcels');
        }

        $niveauId = (int) ($input['niveau_juridiction_id'] ?? 0);
        if ($requireDocs && $niveauId > 0) {
            foreach ($this->docTypes->listByNiveau($niveauId, true) as $type) {
                if (! filter_var($type['is_obligatoire'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    continue;
                }
                $typeId   = (int) $type['type_document_id'];
                $hasValid = false;
                foreach ($filesByType[$typeId] ?? [] as $file) {
                    if ($file instanceof UploadedFile && $file->isValid() && ! $file->hasMoved()) {
                        $hasValid = true;
                        break;
                    }
                }
                if (! $hasValid) {
                    $errors[] = lang('Backoffice.apl_err_doc_required', [$type['libelle_type_document'] ?? $type['code_type_document']]);
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $input
     */
    private function syncParties(int $plainteId, array $input, bool $asAppellant): void
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
            foreach ($personneIds as $personneId) {
                if (! $this->people->find($personneId)) {
                    continue;
                }
                $this->parties->insert([
                    'plainte_id'       => $plainteId,
                    'personne_id'      => $personneId,
                    'role_personne_id' => $roleId,
                    'est_recourant'    => $asAppellant && $roleId === RolePersonneModel::ROLE_PLAIGNANT,
                    'utilisateur_id'   => $this->actorId(),
                    'date_ajout'       => $now,
                    'created_at'       => $now,
                ]);
            }
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    private function syncAppealRoles(int $recoursId, array $input): void
    {
        $roles = [RolePersonneModel::ROLE_PLAIGNANT, RolePersonneModel::ROLE_DEFENDANT];
        if ($this->ids($input['witness_ids'] ?? []) !== [] && ($w = $this->roleTypes->findWitnessId())) {
            $roles[] = $w;
        }
        $this->appealRoles->syncRoles($recoursId, $roles);
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
                'superficie_maitre_carreau' => ($parcel['superficie_maitre_carreau'] ?? '') !== ''
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
     * @param array<int, list<UploadedFile|null>> $filesByType
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
                $dir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'appeals';
                if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
                    continue;
                }
                $name = $file->getRandomName();
                if (! $file->move($dir, $name)) {
                    continue;
                }
                $relative = 'uploads/appeals/' . $name;
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
     * @return list<array<string, mixed>>
     */
    private function listAttendance(int $plainteId): array
    {
        $sql = <<<'SQL'
            SELECT
                pa.presence_audience_id,
                pa.present,
                pa.observations,
                a.date_audience,
                pe.nom_personne,
                pe.prenom_personne,
                rp.description_role_personne
            FROM audience.presence_audience AS pa
            JOIN audience.audience_plainte AS ap ON ap.audience_plainte_id = pa.audience_plainte_id
            JOIN audience.audience AS a ON a.audience_id = ap.audience_id
            LEFT JOIN plaignant.plainte_role_personne AS prp ON prp.plainte_role_personne_id = pa.plainte_role_personne_id
            LEFT JOIN plaignant.personne AS pe ON pe.personne_id = COALESCE(pa.personne_id, prp.personne_id)
            LEFT JOIN plaignant.role_personne AS rp ON rp.role_personne_id = prp.role_personne_id
            WHERE ap.plainte_id = ?
            ORDER BY a.date_audience DESC NULLS LAST
        SQL;

        return db_connect()->query($sql, [$plainteId])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function listNotifications(int $plainteId): array
    {
        $sql = <<<'SQL'
            SELECT
                n.notification_personne_id,
                n.sujet,
                n.corps,
                n.envoye_le,
                n.lu_le,
                sn.description_statut_notification
            FROM notification.notification_personne AS n
            LEFT JOIN notification.statut_notification AS sn ON sn.statut_notification_id = n.statut_notification_id
            WHERE n.plainte_id = ?
            ORDER BY n.envoye_le DESC NULLS LAST, n.notification_personne_id DESC
        SQL;

        try {
            return db_connect()->query($sql, [$plainteId])->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }
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
            log_message('error', 'Appeal detail query failed: {message}', ['message' => $e->getMessage()]);

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
