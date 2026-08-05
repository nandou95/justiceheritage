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

$routes->group('backoffice', ['namespace' => 'Modules\\CourtJurisdiction\\Controllers'], static function ($routes) {
    $routes->get('court-jurisdictions', 'CourtJurisdictions::index');
    $routes->get('court-jurisdictions/create', 'CourtJurisdictions::create');
    $routes->post('court-jurisdictions', 'CourtJurisdictions::store');
    $routes->get('court-jurisdictions/(:num)', 'CourtJurisdictions::show/$1');
    $routes->get('court-jurisdictions/(:num)/edit', 'CourtJurisdictions::edit/$1');
    $routes->post('court-jurisdictions/(:num)', 'CourtJurisdictions::update/$1');
    $routes->post('court-jurisdictions/(:num)/toggle-status', 'CourtJurisdictions::toggleStatus/$1');
    $routes->get('api/court-jurisdictions', 'CourtJurisdictions::options');

    $routes->get('court-jurisdiction-configs', 'CourtJurisdictionConfigs::index');
    $routes->post('court-jurisdiction-configs', 'CourtJurisdictionConfigs::store');
    $routes->post('court-jurisdiction-configs/(:num)', 'CourtJurisdictionConfigs::update/$1');
    $routes->post('court-jurisdiction-configs/(:num)/toggle-status', 'CourtJurisdictionConfigs::toggleStatus/$1');
    $routes->get('api/parent-jurisdiction-level', 'CourtJurisdictionConfigs::parentLevel');

    $routes->get('jurisdiction-levels', 'JurisdictionLevels::index');
    $routes->post('jurisdiction-levels', 'JurisdictionLevels::store');
    $routes->post('jurisdiction-levels/(:num)', 'JurisdictionLevels::update/$1');
    $routes->post('jurisdiction-levels/(:num)/toggle-status', 'JurisdictionLevels::toggleStatus/$1');

    $routes->get('jurisdiction-level-configs', 'JurisdictionLevelConfigs::index');
    $routes->post('jurisdiction-level-configs', 'JurisdictionLevelConfigs::store');
    $routes->post('jurisdiction-level-configs/(:num)', 'JurisdictionLevelConfigs::update/$1');
    $routes->post('jurisdiction-level-configs/(:num)/toggle-status', 'JurisdictionLevelConfigs::toggleStatus/$1');
});
