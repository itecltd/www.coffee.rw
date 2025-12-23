<?php
require __DIR__ . '/../_ikawa/config/App.php';
require __DIR__ . '/../_ikawa/middleware/auth.php';

$page = $_GET[ 'page' ] ?? 'dashboard';

// whitelist of allowed pages
$allowed_pages = [
    'dashboard' => 'views/dashboard.php',
    'manage-users' => 'views/manage-users.php',
    'manage-roles' => 'views/manage-roles.php',
    'manage-accounts' => 'views/manage-accounts.php',
    'manage-expenses' => 'views/manage-expenses.php',
    'permissions' => 'views/permissions-data.php',
    'profile' => 'views/profile.php',
];

if ( isset( $allowed_pages[ $page ] ) ) {
    require __DIR__ . '/' . $allowed_pages[ $page ];
} else {
    http_response_code( 404 );
    echo '<h3>Page not found</h3>';
}

