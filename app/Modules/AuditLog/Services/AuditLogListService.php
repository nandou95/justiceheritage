<?php

namespace Modules\AuditLog\Services;

/**
 * Read/query helpers for System Logs (complainant + back-office user audit trails).
 */
class AuditLogListService
{
    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listComplainantLogs(array $filters = []): array
    {
        $db = db_connect();

        try {
            $builder = $db->table('audit_log.audit_log_personne a')
                ->select("
                    a.audit_log_personne_id AS id,
                    a.personne_id,
                    a.action,
                    a.table_cible,
                    a.enregistrement_id,
                    a.adresse_ip::text AS adresse_ip,
                    a.user_agent,
                    a.created_at,
                    TRIM(CONCAT(COALESCE(p.prenom_personne, ''), ' ', COALESCE(p.nom_personne, ''))) AS complainant_name,
                    p.email AS complainant_email,
                    p.telephone AS complainant_phone,
                    p.numero_cni AS complainant_cni
                ", false)
                ->join('plaignant.personne p', 'p.personne_id = a.personne_id', 'left');

            if (! empty($filters['province_id'])) {
                $builder->where('p.province_naissance_id', (int) $filters['province_id']);
            }
            if (! empty($filters['commune_id'])) {
                $builder->where('p.commune_naissance_id', (int) $filters['commune_id']);
            }
            if (! empty($filters['personne_id'])) {
                $builder->where('a.personne_id', (int) $filters['personne_id']);
            }
            if (($filters['action'] ?? '') !== '') {
                $builder->where('a.action', (string) $filters['action']);
            }
            if (($filters['table_cible'] ?? '') !== '') {
                $builder->where('a.table_cible', (string) $filters['table_cible']);
            }
            if (! empty($filters['date_from'])) {
                $builder->where('a.created_at >=', $filters['date_from'] . ' 00:00:00');
            }
            if (! empty($filters['date_to'])) {
                $builder->where('a.created_at <=', $filters['date_to'] . ' 23:59:59');
            }

            $rows = $builder->orderBy('a.created_at', 'DESC')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list complainant audit logs: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            return [
                'id'                 => (int) $row['id'],
                'personne_id'        => (int) ($row['personne_id'] ?? 0),
                'complainant_name'   => trim((string) ($row['complainant_name'] ?? '')) ?: '—',
                'complainant_email'  => (string) ($row['complainant_email'] ?? ''),
                'complainant_phone'  => (string) ($row['complainant_phone'] ?? ''),
                'complainant_cni'    => (string) ($row['complainant_cni'] ?? ''),
                'action'             => (string) ($row['action'] ?? ''),
                'table_cible'        => (string) ($row['table_cible'] ?? ''),
                'enregistrement_id'  => $row['enregistrement_id'] !== null ? (int) $row['enregistrement_id'] : null,
                'adresse_ip'         => (string) ($row['adresse_ip'] ?? ''),
                'user_agent'         => (string) ($row['user_agent'] ?? ''),
                'created_at'         => self::formatDateTime($row['created_at'] ?? null),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findComplainantLog(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $db = db_connect();

        try {
            $row = $db->table('audit_log.audit_log_personne a')
                ->select("
                    a.*,
                    a.adresse_ip::text AS adresse_ip_text,
                    TRIM(CONCAT(COALESCE(p.prenom_personne, ''), ' ', COALESCE(p.nom_personne, ''))) AS complainant_name,
                    p.email AS complainant_email,
                    p.telephone AS complainant_phone,
                    p.numero_cni AS complainant_cni,
                    p.user_name AS complainant_username
                ", false)
                ->join('plaignant.personne p', 'p.personne_id = a.personne_id', 'left')
                ->where('a.audit_log_personne_id', $id)
                ->get()
                ->getFirstRow('array');
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load complainant audit log {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $row) {
            return null;
        }

        return [
            'id'                   => (int) $row['audit_log_personne_id'],
            'personne_id'          => (int) ($row['personne_id'] ?? 0),
            'complainant_name'     => trim((string) ($row['complainant_name'] ?? '')) ?: '—',
            'complainant_email'    => (string) ($row['complainant_email'] ?? ''),
            'complainant_phone'    => (string) ($row['complainant_phone'] ?? ''),
            'complainant_cni'      => (string) ($row['complainant_cni'] ?? ''),
            'complainant_username' => (string) ($row['complainant_username'] ?? ''),
            'action'               => (string) ($row['action'] ?? ''),
            'table_cible'          => (string) ($row['table_cible'] ?? ''),
            'enregistrement_id'    => $row['enregistrement_id'] !== null ? (int) $row['enregistrement_id'] : null,
            'anciennes_valeurs'    => self::prettyJson($row['anciennes_valeurs'] ?? null),
            'nouvelles_valeurs'    => self::prettyJson($row['nouvelles_valeurs'] ?? null),
            'adresse_ip'           => (string) ($row['adresse_ip_text'] ?? $row['adresse_ip'] ?? ''),
            'user_agent'           => (string) ($row['user_agent'] ?? ''),
            'created_at'           => self::formatDateTime($row['created_at'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listUserLogs(array $filters = []): array
    {
        $db = db_connect();

        try {
            $builder = $db->table('audit_log.audit_log a')
                ->select("
                    a.audit_log_id AS id,
                    a.utilisateur_id,
                    a.action,
                    a.table_cible,
                    a.enregistrement_id,
                    a.adresse_ip::text AS adresse_ip,
                    a.user_agent,
                    a.created_at,
                    TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS user_name,
                    pr.libelle_profil AS profile_name,
                    j.nom_juridiction AS court_name,
                    u.profil_id,
                    u.juridiction_id,
                    j.province_id,
                    j.commune_id,
                    j.niveau_juridiction_id
                ", false)
                ->join('administration.utilisateur u', 'u.utilisateur_id = a.utilisateur_id', 'left')
                ->join('administration.profil pr', 'pr.profil_id = u.profil_id', 'left')
                ->join('juridiction.juridiction j', 'j.juridiction_id = u.juridiction_id', 'left');

            if (! empty($filters['province_id'])) {
                $builder->where('j.province_id', (int) $filters['province_id']);
            }
            if (! empty($filters['commune_id'])) {
                $builder->where('j.commune_id', (int) $filters['commune_id']);
            }
            if (! empty($filters['niveau_juridiction_id'])) {
                $builder->where('j.niveau_juridiction_id', (int) $filters['niveau_juridiction_id']);
            }
            if (! empty($filters['juridiction_id'])) {
                $builder->where('u.juridiction_id', (int) $filters['juridiction_id']);
            }
            if (! empty($filters['utilisateur_id'])) {
                $builder->where('a.utilisateur_id', (int) $filters['utilisateur_id']);
            }
            if (! empty($filters['profil_id'])) {
                $builder->where('u.profil_id', (int) $filters['profil_id']);
            }
            if (($filters['action'] ?? '') !== '') {
                $builder->where('a.action', (string) $filters['action']);
            }
            if (($filters['table_cible'] ?? '') !== '') {
                $builder->where('a.table_cible', (string) $filters['table_cible']);
            }
            if (! empty($filters['date_from'])) {
                $builder->where('a.created_at >=', $filters['date_from'] . ' 00:00:00');
            }
            if (! empty($filters['date_to'])) {
                $builder->where('a.created_at <=', $filters['date_to'] . ' 23:59:59');
            }

            $rows = $builder->orderBy('a.created_at', 'DESC')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to list user audit logs: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return array_map(static function (array $row): array {
            return [
                'id'                => (int) $row['id'],
                'utilisateur_id'    => (int) ($row['utilisateur_id'] ?? 0),
                'user_name'         => trim((string) ($row['user_name'] ?? '')) ?: '—',
                'profile_name'      => (string) ($row['profile_name'] ?? '—'),
                'court_name'        => (string) ($row['court_name'] ?? '—'),
                'action'            => (string) ($row['action'] ?? ''),
                'table_cible'       => (string) ($row['table_cible'] ?? ''),
                'enregistrement_id' => $row['enregistrement_id'] !== null ? (int) $row['enregistrement_id'] : null,
                'adresse_ip'        => (string) ($row['adresse_ip'] ?? ''),
                'user_agent'        => (string) ($row['user_agent'] ?? ''),
                'created_at'        => self::formatDateTime($row['created_at'] ?? null),
            ];
        }, $rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findUserLog(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $db = db_connect();

        try {
            $row = $db->table('audit_log.audit_log a')
                ->select("
                    a.*,
                    a.adresse_ip::text AS adresse_ip_text,
                    TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS user_name,
                    u.email AS user_email,
                    u.telephone AS user_phone,
                    u.numero_matricule AS user_matricule,
                    COALESCE(
                        NULLIF(TRIM(u.email), ''),
                        NULLIF(TRIM(u.numero_cni), ''),
                        NULLIF(TRIM(u.numero_matricule), '')
                    ) AS user_username,
                    pr.libelle_profil AS profile_name,
                    j.nom_juridiction AS court_name
                ", false)
                ->join('administration.utilisateur u', 'u.utilisateur_id = a.utilisateur_id', 'left')
                ->join('administration.profil pr', 'pr.profil_id = u.profil_id', 'left')
                ->join('juridiction.juridiction j', 'j.juridiction_id = u.juridiction_id', 'left')
                ->where('a.audit_log_id', $id)
                ->get()
                ->getFirstRow('array');
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load user audit log {id}: {message}', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $row) {
            return null;
        }

        return [
            'id'                => (int) $row['audit_log_id'],
            'utilisateur_id'    => (int) ($row['utilisateur_id'] ?? 0),
            'user_name'         => trim((string) ($row['user_name'] ?? '')) ?: '—',
            'user_email'        => (string) ($row['user_email'] ?? ''),
            'user_phone'        => (string) ($row['user_phone'] ?? ''),
            'user_matricule'     => (string) ($row['user_matricule'] ?? ''),
            'user_username'     => (string) ($row['user_username'] ?? ''),
            'profile_name'      => (string) ($row['profile_name'] ?? '—'),
            'court_name'        => (string) ($row['court_name'] ?? '—'),
            'action'            => (string) ($row['action'] ?? ''),
            'table_cible'       => (string) ($row['table_cible'] ?? ''),
            'enregistrement_id' => $row['enregistrement_id'] !== null ? (int) $row['enregistrement_id'] : null,
            'anciennes_valeurs' => self::prettyJson($row['anciennes_valeurs'] ?? null),
            'nouvelles_valeurs' => self::prettyJson($row['nouvelles_valeurs'] ?? null),
            'adresse_ip'        => (string) ($row['adresse_ip_text'] ?? $row['adresse_ip'] ?? ''),
            'user_agent'        => (string) ($row['user_agent'] ?? ''),
            'created_at'        => self::formatDateTime($row['created_at'] ?? null),
        ];
    }

    /**
     * @return list<array{id:int|string,label:string}>
     */
    public function complainantOptions(): array
    {
        $db = db_connect();

        try {
            $rows = $db->query("
                SELECT DISTINCT p.personne_id AS id,
                       TRIM(CONCAT(COALESCE(p.prenom_personne, ''), ' ', COALESCE(p.nom_personne, ''))) AS label
                FROM audit_log.audit_log_personne a
                JOIN plaignant.personne p ON p.personne_id = a.personne_id
                ORDER BY label
            ")->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static fn (array $r): array => [
            'id'    => (int) $r['id'],
            'label' => trim((string) $r['label']) ?: ('#' . $r['id']),
        ], $rows);
    }

    /**
     * @return list<array{id:int|string,label:string}>
     */
    public function userOptions(): array
    {
        $db = db_connect();

        try {
            $rows = $db->query("
                SELECT DISTINCT u.utilisateur_id AS id,
                       TRIM(CONCAT(COALESCE(u.prenom_utilisateur, ''), ' ', COALESCE(u.nom_utilisateur, ''))) AS label
                FROM audit_log.audit_log a
                JOIN administration.utilisateur u ON u.utilisateur_id = a.utilisateur_id
                ORDER BY label
            ")->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static fn (array $r): array => [
            'id'    => (int) $r['id'],
            'label' => trim((string) $r['label']) ?: ('#' . $r['id']),
        ], $rows);
    }

    /**
     * @return list<array{id:string,label:string}>
     */
    public function distinctActions(string $source): array
    {
        $table = $source === 'personne' ? 'audit_log.audit_log_personne' : 'audit_log.audit_log';

        try {
            $rows = db_connect()->table($table)
                ->distinct()
                ->select('action')
                ->where('action IS NOT NULL')
                ->where("action <> ''")
                ->orderBy('action', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static fn (array $r): array => [
            'id'    => (string) $r['action'],
            'label' => (string) $r['action'],
        ], $rows);
    }

    /**
     * @return list<array{id:string,label:string}>
     */
    public function distinctTables(string $source): array
    {
        $table = $source === 'personne' ? 'audit_log.audit_log_personne' : 'audit_log.audit_log';

        try {
            $rows = db_connect()->table($table)
                ->distinct()
                ->select('table_cible')
                ->where('table_cible IS NOT NULL')
                ->where("table_cible <> ''")
                ->orderBy('table_cible', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static fn (array $r): array => [
            'id'    => (string) $r['table_cible'],
            'label' => (string) $r['table_cible'],
        ], $rows);
    }

    private static function formatDateTime(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $ts = strtotime((string) $value);

        return $ts ? date('Y-m-d H:i:s', $ts) : (string) $value;
    }

    private static function prettyJson(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_array($value) || is_object($value)) {
            return (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $decoded = json_decode((string) $value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return (string) json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return (string) $value;
    }
}
