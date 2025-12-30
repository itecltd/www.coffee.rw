<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../controllers/UsersController.php';
require_once __DIR__ . '/../controllers/SettingController.php';
require_once __DIR__ . '/../controllers/InventoryController.php';
use Controllers\UsersController;
use Controllers\SettingController;
use Config\Response;
use Controllers\InventoryController;
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
    case $route === '/settings/updateroles':
    $settingcontroller = new SettingController();
    $settingcontroller->UpdateRole();
    break;

    //location
    case $route === '/settings/createlocation':
    $settingcontroller = new SettingController();
    $settingcontroller->createHeadQuater();
    break;

    case $route === '/settings/location':
    $settingcontroller = new SettingController();
    $settingcontroller->getAllLocation();
    break;

    case $route === '/settings/updatelocation':
    $settingcontroller = new SettingController();
    $settingcontroller->UpdateLocation();
    break;

    //inventory
    case $route === '/inventory/getsuppliers':
    $inventorycontroller = new InventoryController();
    $inventorycontroller->SupplierList();
    break;
    case $route === '/inventory/createsupplier':
    $inventorycontroller = new InventoryController();
    $inventorycontroller->createSupplier();
    break;
    case $route === '/inventory/updatesupplier':
    $inventorycontroller = new InventoryController();
    $inventorycontroller->UpdateSupplier();
    break;

    case preg_match( '#^/inventory/deletesupplier/(\d+)$#', $route, $matches ):
    $inventorycontroller = new InventoryController();
    $inventorycontroller->deleteSupplier( $matches[ 1 ] );
    break;

    // company
    case $route === '/settings/createcompany':
    $settingcontroller = new SettingController();
    $settingcontroller->CreateCompany();
    break;

    case $route === '/settings/getcompany':
    $settingcontroller = new SettingController();
    $settingcontroller->getCompanyInfo();
    break;

    case $route === '/settings/updatecompany':
    $settingcontroller = new SettingController();
    $settingcontroller->UpdateCompanyData();
    break;
    // Units
    case $route === '/settings/units':
    $settingcontroller = new SettingController();
    $settingcontroller->getAllUnits();
    break;
    default:
    Response::error( 'Endpoint not found', 404 );
    break;
}
