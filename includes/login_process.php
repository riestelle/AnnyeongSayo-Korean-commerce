<?php
session_start(); // Mandatory for maintaining the login state
require_once 'connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = $_POST['password'];

    // 1. Find the user in the database
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // 2. VERIFY: Compare raw password to the hashed version in DB
        if (password_verify($password, $user['password'])) {
            
            // 3. AUTHENTICATION: Save user info to the Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Crucial for your admin gatekeeper

            // 4. AUTHORIZATION: Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        } else {
            header("Location: ../login.php?error=invalid_password");
        }
    } else {
        header("Location: ../login.php?error=user_not_found");
    }
}
?>