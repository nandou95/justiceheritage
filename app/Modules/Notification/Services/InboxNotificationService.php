<?php

namespace Modules\Notification\Services;

use Modules\Notification\Models\NotificationUtilisateurModel;
use Modules\Notification\Models\StatutNotificationModel;

/**
 * Inbox helpers for the currently authenticated back-office user.
 */
class InboxNotificationService
{
    public const UNREAD_STATUS_ID = 1;

    private NotificationUtilisateurModel $notifications;
    private StatutNotificationModel $statuses;

    public function __construct(
        ?NotificationUtilisateurModel $notifications = null,
        ?StatutNotificationModel $statuses = null
    ) {
        $this->notifications = $notifications ?? new NotificationUtilisateurModel();
        $this->statuses      = $statuses ?? new StatutNotificationModel();
    }

    public function unreadCount(int $utilisateurId): int
    {
        if ($utilisateurId < 1) {
            return 0;
        }

        try {
            return (int) db_connect()->table('notification.notification_utilisateur')
                ->where('utilisateur_id', $utilisateurId)
                ->where('statut_notification_id', self::UNREAD_STATUS_ID)
                ->countAllResults();
        } catch (\Throwable $e) {
            log_message('error', 'Unread notification count failed: {message}', ['message' => $e->getMessage()]);

            return 0;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function unreadForUser(int $utilisateurId, int $limit = 8): array
    {
        if ($utilisateurId < 1) {
            return [];
        }

        try {
            $rows = db_connect()->table('notification.notification_utilisateur AS n')
                ->select('
                    n.notification_utilisateur_id,
                    n.sujet,
                    n.corps,
                    n.created_at,
                    n.envoye_le,
                    n.lu_le,
                    n.statut_notification_id,
                    n.canal_notification_id,
                    c.description_canal_notification AS channel_label
                ')
                ->join(
                    'notification.canal_notification AS c',
                    'c.canal_notification_id = n.canal_notification_id',
                    'left'
                )
                ->where('n.utilisateur_id', $utilisateurId)
                ->where('n.statut_notification_id', self::UNREAD_STATUS_ID)
                ->orderBy('n.created_at', 'DESC')
                ->limit(max(1, $limit))
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Unread notification list failed: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(fn (array $row): array => $this->mapRow($row), $rows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForUser(int $utilisateurId, ?int $statusId = null): array
    {
        if ($utilisateurId < 1) {
            return [];
        }

        try {
            $builder = db_connect()->table('notification.notification_utilisateur AS n')
                ->select('
                    n.notification_utilisateur_id,
                    n.sujet,
                    n.corps,
                    n.created_at,
                    n.envoye_le,
                    n.lu_le,
                    n.statut_notification_id,
                    n.canal_notification_id,
                    c.description_canal_notification AS channel_label,
                    s.description_statut_notification AS status_label
                ')
                ->join(
                    'notification.canal_notification AS c',
                    'c.canal_notification_id = n.canal_notification_id',
                    'left'
                )
                ->join(
                    'notification.statut_notification AS s',
                    's.statut_notification_id = n.statut_notification_id',
                    'left'
                )
                ->where('n.utilisateur_id', $utilisateurId)
                ->orderBy('n.created_at', 'DESC');

            if ($statusId !== null) {
                $builder->where('n.statut_notification_id', $statusId);
            }

            $rows = $builder->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Inbox list failed: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(fn (array $row): array => $this->mapRow($row), $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findOwned(int $utilisateurId, int $notificationId): ?array
    {
        if ($utilisateurId < 1 || $notificationId < 1) {
            return null;
        }

        try {
            $row = db_connect()->table('notification.notification_utilisateur AS n')
                ->select('
                    n.notification_utilisateur_id,
                    n.sujet,
                    n.corps,
                    n.created_at,
                    n.envoye_le,
                    n.lu_le,
                    n.statut_notification_id,
                    n.canal_notification_id,
                    n.utilisateur_id,
                    c.description_canal_notification AS channel_label,
                    s.description_statut_notification AS status_label
                ')
                ->join(
                    'notification.canal_notification AS c',
                    'c.canal_notification_id = n.canal_notification_id',
                    'left'
                )
                ->join(
                    'notification.statut_notification AS s',
                    's.statut_notification_id = n.statut_notification_id',
                    'left'
                )
                ->where('n.notification_utilisateur_id', $notificationId)
                ->where('n.utilisateur_id', $utilisateurId)
                ->get()
                ->getRowArray();
        } catch (\Throwable $e) {
            log_message('error', 'Inbox find failed: {message}', ['message' => $e->getMessage()]);

            return null;
        }

        return is_array($row) ? $this->mapRow($row) : null;
    }

    /**
     * @return array{ok:bool,errors?:list<string>,action?:array<string,mixed>}
     */
    public function markAsRead(int $utilisateurId, int $notificationId): array
    {
        $existing = $this->notifications->find($notificationId);
        if (! is_array($existing) || (int) ($existing['utilisateur_id'] ?? 0) !== $utilisateurId) {
            return ['ok' => false, 'errors' => [lang('Backoffice.inbox_err_not_found')]];
        }

        $readStatusId = $this->statuses->idByKeywords(['lue', 'lu', 'read']) ?? 3;
        $now          = date('Y-m-d H:i:s');

        if ((int) ($existing['statut_notification_id'] ?? 0) !== self::UNREAD_STATUS_ID) {
            if (empty($existing['lu_le'])) {
                try {
                    $this->notifications->update($notificationId, ['lu_le' => $now]);
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            return [
                'ok'     => true,
                'action' => $this->findOwned($utilisateurId, $notificationId),
            ];
        }

        try {
            $this->notifications->update($notificationId, [
                'statut_notification_id' => $readStatusId,
                'lu_le'                  => $now,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Mark notification read failed: {message}', ['message' => $e->getMessage()]);

            return ['ok' => false, 'errors' => [lang('Backoffice.inbox_err_mark_read') . ' ' . $e->getMessage()]];
        }

        return [
            'ok'     => true,
            'action' => $this->findOwned($utilisateurId, $notificationId),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function mapRow(array $row): array
    {
        $body    = trim(strip_tags((string) ($row['corps'] ?? '')));
        $preview = mb_strlen($body) > 110 ? (mb_substr($body, 0, 110) . '…') : $body;
        $created = (string) ($row['created_at'] ?? $row['envoye_le'] ?? '');

        return [
            'id'          => (int) ($row['notification_utilisateur_id'] ?? 0),
            'subject'     => (string) ($row['sujet'] ?? ''),
            'body'        => (string) ($row['corps'] ?? ''),
            'preview'     => $preview !== '' ? $preview : '—',
            'channel'     => (string) ($row['channel_label'] ?? '—'),
            'status_id'   => (int) ($row['statut_notification_id'] ?? 0),
            'status'      => (string) ($row['status_label'] ?? ''),
            'created_at'  => $created,
            'created_fmt' => $this->formatDateTime($created),
            'sent_at'     => $this->formatDateTime((string) ($row['envoye_le'] ?? '')),
            'read_at'     => $this->formatDateTime((string) ($row['lu_le'] ?? '')),
            'is_unread'   => (int) ($row['statut_notification_id'] ?? 0) === self::UNREAD_STATUS_ID,
            'url'         => site_url('backoffice/my/notifications/' . (int) ($row['notification_utilisateur_id'] ?? 0)),
        ];
    }

    private function formatDateTime(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '—';
        }

        $ts = strtotime($value);

        return $ts ? date('d/m/Y H:i', $ts) : $value;
    }
}
