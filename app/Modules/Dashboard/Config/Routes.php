<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Dashboard module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('backoffice', ['namespace' => 'Modules\\Dashboard\\Controllers'], static function ($routes) {
    $routes->get('dashboards/executive', 'Dashboards::executive');
    $routes->get('dashboards/complaints', 'Dashboards::complaints');
    $routes->get('dashboards/complainants', 'Dashboards::complainants');
    $routes->get('dashboards/appeals', 'Dashboards::appeals');
    $routes->get('dashboards/summons', 'Dashboards::summons');
    $routes->get('dashboards/hearings', 'Dashboards::hearings');
    $routes->get('dashboards/notifications', 'Dashboards::notifications');
    $routes->get('dashboards/courts', 'Dashboards::courts');
});
