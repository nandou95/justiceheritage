<?php

namespace App\Libraries;

/**
 * Soft permission gate for Back Office routes.
 *
 * - No logged-in BO user → allow (demo / open preview mode used across modules).
 * - Logged-in user → allow when their profile has an active matching permission,
 *   or when no permissions exist yet for that route in the catalogue.
 */
class BackofficeAccess
{
    public static function can(string $route): bool
    {
        $userId = (int) (session('backoffice_user_id') ?? 0);
        if ($userId < 1) {
            return true;
        }

        $db = db_connect();

        try {
            $permission = $db->table('administration.permission')
                ->select('permission_id')
                ->where('LOWER(url_route)', mb_strtolower($route))
                ->where('is_active', true)
                ->get()
                ->getFirstRow('array');

            if (! $permission) {
                return true;
            }

            $profilId = $db->table('administration.utilisateur')
                ->select('profil_id')
                ->where('utilisateur_id', $userId)
                ->get()
                ->getFirstRow('array');

            if (! $profilId || empty($profilId['profil_id'])) {
                return false;
            }

            $assigned = $db->table('administration.profil_permission')
                ->where('profil_id', (int) $profilId['profil_id'])
                ->where('permission_id', (int) $permission['permission_id'])
                ->groupStart()
                    ->where('is_active', true)
                    ->orWhere('is_active', null)
                ->groupEnd()
                ->countAllResults();

            return $assigned > 0;
        } catch (\Throwable $e) {
            log_message('error', 'BackofficeAccess check failed: {message}', ['message' => $e->getMessage()]);

            return true;
        }
    }

    public static function denyRedirect(string $messageKey = 'Backoffice.access_denied')
    {
        return redirect()->to(site_url('backoffice'))->with('error', lang($messageKey));
    }
}
