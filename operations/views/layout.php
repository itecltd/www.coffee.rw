<?php
require_once __DIR__ . '/../../_ikawa/config/App.php';
require_once __DIR__ . '/../../_ikawa/middleware/auth.php';
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
<?php require __DIR__ . '/scripts/supplier-scripts.php';
?>
<?php require __DIR__ . '/scripts/company-scripts.php';
?>

<?php require __DIR__ . '/scripts/stations-scripts.php';
?>
<!-- accounts -->
<?php require __DIR__ . '/scripts/account-scripts.php';
?>
<!-- account recharge -->
<?php require __DIR__ . '/scripts/recharge-scripts.php';
?>
<!-- expense category -->
<?php require __DIR__ . '/scripts/expense-category-scripts.php';
?>
<!-- expense consume new -->
<?php require __DIR__ . '/scripts/expense-consumer-scripts-new.php';
?>
<!-- expense -->
<?php require __DIR__ . '/scripts/expense-scripts.php';
?>
<!-- expense consume  -->
<?php require __DIR__ . '/scripts/expense-consume-scripts.php';
?>
<!-- expense statement  -->
<?php require __DIR__ . '/scripts/expense-statement-scripts.php';
?>
<!-- Selize -->
<?php require __DIR__ . '/scripts/sellize-script.php';
?>
<!-- Category -->
<?php require __DIR__ . '/scripts/category-script.php';
?>
<!-- Category type-->
<?php require __DIR__ . '/scripts/category-type-script.php';
?>
<!-- Unity -->
<?php require __DIR__ . '/scripts/unity-script.php';
?>
<!-- Category type unit -->
<?php require __DIR__ . '/scripts/category-type-unity-script.php';
?>