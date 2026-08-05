<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Transfer module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('backoffice', ['namespace' => 'Modules\\Transfer\\Controllers'], static function ($routes) {
    $routes->get('transfer-statuses', 'TransferStatuses::index');
    $routes->post('transfer-statuses', 'TransferStatuses::store');
    $routes->post('transfer-statuses/(:num)', 'TransferStatuses::update/$1');
});
