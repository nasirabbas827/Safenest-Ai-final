<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'user') {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$order_id = $_GET['order_id'] ?? null;

if (!$order_id || !is_numeric($order_id)) {
    header("Location: dashboard.php");
    exit;
}

$order_id = intval($order_id);

// Fetch order
$order_query = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
$order_res = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_res) == 0) {
    header("Location: dashboard.php");
    exit;
}
$order = mysqli_fetch_assoc($order_res);

// Fetch order items
$items_query = "SELECT oi.*, p.name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = $order_id";
$order_items_res = mysqli_query($conn, $items_query);
$order_items = [];
while ($item = mysqli_fetch_assoc($order_items_res)) {
    $order_items[] = $item;
}

// Fetch payment info (if exists)
$payment = null;
if ($order['payment_status'] !== 'pending') {
    $payment_query = "SELECT * FROM payments WHERE order_id = $order_id LIMIT 1";
    $payment_res = mysqli_query($conn, $payment_query);
    if (mysqli_num_rows($payment_res) > 0) {
        $payment = mysqli_fetch_assoc($payment_res);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= $order['id'] ?> Details - SafeNest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="text-success">
                <?php if ($order['order_status'] == 'pending' && $order['payment_status'] == 'pending'): ?>
                    Order Placed – Ready for Payment!
                <?php else: ?>
                    Order Confirmed!
                <?php endif; ?>
            </h2>
            <p class="lead">Order ID: <strong>#<?= $order['id'] ?></strong></p>
        </div>

        <div class="row">
            <!-- Order Summary -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Placed on:</strong> <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
                        <p><strong>Order Status:</strong> 
                            <span class="badge badge-<?= 
                                $order['order_status'] == 'delivered' ? 'success' : 
                                ($order['order_status'] == 'cancelled' || $order['order_status'] == 'returned' ? 'danger' : 
                                ($order['order_status'] == 'shipped' ? 'info' : 'warning'))
                            ?>">
                                <?= ucfirst($order['order_status']) ?>
                            </span>
                        </p>
                        <p><strong>Payment Status:</strong> 
                            <span class="badge badge-<?= 
                                $order['payment_status'] == 'paid' ? 'success' : 
                                ($order['payment_status'] == 'failed' || $order['payment_status'] == 'refunded' ? 'danger' : 'warning')
                            ?>">
                                <?= ucfirst($order['payment_status']) ?>
                            </span>
                        </p>
                        <p><strong>Total Amount:</strong> <strong class="text-dark">PKR <?= number_format($order['total_amount'], 2) ?></strong></p>
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Delivery Address</h5>
                    </div>
                    <div class="card-body">
                        <address>
                            <?= htmlspecialchars($order['street']) ?><br>
                            <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['state']) ?> <?= htmlspecialchars($order['postal_code']) ?><br>
                            <?= htmlspecialchars($order['country']) ?>
                        </address>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Details (if paid) -->
        <?php if ($payment): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Method:</strong> <?= htmlspecialchars($payment['payment_method']) ?></p>
                    <p><strong>Transaction ID:</strong> <?= htmlspecialchars($payment['transaction_id']) ?></p>
                    <p><strong>Paid on:</strong> <?= date('d M Y, h:i A', strtotime($payment['payment_date'])) ?></p>
                    <?php if ($payment['transaction_image']): ?>
                        <p><strong>Proof:</strong> 
                            <a href="../<?= htmlspecialchars($payment['transaction_image']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Screenshot
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($order['payment_status'] == 'pending'): ?>
            <div class="alert alert-warning text-center mb-4">
                <strong>Payment Pending!</strong> Please complete payment from your <a href="view_orders.php" class="alert-link">Orders page</a>.
            </div>
        <?php endif; ?>

        <!-- Order Items -->
        <h4 class="mt-4 mb-3">Order Items</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price (PKR)</th>
                        <th>Subtotal (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total = 0;
                    foreach ($order_items as $item): 
                        $subtotal = $item['quantity'] * $item['price'];
                        $grand_total += $subtotal;
                    ?>
                        <tr>
                            <td class="text-left">
                                <?= htmlspecialchars($item['name']) ?>
                            </td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['price'], 2) ?></td>
                            <td><?= number_format($subtotal, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="font-weight-bold">
                        <td colspan="3" class="text-right">Grand Total:</td>
                        <td>PKR <?= number_format($grand_total, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-5">
            <a href="view_orders.php" class="btn btn-primary btn-lg">Back to My Orders</a>
            <a href="dashboard.php" class="btn btn-secondary btn-lg ml-3">Continue Shopping</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>