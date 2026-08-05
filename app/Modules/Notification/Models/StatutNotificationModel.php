<?php

namespace Modules\Notification\Models;

use CodeIgniter\Model;

class StatutNotificationModel extends Model
{
    protected $table         = 'notification.statut_notification';
    protected $primaryKey    = 'statut_notification_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'description_statut_notification',
    ];

    /**
     * @return list<array{id:int,label:string}>
     */
    public function options(): array
    {
        $rows = $this->builder()
            ->select('statut_notification_id AS id, description_statut_notification AS label')
            ->orderBy('statut_notification_id', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn (array $r): array => [
            'id'    => (int) $r['id'],
            'label' => (string) $r['label'],
        ], $rows);
    }

    public function idByKeywords(array $needles): ?int
    {
        $rows = $this->builder()
            ->select('statut_notification_id, description_statut_notification')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $label = mb_strtolower((string) ($row['description_statut_notification'] ?? ''));
            foreach ($needles as $needle) {
                if ($needle !== '' && str_contains($label, mb_strtolower((string) $needle))) {
                    return (int) $row['statut_notification_id'];
                }
            }
        }

        return null;
    }
}
