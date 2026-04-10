<?php
    session_start();
    $username = $_SESSION['username'] ?? 'Guest';
    $role = $_SESSION['role'] ?? 'Unknown';
    $user_id = $_SESSION['user_id'] ?? '00000';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,400;0,700;0,900;1,900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
  /* ── Reset & Base ── */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --surface: #f5f6f7;
    --surface-container: #e6e8ea;
    --surface-container-low: #eff1f2;
    --surface-container-high: #e0e3e4;
    --surface-container-highest: #dadddf;
    --surface-container-lowest: #ffffff;
    --surface-bright: #f5f6f7;
    --surface-dim: #d1d5d7;
    --surface-variant: #dadddf;
    --on-surface: #2c2f30;
    --on-surface-variant: #595c5d;
    --on-background: #2c2f30;
    --background: #f5f6f7;
    --primary: #b70048;
    --primary-dim: #a1003f;
    --primary-container: #ff7290;
    --primary-fixed: #ff7290;
    --primary-fixed-dim: #ff557f;
    --on-primary: #ffeff0;
    --on-primary-fixed: #000000;
    --on-primary-container: #4d001a;
    --on-primary-fixed-variant: #5f0022;
    --secondary: #006668;
    --secondary-dim: #00595b;
    --secondary-container: #52f9fc;
    --secondary-fixed: #52f9fc;
    --secondary-fixed-dim: #3ceaee;
    --on-secondary: #c0feff;
    --on-secondary-fixed: #004749;
    --on-secondary-container: #005b5d;
    --on-secondary-fixed-variant: #006668;
    --tertiary: #6c5a00;
    --tertiary-dim: #5e4f00;
    --tertiary-container: #fdd828;
    --tertiary-fixed: #fdd828;
    --tertiary-fixed-dim: #eeca12;
    --on-tertiary: #fff2cc;
    --on-tertiary-fixed: #453900;
    --on-tertiary-container: #5b4c00;
    --on-tertiary-fixed-variant: #665500;
    --error: #b31b25;
    --error-dim: #9f0519;
    --error-container: #fb5151;
    --on-error: #ffefee;
    --on-error-container: #570008;
    --outline: #757778;
    --outline-variant: #abadae;
    --inverse-surface: #0c0f10;
    --inverse-on-surface: #9b9d9e;
    --inverse-primary: #ff4e7c;
    --surface-tint: #b70048;
    --font-headline: 'Epilogue', sans-serif;
    --font-body: 'Plus Jakarta Sans', sans-serif;
  }

  body {
    background: var(--surface);
    font-family: var(--font-body);
    color: var(--on-surface);
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  /* ── Utility ── */
  .halftone {
    background-image: radial-gradient(circle, currentColor 1px, transparent 1px);
    background-size: 8px 8px;
  }
  .kinetic-shadow { box-shadow: 6px 6px 0 0 #000; }
  .neon-stroke { -webkit-text-stroke: 1.5px #000; }
  .stamp-effect {
    transform: rotate(-12deg);
    border: 4px double var(--primary);
    padding: 4px 8px;
    font-weight: 900;
    text-transform: uppercase;
  }

  /* ── Header ── */
  header {
  background: #ffffff;
  width: 100%;
  border-bottom: 4px solid #000000;
  position: relative;
  z-index: 50;
}

.header-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 1rem 2.5rem;
  max-width: 100%;
  margin: 0 auto;
}

.logo {
  font-family: 'Epilogue', serif;
  font-size: 1.875rem;
  font-weight: 900;
  font-style: italic;
  letter-spacing: -0.05em;
  color: #000000;
  text-shadow: 4px 4px 0px #fdd828;
  text-decoration: none;
  text-transform: none; /* Set to none as requested for webname */
  flex-shrink: 0;
}

.header-right-group {
  display: flex;
  align-items: center;
  gap: 3rem;
}

.header-left-group {
  display: flex;
  align-items: baseline; 
  gap: 3rem;
}

nav {
  display: flex;
  gap: 2rem;
  align-items: center;
  background: transparent !important; /* Overrides old nav background */
  border: none !important; /* Overrides old nav border */
  box-shadow: none !important; /* Overrides old nav shadow */
  padding: 0 !important;
}

nav a {
  font-family: 'Epilogue', serif;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: -0.05em;
  color: #000000;
  text-decoration: none;
  transition: color 0.15s, transform 0.15s;
  white-space: nowrap;
}

nav a:hover { 
  color: var(--primary); 
  transform: skewX(-2deg) translateY(-2px); 
  background: transparent !important; /* Removes old yellow hover box */
}

nav a.active {
  color: var(--primary);
  border-bottom: 4px solid var(--primary);
  padding-bottom: 0.25rem;
}

/* Stylized Profile Button */
.profile-trigger {
  width: 52px;
  height: 52px;
  background-color: var(--primary);
  border: 3px solid #000000;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.1s;
  box-shadow: 5px 5px 0px 0px #000000;
  text-decoration: none;
  flex-shrink: 0;
}

.profile-trigger:hover {
  transform: translate(2px, 2px);
  box-shadow: 3px 3px 0px 0px #000000;
}

.profile-trigger:active {
  transform: translate(5px, 5px);
  box-shadow: none;
}

.profile-trigger .material-symbols-outlined {
  color: #000000;
  font-variation-settings: 'FILL' 1, 'wght' 700, 'GRAD' 0, 'opsz' 48;
  font-size: 32px;
}
  /* ── Main Grid ── */
  main {
    flex-grow: 1;
    padding: 96px 24px 48px;
    max-width: 1440px;
    margin: 0 auto;
    width: 100%;
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 32px;
  }

  .main-left {
    grid-column: span 12;
    display: flex;
    flex-direction: column;
    gap: 32px;
  }
  .sidebar {
    grid-column: span 12;
    display: flex;
    flex-direction: column;
    gap: 32px;
  }

  @media (min-width: 1024px) {
    .main-left { grid-column: span 8; }
    .sidebar { grid-column: span 4; }
  }

  /* ── Section base ── */
  section, .card {
    background: #fff;
    border: 4px solid #000;
  }

  /* ── Revenue Stream ── */
  .revenue-section {
    padding: 24px;
    position: relative;
    overflow: hidden;
  }
  .revenue-stamp {
    position: absolute;
    top: 0; right: 0;
    padding: 16px;
    opacity: 0.2;
    pointer-events: none;
  }
  .revenue-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 32px;
    position: relative;
    z-index: 1;
  }
  .revenue-header h2 {
    font-family: var(--font-headline);
    font-size: 2.25rem;
    font-weight: 900;
    font-style: italic;
    text-transform: uppercase;
    letter-spacing: -0.05em;
    color: var(--on-background);
  }
  .revenue-header .subtitle {
    font-weight: 700;
    color: var(--secondary-dim);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 0.875rem;
  }
  .revenue-amount {
    font-size: 3rem;
    font-family: var(--font-headline);
    font-weight: 900;
    color: var(--primary);
  }
  .revenue-badge {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    background: var(--secondary-container);
    color: var(--on-secondary-container);
    padding: 4px 8px;
    margin-top: 8px;
    font-weight: 700;
    font-style: italic;
    border: 2px solid #000;
    font-size: 0.875rem;
    gap: 4px;
  }
  .revenue-badge .material-symbols-outlined { font-size: 1rem; }

  /* ── Bar Chart ── */
  .bar-chart-wrap {
    height: 256px;
    width: 100%;
    background: var(--surface-container-low);
    border: 2px solid #000;
    position: relative;
    overflow: hidden;
  }
  .bar-chart-wrap .halftone {
    position: absolute;
    inset: 0;
    opacity: 0.1;
    pointer-events: none;
    color: #000;
  }
  .bars {
    position: absolute;
    bottom: 0; left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: flex-end;
    padding: 0 16px 16px;
    gap: 8px;
  }
  .bar {
    flex: 1;
    border: 2px solid #000;
    transition: transform 0.3s;
    transform-origin: bottom;
  }
  .bar-chart-wrap:hover .bar { transform: scaleY(1.1); }
  .bar.pink  { background: var(--primary); }
  .bar.teal  { background: var(--secondary); }
  .bar.yellow{ background: var(--tertiary-container); }
  .gradient-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.05), transparent);
    pointer-events: none;
  }

  /* ── Top Selling Items ── */
  .items-section {
    background: var(--surface-container-lowest);
    border: 4px solid #000;
    padding: 24px;
  }
  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
  }
  .section-header h3 {
    font-family: var(--font-headline);
    font-size: 1.875rem;
    font-weight: 900;
    font-style: italic;
    text-transform: uppercase;
    letter-spacing: -0.05em;
  }
  .badge-heat {
    background: #000;
    color: #fff;
    padding: 4px 16px;
    font-weight: 700;
    transform: skewX(12deg);
    font-size: 0.875rem;
  }

  .items-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
  }
  @media (min-width: 640px) {
    .items-grid { grid-template-columns: repeat(3, 1fr); }
  }

  .item-card { cursor: pointer; }
  .item-img-wrap {
    aspect-ratio: 1;
    border: 4px solid #000;
    position: relative;
    overflow: hidden;
    margin-bottom: 12px;
  }
  .item-img-wrap img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
    display: block;
  }
  .item-card:hover .item-img-wrap img { transform: scale(1.1); }
  .item-rank {
    position: absolute;
    top: 8px; left: 8px;
    font-weight: 900;
    padding: 4px 8px;
    border: 2px solid #000;
    font-size: 0.875rem;
    color: #fff;
  }
  .item-rank.rank1 { background: var(--primary); transform: rotate(-6deg); }
  .item-rank.rank2 { background: var(--secondary); transform: rotate(3deg); }
  .item-rank.rank3 { background: var(--tertiary-container); color: #000; transform: rotate(-3deg); }
  .item-card h4 { font-weight: 900; font-size: 1.125rem; }
  .item-card p  { color: var(--primary); font-weight: 700; }

  /* ── Top Customers ── */
  .customers-section {
    background: #fff;
    border: 4px solid #000;
    padding: 24px;
  }
  .customer-list { display: flex; flex-direction: column; gap: 12px; }
  .customer-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--surface-container);
    border: 2px solid #000;
    padding: 16px;
    transition: background 0.2s;
  }
  .customer-row:hover { background: var(--tertiary-container); }
  .customer-info { display: flex; align-items: center; gap: 16px; }
  .customer-id { font-size: 0.75rem; font-weight: 900; color: var(--on-surface-variant); }
  .customer-name { font-weight: 900; font-size: 1.125rem; text-transform: uppercase; font-style: italic; }
  .customer-amount { font-weight: 900; color: var(--primary); }

  /* ── Sidebar Metric Cards ── */
  .metrics { display: flex; flex-direction: column; gap: 24px; }

  .metric-card {
    border: 4px solid #000;
    padding: 24px;
    position: relative;
    overflow: hidden;
  }
  .metric-card.teal-card { background: #52f9fc; }
  .metric-card.yellow-card { background: #fdd828; }

  .metric-card .halftone {
    position: absolute;
    inset: 0;
    opacity: 0.2;
    pointer-events: none;
    color: #000;
  }
  .metric-inner { position: relative; z-index: 1; }
  .metric-label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-weight: 700;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
  }
  .metric-label .material-symbols-outlined { font-weight: 900; }
  .metric-value {
    font-family: var(--font-headline);
    font-size: 4.5rem;
    font-weight: 900;
    font-style: italic;
    -webkit-text-stroke: 1.5px #000;
    line-height: 1;
  }
  .metric-sub {
    margin-top: 16px;
    display: flex;
    align-items: center;
    gap: 4px;
    font-weight: 700;
    color: #000;
    font-size: 0.875rem;
  }
  .metric-tag {
    position: absolute;
    top: -8px; right: -8px;
    background: #fff;
    border: 2px solid #000;
    padding: 4px 12px;
    font-weight: 900;
    font-size: 0.75rem;
    transform: rotate(12deg);
    box-shadow: 4px 4px 0 0 #000;
  }

  /* ── Dashboard Hub ── */
  .hub-card {
    background: #fff;
    border: 4px solid #000;
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }
  .hub-title {
    background: #000;
    color: #fff;
    padding: 16px;
    font-family: var(--font-headline);
    font-weight: 900;
    font-style: italic;
    text-transform: uppercase;
    letter-spacing: -0.05em;
    font-size: 1.25rem;
  }
  .hub-buttons { padding: 24px; display: flex; flex-direction: column; gap: 16px; }

  .hub-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    border: 4px solid #000;
    cursor: pointer;
    background: transparent;
    font-family: var(--font-headline);
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -0.03em;
    font-size: 1.125rem;
    transition: background 0.2s;
    width: 100%;
    text-align: left;
  }
  .hub-btn .material-symbols-outlined {
    font-size: 1.875rem;
    transition: transform 0.2s;
  }
  .hub-btn:hover .material-symbols-outlined { transform: translateX(8px); }
  .hub-btn.btn-pink   { background: var(--primary-container); }
  .hub-btn.btn-pink:hover { background: var(--primary-container); }
  .hub-btn.btn-teal   { background: var(--secondary-container); }
  .hub-btn.btn-yellow { background: var(--tertiary-container); }
  .hub-btn.btn-grey   { background: var(--surface-variant); }
  .hub-btn.btn-grey:hover { background: #000; color: #fff; }

  .badge-count {
    background: var(--error);
    color: #fff;
    padding: 2px 8px;
    font-size: 0.75rem;
    font-weight: 900;
    margin-right: 8px;
  }
  .badge-count.pink-badge { background: var(--primary); }
  .hub-btn-right { display: flex; align-items: center; }

  /* ── Promo Banner ── */
  .promo-wrap {
    padding: 16px;
    background: var(--surface-variant);
    border-top: 4px solid #000;
  }
  .promo-inner {
    transform: rotate(2deg);
    background: var(--primary-container);
    border: 4px solid #000;
    padding: 16px;
    position: relative;
    overflow: hidden;
    box-shadow: 6px 6px 0 0 #000;
    text-align: center;
  }
  .promo-inner .halftone {
    position: absolute; inset: 0; opacity: 0.1;
  }
  .promo-text {
    font-family: var(--font-headline);
    font-weight: 900;
    font-style: italic;
    text-transform: uppercase;
    line-height: 1;
    position: relative;
    z-index: 1;
  }
  .promo-text span { font-size: 1.875rem; display: block; }
  .promo-icon {
    position: absolute;
    right: -16px; bottom: -8px;
    transform: rotate(-12deg);
    opacity: 0.3;
    font-size: 3.75rem;
    color: var(--primary);
    font-family: 'Material Symbols Outlined';
    font-variation-settings: 'FILL' 1;
  }

  /* Profile dropdown wrapper */
  .profile-trigger-wrap {
    position: relative;
    flex-shrink: 0;
  }
  
  /* Dropdown panel */
.profile-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 12px);
    right: 0;
    background: #ffffff;
    border: 4px solid #000000;        
    box-shadow: 8px 8px 0px 0px #000000;  
    min-width: 220px;
    z-index: 999;
    overflow: visible;
    transform: rotate(3deg);       
}

.profile-dropdown.open {
    display: block;
}

/* User info block */
.dropdown-user-info {
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    background: var(--primary-container);
    border-bottom: 4px solid #000;
    background-image: radial-gradient(#00000018 1px, transparent 0);  /* ben-day dots */
    background-size: 6px 6px;
    background-color: var(--primary-container);
}

.dropdown-username {
    font-family: var(--font-headline);
    font-weight: 900;
    font-style: italic;
    font-size: 1.1rem;
    color: #000000;
    text-transform: uppercase;
    letter-spacing: -0.03em;
    -webkit-text-stroke: 0.5px #000;  
}

.dropdown-role {
    font-family: var(--font-body);
    font-weight: 700;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(0,0,0,0.6);
}

.dropdown-id {
    font-family: var(--font-body);
    font-weight: 700;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(0,0,0,0.4);
}

/* Divider */
.dropdown-divider {
    height: 0;
    border-top: 2px solid #000;
}

/* Logout button */
.dropdown-logout {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    font-family: var(--font-headline);
    font-weight: 900;
    font-style: italic;
    font-size: 0.9rem;
    text-transform: uppercase;
    color: #000000;
    text-decoration: none;
    background: var(--primary); 
    border-top: 2px solid #000;
    transition: background 0.1s, color 0.1s;
    letter-spacing: -0.02em;
}

.dropdown-logout:hover {
    background: var(--tertiary-container);
    color: #000;
}

.dropdown-logout .material-symbols-outlined {
    font-size: 1.1rem;
}

  /* ── Footer ── */
  footer {
    background-color: #000000;
    color: #ffffff;
    width: 100%;
    border-top: 4px solid #000000;
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    gap: 40px;
  }

@media (min-width: 768px) {
    footer {
        flex-direction: row;
        align-items: flex-start;
    }
}

.footer-brand {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer-brand-name {
    font-family: 'Epilogue', sans-serif;
    font-weight: 900;
    font-style: italic;
    font-size: 1.4rem;
    color: var(--tertiary-container); /* This is the yellow color */
    text-shadow: 2px 2px 0 #000000;
}

.footer-rights {
    font-size: 0.8rem;
    font-weight: 700;
    color: #888888;
    line-height: 1.4;
    text-transform: uppercase;
}

.footer-links {
    list-style: none;
    display: flex;
    flex-direction: row;
    gap: 24px;
    flex-wrap: wrap;
}

.footer-links a {
    color: #ffffff;
    text-decoration: none;
    font-weight: 800;
    font-size: 0.9rem;
    text-transform: uppercase;
    transition: color 0.2s;
}

.footer-links a:hover {
    color: var(--primary);
}

.footer-socials {
    display: flex;
    gap: 12px;
}

.social-icon {
    width: 48px;
    height: 48px;
    background-color: #ffffff;
    border: 3px solid #000000;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: #000000;
    box-shadow: 4px 4px 0px 0px #000000;
    transition: all 0.1s;
}

.social-icon:hover {
    transform: translate(2px, 2px);
    box-shadow: 2px 2px 0px 0px #000000;
    background-color: var(--primary-container);
}
</style>
</head>
<body>

<!-- Header -->
<header>
  <div class="header-inner">
    <div class="header-left-group">
      <a href="login_register.html" class="logo">WebName</a>
      <nav>
        <a href="dashboard.html" class="active">Dashboard</a>
        <a href="inventoryMng.html">Inventory</a>
        <a href="orderMng.html">Orders</a>
        <a href="usersMng.html">Users</a>
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

  <!-- Left Column -->
  <div class="main-left">

    <!-- Revenue Stream -->
    <section class="revenue-section kinetic-shadow">
      <div class="revenue-stamp">
        <div class="stamp-effect" style="color:var(--primary);font-size:1.5rem;">VERIFIED AUTHENTIC</div>
      </div>
      <div class="revenue-header">
        <div>
          <h2>Revenue Stream</h2>
          <p class="subtitle">Live Performance Tracking</p>
        </div>
        <div style="text-align:right;">
          <div class="revenue-amount">₩24,500,000</div>
          <div class="revenue-badge">
            <span class="material-symbols-outlined">trending_up</span> +12.4%
          </div>
        </div>
      </div>
      <div class="bar-chart-wrap">
        <div class="halftone"></div>
        <div class="bars">
          <div class="bar pink"  style="height:40%"></div>
          <div class="bar teal"  style="height:65%"></div>
          <div class="bar yellow"style="height:45%"></div>
          <div class="bar pink"  style="height:80%"></div>
          <div class="bar teal"  style="height:55%"></div>
          <div class="bar yellow"style="height:90%"></div>
          <div class="bar pink"  style="height:70%"></div>
          <div class="bar teal"  style="height:60%"></div>
        </div>
        <div class="gradient-overlay"></div>
      </div>
    </section>

    <!-- Top Selling Items -->
    <section class="items-section kinetic-shadow">
      <div class="section-header">
        <h3>Top Selling Items</h3>
        <div class="badge-heat">WEEKLY HEAT</div>
      </div>
      <div class="items-grid">
        <!-- Item 1 -->
        <div class="item-card">
          <div class="item-img-wrap">
            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDG2gtOjsB7ITE33ZKP5rXiGpia4n5Gf1ITpx3QTSOOYPf0JpSxR9gKDdaj2201AT5scDvWoizbImpp70_CTmUF_6yUX6VMRt60OdqZVPWpuIGUJgKGJ8-6lRiQmkAUEgz0KF9az71RYC8thSO8ycKQfm4k4ISgIYrl9AznkowCcfS8-d_PmAkDYKprvXz-ONSgPmkqVXqS_YJz1xjlzupHjwnYv0oEC2P-Ftz6KpKCDQZKp4ZMWiOzeHsEcscffx66PUigUYpuRyc" alt="vibrant neon colored limited edition vinyl toy"/>
            <div class="item-rank rank1">#1</div>
          </div>
          <h4>NEON GGO-MA</h4>
          <p>₩150,000</p>
        </div>
        <!-- Item 2 -->
        <div class="item-card">
          <div class="item-img-wrap">
            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDLSq48FaMLkFJh--jmou9w1vCIib8HjDf1NAA7vz_xuXe5hHkEURaMuqId6B_iTMQZKPN0boP2ktKyd8TsHV3Aj-iEz3N-gikrP7-N4G7qq2oJAtTzVlEinp3CuGsKtNKmnXKdm8LQEy1G74eyeB0ROMmXd8jBpPJty2fDAVXBhXep01cwLaPCRUye54Qj6-UcYa6YA3f5jW-c19d-Zwimr89oYll0DPSATg1PocVUQ88QVWFgO9KV80-XlbiTs35A66IdUQj8fEU" alt="stylized comic book style poster"/>
            <div class="item-rank rank2">#2</div>
          </div>
          <h4>CYBER SEOUL ZINE</h4>
          <p>₩45,000</p>
        </div>
        <!-- Item 3 -->
        <div class="item-card">
          <div class="item-img-wrap">
            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCdazpm1JfEZ4ycIn7NpiJWBNGAbJYNZrGj8WikYjr-NhsB83qbqupTgNxI7WjGG2i0edqaBUdkDGySm5d3yc8u2tNxch6sBLk5Vpx4nJgo_IlhbgWErhQFo9h8mlYDMaU3BXWV3N7Km1G4Vn2SryiG1XrYWCXjWRziE0PmWzatZkjSdhPCYWce-DcpECwaqoH306CSt6KtXtyvoWa16xg3MBmftuiruyhLDHnIKG6hyCu_QCcSmhBUSisXKlh3OBwN8CzEVU9sCXQ" alt="brightly colored designer stickers"/>
            <div class="item-rank rank3">#3</div>
          </div>
          <h4>HOLO-STICKER PACK</h4>
          <p>₩12,000</p>
        </div>
      </div>
    </section>

    <!-- Top Customers -->
    <section class="customers-section kinetic-shadow">
      <div class="section-header">
        <h3>Top Customers</h3>
        <div class="badge-heat">WEEKLY HEAT</div>
      </div>
      <div class="customer-list">
        <div class="customer-row">
          <div class="customer-info">
            <span class="customer-id">#ID-8821</span>
            <span class="customer-name">Alex Kim</span>
          </div>
          <span class="customer-amount">₩4,250,000</span>
        </div>
        <div class="customer-row">
          <div class="customer-info">
            <span class="customer-id">#ID-5502</span>
            <span class="customer-name">Sarah J.</span>
          </div>
          <span class="customer-amount">₩3,800,000</span>
        </div>
        <div class="customer-row">
          <div class="customer-info">
            <span class="customer-id">#ID-1194</span>
            <span class="customer-name">Leo Park</span>
          </div>
          <span class="customer-amount">₩2,940,000</span>
        </div>
        <div class="customer-row">
          <div class="customer-info">
            <span class="customer-id">#ID-0042</span>
            <span class="customer-name">Mina Choi</span>
          </div>
          <span class="customer-amount">₩2,100,000</span>
        </div>
      </div>
    </section>

  </div><!-- /main-left -->

  <!-- Sidebar -->
  <aside class="sidebar">

    <!-- Metric Cards -->
    <div class="metrics">
      <div class="metric-card teal-card kinetic-shadow">
        <div class="halftone"></div>
        <div class="metric-inner">
          <div class="metric-label">
            <span class="material-symbols-outlined">shopping_cart</span>
            TOTAL ORDERS
          </div>
          <div class="metric-value neon-stroke">158</div>
          <div class="metric-sub">
            <span class="material-symbols-outlined">trending_up</span>
            +12% THIS MONTH
          </div>
        </div>
        <div class="metric-tag">AUTHENTIC</div>
      </div>

      <div class="metric-card yellow-card kinetic-shadow">
        <div class="metric-label">
          <span class="material-symbols-outlined">group</span>
          TOTAL USERS
        </div>
        <div class="metric-value neon-stroke">24</div>
        <div class="metric-sub">
          <span class="material-symbols-outlined">favorite</span>
          DAEBAK! GROWING FAST
        </div>
      </div>
    </div>

    <!-- Dashboard Hub -->
    <div class="hub-card kinetic-shadow">
      <div class="hub-title">DASHBOARD HUB</div>
      <div class="hub-buttons">
        <button class="hub-btn btn-pink">
          <span>Add New Product</span>
          <span class="material-symbols-outlined">add_box</span>
        </button>
        <button class="hub-btn btn-teal">
          <span>Restock Requests</span>
          <div class="hub-btn-right">
            <span class="badge-count">5</span>
            <span class="material-symbols-outlined">inventory_2</span>
          </div>
        </button>
        <button class="hub-btn btn-yellow">
          <span>Fan Mail</span>
          <div class="hub-btn-right">
            <span class="badge-count pink-badge">12</span>
            <span class="material-symbols-outlined">mail</span>
          </div>
        </button>
        <button class="hub-btn btn-grey">
          <span>Shop Analytics</span>
          <span class="material-symbols-outlined">analytics</span>
        </button>
      </div>

      <!-- Promo Banner -->
      <div class="promo-wrap">
        <div class="promo-inner">
          <div class="halftone"></div>
          <p class="promo-text">Upgrade to<br/><span>PRO MAX</span></p>
          <span class="promo-icon">rocket_launch</span>
        </div>
      </div>
    </div>

  </aside>

</main>

<!-- Footer -->
<footer>
    <div class="footer-brand">
        <span class="footer-brand-name">WebName</span>
        <span class="footer-rights">© 2024 WebName Ltd.<br/>All rights reserved.</span>
    </div>

    <ul class="footer-links">
        <li><a href="dashboard.html">Dashboard</a></li>
        <li><a href="inventory.html">Inventory</a></li>
        <li><a href="orders.html">Orders</a></li>
        <li><a href="usersMng.html">Users</a></li>
    </ul>

    <div class="footer-socials">
        <a href="#" class="social-icon" title="Instagram">
            <span class="material-symbols-outlined">photo_camera</span>
        </a>
        <a href="#" class="social-icon" title="Twitter / X">
            <span class="material-symbols-outlined">alternate_email</span>
        </a>
        <a href="#" class="social-icon" title="YouTube">
            <span class="material-symbols-outlined">smart_display</span>
        </a>
        <a href="#" class="social-icon" title="TikTok">
            <span class="material-symbols-outlined">music_note</span>
        </a>
    </div>
</footer>
<script>
    function toggleDropdown(e) {
        e.preventDefault();
        document.getElementById('profileDropdown').classList.toggle('open');
      }
      
      /* close when clicking outside */
      document.addEventListener('click', function(e) {
        var wrap = document.querySelector('.profile-trigger-wrap');
        if (!wrap.contains(e.target)) {
            document.getElementById('profileDropdown').classList.remove('open');
          }
        });
    </script>

</body>
</html>