<?php
// register_process.php

require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login_register.php?form=register');
    exit();
}

$username         = trim($_POST['username']         ?? '');
$email            = trim($_POST['email']            ?? '');
$password         = $_POST['password']              ?? '';
$confirm_password = $_POST['confirm_password']      ?? '';

// ── Server-side validation ──
if ($username === '' || $email === '' || $password === '' || $confirm_password === '') {
    header('Location: ../login_register.php?form=register&error=All+fields+are+required');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../login_register.php?form=register&error=Invalid+email+address');
    exit();
}

if (strlen($password) < 8) {
    header('Location: ../login_register.php?form=register&error=Password+must+be+at+least+8+characters');
    exit();
}

if ($password !== $confirm_password) {
    header('Location: ../login_register.php?form=register&error=Passwords+do+not+match');
    exit();
}

// ── Check username taken (prepared statement) ──
$stmt = mysqli_prepare($con, "SELECT id FROM users WHERE username = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    header('Location: ../login_register.php?form=register&error=Username+is+already+taken');
    exit();
}
mysqli_stmt_close($stmt);

// ── Check email taken ──
$stmt2 = mysqli_prepare($con, "SELECT id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt2, 's', $email);
mysqli_stmt_execute($stmt2);
mysqli_stmt_store_result($stmt2);

if (mysqli_stmt_num_rows($stmt2) > 0) {
    mysqli_stmt_close($stmt2);
    header('Location: ../login_register.php?form=register&error=Email+is+already+registered');
    exit();
}
mysqli_stmt_close($stmt2);

// ── Insert new user ──
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
$role = 'customer';

$insert = mysqli_prepare($con, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($insert, 'ssss', $username, $email, $hashed_password, $role);

if (mysqli_stmt_execute($insert)) {
    mysqli_stmt_close($insert);
    header('Location: ../login_register.php?form=login&success=Account+created!+You+can+now+log+in');
    exit();
} else {
    mysqli_stmt_close($insert);
    header('Location: ../login_register.php?form=register&error=Something+went+wrong.+Please+try+again');
    exit();
}