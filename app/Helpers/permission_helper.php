<?php

use App\Libraries\BackofficeAccess;

if (! function_exists('hasPermission')) {
    /**
     * Whether the authenticated back-office user has a catalogue permission route.
     */
    function hasPermission(string $permission): bool
    {
        return BackofficeAccess::hasPermission($permission);
    }
}

if (! function_exists('can_access')) {
    /**
     * Alias used in views: can_access('backoffice/users/create').
     */
    function can_access(string $route): bool
    {
        return BackofficeAccess::canAccess($route);
    }
}

if (! function_exists('canAccess')) {
    function canAccess(string $route): bool
    {
        return BackofficeAccess::canAccess($route);
    }
}

if (! function_exists('userPermissions')) {
    /**
     * @return list<string>
     */
    function userPermissions(): array
    {
        return BackofficeAccess::userPermissions();
    }
}
