<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../controllers/UsersController.php';
require_once __DIR__ . '/../controllers/SettingController.php';
require_once __DIR__ . '/../controllers/AccountController.php';
use Controllers\UsersController;
use Controllers\SettingController;
use Controllers\AccountController;
use Config\Response;

// Get request URI
$requestUri = parse_url( $_SERVER[ 'REQUEST_URI' ], PHP_URL_PATH );

// Remove base path dynamically
$basePath = '/www.ikawa.rw/_ikawa';
$route = str_replace( $basePath, '', $requestUri );
$route = rtrim( $route, '/' );
$route = $route === '' ? '/' : $route;

// Routing
switch ( true ) {
    case $route === '/users/login':
    $controller = new UsersController();
    $controller->login();
    break;
    case $route === '/users/get-all-users':
    $controller = new UsersController();
    $controller->getUsers();
    break;
    case $route === '/users/logout':
    $controller = new UsersController();
    $controller->logout();
    break;
    case $route === '/users/extend-session':
    $controller = new UsersController();
    $controller->extendSession();
    break;

    case $route === '/users/create':
    $controller = new UsersController();
    $controller->createUser();
    break;
    case $route === '/users/update':
    $controller = new UsersController();
    $controller->updateUser();
    break;
    case preg_match( '#^/users/delete/(\d+)$#', $route, $matches ):
    $controller = new UsersController();
    $controller->deleteUser( $matches[ 1 ] );
    break;

    // roles
    case $route === '/settings/createrole':
    $settingcontroller = new SettingController();
    $settingcontroller->CreateRole();
    break;

    case $route === '/settings/roles':
    $settingcontroller = new SettingController();
    $settingcontroller->getAllRoles();
    break;
    //location
    case $route === '/settings/location':
    $settingcontroller = new SettingController();
    $settingcontroller->getAllLocation();
    break;

    // payment modes
    case $route === '/settings/paymentmodes':
    $settingcontroller = new SettingController();
    $settingcontroller->getPaymentModes();
    break;

    // stations
    case $route === '/settings/stations':
    $settingcontroller = new SettingController();
    $settingcontroller->getStations();
    break;

    // accounts
    case $route === '/accounts/get-all':
    $accountController = new AccountController();
    $accountController->getAllAccounts();
    break;

    case $route === '/accounts/create':
    $accountController = new AccountController();
    $accountController->createAccount();
    break;

    case $route === '/accounts/update':
    $accountController = new AccountController();
    $accountController->updateAccount();
    break;

    case preg_match( '#^/accounts/delete/(\d+)$#', $route, $matches ):
    $accountController = new AccountController();
    $accountController->deleteAccount( $matches[ 1 ] );
    break;

    default:
    Response::error( 'Endpoint not found', 404 );
    break;
}
