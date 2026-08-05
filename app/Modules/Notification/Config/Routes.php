<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Notification module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('backoffice', ['namespace' => 'Modules\\Notification\\Controllers'], static function ($routes) {
    $routes->get('notifications/complainants', 'ComplainantNotifications::index');
    $routes->get('notifications/complainants/(:num)', 'ComplainantNotifications::show/$1');
    $routes->post('notifications/complainants/(:num)/resend', 'ComplainantNotifications::resend/$1');

    $routes->get('notifications/users', 'UserNotifications::index');
    $routes->get('notifications/users/(:num)', 'UserNotifications::show/$1');
    $routes->post('notifications/users/(:num)/resend', 'UserNotifications::resend/$1');
});
