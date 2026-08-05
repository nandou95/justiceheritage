<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Hearings module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('backoffice', ['namespace' => 'Modules\\Hearings\\Controllers'], static function ($routes) {
    $routes->get('hearings', 'Hearings::index');
    $routes->get('hearings/create', 'Hearings::create');
    $routes->post('hearings', 'Hearings::store');
    $routes->get('hearings/(:num)', 'Hearings::show/$1');
    $routes->get('hearings/(:num)/assignments', 'Hearings::assignments/$1');
    $routes->post('hearings/(:num)/assignments', 'Hearings::storeAssignment/$1');
    $routes->post('hearings/(:num)/assignments/(:num)', 'Hearings::updateAssignment/$1/$2');
    $routes->post('hearings/(:num)/assignments/(:num)/toggle-status', 'Hearings::toggleAssignment/$1/$2');
    $routes->get('hearings/(:num)/process', 'Hearings::process/$1');
    $routes->post('hearings/(:num)/process', 'Hearings::storeProcess/$1');
    $routes->get('api/hearing-eligible-complaints', 'Hearings::eligibleComplaints');

    $routes->get('hearing-statuses', 'HearingStatuses::index');
    $routes->post('hearing-statuses', 'HearingStatuses::store');
    $routes->post('hearing-statuses/(:num)', 'HearingStatuses::update/$1');
});
