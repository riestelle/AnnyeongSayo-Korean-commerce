<?php
// Migration script: Add quantity column to wishlist table if it doesn't exist
session_start();
require_once 'db_connection.php';

// Check if quantity column exists
$result = $con->query("SHOW COLUMNS FROM wishlist LIKE 'quantity'");

if ($result->num_rows === 0) {
    // Column doesn't exist, add it
    $alter_sql = "ALTER TABLE wishlist ADD COLUMN quantity INT(11) DEFAULT 1 AFTER product_id";

    if ($con->query($alter_sql)) {
        echo json_encode(['success' => true, 'message' => 'Quantity column added to wishlist table']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add quantity column: ' . $con->error]);
    }
} else {
    echo json_encode(['success' => true, 'message' => 'Quantity column already exists']);
}
?>
