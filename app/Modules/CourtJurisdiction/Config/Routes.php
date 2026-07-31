<?php

use CodeIgniter\Router\RouteCollection;

/**
 * CourtJurisdiction module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('api', ['namespace' => 'Modules\\CourtJurisdiction\\Controllers'], static function ($routes) {
    $routes->get('sexes', 'LocationApi::sexes');
    $routes->get('provinces', 'LocationApi::provinces');
    $routes->get('communes', 'LocationApi::communes');
    $routes->get('zones', 'LocationApi::zones');
    $routes->get('collines', 'LocationApi::collines');
});
