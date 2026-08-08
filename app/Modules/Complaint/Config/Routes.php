<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Complaint module routes (portal scaffold + back-office management).
 *
 * @var RouteCollection $routes
 */
$routes->group('', ['namespace' => 'Modules\\Complaint\\Controllers'], static function ($routes) {
    $routes->get('complaint', 'Placeholder::index');
});

$routes->group('backoffice', ['namespace' => 'Modules\\Complaint\\Controllers'], static function ($routes) {
    // Complaint stages
    $routes->get('complaint-stages', 'ComplaintStages::index');
    $routes->get('complaint-stages/create', 'ComplaintStages::create');
    $routes->post('complaint-stages', 'ComplaintStages::store');
    $routes->get('complaint-stages/(:num)/edit', 'ComplaintStages::edit/$1');
    $routes->post('complaint-stages/(:num)', 'ComplaintStages::update/$1');
    $routes->post('complaint-stages/(:num)/toggle-status', 'ComplaintStages::toggleStatus/$1');
    $routes->get('complaint-stages/(:num)/profiles', 'ComplaintStages::profiles/$1');
    $routes->get('complaint-stages/(:num)/actions', 'ComplaintStages::actions/$1');
    $routes->post('complaint-stages/(:num)/actions', 'ComplaintStages::storeAction/$1');
    $routes->get('complaint-stages/(:num)/actions/(:num)/json', 'ComplaintStages::actionJson/$1/$2');
    $routes->post('complaint-stages/(:num)/actions/(:num)', 'ComplaintStages::updateAction/$1/$2');
    $routes->get('complaint-stages/(:num)/actions-json', 'ComplaintStages::actionsJson/$1');
    $routes->post('complaint-stages/(:num)/actions/(:num)/toggle-status', 'ComplaintStages::toggleAction/$1/$2');

    // Stage configuration
    $routes->get('complaint-stage-configs', 'ComplaintStageConfigs::index');
    $routes->post('complaint-stage-configs', 'ComplaintStageConfigs::store');
    $routes->post('complaint-stage-configs/(:num)', 'ComplaintStageConfigs::update/$1');
    $routes->post('complaint-stage-configs/(:num)/toggle-status', 'ComplaintStageConfigs::toggleStatus/$1');
    $routes->get('complaint-stage-configs/profiles/(:num)', 'ComplaintStageConfigs::profiles/$1');
    $routes->get('api/complaint-stages', 'ComplaintStageConfigs::stages');
    $routes->get('api/complaint-stage-actions', 'ComplaintStageConfigs::stageActions');

    // Statuses
    $routes->get('complaint-statuses', 'ComplaintStatuses::index');
    $routes->post('complaint-statuses', 'ComplaintStatuses::store');
    $routes->post('complaint-statuses/(:num)', 'ComplaintStatuses::update/$1');
    $routes->post('complaint-statuses/(:num)/toggle-status', 'ComplaintStatuses::toggleStatus/$1');
    $routes->get('api/complaint-statuses', 'ComplaintStatuses::optionsJson');

    // Document types
    $routes->get('document-types', 'DocumentTypes::index');
    $routes->post('document-types', 'DocumentTypes::store');
    $routes->post('document-types/(:num)', 'DocumentTypes::update/$1');
    $routes->post('document-types/(:num)/toggle-status', 'DocumentTypes::toggleStatus/$1');

    // Complaints
    $routes->get('complaints', 'Complaints::index');
    $routes->get('complaints/create', 'Complaints::create');
    $routes->post('complaints', 'Complaints::store');
    $routes->get('complaints/(:num)', 'Complaints::show/$1');
    $routes->get('complaints/(:num)/edit', 'Complaints::edit/$1');
    $routes->post('complaints/(:num)', 'Complaints::update/$1');
    $routes->get('api/complaint-document-types', 'Complaints::documentTypes');
});
