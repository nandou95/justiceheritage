<?php

namespace Modules\Administration\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table         = 'audit_log.audit_log';
    protected $primaryKey    = 'audit_log_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'utilisateur_id',
        'action',
        'table_cible',
        'enregistrement_id',
        'anciennes_valeurs',
        'nouvelles_valeurs',
        'adresse_ip',
        'user_agent',
        'created_at',
    ];

    /**
     * @param array<string, mixed>|null $old
     * @param array<string, mixed>|null $new
     */
    public function record(
        string $action,
        string $table,
        ?int $recordId,
        ?array $old = null,
        ?array $new = null,
        ?int $utilisateurId = null
    ): void {
        $request = service('request');

        try {
            $this->insert([
                'utilisateur_id'    => $utilisateurId,
                'action'            => $action,
                'table_cible'       => $table,
                'enregistrement_id' => $recordId,
                'anciennes_valeurs' => $old === null ? null : json_encode($old, JSON_UNESCAPED_UNICODE),
                'nouvelles_valeurs' => $new === null ? null : json_encode($new, JSON_UNESCAPED_UNICODE),
                'adresse_ip'        => $request->getIPAddress(),
                'user_agent'        => substr((string) $request->getUserAgent(), 0, 500),
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to write audit log: {message}', ['message' => $e->getMessage()]);
        }
    }
}
