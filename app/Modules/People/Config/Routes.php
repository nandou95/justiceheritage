<?php

use CodeIgniter\Router\RouteCollection;

/**
 * People module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('backoffice', ['namespace' => 'Modules\\People\\Controllers'], static function ($routes) {
    $routes->get('people', 'People::index');
    $routes->get('people/create', 'People::create');
    $routes->post('people', 'People::store');
    $routes->get('people/(:num)', 'People::show/$1');
    $routes->get('people/(:num)/edit', 'People::edit/$1');
    $routes->post('people/(:num)', 'People::update/$1');
    $routes->get('people/(:num)/cni/view', 'People::viewCni/$1');
    $routes->get('people/(:num)/cni/download', 'People::downloadCni/$1');
});
