<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Complaint module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('complaint', ['namespace' => 'Modules\\Complaint\\Controllers'], static function ($routes) {
    $routes->get('/', 'Placeholder::index');
});
