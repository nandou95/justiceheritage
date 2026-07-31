<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Transfer module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('transfer', ['namespace' => 'Modules\\Transfer\\Controllers'], static function ($routes) {
    $routes->get('/', 'Placeholder::index');
});
