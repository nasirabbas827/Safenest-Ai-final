<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'user') {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch user info for shipping
$user_res = mysqli_query($conn, "SELECT street, city, state, postal_code, country FROM users WHERE id=$user_id");
$user = mysqli_fetch_assoc($user_res);
$full_address = $user['street'] . ' ' . $user['city'] . ' ' . $user['state'] . ' ' . $user['postal_code'] . ' ' . $user['country'];

// Get user's cart
$cart_res = mysqli_query($conn, "SELECT id FROM cart WHERE user_id=$user_id");
if (mysqli_num_rows($cart_res) == 0) {
    header("Location: view_cart.php");
    exit;
}
$cart_id = mysqli_fetch_assoc($cart_res)['id'];

// Fetch cart items
$cart_items_res = mysqli_query($conn, "SELECT ci.*, p.name, p.price, p.stock_quantity
                                      FROM cart_items ci
                                      JOIN products p ON ci.product_id=p.id
                                      WHERE ci.cart_id=$cart_id");

$cart_items = [];
$total = 0;
while ($c = mysqli_fetch_assoc($cart_items_res)) {
    $cart_items[] = $c;
    $total += $c['quantity'] * $c['price'];
}

// Handle order placement
if (isset($_POST['place_order'])) {

    // Save order with user info as shipping address
    $street = mysqli_real_escape_string($conn, $user['street']);
    $city = mysqli_real_escape_string($conn, $user['city']);
    $state = mysqli_real_escape_string($conn, $user['state']);
    $postal_code = mysqli_real_escape_string($conn, $user['postal_code']);
    $country = mysqli_real_escape_string($conn, $user['country']);

    mysqli_query($conn, "INSERT INTO orders(user_id, total_amount, order_status, payment_status, street, city, state, postal_code, country)
                        VALUES($user_id, $total, 'pending', 'pending', '$street', '$city', '$state', '$postal_code', '$country')");
    $order_id = mysqli_insert_id($conn);

    // Insert order items
    foreach ($cart_items as $item) {
        mysqli_query($conn, "INSERT INTO order_items(order_id, product_id, quantity, price)
                            VALUES({$order_id}, {$item['product_id']}, {$item['quantity']}, {$item['price']})");

        // Reduce stock
        mysqli_query($conn, "UPDATE products SET stock_quantity=stock_quantity-{$item['quantity']} WHERE id={$item['product_id']}");
    }

    // Clear cart
    mysqli_query($conn, "DELETE FROM cart_items WHERE cart_id=$cart_id");

    header("Location: order_success.php?order_id=$order_id");
    exit;
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Checkout - SafeNest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container py-5">
        <h2>Checkout</h2>

        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty. <a href="dashboard.php">Browse Products</a></p>
        <?php else: ?>
            <h4>Order Summary</h4>
            <ul class="list-group mb-4">
                <?php foreach ($cart_items as $c): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <?= htmlspecialchars($c['name']) ?> x <?= $c['quantity'] ?>
                        <span>PKR <?= number_format($c['quantity'] * $c['price'], 2) ?></span>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item d-flex justify-content-between font-weight-bold">
                    Total
                    <span>PKR <?= number_format($total, 2) ?></span>
                </li>
            </ul>

            <h5>Shipping Address</h5>
            <p><?= htmlspecialchars($full_address) ?></p>

            <form method="POST">
                <button type="submit" name="place_order" class="btn btn-success">Place Order</button>
                <a href="view_cart.php" class="btn btn-secondary">Back to Cart</a>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>