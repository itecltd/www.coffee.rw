<?php
require __DIR__ . '/../../_ikawa/config/App.php';
require __DIR__ . '/../../_ikawa/middleware/auth.php';
require __DIR__ . '/../../pages/header.php';
require __DIR__ . '/../../vendor/topcontent.php';
require __DIR__ . '/../../vendor/navbar.php';
require __DIR__ . '/toasters.php';
?>

<div id = 'app'>
<?php require __DIR__ . '/dashboard.php';
?>
</div>

<?php
require __DIR__ . '/../../pages/footer.php';
?>
<?php require __DIR__ . '/getpages.php';
?>
<?php require __DIR__ . '/scripts/user-scripts.php';
?>
<?php require __DIR__ . '/scripts/roles-scripts.php';
?>
<?php require __DIR__ . '/scripts/account-scripts.php';
?>
<?php require __DIR__ . '/scripts/expense-scripts.php';
?>