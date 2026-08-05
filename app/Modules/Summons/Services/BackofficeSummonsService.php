<?php

namespace Modules\Summons\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Administration\Models\UtilisateurModel;
use Modules\Complaint\Models\ConfigurationEtapePlainteModel;
use Modules\Complaint\Models\EtapePlainteModel;
use Modules\Complaint\Models\PlainteModel;
use Modules\Complaint\Models\PlainteRolePersonneModel;
use Modules\CourtJurisdiction\Models\JuridictionModel;
use Modules\Notification\Services\NotificationMailer;
use Modules\Summons\Models\ConvocationDestinataireModel;
use Modules\Summons\Models\ConvocationModel;
use Modules\Summons\Models\StatutConvocationModel;

class BackofficeSummonsService
{
    private ConvocationModel $summons;
    private ConvocationDestinataireModel $recipients;
    private StatutConvocationModel $statuses;
    private PlainteModel $plaintes;
    private PlainteRolePersonneModel $parties;
    private ConfigurationEtapePlainteModel $stageConfig;
    private EtapePlainteModel $stages;
    private JuridictionModel $courts;
    private UtilisateurModel $users;
    private AuditLogModel $audit;
    private NotificationMailer $mailer;

    public function __construct()
    {
        $this->summons     = new ConvocationModel();
        $this->recipients  = new ConvocationDestinataireModel();
        $this->statuses    = new StatutConvocationModel();
        $this->plaintes    = new PlainteModel();
        $this->parties     = new PlainteRolePersonneModel();
        $this->stageConfig = new ConfigurationEtapePlainteModel();
        $this->stages      = new EtapePlainteModel();
        $this->courts      = new JuridictionModel();
        $this->users       = new UtilisateurModel();
        $this->audit       = new AuditLogModel();
        $this->mailer      = service('notifications');
    }

    /**
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    public function list(array $query = []): array
    {
        try {
            $rows = $this->summons->listFiltered([
                'niveau_juridiction_id' => ! empty($query['niveau_juridiction_id']) ? (int) $query['niveau_juridiction_id'] : null,
                'province_id'           => ! empty($query['province_id']) ? (int) $query['province_id'] : null,
                'commune_id'            => ! empty($query['commune_id']) ? (int) $query['commune_id'] : null,
                'juridiction_id'        => ! empty($query['juridiction_id']) ? (int) $query['juridiction_id'] : null,
                'date_audience'         => ! empty($query['date_audience']) ? (string) $query['date_audience'] : null,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list summons: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $level = $row['desc_niveau_juridiction'] ?? '';
            $court = $row['nom_juridiction'] ?? '';
            $location = implode(' / ', array_filter([
                $row['province_name'] ?? null,
                $row['commune_name'] ?? null,
                $row['zone_name'] ?? null,
                $row['colline_name'] ?? null,
            ]));
            $hearingAt = trim(($row['date_audience'] ?? '') . ' ' . substr((string) ($row['heure_audience'] ?? ''), 0, 5));

            return [
                'id'           => (int) $row['convocation_id'],
                'case_number'  => $row['numero_dossier'] ?? '',
                'subject'      => $row['objet'] ?? '',
                'court'        => trim(($level ? $level . ' / ' : '') . $court, ' /'),
                'location'     => $location !== '' ? $location : ($row['lieu_audience'] ?? '—'),
                'hearing_at'   => $hearingAt !== '' ? $hearingAt : '—',
                'status'       => $row['description_statut_convocation'] ?? '—',
            ];
        }, $rows);
    }

    /**
     * @param array<string, mixed> $query
     * @return list<array<string, mixed>>
     */
    public function listPendingComplaints(array $query = []): array
    {
        try {
            $rows = $this->summons->listComplaintsRequiringSummons([
                'niveau_juridiction_id' => ! empty($query['niveau_juridiction_id']) ? (int) $query['niveau_juridiction_id'] : null,
                'province_id'           => ! empty($query['province_id']) ? (int) $query['province_id'] : null,
                'commune_id'            => ! empty($query['commune_id']) ? (int) $query['commune_id'] : null,
                'juridiction_id'        => ! empty($query['juridiction_id']) ? (int) $query['juridiction_id'] : null,
                'date_depot'            => ! empty($query['date_depot']) ? (string) $query['date_depot'] : null,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list complaints requiring summons: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            $level = $row['desc_niveau_juridiction'] ?? '';
            $court = $row['nom_juridiction'] ?? '';

            return [
                'id'            => (int) $row['plainte_id'],
                'case_number'   => $row['numero_dossier'] ?? '',
                'subject'       => $row['objet'] ?? '',
                'parties_count' => (int) ($row['people_count'] ?? 0),
                'parcels_count' => (int) ($row['parcels_count'] ?? 0),
                'court'         => trim(($level ? $level . ' / ' : '') . $court, ' /'),
                'filing_date'   => $row['date_depot'] ?? '',
                'stage'         => $row['description_etape_plainte'] ?? '—',
                'status'        => $row['description_statut_plainte'] ?? '—',
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function details(int $id): ?array
    {
        try {
            $record = $this->summons->findDetailed($id);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load summons {id}: {message}', ['id' => $id, 'message' => $e->getMessage()]);

            return null;
        }

        if (! $record) {
            return null;
        }

        $recipients = [];
        try {
            $recipients = $this->recipients->listByConvocation($id);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load summons recipients: {message}', ['message' => $e->getMessage()]);
        }

        return [
            'record'     => $record,
            'recipients' => $recipients,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findEligibleComplaint(int $plainteId): ?array
    {
        try {
            $complaint = $this->plaintes->findForBackoffice($plainteId);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $complaint) {
            return null;
        }

        $stageId = (int) ($complaint['etape_plainte_id'] ?? 0);
        if ($stageId < 1) {
            return null;
        }

        try {
            $stage = $this->stages->find($stageId);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $stage || ! filter_var($stage['is_convocation'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }

        return $complaint;
    }

    /**
     * @param array<string, mixed> $input
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function create(int $plainteId, array $input): array
    {
        $complaint = $this->findEligibleComplaint($plainteId);
        if (! $complaint) {
            return ['ok' => false, 'errors' => [lang('Backoffice.sum_err_not_eligible')]];
        }

        $errors = $this->validate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $statusId = $this->statuses->findDefaultId();
        if (! $statusId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.sum_err_no_status')]];
        }

        $courtId = (int) $input['juridiction_lieu_audience_id'];
        $court   = $this->courts->find($courtId);
        if (! $court) {
            return ['ok' => false, 'errors' => [lang('Backoffice.sum_err_court')]];
        }

        $parties = $this->parties->listByPlainte($plainteId);
        if ($parties === []) {
            return ['ok' => false, 'errors' => [lang('Backoffice.sum_err_no_parties')]];
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'plainte_id'                   => $plainteId,
            'niveau_juridiction_id'        => (int) ($complaint['niveau_juridiction_id'] ?? ($court['niveau_juridiction_id'] ?? 0)),
            'date_audience'                => (string) $input['date_audience'],
            'heure_audience'               => (string) $input['heure_audience'],
            'province_lieu_audience_id'    => (int) $input['province_lieu_audience_id'],
            'commune_lieu_audience_id'     => (int) $input['commune_lieu_audience_id'],
            'zone_lieu_audience_id'        => ! empty($input['zone_lieu_audience_id']) ? (int) $input['zone_lieu_audience_id'] : null,
            'colline_lieu_audience_id'     => ! empty($input['colline_lieu_audience_id']) ? (int) $input['colline_lieu_audience_id'] : null,
            'lieu_audience'                => trim((string) ($input['lieu_audience'] ?? '')),
            'emise_le'                     => date('Y-m-d'),
            'emise_par'                    => $this->actorId(),
            'statut_convocation_id'        => $statusId,
            'observations'                 => trim((string) ($input['observations'] ?? '')) ?: null,
            'created_at'                   => $now,
            'juridiction_lieu_audience_id' => $courtId,
        ];

        $db = db_connect();
        $db->transStart();
        $convocationId = 0;

        try {
            $convocationId = (int) $this->summons->insert($data, true);
            if ($convocationId < 1) {
                throw new \RuntimeException('convocation insert failed');
            }

            foreach ($parties as $party) {
                $roleId = (int) ($party['plainte_role_personne_id'] ?? 0);
                if ($roleId < 1) {
                    continue;
                }
                $this->recipients->insert([
                    'convocation_id'           => $convocationId,
                    'plainte_role_personne_id' => $roleId,
                    'statut_convocation_id'    => $statusId,
                    'created_at'               => $now,
                ]);
            }

            $this->advanceWorkflow($plainteId, (int) ($complaint['etape_plainte_id'] ?? 0));
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to create summons: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.sum_err_save')]];
        }

        if ($db->transStatus() === false || $convocationId < 1) {
            return ['ok' => false, 'errors' => [lang('Backoffice.sum_err_save')]];
        }

        $this->audit->record('CREATE', 'convocation.convocation', $convocationId, null, $data, $this->actorId());
        $this->notifyPartiesAndCourt($complaint, $data, $parties, $courtId);

        return ['ok' => true, 'id' => $convocationId];
    }

    private function advanceWorkflow(int $plainteId, int $currentStageId): void
    {
        if ($currentStageId < 1) {
            return;
        }

        $row = $this->stageConfig->builder()
            ->select('etape_plainte_suivant_id')
            ->where('etape_plainte_actuel_id', $currentStageId)
            ->where('(is_active IS NULL OR is_active = TRUE)', null, false)
            ->orderBy('configuration_etape_plainte_id', 'ASC')
            ->get(1)
            ->getRowArray();

        $nextId = (int) ($row['etape_plainte_suivant_id'] ?? 0);
        if ($nextId < 1) {
            return;
        }

        $before = $this->plaintes->find($plainteId);
        $this->plaintes->update($plainteId, [
            'etape_plainte_id' => $nextId,
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $this->audit->record(
            'UPDATE',
            'plainte.plainte',
            $plainteId,
            ['etape_plainte_id' => $before['etape_plainte_id'] ?? null],
            ['etape_plainte_id' => $nextId, 'reason' => 'summons_generated'],
            $this->actorId()
        );
    }

    /**
     * @param array<string, mixed> $complaint
     * @param array<string, mixed> $summons
     * @param list<array<string, mixed>> $parties
     */
    private function notifyPartiesAndCourt(array $complaint, array $summons, array $parties, int $courtId): void
    {
        $caseNumber = (string) ($complaint['numero_dossier'] ?? '');
        $subject    = (string) ($complaint['objet'] ?? '');
        $hearingDate = (string) ($summons['date_audience'] ?? '');
        $hearingTime = substr((string) ($summons['heure_audience'] ?? ''), 0, 5);
        $venue       = (string) ($summons['lieu_audience'] ?? '');
        $mailSubject = lang('Mail.subject_summons_issued', [$caseNumber]);
        $mailBody    = lang('Mail.summons_issued_body', [$caseNumber, $hearingDate, $hearingTime, $venue]);

        $canalId  = $this->lookupId('notification.canal_notification', 'canal_notification_id', 'description_canal_notification', ['email', 'e-mail', 'mail']);
        $statutId = $this->lookupId('notification.statut_notification', 'statut_notification_id', 'description_statut_notification', ['envoyé', 'envoye', 'sent', 'delivered']);

        foreach ($parties as $party) {
            $name  = trim(($party['prenom_personne'] ?? '') . ' ' . ($party['nom_personne'] ?? ''));
            $email = trim((string) ($party['email'] ?? ''));
            $personneId = (int) ($party['personne_id'] ?? 0);

            $this->insertPersonNotification($personneId, $canalId, $statutId, $mailSubject, $mailBody, (int) ($complaint['plainte_id'] ?? 0));

            if ($email !== '') {
                try {
                    $this->mailer->sendSummonsIssued($email, $name, $caseNumber, $subject, $hearingDate, $hearingTime, $venue);
                } catch (\Throwable $e) {
                    log_message('error', 'Summons email failed for {email}: {message}', [
                        'email'   => $email,
                        'message' => $e->getMessage(),
                    ]);
                }
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
            $userId = (int) ($user['utilisateur_id'] ?? 0);
            $name   = trim(($user['prenom_utilisateur'] ?? '') . ' ' . ($user['nom_utilisateur'] ?? ''));
            $email  = trim((string) ($user['email'] ?? ''));

            $this->insertUserNotification($userId, $canalId, $statutId, $mailSubject, $mailBody);

            if ($email !== '') {
                try {
                    $this->mailer->sendSummonsIssued($email, $name, $caseNumber, $subject, $hearingDate, $hearingTime, $venue);
                } catch (\Throwable $e) {
                    log_message('error', 'Summons court email failed for {email}: {message}', [
                        'email'   => $email,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    private function insertPersonNotification(
        int $personneId,
        ?int $canalId,
        ?int $statutId,
        string $subject,
        string $body,
        int $plainteId
    ): void {
        if ($personneId < 1 || ! $canalId || ! $statutId) {
            return;
        }

        try {
            db_connect()->table('notification.notification_personne')->insert([
                'personne_id'            => $personneId,
                'canal_notification_id'  => $canalId,
                'sujet'                  => $subject,
                'corps'                  => $body,
                'plainte_id'             => $plainteId ?: null,
                'statut_notification_id' => $statutId,
                'envoye_le'              => date('Y-m-d H:i:s'),
                'created_at'             => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert notification_personne: {message}', ['message' => $e->getMessage()]);
        }
    }

    private function insertUserNotification(
        int $utilisateurId,
        ?int $canalId,
        ?int $statutId,
        string $subject,
        string $body
    ): void {
        if ($utilisateurId < 1 || ! $canalId || ! $statutId) {
            return;
        }

        try {
            db_connect()->table('notification.notification_utilisateur')->insert([
                'utilisateur_id'         => $utilisateurId,
                'canal_notification_id'  => $canalId,
                'sujet'                  => $subject,
                'corps'                  => $body,
                'statut_notification_id' => $statutId,
                'envoye_le'              => date('Y-m-d H:i:s'),
                'created_at'             => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert notification_utilisateur: {message}', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param list<string> $needles
     */
    private function lookupId(string $table, string $idCol, string $labelCol, array $needles): ?int
    {
        try {
            $rows = db_connect()->table($table)->select("{$idCol}, {$labelCol}")->get()->getResultArray();
        } catch (\Throwable $e) {
            return null;
        }

        foreach ($rows as $row) {
            $label = mb_strtolower(trim((string) ($row[$labelCol] ?? '')));
            foreach ($needles as $needle) {
                if ($label !== '' && str_contains($label, mb_strtolower($needle))) {
                    return (int) $row[$idCol];
                }
            }
        }

        $first = $rows[0] ?? null;

        return $first ? (int) $first[$idCol] : null;
    }

    /**
     * @param array<string, mixed> $input
     * @return list<string>
     */
    private function validate(array $input): array
    {
        $errors = [];
        $required = [
            'date_audience'                => lang('Backoffice.sum_field_hearing_date'),
            'heure_audience'               => lang('Backoffice.sum_field_hearing_time'),
            'juridiction_lieu_audience_id' => lang('Backoffice.sum_field_court'),
            'province_lieu_audience_id'    => lang('Backoffice.sum_field_province'),
            'commune_lieu_audience_id'     => lang('Backoffice.sum_field_commune'),
            'lieu_audience'                => lang('Backoffice.sum_field_venue'),
        ];
        foreach ($required as $key => $label) {
            if (trim((string) ($input[$key] ?? '')) === '') {
                $errors[] = lang('Backoffice.sum_err_required', [$label]);
            }
        }

        return $errors;
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }
}
