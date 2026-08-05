<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Administration module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('backoffice', ['namespace' => 'Modules\\Administration\\Controllers'], static function ($routes) {
    $routes->get('/', 'Backoffice::index');

    $routes->get('users', 'Users::index');
    $routes->get('users/create', 'Users::create');
    $routes->post('users', 'Users::store');
    $routes->get('users/(:num)', 'Users::show/$1');
    $routes->get('users/(:num)/edit', 'Users::edit/$1');
    $routes->post('users/(:num)', 'Users::update/$1');
    $routes->post('users/(:num)/toggle-status', 'Users::toggleStatus/$1');
    $routes->get('api/jurisdictions', 'Users::jurisdictions');

    $routes->get('permissions', 'Permissions::index');
    $routes->post('permissions', 'Permissions::store');
    $routes->post('permissions/(:num)', 'Permissions::update/$1');
    $routes->post('permissions/(:num)/toggle-status', 'Permissions::toggleStatus/$1');

    $routes->get('profiles', 'Profiles::index');
    $routes->get('profiles/create', 'Profiles::create');
    $routes->post('profiles', 'Profiles::store');
    $routes->get('profiles/(:num)', 'Profiles::show/$1');
    $routes->get('profiles/(:num)/edit', 'Profiles::edit/$1');
    $routes->post('profiles/(:num)', 'Profiles::update/$1');
    $routes->post('profiles/(:num)/toggle-status', 'Profiles::toggleStatus/$1');

    $routes->get('module/(:segment)', 'Backoffice::module/$1');
});
