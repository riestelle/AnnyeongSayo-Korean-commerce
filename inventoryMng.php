<?php
require_once 'includes/check_admin.php';
require_once 'includes/connect.php';

$username = $_SESSION['username'] ?? 'Admin';
$user_id  = $_SESSION['user_id']  ?? '00000';
$role     = $_SESSION['role']     ?? 'admin';


$msg = '';
$msg_type = '';

// ── CREATE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name     = mysqli_real_escape_string($con, trim($_POST['name']));
    $desc     = mysqli_real_escape_string($con, trim($_POST['description']));
    $price    = floatval($_POST['price']);
    $stock    = intval($_POST['stock']);
    $category = mysqli_real_escape_string($con, trim($_POST['category']));
    $img      = mysqli_real_escape_string($con, trim($_POST['image_url']));

    if ($name && $price > 0 && $stock >= 0) {
        $sql = "INSERT INTO products (name, description, price, stock_quantity, category, image_url)
                VALUES ('$name','$desc',$price,$stock,'$category','$img')";
        if (mysqli_query($con, $sql)) {
            $msg = '✅ Product deployed successfully!';
            $msg_type = 'success';
        } else {
            $msg = '❌ Error: ' . mysqli_error($con);
            $msg_type = 'error';
        }
    } else {
        $msg = '❌ Please fill in all required fields.';
        $msg_type = 'error';
    }
}

// ── UPDATE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id       = intval($_POST['id']);
    $name     = mysqli_real_escape_string($con, trim($_POST['name']));
    $desc     = mysqli_real_escape_string($con, trim($_POST['description']));
    $price    = floatval($_POST['price']);
    $stock    = intval($_POST['stock']);
    $category = mysqli_real_escape_string($con, trim($_POST['category']));
    $img      = mysqli_real_escape_string($con, trim($_POST['image_url']));

    $sql = "UPDATE products SET name='$name', description='$desc', price=$price,
            stock_quantity=$stock, category='$category', image_url='$img'
            WHERE id=$id";
    if (mysqli_query($con, $sql)) {
        $msg = '✅ Product updated!';
        $msg_type = 'success';
    } else {
        $msg = '❌ Update failed: ' . mysqli_error($con);
        $msg_type = 'error';
    }
}

// ── DELETE ──
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($con, "DELETE FROM products WHERE id=$id");
    $msg = '🗑️ Product removed.';
    $msg_type = 'info';
}

// ── READ + SEARCH ──
$search   = isset($_GET['search'])   ? mysqli_real_escape_string($con, trim($_GET['search']))   : '';
$cat_filter = isset($_GET['category']) ? mysqli_real_escape_string($con, trim($_GET['category'])) : '';

$where = "WHERE 1=1";
if ($search)     $where .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
if ($cat_filter) $where .= " AND category = '$cat_filter'";

$products_result = mysqli_query($con, "SELECT * FROM products $where ORDER BY created_at DESC");
$total_items = mysqli_num_rows($products_result);

// Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = mysqli_query($con, "SELECT * FROM products WHERE id=$edit_id");
    if ($edit_result) $edit_product = mysqli_fetch_assoc($edit_result);
}

// Categories for filter
$categories_result = mysqli_query($con, "SELECT DISTINCT category FROM products WHERE category != '' ORDER BY category");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Inventory Management</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,900;1,900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --surface: #f5f6f7; --surface-container: #e6e8ea; --surface-container-low: #eff1f2;
    --surface-container-lowest: #ffffff; --surface-container-highest: #dadddf;
    --on-surface: #2c2f30; --on-surface-variant: #595c5d; --on-background: #2c2f30;
    --primary: #b70048; --primary-dim: #a1003f; --primary-container: #ff7290;
    --on-primary: #ffeff0; --on-primary-container: #4d001a;
    --secondary: #006668; --secondary-container: #52f9fc; --secondary-fixed: #52f9fc;
    --on-secondary-container: #005b5d; --on-secondary-fixed: #004749;
    --tertiary: #6c5a00; --tertiary-container: #fdd828; --on-tertiary-container: #5b4c00;
    --error: #b31b25; --error-container: #fb5151; --on-error: #ffefee;
    --outline: #757778; --outline-variant: #abadae;
    --font-headline: 'Epilogue', sans-serif; --font-body: 'Plus Jakarta Sans', sans-serif;
  }
  body { background-color: var(--surface); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--on-surface); min-height: 100vh; }
  .material-symbols-outlined { font-family: 'Material Symbols Outlined'; font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; font-size: 24px; line-height: 1; vertical-align: middle; user-select: none; display: inline-block; }
  .halftone-bg { background-image: radial-gradient(#000000 1px, transparent 0); background-size: 10px 10px; opacity: 0.05; position: absolute; inset: 0; pointer-events: none; }
  /* Header */
  header { background: #ffffff; width: 100%; border-bottom: 4px solid #000000; position: sticky; top: 0; z-index: 50; }
  .header-inner { display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 1rem 2.5rem; }
  .logo { font-family: 'Epilogue', serif; font-size: 1.875rem; font-weight: 900; font-style: italic; letter-spacing: -0.05em; color: #000000; text-shadow: 4px 4px 0px #fdd828; text-decoration: none; flex-shrink: 0; }
  .header-left-group { display: flex; align-items: baseline; gap: 3rem; }
  nav { display: flex; gap: 2rem; align-items: center; background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; }
  nav a { font-family: 'Epilogue', serif; font-weight: 900; text-transform: uppercase; letter-spacing: -0.05em; color: #000000; text-decoration: none; transition: color 0.15s, transform 0.15s; white-space: nowrap; }
  nav a:hover { color: var(--primary); transform: skewX(-2deg) translateY(-2px); }
  nav a.active { color: var(--primary); border-bottom: 4px solid var(--primary); padding-bottom: 0.25rem; }
  .profile-trigger-wrap { position: relative; flex-shrink: 0; }
  .profile-trigger { width: 52px; height: 52px; background-color: var(--primary); border: 3px solid #000000; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.1s; box-shadow: 5px 5px 0px 0px #000000; text-decoration: none; }
  .profile-trigger:hover { transform: translate(2px,2px); box-shadow: 3px 3px 0px 0px #000000; }
  .profile-trigger .material-symbols-outlined { color: #000000; font-variation-settings: 'FILL' 1,'wght' 700,'GRAD' 0,'opsz' 48; font-size: 32px; }
  .profile-dropdown { display: none; position: absolute; top: calc(100% + 12px); right: 0; background: #ffffff; border: 4px solid #000000; box-shadow: 8px 8px 0px 0px #000000; min-width: 220px; z-index: 999; transform: rotate(3deg); }
  .profile-dropdown.open { display: block; }
  .dropdown-user-info { padding: 16px 20px; display: flex; flex-direction: column; gap: 4px; background: var(--primary-container); border-bottom: 4px solid #000; }
  .dropdown-username { font-family: var(--font-headline); font-weight: 900; font-style: italic; font-size: 1.1rem; color: #000; text-transform: uppercase; }
  .dropdown-role { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: rgba(0,0,0,0.6); }
  .dropdown-logout { display: flex; align-items: center; gap: 10px; padding: 14px 20px; font-family: var(--font-headline); font-weight: 900; font-style: italic; font-size: 0.9rem; text-transform: uppercase; color: #000; text-decoration: none; background: var(--primary); transition: background 0.1s; }
  .dropdown-logout:hover { background: var(--tertiary-container); }
  /* Main */
  main { max-width: 1440px; margin: 0 auto; padding: 2.5rem; }
  h1.page-title { font-family: 'Epilogue', serif; font-size: clamp(3rem,8vw,5rem); font-weight: 900; font-style: italic; -webkit-text-stroke: 1px #000000; color: var(--primary); text-shadow: 6px 6px 0px #000000; text-transform: uppercase; line-height: 1; letter-spacing: -0.05em; margin-bottom: 2rem; }
  .main-grid { display: grid; grid-template-columns: 1fr; gap: 2.5rem; }
  @media (min-width: 1024px) { .main-grid { grid-template-columns: 7fr 5fr; } }
  /* Search */
  .search-filter { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
  .search-wrap { position: relative; flex-grow: 1; }
  .search-wrap input { width: 100%; background: #fff; border: 4px solid #000; padding: 0.75rem 0.75rem 0.75rem 3rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.875rem; outline: none; }
  .search-wrap input:focus { border-color: var(--primary); }
  .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); }
  .filter-wrap { position: relative; min-width: 180px; }
  .filter-wrap select { width: 100%; background: #fff; border: 4px solid #000; padding: 0.75rem 2.5rem 0.75rem 0.75rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.875rem; appearance: none; cursor: pointer; outline: none; }
  .filter-arrow { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; }
  .btn-search { background: #000; color: #fff; border: 4px solid #000; padding: 0.75rem 1.5rem; font-family: 'Epilogue', serif; font-weight: 900; font-size: 0.875rem; text-transform: uppercase; cursor: pointer; transition: background 0.15s; }
  .btn-search:hover { background: var(--primary); }
  .col-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 4px solid #000; padding-bottom: 1rem; margin-bottom: 1.5rem; }
  .col-header h2 { font-family: 'Epilogue', serif; font-size: 2rem; font-weight: 900; text-transform: uppercase; letter-spacing: -0.05em; }
  .col-header span { font-weight: 700; color: var(--secondary); font-size: 0.875rem; }
  /* Product Cards */
  .product-list { display: flex; flex-direction: column; gap: 1rem; }
  .product-card { background: var(--surface-container-lowest); border: 2px solid #000; padding: 1rem; display: flex; align-items: center; gap: 1rem; box-shadow: 4px 4px 0px 0px #000; transition: transform 0.15s, box-shadow 0.15s; position: relative; overflow: hidden; }
  .product-card:hover { transform: translate(2px,2px); box-shadow: none; }
  .product-thumb { width: 5rem; height: 5rem; border: 2px solid #000; flex-shrink: 0; display: flex; align-items: center; justify-content: center; overflow: hidden; background: var(--surface-container); }
  .product-thumb img { width: 100%; height: 100%; object-fit: cover; }
  .product-thumb .material-symbols-outlined { font-size: 2rem; color: var(--outline); }
  .product-info { flex-grow: 1; }
  .product-top { display: flex; justify-content: space-between; align-items: flex-start; }
  .product-top h3 { font-family: 'Epilogue', serif; font-weight: 900; font-size: 1.1rem; color: #000; }
  .product-top .category-tag { font-size: 0.7rem; font-weight: 700; color: var(--on-surface-variant); }
  .badge { padding: 0.125rem 0.5rem; font-size: 0.625rem; font-weight: 900; border: 2px solid #000; white-space: nowrap; }
  .badge-in { background: var(--primary); color: var(--on-primary); }
  .badge-low { background: var(--tertiary-container); color: var(--on-tertiary-container); }
  .badge-out { background: var(--error); color: var(--on-error); }
  .product-bottom { display: flex; align-items: flex-end; justify-content: space-between; margin-top: 0.5rem; }
  .product-stats { display: flex; gap: 1rem; }
  .stat-label { font-size: 0.625rem; text-transform: uppercase; font-weight: 700; color: var(--on-surface-variant); }
  .stat-val-good { font-weight: 900; color: var(--secondary); }
  .stat-val-bad  { font-weight: 900; color: var(--error); }
  .stat-val-norm { font-weight: 900; color: #000; }
  .action-btns { display: flex; gap: 0.5rem; }
  .action-btn { width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; background: var(--surface-container-highest); border: 2px solid #000; cursor: pointer; transition: background 0.15s; text-decoration: none; color: #000; }
  .action-btn:hover.edit-btn { background: var(--tertiary-container); }
  .action-btn.delete-btn:hover { background: var(--error-container); }
  .action-btn .material-symbols-outlined { font-size: 1rem; }
  .no-products { padding: 2rem; text-align: center; font-weight: 700; color: var(--outline); font-style: italic; border: 2px dashed var(--outline); }
  /* Add/Edit Form Panel */
  .quick-add-panel { background-color: var(--tertiary-container); border: 4px solid #000; padding: 2rem; box-shadow: 10px 10px 0px 0px #000; position: relative; }
  .panel-label { position: absolute; top: -1.5rem; left: 2rem; background: #000; color: #fff; border: 2px solid #000; padding: 0.5rem 1.5rem; transform: rotate(-1deg); }
  .panel-label h2 { font-family: 'Epilogue', serif; font-weight: 900; font-size: 1.25rem; text-transform: uppercase; letter-spacing: -0.05em; }
  .quick-add-form { margin-top: 2rem; }
  .form-group { margin-bottom: 1rem; }
  .form-group label { display: block; font-weight: 900; font-size: 0.875rem; text-transform: uppercase; margin-bottom: 0.25rem; }
  .form-group input, .form-group select, .form-group textarea { width: 100%; background: #fff; border: 2px solid #000; padding: 0.75rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.875rem; outline: none; transition: box-shadow 0.15s; }
  .form-group input:focus, .form-group select:focus, .form-group textarea:focus { box-shadow: 0 0 0 4px rgba(0,102,104,0.2); }
  .form-group textarea { resize: vertical; min-height: 80px; }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .btn-deploy { display: block; width: 100%; background: var(--primary); color: var(--on-primary); border: 2px solid #000; padding: 1.25rem; box-shadow: 6px 6px 0px 0px #000; cursor: pointer; transition: all 0.15s; margin-top: 1.5rem; }
  .btn-deploy:hover { transform: translate(3px,3px); box-shadow: none; }
  .btn-deploy span { font-family: 'Epilogue', serif; font-weight: 900; font-size: 1.5rem; text-transform: uppercase; font-style: italic; letter-spacing: 0.05em; -webkit-text-stroke: 1px #000; }
  .btn-cancel { display: block; width: 100%; background: #fff; color: #000; border: 2px solid #000; padding: 0.75rem; cursor: pointer; margin-top: 0.5rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.875rem; text-transform: uppercase; text-decoration: none; text-align: center; }
  .btn-cancel:hover { background: var(--surface-container); }
  /* Alert message */
  .alert-msg { padding: 1rem; border: 2px solid #000; font-weight: 700; margin-bottom: 1.5rem; }
  .alert-msg.success { background: var(--secondary-container); color: var(--on-secondary-fixed); }
  .alert-msg.error   { background: var(--error-container); color: var(--on-error); }
  .alert-msg.info    { background: var(--tertiary-container); color: var(--on-tertiary-container); }
  /* Edit mode panel */
  .panel-edit { background-color: var(--secondary-container) !important; }
  /* Footer */
  footer { background: #000; color: #fff; width: 100%; border-top: 4px solid #000; padding: 40px; display: flex; flex-direction: column; justify-content: space-between; align-items: center; gap: 24px; }
  @media (min-width: 768px) { footer { flex-direction: row; align-items: flex-start; } }
  .footer-brand-name { font-family: 'Epilogue', sans-serif; font-weight: 900; font-style: italic; font-size: 1.4rem; color: var(--tertiary-container); text-shadow: 2px 2px 0 #000; }
  .footer-rights { font-size: 0.8rem; font-weight: 700; color: #888; line-height: 1.4; text-transform: uppercase; margin-top: 8px; }
  .footer-links { list-style: none; display: flex; gap: 24px; flex-wrap: wrap; }
  .footer-links a { color: #fff; text-decoration: none; font-weight: 800; font-size: 0.9rem; text-transform: uppercase; transition: color 0.2s; }
  .footer-links a:hover { color: var(--primary); }
  .footer-socials { display: flex; gap: 12px; }
  .social-icon { width: 48px; height: 48px; background: #fff; border: 3px solid #000; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #000; box-shadow: 4px 4px 0px 0px #000; transition: all 0.1s; }
  .social-icon:hover { transform: translate(2px,2px); box-shadow: 2px 2px 0px 0px #000; background: var(--primary-container); }
</style>
</head>
<body>

<header>
  <div class="header-inner">
    <div class="header-left-group">
      <a href="dashboard.php" class="logo">Annyeong</a>
      <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="inventoryMng.php" class="active">Inventory</a>
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
        </div>
        <a href="includes/logout.php" class="dropdown-logout">
          <span class="material-symbols-outlined">logout</span> Log Out
        </a>
      </div>
    </div>
  </div>
</header>

<main>
  <h1 class="page-title">Inventory</h1>

  <?php if ($msg): ?>
  <div class="alert-msg <?php echo $msg_type; ?>"><?php echo $msg; ?></div>
  <?php endif; ?>

  <div class="main-grid">
    <!-- LEFT: Product List -->
    <div>
      <div class="col-header">
        <h2>All Products</h2>
        <span>Total: <?php echo $total_items; ?> items</span>
      </div>

      <!-- Search & Filter -->
      <form method="GET" action="inventoryMng.php" class="search-filter">
        <div class="search-wrap">
          <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>"/>
          <span class="material-symbols-outlined search-icon">search</span>
        </div>
        <div class="filter-wrap">
          <select name="category">
            <option value="">All Categories</option>
            <?php if ($categories_result): while ($cat = mysqli_fetch_assoc($categories_result)): ?>
            <option value="<?php echo htmlspecialchars($cat['category']); ?>"
              <?php echo ($cat_filter === $cat['category']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($cat['category']); ?>
            </option>
            <?php endwhile; endif; ?>
          </select>
          <span class="material-symbols-outlined filter-arrow">keyboard_arrow_down</span>
        </div>
        <button type="submit" class="btn-search">Filter</button>
        <?php if ($search || $cat_filter): ?>
        <a href="inventoryMng.php" class="btn-cancel" style="width:auto;margin:0;padding:0.75rem 1rem;">Clear</a>
        <?php endif; ?>
      </form>

      <!-- Product Cards -->
      <div class="product-list">
        <?php if ($products_result && mysqli_num_rows($products_result) > 0):
          while ($p = mysqli_fetch_assoc($products_result)):
            if ($p['stock_quantity'] > 20) { $stock_class = 'stat-val-good'; $badge_class = 'badge-in'; $badge_text = 'IN STOCK'; }
            elseif ($p['stock_quantity'] > 0) { $stock_class = 'stat-val-bad'; $badge_class = 'badge-low'; $badge_text = 'LOW STOCK'; }
            else { $stock_class = 'stat-val-bad'; $badge_class = 'badge-out'; $badge_text = 'OUT OF STOCK'; }
        ?>
        <div class="product-card">
          <div class="halftone-bg"></div>
          <div class="product-thumb">
            <?php if ($p['image_url']): ?>
            <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='block'"/>
            <span class="material-symbols-outlined" style="display:none">inventory_2</span>
            <?php else: ?>
            <span class="material-symbols-outlined">inventory_2</span>
            <?php endif; ?>
          </div>
          <div class="product-info">
            <div class="product-top">
              <div>
                <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                <p class="category-tag"><?php echo htmlspecialchars($p['category'] ?? 'Uncategorized'); ?></p>
              </div>
              <span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
            </div>
            <div class="product-bottom">
              <div class="product-stats">
                <div>
                  <p class="stat-label">Stock</p>
                  <p class="<?php echo $stock_class; ?>"><?php echo $p['stock_quantity']; ?> units</p>
                </div>
                <div>
                  <p class="stat-label">Price</p>
                  <p class="stat-val-norm">₱<?php echo number_format($p['price'], 2); ?></p>
                </div>
              </div>
              <div class="action-btns">
                <a href="inventoryMng.php?edit=<?php echo $p['id']; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="action-btn edit-btn" title="Edit">
                  <span class="material-symbols-outlined">edit</span>
                </a>
                <a href="inventoryMng.php?delete=<?php echo $p['id']; ?>" class="action-btn delete-btn"
                   onclick="return confirm('Delete <?php echo addslashes($p['name']); ?>?')" title="Delete">
                  <span class="material-symbols-outlined">delete</span>
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile;
        else: ?>
        <div class="no-products">No products found. Add your first product using the form!</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- RIGHT: Add / Edit Panel -->
    <div>
      <div class="quick-add-panel <?php echo $edit_product ? 'panel-edit' : ''; ?>">
        <div class="panel-label">
          <h2><?php echo $edit_product ? 'EDIT PRODUCT' : 'QUICK ADD PRODUCT'; ?></h2>
        </div>

        <form class="quick-add-form" method="POST" action="inventoryMng.php">
          <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit' : 'add'; ?>"/>
          <?php if ($edit_product): ?>
          <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>"/>
          <?php endif; ?>

          <div class="form-group">
            <label>Product Name *</label>
            <input type="text" name="name" placeholder="e.g. Buldak Ramen" required
              value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>"/>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Short product description..."><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Category</label>
              <select name="category">
                <option value="Snacks"     <?php echo ($edit_product && $edit_product['category']==='Snacks')     ? 'selected':'' ?>>Snacks</option>
                <option value="Drinks"     <?php echo ($edit_product && $edit_product['category']==='Drinks')     ? 'selected':'' ?>>Drinks</option>
                <option value="Instant Food" <?php echo ($edit_product && $edit_product['category']==='Instant Food') ? 'selected':'' ?>>Instant Food</option>
                <option value="Sweets"     <?php echo ($edit_product && $edit_product['category']==='Sweets')     ? 'selected':'' ?>>Sweets</option>
                <option value="Beauty"     <?php echo ($edit_product && $edit_product['category']==='Beauty')     ? 'selected':'' ?>>Beauty</option>
                <option value="Other"      <?php echo ($edit_product && $edit_product['category']==='Other')      ? 'selected':'' ?>>Other</option>
              </select>
            </div>
            <div class="form-group">
              <label>Price (₱) *</label>
              <input type="number" name="price" step="0.01" min="0" placeholder="0.00" required
                value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>"/>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Stock Qty *</label>
              <input type="number" name="stock" min="0" placeholder="0" required
                value="<?php echo $edit_product ? $edit_product['stock_quantity'] : ''; ?>"/>
            </div>
            <div class="form-group">
              <label>Image URL</label>
              <input type="text" name="image_url" id="img_url_input" placeholder="https://..."
                value="<?php echo $edit_product ? htmlspecialchars($edit_product['image_url']) : ''; ?>"/>
            </div>
          </div>

          <button class="btn-deploy" type="submit">
            <span><?php echo $edit_product ? 'UPDATE ITEM' : 'DEPLOY ITEM'; ?></span>
          </button>
          <?php if ($edit_product): ?>
          <a href="inventoryMng.php" class="btn-cancel">Cancel Edit</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</main>

<footer>
  <div class="footer-brand">
    <span class="footer-brand-name">Annyeong</span>
    <span class="footer-rights">© 2025 Annyeong Market. All rights reserved.</span>
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
    if (wrap && !wrap.contains(e.target)) document.getElementById('profileDropdown').classList.remove('open');
  });


</script>
</body>
</html>