<?php

namespace Modules\Verdicts\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use DateTimeImmutable;
use Modules\Administration\Models\AuditLogModel;
use Modules\Administration\Models\UtilisateurModel;
use Modules\Complaint\Models\ConfigurationEtapePlainteModel;
use Modules\Complaint\Models\DocumentPlainteModel;
use Modules\Complaint\Models\PlainteModel;
use Modules\Complaint\Models\PlainteRolePersonneModel;
use Modules\Complaint\Models\RolePersonneModel;
use Modules\Complaint\Models\StatutPlainteModel;
use Modules\Hearings\Models\AudienceAffectionModel;
use Modules\Hearings\Models\DocumentAudienceModel;
use Modules\Notification\Services\NotificationMailer;
use Modules\Verdicts\Models\TypeVerdictModel;
use Modules\Verdicts\Models\VerdictAffectationJugeModel;
use Modules\Verdicts\Models\VerdictModel;

class BackofficeVerdictService
{
    private const JUDGE_NEEDLES = ['juge', 'judge', 'magistrat', 'magistrate'];
    private const DOC_EXTS      = ['pdf', 'jpg', 'jpeg', 'png'];
    private const DOC_MAX_KB    = 10240;

    private VerdictModel $verdicts;
    private TypeVerdictModel $types;
    private VerdictAffectationJugeModel $judges;
    private PlainteModel $plaintes;
    private PlainteRolePersonneModel $parties;
    private RolePersonneModel $roleTypes;
    private DocumentPlainteModel $complaintDocs;
    private DocumentAudienceModel $hearingDocs;
    private AudienceAffectionModel $assignments;
    private ConfigurationEtapePlainteModel $stageConfig;
    private StatutPlainteModel $statuses;
    private UtilisateurModel $users;
    private AuditLogModel $audit;
    private NotificationMailer $mailer;

    public function __construct()
    {
        $this->verdicts      = new VerdictModel();
        $this->types         = new TypeVerdictModel();
        $this->judges        = new VerdictAffectationJugeModel();
        $this->plaintes      = new PlainteModel();
        $this->parties       = new PlainteRolePersonneModel();
        $this->roleTypes     = new RolePersonneModel();
        $this->complaintDocs = new DocumentPlainteModel();
        $this->hearingDocs   = new DocumentAudienceModel();
        $this->assignments   = new AudienceAffectionModel();
        $this->stageConfig   = new ConfigurationEtapePlainteModel();
        $this->statuses      = new StatutPlainteModel();
        $this->users         = new UtilisateurModel();
        $this->audit         = new AuditLogModel();
        $this->mailer        = service('notifications');
    }

    /**
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    public function list(array $query = []): array
    {
        try {
            $rows = $this->verdicts->listFiltered([
                'niveau_juridiction_id' => ! empty($query['niveau_juridiction_id']) ? (int) $query['niveau_juridiction_id'] : null,
                'province_id'           => ! empty($query['province_id']) ? (int) $query['province_id'] : null,
                'commune_id'            => ! empty($query['commune_id']) ? (int) $query['commune_id'] : null,
                'juridiction_id'        => ! empty($query['juridiction_id']) ? (int) $query['juridiction_id'] : null,
                'date_verdict'          => ! empty($query['date_verdict']) ? (string) $query['date_verdict'] : null,
                'type_verdict_id'       => ! empty($query['type_verdict_id']) ? (int) $query['type_verdict_id'] : null,
                'statut_audience_id'    => ! empty($query['statut_audience_id']) ? (int) $query['statut_audience_id'] : null,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list verdicts: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static fn (array $row): array => [
            'id'          => (int) $row['verdict_id'],
            'case_number' => $row['numero_dossier'] ?? '',
            'subject'     => $row['objet'] ?? '',
            'court'       => trim(($row['desc_niveau_juridiction'] ?? '') . ' / ' . ($row['nom_juridiction'] ?? ''), ' /'),
            'type'        => $row['description_type_verdict'] ?? '—',
            'verdict_date'=> $row['date_verdict'] ?? '',
        ], $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function details(int $id): ?array
    {
        try {
            $record = $this->verdicts->findDetailed($id);
        } catch (\Throwable $e) {
            return null;
        }
        if (! $record) {
            return null;
        }

        $plainteId  = (int) ($record['plainte_id'] ?? 0);
        $audienceId = (int) ($record['audience_id'] ?? 0);
        $witnessId  = $this->roleTypes->findWitnessId();

        return [
            'record'           => $record,
            'judges'           => $this->safe(fn () => $this->judges->listByVerdict($id)),
            'staff'            => $audienceId ? $this->safe(fn () => $this->assignments->listByAudience($audienceId)) : [],
            'complainants'     => $plainteId ? $this->safe(fn () => $this->parties->listByPlainte($plainteId, RolePersonneModel::ROLE_PLAIGNANT)) : [],
            'defendants'       => $plainteId ? $this->safe(fn () => $this->parties->listByPlainte($plainteId, RolePersonneModel::ROLE_DEFENDANT)) : [],
            'witnesses'        => ($plainteId && $witnessId) ? $this->safe(fn () => $this->parties->listByPlainte($plainteId, $witnessId)) : [],
            'complaint_docs'   => $plainteId ? $this->safe(fn () => $this->complaintDocs->listByPlainte($plainteId)) : [],
            'hearing_docs'     => $audienceId ? $this->safe(fn () => $this->hearingDocs->listByAudience($audienceId)) : [],
            'appeals'          => $plainteId ? $this->safe(fn () => $this->plaintes->relatedAppeals($plainteId)) : [],
        ];
    }

    /**
     * @return list<array{id:int,label:string,audience_id:int,plainte_id:int,juridiction_id:int,niveau_id:int,hearing_date:?string}>
     */
    public function eligibleAudiencePlainteOptions(?int $juridictionId = null, ?int $niveauId = null): array
    {
        try {
            $rows = $this->verdicts->listEligibleAudiencePlaintes($juridictionId, $niveauId);
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static function (array $row): array {
            $hearingDate = $row['date_tenue'] ?? ($row['date_audience'] ?? '');

            return [
                'id'             => (int) $row['audience_plainte_id'],
                'label'          => trim(($row['numero_dossier'] ?? '') . ' — ' . ($row['objet'] ?? '') . ' (' . $hearingDate . ')'),
                'audience_id'    => (int) ($row['audience_id'] ?? 0),
                'plainte_id'     => (int) ($row['plainte_id'] ?? 0),
                'juridiction_id' => (int) ($row['juridiction_id'] ?? 0),
                'niveau_id'      => (int) ($row['niveau_juridiction_id'] ?? 0),
                'hearing_date'   => $hearingDate ? substr((string) $hearingDate, 0, 10) : null,
            ];
        }, $rows);
    }

    /**
     * Judges actively assigned to a hearing.
     *
     * @return list<array{id:int,label:string,profil_id:int,utilisateur_id:int}>
     */
    public function hearingJudgeOptions(int $audienceId): array
    {
        if ($audienceId < 1) {
            return [];
        }

        $out = [];
        foreach ($this->safe(fn () => $this->assignments->listByAudience($audienceId)) as $row) {
            if (! filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }
            $hay = mb_strtolower(($row['code_profil'] ?? '') . ' ' . ($row['libelle_profil'] ?? ''));
            $isJudge = false;
            foreach (self::JUDGE_NEEDLES as $n) {
                if (str_contains($hay, $n)) {
                    $isJudge = true;
                    break;
                }
            }
            if (! $isJudge) {
                continue;
            }
            $userId = (int) ($row['utilisateur_affecte_id'] ?? 0);
            if ($userId < 1) {
                continue;
            }
            $out[] = [
                'id'             => $userId,
                'label'          => trim((string) ($row['assignee_name'] ?? '')) ?: ('#' . $userId),
                'profil_id'      => (int) ($row['profil_id'] ?? 0),
                'utilisateur_id' => $userId,
            ];
        }

        return $out;
    }

    public function defaultAppealDeadline(string $verdictDate): string
    {
        try {
            $date = new DateTimeImmutable(substr($verdictDate, 0, 10));
        } catch (\Throwable) {
            $date = new DateTimeImmutable('today');
        }

        return $date->modify('+' . VerdictModel::APPEAL_DEADLINE_DAYS . ' days')->format('Y-m-d');
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function create(array $input, ?UploadedFile $reportFile = null): array
    {
        $errors = $this->validate($input, $reportFile);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $apId = (int) $input['audience_plainte_id'];
        $eligible = null;
        foreach ($this->eligibleAudiencePlainteOptions(
            (int) $input['juridiction_id'],
            (int) $input['niveau_juridiction_id']
        ) as $opt) {
            if ((int) $opt['id'] === $apId) {
                $eligible = $opt;
                break;
            }
        }
        if (! $eligible) {
            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_err_not_eligible')]];
        }

        $hearingDate = $eligible['hearing_date'] ?? null;
        $verdictDate = substr((string) $input['date_verdict'], 0, 10);
        if ($hearingDate && $verdictDate < $hearingDate) {
            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_err_date_before_hearing')]];
        }

        if ($this->verdicts->existsForLevel((int) $eligible['plainte_id'], (int) $input['niveau_juridiction_id'])) {
            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_err_duplicate_level')]];
        }

        $judgeIds = $this->ids($input['judge_ids'] ?? []);
        $allowedJudges = [];
        foreach ($this->hearingJudgeOptions((int) $eligible['audience_id']) as $j) {
            $allowedJudges[(int) $j['utilisateur_id']] = $j;
        }
        if ($judgeIds === []) {
            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_err_judges_required')]];
        }
        foreach ($judgeIds as $jid) {
            if (! isset($allowedJudges[$jid])) {
                return ['ok' => false, 'errors' => [lang('Backoffice.vrd_err_judges_hearing')]];
            }
        }

        $deadline = trim((string) ($input['date_limite_recours'] ?? ''));
        if ($deadline === '') {
            $deadline = $this->defaultAppealDeadline($verdictDate);
        }

        $storedPath = null;
        if ($reportFile instanceof UploadedFile && $reportFile->isValid() && ! $reportFile->hasMoved()) {
            $storedPath = $this->storeReport($reportFile);
            if ($storedPath === null) {
                return ['ok' => false, 'errors' => [lang('Backoffice.vrd_err_report_file')]];
            }
        }

        $data = [
            'audience_plainte_id'     => $apId,
            'niveau_juridiction_id'   => (int) $input['niveau_juridiction_id'],
            'type_verdict_id'         => (int) $input['type_verdict_id'],
            'date_verdict'            => $verdictDate,
            'resume'                  => trim((string) $input['resume']),
            'dispositif'              => trim((string) $input['dispositif']),
            'date_limite_recours'     => $deadline,
            'recours_exerce'          => false,
            'created_at'              => date('Y-m-d H:i:s'),
            'juridiction_id'          => (int) $input['juridiction_id'],
            'upload_rapport_verdict'  => $storedPath,
        ];

        $db = db_connect();
        $db->transStart();
        $verdictId = 0;

        try {
            $verdictId = (int) $this->verdicts->insert($data, true);
            if ($verdictId < 1) {
                throw new \RuntimeException('verdict insert failed');
            }

            foreach ($judgeIds as $jid) {
                $this->judges->insert([
                    'verdict_id'     => $verdictId,
                    'utilisateur_id' => $jid,
                    'profil_id'      => (int) $allowedJudges[$jid]['profil_id'],
                ]);
            }

            $this->advanceWorkflow((int) $eligible['plainte_id']);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            if ($storedPath) {
                $this->deleteFile($storedPath);
            }
            log_message('error', 'Failed to create verdict: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_err_save')]];
        }

        if ($db->transStatus() === false || $verdictId < 1) {
            if ($storedPath) {
                $this->deleteFile($storedPath);
            }

            return ['ok' => false, 'errors' => [lang('Backoffice.vrd_err_save')]];
        }

        $this->audit->record('CREATE', 'verdict.verdict', $verdictId, null, $data + ['judge_ids' => $judgeIds], $this->actorId());
        $this->notifyParties((int) $eligible['plainte_id'], $data, (int) $input['juridiction_id']);

        return ['ok' => true, 'id' => $verdictId];
    }

    private function advanceWorkflow(int $plainteId): void
    {
        $complaint = $this->plaintes->find($plainteId);
        if (! $complaint) {
            return;
        }

        $current = (int) ($complaint['etape_plainte_id'] ?? 0);
        $update  = ['updated_at' => date('Y-m-d H:i:s')];

        if ($current > 0) {
            $row = $this->stageConfig->builder()
                ->select('etape_plainte_suivant_id')
                ->where('etape_plainte_actuel_id', $current)
                ->where('(is_active IS NULL OR is_active = TRUE)', null, false)
                ->orderBy('configuration_etape_plainte_id', 'ASC')
                ->get(1)
                ->getRowArray();
            $nextId = (int) ($row['etape_plainte_suivant_id'] ?? 0);
            if ($nextId > 0) {
                $update['etape_plainte_id'] = $nextId;
            }
        }

        // Prefer a status that looks like "judged/decided" when available.
        try {
            foreach ($this->statuses->listFiltered(true) as $st) {
                $label = mb_strtolower((string) ($st['description_statut_plainte'] ?? ''));
                if (str_contains($label, 'jug') || str_contains($label, 'decid') || str_contains($label, 'verdict') || str_contains($label, 'clos')) {
                    $update['statut_plainte_id'] = (int) $st['statut_plainte_id'];
                    break;
                }
            }
        } catch (\Throwable $e) {
            // keep existing status
        }

        $this->plaintes->update($plainteId, $update);
        $this->audit->record(
            'UPDATE',
            'plainte.plainte',
            $plainteId,
            [
                'etape_plainte_id'  => $complaint['etape_plainte_id'] ?? null,
                'statut_plainte_id' => $complaint['statut_plainte_id'] ?? null,
            ],
            $update + ['reason' => 'verdict_issued'],
            $this->actorId()
        );
    }

    /**
     * @param array<string, mixed> $verdict
     */
    private function notifyParties(int $plainteId, array $verdict, int $courtId): void
    {
        $complaint = $this->plaintes->findForBackoffice($plainteId);
        $caseNo    = (string) ($complaint['numero_dossier'] ?? '');
        $subject   = (string) ($complaint['objet'] ?? '');
        $mailSubject = lang('Mail.subject_verdict_issued', [$caseNo]);
        $body = lang('Mail.verdict_issued_body', [
            $caseNo,
            (string) ($verdict['date_verdict'] ?? ''),
            (string) ($verdict['date_limite_recours'] ?? ''),
        ]);

        foreach ($this->safe(fn () => $this->parties->listByPlainte($plainteId)) as $party) {
            $email = trim((string) ($party['email'] ?? ''));
            $name  = trim(($party['prenom_personne'] ?? '') . ' ' . ($party['nom_personne'] ?? ''));
            if ($email === '') {
                continue;
            }
            try {
                $this->mailer->send(
                    'verdict_issued',
                    $email,
                    $name,
                    $mailSubject,
                    'Modules\Notification\Views\emails\verdict_issued',
                    [
                        'name'            => $name,
                        'complaintNumber' => $caseNo,
                        'complaintTitle'  => $subject,
                        'bodyText'        => $body,
                        'portalUrl'       => site_url('portal/complaints'),
                    ]
                );
            } catch (\Throwable $e) {
                log_message('error', 'Verdict email failed: {message}', ['message' => $e->getMessage()]);
            }
        }

        try {
            $courtUsers = $this->users->listWithRelations([
                'juridiction_id' => $courtId,
                'account_active' => true,
            ]);
        } catch (\Throwable $e) {
            $courtUsers = [];
        }

        foreach ($courtUsers as $user) {
            $email = trim((string) ($user['email'] ?? ''));
            $name  = trim(($user['prenom_utilisateur'] ?? '') . ' ' . ($user['nom_utilisateur'] ?? ''));
            if ($email === '') {
                continue;
            }
            try {
                $this->mailer->send(
                    'verdict_issued',
                    $email,
                    $name,
                    $mailSubject,
                    'Modules\Notification\Views\emails\verdict_issued',
                    [
                        'name'            => $name,
                        'complaintNumber' => $caseNo,
                        'complaintTitle'  => $subject,
                        'bodyText'        => $body,
                        'portalUrl'       => site_url('backoffice/verdicts'),
                    ]
                );
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    private function storeReport(UploadedFile $file): ?string
    {
        $ext = strtolower((string) $file->getExtension());
        if (! in_array($ext, self::DOC_EXTS, true) || $file->getSize() > self::DOC_MAX_KB * 1024) {
            return null;
        }
        $dir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'verdicts';
        if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
            return null;
        }
        $name = $file->getRandomName();
        if (! $file->move($dir, $name)) {
            return null;
        }

        return 'uploads/verdicts/' . $name;
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validate(array $input, ?UploadedFile $reportFile): array
    {
        $errors = [];
        $required = [
            'niveau_juridiction_id' => lang('Backoffice.vrd_field_level'),
            'province_id'           => lang('Backoffice.vrd_field_province'),
            'commune_id'            => lang('Backoffice.vrd_field_commune'),
            'juridiction_id'        => lang('Backoffice.vrd_field_court'),
            'audience_plainte_id'   => lang('Backoffice.vrd_field_hearing_complaint'),
            'type_verdict_id'       => lang('Backoffice.vrd_field_type'),
            'resume'                => lang('Backoffice.vrd_field_resume'),
            'dispositif'            => lang('Backoffice.vrd_field_dispositif'),
            'date_verdict'          => lang('Backoffice.vrd_field_date'),
        ];
        foreach ($required as $key => $label) {
            if (trim((string) ($input[$key] ?? '')) === '') {
                $errors[] = lang('Backoffice.vrd_err_required', [$label]);
            }
        }

        if ($reportFile instanceof UploadedFile && $reportFile->getError() !== UPLOAD_ERR_NO_FILE) {
            if (! $reportFile->isValid() || $reportFile->hasMoved()) {
                $errors[] = lang('Backoffice.vrd_err_report_file');
            } else {
                $ext = strtolower((string) $reportFile->getExtension());
                if (! in_array($ext, self::DOC_EXTS, true) || $reportFile->getSize() > self::DOC_MAX_KB * 1024) {
                    $errors[] = lang('Backoffice.vrd_err_report_file');
                }
            }
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
