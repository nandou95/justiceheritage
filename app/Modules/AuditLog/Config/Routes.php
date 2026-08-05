<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Audit Log module routes (System Logs).
 *
 * @var RouteCollection $routes
 */
$routes->group('backoffice', ['namespace' => 'Modules\\AuditLog\\Controllers'], static function ($routes) {
    $routes->get('system-logs/complainants', 'ComplainantLogs::index');
    $routes->get('system-logs/complainants/(:num)', 'ComplainantLogs::show/$1');

    $routes->get('system-logs/users', 'UserLogs::index');
    $routes->get('system-logs/users/(:num)', 'UserLogs::show/$1');
});
