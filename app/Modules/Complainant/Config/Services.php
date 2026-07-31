<?php

namespace Modules\Complainant\Config;

use CodeIgniter\Config\BaseService;
use Modules\Complainant\Services\CaseOverviewService;

class Services extends BaseService
{
    public static function caseOverview(bool $getShared = true): CaseOverviewService
    {
        if ($getShared) {
            return static::getSharedInstance('caseOverview');
        }

        return new CaseOverviewService();
    }
}
