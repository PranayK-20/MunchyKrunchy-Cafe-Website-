<?php
session_start();
include('Connection.php');

$message = '';
$item = null;
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;

if ($item_id > 0) {
    $stmt = mysqli_prepare($conn, 'SELECT menu_items, price FROM menu WHERE item_id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $item = mysqli_fetch_assoc($result);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $order_item = trim($_POST['order_item'] ?? '');
    $table_no = trim($_POST['table_no'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'Cash');
    $upi_id = trim($_POST['upi_id'] ?? '');
    $total = floatval($_POST['total'] ?? 0);
    $status = 'Pending';

    if ($customer_name === '' || $order_item === '' || $table_no === '') {
        $message = 'Please complete all order details before submitting.';
    } elseif ($payment_method === 'UPI' && $upi_id === '') {
        $message = 'Please enter your UPI ID for UPI payments.';
    } else {
        $insert = mysqli_prepare($conn, 'INSERT INTO `orders` (Customer_name, Ordered_item, Table_no, Status, Total, Payment_method, UPI_ID) VALUES (?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($insert, 'ssisdss', $customer_name, $order_item, $table_no, $status, $total, $payment_method, $upi_id);
        if (mysqli_stmt_execute($insert)) {
            $inserted_order_id = mysqli_insert_id($conn);
            $message = 'Order placed successfully! Your order ID is ' . $inserted_order_id . '.';
            $item = null;
        } else {
            $message = 'Error placing order: ' . mysqli_stmt_error($insert);
        }
        mysqli_stmt_close($insert);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Item - Munchy Krunchy</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { 
            margin: 0; 
            font-family: Arial, sans-serif; 
            background: #f5f5f5; 
        }
        .order-page { 
            max-width: 760px; 
            margin: 40px auto; 
            padding: 24px; 
            background: #fff; 
            border-radius: 14px; 
            box-shadow: 0 12px 30px rgba(0,0,0,0.08); 
        }
        .order-page h1 { 
            margin-top: 0; 
        }
        .order-info, .order-form { 
            margin-top: 18px; 
        }
        .order-info p { 
            margin: 6px 0; 
        }
        .form-row { 
            margin-bottom: 14px; 
        }
        .form-row label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 6px; 
        }
        .form-row input[type="text"] { 
            width: 100%; 
            padding: 10px 12px; 
            border: 1px solid #ccc; 
            border-radius: 8px; 
        }
        .payment-group { 
            margin-bottom: 18px; 
            border: 1px solid #d7d7d7; 
            border-radius: 12px; 
            padding: 16px; 
            background: #faf9f8; 
        }
        .payment-group legend { 
            font-size: 1rem; 
            font-weight: 700; 
            margin-bottom: 10px; 
        }
        .payment-option { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            padding: 12px 14px; 
            border: 1px solid #e1d9d2; 
            border-radius: 10px; 
            margin-bottom: 10px; 
            background: #fff; 
        }
        .payment-option input { 
            margin: 0; 
            accent-color: #2E1F26; 
        }
        .payment-option label { 
            margin: 0; 
            cursor: pointer; 
            font-weight: 600; }
        #upi-row input { 
            width: 100%; 
        }
        .btn-primary { 
            background: #2E1F26; 
            color: #fff; 
            padding: 12px 18px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
        }
        .btn-link { 
            margin-left: 14px; 
            color: #2E1F26; 
            text-decoration: none; 
        }
        .message { 
            margin-bottom: 18px; 
            padding: 12px 14px; 
            border-radius: 10px; 
            background: #eef6f8; 
        }
        .message.error { 
            background: #ffe8e8; 
            color: #a33; 
        }
        .message.success { 
            background: #e8ffe8; 
            color: #2a662a; 
        }
    </style>
</head>
<body>
    <div class="order-page">
        <h1>Place Your Order</h1>

        <?php if ($message): ?>
            <div class="message<?php echo strpos($message, 'Error') === 0 ? ' error' : ' success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($item): ?>
            <div class="order-info">
                <p><strong>Item:</strong> <?php echo $item['menu_items']; ?></p>
                <p><strong>Price:</strong> Rs. <?php echo number_format($item['price'], 2); ?></p>
            </div>

            <form method="post" class="order-form">
                <input type="hidden" name="order_item" value="<?php echo $item['menu_items']; ?>">
                <input type="hidden" name="total" value="<?php echo $item['price']; ?>">

                <div class="form-row">
                    <label for="customer_name">Customer Name</label>
                    <input id="customer_name" name="customer_name" type="text" required>
                </div>
                <div class="form-row">
                    <label for="table_no">Table Number</label>
                    <input id="table_no" name="table_no" type="text" required>
                </div>
                <fieldset class="payment-group">
                    <legend>Payment Method</legend>
                    <div class="payment-option">
                        <input type="radio" id="pay_cash" name="payment_method" value="Cash" checked>
                        <label for="pay_cash">Cash</label>
                    </div>
                    <div class="payment-option">
                        <input type="radio" id="pay_upi" name="payment_method" value="UPI" disabled>
                        <label for="pay_upi">UPI (currently unavailable)</label>
                    </div>
                    <p style="margin:0; padding-top:4px; font-size:0.95rem; color:#666;">Please choose Cash for now.</p>
                </fieldset>
                <div class="form-row" id="upi-row" style="display:none;">
                    <label for="upi_id">UPI ID</label>
                    <input id="upi_id" name="upi_id" type="text" placeholder="your@upi">
                </div>
                <button type="submit" name="place_order" class="btn-primary">Submit Order</button>
                <a href="MunchyKrunchy.php" class="btn-link">Back to Menu</a>
            </form>
            <script>
                document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
                    radio.addEventListener('change', function() {
                        document.getElementById('upi-row').style.display = this.value === 'UPI' ? 'block' : 'none';
                    });
                });
            </script>
        <?php else: ?>
            <p>No item selected for order. Please choose an item from the <a href="MunchyKrunchy.php">menu</a>.</p>
        <?php endif; ?>
    </div>
</body>
</html>