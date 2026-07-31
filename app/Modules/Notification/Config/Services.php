<?php

namespace Modules\Notification\Config;

use CodeIgniter\Config\BaseService;
use Modules\Notification\Services\NotificationMailer;

class Services extends BaseService
{
    public static function notifications(bool $getShared = true): NotificationMailer
    {
        if ($getShared) {
            return static::getSharedInstance('notifications');
        }

        return new NotificationMailer();
    }
}
