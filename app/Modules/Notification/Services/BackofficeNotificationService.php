<?php

namespace Modules\Notification\Services;

use Modules\Administration\Models\AuditLogModel;
use Modules\Notification\Models\NotificationPersonneModel;
use Modules\Notification\Models\NotificationUtilisateurModel;
use Modules\Notification\Models\StatutNotificationModel;

class BackofficeNotificationService
{
    private NotificationPersonneModel $personNotifications;
    private NotificationUtilisateurModel $userNotifications;
    private StatutNotificationModel $statuses;
    private NotificationMailer $mailer;
    private AuditLogModel $audit;

    public function __construct(
        ?NotificationPersonneModel $personNotifications = null,
        ?NotificationUtilisateurModel $userNotifications = null,
        ?StatutNotificationModel $statuses = null,
        ?NotificationMailer $mailer = null,
        ?AuditLogModel $audit = null
    ) {
        $this->personNotifications = $personNotifications ?? new NotificationPersonneModel();
        $this->userNotifications   = $userNotifications ?? new NotificationUtilisateurModel();
        $this->statuses            = $statuses ?? new StatutNotificationModel();
        $this->mailer              = $mailer ?? service('notifications');
        $this->audit               = $audit ?? new AuditLogModel();
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listComplainantNotifications(array $filters = []): array
    {
        $db = db_connect();

        try {
            $builder = $db->table('notification.notification_personne n')
                ->select("
                    n.notification_personne_id AS id,
                    n.sujet,
                    n.envoye_le,
                    n.lu_le,
                    n.canal_notification_id,
                    n.statut_notification_id,
                    n.plainte_id,
                    n.personne_id,
                    c.description_canal_notification AS channel,
                    s.description_statut_notification AS status_label,
                    TRIM(CONCAT(COALESCE(p.prenom_personne, ''), ' ', COALESCE(p.nom_personne, ''))) AS recipient_name,
                    pl.numero_dossier AS case_number,
                    p.province_naissance_id,
                    p.commune_naissance_id
                ", false)
                ->join('notification.canal_notification c', 'c.canal_notification_id = n.canal_notification_id', 'left')
                ->join('notification.statut_notification s', 's.statut_notification_id = n.statut_notification_id', 'left')
                ->join('plaignant.personne p', 'p.personne_id = n.personne_id', 'left')
                ->join('plainte.plainte pl', 'pl.plainte_id = n.plainte_id', 'left');

            if (! empty($filters['canal_notification_id'])) {
                $builder->where('n.canal_notification_id', (int) $filters['canal_notification_id']);
            }
            if (! empty($filters['statut_notification_id'])) {
                $builder->where('n.statut_notification_id', (int) $filters['statut_notification_id']);
            }
            if (! empty($filters['province_id'])) {
                $builder->where('p.province_naissance_id', (int) $filters['province_id']);
            }
            if (! empty($filters['commune_id'])) {
                $builder->where('p.commune_naissance_id', (int) $filters['commune_id']);
            }
            if (! empty($filters['personne_id'])) {
                $builder->where('n.personne_id', (int) $filters['personne_id']);
            }
            if (! empty($filters['plainte_id'])) {
                $builder->where('n.plainte_id', (int) $filters['plainte_id']);
            }
            if (! empty($filters['date_from'])) {
                $builder->where('n.envoye_le >=', $filters['date_from'] . ' 00:00:00');
            }
            if (! empty($filters['date_to'])) {
                $builder->where('n.envoye_le <=', $filters['date_to'] . ' 23:59:59');
            }

            $rows = $builder->orderBy('n.envoye_le', 'DESC')->orderBy('n.notification_personne_id', 'DESC')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list complainant notifications: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            return [
                'id'            => (int) $row['id'],
                'subject'       => (string) ($row['sujet'] ?? ''),
                'recipient'     => trim((string) ($row['recipient_name'] ?? '')) ?: '—',
                'channel'       => (string) ($row['channel'] ?? '—'),
                'case_number'   => (string) ($row['case_number'] ?? '—'),
                'status_label'  => (string) ($row['status_label'] ?? '—'),
                'sent_at'       => self::formatDateTime($row['envoye_le'] ?? null),
                'read_at'       => self::formatDateTime($row['lu_le'] ?? null, true),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findComplainantNotification(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $db = db_connect();

        try {
            $row = $db->table('notification.notification_personne n')
                ->select("
                    n.*,
                    c.description_canal_notification AS channel,
                    s.description_statut_notification AS status_label,
                    TRIM(CONCAT(COALESCE(p.prenom_personne, ''), ' ', COALESCE(p.nom_personne, ''))) AS recipient_name,
                    p.email AS recipient_email,
                    p.telephone AS recipient_phone,
                    p.numero_cni AS recipient_cni,
                    pl.numero_dossier AS case_number,
                    pl.objet AS case_subject
                ", false)
                ->join('notification.canal_notification c', 'c.canal_notification_id = n.canal_notification_id', 'left')
                ->join('notification.statut_notification s', 's.statut_notification_id = n.statut_notification_id', 'left')
                ->join('plaignant.personne p', 'p.personne_id = n.personne_id', 'left')
                ->join('plainte.plainte pl', 'pl.plainte_id = n.plainte_id', 'left')
                ->where('n.notification_personne_id', $id)
                ->get()
                ->getFirstRow('array');
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load complainant notification {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $row) {
            return null;
        }

        return $this->mapPersonDetail($row);
    }

    /**
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function resendComplainantNotification(int $id): array
    {
        $original = $this->findComplainantNotification($id);
        if (! $original) {
            return ['ok' => false, 'errors' => [lang('Backoffice.ntf_err_not_found')]];
        }

        $channel = mb_strtoupper((string) ($original['channel'] ?? ''));
        $email   = trim((string) ($original['recipient_email'] ?? ''));
        $phone   = trim((string) ($original['recipient_phone'] ?? ''));
        $name    = (string) ($original['recipient'] ?? '');
        $subject = (string) ($original['subject'] ?? '');
        $body    = (string) ($original['body'] ?? '');

        $sentOk     = false;
        $statusId   = $this->statuses->idByKeywords(['échec', 'echec', 'fail']) ?? 4;
        $sentStatus = $this->statuses->idByKeywords(['envoy', 'sent', 'deliver']) ?? 2;
        $pendingId  = $this->statuses->idByKeywords(['attente', 'pending']) ?? 1;

        if (str_contains($channel, 'EMAIL') || str_contains($channel, 'MAIL')) {
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['ok' => false, 'errors' => [lang('Backoffice.ntf_err_no_email')]];
            }
            $sentOk   = $this->mailer->send(
                'notification_resend',
                $email,
                $name,
                $subject,
                'Modules\Notification\Views\emails\generic_message',
                ['name' => $name, 'subject' => $subject, 'body' => $body]
            );
            $statusId = $sentOk ? $sentStatus : ($this->statuses->idByKeywords(['échec', 'echec', 'fail']) ?? 4);
            if (! $sentOk) {
                return ['ok' => false, 'errors' => [lang('Backoffice.ntf_err_resend') . ' ' . $this->mailer->getLastError()]];
            }
        } elseif (str_contains($channel, 'SMS')) {
            if ($phone === '') {
                return ['ok' => false, 'errors' => [lang('Backoffice.ntf_err_no_phone')]];
            }
            // SMS gateway is not wired yet — keep history with pending status.
            log_message('info', 'SMS resend queued historically for person notification {id} to {phone}', [
                'id'    => $id,
                'phone' => $phone,
            ]);
            $statusId = $pendingId;
            $sentOk   = true;
        } else {
            // Platform / other channel: mark as sent (in-app history).
            $statusId = $sentStatus;
            $sentOk   = true;
        }

        $now = date('Y-m-d H:i:s');
        $newId = null;

        try {
            $this->personNotifications->insert([
                'personne_id'            => (int) $original['personne_id'],
                'canal_notification_id'  => (int) $original['canal_notification_id'],
                'sujet'                  => $subject,
                'corps'                  => $body,
                'plainte_id'             => $original['plainte_id'] ?: null,
                'statut_notification_id' => $statusId,
                'envoye_le'              => $now,
                'lu_le'                  => null,
                'created_at'             => $now,
            ]);
            $newId = (int) $this->personNotifications->getInsertID();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert resent complainant notification: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.ntf_err_resend')]];
        }

        $this->audit->record(
            'RESEND',
            'notification.notification_personne',
            $newId,
            ['source_notification_id' => $id],
            [
                'notification_personne_id' => $newId,
                'personne_id'              => $original['personne_id'],
                'canal'                    => $original['channel'],
                'sujet'                    => $subject,
                'sent_ok'                  => $sentOk,
            ],
            $this->actorId()
        );

        return ['ok' => true, 'id' => $newId];
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listUserNotifications(array $filters = []): array
    {
        $db = db_connect();

        try {
            $builder = $db->table('notification.notification_utilisateur n')
                ->select("
                    n.notification_utilisateur_id AS id,
                    n.sujet,
                    n.envoye_le,
                    n.lu_le,
                    n.canal_notification_id,
                    n.statut_notification_id,
                    n.utilisateur_id,
                    c.description_canal_notification AS channel,
                    s.description_statut_notification AS status_label,
                    TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS recipient_name,
                    pr.libelle_profil AS profile_name,
                    j.nom_juridiction AS court_name,
                    u.profil_id,
                    u.juridiction_id,
                    j.province_id,
                    j.commune_id,
                    j.niveau_juridiction_id
                ", false)
                ->join('notification.canal_notification c', 'c.canal_notification_id = n.canal_notification_id', 'left')
                ->join('notification.statut_notification s', 's.statut_notification_id = n.statut_notification_id', 'left')
                ->join('administration.utilisateur u', 'u.utilisateur_id = n.utilisateur_id', 'left')
                ->join('administration.profil pr', 'pr.profil_id = u.profil_id', 'left')
                ->join('juridiction.juridiction j', 'j.juridiction_id = u.juridiction_id', 'left');

            if (! empty($filters['canal_notification_id'])) {
                $builder->where('n.canal_notification_id', (int) $filters['canal_notification_id']);
            }
            if (! empty($filters['statut_notification_id'])) {
                $builder->where('n.statut_notification_id', (int) $filters['statut_notification_id']);
            }
            if (! empty($filters['province_id'])) {
                $builder->where('j.province_id', (int) $filters['province_id']);
            }
            if (! empty($filters['commune_id'])) {
                $builder->where('j.commune_id', (int) $filters['commune_id']);
            }
            if (! empty($filters['niveau_juridiction_id'])) {
                $builder->where('j.niveau_juridiction_id', (int) $filters['niveau_juridiction_id']);
            }
            if (! empty($filters['juridiction_id'])) {
                $builder->where('u.juridiction_id', (int) $filters['juridiction_id']);
            }
            if (! empty($filters['utilisateur_id'])) {
                $builder->where('n.utilisateur_id', (int) $filters['utilisateur_id']);
            }
            if (! empty($filters['profil_id'])) {
                $builder->where('u.profil_id', (int) $filters['profil_id']);
            }
            if (! empty($filters['date_from'])) {
                $builder->where('n.envoye_le >=', $filters['date_from'] . ' 00:00:00');
            }
            if (! empty($filters['date_to'])) {
                $builder->where('n.envoye_le <=', $filters['date_to'] . ' 23:59:59');
            }

            $rows = $builder->orderBy('n.envoye_le', 'DESC')->orderBy('n.notification_utilisateur_id', 'DESC')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list user notifications: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            return [
                'id'           => (int) $row['id'],
                'subject'      => (string) ($row['sujet'] ?? ''),
                'recipient'    => trim((string) ($row['recipient_name'] ?? '')) ?: '—',
                'profile_name' => (string) ($row['profile_name'] ?? '—'),
                'court_name'   => (string) ($row['court_name'] ?? '—'),
                'channel'      => (string) ($row['channel'] ?? '—'),
                'status_label' => (string) ($row['status_label'] ?? '—'),
                'sent_at'      => self::formatDateTime($row['envoye_le'] ?? null),
                'read_at'      => self::formatDateTime($row['lu_le'] ?? null, true),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findUserNotification(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $db = db_connect();

        try {
            $row = $db->table('notification.notification_utilisateur n')
                ->select("
                    n.*,
                    c.description_canal_notification AS channel,
                    s.description_statut_notification AS status_label,
                    TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS recipient_name,
                    u.email AS recipient_email,
                    u.telephone AS recipient_phone,
                    u.numero_matricule AS recipient_matricule,
                    pr.libelle_profil AS profile_name,
                    j.nom_juridiction AS court_name
                ", false)
                ->join('notification.canal_notification c', 'c.canal_notification_id = n.canal_notification_id', 'left')
                ->join('notification.statut_notification s', 's.statut_notification_id = n.statut_notification_id', 'left')
                ->join('administration.utilisateur u', 'u.utilisateur_id = n.utilisateur_id', 'left')
                ->join('administration.profil pr', 'pr.profil_id = u.profil_id', 'left')
                ->join('juridiction.juridiction j', 'j.juridiction_id = u.juridiction_id', 'left')
                ->where('n.notification_utilisateur_id', $id)
                ->get()
                ->getFirstRow('array');
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load user notification {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $row) {
            return null;
        }

        return $this->mapUserDetail($row);
    }

    /**
     * @return array{ok:bool,errors?:list<string>,id?:int}
     */
    public function resendUserNotification(int $id): array
    {
        $original = $this->findUserNotification($id);
        if (! $original) {
            return ['ok' => false, 'errors' => [lang('Backoffice.ntf_err_not_found')]];
        }

        $channel = mb_strtoupper((string) ($original['channel'] ?? ''));
        $email   = trim((string) ($original['recipient_email'] ?? ''));
        $phone   = trim((string) ($original['recipient_phone'] ?? ''));
        $name    = (string) ($original['recipient'] ?? '');
        $subject = (string) ($original['subject'] ?? '');
        $body    = (string) ($original['body'] ?? '');

        $sentOk     = false;
        $sentStatus = $this->statuses->idByKeywords(['envoy', 'sent', 'deliver']) ?? 2;
        $pendingId  = $this->statuses->idByKeywords(['attente', 'pending']) ?? 1;
        $failId     = $this->statuses->idByKeywords(['échec', 'echec', 'fail']) ?? 4;
        $statusId   = $failId;

        if (str_contains($channel, 'EMAIL') || str_contains($channel, 'MAIL')) {
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['ok' => false, 'errors' => [lang('Backoffice.ntf_err_no_email')]];
            }
            $sentOk = $this->mailer->send(
                'notification_resend',
                $email,
                $name,
                $subject,
                'Modules\Notification\Views\emails\generic_message',
                ['name' => $name, 'subject' => $subject, 'body' => $body]
            );
            $statusId = $sentOk ? $sentStatus : $failId;
            if (! $sentOk) {
                return ['ok' => false, 'errors' => [lang('Backoffice.ntf_err_resend') . ' ' . $this->mailer->getLastError()]];
            }
        } elseif (str_contains($channel, 'SMS')) {
            if ($phone === '') {
                return ['ok' => false, 'errors' => [lang('Backoffice.ntf_err_no_phone')]];
            }
            log_message('info', 'SMS resend queued historically for user notification {id} to {phone}', [
                'id'    => $id,
                'phone' => $phone,
            ]);
            $statusId = $pendingId;
            $sentOk   = true;
        } else {
            $statusId = $sentStatus;
            $sentOk   = true;
        }

        $now   = date('Y-m-d H:i:s');
        $newId = null;

        try {
            $this->userNotifications->insert([
                'utilisateur_id'         => (int) $original['utilisateur_id'],
                'canal_notification_id'  => (int) $original['canal_notification_id'],
                'sujet'                  => $subject,
                'corps'                  => $body,
                'statut_notification_id' => $statusId,
                'envoye_le'              => $now,
                'lu_le'                  => null,
                'created_at'             => $now,
            ]);
            $newId = (int) $this->userNotifications->getInsertID();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to insert resent user notification: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.ntf_err_resend')]];
        }

        $this->audit->record(
            'RESEND',
            'notification.notification_utilisateur',
            $newId,
            ['source_notification_id' => $id],
            [
                'notification_utilisateur_id' => $newId,
                'utilisateur_id'              => $original['utilisateur_id'],
                'canal'                       => $original['channel'],
                'sujet'                       => $subject,
                'sent_ok'                     => $sentOk,
            ],
            $this->actorId()
        );

        return ['ok' => true, 'id' => $newId];
    }

    /**
     * @return list<array{id:int,label:string}>
     */
    public function complainantOptions(): array
    {
        try {
            $rows = db_connect()->query("
                SELECT DISTINCT p.personne_id AS id,
                       TRIM(CONCAT(COALESCE(p.prenom_personne, ''), ' ', COALESCE(p.nom_personne, ''))) AS label
                FROM notification.notification_personne n
                JOIN plaignant.personne p ON p.personne_id = n.personne_id
                ORDER BY label
            ")->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static fn (array $r): array => [
            'id'    => (int) $r['id'],
            'label' => trim((string) $r['label']) ?: ('#' . $r['id']),
        ], $rows);
    }

    /**
     * @return list<array{id:int,label:string}>
     */
    public function complaintOptions(): array
    {
        try {
            $rows = db_connect()->query("
                SELECT DISTINCT pl.plainte_id AS id,
                       COALESCE(pl.numero_dossier, CONCAT('ID ', pl.plainte_id::text)) AS label
                FROM notification.notification_personne n
                JOIN plainte.plainte pl ON pl.plainte_id = n.plainte_id
                ORDER BY label
            ")->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static fn (array $r): array => [
            'id'    => (int) $r['id'],
            'label' => (string) $r['label'],
        ], $rows);
    }

    /**
     * @return list<array{id:int,label:string}>
     */
    public function userOptions(): array
    {
        try {
            $rows = db_connect()->query("
                SELECT DISTINCT u.utilisateur_id AS id,
                       TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS label
                FROM notification.notification_utilisateur n
                JOIN administration.utilisateur u ON u.utilisateur_id = n.utilisateur_id
                ORDER BY label
            ")->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static fn (array $r): array => [
            'id'    => (int) $r['id'],
            'label' => trim((string) $r['label']) ?: ('#' . $r['id']),
        ], $rows);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function mapPersonDetail(array $row): array
    {
        return [
            'id'                     => (int) $row['notification_personne_id'],
            'personne_id'            => (int) ($row['personne_id'] ?? 0),
            'plainte_id'             => ! empty($row['plainte_id']) ? (int) $row['plainte_id'] : null,
            'canal_notification_id'  => (int) ($row['canal_notification_id'] ?? 0),
            'statut_notification_id' => (int) ($row['statut_notification_id'] ?? 0),
            'recipient'              => trim((string) ($row['recipient_name'] ?? '')) ?: '—',
            'recipient_email'        => (string) ($row['recipient_email'] ?? ''),
            'recipient_phone'        => (string) ($row['recipient_phone'] ?? ''),
            'recipient_cni'          => (string) ($row['recipient_cni'] ?? ''),
            'case_number'            => (string) ($row['case_number'] ?? '—'),
            'case_subject'           => (string) ($row['case_subject'] ?? '—'),
            'channel'                => (string) ($row['channel'] ?? '—'),
            'subject'                => (string) ($row['sujet'] ?? ''),
            'body'                   => (string) ($row['corps'] ?? ''),
            'status_label'           => (string) ($row['status_label'] ?? '—'),
            'sent_at'                => self::formatDateTime($row['envoye_le'] ?? null),
            'read_at'                => self::formatDateTime($row['lu_le'] ?? null, true),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function mapUserDetail(array $row): array
    {
        return [
            'id'                     => (int) $row['notification_utilisateur_id'],
            'utilisateur_id'         => (int) ($row['utilisateur_id'] ?? 0),
            'canal_notification_id'  => (int) ($row['canal_notification_id'] ?? 0),
            'statut_notification_id' => (int) ($row['statut_notification_id'] ?? 0),
            'recipient'              => trim((string) ($row['recipient_name'] ?? '')) ?: '—',
            'recipient_email'        => (string) ($row['recipient_email'] ?? ''),
            'recipient_phone'        => (string) ($row['recipient_phone'] ?? ''),
            'recipient_matricule'     => (string) ($row['recipient_matricule'] ?? ''),
            'profile_name'           => (string) ($row['profile_name'] ?? '—'),
            'court_name'             => (string) ($row['court_name'] ?? '—'),
            'channel'                => (string) ($row['channel'] ?? '—'),
            'subject'                => (string) ($row['sujet'] ?? ''),
            'body'                   => (string) ($row['corps'] ?? ''),
            'status_label'           => (string) ($row['status_label'] ?? '—'),
            'sent_at'                => self::formatDateTime($row['envoye_le'] ?? null),
            'read_at'                => self::formatDateTime($row['lu_le'] ?? null, true),
        ];
    }

    private function actorId(): ?int
    {
        $id = session('backoffice_user_id');

        return $id ? (int) $id : null;
    }

    private static function formatDateTime(mixed $value, bool $allowEmpty = false): string
    {
        if ($value === null || $value === '') {
            return $allowEmpty ? '—' : '—';
        }

        $ts = strtotime((string) $value);

        return $ts ? date('Y-m-d H:i:s', $ts) : (string) $value;
    }
}
