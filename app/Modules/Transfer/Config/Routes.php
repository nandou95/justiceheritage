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

    $routes->get('transfers', 'Transfers::index');
    $routes->get('transfers/create', 'Transfers::create');
    $routes->post('transfers', 'Transfers::store');
    $routes->get('transfers/api/eligible-complaints', 'Transfers::eligibleComplaints');
    $routes->get('transfers/api/destination-courts', 'Transfers::destinationCourts');
    $routes->get('transfers/documents/(:num)/view', 'Transfers::viewDocument/$1');
    $routes->get('transfers/documents/(:num)/download', 'Transfers::downloadDocument/$1');
    $routes->get('transfers/(:num)', 'Transfers::show/$1');
    $routes->get('transfers/(:num)/process', 'Transfers::process/$1');
    $routes->post('transfers/(:num)/receive', 'Transfers::receive/$1');
});
