<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Notification module routes.
 *
 * @var RouteCollection $routes
 */
$routes->group('backoffice', ['namespace' => 'Modules\\Notification\\Controllers'], static function ($routes) {
    $routes->get('my/notifications/unread-json', 'InboxNotifications::unreadJson');
    $routes->get('my/notifications/count-json', 'InboxNotifications::countJson');
    $routes->post('my/notifications/(:num)/read', 'InboxNotifications::markRead/$1');
    $routes->get('my/notifications/(:num)', 'InboxNotifications::show/$1');
    $routes->get('my/notifications', 'InboxNotifications::index');

    $routes->get('notifications/complainants', 'ComplainantNotifications::index');
    $routes->get('notifications/complainants/(:num)', 'ComplainantNotifications::show/$1');
    $routes->post('notifications/complainants/(:num)/resend', 'ComplainantNotifications::resend/$1');

    $routes->get('notifications/users', 'UserNotifications::index');
    $routes->get('notifications/users/(:num)', 'UserNotifications::show/$1');
    $routes->post('notifications/users/(:num)/resend', 'UserNotifications::resend/$1');
});
