<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Appeals module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('appeals', ['namespace' => 'Modules\\Appeals\\Controllers'], static function ($routes) {
    $routes->get('/', 'Placeholder::index');
});

$routes->group('backoffice', ['namespace' => 'Modules\\Appeals\\Controllers'], static function ($routes) {
    $routes->get('appeals', 'Appeals::index');
    $routes->get('appeals/create', 'Appeals::create');
    $routes->post('appeals', 'Appeals::store');
    $routes->get('appeals/(:num)', 'Appeals::show/$1');
    $routes->get('appeals/(:num)/edit', 'Appeals::edit/$1');
    $routes->post('appeals/(:num)', 'Appeals::update/$1');
    $routes->get('api/appeal-parents', 'Appeals::eligibleParents');
    $routes->get('api/appeal-parents/(:num)', 'Appeals::parentInfo/$1');
    $routes->get('api/appeal-document-types', 'Appeals::documentTypes');
    $routes->get('appeals/documents/(:num)/view', 'Appeals::viewDocument/$1');
    $routes->get('appeals/documents/(:num)/download', 'Appeals::downloadDocument/$1');
});
