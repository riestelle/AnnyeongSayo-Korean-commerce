<?php
require_once 'includes/connect.php'; // Use your verified connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = $_POST['password']; 
    $role = 'customer'; // Default role for new sign-ups

    // 1. SECURITY: Hash the password (Information Security requirement)
    // We use BCRYPT, just like in your previous CMD exercises.
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // 2. CHECK: Ensure username isn't taken
    $checkUser = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($con, $checkUser);

    if (mysqli_num_rows($result) > 0) {
        header("Location: ../register.php?error=taken");
    } else {
        // 3. ACTION: Insert into the korean_store database
        $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";
        
        if (mysqli_query($con, $sql)) {
            header("Location: ../login.php?success=registered");
        } else {
            echo "Error: " . mysqli_error($con);
        }
    }
}
?>