<?php

namespace Modules\Notification\Models;

use CodeIgniter\Model;

class NotificationUtilisateurModel extends Model
{
    protected $table         = 'notification.notification_utilisateur';
    protected $primaryKey    = 'notification_utilisateur_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'utilisateur_id',
        'canal_notification_id',
        'sujet',
        'corps',
        'statut_notification_id',
        'envoye_le',
        'lu_le',
        'created_at',
    ];
}
