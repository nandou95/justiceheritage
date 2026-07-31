<?php

namespace Modules\Complaint\Config;

use CodeIgniter\Config\BaseService;
use Modules\Complaint\Services\ComplaintService;

class Services extends BaseService
{
    public static function complaintService(bool $getShared = true): ComplaintService
    {
        if ($getShared) {
            return static::getSharedInstance('complaintService');
        }

        return new ComplaintService();
    }
}
