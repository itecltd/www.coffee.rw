<?php
require_once __DIR__ . '/../_ikawa/config/App.php';
require __DIR__ . '/../_ikawa/middleware/auth.php';

$page = $_GET[ 'page' ] ?? 'dashboard';

// whitelist of allowed pages
$allowed_pages = [
    'dashboard' => 'views/dashboard.php',
    'manage-users' => 'views/manage-users.php',
    'manage-roles' => 'views/manage-roles.php',
    'permissions' => 'views/permissions-data.php',
    'suppliers'=>'views/suppliers-data.php',
    'profile'=>'views/profile-data.php',
    'stations'=>'views/stations-data.php',
    'manage-accounts' => 'views/manage-accounts.php',
    'account-recharge' => 'views/manage-account-recharge.php',
    'approve-investments' => 'views/approve-investments.php',
    'my-rejected-investments' => 'views/my-rejected-investments.php',
    'manage-expense-categories' => 'views/manage-expense-categories.php',
    'manage-expense-consumers' => 'views/manage-expense-consumers.php',
    'manage-expenses' => 'views/manage-expenses.php',
    'manage-expense-consume' => 'views/manage-expense-consume.php',
    'expense-consumed-statement' => 'views/expense-consumed-statement.php',
    'reports' => 'views/reports.php',
    'sellize-management'=>'views/sellize-management.php',
    'coffee-categories'=>'views/coffee-categories.php',
    'coffee-types'=>'views/coffee-types.php',
    'coffee-types-assign-unity'=>'views/coffee-types-assign-unity.php',
    'unity'=>'views/unity.php',
];

if ( isset( $allowed_pages[ $page ] ) ) {
    require __DIR__ . '/' . $allowed_pages[ $page ];
} else {
    http_response_code( 404 );
    echo '<h3>Page not found</h3>';
}

