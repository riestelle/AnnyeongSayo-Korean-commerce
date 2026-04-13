<?php
// ══════════════════════════════════════════════════
//  BACKEND INTEGRATION BLOCK
//  Your teammate should populate these before this
//  file renders. Move this to a controller/init file.
// ══════════════════════════════════════════════════

// 1. Base path for all product images
define('IMG_PATH', '/assets/images/products/');

// 2. Session / auth variables (already set by your auth middleware)
// $username, $role, $user_id — already used below in the header dropdown.

// 3. Product query — replace with your actual DB call:
// Example:
//   $stmt = $pdo->query("SELECT product_id, name, subtitle, price, image, badge, category, stock, rating, sku FROM products WHERE is_active = 1 ORDER BY created_at DESC");
//   $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$products = []; // ← remove this line once DB query is wired up
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>POP! SHOP - Web Shop Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,900;1,900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --surface-bright: #f5f6f7;
  --tertiary: #6c5a00;
  --on-tertiary-container: #5b4c00;
  --surface-tint: #b70048;
  --on-primary-fixed-variant: #5f0022;
  --surface-container-low: #eff1f2;
  --surface-container-high: #e0e3e4;
  --on-error: #ffefee;
  --secondary-fixed: #52f9fc;
  --surface-container-lowest: #ffffff;
  --surface-container-highest: #dadddf;
  --outline: #757778;
  --primary: #b70048;
  --on-primary: #ffeff0;
  --surface-dim: #d1d5d7;
  --surface-container: #e6e8ea;
  --secondary-dim: #00595b;
  --error-dim: #9f0519;
  --on-background: #2c2f30;
  --on-primary-container: #4d001a;
  --surface: #f5f6f7;
  --on-surface-variant: #595c5d;
  --on-secondary: #c0feff;
  --on-secondary-fixed-variant: #006668;
  --secondary: #006668;
  --on-tertiary-fixed-variant: #665500;
  --secondary-fixed-dim: #3ceaee;
  --tertiary-fixed-dim: #eeca12;
  --on-error-container: #570008;
  --secondary-container: #52f9fc;
  --on-primary-fixed: #000000;
  --surface-variant: #dadddf;
  --primary-fixed: #ff7290;
  --inverse-on-surface: #9b9d9e;
  --outline-variant: #abadae;
  --primary-fixed-dim: #ff557f;
  --error: #b31b25;
  --tertiary-fixed: #fdd828;
  --on-tertiary: #fff2cc;
  --on-secondary-fixed: #004749;
  --inverse-surface: #0c0f10;
  --error-container: #fb5151;
  --primary-container: #ff7290;
  --on-secondary-container: #005b5d;
  --tertiary-dim: #5e4f00;
  --on-tertiary-fixed: #453900;
  --primary-dim: #a1003f;
  --tertiary-container: #fdd828;
  --background: #f5f6f7;
  --on-surface: #2c2f30;
  --inverse-primary: #ff4e7c;
}

body {
  background-color: var(--background);
  font-family: 'Plus Jakarta Sans', sans-serif;
  color: var(--on-surface);
  background-image: radial-gradient(#000000 1px, transparent 0); background-size: 8px 8px; 
}

::selection {
  background-color: var(--tertiary-container);
}

.halftone-pattern {
  background-image: radial-gradient(circle, currentColor 1px, transparent 1px);
  background-size: 6px 6px;
}

.ink-shadow {
  box-shadow: 6px 6px 0px 0px #000000;
}

.ink-shadow-sm {
  box-shadow: 4px 4px 0px 0px #000000;
}

.kinetic-shadow {
  box-shadow: 6px 6px 0px 0px #000000;
}

.kinetic-shadow-hover:active {
  transform: translate(2px, 2px);
  box-shadow: 2px 2px 0px 0px #000000;
}

.text-stroke-ink {
  -webkit-text-stroke: 1.5px #000000;
}

.sticker-rotate-left { transform: rotate(-2deg); }
.sticker-rotate-right { transform: rotate(2deg); }

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

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 0.5rem;
  color: #000000;
  font-size: 1.5rem;
  transition: background-color 150ms;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-btn:hover { background-color: #fdd828; }
.icon-btn:active { transform: translate(1px, 1px); }

.material-symbols-outlined {
  font-family: 'Material Symbols Outlined';
  font-weight: normal;
  font-style: normal;
  font-size: 24px;
  line-height: 1;
  letter-spacing: normal;
  text-transform: none;
  display: inline-block;
  white-space: nowrap;
  word-wrap: normal;
  direction: ltr;
  -webkit-font-feature-settings: 'liga';
  font-feature-settings: 'liga';
  -webkit-font-smoothing: antialiased;
}

main {
  max-width: 80rem;
  margin: 0 auto;
  padding: 2rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 3rem;
}

@media (min-width: 768px) {
  main { padding: 2rem; }
}

.hero {
  background-color: var(--primary);
  border: 4px solid #000000;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  min-height: 16rem;
  position: relative;
  overflow: hidden;
  box-shadow: 6px 6px 0px 0px #000000;
}

.hero-halftone {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle, currentColor 1px, transparent 1px);
  background-size: 6px 6px;
  opacity: 0.1;
  pointer-events: none;
  color: white;
}

.hero-content {
  position: relative;
  z-index: 10;
}

.hero h1 {
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  font-size: clamp(3rem, 8vw, 6rem);
  text-transform: uppercase;
  letter-spacing: -0.05em;
  font-style: italic;
  color: #ffffff;
  line-height: 1;
}

.hero-sub {
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  font-size: clamp(1rem, 3vw, 1.5rem);
  text-transform: uppercase;
  letter-spacing: -0.05em;
  font-style: italic;
  color: var(--tertiary-fixed);
  margin-top: 0.5rem;
}

.sticker-badge {
  position: absolute;
  top: 2.5rem;
  right: 2.5rem;
  width: 8rem;
  height: 8rem;
  background-color: var(--tertiary-container);
  border: 4px solid #000000;
  border-radius: 50%;
  box-shadow: 6px 6px 0px 0px #000000;
  display: flex;
  align-items: center;
  justify-content: center;
  transform: rotate(15deg);
}

.sticker-badge span {
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  font-size: 1.25rem;
  text-align: center;
  line-height: 1;
  color: #000;
}

.search-filter {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  align-items: stretch;
}

@media (min-width: 768px) {
  .search-filter {
    flex-direction: row;
    align-items: center;
  }
}

.search-wrap {
  flex: 1;
  position: relative;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  font-weight: bold;
  color: #000;
}

.search-wrap input {
  width: 100%;
  background-color: #ffffff;
  border: 4px solid #000000;
  padding: 1rem 1rem 1rem 3rem;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  font-size: 1.25rem;
  text-transform: uppercase;
  outline: none;
}

.search-wrap input:focus {
  box-shadow: 0 0 0 4px var(--tertiary-fixed);
}

.select-wrap {
  position: relative;
  width: 100%;
}

@media (min-width: 768px) {
  .select-wrap { width: 16rem; }
}

.select-wrap select {
  width: 100%;
  background-color: #ffffff;
  border: 4px solid #000000;
  padding: 1rem;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  font-size: 1.25rem;
  text-transform: uppercase;
  appearance: none;
  -webkit-appearance: none;
  outline: none;
  cursor: pointer;
  box-shadow: 6px 6px 0px 0px #000000;
}

.select-wrap select:focus {
  box-shadow: 0 0 0 4px var(--tertiary-fixed);
}

.select-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  font-weight: 900;
  color: #000;
}

.search-wrap input,
.select-wrap select {
  font-size: 0.875rem;
  padding: 0.6rem 0.6rem 0.6rem 2.5rem;
}

.product-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
}

@media (min-width: 768px) {
  .product-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (min-width: 1024px) {
  .product-grid { grid-template-columns: repeat(4, 1fr); }
}

.card {
  border: 4px solid #000000;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  box-shadow: 6px 6px 0px 0px #000000;
  position: relative;
  overflow: hidden;
}

.card-halftone {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle, currentColor 1px, transparent 1px);
  background-size: 6px 6px;
  opacity: 0.1;
  pointer-events: none;
}

.card-featured {
  grid-column: span 1;
  flex-direction: column;
  background-color: var(--tertiary-container);
  padding: 1.5rem;
}

@media (min-width: 768px) {
  .card-featured {
    grid-column: span 2;
    flex-direction: row;
    gap: 1.5rem;
  }
}

.card-img-wrap {
  position: relative;
  width: 100%;
  background-color: #ffffff;
  border: 4px solid #000000;
  overflow: hidden;
  aspect-ratio: 16/9;
}

@media (min-width: 768px) {
  .card-featured .card-img-wrap {
    width: 50%;
    aspect-ratio: auto;
    min-height: 320px;
  }
}

.card-img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 300ms;
  display: block;
}

.card:hover .card-img-wrap img {
  transform: scale(1.1);
}

.card-badge-rare {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background-color: var(--primary);
  color: #ffffff;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  padding: 0.25rem 1rem;
  border: 2px solid #000000;
  transform: rotate(2deg);
}

.card-badge-new {
  position: absolute;
  top: 0.5rem;
  left: 0.5rem;
  background-color: var(--tertiary-container);
  color: #000000;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  padding: 0.25rem 0.75rem;
  border: 2px solid #000000;
  transform: rotate(-2deg);
}

.card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
  z-index: 10;
}

.card-featured .card-body { margin-top: 1rem; }

@media (min-width: 768px) {
  .card-featured .card-body { margin-top: 0; }
}

.card-title {
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  font-size: 2.25rem;
  text-transform: uppercase;
  letter-spacing: -0.05em;
  font-style: italic;
  line-height: 1.1;
}

.card-subtitle {
  font-weight: 700;
  color: var(--on-surface-variant);
  text-transform: uppercase;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}

.card-footer {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 1.5rem;
  margin-top: 2rem;
}

.price-tag-white,
.price-tag-sm,
.price-tag-tertiary,
.price-tag-primary {
  position: relative;
  display: inline-flex;
  align-items: center;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  padding: 0.4rem 1rem 0.4rem 1.5rem;
  border: 2px solid #000000;
  box-shadow: 3px 3px 0px 0px #000000;
  margin-left: auto;
  transform: rotate(2deg);
}

.price-tag-white::before,
.price-tag-sm::before,
.price-tag-tertiary::before,
.price-tag-primary::before {
  content: '';
  position: absolute;
  left: -8px;
  top: 50%;
  transform: translateY(-50%);
  width: 12px;
  height: 12px;
  background-color: #ffffff;
  border: 2px solid #000000;
  border-radius: 50%;
}

.price-tag-white::after,
.price-tag-sm::after,
.price-tag-tertiary::after,
.price-tag-primary::after {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 6px;
  background-color: rgba(0,0,0,0.15);
}

.price-tag-white { background-color: #ffffff; font-size: 1.5rem; }
.price-tag-sm { background-color: #ffffff; font-size: 1.125rem; }
.price-tag-tertiary { background-color: var(--tertiary-container); font-size: 1.125rem; }
.price-tag-primary { background-color: var(--primary); color: #ffffff; font-size: 1.125rem; }

.btn-primary {
  background-color: #000000;
  color: #ffffff;
  padding: 0.75rem 2rem;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  text-transform: uppercase;
  font-size: 1.125rem;
  border: none;
  cursor: pointer;
  box-shadow: 6px 6px 0px 0px #000000;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background-color 150ms;
}

.btn-group {
  display: flex;
  flex-direction: row;
  gap: 0.5rem;
  margin-top: 1rem;
  width: 100%;
}

.btn-icon {
  background-color: #000000;
  color: #ffffff;
  border: none;
  cursor: pointer;
  box-shadow: 6px 6px 0px 0px #000000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.6rem 0.75rem;
  transition: background-color 150ms;
  flex-shrink: 0;
}

.btn-icon:hover { background-color: var(--primary); }

.btn-primary:hover { background-color: var(--primary); }

.card-standard-sq .card-footer {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.card-standard-sq .card-footer > div:first-child {
  width: 100%;
}

.card-standard-sq .btn-primary {
  width: 100%;
  justify-content: center;
  margin-top: 1rem;
  padding: 0.6rem 1rem;
  font-size: 0.95rem;
}

.btn-outline {
  background-color: #ffffff;
  color: #000000;
  border: 4px solid #000000;
  padding: 0.75rem 1rem;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  text-transform: uppercase;
  font-size: 1.125rem;
  cursor: pointer;
  width: 100%;
  justify-content: center;
  margin-top: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background-color 150ms, color 150ms;
}

.btn-outline:hover {
  background-color: #000000;
  color: #ffffff;
}

.card-standard-sq .card-img-wrap {
  aspect-ratio: 4 / 3;
  margin-bottom: 1rem;
}

.card-standard-sq .card-img-wrap {
  aspect-ratio: 4 / 3;
  margin-bottom: 1rem;
  flex-shrink: 0;
}

.card-standard-sq .card-footer {
  align-items: flex-start;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.card-secondary { background-color: var(--secondary-container); }
.card-primary-c { background-color: var(--primary-container); }
.card-surface { background-color: var(--surface-container-highest); }

/*product details modal pop-up*/
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.75);
  z-index: 1000;
  overflow-y: auto;
  padding: 2rem 1rem;
}
.modal-overlay.open { display: flex; align-items: flex-start; justify-content: center; }

.modal-crosssell { margin-top: 3rem; border-top: 4px solid #000; padding-top: 2rem; }
.modal-crosssell-title {
  font-family: 'Epilogue', sans-serif;
  font-size: 1.5rem;
  font-weight: 900;
  text-transform: uppercase;
  font-style: italic;
  letter-spacing: -0.05em;
  -webkit-text-stroke: 1.5px #000;
  margin-bottom: 0.5rem;
}
.modal-crosssell-underline {
  height: 0.4rem;
  width: 10rem;
  background: var(--tertiary-fixed);
  border: 2px solid #000;
  margin-bottom: 1.5rem;
}
.modal-crosssell-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}
@media (min-width: 640px) {
  .modal-crosssell-grid { grid-template-columns: repeat(4, 1fr); }
}
.modal-crosssell-card {
  background: #fff;
  border: 3px solid #000;
  padding: 0.6rem;
  box-shadow: 4px 4px 0px 0px #000;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s;
}
.modal-crosssell-card:hover { transform: translateY(-4px); box-shadow: 6px 6px 0px 0px #000; }
.modal-crosssell-card img {
  width: 100%;
  aspect-ratio: 1/1;
  object-fit: cover;
  border: 2px solid #000;
  display: block;
  margin-bottom: 0.5rem;
}
.modal-crosssell-card-name {
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  text-transform: uppercase;
  font-style: italic;
  font-size: 0.85rem;
  letter-spacing: -0.05em;
}
.modal-crosssell-card-price { color: var(--primary); font-weight: 900; font-size: 0.8rem; }

.modal-box {
  background: var(--background);
  border: 4px solid #000;
  box-shadow: 10px 10px 0px 0px #000000;
  width: 100%;
  max-width: 1100px;
  position: relative;
  padding: 2rem;
  background-image: radial-gradient(#000000 1px, transparent 0);
  background-size: 8px 8px;
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: #000;
  color: #fff;
  border: none;
  cursor: pointer;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  z-index: 10;
}
.modal-close:hover { background: var(--primary); }
.modal-product-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
  align-items: start;
}
@media (min-width: 768px) {
  .modal-product-grid { grid-template-columns: 1fr 1fr; }
}
.modal-image-panel { position: relative; }
.modal-image-panel-bg {
  position: absolute;
  inset: -1rem;
  background: var(--secondary-container);
  opacity: 0.2;
  transform: rotate(-1deg);
  pointer-events: none;
}
.modal-image-panel-inner {
  position: relative;
  border: 4px solid #000;
  background: #fff;
  box-shadow: 6px 6px 0px 0px #000000;
  padding: 1rem;
  overflow: hidden;
}
.modal-image-panel-inner img {
  width: 100%;
  aspect-ratio: 1/1;
  object-fit: cover;
  border: 2px solid #000;
  display: block;
}
.modal-sticker-spice {
  position: absolute;
  top: 2rem;
  right: 2rem;
  transform: rotate(12deg);
  background: var(--primary);
  border: 4px solid #000;
  padding: 0.5rem 1.5rem;
  box-shadow: 4px 4px 0px 0px #000000;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  font-style: italic;
  color: #fff;
  text-transform: uppercase;
  font-size: 1rem;
  letter-spacing: -0.05em;
}
.modal-content-panel { display: flex; flex-direction: column; gap: 1.5rem; background: var(--surface); border: 4px solid #000; padding: 1.5rem; box-shadow: 6px 6px 0px 0px #000; }
.modal-product-badge {
  display: inline-block;
  background: var(--secondary);
  color: var(--on-secondary);
  padding: 0.25rem 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.75rem;
  border: 2px solid #000;
  transform: rotate(-1deg);
  margin-bottom: 0.5rem;
}
.modal-product-title {
  font-family: 'Epilogue', sans-serif;
  font-size: clamp(1.5rem, 4vw, 2.5rem);
  font-weight: 900;
  text-transform: uppercase;
  font-style: italic;
  letter-spacing: -0.05em;
  -webkit-text-stroke: 1.5px #000;
  line-height: 1;
}
.modal-price-row { display: flex; align-items: center; gap: 1rem; }
.modal-price {
  font-family: 'Epilogue', sans-serif;
  font-size: 1.5rem;
  font-weight: 900;
  color: var(--primary);
  -webkit-text-stroke: 1.5px #000;
}
.modal-free-ship {
  background: var(--tertiary-fixed);
  color: #000;
  padding: 0.1rem 0.4rem;
  font-weight: 700;
  border: 2px solid #000;
  font-size: 0.7rem;
}
.modal-narrative {
  position: relative;
  background: var(--surface-container-high);
  padding: 1rem;
  border: 2px solid #000;
  box-shadow: 4px 4px 0px 0px #000;
}
.modal-narrative-label {
  position: absolute;
  top: -0.8rem;
  left: -0.5rem;
  background: #000;
  color: #fff;
  padding: 0.15rem 0.75rem;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  text-transform: uppercase;
  font-size: 0.7rem;
  font-style: italic;
}
.modal-narrative p { color: var(--on-surface-variant); font-size: 0.8rem; line-height: 1.6; font-weight: 500; }
.modal-nutrition-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.75rem;
}
.modal-nutrition-card {
  background: #fff;
  border: 2px solid #000;
  padding: 0.6rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: 4px 4px 0px 0px #000;
}
.modal-nutrition-label { font-size: 0.6rem; font-weight: 900; text-transform: uppercase; color: var(--secondary); margin-bottom: 0.15rem; }
.modal-nutrition-value { font-family: 'Epilogue', sans-serif; font-size: 1rem; font-weight: 900; }
.modal-add-btn {
  flex: 1;
  background: var(--primary);
  color: var(--on-primary);
  padding: 0.9rem;
  border: 4px solid #000;
  box-shadow: 6px 6px 0px 0px #000;
  font-family: 'Epilogue', sans-serif;
  font-weight: 900;
  text-transform: uppercase;
  font-style: italic;
  font-size: 1.1rem;
  letter-spacing: -0.05em;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: transform 0.1s, box-shadow 0.1s;
}
.modal-add-btn:active { transform: translate(2px,2px); box-shadow: none; }
.modal-cart-btn {
  background: var(--secondary);
  color: #fff;
  border: 4px solid #000;
  box-shadow: 6px 6px 0px 0px #000;
  width: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
}
.modal-cart-btn:hover { background: var(--tertiary-fixed); color: #000; }

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
      <a href="userDashboard.php" class="logo">Annyeong</a>
      <nav>
        <a href="userDashboard.php" class="active">Dashboard</a>
        <a href="wishlistCart.php">Wishlist</a>
        <a href="myOrders.php">My Orders</a>
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

<main>
  <section class="hero">
    <div class="hero-halftone"></div>
    <div class="hero-content">
      <h1>New Drops<br/>Are Here</h1>
      <p class="hero-sub">Limited editions. Infinite drip.</p>
    </div>
    <div class="sticker-badge">
      <span>FREE<br/>SHIPPING</span>
    </div>
  </section>

  <div class="search-filter">
    <div class="search-wrap">
      <span class="material-symbols-outlined search-icon">search</span>
      <input placeholder="SEARCH LIMITED EDITIONS..." type="text"/>
    </div>
    <div class="select-wrap">
      <select>
        <option selected>Category: All</option>
        <option>Apparel</option>
        <option>Tech</option>
        <option>Accessories</option>
      </select>
      <span class="material-symbols-outlined select-icon">expand_more</span>
    </div>
  </div>

  <section class="product-grid">
    <?php
    // ── Card style pool — cycles through styles as products load ──
    $card_styles = [
      'card-featured',
      'card-standard-sq card-secondary',
      'card-standard-sq card-primary-c',
      'card-standard-sq card-surface'
    ];
    $card_index = 0;

    // ── $products should be passed in from your controller/query ──
    // Expected keys: product_id, name, subtitle, price, image, badge, category, stock, rating, sku
    if (!empty($products)) :
      foreach ($products as $product) :
        $style      = $card_styles[$card_index % count($card_styles)];
        $card_index++;
        $img_url    = htmlspecialchars(IMG_PATH . $product['image']);
        $name       = htmlspecialchars($product['name']);
        $subtitle   = htmlspecialchars($product['subtitle'] ?? '');
        $price      = htmlspecialchars($product['price']);
        $badge      = htmlspecialchars($product['badge'] ?? 'NEW');
        $badge_class = (strtolower($badge) === 'ultra rare') ? 'card-badge-rare' : 'card-badge-new';
        $category   = htmlspecialchars($product['category'] ?? '—');
        $stock      = htmlspecialchars($product['stock']    ?? '—');
        $rating     = htmlspecialchars($product['rating']   ?? '—');
        $sku        = htmlspecialchars($product['sku']      ?? '—');
        $pid        = (int) $product['product_id'];
    ?>
    <div class="card <?= $style ?>" data-product-id="<?= $pid ?>">
      <div class="card-halftone"></div>
      <div class="card-img-wrap">
        <img alt="<?= $name ?>" src="<?= $img_url ?>"/>
        <span class="<?= $badge_class ?>"><?= $badge ?></span>
      </div>
      <div class="card-footer">
        <div>
          <h3 class="card-title"><?= $name ?></h3>
          <p class="card-subtitle"><?= $subtitle ?></p>
        </div>
        <div class="price-tag-sm"><?= $price ?></div>
      </div>
      <button class="btn-primary btn-full" style="width:100%;"
        onclick="openModal(
          <?= $pid ?>,
          <?= json_encode($name) ?>,
          <?= json_encode($price) ?>,
          <?= json_encode(IMG_PATH . $product['image']) ?>,
          <?= json_encode($subtitle) ?>,
          <?= json_encode($badge) ?>,
          <?= json_encode(strtoupper($badge)) ?>,
          <?= json_encode($category) ?>,
          <?= json_encode($stock) ?>,
          <?= json_encode($rating) ?>,
          <?= json_encode($sku) ?>
        )">
        <span class="material-symbols-outlined">visibility</span> VIEW DETAILS
      </button>
    </div>
    <?php
      endforeach;
    else :
    ?>
      <p style="grid-column:1/-1; text-align:center; font-weight:700; padding:3rem;">
        No products found.
      </p>
    <?php endif; ?>
  </section>
</main>

<!--product details modal pop-up-->
<div class="modal-overlay" id="productModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal()">
      <span class="material-symbols-outlined">close</span>
    </button>
    <div class="modal-product-grid">
      <div class="modal-image-panel">
        <div class="modal-image-panel-bg"></div>
        <div class="modal-image-panel-inner">
          <img alt="Product" id="modal-img" src=""/>
          <div class="modal-sticker-spice" id="modal-sticker">ULTRA RARE</div>
        </div>
      </div>
      <div class="modal-content-panel">
        <div>
          <div class="modal-product-badge" id="modal-badge">FEATURED</div>
          <h1 class="modal-product-title" id="modal-title">Product Name</h1>
          <div class="modal-price-row">
            <span class="modal-price" id="modal-price">$0.00</span>
            <span class="modal-free-ship">FREE SHIPPING</span>
          </div>
        </div>
        <div class="modal-narrative">
          <div class="modal-narrative-label">THE NARRATIVE</div>
          <p id="modal-desc">Description here.</p>
        </div>
        <div class="modal-nutrition-grid">
          <div class="modal-nutrition-card"><span class="modal-nutrition-label">Category</span><span class="modal-nutrition-value" id="modal-cat">—</span></div>
          <div class="modal-nutrition-card"><span class="modal-nutrition-label">Stock</span><span class="modal-nutrition-value" id="modal-stock">—</span></div>
          <div class="modal-nutrition-card"><span class="modal-nutrition-label">Rating</span><span class="modal-nutrition-value" id="modal-rating">—</span></div>
          <div class="modal-nutrition-card"><span class="modal-nutrition-label">SKU</span><span class="modal-nutrition-value" id="modal-sku">—</span></div>
        </div>
        <div style="display:flex; gap:0.75rem;">
          <button class="modal-add-btn">
            <span class="material-symbols-outlined">payment</span> CHECKOUT
          </button>
          <button class="modal-cart-btn">
            <span class="material-symbols-outlined">shopping_cart</span>
          </button>
        </div>
      </div>
    </div>
    <div class="modal-crosssell">
      <h2 class="modal-crosssell-title">YOU MIGHT ALSO LIKE</h2>
      <div class="modal-crosssell-underline"></div>
      <div class="modal-crosssell-grid">
        <div class="modal-crosssell-card">
          <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCfpG6UZAN9QKsZc_eJVjAC80rU1CcF4FC0IHs05EZUDJhV-ZA5NAIBsn0_UQaG7KAQqWX4NbQLR11o0rkcq9gjwgzfAfVZ8jeBCF-ezOCIJd7gfUdDXXZjuP5jt278aikWvGxXq_JeBxb41C13F_ERxYmKuq3Fpsn8rUSHneqKMOGoyO8sqS7Skzeslpp7_G9L5qhBtswpuZkQ3xTvR8BN94m0dbql_56eUg5o_VakEHxhT94PcDl82nYKTrJURm4aEYQOQn-NWNE" alt="Neon Wasabi Chips"/>
          <div class="modal-crosssell-card-name">Neon Wasabi Chips</div>
          <div class="modal-crosssell-card-price">₩3,200</div>
        </div>
        <div class="modal-crosssell-card">
          <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCjhlDn6YHsmAjatj2PWBKKY5uUwnZX8iboIPldwx6t-sX3AhHAR4wQuqLqtJxIIv6dO2NXFzdC_D_t8vsvxlC-tvZ3G5g1U8r_E7yZ_6rSahYBNMPG0n5_u0-a8T-EVlSejWLZyCIN7QpuDlpnyHWz0Wjmq7miOy6i9BwiknWwhbbDxcMR24vJUaye1nh6Xlv9HBiRzoLGRyd9UKtcgpLDa-G1Kkho2nRdj5dFGzwP5m3W03G_lNEPwRheDjRwa6WL83oFdV04duA" alt="Voltage Soda"/>
          <div class="modal-crosssell-card-name">Voltage Soda</div>
          <div class="modal-crosssell-card-price">₩2,500</div>
        </div>
        <div class="modal-crosssell-card">
          <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAWc_h4FqzupM6JHHdHiZkuu965M6423OEYCfJMbtzf-UT4Gov4uXiQetxlWcP6GxcuL8Eqc9CuncZJOTP6qtTAS-dXFNjtscPmoJzmp49pCYO425jsqEDZt9aMvO0Tcue1G2cWm01l07oVdyOOEwCu9tmLRRVYi_qztERs0DflKpQVJUgFuCWhuGTWMbr0R7Vz34UK4OuQFXFo65k6_8N1f3Tpyb6-ceNe4EJgrDFkr4tcVJTxK40ZXDQr6PkNF0o55Pdw2ogeXSQ" alt="Turbo Protein Bar"/>
          <div class="modal-crosssell-card-name">Turbo Protein Bar</div>
          <div class="modal-crosssell-card-price">₩4,000</div>
        </div>
        <div class="modal-crosssell-card">
          <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCQLy2kPxh8NSHxXUgBs1FtnGc6ap1zqe3wOzBnpDE-NoGR6981rMvkoIrFOfsjZd-9prPIJU0qUeYTkV0OyhzqSmv2hLgA3sBIFTjH76_ZEAdwkT_1EUbC8I1c3etm-XePTgcImIn3dWjTvVFbeuIwOrgSpi7AKTQFlutX5JZukLdjWZzBK6vgkHHP5lsMDQgB2DgEczoINxahaSbF4uYmwk4zQ7AuGV8xX_eYMpgm2SfieqcIBJO_uRkSRx-i_gqEBbmtQHx6klM" alt="Slurp Squad Tee"/>
          <div class="modal-crosssell-card-name">Slurp Squad Tee</div>
          <div class="modal-crosssell-card-price">₩35,000</div>
        </div>
      </div>
    </div>
  </div>
</div>

  </div>
</div>

<footer>
  <div class="footer-brand">
    <span class="footer-brand-name">Annyeong</span>
    <span class="footer-rights">&copy; 2025 Annyeong Market.<br/>All rights reserved.</span>
  </div>
  <ul class="footer-links">
    <li><a href="userDashboard.php">Dashboard</a></li>
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

  function openModal(productId, title, price, img, desc, badge, sticker, cat, stock, rating, sku) {
  // Store product_id on the modal so backend/AJAX can read it
  document.getElementById('productModal').dataset.productId = productId;

  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal-price').textContent = price;
  document.getElementById('modal-img').src = img;
  document.getElementById('modal-img').alt = title;
  document.getElementById('modal-desc').textContent = desc;
  document.getElementById('modal-badge').textContent = badge;
  document.getElementById('modal-sticker').textContent = sticker;
  document.getElementById('modal-cat').textContent = cat;
  document.getElementById('modal-stock').textContent = stock;
  document.getElementById('modal-rating').textContent = rating;
  document.getElementById('modal-sku').textContent = sku;
  document.getElementById('productModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  document.getElementById('productModal').classList.remove('open');
  document.body.style.overflow = '';
}
document.getElementById('productModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>
</body>
</html>