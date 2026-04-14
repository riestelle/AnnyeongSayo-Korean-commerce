<?php
// Start session and include database connection
session_start();
require_once 'includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login_register.php');
    exit();
}

$username = $_SESSION['username'] ?? 'Guest';
$role     = $_SESSION['role'] ?? 'customer';
$user_id  = $_SESSION['user_id'] ?? null;

// Base path for images
define('IMG_PATH', 'https://res.cloudinary.com/ds3irzr48/image/upload/q_auto/f_auto/');

// Fetch wishlist items
$wishlist_items = [];
$stmt = $con->prepare("
    SELECT w.id as wishlist_id, w.product_id, p.name, p.description,
           p.price, p.image_url as image, w.quantity
    FROM wishlist w
    JOIN products p ON p.id = w.product_id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $wishlist_items[] = $row;
}
$stmt->close();

// If no items, redirect back to wishlist
if (empty($wishlist_items)) {
    header('Location: wishlistCart.php?error=empty_wishlist');
    exit();
}

// Calculate totals
$total_items = array_sum(array_map(fn($i) => $i['quantity'], $wishlist_items));
$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $wishlist_items));
$shipping = $subtotal > 0 ? 3000 : 0;
$discount = $subtotal > 0 ? 4100 : 0;
$final_total = $subtotal + $shipping - $discount;

// Handle order placement
$order_placed = false;
$order_error = '';
$order_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'] ?? '';
    $tendered = isset($_POST['tendered_amount']) ? (float)$_POST['tendered_amount'] : 0;

    if (empty($payment_method)) {
        $order_error = '❌ Please select a payment method.';
    } elseif ($tendered < $final_total) {
        $short = number_format($final_total - $tendered, 2);
        $order_error = "❌ Amount short by ₩$short";
    } elseif ($tendered > $final_total) {
        $excess = number_format($tendered - $final_total, 2);
        $order_error = "❌ Amount exceeds by ₩$excess";
    } else {
        // Insert order into database
        $status = 'completed';
        $insert_order = $con->prepare("
            INSERT INTO orders (user_id, total_amount, status)
            VALUES (?, ?, ?)
        ");
        $insert_order->bind_param('ids', $user_id, $final_total, $status);

        if ($insert_order->execute()) {
            $order_db_id = $insert_order->insert_id;
            $insert_order->close();

            // Insert order items
            $insert_items = $con->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($wishlist_items as $item) {
                $price = (float)$item['price'];
                $qty = (int)$item['quantity'];
                $pid = (int)$item['product_id'];
                $insert_items->bind_param('iidi', $order_db_id, $pid, $qty, $price);
                $insert_items->execute();
            }
            $insert_items->close();

            // Clear wishlist
            $clear = $con->prepare("DELETE FROM wishlist WHERE user_id = ?");
            $clear->bind_param('i', $user_id);
            $clear->execute();
            $clear->close();

            $order_placed = true;
            $order_id = '#' . str_pad($order_db_id, 6, '0', STR_PAD_LEFT);
        } else {
            $order_error = '❌ Error placing order. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Order Review | MINI-MART!</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,900;1,900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

:root {
  --primary: #b70048;
  --primary-container: #ff7290;
  --primary-fixed: #ff7290;
  --secondary: #006668;
  --secondary-container: #52f9fc;
  --tertiary: #6c5a00;
  --tertiary-container: #fdd828;
  --tertiary-fixed: #fdd828;
  --background: #f5f6f7;
  --surface: #f5f6f7;
  --on-background: #2c2f30;
  --on-surface: #2c2f30;
}

body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background-color: var(--background);
  color: var(--on-background);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-image: radial-gradient(#000000 1px, transparent 0);
  background-size: 8px 8px;
}

html, body { height: 100%; }

header {
  background: #fff;
  width: 100%;
  border-bottom: 4px solid #000;
  position: sticky;
  top: 0;
  z-index: 50;
}

.header-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 1rem 2.5rem;
}

.logo {
  font-family: 'Epilogue', serif;
  font-size: 1.875rem;
  font-weight: 900;
  font-style: italic;
  letter-spacing: -0.05em;
  color: #000;
  text-shadow: 4px 4px 0px #fdd828;
  text-decoration: none;
  flex-shrink: 0;
}

nav {
  display: flex;
  gap: 2rem;
  align-items: center;
}

nav a {
  font-family: 'Epilogue', serif;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: -0.05em;
  color: #000;
  text-decoration: none;
  font-size: 0.875rem;
}

nav a:hover { color: var(--primary); }

main {
  flex: 1;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
  padding: clamp(1rem, 5vw, 3rem) clamp(0.5rem, 4vw, 1.5rem);
}

.page-title {
  font-family: 'Epilogue', sans-serif;
  font-size: clamp(2rem, 6vw, 3rem);
  font-weight: 900;
  font-style: italic;
  color: var(--primary);
  text-transform: uppercase;
  margin-bottom: 2rem;
  -webkit-text-stroke: 1px #000;
  filter: drop-shadow(3px 3px 0px #000);
}

.order-review {
  background: #fff;
  border: 4px solid #000;
  box-shadow: 6px 6px 0px 0px #000;
  padding: clamp(1rem, 4vw, 2rem);
  margin-bottom: 2rem;
}

.review-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 1.25rem;
  font-weight: 900;
  text-transform: uppercase;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #000;
}

.order-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1.5rem;
  max-height: 400px;
  overflow-y: auto;
}

.order-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border: 2px solid #000;
  background: var(--surface);
  align-items: center;
}

.item-image {
  width: 80px;
  height: 80px;
  border: 2px solid #000;
  overflow: hidden;
  flex-shrink: 0;
  background: var(--tertiary-container);
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-details {
  flex: 1;
}

.item-name {
  font-weight: 900;
  font-size: 0.95rem;
  margin-bottom: 0.25rem;
}

.item-qty {
  font-size: 0.85rem;
  color: var(--on-surface-variant);
  margin-bottom: 0.5rem;
}

.item-price {
  font-weight: 900;
  color: var(--primary);
  font-size: 1rem;
}

.order-summary {
  background: var(--tertiary-container);
  border: 3px solid #000;
  box-shadow: 4px 4px 0px 0px #000;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 700;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
}

.summary-row.total {
  font-size: 1.5rem;
  color: var(--primary);
  border-top: 3px solid #000;
  padding-top: 1rem;
  margin-top: 1rem;
}

.payment-form {
  background: #fff;
  border: 4px solid #000;
  box-shadow: 6px 6px 0px 0px #000;
  padding: clamp(1rem, 4vw, 2rem);
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  font-weight: 900;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  font-size: 0.875rem;
}

.form-input, .form-select {
  width: 100%;
  padding: 0.75rem;
  border: 3px solid #000;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-weight: 700;
  font-size: 1rem;
  background: #fff;
}

.form-input:focus, .form-select:focus {
  outline: none;
  box-shadow: 0 0 0 4px var(--tertiary-fixed);
}

.btn-place-order {
  width: 100%;
  background: var(--primary);
  color: #fff;
  border: 4px solid #000;
  padding: 1rem;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  font-size: 1.125rem;
  text-transform: uppercase;
  cursor: pointer;
  box-shadow: 6px 6px 0px 0px #000;
  transition: transform 0.1s, box-shadow 0.1s;
}

.btn-place-order:active {
  transform: translate(4px, 4px);
  box-shadow: none;
}

.btn-back {
  display: inline-block;
  background: #fff;
  color: #000;
  border: 3px solid #000;
  padding: 0.75rem 1.5rem;
  font-weight: 900;
  text-transform: uppercase;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 2rem;
}

.btn-back:hover {
  background: var(--secondary-container);
}

.error-msg {
  background: #f8d7da;
  border: 3px solid #721c24;
  color: #721c24;
  padding: 1rem;
  margin-bottom: 1rem;
  font-weight: 700;
}

.success-box {
  background: #d4edda;
  border: 4px solid #1a5c2a;
  box-shadow: 6px 6px 0px 0px #1a5c2a;
  padding: 2rem;
  text-align: center;
  margin-bottom: 2rem;
}

.success-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 1.875rem;
  font-weight: 900;
  color: #1a5c2a;
  margin-bottom: 0.5rem;
}

.order-number {
  font-family: 'Epilogue', sans-serif;
  font-size: 1.5rem;
  font-weight: 900;
  color: var(--primary);
  margin: 1rem 0;
}

.success-message {
  font-size: 1.125rem;
  color: #1a5c2a;
  margin-bottom: 1.5rem;
}

.btn-continue {
  background: var(--primary);
  color: #fff;
  border: 3px solid #000;
  padding: 0.75rem 2rem;
  font-weight: 900;
  text-transform: uppercase;
  text-decoration: none;
  cursor: pointer;
  display: inline-block;
  box-shadow: 4px 4px 0px 0px #000;
}

.btn-continue:hover {
  transform: translate(2px, 2px);
  box-shadow: 2px 2px 0px 0px #000;
}

footer {
  background: #000;
  border-top: 4px solid #000;
  padding: 20px 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.footer-brand { display: flex; flex-direction: column; gap: 4px; }
.footer-brand-name {
  font-family: 'Epilogue', sans-serif;
  font-size: 1.5rem;
  font-weight: 900;
  font-style: italic;
  letter-spacing: -0.05em;
  color: #fdd828;
  text-shadow: 3px 3px 0px #000;
}

.footer-rights {
  font-weight: 700;
  font-size: 0.65rem;
  text-transform: uppercase;
  color: rgba(255,255,255,0.5);
}

@media (min-width: 768px) {
  .order-item {
    flex-direction: row;
  }
}
</style>
</head>
<body>

<header>
  <div class="header-inner">
    <a href="login_register.php" class="logo">Annyeong</a>
    <nav>
      <a href="userDashboard.php">Dashboard</a>
      <a href="wishlistCart.php">Wishlist</a>
    </nav>
  </div>
</header>

<main>
  <?php if ($order_placed): ?>
    <div class="success-box">
      <div class="success-title">✅ Order Placed Successfully!</div>
      <div class="order-number"><?= htmlspecialchars($order_id) ?></div>
      <div class="success-message">Thank you for your purchase. Your order has been confirmed.</div>
      <a href="userDashboard.php" class="btn-continue">Back to Dashboard</a>
    </div>
  <?php else: ?>
    <a href="wishlistCart.php" class="btn-back">← Back to Wishlist</a>
    <h1 class="page-title">Order Review</h1>

    <?php if ($order_error): ?>
      <div class="error-msg"><?= htmlspecialchars($order_error) ?></div>
    <?php endif; ?>

    <div class="order-review">
      <div class="review-title">📦 Items in Your Order</div>
      <div class="order-items">
        <?php foreach ($wishlist_items as $item): ?>
          <div class="order-item">
            <div class="item-image">
              <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"/>
            </div>
            <div class="item-details">
              <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="item-qty">Quantity: <?= (int)$item['quantity'] ?> × ₩<?= number_format((float)$item['price']) ?></div>
              <div class="item-price">₩<?= number_format((float)$item['price'] * (int)$item['quantity']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="order-summary">
      <div class="summary-row">
        <span>Subtotal (<?= $total_items ?> items)</span>
        <span>₩<?= number_format($subtotal) ?></span>
      </div>
      <div class="summary-row">
        <span>Shipping</span>
        <span>₩<?= number_format($shipping) ?></span>
      </div>
      <div class="summary-row">
        <span>Membership Discount</span>
        <span>-₩<?= number_format($discount) ?></span>
      </div>
      <div class="summary-row total">
        <span>TOTAL</span>
        <span>₩<?= number_format($final_total) ?></span>
      </div>
    </div>

    <form method="POST" class="payment-form">
      <div class="form-group">
        <label class="form-label">Payment Method</label>
        <select name="payment_method" class="form-select" required>
          <option value="">Select Payment Method</option>
          <option value="card">Credit/Debit Card</option>
          <option value="cash">Cash</option>
          <option value="bank_transfer">Bank Transfer</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Amount to Pay (₩)</label>
        <input type="number" name="tendered_amount" class="form-input" step="0.01" placeholder="₩<?= number_format($final_total) ?>" required/>
      </div>

      <button type="submit" name="place_order" class="btn-place-order">
        Place Order Now
      </button>
    </form>
  <?php endif; ?>
</main>

<footer>
  <div class="footer-brand">
    <span class="footer-brand-name">Annyeong</span>
    <span class="footer-rights">© 2025 Annyeong Market</span>
  </div>
</footer>

</body>
</html>
