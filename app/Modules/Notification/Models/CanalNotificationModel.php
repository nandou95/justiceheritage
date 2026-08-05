<?php

namespace Modules\Notification\Models;

use CodeIgniter\Model;

class CanalNotificationModel extends Model
{
    protected $table         = 'notification.canal_notification';
    protected $primaryKey    = 'canal_notification_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'description_canal_notification',
        'is_active',
    ];

    /**
     * @return list<array{id:int,label:string}>
     */
    public function options(?bool $activeOnly = null): array
    {
        $builder = $this->builder()
            ->select('canal_notification_id AS id, description_canal_notification AS label')
            ->orderBy('description_canal_notification', 'ASC');

        if ($activeOnly === true) {
            $builder->where('is_active', true);
        }

        return array_map(static fn (array $r): array => [
            'id'    => (int) $r['id'],
            'label' => (string) $r['label'],
        ], $builder->get()->getResultArray());
    }
}
