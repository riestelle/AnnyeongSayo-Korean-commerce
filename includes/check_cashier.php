<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['admin', 'cashier'])) {
    header("Location: ../login_register.php?form=login&error=unauthorized");
    exit();
}
?>