<?php

namespace Modules\Transfer\Config;

use CodeIgniter\Config\BaseService;
use Modules\Transfer\Services\BackofficeTransferService;

class Services extends BaseService
{
    public static function transferService(bool $getShared = true): BackofficeTransferService
    {
        if ($getShared) {
            return static::getSharedInstance('transferService');
        }

        return new BackofficeTransferService();
    }
}
