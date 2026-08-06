<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Application core routes (public site + authentication).
 * Module routes are auto-discovered from each module Config Routes file.
 *
 * @var RouteCollection $routes
 */
$routes->get('/', 'PublicSite::index');
$routes->get('dispute-management', 'PublicSite::disputeManagement');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::registerSubmit');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::loginSubmit');
$routes->get('login/2fa', 'Auth::loginTwoFactor');
$routes->post('login/2fa', 'Auth::loginTwoFactorSubmit');
$routes->post('login/2fa/resend', 'Auth::loginTwoFactorResend');
$routes->get('logout', 'Auth::logout');
$routes->get('lang/(:segment)', 'PublicSite::switchLanguage/$1');

// Back-office authentication (public entry; protected resources use backofficeAuth filter).
$routes->get('backoffice/login', 'BackofficeAuthController::login');
$routes->post('backoffice/login', 'BackofficeAuthController::loginSubmit');
$routes->get('backoffice/login/2fa', 'BackofficeAuthController::twoFactor');
$routes->post('backoffice/login/2fa', 'BackofficeAuthController::twoFactorSubmit');
$routes->post('backoffice/login/2fa/resend', 'BackofficeAuthController::twoFactorResend');
$routes->get('backoffice/logout', 'BackofficeAuthController::logout');
