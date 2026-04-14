<?php
require_once 'includes/check_admin.php';
require_once 'includes/connect.php';

$username = $_SESSION['username'] ?? 'Admin';
$user_id  = $_SESSION['user_id']  ?? '00000';
$role     = $_SESSION['role']     ?? 'admin';

$msg = ''; $msg_type = '';

// ── BULK ACTION ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'], $_POST['selected_orders'])) {
    $bulk_status = $_POST['bulk_action'];
    $allowed = ['pending', 'completed', 'cancelled'];
    if (in_array($bulk_status, $allowed)) {
        $ids = array_map('intval', $_POST['selected_orders']);
        if (!empty($ids)) {
            $id_list = implode(',', $ids);
            mysqli_query($con, "UPDATE orders SET status='$bulk_status' WHERE id IN ($id_list)");
            $count = count($ids);
            $msg = "✅ $count order(s) marked as " . strtoupper($bulk_status) . ".";
            $msg_type = 'success';
        }
    }
}

// ── SINGLE UPDATE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id   = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($con, $_POST['status']);
    $allowed    = ['pending', 'completed', 'cancelled'];
    if (in_array($new_status, $allowed)) {
        mysqli_query($con, "UPDATE orders SET status='$new_status' WHERE id=$order_id");
        $msg      = '✅ Order #' . str_pad($order_id, 5, '0', STR_PAD_LEFT) . ' updated to ' . strtoupper($new_status) . '.';
        $msg_type = 'success';
    }
}

// ── DELETE ──
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    mysqli_query($con, "DELETE FROM orders WHERE id=$del_id");
    $msg      = '🗑️ Order deleted.';
    $msg_type = 'info';
}

// ── FILTERS ──
$search        = isset($_GET['search'])  ? mysqli_real_escape_string($con, trim($_GET['search']))  : '';
$status_filter = isset($_GET['status'])  ? mysqli_real_escape_string($con, trim($_GET['status']))  : '';

// ── PAGINATION ──
$per_page    = 25;
$current_page = max(1, intval($_GET['page'] ?? 1));
$offset       = ($current_page - 1) * $per_page;

$where = "WHERE 1=1";
if ($search)        $where .= " AND (o.id LIKE '%$search%' OR u.username LIKE '%$search%')";
if ($status_filter) $where .= " AND o.status='$status_filter'";

// Total count for pagination
$count_result = mysqli_fetch_row(mysqli_query($con,
    "SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id $where"
));
$total_filtered = $count_result[0] ?? 0;
$total_pages    = max(1, ceil($total_filtered / $per_page));
$current_page   = min($current_page, $total_pages);
$offset         = ($current_page - 1) * $per_page;

$orders_result = mysqli_query($con, "
    SELECT o.*, u.username
    FROM orders o
    JOIN users u ON o.user_id = u.id
    $where
    ORDER BY
        CASE o.status WHEN 'pending' THEN 0 ELSE 1 END,
        o.order_date ASC
    LIMIT $per_page OFFSET $offset
");

// ── METRICS ──
$total_orders    = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM orders"))[0];
$pending_count   = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM orders WHERE status='pending'"))[0];
$completed_count = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM orders WHERE status='completed'"))[0];
$total_rev_row   = mysqli_fetch_row(mysqli_query($con, "SELECT SUM(total_amount) FROM orders WHERE status='completed'"));
$total_rev       = $total_rev_row[0] ?? 0;

// Overdue = pending for more than 24 hours
$overdue_count   = mysqli_fetch_row(mysqli_query($con,
    "SELECT COUNT(*) FROM orders WHERE status='pending' AND order_date < NOW() - INTERVAL 24 HOUR"
))[0];

// Build query string helper (keeps filters when paginating)
function qstr($extras = []) {
    $base = [];
    if (!empty($_GET['search']))  $base['search']  = $_GET['search'];
    if (!empty($_GET['status']))  $base['status']  = $_GET['status'];
    return '?' . http_build_query(array_merge($base, $extras));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Order Management</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;700;900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --primary: #b70048; --primary-container: #ff7290; --on-primary: #ffeff0; --primary-dim: #a1003f;
    --secondary: #006668; --secondary-container: #52f9fc; --on-secondary-container: #005b5d;
    --tertiary: #6c5a00; --tertiary-container: #fdd828; --on-tertiary-container: #5b4c00;
    --background: #f5f6f7; --surface: #f5f6f7; --on-background: #2c2f30; --on-surface: #2c2f30;
    --surface-container: #e6e8ea; --surface-container-low: #eff1f2; --surface-container-lowest: #fff;
    --surface-container-highest: #dadddf; --on-surface-variant: #595c5d;
    --outline: #757778; --outline-variant: #abadae;
    --error: #b31b25; --error-container: #fb5151; --on-error: #ffefee;
    --font-headline: 'Epilogue', sans-serif; --font-body: 'Plus Jakarta Sans', sans-serif;
  }
  body { background: var(--background); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--on-background); min-height: 100vh; display: flex; flex-direction: column; background-image: radial-gradient(#000000 1px, transparent 0); background-size: 8px 8px; }
  .material-symbols-outlined { font-family: 'Material Symbols Outlined'; font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; font-size: 24px; line-height: 1; vertical-align: middle; user-select: none; display:inline-block; }

  /* ── Header ── */
  header { background: #fff; width: 100%; border-bottom: 4px solid #000; position: sticky; top: 0; z-index: 50; }
  .header-inner { display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 1rem 2.5rem; }
  .logo { font-family: 'Epilogue', serif; font-size: 1.875rem; font-weight: 900; font-style: italic; letter-spacing: -0.05em; color: #000; text-shadow: 4px 4px 0px #fdd828; text-decoration: none; }
  .header-left-group { display: flex; align-items: baseline; gap: 3rem; }
  nav { display: flex; gap: 2rem; align-items: center; }
  nav a { font-family: 'Epilogue', serif; font-weight: 900; text-transform: uppercase; letter-spacing: -0.05em; color: #000; text-decoration: none; transition: color 0.15s; white-space: nowrap; }
  nav a:hover { color: var(--primary); }
  nav a.active { color: var(--primary); border-bottom: 4px solid var(--primary); padding-bottom: 0.25rem; }
  .profile-trigger-wrap { position: relative; }
  .profile-trigger { width: 52px; height: 52px; background: var(--primary); border: 3px solid #000; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.1s; box-shadow: 5px 5px 0px 0px #000; text-decoration: none; }
  .profile-trigger:hover { transform: translate(2px,2px); box-shadow: 3px 3px 0px 0px #000; }
  .profile-trigger .material-symbols-outlined { color: #000; font-variation-settings: 'FILL' 1,'wght' 700,'GRAD' 0,'opsz' 48; font-size: 32px; }
  .profile-dropdown { display: none; position: absolute; top: calc(100% + 12px); right: 0; background: #fff; border: 4px solid #000; box-shadow: 8px 8px 0px 0px #000; min-width: 220px; z-index: 999; transform: rotate(3deg); }
  .profile-dropdown.open { display: block; }
  .dropdown-user-info { padding: 16px 20px; background: var(--primary-container); border-bottom: 4px solid #000; }
  .dropdown-username { font-family: var(--font-headline); font-weight: 900; font-style: italic; font-size: 1.1rem; color: #000; text-transform: uppercase; display: block; }
  .dropdown-role { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: rgba(0,0,0,0.6); display: block; }
  .dropdown-logout { display: flex; align-items: center; gap: 10px; padding: 14px 20px; font-family: var(--font-headline); font-weight: 900; font-style: italic; font-size: 0.9rem; text-transform: uppercase; color: #000; text-decoration: none; background: var(--primary); transition: background 0.1s; }
  .dropdown-logout:hover { background: var(--tertiary-container); }

  /* ── Main ── */
  main { flex-grow: 1; max-width: 1440px; margin: 0 auto; padding: 2.5rem; width: 100%; }
  h1.page-title { font-family: 'Epilogue', serif; font-size: clamp(3rem,8vw,5rem); font-weight: 900; font-style: italic; -webkit-text-stroke: 1px #000; color: var(--primary); text-shadow: 6px 6px 0px #000; text-transform: uppercase; line-height: 1; letter-spacing: -0.05em; margin-bottom: 2rem; }

  /* ── Metrics ── */
  .metrics-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 1rem; margin-bottom: 1.25rem; }
  @media (min-width: 768px) { .metrics-grid { grid-template-columns: repeat(5,1fr); } }
  .metric-card { border: 4px solid #000; padding: 1.25rem; position: relative; overflow: hidden; }
  .metric-card.pink   { background: var(--primary-container); }
  .metric-card.teal   { background: var(--secondary-container); }
  .metric-card.yellow { background: var(--tertiary-container); }
  .metric-card.white  { background: #fff; }
  .metric-card.red    { background: var(--error-container); }
  .metric-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: rgba(0,0,0,0.6); margin-bottom: 0.5rem; }
  .metric-value { font-family: 'Epilogue', serif; font-size: 2.5rem; font-weight: 900; font-style: italic; -webkit-text-stroke: 1px #000; line-height: 1; }
  .metric-card.red .metric-value { color: var(--on-error); -webkit-text-stroke: 1px rgba(0,0,0,0.4); }

  /* ── Alert ── */
  .alert-msg { padding: 1rem; border: 2px solid #000; font-weight: 700; margin-bottom: 1.5rem; }
  .alert-msg.success { background: var(--secondary-container); color: var(--on-secondary-container); }
  .alert-msg.info    { background: var(--tertiary-container); color: var(--on-tertiary-container); }
  .alert-msg.error   { background: var(--error-container); color: var(--on-error); }

  /* ── Overdue banner ── */
  .overdue-banner { display: flex; align-items: center; gap: 0.75rem; background: var(--error-container); border: 3px solid #000; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; box-shadow: 4px 4px 0px 0px #000; }
  .overdue-banner .material-symbols-outlined { font-variation-settings: 'FILL' 1; color: var(--on-error); font-size: 1.5rem; flex-shrink: 0; }
  .overdue-banner p { font-family: 'Epilogue', serif; font-weight: 900; font-style: italic; font-size: 0.95rem; text-transform: uppercase; color: var(--on-error); }
  .overdue-banner a { color: var(--on-error); text-decoration: underline; cursor: pointer; }

  /* ── Toolbar ── */
  .toolbar { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: flex-end; }
  .search-wrap { position: relative; flex-grow: 1; min-width: 200px; }
  .search-wrap input { width: 100%; background: #fff; border: 4px solid #000; padding: 0.75rem 0.75rem 0.75rem 3rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.875rem; outline: none; }
  .search-wrap input:focus { box-shadow: 0 0 0 4px #fdd828; }
  .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); }
  .filter-wrap { position: relative; min-width: 160px; }
  .filter-wrap select { width: 100%; background: #fff; border: 4px solid #000; padding: 0.75rem 2.5rem 0.75rem 0.75rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.875rem; appearance: none; cursor: pointer; outline: none; }
  .filter-wrap select:focus { box-shadow: 0 0 0 4px #fdd828; }
  .filter-arrow { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; }
  .btn-search { background: #000; color: #fff; border: 4px solid #000; padding: 0.75rem 1.5rem; font-family: 'Epilogue', serif; font-weight: 900; font-size: 0.875rem; text-transform: uppercase; cursor: pointer; white-space: nowrap; }
  .btn-search:hover { background: var(--primary); border-color: var(--primary); }
  .btn-clear { background: #fff; color: #000; border: 4px solid #000; padding: 0.75rem 1rem; font-weight: 700; cursor: pointer; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; white-space: nowrap; }
  .btn-clear:hover { background: var(--surface-container); }

  /* ── Bulk action bar ── */
  .bulk-bar { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; padding: 0.75rem 1rem; background: var(--tertiary-container); border: 3px solid #000; margin-bottom: 0; }
  .bulk-bar label { font-family: 'Epilogue', serif; font-weight: 900; font-style: italic; font-size: 0.875rem; text-transform: uppercase; white-space: nowrap; }
  .bulk-bar select { background: #fff; border: 3px solid #000; padding: 0.4rem 0.6rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.875rem; cursor: pointer; outline: none; }
  .bulk-bar select:focus { box-shadow: 0 0 0 3px #000; }
  .btn-bulk-apply { background: #000; color: #fff; border: 3px solid #000; padding: 0.45rem 1.25rem; font-family: 'Epilogue', serif; font-weight: 900; font-size: 0.875rem; text-transform: uppercase; cursor: pointer; white-space: nowrap; }
  .btn-bulk-apply:hover { background: var(--primary); border-color: var(--primary); }
  .bulk-count { font-size: 0.8rem; font-weight: 700; color: var(--on-tertiary-container); margin-left: auto; white-space: nowrap; }

  /* ── Table ── */
  .table-wrap { background: #fff; border: 4px solid #000; overflow-x: auto; }
  .section-title { padding: 1.25rem 1.5rem; background: #000; color: #fff; font-family: 'Epilogue', serif; font-weight: 900; font-style: italic; font-size: 1.25rem; text-transform: uppercase; letter-spacing: -0.03em; display: flex; align-items: center; justify-content: space-between; }
  .section-title-right { font-size: 0.75rem; font-weight: 700; font-style: normal; letter-spacing: 0; color: rgba(255,255,255,0.6); }
  table { width: 100%; border-collapse: collapse; min-width: 760px; }
  thead tr { background: var(--surface-container-highest); border-bottom: 4px solid #000; }
  thead th { padding: 0.85rem 1rem; text-align: left; font-weight: 900; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap; }
  thead th.th-check { width: 40px; text-align: center; padding: 0.85rem 0.5rem; }
  tbody tr { border-bottom: 2px solid #000; transition: background 0.15s; }
  tbody tr:hover { background: var(--surface-container-low); }
  tbody tr.overdue-row { background: #fff5f5; }
  tbody tr.overdue-row:hover { background: #ffe8e8; }
  tbody td { padding: 0.85rem 1rem; font-size: 0.875rem; vertical-align: middle; }
  tbody td.td-check { text-align: center; padding: 0.85rem 0.5rem; }
  .order-id { font-weight: 900; font-family: 'Epilogue', serif; }
  .customer-name { font-weight: 700; }
  .amount { font-weight: 900; color: var(--primary); }
  .ts { font-size: 0.75rem; color: var(--on-surface-variant); font-weight: 700; }
  .badge { padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 900; border: 2px solid #000; display: inline-block; }
  .badge-pending   { background: var(--tertiary-container); color: var(--on-tertiary-container); }
  .badge-completed { background: var(--secondary-container); color: var(--on-secondary-container); }
  .badge-cancelled { background: var(--error-container); color: var(--on-error); }
  .overdue-flag { display: inline-flex; align-items: center; gap: 3px; background: var(--error); color: #fff; font-size: 0.6rem; font-weight: 900; padding: 0.1rem 0.4rem; border: 1.5px solid #000; vertical-align: middle; margin-left: 4px; text-transform: uppercase; }
  .overdue-flag .material-symbols-outlined { font-size: 0.75rem; }
  .action-cell { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
  .status-form select { background: #fff; border: 2px solid #000; padding: 0.3rem 0.5rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.75rem; cursor: pointer; }
  .btn-update { background: var(--secondary); color: #fff; border: 2px solid #000; padding: 0.3rem 0.75rem; font-weight: 900; font-size: 0.75rem; cursor: pointer; transition: background 0.15s; white-space: nowrap; }
  .btn-update:hover { background: #00595b; }
  .btn-delete { background: var(--error-container); color: var(--on-error); border: 2px solid #000; padding: 0.3rem 0.75rem; font-weight: 900; font-size: 0.75rem; cursor: pointer; text-decoration: none; white-space: nowrap; display: inline-block; }
  .btn-delete:hover { background: var(--error); }
  .no-orders { padding: 3rem; text-align: center; font-weight: 700; color: var(--outline); font-style: italic; }

  /* ── Pagination ── */
  .pagination-wrap { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; border-top: 3px solid #000; background: var(--surface-container-lowest); flex-wrap: wrap; gap: 0.75rem; }
  .pagination-info { font-size: 0.8rem; font-weight: 700; color: var(--on-surface-variant); }
  .pagination-btns { display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap; }
  .page-btn { min-width: 36px; height: 36px; background: #fff; border: 3px solid #000; font-family: 'Epilogue', serif; font-weight: 900; font-size: 0.875rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #000; transition: background 0.1s, transform 0.1s; padding: 0 0.5rem; }
  .page-btn:hover { background: var(--surface-container); transform: translateY(-1px); }
  .page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
  .page-btn:disabled, .page-btn.disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }

  /* ── Checkbox ── */
  input[type="checkbox"] { width: 18px; height: 18px; border: 2px solid #000; accent-color: var(--primary); cursor: pointer; }

  /* ── Footer ── */
  footer { background: #000; border-top: 4px solid #000; padding: 20px 32px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
  .footer-brand { display: flex; flex-direction: column; gap: 4px; }
  .footer-brand-name { font-family: 'Epilogue', sans-serif; font-size: 1.5rem; font-weight: 900; font-style: italic; letter-spacing: -0.05em; color: #fdd828; }
  .footer-rights { font-weight: 700; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.5); }
  .footer-links { list-style: none; display: flex; gap: 20px; flex-wrap: wrap; }
  .footer-links li a { font-size: 0.7rem; color: rgba(255,255,255,0.5); text-decoration: none; transition: color 0.15s; }
  .footer-links li a:hover { color: var(--primary); }
  .footer-socials { display: flex; gap: 10px; }
  .social-icon { width: 36px; height: 36px; border: 2px solid rgba(255,255,255,0.3); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.7); cursor: pointer; transition: border-color 0.15s, color 0.15s, background 0.15s; text-decoration: none; }
  .social-icon:hover { border-color: var(--primary); color: #fff; background: rgba(183,0,72,0.2); }
  .social-icon .material-symbols-outlined { font-size: 1.1rem; }
</style>
</head>
<body>

<header>
  <div class="header-inner">
    <div class="header-left-group">
      <a href="dashboard.php" class="logo">Annyeong'Sayo</a>
      <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="inventoryMng.php">Inventory</a>
        <a href="orderMng.php" class="active">Orders</a>
        <a href="usersMng.php">Users</a>
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
  <h1 class="page-title">Orders</h1>

  <?php if ($msg): ?>
  <div class="alert-msg <?php echo $msg_type; ?>"><?php echo $msg; ?></div>
  <?php endif; ?>

  <!-- Overdue Banner -->
  <?php if ($overdue_count > 0): ?>
  <div class="overdue-banner">
    <span class="material-symbols-outlined">warning</span>
    <p>
      <?php echo $overdue_count; ?> order<?php echo $overdue_count > 1 ? 's are' : ' is'; ?>
      pending for over 24 hours! &mdash;
      <a href="orderMng.php?status=pending">View pending orders</a>
    </p>
  </div>
  <?php endif; ?>

  <!-- Metrics -->
  <div class="metrics-grid">
    <div class="metric-card pink">
      <div class="metric-label">Total Orders</div>
      <div class="metric-value"><?php echo $total_orders; ?></div>
    </div>
    <div class="metric-card yellow">
      <div class="metric-label">Pending</div>
      <div class="metric-value"><?php echo $pending_count; ?></div>
    </div>
    <div class="metric-card teal">
      <div class="metric-label">Completed</div>
      <div class="metric-value"><?php echo $completed_count; ?></div>
    </div>
    <div class="metric-card red">
      <div class="metric-label">Overdue (&gt;24h)</div>
      <div class="metric-value"><?php echo $overdue_count; ?></div>
    </div>
    <div class="metric-card white">
      <div class="metric-label">Total Revenue</div>
      <div class="metric-value" style="font-size:1.75rem;">₱<?php echo number_format($total_rev, 0); ?></div>
    </div>
  </div>

  <!-- Main form wraps table + bulk bar together -->
  <form method="POST" action="orderMng.php<?php echo qstr(['page' => $current_page]); ?>" id="bulkForm">

    <!-- Search & Filter toolbar -->
    <div class="toolbar">
      <div class="search-wrap">
        <input type="text" name="search" placeholder="Search by Order ID or Customer..."
               value="<?php echo htmlspecialchars($search); ?>"
               onkeydown="if(event.key==='Enter'){this.form.action='orderMng.php';this.form.submit();}"/>
        <span class="material-symbols-outlined search-icon">search</span>
      </div>
      <div class="filter-wrap">
        <select name="status_filter" onchange="applyFilter(this.value)">
          <option value="">All Status</option>
          <option value="pending"   <?php echo $status_filter==='pending'   ? 'selected':''; ?>>Pending</option>
          <option value="completed" <?php echo $status_filter==='completed' ? 'selected':''; ?>>Completed</option>
          <option value="cancelled" <?php echo $status_filter==='cancelled' ? 'selected':''; ?>>Cancelled</option>
        </select>
        <span class="material-symbols-outlined filter-arrow">keyboard_arrow_down</span>
      </div>
      <button type="button" class="btn-search" onclick="submitSearch()">
        <span class="material-symbols-outlined" style="font-size:1rem;vertical-align:middle;">search</span>
        Search
      </button>
      <?php if ($search || $status_filter): ?>
      <a href="orderMng.php" class="btn-clear">
        <span class="material-symbols-outlined" style="font-size:1rem;margin-right:4px;">close</span>Clear
      </a>
      <?php endif; ?>
    </div>

    <!-- Bulk Action Bar -->
    <div class="bulk-bar">
      <label for="bulk_action">Bulk Action:</label>
      <select name="bulk_action" id="bulk_action">
        <option value="">— Select action —</option>
        <option value="completed">Mark as Completed</option>
        <option value="pending">Mark as Pending</option>
        <option value="cancelled">Mark as Cancelled</option>
      </select>
      <button type="submit" class="btn-bulk-apply" onclick="return confirmBulk()">Apply to Selected</button>
      <button type="button" class="btn-clear" style="padding:0.4rem 0.75rem;font-size:0.8rem;" onclick="toggleAll(false)">Deselect All</button>
      <span class="bulk-count" id="bulkCount">0 selected</span>
    </div>

    <!-- Orders Table -->
    <div class="table-wrap">
      <div class="section-title">
        <span>Order Queue</span>
        <span class="section-title-right">
          Showing <?php echo min($offset + 1, $total_filtered); ?>–<?php echo min($offset + $per_page, $total_filtered); ?>
          of <?php echo $total_filtered; ?> orders
        </span>
      </div>
      <table>
        <thead>
          <tr>
            <th class="th-check">
              <input type="checkbox" id="selectAll" title="Select all on this page" onchange="toggleAll(this.checked)"/>
            </th>
            <th>Order ID</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($orders_result && mysqli_num_rows($orders_result) > 0):
            while ($o = mysqli_fetch_assoc($orders_result)):
              $badge    = 'badge-' . $o['status'];
              $order_dt = strtotime($o['order_date']);
              $is_overdue = ($o['status'] === 'pending' && (time() - $order_dt) > 86400);
          ?>
          <tr class="<?php echo $is_overdue ? 'overdue-row' : ''; ?>">
            <td class="td-check">
              <input type="checkbox" name="selected_orders[]"
                     value="<?php echo $o['id']; ?>"
                     class="row-check" onchange="updateBulkCount()"/>
            </td>
            <td class="order-id">
              #<?php echo str_pad($o['id'], 5, '0', STR_PAD_LEFT); ?>
              <?php if ($is_overdue): ?>
              <span class="overdue-flag">
                <span class="material-symbols-outlined">schedule</span> OVERDUE
              </span>
              <?php endif; ?>
            </td>
            <td class="ts"><?php echo date('M d, Y H:i', $order_dt); ?></td>
            <td class="customer-name">
              <?php echo $o['is_walkin']
                ? htmlspecialchars($o['cashier_name'] ?? 'Walk-in')
                : htmlspecialchars($o['username']); ?>
            </td>
            <td class="amount">₱<?php echo number_format($o['total_amount'], 2); ?></td>
            <td><span class="badge <?php echo $badge; ?>"><?php echo strtoupper($o['status']); ?></span></td>
            <td>
              <div class="action-cell">
                <!-- Single update (separate form so it doesn't conflict with bulk) -->
                <form method="POST" action="orderMng.php<?php echo qstr(['page' => $current_page]); ?>"
                      class="status-form" style="display:flex;gap:0.4rem;align-items:center;">
                  <input type="hidden" name="action" value="update_status"/>
                  <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>"/>
                  <select name="status">
                    <option value="pending"   <?php echo $o['status']==='pending'   ? 'selected':''; ?>>Pending</option>
                    <option value="completed" <?php echo $o['status']==='completed' ? 'selected':''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $o['status']==='cancelled' ? 'selected':''; ?>>Cancelled</option>
                  </select>
                  <button type="submit" class="btn-update">Update</button>
                </form>
                <a href="orderMng.php?delete=<?php echo $o['id']; ?><?php echo ($search ? '&search='.urlencode($search) : '').($status_filter ? '&status='.urlencode($status_filter) : '').'&page='.$current_page; ?>"
                   class="btn-delete"
                   onclick="return confirm('Delete Order #<?php echo str_pad($o['id'],5,'0',STR_PAD_LEFT); ?>? This cannot be undone.')">
                  Delete
                </a>
              </div>
            </td>
          </tr>
          <?php endwhile;
          else: ?>
          <tr><td colspan="7" class="no-orders">No orders found. Try adjusting your filters!</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
      <div class="pagination-wrap">
        <div class="pagination-info">
          Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
          &nbsp;&middot;&nbsp; <?php echo $total_filtered; ?> total order<?php echo $total_filtered != 1 ? 's' : ''; ?>
        </div>
        <div class="pagination-btns">
          <!-- Prev -->
          <?php if ($current_page > 1): ?>
          <a class="page-btn" href="orderMng.php<?php echo qstr(['page' => $current_page - 1]); ?>">
            <span class="material-symbols-outlined" style="font-size:1rem;">chevron_left</span>
          </a>
          <?php else: ?>
          <span class="page-btn disabled">
            <span class="material-symbols-outlined" style="font-size:1rem;">chevron_left</span>
          </span>
          <?php endif; ?>

          <?php
          // Show at most 7 page buttons with ellipsis
          $range = 2;
          $pages_to_show = [];
          for ($p = 1; $p <= $total_pages; $p++) {
              if ($p === 1 || $p === $total_pages ||
                  ($p >= $current_page - $range && $p <= $current_page + $range)) {
                  $pages_to_show[] = $p;
              }
          }
          $prev = null;
          foreach ($pages_to_show as $p):
              if ($prev !== null && $p - $prev > 1): ?>
              <span class="page-btn disabled" style="border:none;background:transparent;">…</span>
          <?php   endif; ?>
          <a class="page-btn <?php echo $p === $current_page ? 'active' : ''; ?>"
             href="orderMng.php<?php echo qstr(['page' => $p]); ?>"><?php echo $p; ?></a>
          <?php   $prev = $p;
          endforeach; ?>

          <!-- Next -->
          <?php if ($current_page < $total_pages): ?>
          <a class="page-btn" href="orderMng.php<?php echo qstr(['page' => $current_page + 1]); ?>">
            <span class="material-symbols-outlined" style="font-size:1rem;">chevron_right</span>
          </a>
          <?php else: ?>
          <span class="page-btn disabled">
            <span class="material-symbols-outlined" style="font-size:1rem;">chevron_right</span>
          </span>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

    </div><!-- /.table-wrap -->
  </form>

</main>

<footer>
  <div class="footer-brand">
    <span class="footer-brand-name">Annyeong'Sayo</span>
    <span class="footer-rights">© 2025 Annyeong Market.<br/>All rights reserved.</span>
  </div>
  <ul class="footer-links">
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="inventoryMng.php">Inventory</a></li>
    <li><a href="orderMng.php">Orders</a></li>
    <li><a href="usersMng.php">Users</a></li>
  </ul>
  <div class="footer-socials">
    <a href="#" class="social-icon"><span class="material-symbols-outlined">photo_camera</span></a>
    <a href="#" class="social-icon"><span class="material-symbols-outlined">alternate_email</span></a>
    <a href="#" class="social-icon"><span class="material-symbols-outlined">smart_display</span></a>
    <a href="#" class="social-icon"><span class="material-symbols-outlined">music_note</span></a>
  </div>
</footer>

<script>
  /* ── Profile dropdown ── */
  function toggleDropdown(e) {
    e.preventDefault();
    document.getElementById('profileDropdown').classList.toggle('open');
  }
  document.addEventListener('click', function(e) {
    var wrap = document.querySelector('.profile-trigger-wrap');
    if (wrap && !wrap.contains(e.target)) document.getElementById('profileDropdown').classList.remove('open');
  });

  /* ── Filter redirect ── */
  function applyFilter(val) {
    var url = 'orderMng.php?status=' + encodeURIComponent(val);
    var search = document.querySelector('input[name="search"]').value.trim();
    if (search) url += '&search=' + encodeURIComponent(search);
    window.location.href = url;
  }
  function submitSearch() {
    var search = document.querySelector('input[name="search"]').value.trim();
    var status = document.querySelector('select[name="status_filter"]').value;
    var url = 'orderMng.php?';
    if (search) url += 'search=' + encodeURIComponent(search) + '&';
    if (status) url += 'status=' + encodeURIComponent(status);
    window.location.href = url;
  }

  /* ── Checkbox / bulk ── */
  function toggleAll(checked) {
    document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = checked; });
    document.getElementById('selectAll').checked = checked;
    updateBulkCount();
  }
  function updateBulkCount() {
    var n = document.querySelectorAll('.row-check:checked').length;
    document.getElementById('bulkCount').textContent = n + ' selected';
    // Sync "select all" indeterminate state
    var total = document.querySelectorAll('.row-check').length;
    var sel   = document.getElementById('selectAll');
    sel.indeterminate = (n > 0 && n < total);
    sel.checked = (n === total && total > 0);
  }
  function confirmBulk() {
    var n      = document.querySelectorAll('.row-check:checked').length;
    var action = document.getElementById('bulk_action').value;
    if (!action) { alert('Please select a bulk action first.'); return false; }
    if (n === 0) { alert('Please select at least one order.'); return false; }
    return confirm('Mark ' + n + ' order(s) as ' + action.toUpperCase() + '?');
  }
</script>
</body>
</html>