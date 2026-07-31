<?php

namespace Modules\Appeals\Config;

use CodeIgniter\Config\BaseService;
use Modules\Appeals\Services\AppealService;

class Services extends BaseService
{
    public static function appealService(bool $getShared = true): AppealService
    {
        if ($getShared) {
            return static::getSharedInstance('appealService');
        }

        return new AppealService();
    }
}
