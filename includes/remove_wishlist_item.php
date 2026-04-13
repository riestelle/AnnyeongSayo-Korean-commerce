<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['wishlist_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing wishlist_id']);
    exit();
}

$wishlist_id = (int)$input['wishlist_id'];
$user_id = (int)($_SESSION['user_id'] ?? 0);

// Delete from wishlist
$stmt = $con->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $wishlist_id, $user_id);

if ($stmt->execute()) {
    // Get updated totals (now accounting for quantity)
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
    $discount = $subtotal > 0 ? 4100 : 0;

    echo json_encode([
        'success' => true,
        'message' => 'Removed from wishlist',
        'totals' => [
            'item_count' => (int)$totals['item_count'],
            'subtotal' => (int)$subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'final_total' => (int)($subtotal + $shipping - $discount)
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
}
$stmt->close();
?>
