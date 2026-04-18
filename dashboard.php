<?php
session_start();
require_once 'includes/check_admin.php';
require_once 'includes/connect.php';

$username = $_SESSION['username'] ?? 'Admin';
$role     = $_SESSION['role']     ?? 'admin';
$user_id  = $_SESSION['user_id']  ?? '00000';

// Real stats from DB — separated by order type
$total_orders        = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM orders WHERE is_walkin = 0"))[0];
$total_walkin_orders = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM orders WHERE is_walkin = 1"))[0];
$total_users         = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM users WHERE role='customer'"))[0];
$total_products      = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM products"))[0];

$online_revenue_row  = mysqli_fetch_row(mysqli_query($con, "SELECT SUM(total_amount) FROM orders WHERE status='completed' AND is_walkin = 0"));
$walkin_revenue_row  = mysqli_fetch_row(mysqli_query($con, "SELECT SUM(total_amount) FROM orders WHERE status='completed' AND is_walkin = 1"));
$total_revenue       = $online_revenue_row[0] ?? 0;
$walkin_revenue      = $walkin_revenue_row[0] ?? 0;

// Top 3 selling products (all orders)
$top_products_result = mysqli_query($con, "
    SELECT p.name, p.price, p.image_url, SUM(oi.quantity) AS sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY sold DESC
    LIMIT 3
");

// Top 4 online customers by spend (exclude walk-in ghost orders)
$top_customers_result = mysqli_query($con, "
    SELECT u.id, u.username, SUM(o.total_amount) AS total_spent
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.status = 'completed' AND o.is_walkin = 0
    GROUP BY u.id
    ORDER BY total_spent DESC
    LIMIT 4
");

// Pending orders count (online only — walk-ins are auto-completed)
$pending_orders = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM orders WHERE status='pending' AND is_walkin = 0"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>Dashboard — Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,400;0,700;0,900;1,900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --surface: #f5f6f7; --surface-container: #e6e8ea; --surface-container-low: #eff1f2;
      --surface-container-high: #e0e3e4; --surface-container-highest: #dadddf;
      --surface-container-lowest: #ffffff; --surface-bright: #f5f6f7; --surface-dim: #d1d5d7;
      --surface-variant: #dadddf; --on-surface: #2c2f30; --on-surface-variant: #595c5d;
      --on-background: #2c2f30; --background: #f5f6f7; --primary: #b70048; --primary-dim: #a1003f;
      --primary-container: #f4a0b0; --primary-fixed: #f4a0b0; --primary-fixed-dim: #f08096;
      --on-primary: #ffeff0; --on-primary-fixed: #000000; --on-primary-container: #4d001a;
      --secondary: #006668; --secondary-dim: #00595b; --secondary-container: #a8d5d6;
      --secondary-fixed: #a8d5d6; --secondary-fixed-dim: #8fc8ca; --on-secondary: #d8f0f1;
      --on-secondary-fixed: #004749; --on-secondary-container: #005b5d;
      --tertiary: #6c5a00; --tertiary-container: #e8c84a; --on-tertiary: #fdf6d8;
      --on-tertiary-fixed: #453900; --on-tertiary-container: #5b4c00;
      --error: #b31b25; --error-container: #e87070; --on-error: #fff4f4;
      --outline: #757778; --outline-variant: #abadae;
      --inverse-surface: #0c0f10; --inverse-on-surface: #9b9d9e; --inverse-primary: #ef7090;
      --font-headline: 'Epilogue', sans-serif; --font-body: 'Plus Jakarta Sans', sans-serif;
    }
    body { background: var(--surface); font-family: var(--font-body); color: var(--on-surface); display: flex; flex-direction: column; min-height: 100vh;  }
    .halftone {  }
    .kinetic-shadow { box-shadow: 6px 6px 0 0 #000; }
    .neon-stroke { -webkit-text-stroke: 1.5px #000; }
    .stamp-effect { transform: rotate(-12deg); border: 4px double var(--primary); padding: 4px 8px; font-weight: 900; text-transform: uppercase; }
    header { background: #ffffff; width: 100%; border-bottom: 4px solid #000000; position: sticky; top: 0; z-index: 50; }
    .header-inner { display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 1rem 2.5rem; }
    .logo { font-family: 'Epilogue', serif; font-size: 1.875rem; font-weight: 900; font-style: italic; letter-spacing: -0.05em; color: #000000; text-shadow: 4px 4px 0px #e8c84a; text-decoration: none; flex-shrink: 0; }
    .header-left-group { display: flex; align-items: baseline; gap: 3rem; }
    nav { display: flex; gap: 2rem; align-items: center; background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; }
    nav a { font-family: 'Epilogue', serif; font-weight: 900; text-transform: uppercase; letter-spacing: -0.05em; color: #000000; text-decoration: none; transition: color 0.15s, transform 0.15s; white-space: nowrap; }
    nav a:hover { color: var(--primary); transform: skewX(-2deg) translateY(-2px); background: transparent !important; }
    nav a.active { color: var(--primary); border-bottom: 4px solid var(--primary); padding-bottom: 0.25rem; }
    .profile-trigger-wrap { position: relative; flex-shrink: 0; }
    .profile-trigger { width: 52px; height: 52px; background-color: var(--primary); border: 3px solid #000000; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.1s; box-shadow: 5px 5px 0px 0px #000000; text-decoration: none; flex-shrink: 0; }
    .profile-trigger:hover { transform: translate(2px, 2px); box-shadow: 3px 3px 0px 0px #000000; }
    .profile-trigger:active { transform: translate(5px, 5px); box-shadow: none; }
    .profile-trigger .material-symbols-outlined { color: #000000; font-variation-settings: 'FILL' 1, 'wght' 700, 'GRAD' 0, 'opsz' 48; font-size: 32px; }
    .profile-dropdown { display: none; position: absolute; top: calc(100% + 12px); right: 0; background: #ffffff; border: 4px solid #000000; box-shadow: 8px 8px 0px 0px #000000; min-width: 220px; z-index: 999; transform: rotate(3deg); }
    .profile-dropdown.open { display: block; }
    .dropdown-user-info { padding: 16px 20px; display: flex; flex-direction: column; gap: 4px; background: var(--primary-container); border-bottom: 4px solid #000;  }
    .dropdown-username { font-family: var(--font-headline); font-weight: 900; font-style: italic; font-size: 1.1rem; color: #000000; text-transform: uppercase; letter-spacing: -0.03em; }
    .dropdown-role { font-family: var(--font-body); font-weight: 700; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(0,0,0,0.6); }
    .dropdown-id { font-family: var(--font-body); font-weight: 700; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(0,0,0,0.4); }
    .dropdown-divider { height: 0; border-top: 2px solid #000; }
    .dropdown-logout { display: flex; align-items: center; gap: 10px; padding: 14px 20px; font-family: var(--font-headline); font-weight: 900; font-style: italic; font-size: 0.9rem; text-transform: uppercase; color: #000000; text-decoration: none; background: var(--primary); border-top: 2px solid #000; transition: background 0.1s, color 0.1s; letter-spacing: -0.02em; }
    .dropdown-logout:hover { background: var(--tertiary-container); color: #000; }
    .dropdown-logout .material-symbols-outlined { font-size: 1.1rem; }
    main { flex-grow: 1; padding: 48px 24px; max-width: 1440px; margin: 0 auto; width: 100%; display: grid; grid-template-columns: repeat(12, 1fr); gap: 32px; }
    .main-left { grid-column: span 12; display: flex; flex-direction: column; gap: 32px; }
    .sidebar { grid-column: span 12; display: flex; flex-direction: column; gap: 32px; }
    @media (min-width: 1024px) { .main-left { grid-column: span 8; } .sidebar { grid-column: span 4; } }
    section, .card { background: #fff; border: 4px solid #000; }
    .revenue-section { padding: 24px; position: relative; overflow: hidden; }
    .revenue-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; position: relative; z-index: 1; }
    .revenue-header h2 { font-family: var(--font-headline); font-size: 2.25rem; font-weight: 900; font-style: italic; text-transform: uppercase; letter-spacing: -0.05em; color: var(--on-background); }
    .revenue-header .subtitle { font-weight: 700; color: var(--secondary-dim); text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.875rem; }
    .revenue-amount { font-size: 3rem; font-family: var(--font-headline); font-weight: 900; color: var(--primary); }
    .revenue-badge { display: flex; align-items: center; justify-content: flex-end; background: var(--secondary-container); color: var(--on-secondary-container); padding: 4px 8px; margin-top: 8px; font-weight: 700; font-style: italic; border: 2px solid #000; font-size: 0.875rem; gap: 4px; }
    .revenue-badge .material-symbols-outlined { font-size: 1rem; }
    .bar-chart-wrap { width: 100%; background: var(--surface-container-low); border: 2px solid #000; position: relative; }
    .bars { position: absolute; bottom: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: flex-end; padding: 0 16px 16px; gap: 8px; }
    .bar { flex: 1; border: 2px solid #000; transition: transform 0.3s; transform-origin: bottom; }
    .bar.pink { background: var(--primary); } .bar.teal { background: var(--secondary); } .bar.yellow { background: var(--tertiary-container); }
    .gradient-overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.05), transparent); pointer-events: none; }
    .items-section { background: var(--surface-container-lowest); border: 4px solid #000; padding: 24px; }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .section-header h3 { font-family: var(--font-headline); font-size: 1.875rem; font-weight: 900; font-style: italic; text-transform: uppercase; letter-spacing: -0.05em; }
    .badge-heat { background: #000; color: #fff; padding: 4px 16px; font-weight: 700; transform: skewX(12deg); font-size: 0.875rem; }
    .items-grid { display: flex; flex-direction: row; gap: 16px; overflow-x: auto; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; padding-bottom: 8px; }
    .items-grid::-webkit-scrollbar { height: 4px; } .items-grid::-webkit-scrollbar-track { background: var(--surface-container); } .items-grid::-webkit-scrollbar-thumb { background: #000; }
    .item-card { scroll-snap-align: start; flex: 0 0 200px; cursor: pointer; }
    .item-img-wrap { aspect-ratio: 1; border: 4px solid #000; position: relative; overflow: hidden; margin-bottom: 12px; background: var(--surface-container); display:flex; align-items:center; justify-content:center; }
    .item-img-wrap .material-symbols-outlined { font-size: 3rem; color: var(--outline); }
    .item-rank { position: absolute; top: 8px; left: 8px; font-weight: 900; padding: 4px 8px; border: 2px solid #000; font-size: 0.875rem; color: #fff; }
    .item-rank.rank1 { background: var(--primary); transform: rotate(-6deg); }
    .item-rank.rank2 { background: var(--secondary); transform: rotate(3deg); }
    .item-rank.rank3 { background: var(--tertiary-container); color: #000; transform: rotate(-3deg); }
    .item-card h4 { font-weight: 900; font-size: 1.125rem; }
    .item-card p { color: var(--primary); font-weight: 700; }
    .no-data { font-weight: 700; color: var(--outline); font-style: italic; padding: 24px; }
    .customers-section { background: #fff; border: 4px solid #000; padding: 24px; }
    .customer-list { display: flex; flex-direction: column; gap: 12px; }
    .customer-row { display: flex; justify-content: space-between; align-items: center; background: var(--surface-container); border: 2px solid #000; padding: 16px; transition: background 0.2s; }
    .customer-row:hover { background: #8fc8ca; }
    .customer-row.active { background: var(--tertiary-container); border-left: 6px solid var(--primary); }
    .customer-info { display: flex; align-items: center; gap: 16px; }
    .customer-id { font-size: 0.75rem; font-weight: 900; color: var(--on-surface-variant); }
    .customer-name { font-weight: 900; font-size: 1.125rem; text-transform: uppercase; font-style: italic; }
    .customer-amount { font-weight: 900; color: var(--primary); }
    .metrics { display: flex; flex-direction: column; gap: 24px; }
    .metric-card { border: 4px solid #000; padding: 24px; position: relative; overflow: hidden; }
    .metric-card.teal-card { background: #a8d5d6; } .metric-card.yellow-card { background: #e8c84a; }
    .metric-card .halftone { position: absolute; inset: 0; opacity: 0.2; pointer-events: none; color: #000; }
    .metric-inner { position: relative; z-index: 1; }
    .metric-label { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: 700; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; }
    .metric-value { font-family: var(--font-headline); font-size: 4.5rem; font-weight: 900; font-style: italic; -webkit-text-stroke: 1.5px #000; line-height: 1; }
    .metric-sub { margin-top: 16px; display: flex; align-items: center; gap: 4px; font-weight: 700; color: #000; font-size: 0.875rem; }
    .hub-card { background: #fff; border: 4px solid #000; overflow: hidden; display: flex; flex-direction: column; }
    .hub-title { background: #000; color: #fff; padding: 16px; font-family: var(--font-headline); font-weight: 900; font-style: italic; text-transform: uppercase; letter-spacing: -0.05em; font-size: 1.25rem; }
    .hub-buttons { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
    .hub-btn { display: flex; align-items: center; justify-content: space-between; padding: 16px; border: 4px solid #000; cursor: pointer; background: transparent; font-family: var(--font-headline); font-weight: 900; text-transform: uppercase; letter-spacing: -0.03em; font-size: 1.125rem; transition: background 0.2s; width: 100%; text-align: left; text-decoration: none; color: #000; }
    .hub-btn .material-symbols-outlined { font-size: 1.875rem; transition: transform 0.2s; }
    .hub-btn:hover .material-symbols-outlined { transform: translateX(8px); }
    .hub-btn.btn-pink { background: var(--primary-container); } .hub-btn.btn-teal { background: var(--secondary-container); } .hub-btn.btn-yellow { background: var(--tertiary-container); } .hub-btn.btn-grey { background: var(--surface-variant); }
    .hub-btn.btn-grey:hover { background: #000; color: #fff; }
    .badge-count { background: var(--error); color: #fff; padding: 2px 8px; font-size: 0.75rem; font-weight: 900; margin-right: 8px; }
    .badge-count.pink-badge { background: var(--primary); }
    .hub-btn-right { display: flex; align-items: center; }
    footer { background: #000000; border-top: 4px solid #000000; padding: 20px 32px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .footer-brand { display: flex; flex-direction: column; gap: 4px; }
    .footer-brand-name { font-family: 'Epilogue', sans-serif; font-size: 1.5rem; font-weight: 900; font-style: italic; letter-spacing: -0.05em; color: #e8c84a; text-shadow: 3px 3px 0px #000; }
    .footer-rights { font-family: var(--font-body); font-weight: 700; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.5); }
    .footer-links { list-style: none; display: flex; gap: 20px; flex-wrap: wrap; }
    .footer-links li a { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.7rem; color: rgba(255,255,255,0.5); text-decoration: none; transition: color 0.15s; display: inline-block; }
    .footer-links li a:hover { color: var(--primary); }
    .footer-socials { display: flex; gap: 10px; }
    .social-icon { width: 36px; height: 36px; border: 2px solid rgba(255,255,255,0.3); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.7); cursor: pointer; transition: border-color 0.15s, color 0.15s, background 0.15s; text-decoration: none; }
    .social-icon:hover { border-color: var(--primary); color: #fff; background: rgba(183,0,72,0.2); }
    .social-icon .material-symbols-outlined { font-size: 1.1rem; }
    .material-symbols-outlined { font-family: 'Material Symbols Outlined'; font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 24px; line-height: 1; letter-spacing: normal; display: inline-block; vertical-align: middle; }
  </style>
</head>
<body>

<header>
  <div class="header-inner">
    <div class="header-left-group">
      <a href="dashboard.php" class="logo">Annyeong'Sayo</a>
      <nav>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="inventoryMng.php">Inventory</a>
        <a href="orderMng.php">Orders</a>
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
          <span class="dropdown-id">#<?php echo htmlspecialchars($user_id); ?></span>
        </div>
        <div class="dropdown-divider"></div>
        <a href="includes/logout.php" class="dropdown-logout">
          <span class="material-symbols-outlined">logout</span>
          Log Out
        </a>
      </div>
    </div>
  </div>
</header>

<main>
  <div class="main-left">

    <!-- Revenue -->
    <section class="revenue-section kinetic-shadow">
      <div class="revenue-header">
        <div>
          <h2>Revenue Stream</h2>
          <p class="subtitle">Completed Orders Only</p>
        </div>
        <div style="text-align:right;">
          <div class="revenue-amount">₱<?php echo number_format($total_revenue + $walkin_revenue, 2); ?></div>
          <div class="revenue-badge" style="gap:12px;">
            <span style="display:flex;align-items:center;gap:4px;">
              <span class="material-symbols-outlined">shopping_bag</span>
              Online: ₱<?php echo number_format($total_revenue, 2); ?>
            </span>
            <span style="display:flex;align-items:center;gap:4px;">
              <span class="material-symbols-outlined">storefront</span>
              Walk-in: ₱<?php echo number_format($walkin_revenue, 2); ?>
            </span>
          </div>
        </div>
      </div>
      <?php
        $c_online   = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM orders WHERE status='completed' AND is_walkin=0"))[0];
        $c_walkin   = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM orders WHERE status='completed' AND is_walkin=1"))[0];
        $c_pending  = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM orders WHERE status='pending'"))[0];
        $c_cancel   = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM orders WHERE status='cancelled'"))[0];
        $c_total    = ($c_online + $c_walkin + $c_pending + $c_cancel) ?: 1;
        $pct = fn($n) => round($n / $c_total * 100);
        $segs = [
          ['val'=>$c_online,  'pct'=>$pct($c_online),  'color'=>'var(--primary)',            'label'=>'Online Done'],
          ['val'=>$c_walkin,  'pct'=>$pct($c_walkin),  'color'=>'var(--tertiary-container)', 'label'=>'Walk-in Done'],
          ['val'=>$c_pending, 'pct'=>$pct($c_pending), 'color'=>'var(--secondary)',           'label'=>'Pending'],
          ['val'=>$c_cancel,  'pct'=>$pct($c_cancel),  'color'=>'var(--outline-variant)',     'label'=>'Cancelled'],
        ];
      ?>
      <div class="bar-chart-wrap" style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;height:auto;">
        <?php foreach ($segs as $s): ?>
        <div style="display:flex;align-items:center;gap:12px;">
          <span style="width:130px;flex-shrink:0;font-size:0.7rem;font-weight:900;text-transform:uppercase;letter-spacing:0.06em;color:var(--on-surface-variant);text-align:right;"><?php echo $s['label']; ?></span>
          <div style="flex:1;position:relative;height:32px;background:var(--surface-container-high);border:2px solid #000;">
            <div style="width:<?php echo max($s['pct'], 2); ?>%;height:100%;background:<?php echo $s['color']; ?>;border-right:<?php echo $s['pct'] > 0 ? '2px solid #000' : 'none'; ?>;transition:width 0.6s ease;"></div>
          </div>
          <span style="width:60px;flex-shrink:0;font-size:0.8rem;font-weight:900;color:var(--on-surface);"><?php echo $s['pct']; ?>% <span style="font-weight:500;color:var(--on-surface-variant);">(<?php echo $s['val']; ?>)</span></span>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:4px;padding-top:12px;border-top:2px dashed #000;font-size:0.7rem;font-weight:700;color:var(--on-surface-variant);text-transform:uppercase;letter-spacing:0.08em;">Total Orders: <strong style="color:var(--on-surface);"><?php echo $c_total; ?></strong></div>
      </div>
    </section>

    <!-- Top Selling Items -->
    <section class="items-section kinetic-shadow">
      <div class="section-header">
        <h3>Top Selling Items</h3>
        <div class="badge-heat">ALL TIME</div>
      </div>
      <div class="items-grid">
        <?php
        $ranks = ['rank1','rank2','rank3'];
        $i = 0;
        if ($top_products_result && mysqli_num_rows($top_products_result) > 0):
          while ($p = mysqli_fetch_assoc($top_products_result)):
            $rank = $ranks[$i] ?? 'rank3';
        ?>
        <div class="item-card">
          <div class="item-img-wrap">
            <?php if (!empty($p['image_url'])): ?>
            <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';this.nextElementSibling.style.display='block'"/>
            <span class="material-symbols-outlined" style="display:none">inventory_2</span>
            <?php else: ?>
            <span class="material-symbols-outlined">inventory_2</span>
            <?php endif; ?>
            <div class="item-rank <?php echo $rank; ?>">#<?php echo $i+1; ?></div>
          </div>
          <h4><?php echo htmlspecialchars($p['name']); ?></h4>
          <p>₱<?php echo number_format($p['price'], 2); ?></p>
        </div>
        <?php $i++; endwhile;
        else: ?>
        <p class="no-data">No sales data yet. Add products and process orders!</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Top Customers -->
    <section class="customers-section kinetic-shadow">
      <div class="section-header">
        <h3>Top Customers</h3>
        <div class="badge-heat">BY SPEND</div>
      </div>
      <div class="customer-list">
        <?php
        $first = true;
        if ($top_customers_result && mysqli_num_rows($top_customers_result) > 0):
          while ($c = mysqli_fetch_assoc($top_customers_result)):
        ?>
        <div class="customer-row <?php echo $first ? 'active' : ''; ?>">
          <div class="customer-info">
            <span class="customer-id">#ID-<?php echo str_pad($c['id'], 4, '0', STR_PAD_LEFT); ?></span>
            <span class="customer-name"><?php echo htmlspecialchars($c['username']); ?></span>
          </div>
          <span class="customer-amount">₱<?php echo number_format($c['total_spent'], 2); ?></span>
        </div>
        <?php $first = false; endwhile;
        else: ?>
        <p class="no-data">No completed orders yet.</p>
        <?php endif; ?>
      </div>
    </section>

  </div>

  <aside class="sidebar">
    <div class="metrics">
      <div class="metric-card teal-card kinetic-shadow">
        <div class="halftone"></div>
        <div class="metric-inner">
          <div class="metric-label">
            <span class="material-symbols-outlined">shopping_bag</span>
            ONLINE ORDERS
          </div>
          <div class="metric-value neon-stroke"><?php echo $total_orders; ?></div>
          <div class="metric-sub">
            <span class="material-symbols-outlined">pending</span>
            <?php echo $pending_orders; ?> PENDING
          </div>
        </div>
      </div>
      <div class="metric-card yellow-card kinetic-shadow">
        <div class="halftone"></div>
        <div class="metric-inner">
          <div class="metric-label">
            <span class="material-symbols-outlined">storefront</span>
            WALK-IN ORDERS
          </div>
          <div class="metric-value neon-stroke"><?php echo $total_walkin_orders; ?></div>
          <div class="metric-sub">
            <span class="material-symbols-outlined">payments</span>
            &#8369;<?php echo number_format($walkin_revenue, 2); ?> REVENUE
          </div>
        </div>
      </div>
      <div class="metric-card kinetic-shadow" style="background:#fff;">
        <div class="halftone"></div>
        <div class="metric-inner">
          <div class="metric-label">
            <span class="material-symbols-outlined">group</span>
            ONLINE CUSTOMERS
          </div>
          <div class="metric-value neon-stroke" style="font-size:3rem;"><?php echo $total_users; ?></div>
          <div class="metric-sub">
            <span class="material-symbols-outlined">inventory_2</span>
            <?php echo $total_products; ?> PRODUCTS LISTED
          </div>
        </div>
      </div>

    <div class="hub-card kinetic-shadow">
      <div class="hub-title">DASHBOARD HUB</div>
      <div class="hub-buttons">
        <a href="inventoryMng.php" class="hub-btn btn-pink">
          <span>Inventory</span>
          <span class="material-symbols-outlined">add_box</span>
        </a>
        <a href="orderMng.php" class="hub-btn btn-teal">
          <span>Orders</span>
          <div class="hub-btn-right">
            <span class="badge-count"><?php echo $pending_orders; ?></span>
            <span class="material-symbols-outlined">inventory_2</span>
          </div>
        </a>
        <a href="usersMng.php" class="hub-btn btn-yellow">
          <span>Users</span>
          <div class="hub-btn-right">
            <span class="badge-count pink-badge"><?php echo $total_users; ?></span>
            <span class="material-symbols-outlined">group</span>
          </div>
        </a>
        <a href="userDashboard.php" class="hub-btn btn-grey">
          <span>View Shop</span>
          <span class="material-symbols-outlined">storefront</span>
        </a>
      </div>
    </div>
  </aside>
</main>

<footer>
  <div class="footer-brand">
    <span class="footer-brand-name">Annyeong'Sayo</span>
    <span class="footer-rights">&copy; 2025 Annyeong Market.<br/>All rights reserved.</span>
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
  function toggleDropdown(e) {
    e.preventDefault();
    document.getElementById('profileDropdown').classList.toggle('open');
  }
  document.addEventListener('click', function(e) {
    var wrap = document.querySelector('.profile-trigger-wrap');
    if (wrap && !wrap.contains(e.target)) {
      document.getElementById('profileDropdown').classList.remove('open');
    }
  });
</script>
</body>
</html>