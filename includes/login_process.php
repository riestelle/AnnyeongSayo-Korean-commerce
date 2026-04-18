<?php
// login_process.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login_register.php?form=login');
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: ../login_register.php?form=login&error=All+fields+are+required');
    exit();
}

// ── Prepared statement — no SQL injection possible ──
$stmt = mysqli_prepare($con, "SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['password'])) {
        // ── Regenerate session ID on login to prevent session fixation ──
        session_regenerate_id(true);

        $_SESSION['user_id']  = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        mysqli_stmt_close($stmt);

        if ($user['role'] === 'admin') {
            header('Location: ../dashboard.php');
        } elseif ($user['role'] === 'cashier') {
            header('Location: ../cashierMng.php');
        } else {
            header('Location: ../userDashboard.php');
        }
        exit();
    }
}

mysqli_stmt_close($stmt);

// Generic message — don't reveal whether username or password was wrong
header('Location: ../login_register.php?form=login&error=Invalid+username+or+password');
exit();