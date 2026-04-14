<?php
require_once 'includes/check_admin.php';
require_once 'includes/connect.php';

$username = $_SESSION['username'] ?? 'Admin';
$user_id  = $_SESSION['user_id']  ?? '00000';
$role     = $_SESSION['role']     ?? 'admin';

$msg = ''; $msg_type = '';

// ── DELETE user (cannot delete self or other admins) ──
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    if ($del_id == $_SESSION['user_id']) {
        $msg = '❌ You cannot delete your own account!';
        $msg_type = 'error';
    } else {
        $check = mysqli_fetch_assoc(mysqli_query($con, "SELECT role FROM users WHERE id=$del_id"));
        if ($check && $check['role'] === 'admin') {
            $msg = '❌ Cannot delete another admin account.';
            $msg_type = 'error';
        } else {
            mysqli_query($con, "DELETE FROM users WHERE id=$del_id");
            $msg = '🗑️ User deleted.';
            $msg_type = 'info';
        }
    }
}

// ── UPDATE role ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    $upd_id   = intval($_POST['user_id']);
    $new_role = mysqli_real_escape_string($con, $_POST['role']);
    if (in_array($new_role, ['admin','customer']) && $upd_id != $_SESSION['user_id']) {
        mysqli_query($con, "UPDATE users SET role='$new_role' WHERE id=$upd_id");
        $msg = '✅ User role updated!';
        $msg_type = 'success';
    }
}

// ── READ + SEARCH ──
$search     = isset($_GET['search'])  ? mysqli_real_escape_string($con, trim($_GET['search']))  : '';
$role_filter = isset($_GET['role_filter']) ? mysqli_real_escape_string($con, trim($_GET['role_filter'])) : '';

$where = "WHERE 1=1";
if ($search)      $where .= " AND (username LIKE '%$search%' OR email LIKE '%$search%')";
if ($role_filter) $where .= " AND role='$role_filter'";

$users_result = mysqli_query($con, "SELECT * FROM users $where ORDER BY created_at DESC");

$total_users = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM users WHERE role='customer'"))[0];
$total_admins = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM users WHERE role='admin'"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Users Management</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --primary: #b70048; --primary-container: #ff7290; --on-primary: #ffeff0;
    --secondary: #006668; --secondary-container: #52f9fc; --on-secondary-fixed: #004749; --on-secondary-container: #005b5d;
    --tertiary: #6c5a00; --tertiary-container: #fdd828; --on-tertiary-container: #5b4c00;
    --background: #f5f6f7; --surface: #f5f6f7; --on-background: #2c2f30; --on-surface: #2c2f30;
    --surface-container: #e6e8ea; --surface-container-low: #eff1f2; --surface-container-lowest: #fff;
    --surface-container-highest: #dadddf; --on-surface-variant: #595c5d;
    --outline: #757778; --error: #b31b25; --error-container: #fb5151; --on-error: #ffefee;
    --font-headline: 'Plus Jakarta Sans', 'Epilogue', sans-serif; --font-body: 'Plus Jakarta Sans', sans-serif;
  }
  body { background: var(--background); color: var(--on-background); font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: flex; flex-direction: column; background-image: radial-gradient(#000000 1px, transparent 0); background-size: 8px 8px; }
  .material-symbols-outlined { font-family: 'Material Symbols Outlined'; font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; font-size: 24px; line-height: 1; vertical-align: middle; user-select: none; display: inline-block; }
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
  main { flex-grow: 1; max-width: 1440px; margin: 0 auto; padding: 2.5rem; width: 100%; }
  h1.page-title { font-family: 'Epilogue', serif; font-size: clamp(3rem,8vw,5rem); font-weight: 900; font-style: italic; -webkit-text-stroke: 1px #000; color: var(--primary); text-shadow: 6px 6px 0px #000; text-transform: uppercase; line-height: 1; letter-spacing: -0.05em; margin-bottom: 2rem; }
  .metrics-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 1rem; margin-bottom: 2rem; }
  @media (min-width: 768px) { .metrics-grid { grid-template-columns: repeat(3,1fr); } }
  .metric-card { border: 4px solid #000; padding: 1.25rem; }
  .metric-card.teal { background: var(--secondary-container); }
  .metric-card.yellow { background: var(--tertiary-container); }
  .metric-card.pink { background: var(--primary-container); }
  .metric-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: rgba(0,0,0,0.6); margin-bottom: 0.5rem; }
  .metric-value { font-family: 'Epilogue', serif; font-size: 2.5rem; font-weight: 900; font-style: italic; -webkit-text-stroke: 1px #000; }
  .alert-msg { padding: 1rem; border: 2px solid #000; font-weight: 700; margin-bottom: 1.5rem; }
  .alert-msg.success { background: #d4edda; color: #1a5c2a; }
  .alert-msg.info    { background: #e2e3e5; color: #383d41; }
  .alert-msg.error   { background: #f8d7da; color: #721c24; }
  .alert-toast {position: fixed;top: 5rem;left: 50%;transform: translateX(-50%);z-index: 9999;padding: 1rem 1.5rem;border: 3px solid #000;box-shadow: 6px 6px 0px 0px #000;font-weight: 700;font-size: 0.95rem;max-width: 360px;width: max-content;opacity: 1;transition: opacity 0.5s ease;}
  .alert-toast.success { background: #d4edda; color: #1a5c2a; }
  .alert-toast.info    { background: #e2e3e5; color: #383d41; }
  .alert-toast.error   { background: #f8d7da; color: #721c24; }
  .search-bar { display: flex; flex-direction: row; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: nowrap; align-items: center; }
  .search-wrap { position: relative; flex-grow: 1; }
  .search-wrap input { width: 100%; background: #fff; border: 4px solid #000; padding: 0.75rem 0.75rem 0.75rem 3rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.875rem; outline: none; }
  .search-wrap input:focus { border-color: var(--primary); }
  .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); }
  .filter-wrap { position: relative; width: 220px; flex-shrink: 0; }
  .filter-wrap select { width: 100%; background: #fff; border: 4px solid #000; padding: 0.75rem 2.5rem 0.75rem 0.75rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.875rem; appearance: none; cursor: pointer; outline: none; }
  .filter-arrow { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; }
  .btn-search { background: #000; color: #fff; border: 4px solid #000; padding: 0.75rem 1.5rem; font-family: 'Epilogue', serif; font-weight: 900; font-size: 0.875rem; text-transform: uppercase; cursor: pointer; }
  .btn-search:hover { background: var(--primary); }
  .btn-clear { background: #fff; color: #000; border: 4px solid #000; padding: 0.75rem 1rem; font-weight: 700; cursor: pointer; text-decoration: none; font-size: 0.875rem; display: inline-block; }
  .table-wrap { background: #fff; border: 4px solid #000; overflow-x: auto; }
  .section-title { padding: 1.25rem 1.5rem; background: #000; color: #fff; font-family: 'Epilogue', serif; font-weight: 900; font-style: italic; font-size: 1.25rem; text-transform: uppercase; letter-spacing: -0.03em; }
  .search-wrap input:focus { border-color: #000; box-shadow: 0 0 0 4px #fdd828; }
  .filter-wrap select:focus { box-shadow: 0 0 0 4px #fdd828; }
  table { width: 100%; border-collapse: collapse; min-width: 600px; }
  thead tr { background: var(--surface-container-highest); border-bottom: 4px solid #000; }
  thead th { padding: 1rem; text-align: left; font-weight: 900; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
  tbody tr { border-bottom: 2px solid #000; transition: background 0.15s; }
  tbody tr:hover { background: var(--surface-container-low); }
  tbody td { padding: 1rem; font-size: 0.875rem; vertical-align: middle; }
  .user-name { font-weight: 900; }
  .user-email { font-size: 0.75rem; color: var(--on-surface-variant); font-weight: 700; }
  .user-id { font-size: 0.7rem; font-weight: 900; color: var(--on-surface-variant); }
  .badge { padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 900; border: 2px solid #000; display: inline-block; }
  .badge-admin { background: var(--primary); color: var(--on-primary); }
  .badge-customer { background: var(--secondary-container); color: var(--on-secondary-container); }
  .ts { font-size: 0.75rem; color: var(--on-surface-variant); font-weight: 700; }
  .action-cell { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }
  .role-form select { background: #fff; border: 2px solid #000; padding: 0.3rem 0.5rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.75rem; cursor: pointer; }
  .btn-update { background: var(--secondary); color: #fff; border: 2px solid #000; padding: 0.3rem 0.75rem; font-weight: 900; font-size: 0.75rem; cursor: pointer; white-space: nowrap; }
  .btn-update:hover { background: var(--on-secondary-fixed); }
  .btn-delete { background: var(--error-container); color: var(--on-error); border: 2px solid #000; padding: 0.3rem 0.75rem; font-weight: 900; font-size: 0.75rem; cursor: pointer; text-decoration: none; white-space: nowrap; display: inline-block; }
  .btn-delete:hover { background: var(--error); }
  .you-badge { background: var(--tertiary-container); color: var(--on-tertiary-container); font-size: 0.65rem; font-weight: 900; border: 1px solid #000; padding: 0.1rem 0.4rem; vertical-align: middle; }
  .no-users { padding: 3rem; text-align: center; font-weight: 700; color: var(--outline); font-style: italic; }
  footer { background: #000000; border-top: 4px solid #000000; padding: 20px 32px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .footer-brand { display: flex; flex-direction: column; gap: 4px; }
    .footer-brand-name { font-family: 'Epilogue', sans-serif; font-size: 1.5rem; font-weight: 900; font-style: italic; letter-spacing: -0.05em; color: #fdd828; text-shadow: 3px 3px 0px #000; }
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
        <a href="dashboard.php">Dashboard</a>
        <a href="inventoryMng.php">Inventory</a>
        <a href="orderMng.php">Orders</a>
        <a href="usersMng.php" class="active">Users</a>
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
  <h1 class="page-title">Users</h1>

  <?php if ($msg): ?>
  <div class="alert-toast <?php echo $msg_type; ?>" id="alertToast"><?php echo $msg; ?></div>
  <?php endif; ?>

  <div class="metrics-grid">
    <div class="metric-card teal">
      <div class="metric-label">Customers</div>
      <div class="metric-value"><?php echo $total_users; ?></div>
    </div>
    <div class="metric-card pink">
      <div class="metric-label">Admins</div>
      <div class="metric-value"><?php echo $total_admins; ?></div>
    </div>
    <div class="metric-card yellow">
      <div class="metric-label">Total Users</div>
      <div class="metric-value"><?php echo $total_users + $total_admins; ?></div>
    </div>
  </div>

  <form method="GET" action="usersMng.php" class="search-bar">
    <div class="search-wrap">
      <input type="text" name="search" placeholder="Search by username or email..." value="<?php echo htmlspecialchars($search); ?>"/>
      <span class="material-symbols-outlined search-icon">search</span>
    </div>
    <div class="filter-wrap">
     <select name="role_filter" onchange="this.form.submit()">
        <option value="">All Roles</option>
        <option value="customer" <?php echo $u['role']==='customer' ? 'selected':''; ?>>Customer</option>
        <option value="cashier"  <?php echo $u['role']==='cashier'  ? 'selected':''; ?>>Cashier</option>
        <option value="admin"    <?php echo $u['role']==='admin'    ? 'selected':''; ?>>Admin</option>
      </select>
      <span class="material-symbols-outlined filter-arrow">keyboard_arrow_down</span>
    </div>
    <?php if ($search): ?>
    <a href="usersMng.php" class="btn-clear">Clear</a>
    <?php endif; ?>
  </form>

  <div class="table-wrap">
    <div class="section-title">All Users</div>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Joined</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($users_result && mysqli_num_rows($users_result) > 0):
          while ($u = mysqli_fetch_assoc($users_result)):
        ?>
        <tr>
          <td class="user-id">#<?php echo str_pad($u['id'], 4, '0', STR_PAD_LEFT); ?></td>
          <td>
            <span class="user-name"><?php echo htmlspecialchars($u['username']); ?></span>
            <?php if ($u['id'] == $_SESSION['user_id']): ?>
            <span class="you-badge">YOU</span>
            <?php endif; ?>
          </td>
          <td class="user-email"><?php echo htmlspecialchars($u['email'] ?: '—'); ?></td>
          <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo strtoupper($u['role']); ?></span></td>
          <td class="ts"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
          <td>
            <div class="action-cell">
              <?php if ($u['id'] != $_SESSION['user_id']): ?>
              <form method="POST" action="usersMng.php" class="role-form" style="display:flex;gap:0.4rem;align-items:center;">
                <input type="hidden" name="action" value="update_role"/>
                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"/>
                <select name="role">
                  <option value="customer" <?php echo $u['role']==='customer' ? 'selected':''; ?>>Customer</option>
                  <option value="admin"    <?php echo $u['role']==='admin'    ? 'selected':''; ?>>Admin</option>
                </select>
                <button type="submit" class="btn-update">Update</button>
              </form>
              <a href="usersMng.php?delete=<?php echo $u['id']; ?>"
                 class="btn-delete"
                 onclick="return confirm('Delete user <?php echo addslashes($u['username']); ?>?')">
                Delete
              </a>
              <?php else: ?>
              <span style="font-size:0.75rem;font-weight:700;color:var(--outline);">Cannot edit self</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endwhile;
        else: ?>
        <tr><td colspan="6" class="no-users">No users found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
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
  function toggleDropdown(e) {
    e.preventDefault();
    document.getElementById('profileDropdown').classList.toggle('open');
  }
  document.addEventListener('click', function(e) {
    var wrap = document.querySelector('.profile-trigger-wrap');
    if (wrap && !wrap.contains(e.target)) document.getElementById('profileDropdown').classList.remove('open');
  });

  var toast = document.getElementById('alertToast');
  if (toast) {
    setTimeout(function() {
      toast.style.opacity = '0';
      setTimeout(function() { toast.style.display = 'none'; }, 500);
    }, 3000);
  }
</script>
</body>
</html>
