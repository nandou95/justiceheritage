<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->group('portal', ['namespace' => 'Modules\\Complainant\\Controllers', 'filter' => 'complainantAuth'], static function ($routes) {
    $routes->get('/', 'Portal::index');
    $routes->get('complaints', 'Portal::complaints');
    $routes->get('complaints/new', 'Portal::createComplaint');
    $routes->post('complaints/new', 'Portal::createComplaint');
    $routes->get('complaints/(:segment)', 'Portal::showComplaint/$1');
    $routes->get('appeals/provincial', 'Portal::provincialAppeal');
    $routes->post('appeals/provincial', 'Portal::provincialAppeal');
    $routes->get('appeals/regional', 'Portal::regionalAppeal');
    $routes->post('appeals/regional', 'Portal::regionalAppeal');
    $routes->get('ministry', 'Portal::ministry');
    $routes->get('profile', 'Portal::profile');
    $routes->post('profile', 'Portal::profile');
});
