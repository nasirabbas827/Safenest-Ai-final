<?php
session_start();
include('config.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("location: admin_login.php");
    exit;
}

// Handle order status update
if (isset($_POST['update_order_status'])) {
    $order_id = intval($_POST['order_id']);
    $status   = mysqli_real_escape_string($conn, $_POST['order_status']);

    // Optional: Validate status value
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
    if (in_array($status, $valid_statuses)) {
        mysqli_query($conn, "UPDATE orders SET order_status = '$status' WHERE id = $order_id");
    }

    header("Location: admin_view_orders.php");
    exit;
}

// Fetch all orders with user info
$result = mysqli_query($conn, "SELECT o.*, u.full_name, u.email 
                               FROM orders o
                               JOIN users u ON o.user_id = u.id
                               ORDER BY o.created_at DESC");

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container py-5">
        <h2 class="mb-4">All Orders</h2>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info text-center">No orders found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Total Amount</th>
                            <th>Order Status</th>
                            <th>Payment Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['full_name']) ?><br>
                                    <small class="text-muted">(<?= htmlspecialchars($order['email']) ?>)</small>
                                </td>
                                <td>PKR <?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $order['order_status'] == 'delivered' ? 'success' : 
                                        ($order['order_status'] == 'cancelled' || $order['order_status'] == 'returned' ? 'danger' : 
                                        ($order['order_status'] == 'shipped' ? 'info' : 'warning'))
                                    ?>">
                                        <?= ucfirst($order['order_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= 
                                        $order['payment_status'] == 'paid' ? 'success' : 
                                        ($order['payment_status'] == 'failed' || $order['payment_status'] == 'refunded' ? 'danger' : 'warning')
                                    ?>">
                                        <?= ucfirst($order['payment_status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#detailsModal<?= $order['id'] ?>">
                                        View Details
                                    </button>
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#statusModal<?= $order['id'] ?>">
                                        Update Status
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- All Details Modals -->
            <?php foreach ($orders as $order): ?>
                <div class="modal fade" id="detailsModal<?= $order['id'] ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">Order Details #<?= $order['id'] ?></h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>User:</strong> <?= htmlspecialchars($order['full_name']) ?> (<?= htmlspecialchars($order['email']) ?>)
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Placed:</strong> <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <strong>Order Status:</strong> <?= ucfirst($order['order_status']) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Payment Status:</strong> <?= ucfirst($order['payment_status']) ?>
                                    </div>
                                </div>

                                <h5>Shipping Address</h5>
                                <address class="mb-4">
                                    <?= htmlspecialchars($order['street'] ?? 'N/A') ?><br>
                                    <?= htmlspecialchars($order['city'] ?? '') ?>, 
                                    <?= htmlspecialchars($order['state'] ?? '') ?> 
                                    <?= htmlspecialchars($order['postal_code'] ?? '') ?><br>
                                    <?= htmlspecialchars($order['country'] ?? 'Pakistan') ?>
                                </address>

                                <h5>Order Items</h5>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $items_res = mysqli_query($conn, "SELECT oi.*, p.name 
                                                                              FROM order_items oi 
                                                                              JOIN products p ON oi.product_id = p.id 
                                                                              WHERE oi.order_id = " . $order['id']);
                                            $grand_total = 0;
                                            while ($item = mysqli_fetch_assoc($items_res)):
                                                $subtotal = $item['quantity'] * $item['price'];
                                                $grand_total += $subtotal;
                                            ?>
                                                <tr>
                                                    <td class="text-left"><?= htmlspecialchars($item['name']) ?></td>
                                                    <td><?= $item['quantity'] ?></td>
                                                    <td>PKR <?= number_format($item['price'], 2) ?></td>
                                                    <td>PKR <?= number_format($subtotal, 2) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                            <tr class="font-weight-bold">
                                                <td colspan="3" class="text-right">Total:</td>
                                                <td>PKR <?= number_format($grand_total, 2) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <h5>Payment Information</h5>
                                <?php
                                $payment_res = mysqli_query($conn, "SELECT * FROM payments WHERE order_id = " . $order['id']);
                                if (mysqli_num_rows($payment_res) > 0):
                                    $payment = mysqli_fetch_assoc($payment_res);
                                ?>
                                    <ul class="list-group">
                                        <li class="list-group-item"><strong>Method:</strong> <?= htmlspecialchars($payment['payment_method']) ?></li>
                                        <li class="list-group-item"><strong>Transaction ID:</strong> <?= htmlspecialchars($payment['transaction_id']) ?></li>
                                        <li class="list-group-item"><strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($payment['payment_date'])) ?></li>
                                        <?php if (!empty($payment['transaction_image'])): ?>
                                            <li class="list-group-item">
                                                <strong>Proof:</strong><br>
                                                <img src="../<?= htmlspecialchars($payment['transaction_image']) ?>" alt="Payment Proof" class="img-thumbnail mt-2" style="max-width: 200px;">
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="alert alert-warning">No payment record found.</div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Status Modal -->
                <div class="modal fade" id="statusModal<?= $order['id'] ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title">Update Status - Order #<?= $order['id'] ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <div class="form-group">
                                        <label for="order_status">New Order Status</label>
                                        <select name="order_status" id="order_status" class="form-control" required>
                                            <option value="pending"    <?= $order['order_status'] === 'pending'    ? 'selected' : '' ?>>Pending</option>
                                            <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="shipped"    <?= $order['order_status'] === 'shipped'    ? 'selected' : '' ?>>Shipped</option>
                                            <option value="delivered"  <?= $order['order_status'] === 'delivered'  ? 'selected' : '' ?>>Delivered</option>
                                            <option value="cancelled"  <?= $order['order_status'] === 'cancelled'  ? 'selected' : '' ?>>Cancelled</option>
                                            <option value="returned"   <?= $order['order_status'] === 'returned'   ? 'selected' : '' ?>>Returned</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="update_order_status" class="btn btn-warning">Update Status</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>