<?php

namespace Modules\AuditLog\Models;

use CodeIgniter\Model;

class AuditLogPersonneModel extends Model
{
    protected $table         = 'audit_log.audit_log_personne';
    protected $primaryKey    = 'audit_log_personne_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'personne_id',
        'action',
        'table_cible',
        'enregistrement_id',
        'anciennes_valeurs',
        'nouvelles_valeurs',
        'adresse_ip',
        'user_agent',
        'created_at',
    ];
}
