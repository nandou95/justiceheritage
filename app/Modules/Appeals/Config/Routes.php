<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Appeals module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('appeals', ['namespace' => 'Modules\\Appeals\\Controllers'], static function ($routes) {
    $routes->get('/', 'Placeholder::index');
});
