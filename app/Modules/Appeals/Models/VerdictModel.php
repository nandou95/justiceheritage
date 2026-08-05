<?php

namespace Modules\Appeals\Models;

use CodeIgniter\Model;

/**
 * Thin verdict access for appeal eligibility / updates.
 */
class VerdictModel extends Model
{
    protected $table            = 'verdict.verdict';
    protected $primaryKey       = 'verdict_id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'recours_exerce',
    ];
}
