<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'user') {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Ensure the user has a cart
$cart_res = mysqli_query($conn, "SELECT id FROM cart WHERE user_id=$user_id");
if (mysqli_num_rows($cart_res) == 0) {
    mysqli_query($conn, "INSERT INTO cart(user_id) VALUES($user_id)");
    $cart_id = mysqli_insert_id($conn);
} else {
    $cart_id = mysqli_fetch_assoc($cart_res)['id'];
}

// Handle remove item
if (isset($_POST['remove_item'])) {
    $item_id = $_POST['item_id'];
    mysqli_query($conn, "DELETE FROM cart_items WHERE id=$item_id AND cart_id=$cart_id");
    header("Location: view_cart.php");
    exit;
}

// Handle update quantity
if (isset($_POST['update_quantity'])) {
    $item_id = $_POST['item_id'];
    $quantity = max(1, (int) $_POST['quantity']); // minimum 1

    // Check product stock
    $prod_res = mysqli_query($conn, "SELECT stock_quantity FROM products p 
                                    JOIN cart_items ci ON ci.product_id=p.id 
                                    WHERE ci.id=$item_id AND ci.cart_id=$cart_id");
    $prod = mysqli_fetch_assoc($prod_res);
    if ($prod && $quantity <= $prod['stock_quantity']) {
        mysqli_query($conn, "UPDATE cart_items SET quantity=$quantity WHERE id=$item_id AND cart_id=$cart_id");
    }
    header("Location: view_cart.php");
    exit;
}

// Fetch cart items
$cart_items_res = mysqli_query($conn, "SELECT ci.*, p.name, p.price, p.image, p.stock_quantity
                                       FROM cart_items ci
                                       JOIN products p ON ci.product_id=p.id
                                       WHERE ci.cart_id=$cart_id");

$cart_items = [];
$total = 0;
while ($c = mysqli_fetch_assoc($cart_items_res)) {
    $cart_items[] = $c;
    $total += $c['quantity'] * $c['price'];
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Shopping Cart - SafeNest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container py-5">
        <h2>Your Shopping Cart</h2>
        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty. <a href="dashboard.php">Browse Products</a></p>
        <?php else: ?>
            <table class="table table-bordered text-center">
                <thead class="thead-light">
                    <tr>
                        <th>Product</th>
                        <th>Image</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $c):
                        $subtotal = $c['quantity'] * $c['price'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($c['name']) ?></td>
                            <td><img src="../admin/<?= htmlspecialchars($c['image']) ?>" width="80"></td>
                            <td>
                                <form method="POST" class="form-inline justify-content-center">
                                    <input type="hidden" name="item_id" value="<?= $c['id'] ?>">
                                    <input type="number" name="quantity" min="1" max="<?= $c['stock_quantity'] ?>"
                                        value="<?= $c['quantity'] ?>" class="form-control mr-2" style="width:80px;">
                                    <button type="submit" name="update_quantity" class="btn btn-primary btn-sm">Update</button>
                                </form>
                            </td>
                            <td><?= number_format($c['price'], 2) ?></td>
                            <td><?= number_format($subtotal, 2) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="item_id" value="<?= $c['id'] ?>">
                                    <button type="submit" name="remove_item" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="4" class="text-right font-weight-bold">Total:</td>
                        <td colspan="2" class="font-weight-bold"><?= number_format($total, 2) ?></td>
                    </tr>
                </tbody>
            </table>
            <a href="dashboard.php" class="btn btn-secondary">Continue Shopping</a>
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>