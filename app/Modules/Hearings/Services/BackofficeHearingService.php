<?php

namespace Modules\Hearings\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Modules\Administration\Models\AuditLogModel;
use Modules\Administration\Models\ProfilModel;
use Modules\Administration\Models\UtilisateurModel;
use Modules\Complaint\Models\ConfigurationEtapePlainteModel;
use Modules\Complaint\Models\PlainteModel;
use Modules\Complaint\Models\PlainteRolePersonneModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\Hearings\Models\AudienceAffectionModel;
use Modules\Hearings\Models\AudienceModel;
use Modules\Hearings\Models\AudiencePlainteModel;
use Modules\Hearings\Models\DocumentAudienceModel;
use Modules\Hearings\Models\PresenceAudienceModel;
use Modules\Hearings\Models\StatutAudienceModel;
use Modules\Notification\Services\NotificationMailer;

class BackofficeHearingService
{
    private const JUDGE_NEEDLES  = ['juge', 'judge', 'magistrat', 'magistrate'];
    private const CLERK_NEEDLES  = ['greffier', 'clerk', 'greffe'];
    private const DOC_EXTS       = ['pdf', 'jpg', 'jpeg', 'png'];
    private const DOC_MAX_KB     = 10240;

    private AudienceModel $hearings;
    private AudiencePlainteModel $hearingComplaints;
    private AudienceAffectionModel $assignments;
    private DocumentAudienceModel $documents;
    private PresenceAudienceModel $attendance;
    private StatutAudienceModel $statuses;
    private PlainteModel $plaintes;
    private PlainteRolePersonneModel $parties;
    private ConfigurationEtapePlainteModel $stageConfig;
    private JuridictionModel $courts;
    private UtilisateurModel $users;
    private ProfilModel $profiles;
    private AuditLogModel $audit;
    private NotificationMailer $mailer;

    public function __construct()
    {
        $this->hearings          = new AudienceModel();
        $this->hearingComplaints = new AudiencePlainteModel();
        $this->assignments       = new AudienceAffectionModel();
        $this->documents         = new DocumentAudienceModel();
        $this->attendance        = new PresenceAudienceModel();
        $this->statuses          = new StatutAudienceModel();
        $this->plaintes          = new PlainteModel();
        $this->parties           = new PlainteRolePersonneModel();
        $this->stageConfig       = new ConfigurationEtapePlainteModel();
        $this->courts            = new JuridictionModel();
        $this->users             = new UtilisateurModel();
        $this->profiles          = new ProfilModel();
        $this->audit             = new AuditLogModel();
        $this->mailer            = service('notifications');
    }

    /**
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    public function list(array $query = []): array
    {
        try {
            $rows = $this->hearings->listFiltered([
                'niveau_juridiction_id' => ! empty($query['niveau_juridiction_id']) ? (int) $query['niveau_juridiction_id'] : null,
                'province_id'           => ! empty($query['province_id']) ? (int) $query['province_id'] : null,
                'commune_id'            => ! empty($query['commune_id']) ? (int) $query['commune_id'] : null,
                'juridiction_id'        => ! empty($query['juridiction_id']) ? (int) $query['juridiction_id'] : null,
                'date_audience'         => ! empty($query['date_audience']) ? (string) $query['date_audience'] : null,
                'statut_audience_id'    => ! empty($query['statut_audience_id']) ? (int) $query['statut_audience_id'] : null,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list hearings: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(function (array $row): array {
            $id = (int) $row['audience_id'];
            $start = substr((string) ($row['heure_debut'] ?? $row['heure_audience'] ?? ''), 0, 5);
            $end   = substr((string) ($row['heure_fin'] ?? ''), 0, 5);
            $period = trim($start . ($end !== '' ? ' / ' . $end : ''), ' /');

            return [
                'id'                => $id,
                'court'             => trim(($row['desc_niveau_juridiction'] ?? '') . ' / ' . ($row['nom_juridiction'] ?? ''), ' /'),
                'hearing_at'        => trim(($row['date_audience'] ?? '') . ' ' . substr((string) ($row['heure_audience'] ?? ''), 0, 5)),
                'complaints_count'  => (int) ($row['complaints_count'] ?? 0),
                'venue'             => $row['lieu_audience'] ?? '—',
                'period'            => $period !== '' ? $period : '—',
                'status'            => $row['description_statut_audience'] ?? '—',
                'complaints'        => $this->safe(fn () => $this->hearingComplaints->listByAudience($id)),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        try {
            return $this->hearings->findDetailed($id);
        } catch (\Throwable $e) {
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

        return [
            'record'      => $record,
            'staff'       => $this->safe(fn () => $this->assignments->listByAudience($id)),
            'complaints'  => $this->safe(fn () => $this->hearingComplaints->listByAudience($id)),
            'attendance'  => $this->safe(fn () => $this->attendance->listByAudience($id)),
            'documents'   => $this->safe(fn () => $this->documents->listByAudience($id)),
            'summons'     => $this->safe(fn () => $this->relatedSummons($id)),
            'verdicts'    => $this->safe(fn () => $this->relatedVerdicts($id)),
            'history'     => $this->safe(fn () => $this->statusHistory($id)),
        ];
    }

    /**
     * @return list<array{id:int,label:string,juridiction_id:int,convocation_id:int}>
     */
    public function eligibleComplaintOptions(?int $juridictionId = null): array
    {
        try {
            $rows = $this->hearings->listEligibleComplaints($juridictionId);
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static fn (array $row): array => [
            'id'             => (int) $row['plainte_id'],
            'label'          => trim(($row['numero_dossier'] ?? '') . ' — ' . ($row['objet'] ?? '')),
            'juridiction_id' => (int) ($row['juridiction_id'] ?? 0),
            'convocation_id' => (int) ($row['convocation_id'] ?? 0),
            'court'          => trim(($row['desc_niveau_juridiction'] ?? '') . ' / ' . ($row['nom_juridiction'] ?? ''), ' /'),
        ], $rows);
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function create(array $input): array
    {
        $errors = $this->validateCreate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $statusId = $this->statuses->findDefaultId();
        if (! $statusId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_no_status')]];
        }

        $complaintIds = $this->ids($input['plainte_ids'] ?? []);
        $eligible     = [];
        foreach ($this->eligibleComplaintOptions((int) $input['juridiction_audience_id']) as $opt) {
            $eligible[(int) $opt['id']] = $opt;
        }

        $selected = [];
        foreach ($complaintIds as $cid) {
            if (! isset($eligible[$cid])) {
                return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_complaint_summons')]];
            }
            $selected[] = $eligible[$cid];
        }

        $courtIds = array_unique(array_map(static fn ($r) => (int) $r['juridiction_id'], $selected));
        if (count($courtIds) !== 1 || (int) $courtIds[0] !== (int) $input['juridiction_audience_id']) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_same_court')]];
        }

        $now  = date('Y-m-d H:i:s');
        $data = [
            'niveau_juridiction_id'   => (int) $input['niveau_juridiction_id'],
            'date_audience'           => (string) $input['date_audience'],
            'heure_audience'          => (string) $input['heure_audience'],
            'juridiction_audience_id' => (int) $input['juridiction_audience_id'],
            'province_audience_id'    => (int) $input['province_audience_id'],
            'commune_audience_id'     => (int) $input['commune_audience_id'],
            'zone_audience_id'        => ! empty($input['zone_audience_id']) ? (int) $input['zone_audience_id'] : null,
            'colline_audience_id'     => ! empty($input['colline_audience_id']) ? (int) $input['colline_audience_id'] : null,
            'lieu_audience'           => trim((string) $input['lieu_audience']),
            'statut_audience_id'      => $statusId,
            'created_at'              => $now,
            'updated_at'              => $now,
        ];

        $db = db_connect();
        $db->transStart();
        $audienceId = 0;

        try {
            $audienceId = (int) $this->hearings->insert($data, true);
            if ($audienceId < 1) {
                throw new \RuntimeException('audience insert failed');
            }

            foreach ($selected as $item) {
                $this->hearingComplaints->insert([
                    'audience_id'        => $audienceId,
                    'plainte_id'         => (int) $item['id'],
                    'convocation_id'     => (int) $item['convocation_id'] ?: null,
                    'statut_audience_id' => $statusId,
                    'created_at'         => $now,
                ]);
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to create hearing: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_save')]];
        }

        if ($db->transStatus() === false || $audienceId < 1) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_save')]];
        }

        $this->audit->record('CREATE', 'audience.audience', $audienceId, null, $data + ['plainte_ids' => $complaintIds], $this->actorId());

        return ['ok' => true, 'id' => $audienceId];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listAssignments(int $audienceId): array
    {
        try {
            $rows = $this->assignments->listByAudience($audienceId);
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static function (array $row): array {
            $active = db_bool($row['is_active'] ?? false);

            return [
                'id'            => (int) $row['audience_affection_id'],
                'full_name'     => trim((string) ($row['assignee_name'] ?? '')) ?: '—',
                'profile'       => $row['libelle_profil'] ?? '—',
                'profil_id'     => (int) ($row['profil_id'] ?? 0),
                'user_id'       => (int) ($row['utilisateur_affecte_id'] ?? 0),
                'is_active'     => $active,
                'status'        => $active ? lang('Backoffice.status_active') : lang('Backoffice.status_inactive'),
                'assigned_by'   => trim((string) ($row['assigned_by_name'] ?? '')) ?: '—',
                'assigned_at'   => $row['create_at'] ?? '—',
            ];
        }, $rows);
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function createAssignment(int $audienceId, array $input): array
    {
        $hearing = $this->find($audienceId);
        if (! $hearing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_not_found')]];
        }

        $userId    = (int) ($input['utilisateur_affecte_id'] ?? 0);
        $profilId  = (int) ($input['profil_id'] ?? 0);
        $isActive  = db_bool($input['is_active'] ?? true);

        if ($userId < 1 || $profilId < 1) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_assignment_required')]];
        }

        $courtId = (int) ($hearing['juridiction_audience_id'] ?? 0);
        if (! $this->userBelongsToCourt($userId, $courtId)) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_assignment_court')]];
        }

        if ($this->assignments->pairExists($audienceId, $userId, $profilId)) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_assignment_duplicate')]];
        }

        $data = [
            'audience_id'             => $audienceId,
            'profil_id'               => $profilId,
            'utilisateur_affecte_id'  => $userId,
            'utilisateur_id'          => $this->actorId(),
            'is_active'               => $isActive,
            'create_at'               => date('Y-m-d H:i:s'),
        ];

        try {
            $id = $this->assignments->insert($data, true);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_assignment_save')]];
        }

        if (! $id) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_assignment_save')]];
        }

        $this->audit->record('CREATE', 'audience.audience_affection', (int) $id, null, $data, $this->actorId());

        return ['ok' => true, 'id' => (int) $id];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>}
     */
    public function updateAssignment(int $audienceId, int $assignmentId, array $input): array
    {
        $existing = $this->assignments->find($assignmentId);
        if (! $existing || (int) ($existing['audience_id'] ?? 0) !== $audienceId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_assignment_not_found')]];
        }

        $userId   = (int) ($input['utilisateur_affecte_id'] ?? $existing['utilisateur_affecte_id']);
        $profilId = (int) ($input['profil_id'] ?? $existing['profil_id']);
        $isActive = array_key_exists('is_active', $input)
            ? db_bool($input['is_active'])
            : db_bool($existing['is_active'] ?? false);

        $hearing = $this->find($audienceId);
        $courtId = (int) ($hearing['juridiction_audience_id'] ?? 0);
        if (! $this->userBelongsToCourt($userId, $courtId)) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_assignment_court')]];
        }

        if ($this->assignments->pairExists($audienceId, $userId, $profilId, $assignmentId)) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_assignment_duplicate')]];
        }

        $data = [
            'utilisateur_affecte_id' => $userId,
            'profil_id'              => $profilId,
            'is_active'              => $isActive,
        ];

        try {
            $this->assignments->update($assignmentId, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_assignment_save')]];
        }

        $this->audit->record('UPDATE', 'audience.audience_affection', $assignmentId, $existing, $data, $this->actorId());

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,errors?:list<string>,activated?:bool}
     */
    public function toggleAssignment(int $audienceId, int $assignmentId): array
    {
        $existing = $this->assignments->find($assignmentId);
        if (! $existing || (int) ($existing['audience_id'] ?? 0) !== $audienceId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_assignment_not_found')]];
        }

        $isActive   = db_bool($existing['is_active'] ?? false);
        $activating = ! $isActive;

        try {
            $this->assignments->update($assignmentId, ['is_active' => $activating]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_assignment_save')]];
        }

        $this->audit->record(
            $activating ? 'ACTIVATE' : 'DEACTIVATE',
            'audience.audience_affection',
            $assignmentId,
            ['is_active' => $isActive],
            ['is_active' => $activating],
            $this->actorId()
        );

        return ['ok' => true, 'activated' => $activating];
    }

    public function canProcess(int $audienceId): bool
    {
        return $this->hasRequiredStaff($audienceId);
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $files
     * @return array{ok:bool,errors?:list<string>}
     */
    public function process(int $audienceId, array $input, array $files = []): array
    {
        $hearing = $this->find($audienceId);
        if (! $hearing) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_not_found')]];
        }

        if (! $this->hasRequiredStaff($audienceId)) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_staff_required')]];
        }

        $held = filter_var($input['hearing_held'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $complaints = $this->hearingComplaints->listByAudience($audienceId);
        if ($complaints === []) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_no_complaints')]];
        }

        if (! $held && trim((string) ($input['motif_report'] ?? '')) === '') {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_required', [lang('Backoffice.hrg_field_postpone_reason')])]];
        }

        if ($held) {
            foreach (['date_tenue', 'heure_debut', 'heure_fin', 'rapport'] as $key) {
                if (trim((string) ($input[$key] ?? '')) === '') {
                    $label = match ($key) {
                        'date_tenue'  => lang('Backoffice.hrg_field_actual_date'),
                        'heure_debut' => lang('Backoffice.hrg_field_start_time'),
                        'heure_fin'   => lang('Backoffice.hrg_field_end_time'),
                        default       => lang('Backoffice.hrg_field_report'),
                    };
                    return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_required', [$label])]];
                }
            }
        }

        $statusId = $held
            ? ($this->statuses->findByLabelNeedles(['tenu', 'held', 'terminé', 'termine', 'done', 'completed']) ?? $this->statuses->findDefaultId())
            : ($this->statuses->findByLabelNeedles(['report', 'postpone', 'ajourn', 'renvoi']) ?? $this->statuses->findDefaultId());

        $hearingUpdate = [
            'statut_audience_id' => $statusId,
            'updated_at'         => date('Y-m-d H:i:s'),
            'motif_report'       => ! $held ? trim((string) $input['motif_report']) : null,
            'date_tenue'         => $held ? (string) $input['date_tenue'] : null,
            'heure_debut'        => $held ? (string) $input['heure_debut'] : null,
            'heure_fin'          => $held ? (string) $input['heure_fin'] : null,
            'rapport'            => $held ? trim((string) $input['rapport']) : null,
            'rapport_valide'     => $held ? db_bool($input['rapport_valide'] ?? false) : null,
        ];

        if (! $held && ! empty($input['new_hearing_date'])) {
            $hearingUpdate['date_audience'] = (string) $input['new_hearing_date'];
        }

        $complaintInputs = $input['complaints'] ?? [];
        if (! is_array($complaintInputs)) {
            $complaintInputs = [];
        }

        $db = db_connect();
        $db->transStart();
        $heardPlainteIds = [];

        try {
            $this->hearings->update($audienceId, $hearingUpdate);

            foreach ($complaints as $complaint) {
                $apId = (int) $complaint['audience_plainte_id'];
                $pid  = (int) $complaint['plainte_id'];
                $row  = $complaintInputs[$apId] ?? $complaintInputs[(string) $apId] ?? [];
                $heard = $held && filter_var($row['heard'] ?? false, FILTER_VALIDATE_BOOLEAN);

                $apUpdate = [
                    'statut_audience_id' => $statusId,
                    'motif_report'       => ! $heard ? trim((string) ($row['observations'] ?? ($input['motif_report'] ?? ''))) ?: null : null,
                    'rapport'            => $heard ? trim((string) ($row['rapport'] ?? ($input['rapport'] ?? ''))) ?: null : null,
                    'rapport_valide'     => $heard ? db_bool($row['rapport_valide'] ?? false) : null,
                ];
                $this->hearingComplaints->update($apId, $apUpdate);

                $this->attendance->deleteByAudiencePlainte($apId);
                $presenceRows = $row['attendance'] ?? [];
                if (is_array($presenceRows)) {
                    foreach ($this->parties->listByPlainte($pid) as $party) {
                        $roleId = (int) ($party['plainte_role_personne_id'] ?? 0);
                        $pres   = $presenceRows[$roleId] ?? $presenceRows[(string) $roleId] ?? [];
                        $this->attendance->insert([
                            'audience_plainte_id'      => $apId,
                            'plainte_role_personne_id' => $roleId,
                            'personne_id'              => (int) ($party['personne_id'] ?? 0) ?: null,
                            'present'                  => db_bool($pres['present'] ?? false),
                            'observations'             => trim((string) ($pres['observations'] ?? '')) ?: null,
                            'utilisateur_id'           => $this->actorId(),
                            'created_at'               => date('Y-m-d H:i:s'),
                        ]);
                    }
                }

                if ($heard) {
                    $heardPlainteIds[] = $pid;
                    $this->storeDocuments($apId, $files['documents'][$apId] ?? ($files['documents'][(string) $apId] ?? []), $row['document_parties'] ?? []);
                    $this->advanceWorkflow($pid);
                }
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to process hearing: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_process')]];
        }

        if ($db->transStatus() === false) {
            return ['ok' => false, 'errors' => [lang('Backoffice.hrg_err_process')]];
        }

        $this->audit->record('UPDATE', 'audience.audience', $audienceId, $hearing, $hearingUpdate + ['processed' => true, 'heard_plaintes' => $heardPlainteIds], $this->actorId());
        $this->notifyHearingProcessed($hearing, $complaints, $held);

        return ['ok' => true];
    }

    /**
     * @return list<array{id:int,label:string}>
     */
    public function courtUserOptions(int $juridictionId): array
    {
        try {
            $rows = $this->users->listWithRelations([
                'juridiction_id' => $juridictionId,
                'account_active' => true,
            ]);
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static fn (array $row): array => [
            'id'    => (int) $row['utilisateur_id'],
            'label' => trim(($row['prenom_utilisateur'] ?? '') . ' ' . ($row['nom_utilisateur'] ?? '')),
        ], $rows);
    }

    private function hasRequiredStaff(int $audienceId): bool
    {
        $hasJudge = false;
        $hasClerk = false;

        foreach ($this->safe(fn () => $this->assignments->listByAudience($audienceId)) as $row) {
            if (! db_bool($row['is_active'] ?? false)) {
                continue;
            }
            $hay = mb_strtolower(($row['code_profil'] ?? '') . ' ' . ($row['libelle_profil'] ?? ''));
            foreach (self::JUDGE_NEEDLES as $n) {
                if (str_contains($hay, $n)) {
                    $hasJudge = true;
                }
            }
            foreach (self::CLERK_NEEDLES as $n) {
                if (str_contains($hay, $n)) {
                    $hasClerk = true;
                }
            }
        }

        return $hasJudge && $hasClerk;
    }

    private function userBelongsToCourt(int $userId, int $courtId): bool
    {
        if ($userId < 1 || $courtId < 1) {
            return false;
        }
        $user = $this->users->find($userId);

        return $user && (int) ($user['juridiction_id'] ?? 0) === $courtId;
    }

    private function advanceWorkflow(int $plainteId): void
    {
        $complaint = $this->plaintes->find($plainteId);
        $current   = (int) ($complaint['etape_plainte_id'] ?? 0);
        if ($current < 1) {
            return;
        }

        $row = $this->stageConfig->builder()
            ->select('etape_plainte_suivant_id')
            ->where('etape_plainte_actuel_id', $current)
            ->where('(is_active IS NULL OR is_active = TRUE)', null, false)
            ->orderBy('configuration_etape_plainte_id', 'ASC')
            ->get(1)
            ->getRowArray();

        $nextId = (int) ($row['etape_plainte_suivant_id'] ?? 0);
        if ($nextId < 1) {
            return;
        }

        $this->plaintes->update($plainteId, [
            'etape_plainte_id' => $nextId,
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $this->audit->record(
            'UPDATE',
            'plainte.plainte',
            $plainteId,
            ['etape_plainte_id' => $current],
            ['etape_plainte_id' => $nextId, 'reason' => 'hearing_processed'],
            $this->actorId()
        );
    }

    /**
     * @param list<UploadedFile|null>|mixed $files
     * @param array<string|int, mixed> $partyMap
     */
    private function storeDocuments(int $audiencePlainteId, mixed $files, array $partyMap): void
    {
        if (! is_array($files)) {
            return;
        }

        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid() || $file->hasMoved()) {
                continue;
            }
            $ext = strtolower((string) $file->getExtension());
            if (! in_array($ext, self::DOC_EXTS, true) || $file->getSize() > self::DOC_MAX_KB * 1024) {
                continue;
            }

            $dir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'hearings';
            if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
                continue;
            }
            $name = $file->getRandomName();
            if (! $file->move($dir, $name)) {
                continue;
            }

            $desc = trim((string) ($partyMap[$index]['description'] ?? ($partyMap[(string) $index]['description'] ?? '')));
            $partyId = (int) ($partyMap[$index]['party_id'] ?? ($partyMap[(string) $index]['party_id'] ?? 0));
            $client  = $file->getClientName() ?: $name;
            $observation = ($desc !== '' ? $desc . ' — ' : '') . $client . "\n__FILE__:uploads/hearings/" . $name;

            $this->documents->insert([
                'observation'         => $observation,
                'audience_plainte_id' => $audiencePlainteId,
                'apporte_par_partie'  => $partyId ?: null,
                'enregistre_par'      => $this->actorId(),
                'enregistre_le'       => date('Y-m-d'),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $hearing
     * @param list<array<string, mixed>> $complaints
     */
    private function notifyHearingProcessed(array $hearing, array $complaints, bool $held): void
    {
        $subject = lang('Mail.subject_hearing_processed', [
            trim(($hearing['date_audience'] ?? '') . ' ' . substr((string) ($hearing['heure_audience'] ?? ''), 0, 5)),
        ]);
        $body = $held
            ? lang('Mail.hearing_held_body')
            : lang('Mail.hearing_postponed_body');

        foreach ($complaints as $complaint) {
            $plainteId = (int) ($complaint['plainte_id'] ?? 0);
            $caseNo    = (string) ($complaint['numero_dossier'] ?? '');
            foreach ($this->safe(fn () => $this->parties->listByPlainte($plainteId)) as $party) {
                $email = trim((string) ($party['email'] ?? ''));
                $name  = trim(($party['prenom_personne'] ?? '') . ' ' . ($party['nom_personne'] ?? ''));
                if ($email === '') {
                    continue;
                }
                try {
                    $this->mailer->send(
                        'hearing_processed',
                        $email,
                        $name,
                        $subject,
                        'Modules\Notification\Views\emails\hearing_processed',
                        [
                            'name'            => $name,
                            'complaintNumber' => $caseNo,
                            'bodyText'        => $body,
                            'portalUrl'       => site_url('portal/complaints'),
                        ]
                    );
                } catch (\Throwable $e) {
                    log_message('error', 'Hearing notification failed: {message}', ['message' => $e->getMessage()]);
                }
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function relatedSummons(int $audienceId): array
    {
        $sql = <<<'SQL'
            SELECT
                c.convocation_id,
                c.date_audience,
                c.heure_audience,
                c.lieu_audience,
                p.numero_dossier,
                sc.description_statut_convocation
            FROM audience.audience_plainte AS ap
            JOIN convocation.convocation AS c ON c.convocation_id = ap.convocation_id
            JOIN plainte.plainte AS p ON p.plainte_id = ap.plainte_id
            LEFT JOIN convocation.statut_convocation AS sc ON sc.statut_convocation_id = c.statut_convocation_id
            WHERE ap.audience_id = ?
            ORDER BY c.date_audience DESC NULLS LAST
        SQL;

        return db_connect()->query($sql, [$audienceId])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function relatedVerdicts(int $audienceId): array
    {
        $sql = <<<'SQL'
            SELECT
                v.verdict_id,
                v.date_verdict,
                v.resume,
                p.numero_dossier,
                tv.description_type_verdict
            FROM audience.audience_plainte AS ap
            JOIN verdict.verdict AS v ON v.audience_plainte_id = ap.audience_plainte_id
            JOIN plainte.plainte AS p ON p.plainte_id = ap.plainte_id
            LEFT JOIN verdict.type_verdict AS tv ON tv.type_verdict_id = v.type_verdict_id
            WHERE ap.audience_id = ?
            ORDER BY v.date_verdict DESC NULLS LAST
        SQL;

        return db_connect()->query($sql, [$audienceId])->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function statusHistory(int $audienceId): array
    {
        $sql = <<<'SQL'
            SELECT
                a.audit_log_id,
                a.action,
                a.created_at,
                a.nouvelles_valeurs,
                TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS actor_name
            FROM audit_log.audit_log AS a
            LEFT JOIN administration.utilisateur AS u ON u.utilisateur_id = a.utilisateur_id
            WHERE a.table_cible = 'audience.audience'
              AND a.enregistrement_id = ?
            ORDER BY a.created_at DESC NULLS LAST
            LIMIT 50
        SQL;

        try {
            return db_connect()->query($sql, [$audienceId])->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validateCreate(array $input): array
    {
        $errors = [];
        $required = [
            'niveau_juridiction_id'   => lang('Backoffice.hrg_field_level'),
            'province_audience_id'    => lang('Backoffice.hrg_field_province'),
            'commune_audience_id'     => lang('Backoffice.hrg_field_commune'),
            'juridiction_audience_id' => lang('Backoffice.hrg_field_court'),
            'lieu_audience'           => lang('Backoffice.hrg_field_venue'),
            'date_audience'           => lang('Backoffice.hrg_field_date'),
            'heure_audience'          => lang('Backoffice.hrg_field_time'),
        ];
        foreach ($required as $key => $label) {
            if (trim((string) ($input[$key] ?? '')) === '') {
                $errors[] = lang('Backoffice.hrg_err_required', [$label]);
            }
        }
        if ($this->ids($input['plainte_ids'] ?? []) === []) {
            $errors[] = lang('Backoffice.hrg_err_complaints_required');
        }

        return $errors;
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
            log_message('error', 'Hearing query failed: {message}', ['message' => $e->getMessage()]);

            return [];
        }
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
