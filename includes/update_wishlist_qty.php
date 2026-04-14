<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['wishlist_id'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing wishlist_id']);
        exit();
    }

    $wishlist_id = (int)$input['wishlist_id'];
    $delta = (int)($input['delta'] ?? 0);
    $user_id = (int)($_SESSION['user_id'] ?? 0);

    // Get current quantity
    $current_stmt = $con->prepare("
        SELECT quantity FROM wishlist
        WHERE id = ? AND user_id = ?
    ");
    $current_stmt->bind_param('ii', $wishlist_id, $user_id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();

    if ($current_result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['message' => 'Item not found']);
        $current_stmt->close();
        exit();
    }

    $current_row = $current_result->fetch_assoc();
    $current_qty = (int)$current_row['quantity'];
    $current_stmt->close();

    // Calculate new quantity
    $new_qty = max(1, $current_qty + $delta); // Minimum 1

    // Update quantity in database
    $update_stmt = $con->prepare("
        UPDATE wishlist SET quantity = ? WHERE id = ? AND user_id = ?
    ");
    $update_stmt->bind_param('iii', $new_qty, $wishlist_id, $user_id);

    if (!$update_stmt->execute()) {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to update quantity']);
        $update_stmt->close();
        exit();
    }
    $update_stmt->close();

    // Get updated totals
    $totals_stmt = $con->prepare("
        SELECT
            COALESCE(SUM(w.quantity), 0) as item_count,
            COALESCE(SUM(p.price * w.quantity), 0) as subtotal
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        WHERE w.user_id = ?
    ");
    $totals_stmt->bind_param('i', $user_id);
    $totals_stmt->execute();
    $totals_result = $totals_stmt->get_result();
    $totals = $totals_result->fetch_assoc();
    $totals_stmt->close();

    $subtotal = (float)$totals['subtotal'];
    $shipping = $subtotal > 0 ? 3000 : 0;
    $discount = min(4100, floor($subtotal * 0.10)); // 10% discount, max ₩4,100
    $final_total = max(0, $subtotal + $shipping - $discount);

    $response = [
        'new_qty' => $new_qty,
        'totals' => [
            'item_count' => (int)$totals['item_count'],
            'subtotal' => (int)$subtotal,
            'shipping' => $shipping,
            'discount' => (int)$discount,
            'final_total' => (int)$final_total
        ]
    ];

    echo json_encode($response);
} else {
    // Invalid request method
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
?>
