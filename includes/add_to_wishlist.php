<?php
// add_to_wishlist.php

session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing product_id']);
    exit();
}

$product_id = (int)$input['product_id'];
$user_id    = (int)$_SESSION['user_id'];

// Already in wishlist? Increment qty
$check = $con->prepare("SELECT id, quantity FROM wishlist WHERE user_id = ? AND product_id = ?");
$check->bind_param('ii', $user_id, $product_id);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    $row     = $check_result->fetch_assoc();
    $new_qty = (int)$row['quantity'] + 1;
    $check->close();

    $upd = $con->prepare("UPDATE wishlist SET quantity = ? WHERE id = ?");
    $upd->bind_param('ii', $new_qty, $row['id']);
    if ($upd->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quantity increased in wishlist']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
    }
    $upd->close();
    exit();
}
$check->close();

// Not in wishlist — insert
$ins = $con->prepare("INSERT INTO wishlist (user_id, product_id, quantity) VALUES (?, ?, 1)");
$ins->bind_param('ii', $user_id, $product_id);

if ($ins->execute()) {
    echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
}
$ins->close();