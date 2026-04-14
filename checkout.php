<?php
// BOOTSTRAP & SESSION
session_start();
require_once 'includes/db_connection.php';

// -- Auth guard (adjust to match your project's session key) --
if (!isset($_SESSION['user_id'])) {
    header('Location: login_register.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Guest';
$role     = $_SESSION['role']     ?? 'Customer';
$user_id  = $_SESSION['user_id']  ?? 0;

// CONSTANTS
if (!defined('IMG_PATH')) {
    define('IMG_PATH', 'https://res.cloudinary.com/ds3irzr48/image/upload/q_auto/f_auto/');
}

// FETCH WISHLIST ITEMS
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

// If no items in wishlist, redirect back
if (empty($wishlist_items)) {
    header('Location: wishlistCart.php?error=empty_wishlist');
    exit;
}

// Calculate totals
$total_items = array_sum(array_map(fn($i) => $i['quantity'], $wishlist_items));
$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $wishlist_items));
$shipping = $subtotal > 0 ? 150 : 0;
$discount = min(200, floor($subtotal * 0.10)); // 10% discount, max ₱200
$final_total = max(0, $subtotal + $shipping - $discount);

// For title and pricing used in the page
$product_name  = 'Wishlist Order (' . $total_items . ' items)';
$product_price = number_format($final_total, 2);

// STEP STATE
// 1 = Drop Zone | 2 = Payment | 3 = Success
$current_step = 2;

// FORM SUBMISSION  (Place Order — POST)
$order_error  = '';
$order_id     = '';
$order_placed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {

    $payment_method  = $_POST['payment_method']  ?? '';
    $tendered_raw    = $_POST['tendered_amount']  ?? '';
    $tendered_amount = (float)$tendered_raw;
    $expected        = $final_total;

    // Round both to 2 decimal places to avoid float drift
    $tendered_rounded = round($tendered_amount, 2);
    $expected_rounded = round($expected, 2);

    if (empty($payment_method)) {
        $order_error = 'Please select a payment method before placing your order.';
    } elseif ($tendered_raw === '' || !is_numeric($tendered_raw)) {
        $order_error = 'Please enter the amount to pay inside the payment modal.';
    } elseif ($tendered_rounded < $expected_rounded) {
        $diff = number_format($expected_rounded - $tendered_rounded, 2);
        $order_error = "Amount is short by ₱{$diff}. Please enter the exact total of ₱{$product_price}.";
    } elseif ($tendered_rounded > $expected_rounded) {
        $diff = number_format($tendered_rounded - $expected_rounded, 2);
        $order_error = "Amount exceeds the total by ₱{$diff}. Please enter the exact total of ₱{$product_price}.";
    } else {
        // ✅ Validation passed - Save order to database
        $status = 'pending';
        $insert_order = $con->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, ?)");
        $insert_order->bind_param('ids', $user_id, $final_total, $status);

        if ($insert_order->execute()) {
            $order_db_id = $insert_order->insert_id;
            $insert_order->close();

            // Insert order items
            $insert_items = $con->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
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
            $current_step = 3;
            $order_id = '#' . str_pad($order_db_id, 6, '0', STR_PAD_LEFT);
        } else {
            $order_error = 'Error placing order. Please try again.';
        }
    }
}
// HELPERS — Progress Bar
function step_class(int $step, int $current): string {
    if ($step < $current)  return 'done';
    if ($step === $current) return 'active';
    return '';
}
function line_class(int $after_step, int $current): string {
    return $after_step < $current ? 'done' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Checkout — <?= $product_name ?></title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,900;1,900&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
  :root {
    --inverse-primary: #ff4e7c; --outline: #757778; --surface-tint: #b70048;
    --on-secondary-fixed: #004749; --on-primary: #ffeff0; --secondary: #006668;
    --on-background: #2c2f30; --primary: #b70048; --inverse-on-surface: #9b9d9e;
    --error-container: #fb5151; --surface-container-highest: #dadddf;
    --surface-container-high: #e0e3e4; --secondary-dim: #00595b;
    --on-tertiary-container: #5b4c00; --tertiary-fixed: #fdd828;
    --on-primary-container: #4d001a; --on-tertiary: #fff2cc; --primary-dim: #a1003f;
    --secondary-fixed-dim: #3ceaee; --surface-dim: #d1d5d7;
    --secondary-container: #52f9fc; --on-surface-variant: #595c5d;
    --surface-container-lowest: #ffffff; --on-error: #ffefee;
    --primary-container: #ff7290; --on-secondary-container: #005b5d;
    --error: #b31b25; --secondary-fixed: #52f9fc; --primary-fixed: #ff7290;
    --error-dim: #9f0519; --surface-container-low: #eff1f2; --tertiary: #6c5a00;
    --surface-bright: #f5f6f7; --tertiary-container: #fdd828; --surface: #f5f6f7;
    --on-error-container: #570008; --outline-variant: #abadae; --on-surface: #2c2f30;
    --background: #f5f6f7; --surface-container: #e6e8ea;
    --on-primary-fixed-variant: #5f0022; --surface-variant: #dadddf;
    --on-primary-fixed: #000000; --tertiary-fixed-dim: #eeca12;
    --inverse-surface: #0c0f10; --on-tertiary-fixed: #453900;
    --on-secondary: #c0feff; --primary-fixed-dim: #ff557f;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background-color: var(--background);
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--on-background);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-image: radial-gradient(#000000 1px, transparent 0);
    background-size: 8px 8px;
  }

  ::selection { background-color: var(--tertiary-container); color: var(--on-tertiary-container); }

  .material-symbols-outlined {
    font-family: 'Material Symbols Outlined';
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    font-size: inherit; line-height: 1; vertical-align: middle; user-select: none;
  }

  .halftone-bg { background-image: radial-gradient(#000000 1px, transparent 0); background-size: 8px 8px; opacity: 0.1; }
  .comic-panel-shadow { box-shadow: 6px 6px 0px 0px #000000; }
  .text-stroke-sm { -webkit-text-stroke: 1px black; }
  .text-stroke-md { -webkit-text-stroke: 2px black; }

  /* ── Header ── */
  header { background: #ffffff; width: 100%; border-bottom: 4px solid #000000; position: sticky; top: 0; z-index: 50; }
  .header-inner { display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 1rem 2.5rem; }
  .logo { font-family: 'Epilogue', serif; font-size: 1.875rem; font-weight: 900; font-style: italic; letter-spacing: -0.05em; color: #000000; text-shadow: 4px 4px 0px #fdd828; text-decoration: none; flex-shrink: 0; }
  .header-left-group { display: flex; align-items: baseline; gap: 3rem; }
  nav { display: flex; gap: 2rem; align-items: center; background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; }
  nav a { font-family: 'Epilogue', serif; font-weight: 900; text-transform: uppercase; letter-spacing: -0.05em; color: #000000; text-decoration: none; transition: color 0.15s, transform 0.15s; white-space: nowrap; }
  nav a:hover { color: var(--primary); transform: skewX(-2deg) translateY(-2px); }
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
  .dropdown-logout { display: flex; align-items: center; gap: 10px; padding: 14px 20px; font-weight: 900; font-style: italic; font-size: 0.9rem; text-transform: uppercase; color: #000000; text-decoration: none; background: var(--primary); border-top: 2px solid #000; transition: background 0.1s; }
  .dropdown-logout:hover { background: var(--tertiary-container); color: #000; }

  /* ── Main ── */
  main { flex-grow: 1; max-width: 64rem; margin: 0 auto; width: 100%; padding: 3rem 2rem; }

  /* ── Step Progress Bar ── */
  .step-progress { margin-bottom: 2.5rem; }
  .step-track { display: flex; align-items: center; position: relative; }
  .step-line { flex: 1; height: 4px; background-color: var(--surface-container-highest); border-top: 3px solid #000000; border-bottom: 3px solid #000000; position: relative; z-index: 0; }
  .step-line.done { background-color: var(--primary); }
  .step-node { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 1; flex-shrink: 0; }
  .step-circle { width: 2.5rem; height: 2.5rem; border: 3px solid #000000; background: var(--surface-container-highest); display: flex; align-items: center; justify-content: center; font-family: 'Epilogue', sans-serif; font-weight: 900; font-style: italic; font-size: 0.9rem; color: var(--on-surface-variant); box-shadow: 3px 3px 0px 0px #000000; transition: background 0.2s, color 0.2s; }
  .step-node.done   .step-circle { background: var(--primary); color: #ffffff; box-shadow: 3px 3px 0px 0px #006668; }
  .step-node.active .step-circle { background: var(--tertiary-fixed); color: #000000; box-shadow: 3px 3px 0px 0px #000000; }
  .step-label { margin-top: 0.4rem; font-family: 'Epilogue', sans-serif; font-size: 0.65rem; font-weight: 900; font-style: italic; text-transform: uppercase; letter-spacing: 0.04em; color: var(--on-surface-variant); white-space: nowrap; }
  .step-node.done   .step-label { color: var(--primary); }
  .step-node.active .step-label { color: #000000; }

  /* ── Sections ── */
  .checkout-sections { display: flex; flex-direction: column; gap: 2rem; }
  section { padding: 1rem; border: 4px solid #000000; }
  .section-bg-surface { background-color: var(--surface); }
  .section-bg-tertiary { background-color: var(--tertiary-container); position: relative; }
  .section-heading { font-family: 'Epilogue', sans-serif; font-size: 1.1rem; font-style: italic; font-weight: 900; text-transform: uppercase; -webkit-text-stroke: 1px black; color: #ffffff; padding: 0.15rem 0.75rem; margin-bottom: 1rem; display: inline-block; }
  .section-heading-pink { background-color: var(--secondary); transform: rotate(1deg); }
  .section-heading-red  { background-color: var(--primary);   transform: rotate(-1deg); }

  /* ── Address fields ── */
  .field-group { display: grid; grid-template-columns: 1fr; gap: 0.85rem; }
  .field { display: flex; flex-direction: column; gap: 0.25rem; }
  .field label { font-family: 'Epilogue', sans-serif; text-transform: uppercase; font-size: 0.75rem; font-weight: 900; font-style: italic; }
  .field input, .field-half input { width: 100%; background-color: var(--surface-container-lowest); border: 3px solid #000000; padding: 0.45rem 0.6rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.8rem; outline: none; transition: box-shadow 0.15s; }
  .field input:focus, .field-half input:focus { box-shadow: 0 0 0 4px var(--tertiary-fixed); }
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .field-half { display: flex; flex-direction: column; gap: 0.25rem; }
  .field-half label { font-family: 'Epilogue', sans-serif; text-transform: uppercase; font-size: 0.75rem; font-weight: 900; font-style: italic; }

  /* ── Loot items ── */
  .loot-list { display: flex; flex-direction: column; gap: 1.5rem; }
  .loot-item { display: flex; align-items: center; gap: 0.75rem; background-color: var(--surface-container-low); border: 2px solid #000000; padding: 0.5rem; position: relative; transition: transform 0.15s; }
  .loot-item:hover { transform: translateY(-4px); }
  .loot-img-wrap { width: 3.5rem; height: 3.5rem; border: 2px solid #000000; flex-shrink: 0; overflow: hidden; background-color: var(--primary-container); }
  .loot-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
  .loot-info { flex-grow: 1; }
  .loot-name { font-family: 'Epilogue', sans-serif; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; font-style: italic; line-height: 1.2; }
  .loot-qty { font-weight: 700; font-size: 0.65rem; color: var(--on-surface-variant); margin-top: 0.15rem; }
  .loot-price { font-family: 'Epilogue', sans-serif; font-weight: 900; font-style: italic; font-size: 0.9rem; text-align: right; }

  /* ── Special request ── */
  .special-badge { position: absolute; top: -1rem; right: 1rem; background: #ffffff; border: 2px solid #000000; padding: 0.25rem 0.5rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 900; font-size: 0.625rem; text-transform: uppercase; transform: rotate(-2deg); }
  .section-heading-tertiary { font-family: 'Epilogue', sans-serif; font-size: 1rem; font-style: italic; font-weight: 900; text-transform: uppercase; margin-bottom: 0.75rem; }
  textarea { width: 100%; background-color: var(--surface-container-lowest); border: 3px solid #000000; padding: 0.5rem 0.6rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.8rem; outline: none; resize: none; transition: box-shadow 0.15s; }
  textarea:focus { box-shadow: 0 0 0 4px var(--primary); }
  textarea::placeholder { color: var(--outline); }

  /* ── Payment options ── */
  .payment-list { display: flex; flex-direction: row; gap: 1rem; }
  .payment-option { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem; padding: 1.25rem 0.75rem; border: 3px solid #000000; cursor: pointer; background: #ffffff; transition: background-color 0.15s, transform 0.1s, box-shadow 0.1s; box-shadow: 4px 4px 0px 0px #000000; user-select: none; }
  .payment-option:hover { background-color: var(--surface-container-low); transform: translate(1px, 1px); box-shadow: 2px 2px 0px 0px #000000; }
  .payment-option.selected { background-color: var(--primary); transform: translate(2px, 2px); box-shadow: none; }
  .payment-option.selected .payment-icon,
  .payment-option.selected .payment-label { color: #ffffff; }
  .payment-option input[type="radio"] { display: none; }
  .payment-icon { font-size: 2rem; color: var(--primary); transition: color 0.15s; }
  .payment-label { font-family: 'Epilogue', sans-serif; text-transform: uppercase; font-weight: 900; font-style: italic; font-size: 0.85rem; text-align: center; transition: color 0.15s; }

  /* ── PHP Error banner ── */
  .error-banner { background: #fff0f2; border: 3px solid var(--error); padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.6rem; box-shadow: 4px 4px 0px 0px var(--error); margin-bottom: 1.5rem; }
  .error-banner .material-symbols-outlined { color: var(--error); font-size: 1.3rem; font-variation-settings: 'FILL' 1; flex-shrink: 0; }
  .error-banner p { font-family: 'Epilogue', sans-serif; font-weight: 900; font-style: italic; font-size: 0.85rem; color: var(--error); text-transform: uppercase; }

  /* ── Tendered input ── */
  .tendered-input { width: 100%; background: #ffffff; border: 3px solid #000000; padding: 0.5rem 0.65rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.9rem; outline: none; transition: box-shadow 0.15s; }
  .tendered-input:focus { box-shadow: 0 0 0 3px var(--tertiary-fixed); }
  .tendered-input.invalid { border-color: var(--error); box-shadow: 0 0 0 3px rgba(179,27,37,0.15); }
  .tendered-hint { font-size: 0.68rem; font-weight: 700; color: var(--on-surface-variant); }
  .tendered-hint.err { color: var(--error); }

  /* ── Payment Modal ── */
  .payment-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 200; align-items: center; justify-content: center; }
  .payment-modal-overlay.open { display: flex; }
  .payment-modal { background: #ffffff; border: 4px solid #000000; box-shadow: 10px 10px 0px 0px #000000; width: 100%; max-width: 28rem; margin: 1rem; position: relative; animation: modalPop 0.2s ease; }
  @keyframes modalPop { from { transform: scale(0.92) translateY(12px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
  .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border-bottom: 4px solid #000000; }
  .modal-header-left { display: flex; align-items: center; gap: 0.6rem; }
  .modal-title { font-family: 'Epilogue', sans-serif; font-size: 1rem; font-weight: 900; font-style: italic; text-transform: uppercase; color: #ffffff; }
  .modal-title-bg-card   { background: var(--primary);  padding: 0.1rem 0.6rem; }
  .modal-title-bg-wallet { background: var(--secondary); padding: 0.1rem 0.6rem; }
  .modal-close { background: #000000; border: none; color: #ffffff; width: 2rem; height: 2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; transition: background 0.1s; }
  .modal-close:hover { background: var(--primary); }
  .modal-body { padding: 1.25rem 1rem; display: flex; flex-direction: column; gap: 0.85rem; }
  .modal-field { display: flex; flex-direction: column; gap: 0.25rem; }
  .modal-field label { font-family: 'Epilogue', sans-serif; font-size: 0.7rem; font-weight: 900; font-style: italic; text-transform: uppercase; }
  .modal-field input { width: 100%; background: var(--surface-container-low); border: 3px solid #000000; padding: 0.5rem 0.65rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.85rem; outline: none; transition: box-shadow 0.15s; }
  .modal-field input:focus { box-shadow: 0 0 0 3px var(--tertiary-fixed); }
  .modal-field input::placeholder { color: var(--outline); font-weight: 400; }
  .modal-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
  .modal-footer { padding: 0 1rem 1rem; }
  .modal-pay-btn { width: 100%; background: var(--primary); color: #ffffff; border: 3px solid #000000; box-shadow: 5px 5px 0px 0px #006668; font-family: 'Epilogue', sans-serif; font-size: 0.95rem; font-weight: 900; font-style: italic; text-transform: uppercase; padding: 0.7rem 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: transform 0.1s, box-shadow 0.1s; }
  .modal-pay-btn:hover { transform: translate(2px, 2px); box-shadow: 2px 2px 0px 0px #006668; }
  .modal-pay-btn:active { transform: scale(0.97); box-shadow: none; }

  /* ── Final Damage ── */
  .final-damage { background-color: #fdd828; padding: 1.25rem; border: 6px solid #000000; box-shadow: 10px 10px 0px 0px #000000; position: relative; overflow: hidden; }
  .final-damage-inner { position: relative; z-index: 1; }
  .damage-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; }
  .damage-row span:first-child { font-family: 'Epilogue', sans-serif; text-transform: uppercase; font-style: italic; font-weight: 700; font-size: 0.85rem; }
  .damage-row span:last-child  { font-family: 'Epilogue', sans-serif; font-style: italic; font-weight: 900; font-size: 1rem; }
  .damage-divider { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 4px dashed #000000; }
  .damage-divider span:first-child { font-family: 'Epilogue', sans-serif; text-transform: uppercase; font-style: italic; font-weight: 700; font-size: 0.85rem; }
  .damage-divider span:last-child  { font-family: 'Epilogue', sans-serif; font-style: italic; font-weight: 900; font-size: 1rem; }
  .final-total-row { display: flex; justify-content: space-between; align-items: flex-end; }
  .final-total-label h2 { font-family: 'Epilogue', sans-serif; font-size: 1.6rem; font-style: italic; font-weight: 900; text-transform: uppercase; -webkit-text-stroke: 2px black; color: #ffffff; text-shadow: 2px 2px 0px #000000; }
  .final-total-label p { font-weight: 700; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 0.2rem; }
  .final-total-amount { font-family: 'Epilogue', sans-serif; font-style: italic; font-weight: 900; font-size: 2.2rem; color: var(--primary); text-shadow: 4px 4px 0px #000000; text-align: right; }

  /* ── Checkout button ── */
  .checkout-btn { width: 100%; background-color: var(--primary); color: #ffffff; font-family: 'Epilogue', sans-serif; font-size: 1.1rem; font-style: italic; font-weight: 900; text-transform: uppercase; padding: 0.85rem 1rem; border: 4px solid #000000; box-shadow: 8px 8px 0px 0px #006668; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.6rem; transition: transform 0.1s, box-shadow 0.1s; }
  .checkout-btn:hover { transform: translate(1px, 1px); box-shadow: none; }
  .checkout-btn:active { transform: scale(0.95); }
  .checkout-btn .btn-icon { font-size: 1.5rem; font-variation-settings: 'FILL' 1; }
  @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
  .checkout-btn:hover .btn-icon { animation: bounce 0.6s infinite; }

  /* ── Footer ── */
  footer { background: #000000; border-top: 4px solid #000000; padding: 20px 32px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
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

  /* ── Success Section ── */
  #success-section { display: flex; flex-direction: column; align-items: center; width: 100%; animation: successFadeIn 0.5s ease; }
  @keyframes successFadeIn { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
  .success-hero-wrapper { position: relative; width: 100%; margin-bottom: 2rem; }
  .success-hero-bg { position: absolute; inset: 0; background-color: var(--primary); border: 4px solid #000; border-radius: 12px; transform: rotate(1deg); z-index: 0; background-image: radial-gradient(#000 10%, transparent 11%); background-size: 10px 10px; opacity: 0.85; }
  .success-hero-card { position: relative; background-color: var(--tertiary-fixed); border: 4px solid #000; padding: 3rem 1.5rem; text-align: center; transform: rotate(-1deg); box-shadow: 6px 6px 0px 0px #000000; z-index: 1; border-radius: 4px; }
  .success-hero-card h1 { font-family: 'Epilogue', sans-serif; font-weight: 900; font-size: 2.25rem; font-style: italic; text-transform: uppercase; color: #fff; -webkit-text-stroke: 1.5px #000; text-shadow: 4px 4px 0px var(--primary); margin-bottom: 8px; line-height: 1.1; }
  .success-hero-card p { font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; color: var(--on-primary-fixed); }
  .success-boom-sticker { position: absolute; top: -24px; right: -8px; transform: rotate(12deg); background: #fff; border: 2px solid #000; padding: 8px; border-radius: 9999px; box-shadow: 2px 2px 0px 0px #000; z-index: 2; }
  .success-boom-sticker span { font-family: 'Epilogue', sans-serif; font-weight: 900; font-size: 1.25rem; color: var(--primary); font-style: italic; display: block; }
  .success-order-badge { width: 100%; margin-bottom: 2rem; background: #fff; border: 4px solid #000; padding: 12px 16px; box-shadow: 4px 4px 0px 0px #000; text-align: center; }
  .success-order-badge__label { font-weight: 700; color: var(--secondary); text-transform: uppercase; display: block; font-size: 0.75rem; margin-bottom: 4px; letter-spacing: 0.05em; }
  .success-order-badge__value { font-family: 'Epilogue', sans-serif; font-weight: 900; font-size: 1.25rem; }
  .success-loot-summary { width: 100%; background: #fff; border: 4px solid #000; padding: 1.25rem; box-shadow: 6px 6px 0px 0px #000; margin-bottom: 2rem; }
  .success-loot-title { font-family: 'Epilogue', sans-serif; font-weight: 900; font-size: 1.25rem; text-transform: uppercase; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px; }
  .success-item-list { display: flex; flex-direction: column; gap: 12px; }
  .success-item-row { display: flex; align-items: center; gap: 12px; padding: 12px; border: 2px solid #000; background-color: var(--surface-container-low); }
  .success-item-img { width: 56px; height: 56px; border: 2px solid #000; overflow: hidden; flex-shrink: 0; background-color: var(--primary-container); }
  .success-item-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .success-item-info { flex-grow: 1; min-width: 0; }
  .success-item-info__name { font-weight: 700; text-transform: uppercase; font-size: 0.875rem; }
  .success-item-info__qty { font-size: 0.75rem; font-weight: 500; color: var(--on-surface-variant); text-transform: uppercase; }
  .success-item-price { font-family: 'Epilogue', sans-serif; font-weight: 900; font-size: 1.1rem; white-space: nowrap; }
  .success-loot-total { margin-top: 1.5rem; padding-top: 1rem; border-top: 4px dashed #000; display: flex; justify-content: space-between; align-items: center; }
  .success-loot-total__label { font-weight: 700; font-size: 0.875rem; text-transform: uppercase; }
  .success-loot-total__value { font-family: 'Epilogue', sans-serif; font-weight: 900; font-size: 1.5rem; color: var(--primary); }
  .success-cta-btn { width: 100%; background-color: var(--secondary); color: #fff; font-family: 'Epilogue', sans-serif; font-weight: 900; font-size: 0.95rem; font-style: italic; text-transform: uppercase; padding: 0.85rem 1rem; border: 4px solid #000; box-shadow: 6px 6px 0px 0px #000; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: transform 0.1s, box-shadow 0.1s; text-decoration: none; }
  .success-cta-btn:hover { transform: translate(2px, 2px); box-shadow: 3px 3px 0px 0px #000; }
</style>
</head>
<body>

<header>
  <div class="header-inner">
    <div class="header-left-group">
      <a href="userDashboard.php" class="logo">Annyeong'Sayo</a>
      <nav>
        <a href="userDashboard.php">Dashboard</a>
        <a href="wishlistCart.php">Wishlist</a>
        <a href="myOrders.php">My Orders</a>
      </nav>
    </div>
    <div style="display:flex;align-items:center;gap:0.75rem;">
      <a href="#" class="profile-trigger" style="background-color:#00595b;">
        <span class="material-symbols-outlined" style="color:#ffffff;">shopping_cart</span>
      </a>
      <div class="profile-trigger-wrap">
        <a href="#" class="profile-trigger" onclick="toggleDropdown(event)">
          <span class="material-symbols-outlined">account_circle</span>
        </a>
        <div class="profile-dropdown" id="profileDropdown">
          <div class="dropdown-user-info">
            <span class="dropdown-username"><?= htmlspecialchars($username) ?></span>
            <span class="dropdown-role"><?= htmlspecialchars($role) ?></span>
            <span class="dropdown-id">#<?= htmlspecialchars($user_id) ?></span>
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
  <div class="step-progress">
    <div class="step-track">

      <div class="step-node <?= step_class(1, $current_step) ?>">
        <div class="step-circle">
          <?php if ($current_step > 1): ?>
            <span class="material-symbols-outlined" style="font-size:1.1rem;font-variation-settings:'FILL' 1;">check</span>
          <?php else: ?>1<?php endif; ?>
        </div>
        <span class="step-label">Drop Zone</span>
      </div>

      <div class="step-line <?= line_class(1, $current_step) ?>"></div>

      <div class="step-node <?= step_class(2, $current_step) ?>">
        <div class="step-circle">
          <?php if ($current_step > 2): ?>
            <span class="material-symbols-outlined" style="font-size:1.1rem;font-variation-settings:'FILL' 1;">check</span>
          <?php else: ?>2<?php endif; ?>
        </div>
        <span class="step-label">Payment</span>
      </div>

      <div class="step-line <?= line_class(2, $current_step) ?>"></div>

      <div class="step-node <?= step_class(3, $current_step) ?>">
        <div class="step-circle">
          <?php if ($current_step > 3): ?>
            <span class="material-symbols-outlined" style="font-size:1.1rem;font-variation-settings:'FILL' 1;">check</span>
          <?php else: ?>3<?php endif; ?>
        </div>
        <span class="step-label">Success</span>
      </div>

    </div>
  </div>

<?php if ($order_placed): ?>
  <!-- success view-->
  <div id="success-section">

    <div class="success-hero-wrapper">
      <div class="success-hero-bg"></div>
      <div class="success-hero-card">
        <h1>HAUL SECURED!</h1>
        <p>Your order is locked and loaded.</p>
        <div class="success-boom-sticker"><span>BOOM!</span></div>
      </div>
    </div>

    <div class="success-order-badge">
      <span class="success-order-badge__label">Order ID</span>
      <span class="success-order-badge__value"><?= htmlspecialchars($order_id) ?></span>
    </div>

    <div class="success-loot-summary">
      <h2 class="success-loot-title">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">inventory_2</span>
        LOOT SUMMARY
      </h2>
      <div class="success-item-list">
        <?php foreach ($wishlist_items as $item): ?>
        <div class="success-item-row">
          <div class="success-item-img">
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"/>
          </div>
          <div class="success-item-info">
            <div class="success-item-info__name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="success-item-info__qty">Qty: <?= (int)$item['quantity'] ?> &times; ₱<?= number_format((float)$item['price']) ?></div>
          </div>
          <span class="success-item-price">₱<?= number_format((float)$item['price'] * (int)$item['quantity']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="success-loot-total">
        <span class="success-loot-total__label">Total Extraction</span>
        <span class="success-loot-total__value">₱<?= number_format($final_total) ?></span>
      </div>
    </div>

    <a href="userDashboard.php" class="success-cta-btn">
      <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">storefront</span>
      Return to Shop
    </a>

  </div>

<?php else: ?>
  <!--checkout form -->

  <?php if ($order_error): ?>
  <div class="error-banner">
    <span class="material-symbols-outlined">error</span>
    <p><?= htmlspecialchars($order_error) ?></p>
  </div>
  <?php endif; ?>

  <form method="POST" action="checkout.php">

    <div class="checkout-sections">

      <section class="section-bg-surface comic-panel-shadow">
        <h2 class="section-heading section-heading-pink">Drop Zone</h2>
        <div class="field-group">
          <div class="field">
            <label>Home Address</label>
            <input type="text" name="address"
                   value="<?= htmlspecialchars($_POST['address'] ?? '123 Neon Street, Apgujeong-ro') ?>"/>
          </div>
          <div class="field-row">
            <div class="field-half">
              <label>Unit/Floor</label>
              <input type="text" name="unit" placeholder="Bldg 4, Fl 12"
                     value="<?= htmlspecialchars($_POST['unit'] ?? '') ?>"/>
            </div>
            <div class="field-half">
              <label>City Code</label>
              <input type="text" name="city_code"
                     value="<?= htmlspecialchars($_POST['city_code'] ?? '06000') ?>"/>
            </div>
          </div>
        </div>
      </section>

      <section class="section-bg-surface comic-panel-shadow">
        <h2 class="section-heading section-heading-pink">Order Review</h2>
        <div class="loot-list">
          <?php foreach ($wishlist_items as $item): ?>
          <div class="loot-item">
            <div class="loot-img-wrap">
              <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"/>
            </div>
            <div class="loot-info">
              <div class="loot-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="loot-qty">Qty: <?= (int)$item['quantity'] ?> × ₱<?= number_format((float)$item['price']) ?></div>
            </div>
            <div class="loot-price">₱<?= number_format((float)$item['price'] * (int)$item['quantity']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="section-bg-tertiary comic-panel-shadow">
        <div class="special-badge">SPECIAL REQUEST?</div>
        <h2 class="section-heading-tertiary">Note for the Mart</h2>
        <textarea name="seller_note" rows="3"
                  placeholder="LEAVE AT THE BODEGA"><?= htmlspecialchars($_POST['seller_note'] ?? '') ?></textarea>
      </section>

      <section class="section-bg-surface comic-panel-shadow">
        <h2 class="section-heading section-heading-red">Payment Method</h2>
        <div class="payment-list">

          <?php $selected_method = $_POST['payment_method'] ?? ''; ?>

          <div class="payment-option <?= $selected_method === 'card'   ? 'selected' : '' ?>"
               id="btn-card"
               onclick="openModal('card'); selectPayment('card')">
            <input type="radio" name="payment_method" value="card"
                   <?= $selected_method === 'card'   ? 'checked' : '' ?>/>
            <span class="material-symbols-outlined payment-icon" style="font-variation-settings:'FILL' 1;">credit_card</span>
            <span class="payment-label">Card</span>
          </div>

          <div class="payment-option <?= $selected_method === 'wallet' ? 'selected' : '' ?>"
               id="btn-wallet"
               onclick="openModal('wallet'); selectPayment('wallet')">
            <input type="radio" name="payment_method" value="wallet"
                   <?= $selected_method === 'wallet' ? 'checked' : '' ?>/>
            <span class="material-symbols-outlined payment-icon" style="font-variation-settings:'FILL' 1;">account_balance_wallet</span>
            <span class="payment-label">Online Wallet</span>
          </div>

        </div>
      </section>

      <div class="payment-modal-overlay" id="modal-card">
        <div class="payment-modal">
          <div class="modal-header">
            <div class="modal-header-left">
              <span class="material-symbols-outlined" style="font-size:1.4rem;font-variation-settings:'FILL' 1;color:var(--primary);">credit_card</span>
              <span class="modal-title modal-title-bg-card">Card Payment</span>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('card')">
              <span class="material-symbols-outlined" style="font-size:1.1rem;">close</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="modal-field">
              <label>Cardholder Name</label>
              <input type="text" name="card_name" placeholder="e.g. Juan dela Cruz"
                     value="<?= htmlspecialchars($_POST['card_name'] ?? '') ?>"/>
            </div>
            <div class="modal-field">
              <label>Card Number</label>
              <input type="text" name="card_number" placeholder="0000 0000 0000 0000"
                     maxlength="19" id="card-number-input"
                     value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>"/>
            </div>
            <div class="modal-field-row">
              <div class="modal-field">
                <label>Expiry Date</label>
                <input type="text" name="card_expiry" placeholder="MM / YY" maxlength="7"
                       value="<?= htmlspecialchars($_POST['card_expiry'] ?? '') ?>"/>
              </div>
              <div class="modal-field">
                <label>CVV</label>
                <input type="password" name="card_cvv" placeholder="•••" maxlength="4"/>
              </div>
            </div>

            <div class="modal-field">
              <label>
                Amount to Pay
                <small style="font-style:normal;text-transform:none;font-weight:400;opacity:0.7;">
                  (exact: ₱<?= $product_price ?>)
                </small>
              </label>
              <input type="number"
                     name="tendered_amount"
                     id="tendered_amount"
                     class="tendered-input <?= $order_error ? 'invalid' : '' ?>"
                     placeholder="<?= $product_price ?>"
                     step="0.01" min="0"
                     value="<?= htmlspecialchars($_POST['tendered_amount'] ?? '') ?>"/>
              <span class="tendered-hint <?= $order_error ? 'err' : '' ?>">
                <?= $order_error ? htmlspecialchars($order_error) : 'Must match the exact total.' ?>
              </span>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="modal-pay-btn" onclick="closeModal('card')">
              <span class="material-symbols-outlined" style="font-size:1.1rem;font-variation-settings:'FILL' 1;">lock</span>
              Confirm &amp; Close
            </button>
          </div>
        </div>
      </div>

      <div class="payment-modal-overlay" id="modal-wallet">
        <div class="payment-modal">
          <div class="modal-header">
            <div class="modal-header-left">
              <span class="material-symbols-outlined" style="font-size:1.4rem;font-variation-settings:'FILL' 1;color:var(--secondary);">account_balance_wallet</span>
              <span class="modal-title modal-title-bg-wallet">Online Wallet</span>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('wallet')">
              <span class="material-symbols-outlined" style="font-size:1.1rem;">close</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="modal-field">
              <label>Wallet Account Name</label>
              <input type="text" name="wallet_name" placeholder="e.g. Juan dela Cruz"
                     value="<?= htmlspecialchars($_POST['wallet_name'] ?? '') ?>"/>
            </div>
            <div class="modal-field">
              <label>Wallet PIN</label>
              <input type="password" name="wallet_pin" placeholder="Enter your PIN" maxlength="6"/>
            </div>

            <div class="modal-field">
              <label>
                Amount to Pay
                <small style="font-style:normal;text-transform:none;font-weight:400;opacity:0.7;">
                  (exact: ₱<?= $product_price ?>)
                </small>
              </label>
              <input type="number"
                     name="tendered_amount_wallet_display"
                     id="tendered_amount_wallet"
                     class="tendered-input <?= $order_error ? 'invalid' : '' ?>"
                     placeholder="<?= $product_price ?>"
                     step="0.01" min="0"
                     value="<?= htmlspecialchars($_POST['tendered_amount'] ?? '') ?>"
                     oninput="document.getElementById('tendered_amount').value = this.value"/>
              <span class="tendered-hint <?= $order_error ? 'err' : '' ?>">
                <?= $order_error ? htmlspecialchars($order_error) : 'Must match the exact total.' ?>
              </span>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="modal-pay-btn"
                    style="background:var(--secondary);box-shadow:5px 5px 0px 0px #004749;"
                    onclick="closeModal('wallet')">
              <span class="material-symbols-outlined" style="font-size:1.1rem;font-variation-settings:'FILL' 1;">send</span>
              Confirm &amp; Close
            </button>
          </div>
        </div>
      </div>

      <section class="final-damage">
        <div class="halftone-bg" style="position:absolute;inset:0;"></div>
        <div class="final-damage-inner">
          <div class="damage-row">
            <span>Subtotal (<?= $total_items ?> items)</span>
            <span>₱<?= number_format($subtotal) ?></span>
          </div>
          <div class="damage-row">
            <span>Shipping</span>
            <span>₱<?= number_format($shipping) ?></span>
          </div>
          <div class="damage-divider">
            <span>Membership Discount</span>
            <span>-₱<?= number_format($discount) ?></span>
          </div>
          <div class="final-total-row">
            <div class="final-total-label">
              <h2 class="text-stroke-md">Final Total</h2>
              <p>Ready for checkout</p>
            </div>
            <div class="final-total-amount">₱<?= number_format($final_total) ?></div>
          </div>
        </div>
      </section>

      <button type="submit" name="place_order" class="checkout-btn">
        <span class="material-symbols-outlined btn-icon">bolt</span>
        Place Order
      </button>

    </div><!-- /.checkout-sections -->
  </form>

<?php endif; ?>
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
  /* ── Dropdown ── */
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

  /* ── Payment modals ── */
  function openModal(type) {
    document.getElementById('modal-' + type).classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeModal(type) {
    document.getElementById('modal-' + type).classList.remove('open');
    document.body.style.overflow = '';
  }
  document.querySelectorAll('.payment-modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
      }
    });
  });

  /* ── Payment tile selection ── */
  function selectPayment(type) {
    document.querySelectorAll('.payment-option').forEach(function(el) {
      el.classList.remove('selected');
    });
    document.getElementById('btn-' + type).classList.add('selected');
    // Ensure the radio is checked so it is included in the POST
    var radio = document.querySelector('#btn-' + type + ' input[type="radio"]');
    if (radio) radio.checked = true;
  }

  /*
   * After a PHP validation error, re-open the modal the user had selected
   * and pre-select the payment tile, so they don't lose context.
   */
  <?php if ($order_error && !empty($selected_method)): ?>
  (function() {
    var method = <?= json_encode($selected_method) ?>;
    selectPayment(method);
    openModal(method);
  })();
  <?php endif; ?>

  /* ── Card number formatter ── */
  var cardInput = document.getElementById('card-number-input');
  if (cardInput) {
    cardInput.addEventListener('input', function() {
      var v = this.value.replace(/\D/g, '').substring(0, 16);
      this.value = v.replace(/(.{4})/g, '$1 ').trim();
    });
  }
</script>
</body>
</html>