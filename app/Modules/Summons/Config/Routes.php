<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Summons module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('backoffice', ['namespace' => 'Modules\\Summons\\Controllers'], static function ($routes) {
    $routes->get('summons', 'Summons::index');
    $routes->get('summons/pending', 'Summons::pending');
    $routes->get('summons/create/(:num)', 'Summons::create/$1');
    $routes->post('summons/create/(:num)', 'Summons::store/$1');
    $routes->get('summons/(:num)', 'Summons::show/$1');

    $routes->get('summons-statuses', 'SummonsStatuses::index');
    $routes->post('summons-statuses', 'SummonsStatuses::store');
    $routes->post('summons-statuses/(:num)', 'SummonsStatuses::update/$1');
});
