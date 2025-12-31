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
<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../controllers/UsersController.php';
require_once __DIR__ . '/../controllers/SettingController.php';
require_once __DIR__ . '/../controllers/InventoryController.php';
require_once __DIR__ . '/../controllers/SellizeController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/CategoryTypeController.php';
require_once __DIR__ . '/../controllers/UnityController.php';
require_once __DIR__ . '/../controllers/CategoryTypeUnityController.php';

use Controllers\UsersController;
use Controllers\SettingController;
use Config\Response;
use Controllers\InventoryController;
use Controllers\SellizeController;
use Controllers\CategoryController;
use Controllers\CategoryTypeController;
use Controllers\UnityController;
use Controllers\CategoryTypeUnityController;
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
    case $route === '/settings/location':
    case $route === '/settings/locations':
    $settingcontroller = new SettingController();
    $settingcontroller->getAllLocation();
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

    // Categories
    case $route === '/categories/create':
    $categoryController = new CategoryController();
    $categoryController->create();
    break;
    case $route === '/categories/get-all-categories':
    $categoryController = new CategoryController();
    $categoryController->getAllCategories();
    break;
    case $route === '/categories/update':
    $categoryController = new CategoryController();   
    $categoryController->update();
    break;
    case $route === '/categories/delete':
    $categoryController = new CategoryController();
    $categoryController->delete();
    break;

    // Sellize
    case $route === '/sallize/create':
    $sellizeController = new SellizeController();
    $sellizeController->create();
    break;
    case $route === '/sallize/get-all-sallize':
    $sellizeController = new SellizeController();
    $sellizeController->getAllSallize();
    break;
    case $route === '/sallize/update':
    $sellizeController = new SellizeController();   
    $sellizeController->update();
    break;
    case $route === '/sallize/delete':
    $sellizeController = new SellizeController();
    $sellizeController->delete();
    break;

    // Category Types
    case $route === '/category-types/create':
    $categoryTypeController = new CategoryTypeController();
    $categoryTypeController->create();
    break;
    case $route === '/category-types/get-all-category-types':
    $categoryTypeController = new CategoryTypeController();
    $categoryTypeController->getAllCategoryTypes();
    break;
    case $route === '/category-types/update':
    $categoryTypeController = new CategoryTypeController();   
    $categoryTypeController->update();
    break;
    case $route === '/category-types/delete':
    $categoryTypeController = new CategoryTypeController();
    $categoryTypeController->delete();
    break;
    case $route === '/categories/get-active-categories':
    $categoryController = new CategoryController();
    $categoryController->getAllCategories(); // You may want to create a specific method for active categories
    break;

    // Unity
    case $route === '/unity/create':
    $unityController = new UnityController();
    $unityController->create();
    break;
    case $route === '/unity/get-all-unity':
    $unityController = new UnityController();
    $unityController->getAllUnity();
    break;
    case $route === '/unity/update':
    $unityController = new UnityController();   
    $unityController->update();
    break;
    case $route === '/unity/delete':
    $unityController = new UnityController();
    $unityController->delete();
    break;

    // Category Type Units
    case $route === '/category-type-units/create':
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->create();
    break;
    case $route === '/category-type-units/get-all-assignments':
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->getAllAssignments();
    break;
    case $route === '/category-type-units/update':
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->update();
    break;
    case $route === '/category-type-units/delete':
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->delete();
    break;

    default:
    Response::error( 'Endpoint not found', 404 );
    break;
}
