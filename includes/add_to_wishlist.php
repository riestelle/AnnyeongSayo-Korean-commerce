<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing product_id']);
    exit();
}

$product_id = (int)$input['product_id'];
$user_id = (int)$_SESSION['user_id'];

// Check if product already in wishlist
$check_stmt = $con->prepare("SELECT id, quantity FROM wishlist WHERE user_id = ? AND product_id = ?");
$check_stmt->bind_param('ii', $user_id, $product_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Already in wishlist — increment quantity
    $row = $check_result->fetch_assoc();
    $wishlist_id = $row['id'];
    $current_qty = $row['quantity'];
    $new_qty = $current_qty + 1;

    $update_stmt = $con->prepare("UPDATE wishlist SET quantity = ? WHERE id = ?");
    $update_stmt->bind_param('ii', $new_qty, $wishlist_id);

    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quantity increased in wishlist']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
    }
    $update_stmt->close();
    $check_stmt->close();
    exit();
}
$check_stmt->close();

// Product not in wishlist — add with quantity 1
$stmt = $con->prepare("INSERT INTO wishlist (user_id, product_id, quantity) VALUES (?, ?, 1)");
$stmt->bind_param('ii', $user_id, $product_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
}
$stmt->close();
?>
