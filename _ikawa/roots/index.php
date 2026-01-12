<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../controllers/UsersController.php';
require_once __DIR__ . '/../controllers/SettingController.php';
require_once __DIR__ . '/../controllers/InventoryController.php';
require_once __DIR__ . '/../controllers/AccountController.php';
require_once __DIR__ . '/../controllers/ExpenseCategoryController.php';
require_once __DIR__ . '/../controllers/ExpenseController.php';
require_once __DIR__ . '/../controllers/ExpenseConsumeController.php';
require_once __DIR__ . '/../controllers/ExpenseConsumerController.php';
require_once __DIR__ . '/../controllers/SellizeController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/CategoryTypeController.php';
require_once __DIR__ . '/../controllers/UnityController.php';
require_once __DIR__ . '/../controllers/CategoryTypeUnityController.php';
require_once __DIR__ . '/../controllers/StockController.php';

use Controllers\SellizeController;
use Controllers\CategoryController;
use Controllers\CategoryTypeController;
use Controllers\UnityController;
use Controllers\CategoryTypeUnityController;
use Controllers\StockController;
use Controllers\ExpenseController;
use Controllers\ExpenseConsumeController;
use Controllers\ExpenseConsumerController;
use Controllers\ExpenseCategoryController;
use Controllers\AccountController;
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
    // payment modes
    case $route === '/settings/paymentmodes':
    $settingcontroller = new SettingController();
    $settingcontroller->getPaymentModes();
    break;

    // expenses
    case $route === '/expenses/get-all':
    $expenseController = new ExpenseController();
    $expenseController->getAllExpenses();
    break;

    case $route === '/expenses/create':
    $expenseController = new ExpenseController();
    $expenseController->createExpense();
    break;

    case $route === '/expenses/update':
    $expenseController = new ExpenseController();
    $expenseController->updateExpense();
    break;

    case preg_match( '#^/expenses/delete/(\d+)$#', $route, $matches ):
    $expenseController = new ExpenseController();
    $expenseController->deleteExpense( $matches[ 1 ] );
    break;

    case preg_match( '#^/expenses/by-category/(\d+)$#', $route, $matches ):
    $expenseController = new ExpenseController();
    $expenseController->getExpensesByCategory( $matches[ 1 ] );
    break;

    // expense categories
    case $route === '/expense-categories/get-all':
    $expenseCategoryController = new ExpenseCategoryController();
    $expenseCategoryController->getAllCategories();
    break;

    case $route === '/expense-categories/create':
    $expenseCategoryController = new ExpenseCategoryController();
    $expenseCategoryController->createCategory();
    break;

    case $route === '/expense-categories/update':
    $expenseCategoryController = new ExpenseCategoryController();
    $expenseCategoryController->updateCategory();
    break;

    case $route === '/expense-categories/delete':
    $expenseCategoryController = new ExpenseCategoryController();
    $expenseCategoryController->deleteCategory();
    break;

    case preg_match( '#^/expense-categories/check-in-use/(\d+)$#', $route, $matches ):
    $expenseCategoryController = new ExpenseCategoryController();
    $expenseCategoryController->checkCategoryInUse( $matches[ 1 ] );
    break;

    // expense consumers
    case $route === '/expense-consumers/get-all':
    $expenseConsumerController = new ExpenseConsumerController();
    $expenseConsumerController->getAllConsumers();
    break;

    case $route === '/expense-consumers/create':
    $expenseConsumerController = new ExpenseConsumerController();
    $expenseConsumerController->createConsumer();
    break;

    case $route === '/expense-consumers/update':
    $expenseConsumerController = new ExpenseConsumerController();
    $expenseConsumerController->updateConsumer();
    break;

    case $route === '/expense-consumers/delete':
    $expenseConsumerController = new ExpenseConsumerController();
    $expenseConsumerController->deleteConsumer();
    break;

    case preg_match( '#^/expense-consumers/check-in-use/(\d+)$#', $route, $matches ):
    $expenseConsumerController = new ExpenseConsumerController();
    $expenseConsumerController->checkConsumerInUse( $matches[ 1 ] );
    break;

    // expense consume
    case $route === '/expense-consume/get-all':
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->getAllExpenseConsumes();
    break;

    case $route === '/expense-consume/create':
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->createExpenseConsume();
    break;

    case $route === '/expense-consume/update':
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->updateExpenseConsume();
    break;

    case $route === '/expense-consume/delete':
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->deleteExpenseConsume();
    break;

    case preg_match( '#^/expense-consume/by-station/(\d+)$#', $route, $matches ):
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->getExpensesByStation( $matches[ 1 ] );
    break;

    case $route === '/expense-consume/total':
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->getTotalExpensesByPeriod();
    break;

    // Get accounts by payment mode
    case preg_match( '#^/accounts/by-mode/(\d+)$#', $route, $matches ):
    $expenseConsumeController = new ExpenseConsumeController();
    $expenseConsumeController->getAccountsByPaymentMode( $matches[ 1 ] );
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
    $categoryController->getAllCategories();
    // You may want to create a specific method for active categories
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
    case preg_match('#^/category-type-units/get-units-by-type/(\d+)$#', $route, $matches):
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->getUnitsByType($matches[1]);
    break;

    case $route === '/category-type-units/get-types-with-units':
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->getTypesWithUnits();
    break;

    case preg_match('#^/category-type-units/get-types-by-category/(\d+)$#', $route, $matches):
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->getTypesByCategory($matches[1]);
    break;

    case preg_match('#^/category-type-units/get-type-unity-by-category/(\d+)$#', $route, $matches):
    $categoryTypeUnityController = new CategoryTypeUnityController();
    $categoryTypeUnityController->getTypeUnityByCategory($matches[1]);
    break;

    // Stock Management
    case $route === '/stock/create':
    $stockController = new StockController();
    $stockController->create();
    break;

    case $route === '/stock/create-multiple':
    $stockController = new StockController();
    $stockController->createMultiple();
    break;

    case $route === '/stock/get-detailed-stock':
    $stockController = new StockController();
    $stockController->getDetailedStock();
    break;

    case $route === '/stock/get-summary-stock':
    $stockController = new StockController();
    $stockController->getSummaryStock();
    break;

    default:
    Response::error( 'Endpoint not found', 404 );
    break;
}
