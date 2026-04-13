<?php
// Database connection using MySQLi
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "korean_store";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Set charset to UTF-8
$con->set_charset("utf8mb4");
?>
