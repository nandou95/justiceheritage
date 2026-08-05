<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Verdicts module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('backoffice', ['namespace' => 'Modules\\Verdicts\\Controllers'], static function ($routes) {
    $routes->get('verdicts', 'Verdicts::index');
    $routes->get('verdicts/create', 'Verdicts::create');
    $routes->post('verdicts', 'Verdicts::store');
    $routes->get('verdicts/(:num)', 'Verdicts::show/$1');
    $routes->get('verdicts/(:num)/report/view', 'Verdicts::viewReport/$1');
    $routes->get('verdicts/(:num)/report/download', 'Verdicts::downloadReport/$1');
    $routes->get('api/verdict-eligible-hearings', 'Verdicts::eligibleHearings');
    $routes->get('api/verdict-hearing-judges', 'Verdicts::hearingJudges');
    $routes->get('api/verdict-default-deadline', 'Verdicts::defaultDeadline');

    $routes->get('verdict-types', 'VerdictTypes::index');
    $routes->post('verdict-types', 'VerdictTypes::store');
    $routes->post('verdict-types/(:num)', 'VerdictTypes::update/$1');
});
