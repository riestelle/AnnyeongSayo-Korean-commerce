<?php
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($con, trim($_POST['username']));
    $email    = mysqli_real_escape_string($con, trim($_POST['email'] ?? ''));
    $password = $_POST['password'];
    $role     = 'customer';

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $checkUser = "SELECT * FROM users WHERE username = '$username'";
    $result    = mysqli_query($con, $checkUser);

    if (mysqli_num_rows($result) > 0) {
        header("Location: ../login_register.php?form=register&error=taken");
    } else {
        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', '$role')";
        if (mysqli_query($con, $sql)) {
            header("Location: ../login_register.php?form=login&success=registered");
        } else {
            header("Location: ../login_register.php?form=register&error=db_error");
        }
    }
}
?>
