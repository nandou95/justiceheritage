<?php

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;
use Modules\Administration\Models\AuditLogModel;

/**
 * Central back-office authorization (profile → permissions → routes).
 */
class BackofficeAccess
{
    public const SESSION_PERMISSIONS = 'backoffice_permissions';
    public const SESSION_PERM_HASH   = 'backoffice_permissions_hash';
    public const SESSION_PROFIL_ID   = 'backoffice_profil_id';

    /** @var list<string>|null */
    private static ?array $catalogueCache = null;

    /**
     * Load active permission routes for a user profile into the session.
     *
     * @return list<string> Normalized url_route values
     */
    public static function hydrateSessionPermissions(int $utilisateurId, int $profilId): array
    {
        $routes = self::fetchAssignedRoutes($profilId);
        $hash   = self::fingerprint($profilId, $routes);

        session()->set([
            self::SESSION_PERMISSIONS => $routes,
            self::SESSION_PERM_HASH   => $hash,
            self::SESSION_PROFIL_ID   => $profilId,
        ]);

        return $routes;
    }

    /**
     * @return list<string>
     */
    public static function userPermissions(): array
    {
        $perms = session(self::SESSION_PERMISSIONS);

        return is_array($perms) ? array_values(array_map('strval', $perms)) : [];
    }

    public static function hasPermission(string $permission): bool
    {
        return self::can($permission);
    }

    public static function canAccess(string $route): bool
    {
        return self::can($route);
    }

    /**
     * Whether the current user may access a catalogue permission route.
     */
    public static function can(string $route): bool
    {
        $userId = (int) (session(BackofficeAuth::SESSION_USER_ID) ?? 0);
        if ($userId < 1) {
            return false;
        }

        $route = self::normalizePath($route);
        if ($route === '') {
            return false;
        }

        $perms = self::userPermissions();
        if ($perms === []) {
            return false;
        }

        if (in_array($route, $perms, true)) {
            return true;
        }

        // "…/manage" grants the list route and all sibling actions for that resource.
        if (str_ends_with($route, '/manage')) {
            return false;
        }

        if (in_array($route . '/manage', $perms, true)) {
            return true;
        }

        $manage = preg_replace(
            '#/(create|edit|show|delete|toggle-status|assign|process|receive|transfer|generate|resend|pending)$#',
            '/manage',
            $route
        );
        if (is_string($manage) && $manage !== $route && in_array($manage, $perms, true)) {
            return true;
        }

        return false;
    }

    /**
     * Filter entry point: auth + permission version + route authorization.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    public static function authorizeRequest(RequestInterface $request)
    {
        $userId = (int) (session(BackofficeAuth::SESSION_USER_ID) ?? 0);
        if ($userId < 1) {
            return redirect()
                ->to(site_url('backoffice/login'))
                ->with('error', lang('Backoffice.login_err_required'));
        }

        $fresh = self::currentPermissionState($userId);
        if ($fresh === null) {
            return self::terminateSession(
                $request,
                $userId,
                self::requestPath($request),
                null,
                'account_or_profile_missing',
                lang('Backoffice.login_err_account_invalid')
            );
        }

        $sessionHash = (string) (session(self::SESSION_PERM_HASH) ?? '');
        if ($sessionHash === '' || ! hash_equals($sessionHash, $fresh['hash'])) {
            return self::terminateSession(
                $request,
                $userId,
                self::requestPath($request),
                null,
                'permissions_changed',
                lang('Backoffice.login_err_permissions_changed')
            );
        }

        // Keep session permissions in sync with DB (same hash).
        session()->set([
            self::SESSION_PERMISSIONS => $fresh['routes'],
            self::SESSION_PERM_HASH   => $fresh['hash'],
            self::SESSION_PROFIL_ID   => $fresh['profil_id'],
        ]);

        $path     = self::requestPath($request);
        $required = self::resolveRequiredPermission($path, $request->getMethod());

        // Routes absent from the catalogue are not gated (API helpers, etc.).
        if ($required === null) {
            return null;
        }

        if (self::can($required)) {
            return null;
        }

        return self::terminateSession(
            $request,
            $userId,
            $path,
            $required,
            'unauthorized_route',
            lang('Backoffice.login_err_unauthorized_access')
        );
    }

    /**
     * Map a request URI (+ method) to a catalogue url_route, or null if ungated.
     */
    public static function resolveRequiredPermission(string $path, string $method = 'GET'): ?string
    {
        $path   = self::normalizePath($path);
        $method = strtoupper($method);
        $mapped = self::mapPathToPermissionRoute($path, $method);

        if ($mapped === '' || $mapped === 'backoffice/login' || str_starts_with($mapped, 'backoffice/login/')) {
            return null;
        }

        if ($mapped === 'backoffice/logout' || str_starts_with($mapped, 'backoffice/api/')) {
            return null;
        }

        $catalogue = self::catalogueRoutes();
        if ($catalogue === []) {
            // Fail closed when catalogue cannot be loaded.
            return $mapped;
        }

        if (isset($catalogue[$mapped])) {
            return $mapped;
        }

        // Try without trailing /show for list-equivalent GET collections already handled.
        return null;
    }

    /**
     * @deprecated Prefer authorizeRequest(); kept for legacy controller calls.
     */
    public static function denyRedirect(string $messageKey = 'Backoffice.login_err_unauthorized_access')
    {
        $auth = new BackofficeAuth();
        $userId = (int) (session(BackofficeAuth::SESSION_USER_ID) ?? 0);
        $path = self::requestPath(service('request'));
        $required = self::resolveRequiredPermission($path, service('request')->getMethod());

        self::auditUnauthorized($userId > 0 ? $userId : null, $path, $required, 'controller_deny');
        $auth->logout();

        return redirect()
            ->to(site_url('backoffice/login'))
            ->with('error', lang($messageKey));
    }

    public static function normalizePath(string $path): string
    {
        $path = trim($path);
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = str_replace('\\', '/', $path);
        $path = trim($path, '/');

        if (str_starts_with($path, 'index.php/')) {
            $path = substr($path, strlen('index.php/'));
        }

        // Absolute site URLs → path only.
        if (str_contains($path, '://')) {
            $parsed = parse_url($path, PHP_URL_PATH);
            $path   = is_string($parsed) ? trim($parsed, '/') : $path;
        }

        // Strip base folder if present (e.g. justiceheritage/public).
        if (preg_match('#(?:^|/)(backoffice(?:/.*)?)$#', $path, $m)) {
            $path = $m[1];
        }

        return mb_strtolower($path);
    }

    /**
     * Convert a live request path into a permission catalogue route.
     */
    public static function mapPathToPermissionRoute(string $path, string $method = 'GET'): string
    {
        $path   = self::normalizePath($path);
        $method = strtoupper($method);
        $parts  = $path === '' ? [] : explode('/', $path);

        if ($parts === [] || ($parts[0] ?? '') !== 'backoffice') {
            return $path;
        }

        // POST backoffice/{resource} → create
        if ($method === 'POST' && count($parts) === 2) {
            return $parts[0] . '/' . $parts[1] . '/create';
        }

        // Nested collections: backoffice/notifications/users, backoffice/system-logs/users, …
        // POST backoffice/a/b → create
        if ($method === 'POST' && count($parts) === 3 && ! ctype_digit($parts[2])) {
            // e.g. POST backoffice/notifications/users/{?} already handled below
            // POST to exact nested list is uncommon; leave as-is unless digit.
        }

        $out = [];
        $i   = 0;
        $n   = count($parts);

        while ($i < $n) {
            $seg = $parts[$i];

            if (ctype_digit($seg)) {
                $next = $parts[$i + 1] ?? null;
                if ($next !== null && ! ctype_digit($next)) {
                    // …/{id}/{action}
                    $out[] = $next;
                    $i    += 2;
                    continue;
                }

                // Trailing id → show (GET) or edit (POST update)
                $out[] = ($method === 'POST') ? 'edit' : 'show';
                $i++;
                continue;
            }

            $out[] = $seg;
            $i++;
        }

        $mapped = implode('/', $out);

        // Catalogue aliases for plural/action naming differences.
        $aliases = [
            'backoffice/hearings/assignments' => 'backoffice/hearings/assign',
        ];

        return $aliases[$mapped] ?? $mapped;
    }

    /**
     * @return array{profil_id:int,routes:list<string>,hash:string}|null
     */
    private static function currentPermissionState(int $utilisateurId): ?array
    {
        try {
            $row = db_connect()->table('administration.utilisateur')
                ->select('profil_id, statut_compte_id')
                ->where('utilisateur_id', $utilisateurId)
                ->get()
                ->getFirstRow('array');
        } catch (\Throwable $e) {
            log_message('error', 'Permission state load failed: {message}', ['message' => $e->getMessage()]);

            return null;
        }

        if (! $row || empty($row['profil_id'])) {
            return null;
        }

        $profilId = (int) $row['profil_id'];
        $routes   = self::fetchAssignedRoutes($profilId);

        return [
            'profil_id' => $profilId,
            'routes'    => $routes,
            'hash'      => self::fingerprint($profilId, $routes),
        ];
    }

    /**
     * @return list<string>
     */
    private static function fetchAssignedRoutes(int $profilId): array
    {
        if ($profilId < 1) {
            return [];
        }

        try {
            $sql = <<<'SQL'
                SELECT LOWER(TRIM(p.url_route)) AS url_route
                FROM administration.profil_permission AS pp
                INNER JOIN administration.permission AS p
                    ON p.permission_id = pp.permission_id
                INNER JOIN administration.profil AS pr
                    ON pr.profil_id = pp.profil_id
                WHERE pp.profil_id = ?
                  AND pp.is_active = TRUE
                  AND p.is_active = TRUE
                  AND (pr.is_active IS NULL OR pr.is_active = TRUE)
                ORDER BY 1
            SQL;

            $rows = db_connect()->query($sql, [$profilId])->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch profile permissions: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        $routes = [];
        foreach ($rows as $row) {
            $route = self::normalizePath((string) ($row['url_route'] ?? ''));
            if ($route !== '') {
                $routes[] = $route;
            }
        }

        return array_values(array_unique($routes));
    }

    /**
     * @param list<string> $routes
     */
    private static function fingerprint(int $profilId, array $routes): string
    {
        $normalized = $routes;
        sort($normalized, SORT_STRING);

        return hash('sha256', $profilId . '|' . implode(',', $normalized));
    }

    /**
     * @return array<string, true>
     */
    private static function catalogueRoutes(): array
    {
        if (self::$catalogueCache !== null) {
            return array_fill_keys(self::$catalogueCache, true);
        }

        try {
            $rows = db_connect()->table('administration.permission')
                ->select('url_route')
                ->where('is_active', true)
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to load permission catalogue: {message}', ['message' => $e->getMessage()]);
            self::$catalogueCache = [];

            return [];
        }

        $routes = [];
        foreach ($rows as $row) {
            $route = self::normalizePath((string) ($row['url_route'] ?? ''));
            if ($route !== '') {
                $routes[] = $route;
            }
        }

        self::$catalogueCache = array_values(array_unique($routes));

        return array_fill_keys(self::$catalogueCache, true);
    }

    private static function requestPath(RequestInterface $request): string
    {
        return self::normalizePath($request->getUri()->getPath());
    }

    /**
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    private static function terminateSession(
        RequestInterface $request,
        ?int $userId,
        string $path,
        ?string $required,
        string $reason,
        string $message
    ) {
        self::auditUnauthorized($userId, $path, $required, $reason);

        $auth = new BackofficeAuth();
        $auth->logout();

        return redirect()
            ->to(site_url('backoffice/login'))
            ->with('error', $message);
    }

    private static function auditUnauthorized(?int $userId, string $path, ?string $required, string $reason): void
    {
        $request = service('request');

        log_message('warning', 'BO unauthorized access user={id} path={path} required={required} reason={reason} ip={ip}', [
            'id'       => $userId !== null ? (string) $userId : '-',
            'path'     => $path,
            'required' => $required ?? '-',
            'reason'   => $reason,
            'ip'       => $request->getIPAddress(),
        ]);

        (new AuditLogModel())->record(
            'UNAUTHORIZED_ACCESS',
            'administration.permission',
            null,
            null,
            [
                'success'             => false,
                'reason'              => $reason,
                'requested_url'       => $path,
                'permission_required' => $required,
                'method'              => $request->getMethod(),
            ],
            $userId
        );
    }
}
