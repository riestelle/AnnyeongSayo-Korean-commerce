<?php
// Start the session to access user data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Access Control Logic:
 * 1. Check if the user is logged in (session user_id exists).
 * 2. Check if the user's role is 'admin'.
 * * This implements the Principle of Least Privilege by restricting 
 * non-admin users from management pages.
 */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // If not an admin, redirect to login page or a "not authorized" error
    header("Location: login.php?error=unauthorized");
    exit();
}

// If the code reaches this point, the user is an authenticated Admin.
?>