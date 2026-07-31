<?php

namespace Modules\Transfer\Config;

use CodeIgniter\Config\BaseService;
use Modules\Transfer\Services\TransferService;

class Services extends BaseService
{
    public static function transferService(bool $getShared = true): TransferService
    {
        if ($getShared) {
            return static::getSharedInstance('transferService');
        }

        return new TransferService();
    }
}
