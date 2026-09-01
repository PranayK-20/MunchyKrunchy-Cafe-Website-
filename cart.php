<?php
session_start();
include('Connection.php');

// Initialize cart in session
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart via ?add_item=ID
if (isset($_GET['add_item'])) {
    $item_id = intval($_GET['add_item']);
    $res = mysqli_query($conn, "SELECT * FROM menu WHERE item_id = $item_id LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        if (isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]['qty'] += 1;
        } else {
            $_SESSION['cart'][$item_id] = [
                'id' => $row['item_id'],
                'name' => $row['menu_items'],
                'price' => (float)$row['price'],
                'qty' => 1
            ];
        }
    }
    header('Location: cart.php');
    exit;
}

// Decrease quantity ?decrease=ID
if (isset($_GET['decrease'])) {
    $id = intval($_GET['decrease']);
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty']--;
        if ($_SESSION['cart'][$id]['qty'] <= 0) unset($_SESSION['cart'][$id]);
    }
    header('Location: cart.php');
    exit;
}

// Remove item ?remove=ID
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$id])) unset($_SESSION['cart'][$id]);
    header('Location: cart.php');
    exit;
}

$message = '';
// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $payment = $_POST['payment_method'] ?? '';
    $upi_id = trim($_POST['upi_id'] ?? '');

    if (empty($_SESSION['cart'])) {
        $message = 'Your cart is empty.';
    } elseif ($payment === '') {
        $message = 'Please choose a payment method.';
    } elseif ($payment === 'upi' && $upi_id === '') {
        $message = 'Please provide your UPI ID.';
    } else {
        // Build order summary (not inserting to DB here)
        $total = 0;
        foreach ($_SESSION['cart'] as $it) {
            $total += $it['price'] * $it['qty'];
        }

        $order = [
            'items' => $_SESSION['cart'],
            'total' => $total,
            'payment' => $payment,
            'upi_id' => $payment === 'upi' ? $upi_id : null,
            'placed_at' => date('Y-m-d H:i:s')
        ];

        // Save last order to session for display
        $_SESSION['last_order'] = $order;

        // Clear cart
        $_SESSION['cart'] = [];

        $message = 'Order placed successfully. Payment: ' . strtoupper($payment) . '.';
    }
}

function format_price($v) { return number_format($v, 2); }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cart - Munchy Krunchy</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-table 
            { width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 18px; 
            }
        .cart-table th, .cart-table td { 
            padding: 12px; 
            text-align: left; 
            border-bottom: 1px solid #eee; 
        }
        .cart-actions a { 
            margin-right: 8px; 
        }
        .checkout { 
            margin-top: 18px; 
        }
        .notice { 
            padding: 10px; 
            background:#f0f4f8; 
            border-radius:6px; 
            margin-bottom:12px;
        }
        .success { 
            padding: 10px; 
            background:#e6ffed; 
            border-radius:6px; 
            margin-bottom:12px; 
        }
        .add, .minus, .remv { 
            padding: 4px 8px; 
            background: #2E1F26; 
            color:#fff; 
            border:none; 
            border-radius:4px; 
            cursor:pointer; 
            text-decoration:none;
        }
        .btn { 
            padding: 8px 16px; 
            background: #2E1F26; 
            color:#fff; 
            border:none; 
            border-radius:4px; 
            cursor:pointer; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Cart</h1>

        <?php if ($message): ?>
            <div class="notice"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['cart'])): ?>
            <table class="cart-table">
                <thead>
                    <tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
                </thead>
                <tbody>
                <?php $grand = 0; foreach ($_SESSION['cart'] as $it): $sub = $it['price'] * $it['qty']; $grand += $sub; ?>
                    <tr>
                        <td><?php echo $it['name']; ?></td>
                        <td>Rs. <?php echo format_price($it['price']); ?></td>
                        <td><?php echo intval($it['qty']); ?></td>
                        <td>Rs. <?php echo format_price($sub); ?></td>
                        <td class="cart-actions">
                            <a href="cart.php?add_item=<?php echo $it['id']; ?>" class="add">+</a>
                            <a href="cart.php?decrease=<?php echo $it['id']; ?>" class="minus">-</a>
                            <a href="cart.php?remove=<?php echo $it['id']; ?>" class="remv">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="3">Total</th><th>Rs. <?php echo format_price($grand); ?></th><th></th></tr>
                </tfoot>
            </table>

            <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <a href="MunchyKrunchy.php" class="btn">Continue shopping</a>
                <?php $firstItem = reset($_SESSION['cart']); if ($firstItem): ?>
                    <a href="Order.php?item_id=<?php echo $firstItem['id']; ?>" class="btn">Proceed to Order</a>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <p>Your cart is empty.</p>
              <br>
              <br>  
            <input type="button" value="Return to menu" class="btn" onclick="window.location.href='MunchyKrunchy.php'">
        <?php endif; ?>

        <?php if (!empty($_SESSION['last_order'])): $o = $_SESSION['last_order']; ?>
            <div class="success">
                <strong>Last order:</strong>
                <div>Total: Rs. <?php echo format_price($o['total']); ?></div>
                <div>Payment: <?php echo strtoupper($o['payment']); ?><?php echo isset($o['upi_id']) ? ' (UPI: '.htmlspecialchars($o['upi_id']).')' : ''; ?></div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
