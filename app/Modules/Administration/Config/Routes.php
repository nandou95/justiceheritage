<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Administration module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('backoffice', ['namespace' => 'Modules\\Administration\\Controllers'], static function ($routes) {
    $routes->get('/', 'Backoffice::index');
    $routes->get('module/(:segment)', 'Backoffice::module/$1');
});
