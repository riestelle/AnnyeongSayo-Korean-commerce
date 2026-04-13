<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>My Orders</title>
<link href="https://fonts.googleapis.com/css2?family=Bangers&family=Epilogue:ital,wght@0,900;1,900&family=Noto+Sans+KR:wght@700;900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
  /* ── Reset ── */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  /* ── Variables ── */
  :root {
    --primary:                #b70048;
    --primary-dim:            #a1003f;
    --primary-container:      #ff7290;
    --secondary:              #006668;
    --secondary-container:    #52f9fc;
    --tertiary-fixed:         #fdd828;
    --tertiary-container:     #fdd828;
    --surface:                #f5f6f7;
    --surface-container-low:  #eff1f2;
    --surface-container-lowest: #ffffff;
    --on-surface:             #2c2f30;
    --on-surface-variant:     #595c5d;
    --background:             #f5f6f7;
    --outline:                #757778;
    --error:                  #b31b25;
  }

  /* ── Base ── */
  body {
    background-color: var(--background);
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--on-surface);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-image: radial-gradient(#000000 1px, transparent 0); background-size: 8px 8px; 
  }

  .material-symbols-outlined {
    font-family: 'Material Symbols Outlined';
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    font-size: 24px;
    line-height: 1;
    display: inline-block;
    vertical-align: middle;
    user-select: none;
  }

  /* ── Utilities ── */
  .halftone-overlay {
    background-image: radial-gradient(circle, #000 1px, transparent 1px);
    background-size: 4px 4px;
    opacity: 0.1;
  }
  .comic-stroke   { -webkit-text-stroke: 1.5px #000000; }
  .kinetic-shadow { box-shadow: 6px 6px 0px 0px #000000; }
  .sticker-rotate-pos { transform: rotate(2deg); }
  .sticker-rotate-neg { transform: rotate(-2deg); }

 header { background: #ffffff; width: 100%; border-bottom: 4px solid #000000; position: sticky; top: 0; z-index: 50; }
    .header-inner { display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 1rem 2.5rem; }
    .logo { font-family: 'Epilogue', serif; font-size: 1.875rem; font-weight: 900; font-style: italic; letter-spacing: -0.05em; color: #000000; text-shadow: 4px 4px 0px #fdd828; text-decoration: none; flex-shrink: 0; }
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
    .dropdown-user-info { padding: 16px 20px; display: flex; flex-direction: column; gap: 4px; background: var(--primary-container); border-bottom: 4px solid #000; background-image: radial-gradient(#00000018 1px, transparent 0); background-size: 6px 6px; }
    .dropdown-username { font-family: var(--font-headline); font-weight: 900; font-style: italic; font-size: 1.1rem; color: #000000; text-transform: uppercase; letter-spacing: -0.03em; }
    .dropdown-role { font-family: var(--font-body); font-weight: 700; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(0,0,0,0.6); }
    .dropdown-id { font-family: var(--font-body); font-weight: 700; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(0,0,0,0.4); }
    .dropdown-divider { height: 0; border-top: 2px solid #000; }
    .dropdown-logout { display: flex; align-items: center; gap: 10px; padding: 14px 20px; font-family: var(--font-headline); font-weight: 900; font-style: italic; font-size: 0.9rem; text-transform: uppercase; color: #000000; text-decoration: none; background: var(--primary); border-top: 2px solid #000; transition: background 0.1s, color 0.1s; letter-spacing: -0.02em; }
    .dropdown-logout:hover { background: var(--tertiary-container); color: #000; }

  /* ── Main ── */
  main {
    flex-grow: 1;
    max-width: 80rem;
    margin: 0 auto;
    width: 100%;
    padding: 1.5rem 1.5rem;
  }

  /* ── Hero Header ── */
  .hero-header { margin-bottom: 1.5rem; position: relative; }

  .hero-header h1 {
    font-family: 'Epilogue', sans-serif;
    font-weight: 900;
    font-size: clamp(3rem, 7vw, 5rem);
    text-transform: uppercase;
    letter-spacing: -0.05em;
    color: var(--primary);
    -webkit-text-stroke: 1.5px #000000;
    text-shadow: 5px 5px 0px #fdd828;
    line-height: 1;
  }

  .hero-subtitle {
    margin-top: 0.5rem;
    display: inline-block;
    background: var(--secondary);
    padding: 0.3rem 1rem;
    border: 3px solid #000000;
    box-shadow: 4px 4px 0px 0px #000000;
    transform: rotate(-2deg);
  }
  .hero-subtitle p {
    font-family: 'Epilogue', sans-serif;
    font-weight: 700;
    color: #ffffff;
    text-transform: uppercase;
    font-size: 0.85rem;
  }

  /* ── Filter Tabs ── */
  .filter-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    margin-bottom: 1.5rem;
    justify-content: flex-end;
  }

  .filter-tab {
    padding: 0.5rem 1.25rem;
    border: 3px solid #000000;
    box-shadow: 4px 4px 0px 0px #000000;
    font-family: 'Epilogue', sans-serif;
    font-weight: 900;
    text-transform: uppercase;
    background: #ffffff;
    cursor: pointer;
    transition: transform 0.1s, box-shadow 0.1s;
    letter-spacing: 0.02em;
    font-size: 0.8rem;
  }
  .filter-tab:hover { transform: translateY(2px); box-shadow: 2px 2px 0px 0px #000000; }
  .filter-tab.active { background: var(--tertiary-container); }

  /* ── Card Grid ── */
  .card-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
  }
  @media (min-width: 768px) {
    .card-grid { grid-template-columns: repeat(2, 1fr); }
  }

  /* ── Order Card ── */
  .order-card {
    position: relative;
    background: var(--surface-container-lowest);
    border: 4px solid #000000;
    box-shadow: 6px 6px 0px 0px #000000;
    overflow: visible;
    display: flex;
    flex-direction: column;
  }
  @media (min-width: 768px) {
    .order-card { flex-direction: row; }
  }

  .order-card:hover .card-img { transform: scale(1.1); }
  .order-card:hover .card-title { color: var(--primary); }

  .card-halftone { display: none; }

  /* ── Card Image ── */
  .card-image-wrap {
    width: 100%;
    flex-shrink: 0;
    position: relative;
    padding: 0.65rem;
    background: #fff;
    border-bottom: 4px solid #000000;
    display: flex;
    align-items: stretch;
  }
  @media (min-width: 768px) {
    .card-image-wrap {
      width: 38%;
      border-bottom: none;
      border-right: 4px solid #000000;
    }
  }

  .card-img-frame {
    width: 100%;
    height: 9rem;
    border: 3px solid #000000;
    box-shadow: 4px 4px 0px 0px #000000;
    overflow: hidden;
    flex-shrink: 0;
  }
  @media (min-width: 768px) {
    .card-img-frame { height: 100%; min-height: 7rem; }
  }

  .card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
  }

  /* ── Status Tag Badge ── */
.card-status-badge {
  position: absolute;
  top: -1.3rem;
  right: -1.6rem;
  padding: 0.35rem 0.75rem 0.35rem 0.6rem;
  border: 3px solid #000000;
  font-weight: 900;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-family: 'Epilogue', sans-serif;
  z-index: 10;
  box-shadow: 3px 3px 0px 0px #000;
  transform: rotate(15deg);
}
.card-status-badge::before { display: none; }
.card-status-badge::after {
  content: '';
  display: block;
  position: absolute;
  right: -1px;
  bottom: -8px;
  width: 0;
  height: 0;
  border-left: 8px solid #000;
  border-bottom: 8px solid transparent;
}
.badge-transit {
  background: #3b82f6;
  color: #ffffff;
}
.badge-delivered {
  background: #fdd828;
  color: #000000;
}
.badge-to-pay {
  background: #b70048;
  color: #ffffff;
}
.badge-to-receive {
  background: #006668;
  color: #ffffff;
}
.badge-completed {
  background: #16a34a;
  color: #ffffff;
}
  /* ── Card Body ── */
  .card-body {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex-grow: 1;
    position: relative;
    z-index: 1;
  }

  .card-meta {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.35rem;
  }

  .card-order-num {
    font-family: 'Epilogue', sans-serif;
    font-weight: 900;
    font-size: 0.7rem;
    color: var(--secondary);
  }

  .card-price {
    font-family: 'Epilogue', sans-serif;
    font-weight: 900;
    font-size: 1.1rem;
    color: #000000;
  }

  .card-title {
    font-family: 'Epilogue', sans-serif;
    font-weight: 900;
    font-size: 1.1rem;
    text-transform: uppercase;
    line-height: 1;
    margin-bottom: 0.5rem;
    transition: color 0.15s;
  }

  .card-status-text {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--on-surface-variant);
    font-style: italic;
  }

  /* ── Card Buttons ── */
  .card-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
  }

  .btn-primary {
    flex-grow: 1;
    background: var(--primary);
    color: #ffffff;
    border: 2px solid #000000;
    box-shadow: 4px 4px 0px 0px #000000;
    padding: 0.5rem 0.75rem;
    font-family: 'Epilogue', sans-serif;
    font-weight: 900;
    text-transform: uppercase;
    font-size: 0.72rem;
    cursor: pointer;
    transition: transform 0.1s, box-shadow 0.1s, background 0.1s;
    letter-spacing: 0.02em;
  }
  .btn-primary:hover { transform: translate(2px, 2px); box-shadow: 2px 2px 0px 0px #000000; }
  .btn-primary:active { background: var(--primary-dim); box-shadow: none; transform: translate(4px, 4px); }

  .btn-secondary {
    background: #ffffff;
    color: #000000;
    border: 2px solid #000000;
    box-shadow: 4px 4px 0px 0px #000000;
    padding: 0.5rem 1rem;
    font-family: 'Epilogue', sans-serif;
    font-weight: 900;
    text-transform: uppercase;
    font-size: 0.72rem;
    cursor: pointer;
    transition: transform 0.1s, box-shadow 0.1s;
    letter-spacing: 0.02em;
  }
  .btn-secondary:hover { transform: translate(2px, 2px); box-shadow: 2px 2px 0px 0px #000000; }
  .btn-secondary:active { box-shadow: none; transform: translate(4px, 4px); }

  .rate-modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.75);
  z-index: 2000;
  align-items: center;
  justify-content: center;
}
.rate-modal-overlay.open { display: flex; }
.rate-modal-box {
  background: #fff;
  border: 4px solid #000;
  box-shadow: 10px 10px 0px 0px #000;
  padding: 2rem;
  max-width: 420px;
  width: 90%;
  position: relative;
  text-align: center;
}
.rate-modal-close {
  position: absolute;
  top: -12px;
  right: -12px;
  background: #000;
  color: #fff;
  border: none;
  width: 32px;
  height: 32px;
  cursor: pointer;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}
.rate-modal-close:hover { background: var(--primary); }
.rate-modal-title {
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  font-size: 1.25rem;
  text-transform: uppercase;
  letter-spacing: -0.03em;
  margin-bottom: 0.25rem;
}
.rate-modal-product {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--on-surface-variant);
  text-transform: uppercase;
  margin-bottom: 1.5rem;
}
.star-row {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}
.star {
  font-size: 2.5rem;
  cursor: pointer;
  color: #ddd;
  transition: color 0.15s, transform 0.1s;
  line-height: 1;
}
.star:hover,
.star.active { color: #fdd828; transform: scale(1.2); }
.rate-submit-btn {
  width: 100%;
  background: var(--primary);
  color: #fff;
  border: 3px solid #000;
  box-shadow: 5px 5px 0px 0px #000;
  padding: 0.75rem;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  font-size: 1rem;
  text-transform: uppercase;
  cursor: pointer;
  transition: transform 0.1s, box-shadow 0.1s;
}
.rate-submit-btn:active { transform: translate(2px,2px); box-shadow: none; }

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
      <a href="login_register.php" class="logo">Annyeong'Sayo</a>
      <nav>
        <a href="userDashboard.php">Dashboard</a>
        <a href="wishlistCart.php">Wishlist</a>
        <a href="myOrders.php" class="active">My Orders</a>
      </nav>
    </div>
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <a href="wishlistCart.php" class="profile-trigger" style="background-color: #00595b;">
        <span class="material-symbols-outlined" style="color: #ffffff;">shopping_cart</span>
      </a>
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
  </div>
</header>

<!-- ── Main ── -->
<main>

  <!-- Hero Header -->
  <div class="hero-header">
    <h1 class="comic-stroke">ORDER ARCHIVE</h1>
    <div class="hero-subtitle">
      <p>My Order History</p>
    </div>
  </div>

  <!-- Filter Tabs -->
  <div class="filter-tabs">
    <button class="filter-tab active" data-filter="all">ALL</button>
    <button class="filter-tab" data-filter="to_pay">TO PAY</button>
    <button class="filter-tab" data-filter="to_receive">TO RECEIVE</button>
    <button class="filter-tab" data-filter="completed">COMPLETED</button>
  </div>

  <!-- Card Grid -->
  <div class="card-grid">

    <!-- Card 1 -->
    <div class="order-card" data-status="to_receive">
      <div class="card-halftone halftone-overlay"></div>
      <div class="card-status-badge badge-transit">IN TRANSIT</div>
      <div class="card-image-wrap">
        <div class="card-img-frame">
          <img class="card-img" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC_-ZcF3ZsHlBwfuvMZA9XkHlgqwkr6zS8FPjEeGliLmaq9p4qBQ2B0xwc5ZqCUHcOyGFlFdTuJqLLRvnnH0VPU3rY_Yf_U_vZoxW80p919PhuLakwNN16EWpKdo5siSmIrO7gLYAL4h8pk7MVORyRBuFKu7USfRKUf7jfmfagMuOw2BrzeVKSSRuELlIbHxdC6q6UqPsSyP3ClJ8uf6usGAVKoaJIjVdR4BSvwT3kALeYjs0L5uR3rp75CeLcnzxBq2fP4KQ73B3I" alt="Solo Leveling Vol 7 Box Set"/>
        </div>
      </div>
      <div class="card-body">
        <div>
          <div class="card-meta">
            <span class="card-order-num">ORDER #MM-9921</span>
            <span class="card-price">$124.50</span>
          </div>
          <h3 class="card-title">SOLO LEVELING: VOL 7 BOX SET</h3>
          <p class="card-status-text">Arriving by Thursday, June 20</p>
        </div>
        <div class="card-actions">
            <a href="productD.php?id=1&autoopen=1" class="btn-primary" style="text-decoration:none;text-align:center;">BUY AGAIN</a>
            <button class="btn-secondary" onclick="openRateModal('SOLO LEVELING: VOL 7 BOX SET')">RATE</button>
        </div>
      </div>
    </div>

    <!-- Card 2 -->
    <div class="order-card" data-status="completed">
      <div class="card-halftone halftone-overlay"></div>
      <div class="card-status-badge badge-delivered">DELIVERED</div>
      <div class="card-image-wrap">
        <div class="card-img-frame">
          <img class="card-img" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCToquLjuH85rVrJDzbm2JpyhCSMv30q6y5nOWqWN7T7ZbQiaNqUbsl05tvDNoqCU_lVG5Xo2W8GeGTmk-DoV5LUkKQ8D3W9P2-Ippwn3qLx7SyGRVCP0mnJrXkFn-0roR864cvDVtGfcQC4hbmBuI4K3EJPDCDD6YDDM5vhKTI94ikoj68NBQeqxK1mBhLLBdhKBT7lAIgLCeX_JMdtG7uTMnjtcccqOuSumwyFTGLDb8qSTTcT6_oTXscpBDycJwyEnVBd2p42Bg" alt="Omniscient Reader Vol 1-3"/>
        </div>
      </div>
      <div class="card-body">
        <div>
          <div class="card-meta">
            <span class="card-order-num">ORDER #MM-8842</span>
            <span class="card-price">$89.99</span>
          </div>
          <h3 class="card-title">OMNISCIENT READER: VOL 1-3</h3>
          <p class="card-status-text">Package dropped at side porch.</p>
        </div>
        <div class="card-actions">
            <a href="productD.php?id=2&autoopen=1" class="btn-primary" style="text-decoration:none;text-align:center;">BUY AGAIN</a>
            <button class="btn-secondary" onclick="openRateModal('OMNISCIENT READER: VOL 1-3')">RATE</button>
        </div>
      </div>
    </div>

    <!-- Card 3 -->
    <div class="order-card" data-status="to_receive">
      <div class="card-halftone halftone-overlay"></div>
      <div class="card-status-badge badge-to-pay">TO PAY</div>
      <div class="card-image-wrap">
        <div class="card-img-frame">
          <img class="card-img" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB7Q-0fnMZvTn9_jlkmQCgXU9LCK0OhNOZ8G50Y8GkG-1Fyb4HS5ekEohx3lJZtLdHvi0T8vyCFJ09kOd-fmL8BMlbBGjBkEfo6aeVIuRdvTn1bdO1fzgbsfn2yEl-v6AZ-IsIXgldpU9zGBnGJ7AVy6vQCxdHi3W4-CJ1ZMW3hOTrkU-ozo1eXBU5zY85E9HJ1hKewYRrioblgUjXCmAPMIldOK1Wz8dfLOi4crhqgAy2qyxxCEmyM6mXY_Gd2MjvrYdgn3-CXP8Q" alt="Tower of God Bam Figure"/>
        </div>
      </div>
      <div class="card-body">
        <div>
          <div class="card-meta">
            <span class="card-order-num">ORDER #MM-7721</span>
            <span class="card-price">$45.00</span>
          </div>
          <h3 class="card-title">TOWER OF GOD: BAM FIGURE</h3>
          <p class="card-status-text">Out for delivery today!</p>
        </div>
        <div class="card-actions">
            <a href="productD.php?id=3&autoopen=1" class="btn-primary" style="text-decoration:none;text-align:center;">BUY AGAIN</a>
            <button class="btn-secondary" onclick="openRateModal('TOWER OF GOD: BAM FIGURE')">RATE</button>
        </div>
      </div>
    </div>

    <!-- Card 4 -->
    <div class="order-card" data-status="completed">
      <div class="card-halftone halftone-overlay"></div>
      <div class="card-status-badge badge-to-receive">TO RECEIVE</div>
      <div class="card-image-wrap">
        <div class="card-img-frame">
          <img class="card-img" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB9PQxJcaoDq3ZSXu1gVRPSletwod6sDmHkZBxt1YU8AHxg4Y0fdNrrBzlUatzX09fESK13wsrNUYNgJtfineQVDsqYDQwNkihUGK7zOqAXyis76EH0hXW7XZuOg5Sqx3Cy0aiKH2gWy9gLaUYprtyB3IoDcF4R5GEhagHwn_iso4I48St3u8ANYrlegMDP0I1Ei_K1OVTBtXiE-xxqMuvI9nb5LhfHOnBKhX40HTBOn7U1D2Qm3AheQP1ESFTHhBSwRMkALSewAH8" alt="The Beginning After The End Ltd"/>
        </div>
      </div>
      <div class="card-body">
        <div>
          <div class="card-meta">
            <span class="card-order-num">ORDER #MM-6615</span>
            <span class="card-price">$210.75</span>
          </div>
          <h3 class="card-title">THE BEGINNING AFTER THE END LTD.</h3>
          <p class="card-status-text">Delivered on Monday, June 03</p>
        </div>
        <div class="card-actions">
          <a href="productD.php?id=4&autoopen=1" class="btn-primary" style="text-decoration:none;text-align:center;">BUY AGAIN</a>
          <button class="btn-secondary" onclick="openRateModal('THE BEGINNING AFTER THE END LTD.')">RATE</button>
        </div>
      </div>
    </div>

  </div><!-- /.card-grid -->

  <div style="text-align: center; margin-top: 2rem; padding: 1rem 0; display: flex; align-items: center; gap: 1rem;">
    <div style="flex: 1; height: 3px; background: #000;"></div>
    <p style="font-family: 'Epilogue', sans-serif; font-weight: 900; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; color: #000; background: var(--background); padding: 0 1rem; white-space: nowrap;">YOU'VE REACHED THE END OF YOUR ARCHIVE</p>
    <div style="flex: 1; height: 3px; background: #000;"></div>
  </div>

</main>

<!--rate popup-->
<div class="rate-modal-overlay" id="rateModal">
  <div class="rate-modal-box">
    <button class="rate-modal-close" onclick="closeRateModal()">
      <span class="material-symbols-outlined" style="font-size:1rem;">close</span>
    </button>
    <div class="rate-modal-title">How would you like to rate this product?</div>
    <div class="rate-modal-product" id="rateProductName"></div>
    <div class="star-row" id="starRow">
      <span class="star" data-val="1">★</span>
      <span class="star" data-val="2">★</span>
      <span class="star" data-val="3">★</span>
      <span class="star" data-val="4">★</span>
      <span class="star" data-val="5">★</span>
    </div>
    <button class="rate-submit-btn" onclick="submitRating()">SUBMIT RATING</button>
  </div>
</div>

<footer>
  <div class="footer-brand">
    <span class="footer-brand-name">Annyeong</span>
    <span class="footer-rights">&copy; 2025 Annyeong Market.<br/>All rights reserved.</span>
  </div>
  <ul class="footer-links">
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="wishlistCart.php">Wishlist</a></li>
    <li><a href="myOrders.php">My Orders</a></li>
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

  /* ── Filter Logic ── */
  const tabs  = document.querySelectorAll('.filter-tab');
  const cards = document.querySelectorAll('.order-card');
  const grid  = document.querySelector('.card-grid');

  // Empty state
  const emptyState = document.createElement('div');
  emptyState.id = 'empty-state';
  emptyState.style.cssText = 'display:none;grid-column:1/-1;text-align:center;padding:3rem 1rem;border:4px dashed #000;background:#fff;box-shadow:6px 6px 0px 0px #000;';
  emptyState.innerHTML = '<p style="font-family:Epilogue,sans-serif;font-weight:900;font-size:1.5rem;text-transform:uppercase;letter-spacing:-0.03em;color:#b70048;">No Orders Here!</p><p style="font-family:Plus Jakarta Sans,sans-serif;font-size:0.85rem;color:#595c5d;margin-top:0.5rem;">Nothing in this category yet.</p>';
  grid.appendChild(emptyState);

  function filterCards(filter) {
    let visible = 0;
    cards.forEach(function(card) {
      const match = filter === 'all' || card.getAttribute('data-status') === filter;
      if (match) {
        card.style.display = '';
        card.style.opacity = '0';
        card.style.transform = 'translateY(8px)';
        requestAnimationFrame(function() {
          card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        });
        visible++;
      } else {
        card.style.display = 'none';
      }
    });
    emptyState.style.display = visible === 0 ? 'block' : 'none';
  }

  tabs.forEach(function(tab) {
    tab.addEventListener('click', function() {
      tabs.forEach(function(t) { t.classList.remove('active'); });
      this.classList.add('active');
      filterCards(this.getAttribute('data-filter'));

      /*
       * ── WHEN BACKEND IS READY ──
       * fetch(`includes/get_orders.php?status=${this.getAttribute('data-filter')}`)
       *   .then(res => res.json())
       *   .then(data => renderCards(data));
       */
    });
  });

  let currentRating = 0;

function openRateModal(productName) {
  document.getElementById('rateProductName').textContent = productName;
  currentRating = 0;
  document.querySelectorAll('.star').forEach(s => s.classList.remove('active'));
  document.getElementById('rateModal').classList.add('open');
}

function closeRateModal() {
  document.getElementById('rateModal').classList.remove('open');
}

document.querySelectorAll('.star').forEach(function(star) {
  star.addEventListener('click', function() {
    currentRating = parseInt(this.getAttribute('data-val'));
    document.querySelectorAll('.star').forEach(function(s) {
      s.classList.toggle('active', parseInt(s.getAttribute('data-val')) <= currentRating);
    });
  });
});

function submitRating() {
  if (currentRating === 0) {
    alert('Please select a star rating first!');
    return;
  }
  closeRateModal();
  alert('Thanks for rating ' + currentRating + ' star' + (currentRating > 1 ? 's' : '') + '!');
}

document.getElementById('rateModal').addEventListener('click', function(e) {
  if (e.target === this) closeRateModal();
});

// Redirect & Auto-Open Method
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('autoopen') === '1') {
  window.addEventListener('load', function() {
    document.getElementById('productModal') && document.getElementById('productModal').classList.add('open');
  });
}
</script>

</body>
</html>