<?php

namespace Modules\Notification\Models;

use CodeIgniter\Model;

class NotificationPersonneModel extends Model
{
    protected $table         = 'notification.notification_personne';
    protected $primaryKey    = 'notification_personne_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'personne_id',
        'canal_notification_id',
        'sujet',
        'corps',
        'plainte_id',
        'statut_notification_id',
        'envoye_le',
        'lu_le',
        'created_at',
    ];
}
