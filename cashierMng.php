<?php
require_once 'includes/check_cashier.php';
require_once 'includes/connect.php';

$username = $_SESSION['username'] ?? 'Cashier';
$role     = $_SESSION['role'] ?? 'cashier';
$msg = ''; $msg_type = '';

// ── SEARCH PRODUCT (AJAX) ──
if (isset($_GET['ajax']) && $_GET['ajax'] === 'search') {
    $term = mysqli_real_escape_string($con, trim($_GET['q'] ?? ''));
    $res  = mysqli_query($con, "SELECT * FROM products WHERE stock_quantity > 0 AND (name LIKE '%$term%' OR barcode LIKE '%$term%') LIMIT 8");
    $rows = [];
    while ($p = mysqli_fetch_assoc($res)) $rows[] = $p;
    echo json_encode($rows);
    exit();
}

// ── CREATE ORDER ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_order') {
    $product_ids = $_POST['product_id'] ?? [];
    $quantities  = $_POST['quantity'] ?? [];
    $total = 0;
    $items = [];

    foreach ($product_ids as $i => $pid) {
        $pid = intval($pid);
        $qty = intval($quantities[$i] ?? 1);
        if ($qty <= 0) continue;
        $res = mysqli_query($con, "SELECT * FROM products WHERE id=$pid AND stock_quantity >= $qty");
        if ($res && mysqli_num_rows($res) > 0) {
            $product = mysqli_fetch_assoc($res);
            $subtotal = $product['price'] * $qty;
            $total += $subtotal;
            $items[] = ['id' => $pid, 'qty' => $qty, 'price' => $product['price'], 'name' => $product['name'], 'barcode' => $product['barcode']];
        }
    }

    if (!empty($items) && $total > 0) {
        $guest_id = 1;
        $cashier_name = mysqli_real_escape_string($con, $username);
        mysqli_query($con, "INSERT INTO orders (user_id, total_amount, status, is_walkin, cashier_name) VALUES ($guest_id, $total, 'completed', 1, '$cashier_name')");
        $order_id = mysqli_insert_id($con);
        foreach ($items as $item) {
            mysqli_query($con, "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES ($order_id, {$item['id']}, {$item['qty']}, {$item['price']})");
            mysqli_query($con, "UPDATE products SET stock_quantity = stock_quantity - {$item['qty']} WHERE id={$item['id']}");
        }
        // Store receipt data in session
        $_SESSION['last_receipt'] = [
            'order_id' => $order_id,
            'items'    => $items,
            'total'    => $total,
            'cashier'  => $username,
            'date'     => date('F d, Y h:i A')
        ];
        header("Location: cashierMng.php?receipt=1");
        exit();
    } else {
        $msg = '❌ Some items are out of stock or invalid.';
        $msg_type = 'error';
    }
}

// ── UPDATE STATUS ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id   = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($con, $_POST['status']);
    if (in_array($new_status, ['pending','completed','cancelled'])) {
        mysqli_query($con, "UPDATE orders SET status='$new_status' WHERE id=$order_id");
        $msg = '✅ Order updated!';
        $msg_type = 'success';
    }
}

$show_receipt = isset($_GET['receipt']) && isset($_SESSION['last_receipt']);
$receipt = $show_receipt ? $_SESSION['last_receipt'] : null;
if ($show_receipt) unset($_SESSION['last_receipt']);

$orders_result = mysqli_query($con, "
    SELECT o.*, u.username FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC LIMIT 20
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Cashier — Annyeong'Sayo</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,900;1,900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --surface: #f5f6f7; --surface-container-lowest: #ffffff; --surface-container: #e6e8ea;
    --surface-container-low: #eff1f2; --surface-container-highest: #dadddf;
    --on-surface: #2c2f30; --on-surface-variant: #595c5d;
    --primary: #b70048; --primary-container: #f4a0b0; --on-primary: #ffeff0;
    --secondary: #006668; --secondary-container: #a8d5d6; --on-secondary-container: #005b5d;
    --tertiary-container: #e8c84a; --on-tertiary-container: #5b4c00;
    --error: #b31b25; --error-container: #e87070; --on-error: #fff4f4;
    --outline: #757778;
  }
  body { background: var(--surface); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--on-surface); min-height: 100vh; display: flex; flex-direction: column; }
  .material-symbols-outlined { font-family: 'Material Symbols Outlined'; font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; font-size: 24px; line-height: 1; vertical-align: middle; user-select: none; display: inline-block; }
  header { background: #fff; border-bottom: 4px solid #000; position: sticky; top: 0; z-index: 50; }
  .header-inner { display: flex; justify-content: space-between; align-items: center; padding: 1rem 2.5rem; }
  .logo { font-family: 'Epilogue', serif; font-size: 1.875rem; font-weight: 900; font-style: italic; letter-spacing: -0.05em; color: #000; text-shadow: 4px 4px 0px #e8c84a; text-decoration: none; }
  .header-left-group { display: flex; align-items: baseline; gap: 3rem; }
  nav { display: flex; gap: 2rem; }
  nav a { font-family: 'Epilogue', serif; font-weight: 900; text-transform: uppercase; letter-spacing: -0.05em; color: #000; text-decoration: none; white-space: nowrap; }
  nav a:hover { color: var(--primary); }
  nav a.active { color: var(--primary); border-bottom: 4px solid var(--primary); padding-bottom: 0.25rem; }
  .profile-trigger-wrap { position: relative; }
  .profile-trigger { width: 52px; height: 52px; background: var(--primary); border: 3px solid #000; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 5px 5px 0 #000; text-decoration: none; transition: all 0.1s; }
  .profile-trigger:hover { transform: translate(2px,2px); box-shadow: 3px 3px 0 #000; }
  .profile-trigger .material-symbols-outlined { color: #000; font-variation-settings: 'FILL' 1,'wght' 700,'GRAD' 0,'opsz' 48; font-size: 32px; }
  .profile-dropdown { display: none; position: absolute; top: calc(100% + 12px); right: 0; background: #fff; border: 4px solid #000; box-shadow: 8px 8px 0 #000; min-width: 220px; z-index: 999; transform: rotate(3deg); }
  .profile-dropdown.open { display: block; }
  .dropdown-user-info { padding: 16px 20px; background: var(--primary-container); border-bottom: 4px solid #000; }
  .dropdown-username { font-family: 'Epilogue', serif; font-weight: 900; font-style: italic; font-size: 1.1rem; color: #000; text-transform: uppercase; display: block; }
  .dropdown-role { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: rgba(0,0,0,0.6); display: block; }
  .dropdown-logout { display: flex; align-items: center; gap: 10px; padding: 14px 20px; font-family: 'Epilogue', serif; font-weight: 900; font-style: italic; font-size: 0.9rem; text-transform: uppercase; color: #000; text-decoration: none; background: var(--primary); }
  .dropdown-logout:hover { background: var(--tertiary-container); }
  main { flex-grow: 1; max-width: 1440px; margin: 0 auto; padding: 2.5rem; width: 100%; }
  h1.page-title { font-family: 'Epilogue', serif; font-size: clamp(3rem,8vw,5rem); font-weight: 900; font-style: italic; -webkit-text-stroke: 1px #000; color: var(--primary); text-shadow: 6px 6px 0 #000; text-transform: uppercase; line-height: 1; letter-spacing: -0.05em; margin-bottom: 2rem; }
  .main-grid { display: grid; grid-template-columns: 1fr; gap: 2.5rem; }
  @media (min-width: 1100px) { .main-grid { grid-template-columns: 7fr 5fr; } }
  .alert-msg { padding: 1rem; border: 2px solid #000; font-weight: 700; margin-bottom: 1.5rem; }
  .alert-msg.success { background: var(--secondary-container); color: var(--on-secondary-container); }
  .alert-msg.error   { background: var(--error-container); color: var(--on-error); }

  /* POS Panel */
  .pos-panel { background: #fff; border: 4px solid #000; box-shadow: 10px 10px 0 #000; }
  .pos-header { background: #000; color: #fff; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
  .pos-header h2 { font-family: 'Epilogue', serif; font-weight: 900; font-style: italic; font-size: 1.5rem; text-transform: uppercase; }
  .pos-header .ref { font-size: 0.75rem; font-weight: 700; color: #aaa; }
  .pos-body { padding: 1.5rem; }

  /* Lookup */
  .lookup-section { margin-bottom: 1.5rem; }
  .lookup-label { font-size: 0.75rem; font-weight: 900; text-transform: uppercase; color: var(--on-surface-variant); margin-bottom: 0.5rem; }
  .lookup-row { display: flex; gap: 0.5rem; }
  .lookup-input { flex: 1; background: #fff; border: 3px solid #000; padding: 0.75rem 1rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.9rem; outline: none; }
  .lookup-input:focus { border-color: var(--primary); }
  .btn-lookup { background: #000; color: #fff; border: 3px solid #000; padding: 0.75rem 1rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; gap: 0.25rem; white-space: nowrap; font-size: 0.85rem; }
  .btn-lookup:hover { background: var(--primary); }
  .search-results { border: 2px solid #000; background: #fff; max-height: 200px; overflow-y: auto; display: none; }
  .search-result-item { padding: 0.6rem 1rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; font-size: 0.85rem; }
  .search-result-item:hover { background: var(--tertiary-container); }
  .search-result-item .item-name { font-weight: 700; }
  .search-result-item .item-meta { font-size: 0.75rem; color: var(--on-surface-variant); }
  .search-result-item .item-price { font-weight: 900; color: var(--primary); }
  .divider { display: flex; align-items: center; gap: 0.75rem; margin: 0.75rem 0; }
  .divider span { font-size: 0.75rem; font-weight: 900; color: var(--outline); text-transform: uppercase; }
  .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #000; }

  /* Order list */
  .order-list { min-height: 120px; margin-bottom: 1rem; }
  .order-list-header { display: grid; grid-template-columns: 1fr auto auto auto; gap: 0.5rem; padding: 0.5rem 0; border-bottom: 2px solid #000; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; color: var(--on-surface-variant); }
  .order-list-empty { padding: 2rem; text-align: center; color: var(--outline); font-weight: 700; font-style: italic; font-size: 0.875rem; border: 2px dashed var(--outline); margin-top: 0.5rem; }
  .order-line { display: grid; grid-template-columns: 1fr auto auto auto; gap: 0.5rem; padding: 0.6rem 0; border-bottom: 1px solid #eee; align-items: center; }
  .order-line .line-name { font-weight: 700; font-size: 0.875rem; }
  .order-line .line-barcode { font-size: 0.7rem; color: var(--outline); }
  .order-line .line-qty input { width: 55px; border: 2px solid #000; padding: 0.3rem; font-weight: 700; font-size: 0.8rem; text-align: center; background: #fff; outline: none; }
  .order-line .line-price { font-weight: 900; font-size: 0.875rem; color: var(--primary); min-width: 70px; text-align: right; }
  .btn-remove { background: none; border: none; cursor: pointer; color: var(--error); display: flex; align-items: center; padding: 0; }
  .btn-remove .material-symbols-outlined { font-size: 1.1rem; }

  /* Total */
  .total-section { border-top: 4px solid #000; padding-top: 1rem; margin-top: 0.5rem; }
  .total-row { display: flex; justify-content: space-between; align-items: center; padding: 0.25rem 0; font-size: 0.875rem; }
  .total-row.grand { font-family: 'Epilogue', serif; font-size: 1.75rem; font-weight: 900; font-style: italic; border-top: 2px solid #000; margin-top: 0.5rem; padding-top: 0.5rem; }
  .btn-charge { display: block; width: 100%; background: var(--primary); color: var(--on-primary); border: 2px solid #000; padding: 1.25rem; box-shadow: 6px 6px 0 #000; cursor: pointer; transition: all 0.15s; margin-top: 1.25rem; font-family: 'Epilogue', serif; font-weight: 900; font-size: 1.5rem; text-transform: uppercase; font-style: italic; -webkit-text-stroke: 1px #000; }
  .btn-charge:hover { transform: translate(3px,3px); box-shadow: none; }
  .btn-clear-order { display: block; width: 100%; background: #fff; border: 2px solid #000; padding: 0.6rem; cursor: pointer; margin-top: 0.5rem; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; }
  .btn-clear-order:hover { background: var(--error-container); }

  /* Receipt Modal */
  .receipt-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 200; display: flex; align-items: center; justify-content: center; padding: 1rem; }
  .receipt-box { background: #fff; border: 4px solid #000; box-shadow: 12px 12px 0 #000; max-width: 420px; width: 100%; max-height: 90vh; overflow-y: auto; }
  .receipt-top { background: #000; color: #fff; padding: 1.5rem; text-align: center; }
  .receipt-top .r-logo { font-family: 'Epilogue', serif; font-weight: 900; font-style: italic; font-size: 2rem; color: var(--tertiary-container); text-shadow: 3px 3px 0 #555; }
  .receipt-top .r-sub { font-size: 0.75rem; color: #aaa; margin-top: 0.25rem; }
  .receipt-body { padding: 1.5rem; font-size: 0.875rem; }
  .r-ref { text-align: center; margin-bottom: 1rem; }
  .r-ref .r-num { font-family: 'Epilogue', serif; font-size: 1.5rem; font-weight: 900; font-style: italic; color: var(--primary); }
  .r-meta { display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--on-surface-variant); margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px dashed #000; }
  .r-items { margin-bottom: 1rem; }
  .r-item { display: flex; justify-content: space-between; padding: 0.3rem 0; border-bottom: 1px dashed #eee; }
  .r-item .r-item-name { font-weight: 700; }
  .r-item .r-item-price { font-weight: 900; }
  .r-total { border-top: 2px solid #000; padding-top: 0.75rem; }
  .r-total-row { display: flex; justify-content: space-between; padding: 0.2rem 0; }
  .r-total-row.r-grand { font-family: 'Epilogue', serif; font-size: 1.5rem; font-weight: 900; font-style: italic; border-top: 2px solid #000; margin-top: 0.5rem; padding-top: 0.5rem; }
  .r-footer { text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 2px dashed #000; font-size: 0.75rem; color: var(--outline); }
  .r-actions { display: flex; gap: 0.5rem; padding: 1rem 1.5rem; border-top: 2px solid #000; }
  .btn-print { flex: 1; background: #000; color: #fff; border: 2px solid #000; padding: 0.75rem; font-weight: 900; font-size: 0.875rem; text-transform: uppercase; cursor: pointer; }
  .btn-print:hover { background: var(--secondary); }
  .btn-close-receipt { flex: 1; background: #fff; border: 2px solid #000; padding: 0.75rem; font-weight: 700; font-size: 0.875rem; text-transform: uppercase; cursor: pointer; }
  .btn-close-receipt:hover { background: var(--surface-container); }

  /* Recent orders */
  .col-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 4px solid #000; padding-bottom: 1rem; margin-bottom: 1.5rem; }
  .col-header h2 { font-family: 'Epilogue', serif; font-size: 2rem; font-weight: 900; text-transform: uppercase; letter-spacing: -0.05em; }
  .table-wrap { background: #fff; border: 4px solid #000; overflow-x: auto; }
  .section-title { padding: 1rem 1.5rem; background: #000; color: #fff; font-family: 'Epilogue', serif; font-weight: 900; font-style: italic; font-size: 1.1rem; text-transform: uppercase; }
  table { width: 100%; border-collapse: collapse; min-width: 400px; }
  thead tr { background: var(--surface-container-highest); border-bottom: 4px solid #000; }
  thead th { padding: 0.75rem 1rem; text-align: left; font-weight: 900; font-size: 0.75rem; text-transform: uppercase; }
  tbody tr { border-bottom: 2px solid #000; }
  tbody tr:hover { background: var(--surface-container-low); }
  tbody td { padding: 0.75rem 1rem; font-size: 0.875rem; vertical-align: middle; }
  .badge { padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 900; border: 2px solid #000; display: inline-block; }
  .badge-pending   { background: var(--tertiary-container); color: var(--on-tertiary-container); }
  .badge-completed { background: var(--secondary-container); color: var(--on-secondary-container); }
  .badge-cancelled { background: var(--error-container); color: var(--on-error); }
  .status-form select { background: #fff; border: 2px solid #000; padding: 0.3rem 0.5rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.75rem; }
  .btn-update { background: var(--secondary); color: #fff; border: 2px solid #000; padding: 0.3rem 0.75rem; font-weight: 900; font-size: 0.75rem; cursor: pointer; white-space: nowrap; }
  .amount { font-weight: 900; color: var(--primary); }

  footer { background: #000; border-top: 4px solid #000; padding: 40px; display: flex; flex-direction: column; justify-content: space-between; align-items: center; gap: 24px; margin-top: auto; }
  @media (min-width: 768px) { footer { flex-direction: row; align-items: flex-start; } }
  .footer-brand-name { font-family: 'Epilogue', sans-serif; font-weight: 900; font-style: italic; font-size: 1.4rem; color: var(--tertiary-container); }
  .footer-rights { font-size: 0.8rem; font-weight: 700; color: #888; text-transform: uppercase; margin-top: 8px; }
  .footer-links { list-style: none; display: flex; gap: 24px; flex-wrap: wrap; }
  .footer-links a { color: #fff; text-decoration: none; font-weight: 800; font-size: 0.9rem; text-transform: uppercase; }
  .footer-links a:hover { color: var(--primary); }

  @media print {
    body * { visibility: hidden; }
    .receipt-box, .receipt-box * { visibility: visible; }
    .receipt-box { position: fixed; left: 50%; top: 0; transform: translateX(-50%); box-shadow: none; border: none; }
    .r-actions { display: none; }
  }
</style>
</head>
<body>

<header>
  <div class="header-inner">
    <div class="header-left-group">
      <a href="<?php echo $role === 'admin' ? 'dashboard.php' : 'cashierMng.php'; ?>" class="logo">Annyeong'Sayo</a>
      <nav>
        <?php if ($role === 'admin'): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="inventoryMng.php">Inventory</a>
        <a href="orderMng.php">Orders</a>
        <a href="usersMng.php">Users</a>
        <?php endif; ?>
        <a href="cashierMng.php" class="active">Cashier</a>
      </nav>
    </div>
    <div class="profile-trigger-wrap">
      <a href="#" class="profile-trigger" onclick="toggleDropdown(event)">
        <span class="material-symbols-outlined">account_circle</span>
      </a>
      <div class="profile-dropdown" id="profileDropdown">
        <div class="dropdown-user-info">
          <span class="dropdown-username"><?php echo htmlspecialchars($username); ?></span>
          <span class="dropdown-role"><?php echo htmlspecialchars($role); ?></span>
        </div>
        <a href="includes/logout.php" class="dropdown-logout">
          <span class="material-symbols-outlined">logout</span> Log Out
        </a>
      </div>
    </div>
  </div>
</header>

<main>
  <h1 class="page-title">Cashier</h1>

  <?php if ($msg): ?>
  <div class="alert-msg <?php echo $msg_type; ?>"><?php echo $msg; ?></div>
  <?php endif; ?>

  <div class="main-grid">

    <!-- LEFT: POS -->
    <div>
      <div class="pos-panel">
        <div class="pos-header">
          <h2>Point of Sale</h2>
          <span class="ref">Cashier: <?php echo htmlspecialchars($username); ?></span>
        </div>
        <div class="pos-body">

          <!-- Barcode Input -->
          <div class="lookup-section">
            <div class="lookup-label"><span class="material-symbols-outlined" style="font-size:1rem;vertical-align:middle;">barcode_scanner</span> Scan / Type Barcode</div>
            <div class="lookup-row">
              <input type="text" id="barcodeInput" class="lookup-input" placeholder="e.g. KST000001" autocomplete="off"/>
              <button type="button" class="btn-lookup" onclick="lookupBarcode()">
                <span class="material-symbols-outlined" style="font-size:1.1rem;">add</span> Add
              </button>
            </div>
          </div>

          <div class="divider"><span>or search by name</span></div>

          <!-- Name Search -->
          <div class="lookup-section">
            <div class="lookup-label"><span class="material-symbols-outlined" style="font-size:1rem;vertical-align:middle;">search</span> Search Product</div>
            <div class="lookup-row">
              <input type="text" id="searchInput" class="lookup-input" placeholder="Type product name..." autocomplete="off" oninput="searchProducts(this.value)"/>
            </div>
            <div class="search-results" id="searchResults"></div>
          </div>

          <!-- Order Lines -->
          <div class="order-list-header">
            <span>Item</span>
            <span>Qty</span>
            <span>Price</span>
            <span></span>
          </div>
          <div class="order-list" id="orderList">
            <div class="order-list-empty" id="emptyMsg">No items added yet. Scan a barcode or search above.</div>
          </div>

          <!-- Total -->
          <div class="total-section">
            <div class="total-row"><span>Subtotal</span><span id="subtotalDisplay">₱0.00</span></div>
            <div class="total-row grand"><span>TOTAL</span><span id="totalDisplay">₱0.00</span></div>
          </div>

          <!-- Hidden form for submission -->
          <form id="orderForm" method="POST" action="cashierMng.php">
            <input type="hidden" name="action" value="create_order"/>
            <div id="formItems"></div>
            <button type="button" class="btn-charge" onclick="submitOrder()">Complete Sale</button>
            <button type="button" class="btn-clear-order" onclick="clearOrder()">Clear Order</button>
          </form>

        </div>
      </div>
    </div>

    <!-- RIGHT: Recent Orders -->
    <div>
      <div class="col-header">
        <h2>Recent Orders</h2>
      </div>
      <div class="table-wrap">
        <div class="section-title">Last 20 Transactions</div>
        <table>
          <thead>
            <tr>
              <th>Ref #</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($orders_result && mysqli_num_rows($orders_result) > 0):
              while ($o = mysqli_fetch_assoc($orders_result)): ?>
            <tr>
              <td>
                <strong>#<?php echo str_pad($o['id'], 5, '0', STR_PAD_LEFT); ?></strong><br>
                <small style="color:var(--on-surface-variant)"><?php echo date('M d, H:i', strtotime($o['order_date'])); ?></small>
              </td>
              <td class="amount">₱<?php echo number_format($o['total_amount'], 2); ?></td>
              <td><span class="badge badge-<?php echo $o['status']; ?>"><?php echo strtoupper($o['status']); ?></span></td>
              <td>
                <form method="POST" action="cashierMng.php" style="display:flex;gap:0.4rem;align-items:center;">
                  <input type="hidden" name="action" value="update_status"/>
                  <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>"/>
                  <select name="status" class="status-form">
                    <option value="pending"   <?php echo $o['status']==='pending'   ? 'selected':''; ?>>Pending</option>
                    <option value="completed" <?php echo $o['status']==='completed' ? 'selected':''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $o['status']==='cancelled' ? 'selected':''; ?>>Cancelled</option>
                  </select>
                  <button type="submit" class="btn-update">Update</button>
                </form>
              </td>
            </tr>
            <?php endwhile;
            else: ?>
            <tr><td colspan="4" style="padding:2rem;text-align:center;color:var(--outline);font-weight:700;">No transactions yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<!-- Receipt Modal -->
<?php if ($show_receipt && $receipt): ?>
<div class="receipt-overlay" id="receiptOverlay">
  <div class="receipt-box" id="receiptBox">
    <div class="receipt-top">
      <div class="r-logo">Annyeong'Sayo</div>
      <div class="r-sub">Korean Store — Official Receipt</div>
    </div>
    <div class="receipt-body">
      <div class="r-ref">
        <div style="font-size:0.75rem;color:var(--outline);text-transform:uppercase;font-weight:700;">Reference Number</div>
        <div class="r-num">#<?php echo str_pad($receipt['order_id'], 5, '0', STR_PAD_LEFT); ?></div>
      </div>
      <div class="r-meta">
        <span><?php echo $receipt['date']; ?></span>
        <span>Cashier: <?php echo htmlspecialchars($receipt['cashier']); ?></span>
      </div>
      <div class="r-items">
        <?php foreach ($receipt['items'] as $item): ?>
        <div class="r-item">
          <div>
            <div class="r-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
            <div style="font-size:0.7rem;color:var(--outline);"><?php echo $item['barcode']; ?> × <?php echo $item['qty']; ?></div>
          </div>
          <div class="r-item-price">₱<?php echo number_format($item['price'] * $item['qty'], 2); ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="r-total">
        <div class="r-total-row"><span>Subtotal</span><span>₱<?php echo number_format($receipt['total'], 2); ?></span></div>
        <div class="r-total-row r-grand"><span>TOTAL</span><span>₱<?php echo number_format($receipt['total'], 2); ?></span></div>
      </div>
      <div class="r-footer">Thank you for shopping at Annyeong! 감사합니다</div>
    </div>
    <div class="r-actions">
      <button class="btn-print" onclick="window.print()">🖨 Print Receipt</button>
      <button class="btn-close-receipt" onclick="document.getElementById('receiptOverlay').style.display='none'">Close</button>
    </div>
  </div>
</div>
<?php endif; ?>

<footer>
  <div class="footer-brand">
    <span class="footer-brand-name">Annyeong'Sayo</span>
    <span class="footer-rights">© 2025 Annyeong Market. All rights reserved.</span>
  </div>
  <ul class="footer-links">
    <?php if ($role === 'admin'): ?>
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="inventoryMng.php">Inventory</a></li>
    <li><a href="orderMng.php">Orders</a></li>
    <li><a href="usersMng.php">Users</a></li>
    <?php endif; ?>
    <li><a href="cashierMng.php">Cashier</a></li>
  </ul>
</footer>

<script>
  let orderItems = [];

  function searchProducts(q) {
    if (q.length < 1) { document.getElementById('searchResults').style.display = 'none'; return; }
    fetch('cashierMng.php?ajax=search&q=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(data => {
        const box = document.getElementById('searchResults');
        if (!data.length) { box.style.display = 'none'; return; }
        box.innerHTML = data.map(p => `
          <div class="search-result-item" onclick="addItem(${p.id}, '${escJs(p.name)}', ${p.price}, '${escJs(p.barcode)}', ${p.stock_quantity})">
            <div>
              <div class="item-name">${p.name}</div>
              <div class="item-meta">${p.barcode} · ${p.stock_quantity} in stock</div>
            </div>
            <div class="item-price">₱${parseFloat(p.price).toFixed(2)}</div>
          </div>`).join('');
        box.style.display = 'block';
      });
  }

  function lookupBarcode() {
    const val = document.getElementById('barcodeInput').value.trim();
    if (!val) return;
    fetch('cashierMng.php?ajax=search&q=' + encodeURIComponent(val))
      .then(r => r.json())
      .then(data => {
        const exact = data.find(p => p.barcode === val);
        if (exact) {
          addItem(exact.id, exact.name, exact.price, exact.barcode, exact.stock_quantity);
          document.getElementById('barcodeInput').value = '';
          document.getElementById('barcodeInput').focus();
        } else if (data.length === 1) {
          addItem(data[0].id, data[0].name, data[0].price, data[0].barcode, data[0].stock_quantity);
          document.getElementById('barcodeInput').value = '';
          document.getElementById('barcodeInput').focus();
        } else {
          alert('Product not found: ' + val);
        }
      });
  }

  // Also trigger barcode lookup on Enter key
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('barcodeInput').addEventListener('keydown', e => {
      if (e.key === 'Enter') { e.preventDefault(); lookupBarcode(); }
    });
  });

  function addItem(id, name, price, barcode, stock) {
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('searchInput').value = '';
    const existing = orderItems.find(i => i.id === id);
    if (existing) {
      if (existing.qty < stock) { existing.qty++; renderOrder(); }
      else alert('Not enough stock!');
      return;
    }
    orderItems.push({ id, name, price: parseFloat(price), barcode, stock, qty: 1 });
    renderOrder();
  }

  function renderOrder() {
    const list = document.getElementById('orderList');
    const empty = document.getElementById('emptyMsg');
    if (!orderItems.length) {
      list.innerHTML = '<div class="order-list-empty" id="emptyMsg">No items added yet. Scan a barcode or search above.</div>';
      updateTotal(); return;
    }
    list.innerHTML = orderItems.map((item, i) => `
      <div class="order-line">
        <div>
          <div class="line-name">${item.name}</div>
          <div class="line-barcode">${item.barcode}</div>
        </div>
        <div class="line-qty">
          <input type="number" value="${item.qty}" min="1" max="${item.stock}" onchange="updateQty(${i}, this.value)"/>
        </div>
        <div class="line-price">₱${(item.price * item.qty).toFixed(2)}</div>
        <button class="btn-remove" onclick="removeItem(${i})">
          <span class="material-symbols-outlined">delete</span>
        </button>
      </div>`).join('');
    updateTotal();
  }

  function updateQty(i, val) {
    val = parseInt(val);
    if (val < 1) val = 1;
    if (val > orderItems[i].stock) { val = orderItems[i].stock; alert('Max stock reached!'); }
    orderItems[i].qty = val;
    renderOrder();
  }

  function removeItem(i) {
    orderItems.splice(i, 1);
    renderOrder();
  }

  function updateTotal() {
    const total = orderItems.reduce((s, i) => s + i.price * i.qty, 0);
    document.getElementById('subtotalDisplay').textContent = '₱' + total.toFixed(2);
    document.getElementById('totalDisplay').textContent = '₱' + total.toFixed(2);
  }

  function clearOrder() {
    if (orderItems.length && !confirm('Clear all items?')) return;
    orderItems = [];
    renderOrder();
  }

  function submitOrder() {
    if (!orderItems.length) { alert('Add at least one item!'); return; }
    const formItems = document.getElementById('formItems');
    formItems.innerHTML = orderItems.map(item =>
      `<input type="hidden" name="product_id[]" value="${item.id}"/>
       <input type="hidden" name="quantity[]" value="${item.qty}"/>`
    ).join('');
    document.getElementById('orderForm').submit();
  }

  function escJs(str) {
    return str.replace(/'/g, "\\'").replace(/"/g, '\\"');
  }

  function toggleDropdown(e) {
    e.preventDefault();
    document.getElementById('profileDropdown').classList.toggle('open');
  }
  document.addEventListener('click', function(e) {
    const wrap = document.querySelector('.profile-trigger-wrap');
    if (wrap && !wrap.contains(e.target)) document.getElementById('profileDropdown').classList.remove('open');
    const searchBox = document.getElementById('searchResults');
    const searchInput = document.getElementById('searchInput');
    if (searchBox && !searchBox.contains(e.target) && e.target !== searchInput) searchBox.style.display = 'none';
  });
</script>
</body>
</html>