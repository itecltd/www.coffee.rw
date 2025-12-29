<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../controllers/UsersController.php';
require_once __DIR__ . '/../controllers/SettingController.php';
require_once __DIR__ . '/../controllers/AccountController.php';
require_once __DIR__ . '/../controllers/ExpenseController.php';
require_once __DIR__ . '/../controllers/ExpenseConsumeController.php';
use Controllers\UsersController;
use Controllers\SettingController;
use Controllers\AccountController;
use Controllers\ExpenseController;
use Controllers\ExpenseConsumeController;
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

    default:
    Response::error( 'Endpoint not found', 404 );
    break;
}