<?php
// Start session and include database connection
session_start();
require_once 'includes/db_connection.php';

// Base path for all product images
define('IMG_PATH', 'https://res.cloudinary.com/ds3irzr48/image/upload/q_auto/f_auto/');

// Session variables — get from session
$username = $_SESSION['username'] ?? 'Guest';
$role     = $_SESSION['role'] ?? 'customer';
$user_id  = $_SESSION['user_id'] ?? null;

// Check if user is logged in
if (!$user_id) {
    header('Location: login_register.php');
    exit();
}

// Fetch wishlist items with product details
$wishlist_items = [];
$stmt = $con->prepare("
    SELECT w.id as wishlist_id, w.product_id, p.name, p.description as subtitle,
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

// Fetch product suggestions — show products not in wishlist
$suggestions = [];
$stmt = $con->prepare("
    SELECT id as product_id, name, price, image_url as image
    FROM products
    WHERE id NOT IN (SELECT product_id FROM wishlist WHERE user_id = ?)
    ORDER BY created_at DESC
    LIMIT 4
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row;
}
$stmt->close();

// Summary totals — compute from wishlist_items (multiply price by quantity)
$total_items    = array_sum(array_map(fn($i) => $i['quantity'], $wishlist_items));
$subtotal       = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $wishlist_items));
$shipping       = $subtotal > 0 ? 3000 : 0;
$discount       = $subtotal > 0 ? 4100 : 0;
$final_total    = $subtotal + $shipping - $discount;
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>MINI-MART! | Checkout Hub</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,900;1,900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

:root {
  --on-tertiary-fixed-variant: #665500;
  --surface-tint: #b70048;
  --primary-dim: #a1003f;
  --outline-variant: #abadae;
  --primary-fixed: #ff7290;
  --on-background: #2c2f30;
  --surface: #f5f6f7;
  --secondary-dim: #00595b;
  --background: #f5f6f7;
  --inverse-surface: #0c0f10;
  --on-primary-fixed-variant: #5f0022;
  --on-secondary-container: #005b5d;
  --on-surface: #2c2f30;
  --secondary-fixed: #52f9fc;
  --inverse-primary: #ff4e7c;
  --on-primary-container: #4d001a;
  --surface-dim: #d1d5d7;
  --secondary: #006668;
  --surface-variant: #dadddf;
  --surface-container-high: #e0e3e4;
  --surface-bright: #f5f6f7;
  --on-secondary-fixed-variant: #006668;
  --tertiary: #6c5a00;
  --tertiary-fixed: #fdd828;
  --on-surface-variant: #595c5d;
  --on-primary-fixed: #000000;
  --tertiary-fixed-dim: #eeca12;
  --surface-container-highest: #dadddf;
  --secondary-fixed-dim: #3ceaee;
  --surface-container: #e6e8ea;
  --on-error-container: #570008;
  --outline: #757778;
  --on-tertiary-container: #5b4c00;
  --inverse-on-surface: #9b9d9e;
  --tertiary-container: #fdd828;
  --secondary-container: #52f9fc;
  --on-tertiary-fixed: #453900;
  --on-tertiary: #fff2cc;
  --on-secondary-fixed: #004749;
  --error: #b31b25;
  --tertiary-dim: #5e4f00;
  --primary-container: #ff7290;
  --primary: #b70048;
  --error-dim: #9f0519;
  --surface-container-lowest: #ffffff;
  --on-error: #ffefee;
  --on-secondary: #c0feff;
  --on-primary: #ffeff0;
  --surface-container-low: #eff1f2;
  --error-container: #fb5151;
  --primary-fixed-dim: #ff557f;
  --border-radius: 0.125rem;
  --border-radius-lg: 0.25rem;
  --border-radius-xl: 0.5rem;
  --border-radius-full: 0.75rem;
}

.material-symbols-outlined {
  font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 48;
}

body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background-color: var(--surface);
  color: var(--on-background);
  min-height: 100vh;
  background-image: radial-gradient(#000000 1px, transparent 0); background-size: 8px 8px; 
}

h1, h2, h3, .brand-font {
  font-family: 'Epilogue', sans-serif;
}

.kinetic-border {
  border: 3px solid #000000;
}

.hard-shadow {
  box-shadow: 6px 6px 0px 0px #000000;
}

.halftone-bg {
  background-image: radial-gradient(circle, currentColor 1px, transparent 1px);
  background-size: 8px 8px;
}

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
    .dropdown-user-info { padding: 16px 20px; display: flex; flex-direction: column; gap: 4px; background: var(--primary-container); border-bottom: 4px solid #000; }
    .dropdown-username { font-weight: 900; font-style: italic; font-size: 1.1rem; color: #000000; text-transform: uppercase; }
    .dropdown-role { font-weight: 700; font-size: 0.65rem; text-transform: uppercase; color: rgba(0,0,0,0.6); }
    .dropdown-id { font-weight: 700; font-size: 0.65rem; text-transform: uppercase; color: rgba(0,0,0,0.4); }
    .dropdown-divider { height: 0; border-top: 2px solid #000; }
    .dropdown-logout { display: flex; align-items: center; gap: 10px; padding: 14px 20px; font-weight: 900; font-style: italic; font-size: 0.9rem; text-transform: uppercase; color: #000000; text-decoration: none; background: var(--primary); border-top: 2px solid #000; transition: background 0.1s, color 0.1s; }
    .dropdown-logout:hover { background: var(--tertiary-container); color: #000; }

main {
  max-width: 1440px;
  margin: 0 auto;
  padding: clamp(1rem, 5vw, 3rem) clamp(0.5rem, 4vw, 1.5rem);
}

.checkout-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: clamp(1.5rem, 4vw, 3rem);
  align-items: start;
}

@media (min-width: 768px) {
  .checkout-grid {
    gap: clamp(2rem, 5vw, 3rem);
  }
}

@media (min-width: 1024px) {
  .checkout-grid {
    grid-template-columns: 9fr 3fr;
  }
}

.product-list {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.haul-header {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1rem;
}

@media (min-width: 768px) {
  .haul-header {
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-end;
  }
}

.haul-title {
  font-size: clamp(1.75rem, 5vw, 3rem);
  font-weight: 900;
  font-family: 'Epilogue', sans-serif;
  text-transform: uppercase;
  font-style: italic;
  color: #9f0519;
  -webkit-text-stroke: 2px #000000;
  filter: drop-shadow(4px 4px 0px #000000);
  line-height: 1;
}

@media (min-width: 768px) {
  .haul-title {
    font-size: clamp(2rem, 6vw, 3rem);
  }
}

.product-card {
  background-color: var(--surface-container-lowest);
  border: 3px solid #000000;
  box-shadow: 4px 4px 0px 0px #000000;
  padding: clamp(0.5rem, 3vw, 0.875rem);
  position: relative;
  transition: transform 0.2s;
}

.product-card:hover {
  transform: scale(1.01);
}

.product-card-inner {
  display: flex;
  flex-direction: column;
  gap: clamp(0.75rem, 2vw, 1rem);
  align-items: flex-start;
}

@media (min-width: 500px) {
  .product-card-inner {
    flex-direction: row;
    align-items: center;
  }
}

.product-img-wrap {
  width: clamp(4rem, 15vw, 5rem);
  height: clamp(4rem, 15vw, 5rem);
  border: 3px solid #000000;
  overflow: hidden;
  position: relative;
  flex-shrink: 0;
}

.product-img-wrap.tertiary-bg {
  background-color: var(--tertiary-container);
}

.product-img-wrap.secondary-bg {
  background-color: var(--secondary-container);
}

.product-img-wrap.tertiary-fixed-bg {
  background-color: var(--tertiary-fixed);
}

.product-img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.new-badge {
  position: absolute;
  top: 0;
  right: 0;
  background-color: var(--primary);
  color: var(--on-primary);
  font-size: 0.625rem;
  font-weight: 900;
  padding: 0 0.5rem;
  border: 3px solid #000000;
  border-top: none;
  border-right: none;
  text-transform: uppercase;
}

.product-info {
  flex: 1;
}

.product-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.product-name {
  font-size: 0.9rem;
  font-weight: 900;
  font-family: 'Epilogue', sans-serif;
  text-transform: uppercase;
  letter-spacing: -0.025em;
}

.product-sub {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--on-surface-variant);
  text-transform: uppercase;
}

.product-price {
  font-size: 1rem;
  font-weight: 900;
  font-family: 'Epilogue', sans-serif;
  color: var(--primary);
  font-style: italic;
}

.product-actions {
  margin-top: 0.75rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.qty-control {
  display: flex;
  align-items: center;
  border: 2px solid #000000;
  background-color: #ffffff;
  overflow: hidden;
}

.qty-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.15rem 0.5rem;
  font-size: 0.875rem;
  transition: background-color 0.15s;
}

.qty-btn:first-child {
  border-right: 2px solid #000000;
}

.qty-btn:last-child {
  border-left: 2px solid #000000;
}

.qty-btn:hover {
  background-color: var(--surface-variant);
}

.qty-display {
  padding: 0 0.6rem;
  font-weight: 900;
  font-size: 0.8rem;
}

.ditch-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--on-surface-variant);
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: 700;
  font-size: 0.875rem;
  font-family: 'Plus Jakarta Sans', sans-serif;
  transition: color 0.15s;
}

.ditch-btn:hover {
  color: var(--error);
}

.sidebar {
  position: sticky;
  top: clamp(4rem, 10vh, 7rem);
}

@media (max-width: 767px) {
  .sidebar {
    position: static;
    order: -1;
    margin-bottom: 2rem;
  }
}

.command-center {
  background-color: var(--tertiary-container);
  border: 3px solid #000000;
  box-shadow: 4px 4px 0px 0px #000000;
  padding: clamp(1rem, 4vw, 1.25rem);
  display: flex;
  flex-direction: column;
  gap: clamp(0.7rem, 2vw, 1rem);
  position: relative;
  overflow: hidden;
}

.halftone-overlay {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle, rgba(0,0,0,0.05) 1px, transparent 1px);
  background-size: 8px 8px;
  pointer-events: none;
}

.command-content {
  position: relative;
  z-index: 10;
}

.command-title {
  font-size: clamp(1rem, 4vw, 1.25rem);
  font-weight: 900;
  font-family: 'Epilogue', sans-serif;
  text-transform: uppercase;
  font-style: italic;
  line-height: 1;
  margin-bottom: 0.4rem;
}

.command-divider {
  height: 3px;
  background-color: #000000;
  width: 100%;
  margin-bottom: 1rem;
}

.totals {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 700;
  font-size: 0.8rem;
  text-transform: uppercase;
}

.total-row.secondary {
  color: var(--secondary);
}

.total-row.primary-dim {
  color: var(--primary-dim);
}

.total-row span:last-child {
  font-weight: 900;
}

.final-section {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 3px dashed #000000;
}

.final-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1rem;
}

.final-label {
  font-size: 0.7rem;
  font-weight: 900;
  text-transform: uppercase;
  background-color: #000000;
  color: #ffffff;
  padding: 0.2rem 0.4rem;
  transform: rotate(-3deg);
  display: inline-block;
}

.final-amount {
  font-size: clamp(1.25rem, 5vw, 1.875rem);
  font-weight: 900;
  font-family: 'Epilogue', sans-serif;
  color: var(--primary);
  filter: drop-shadow(2px 2px 0px #000000);
}

.checkout-btn {
  width: 100%;
  background-color: var(--primary);
  color: var(--on-primary);
  border: 3px solid #000000;
  box-shadow: 4px 4px 0px 0px #000000;
  padding: 0.875rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: transform 0.1s, box-shadow 0.1s;
}

.checkout-btn:active {
  transform: translate(4px, 4px);
  box-shadow: none;
}

.checkout-btn-main {
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  font-size: 1.25rem;
  text-transform: uppercase;
  font-style: italic;
  letter-spacing: -0.025em;
  position: relative;
  z-index: 10;
}

.checkout-btn-glow {
  position: absolute;
  inset: 0;
  background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
  transform: translateX(-100%);
  transition: transform 0.7s;
}

.checkout-btn:hover .checkout-btn-glow {
  transform: translateX(100%);
}

.star-points {
  margin-top: 1rem;
  background-color: rgba(255,255,255,0.4);
  padding: 0.6rem;
  border: 2px dashed #000000;
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.star-points .material-symbols-outlined {
  font-size: 1.5rem;
  color: var(--secondary);
}

.star-points-label {
  font-size: 0.65rem;
  font-weight: 900;
  text-transform: uppercase;
}

.star-points-sub {
  font-size: 0.55rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--on-surface-variant);
  line-height: 1.3;
}

.suggestions {
  margin-top: 3rem;
}

.section-header {
  font-size: 1.5rem;
  font-weight: 900;
  font-family: 'Epilogue', sans-serif;
  text-transform: uppercase;
  font-style: italic;
  margin-bottom: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.section-line {
  height: 4px;
  background-color: #000000;
  flex: 1;
}

.suggestions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: clamp(1rem, 3vw, 1.5rem);
}

@media (min-width: 480px) {
  .suggestions-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 768px) {
  .suggestions-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 1024px) {
  .suggestions-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

.suggestion-card {
  background-color: #ffffff;
  border: 3px solid #000000;
  box-shadow: 4px 4px 0px 0px #000000;
  padding: 0.4rem;
  transition: transform 0.2s;
  max-width: 550px;
  justify-self: center;
}

.suggestion-card:nth-child(1) { transform: translateY(1rem); }
.suggestion-card:nth-child(1):hover { transform: translateY(0.5rem); }
.suggestion-card:nth-child(2) { transform: translateY(-1rem); }
.suggestion-card:nth-child(2):hover { transform: translateY(-1.5rem); }
.suggestion-card:nth-child(3) { transform: translateY(2rem); }
.suggestion-card:nth-child(3):hover { transform: translateY(1.5rem); }
.suggestion-card:nth-child(4) { transform: translateY(-0.5rem); }
.suggestion-card:nth-child(4):hover { transform: translateY(-1rem); }

.suggestion-img {
  width: 100%;
  aspect-ratio: 1;
  object-fit: cover;
  border: 3px solid #000000;
  margin-bottom: 0.3rem;
  display: block;
  max-height: 250px;
}

.suggestion-name {
  font-weight: 900;
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: -0.025em;
}

.suggestion-price {
  color: var(--primary);
  font-weight: 900;
  font-size: 0.7rem;
  margin-top: 0.1rem;
  text-transform: uppercase;
  font-style: italic;
}

.add-btn {
  margin-top: 0.5rem;
  width: 100%;
  background-color: var(--secondary-container);
  border: 2px solid #000000;
  padding: 0.35rem;
  font-size: 0.65rem;
  font-weight: 900;
  text-transform: uppercase;
  cursor: pointer;
  font-family: 'Plus Jakarta Sans', sans-serif;
  transition: background-color 0.15s, color 0.15s;
}

.add-btn:hover {
  background-color: var(--secondary);
  color: #ffffff;
}

.product-card.checked {
  background-color: #e6f9f0;
  border-color: #006668;
  box-shadow: 4px 4px 0px 0px #006668;
}

.product-card.checked::before {
  content: '✓';
  position: absolute;
  top: -0.6rem;
  right: -0.6rem;
  background: #006668;
  color: #fff;
  font-weight: 900;
  font-size: 0.75rem;
  width: 1.5rem;
  height: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid #000;
  transform: rotate(12deg);
  box-shadow: 2px 2px 0px #000;
}

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
        <a href="wishlistCart.php" class="active">Wishlist</a>
        <a href="myOrders.php">My Orders</a>
      </nav>
    </div>
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <a href="#" class="profile-trigger" style="background-color: #00595b;">
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

  <main>
    <div class="checkout-grid">

      <!-- Product List -->
      <div class="product-list">
        <div class="haul-header">
          <h2 class="haul-title">WISHLIST CART</h2>
        </div>

        <?php
        // ── Image background class cycles per card ──
        $bg_classes = ['tertiary-bg', 'secondary-bg', 'tertiary-fixed-bg'];
        $bg_index   = 0;

        // Expected keys: wishlist_id, product_id, name, subtitle, price, image, badge, quantity
        if (!empty($wishlist_items)) :
          foreach ($wishlist_items as $item) :
            $pid      = (int) $item['product_id'];
            $wid      = (int) $item['wishlist_id'];
            $name     = htmlspecialchars($item['name']);
            $subtitle = htmlspecialchars($item['subtitle'] ?? '');
            $price    = (float) $item['price'];
            $img_url  = htmlspecialchars($item['image']);
            $qty      = (int) $item['quantity'];
            $badge    = $item['badge'] ?? '';
            $bg_class = $bg_classes[$bg_index % count($bg_classes)];
            $bg_index++;
        ?>
        <div class="product-card" data-wishlist-id="<?= $wid ?>" data-product-id="<?= $pid ?>" onclick="this.classList.toggle('checked')">
          <input type="checkbox" style="display:none;">
          <div class="product-card-inner">
            <div class="product-img-wrap <?= $bg_class ?>">
              <img alt="<?= $name ?>" src="<?= $img_url ?>"/>
              <?php if ($badge) : ?>
                <div class="new-badge"><?= htmlspecialchars($badge) ?></div>
              <?php endif; ?>
            </div>
            <div class="product-info">
              <div class="product-top">
                <div>
                  <h3 class="product-name"><?= $name ?></h3>
                  <p class="product-sub"><?= $subtitle ?></p>
                </div>
                <span class="product-price" id="item-price-<?= $wid ?>">₩<?= number_format((int)$price) ?></span>
              </div>
              <div class="product-actions">
                <div class="qty-control">
                  <button class="qty-btn" onclick="event.stopPropagation(); updateQty(<?= $wid ?>, -1)">-</button>
                  <span class="qty-display" id="qty-<?= $wid ?>"><?= str_pad($qty, 2, '0', STR_PAD_LEFT) ?></span>
                  <button class="qty-btn" onclick="event.stopPropagation(); updateQty(<?= $wid ?>, 1)">+</button>
                </div>
                <button class="ditch-btn" onclick="event.stopPropagation(); removeItem(<?= $wid ?>)">
                  <span class="material-symbols-outlined" style="font-size:1.125rem;">delete</span>
                  ANIYO
                </button>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; else: ?>
        <p style="padding:2rem; font-weight:700; text-align:center;">Your wishlist is empty.</p>
        <?php endif; ?>
      </div><!-- end .product-list -->

      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="command-center kinetic-border">
          <div class="halftone-overlay"></div>
          <div class="command-content">
            <h2 class="command-title">SUMMARY</h2>
            <div class="command-divider"></div>
            <div class="totals">
              <div class="total-row">
                <span>TOTAL ITEMS (<span id="summary-item-count"><?= $total_items ?></span>)</span>
                <span id="summary-subtotal">₩<?= number_format($subtotal) ?></span>
              </div>
              <div class="total-row secondary">
                <span>ESTIMATE SHIPPING</span>
                <span id="summary-shipping">₩<?= number_format($shipping) ?></span>
              </div>
              <div class="total-row primary-dim">
                <span>MEMBERSHIP DISCOUNT</span>
                <span id="summary-discount">-₩<?= number_format($discount) ?></span>
              </div>
            </div>
            <div class="final-section">
              <div class="final-row">
                <span class="final-label">FINAL ESTIMATE</span>
                <span class="final-amount" id="summary-final">₩<?= number_format($final_total) ?></span>
              </div>
              <button class="checkout-btn kinetic-border" onclick="window.location.href='checkout.php'">
                <span class="checkout-btn-main">CHECKOUT NOW!</span>
                <div class="checkout-btn-glow"></div>
              </button>
            </div>
            <div class="star-points kinetic-border" style="border-style: dashed;">
              <span class="material-symbols-outlined">electric_bolt</span>
              <div>
                <p class="star-points-label">CHECK OUT NOW WITH 30% DISCOUNT!</p>
                <p class="star-points-sub">Redeem for exclusive discounts &amp; limited edition items</p>
              </div>
            </div>
          </div>
        </div>
      </aside>

    </div>

    <!-- Suggestions -->
    <section class="suggestions">
      <h2 class="section-header">
        DON'T FORGET THESE!
        <span class="section-line"></span>
      </h2>
      <div class="suggestions-grid">
        <?php if (!empty($suggestions)) : ?>
          <?php foreach ($suggestions as $s) : ?>
          <div class="suggestion-card" data-product-id="<?= (int)$s['product_id'] ?>">
            <img alt="<?= htmlspecialchars($s['name']) ?>" class="suggestion-img"
                 src="<?= htmlspecialchars($s['image']) ?>"/>
            <h4 class="suggestion-name"><?= htmlspecialchars($s['name']) ?></h4>
            <p class="suggestion-price">₩<?= number_format((int)$s['price']) ?></p>
            <button class="add-btn" onclick="addToWishlist(<?= (int)$s['product_id'] ?>)">ADD +</button>
          </div>
          <?php endforeach; ?>
        <?php else : ?>
          <p style="grid-column:1/-1; font-weight:700;">No suggestions available.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>

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

  // ── AJAX: Update quantity ──
  // wires this to: includes/update_wishlist_qty.php
  function updateQty(wishlistId, delta) {
    fetch('includes/update_wishlist_qty.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ wishlist_id: wishlistId, delta: delta })
    })
    .then(r => r.json())
    .then(data => {
      if (data.new_qty <= 0) {
        removeItem(wishlistId);
        return;
      }
      document.getElementById('qty-' + wishlistId).textContent = String(data.new_qty).padStart(2, '0');
      // Update sidebar totals via returned values
      if (data.totals) refreshSidebar(data.totals);
    });
  }

  // ── AJAX: Remove item ──
  // wires this to: includes/remove_wishlist_item.php
  function removeItem(wishlistId) {
    fetch('includes/remove_wishlist_item.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ wishlist_id: wishlistId })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        location.reload(); // Reload to show product back in recommendations
      }
    });
  }

  // ── AJAX: Add suggestion to wishlist ──
  // wires this to: includes/add_to_wishlist.php
  function addToWishlist(productId) {
    fetch('includes/add_to_wishlist.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: productId })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) location.reload(); // reload to show new item in list
    });
  }

  // ── Helper: Refresh sidebar totals via AJAX response ──
  // Expected: { item_count, subtotal, shipping, discount, final_total }
  function refreshSidebar(totals) {
    document.getElementById('summary-item-count').textContent = totals.item_count;
    document.getElementById('summary-subtotal').textContent   = '₩' + totals.subtotal.toLocaleString();
    document.getElementById('summary-shipping').textContent   = '₩' + totals.shipping.toLocaleString();
    document.getElementById('summary-discount').textContent   = '-₩' + totals.discount.toLocaleString();
    document.getElementById('summary-final').textContent      = '₩' + totals.final_total.toLocaleString();
  }
</script>
</body>
</html>