<?php
session_start();
include('config.php');

// User Session Logic
$is_logged_in = (isset($_SESSION["id"]) && $_SESSION["role"] === 'user');
$user_id = $is_logged_in ? $_SESSION["id"] : null;

// Get product ID
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id) {
    header("Location: dashboard.php?error=invalid_product");
    exit;
}

// Fetch product with Category Name
$query = "SELECT p.*, c.name AS category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: dashboard.php?error=product_not_found");
    exit;
}

$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Handle JSON Data properly for Key-Value pairs
$features = json_decode($product['features'] ?? '{}', true);
$specifications = json_decode($product['specifications'] ?? '{}', true);
$suitable_home_types = json_decode($product['suitable_home_types'] ?? '[]', true);

// Related Products
$related_query = "SELECT id, name, price, image, stock_quantity FROM products 
                  WHERE category_id = ? AND id != ? AND stock_quantity > 0 
                  ORDER BY RAND() LIMIT 4";
$related_stmt = mysqli_prepare($conn, $related_query);
mysqli_stmt_bind_param($related_stmt, "ii", $product['category_id'], $product_id);
mysqli_stmt_execute($related_stmt);
$related_products = mysqli_stmt_get_result($related_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> | SafeNest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">

    <style>
        :root { --primary-color: #2c3e50; --accent-color: #27ae60; }
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .product-card { border-radius: 15px; border: none; overflow: hidden; }
        .img-zoom-container { overflow: hidden; border-radius: 15px; background: white; }
        .product-img-main { width: 100%; height: 450px; object-fit: contain; transition: transform .3s; }
        .product-img-main:hover { transform: scale(1.05); }
        .price-tag { font-size: 1.8rem; font-weight: 700; color: var(--accent-color); }
        .spec-table th { background-color: #f1f3f5; width: 40%; }
        .badge-home { background: #e9ecef; color: #495057; border-radius: 20px; padding: 5px 15px; font-weight: 500; }
        .btn-cart { border-radius: 10px; padding: 12px 30px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-transparent p-0">
                <li class="breadcrumb-item"><a href="dashboard.php">Shop</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php?cat=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="img-zoom-container shadow-sm p-3">
                    <img src="admin/<?= htmlspecialchars($product['image']) ?>" class="product-img-main" alt="Product Image">
                </div>
            </div>

            <div class="col-lg-6">
                <div class="pl-lg-4">
                    <span class="text-uppercase text-muted small font-weight-bold"><?= htmlspecialchars($product['brand'] ?? 'SafeNest Original') ?></span>
                    <h1 class="display-5 font-weight-bold mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <div class="mb-4">
                        <span class="price-tag">PKR <?= number_format($product['price'], 2) ?></span>
                        <div class="mt-2">
                            <?php if ($product['stock_quantity'] > 10): ?>
                                <span class="text-success small"><i class="fa fa-check-circle"></i> In Stock</span>
                            <?php elseif ($product['stock_quantity'] > 0): ?>
                                <span class="text-warning small"><i class="fa fa-exclamation-triangle"></i> Only <?= $product['stock_quantity'] ?> left!</span>
                            <?php else: ?>
                                <span class="text-danger small"><i class="fa fa-times-circle"></i> Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <p class="text-secondary leading-relaxed mb-4">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </p>

                    <?php if(!empty($suitable_home_types)): ?>
                    <div class="mb-4">
                        <h6 class="font-weight-bold">Best Suited For:</h6>
                        <div class="d-flex flex-wrap mt-2">
                            <?php foreach($suitable_home_types as $type): ?>
                                <span class="badge-home mr-2 mb-2"><?= ucfirst($type) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form action="add_to_cart.php" method="POST" class="border-top pt-4">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold text-uppercase">Quantity</label>
                                <input type="number" name="quantity" class="form-control form-control-lg" value="1" min="1" max="<?= $product['stock_quantity'] ?>" <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                            </div>
                            <div class="col-md-8 mb-3 align-self-end">
                                <?php if ($is_logged_in && $product['stock_quantity'] > 0): ?>
                                    <button type="submit" class="btn btn-primary btn-lg btn-block btn-cart shadow">
                                        <i class="fa fa-shopping-cart mr-2"></i> Add to Cart
                                    </button>
                                <?php elseif (!$is_logged_in): ?>
                                    <a href="../login.php" class="btn btn-outline-dark btn-lg btn-block btn-cart">Login to Purchase</a>
                                <?php else: ?>
                                    <button disabled class="btn btn-secondary btn-lg btn-block btn-cart">Currently Unavailable</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <hr class="my-5">

        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-4 font-weight-bold">Key Features</h4>
                <div class="card product-card shadow-sm">
                    <ul class="list-group list-group-flush">
                        <?php foreach($features as $key => $value): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-capitalize"><?= str_replace('_', ' ', $key) ?></span>
                            <span class="font-weight-bold text-primary"><?= htmlspecialchars($value) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <h4 class="mb-4 font-weight-bold">Technical Specifications</h4>
                <table class="table table-bordered spec-table shadow-sm bg-white">
                    <?php foreach($specifications as $key => $value): ?>
                    <tr>
                        <th class="text-capitalize"><?= str_replace('_', ' ', $key) ?></th>
                        <td><?= is_bool($value) ? ($value ? 'Yes' : 'No') : htmlspecialchars($value) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <?php if ($related_products->num_rows > 0): ?>
        <div class="mt-5">
            <h3 class="mb-4 font-weight-bold">Related Products</h3>
            <div class="row">
                <?php while($rel = $related_products->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 product-card shadow-sm border-0">
                        <img src="admin/<?= $rel['image'] ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="font-weight-bold"><?= $rel['name'] ?></h6>
                            <p class="text-primary mb-2">PKR <?= number_format($rel['price']) ?></p>
                            <a href="product_details.php?id=<?= $rel['id'] ?>" class="btn btn-sm btn-outline-primary btn-block stretched-link">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>