<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'user') {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$search_order_id = $_GET['search_order_id'] ?? '';

// Fetch orders
$query = "SELECT * FROM orders WHERE user_id = $user_id";
if ($search_order_id) {
    $query .= " AND id = " . intval($search_order_id);
}
$query .= " ORDER BY created_at DESC";

$orders_res = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Orders - SafeNest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container py-5">
        <h2>My Orders</h2>

        <form method="GET" class="form-inline mb-4">
            <input type="text" name="search_order_id" class="form-control mr-2" 
                   placeholder="Search by Order ID" value="<?= htmlspecialchars($search_order_id) ?>">
            <button class="btn btn-primary" type="submit">Search</button>
            <a href="view_orders.php" class="btn btn-secondary ml-2">Reset</a>
        </form>

        <?php if (mysqli_num_rows($orders_res) == 0): ?>
            <p>No orders found. <a href="dashboard.php">Shop Now</a></p>
        <?php else: ?>
            <table class="table table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Total Amount (PKR)</th>
                        <th>Order Status</th>
                        <th>Payment Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $pending_orders = []; // We'll store pending ones to generate modals later
                    while ($order = mysqli_fetch_assoc($orders_res)): 
                        if ($order['payment_status'] == 'pending') {
                            $pending_orders[] = $order;
                        }
                    ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= number_format($order['total_amount'], 2) ?></td>
                            <td><?= ucfirst($order['order_status']) ?></td>
                            <td><?= ucfirst($order['payment_status']) ?></td>
                            <td>
                                <a href="order_success.php?order_id=<?= $order['id'] ?>" 
                                   class="btn btn-info btn-sm">View</a>

                                <?php if ($order['payment_status'] == 'pending'): ?>
                                    <button class="btn btn-success btn-sm" 
                                            data-toggle="modal" 
                                            data-target="#payModal<?= $order['id'] ?>">
                                        Pay Now
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- ALL PAYMENT MODALS – moved OUTSIDE the loop and table -->
            <?php foreach ($pending_orders as $order): ?>
                <div class="modal fade" id="payModal<?= $order['id'] ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form method="POST" action="process_payment.php" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title">Payment for Order #<?= $order['id'] ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <div class="form-group">
                                        <label>Payment Method</label>
                                        <select name="payment_method" class="form-control" required>
                                            <option value="JazzCash">JazzCash</option>
                                            <option value="EasyPaisa">EasyPaisa</option>
                                            <option value="HBL Pay">HBL Pay</option>
                                            <option value="UBL Omni">UBL Omni</option>
                                            <option value="Credit Card">Credit Card</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Transaction ID</label>
                                        <input type="text" name="transaction_id" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Upload Transaction Screenshot</label>
                                        <input type="file" name="transaction_image" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Submit Payment</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

    <!-- Scripts at the bottom -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>