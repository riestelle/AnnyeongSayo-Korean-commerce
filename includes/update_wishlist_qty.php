<?php
// update_wishlist_qty.php

session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

// ── Auth check ──
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['wishlist_id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing wishlist_id']);
    exit();
}

$wishlist_id = (int)$input['wishlist_id'];
$delta       = (int)($input['delta'] ?? 0);
$user_id     = (int)$_SESSION['user_id'];

// Fetch current qty — user_id in WHERE prevents tampering with other users' items
$cur = $con->prepare("SELECT quantity FROM wishlist WHERE id = ? AND user_id = ?");
$cur->bind_param('ii', $wishlist_id, $user_id);
$cur->execute();
$cur_result = $cur->get_result();

if ($cur_result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['message' => 'Item not found']);
    $cur->close();
    exit();
}

$current_qty = (int)$cur_result->fetch_assoc()['quantity'];
$cur->close();

$new_qty = max(1, $current_qty + $delta);

$upd = $con->prepare("UPDATE wishlist SET quantity = ? WHERE id = ? AND user_id = ?");
$upd->bind_param('iii', $new_qty, $wishlist_id, $user_id);

if (!$upd->execute()) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to update quantity']);
    $upd->close();
    exit();
}
$upd->close();

$totals_stmt = $con->prepare("
    SELECT
        COALESCE(SUM(w.quantity), 0)          AS item_count,
        COALESCE(SUM(p.price * w.quantity), 0) AS subtotal
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
");
$totals_stmt->bind_param('i', $user_id);
$totals_stmt->execute();
$totals = $totals_stmt->get_result()->fetch_assoc();
$totals_stmt->close();

$subtotal    = (float)$totals['subtotal'];
$shipping    = $subtotal > 0 ? 3000 : 0;
$discount    = min(4100, (int)floor($subtotal * 0.10));
$final_total = max(0, $subtotal + $shipping - $discount);

echo json_encode([
    'new_qty' => $new_qty,
    'totals'  => [
        'item_count'  => (int)$totals['item_count'],
        'subtotal'    => (int)$subtotal,
        'shipping'    => $shipping,
        'discount'    => $discount,
        'final_total' => (int)$final_total,
    ],
]);