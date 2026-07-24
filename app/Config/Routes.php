<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'PublicSite::index');
$routes->get('dispute-management', 'PublicSite::disputeManagement');
$routes->get('register', 'PublicSite::register');
$routes->post('register', 'PublicSite::registerSubmit');
$routes->get('login', 'PublicSite::login');
$routes->post('login', 'PublicSite::loginSubmit');
$routes->get('lang/(:segment)', 'PublicSite::switchLanguage/$1');

$routes->get('portal', 'ComplainantPortal::index');
$routes->get('portal/demo', 'ComplainantPortal::enterDemo');
$routes->get('portal/complaints', 'ComplainantPortal::complaints');
$routes->get('portal/complaints/new', 'ComplainantPortal::createComplaint');
$routes->post('portal/complaints/new', 'ComplainantPortal::createComplaint');
$routes->get('portal/complaints/(:segment)', 'ComplainantPortal::showComplaint/$1');
$routes->get('portal/appeals/provincial', 'ComplainantPortal::provincialAppeal');
$routes->post('portal/appeals/provincial', 'ComplainantPortal::provincialAppeal');
$routes->get('portal/appeals/regional', 'ComplainantPortal::regionalAppeal');
$routes->post('portal/appeals/regional', 'ComplainantPortal::regionalAppeal');
$routes->get('portal/profile', 'ComplainantPortal::profile');
$routes->post('portal/profile', 'ComplainantPortal::profile');

$routes->get('backoffice', 'Backoffice::index');
$routes->get('backoffice/module/(:segment)', 'Backoffice::module/$1');
