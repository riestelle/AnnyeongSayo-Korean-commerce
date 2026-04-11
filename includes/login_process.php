<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($con, trim($_POST['username']));
    $password = $_POST['password'];

    $query  = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../dashboard.php");
            } else {
                header("Location: ../shop.php");
            }
            exit();
        } else {
            header("Location: ../login_register.php?form=login&error=invalid_password");
        }
    } else {
        header("Location: ../login_register.php?form=login&error=user_not_found");
    }
}
?>
