<?php
session_start();
include('config.php');

// Check login & role
if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'user') {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$full_name = $_SESSION["full_name"] ?? 'User';

// Stats Logic
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders WHERE user_id = $user_id"))['cnt'] ?? 0;
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders WHERE user_id = $user_id AND payment_status = 'pending'"))['cnt'] ?? 0;
$cart_count_q = mysqli_query($conn, "SELECT SUM(ci.quantity) as cnt FROM cart c JOIN cart_items ci ON c.id = ci.cart_id WHERE c.user_id = $user_id");
$cart_count = mysqli_fetch_assoc($cart_count_q)['cnt'] ?? 0;

// Filters & Sorting Logic
$conditions = [];
$params = [];
$param_types = '';

if (!empty($_GET['search'])) {
    $conditions[] = "p.name LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
    $param_types .= 's';
}
if (!empty($_GET['brand'])) {
    $conditions[] = "p.brand = ?";
    $params[] = $_GET['brand'];
    $param_types .= 's';
}

$query = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id";
if (!empty($conditions)) { $query .= " WHERE " . implode(" AND ", $conditions); }

$sort = $_GET['sort'] ?? '';
$allowed_sorts = ['price_asc' => 'p.price ASC', 'price_desc' => 'p.price DESC'];
$order_by = $allowed_sorts[$sort] ?? 'p.id DESC';
$query .= " ORDER BY $order_by";

$stmt = mysqli_prepare($conn, $query);
if ($params) { mysqli_stmt_bind_param($stmt, $param_types, ...$params); }
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$brands_res = mysqli_query($conn, "SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != ''");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeNest | Home Security Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        .stat-card { border: none; border-radius: 12px; color: white; transition: 0.3s; }
        .bg-gradient-blue { background: linear-gradient(45deg, #2c3e50, #4ca1af); }
        .bg-gradient-gold { background: linear-gradient(45deg, #f39c12, #d35400); }
        .bg-gradient-teal { background: linear-gradient(45deg, #16a085, #2ecc71); }

        /* Product Card Enhancements */
        .product-card { border: none; border-radius: 15px; overflow: hidden; transition: 0.4s; background: #fff; }
        .product-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .img-holder { height: 220px; background: #eee; position: relative; overflow: hidden; }
        .img-holder img { width: 100%; height: 100%; object-fit: cover; }
        
        .info-label { font-size: 0.75rem; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 2px; }
        .home-type-badge { font-size: 0.7rem; background: #e9ecef; color: #495057; padding: 2px 8px; border-radius: 4px; margin-right: 4px; }
        
        .price-tag { font-size: 1.25rem; font-weight: 700; color: #2c3e50; }
        .stock-indicator { font-size: 0.8rem; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container py-5">
    
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h2 class="font-weight-bold">My Security Hub</h2>
            <a href="view_cart.php" class="btn btn-dark px-4 shadow-sm">
                <i class="fas fa-shopping-basket mr-2"></i> Cart: <?= $cart_count ?>
            </a>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-gradient-blue shadow-sm">
                <div class="card-body">
                    <h6 class="opacity-75">Order Count</h6>
                    <h3><?= $total_orders ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-gradient-gold shadow-sm">
                <div class="card-body">
                    <h6 class="opacity-75">Pending Action</h6>
                    <h3><?= $pending_orders ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-gradient-teal shadow-sm">
                <div class="card-body">
                    <h6 class="opacity-75">Cart Quantity</h6>
                    <h3><?= $cart_count ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5 p-3">
        <form method="GET" class="form-row align-items-end">
            <div class="col-md-5">
                <label class="small font-weight-bold">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search product name..." value="<?= $_GET['search'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold">Brand</label>
                <select name="brand" class="form-control">
                    <option value="">All Brands</option>
                    <?php while($b = mysqli_fetch_assoc($brands_res)): ?>
                        <option value="<?= $b['brand'] ?>" <?= ($_GET['brand'] ?? '') == $b['brand'] ? 'selected' : '' ?>><?= $b['brand'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Filter</button>
            </div>
            <div class="col-md-2 text-center">
                <a href="dashboard.php" class="small text-danger font-weight-bold">Reset Filters</a>
            </div>
        </form>
    </div>

    <div class="row">
        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="col-12 text-center py-5">
                <p class="lead text-muted">No security products found.</p>
            </div>
        <?php else: ?>
            <?php while ($p = mysqli_fetch_assoc($result)): ?>
                <?php 
                    // Decode JSON data for info display
                    $home_types = json_decode($p['suitable_home_types'], true) ?? [];
                ?>
                <div class="col-lg-4 col-md-6 mb-4"> <div class="card h-100 product-card shadow-sm">
                        <div class="img-holder">
                            <img src="../admin/<?= htmlspecialchars($p['image']) ?>" alt="<?= $p['name'] ?>">
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge badge-info"><?= htmlspecialchars($p['brand'] ?? 'SafeNest') ?></span>
                                <span class="stock-indicator <?= $p['stock_quantity'] > 0 ? 'text-success' : 'text-danger' ?>">
                                    <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> <?= $p['stock_quantity'] ?> in stock
                                </span>
                            </div>
                            
                            <h5 class="card-title font-weight-bold text-truncate"><?= htmlspecialchars($p['name']) ?></h5>
                            
                            <div class="mb-3">
                                <div class="info-label">Best for:</div>
                                <div>
                                    <?php if(!empty($home_types)): ?>
                                        <?php foreach($home_types as $type): ?>
                                            <span class="home-type-badge"><?= ucfirst(str_replace('_', ' ', $type)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="small text-muted">All Home Types</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="price-tag">PKR <?= number_format($p['price']) ?></div>
                                <form method="POST" action="add_to_cart.php" class="d-flex align-items-center">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="<?= $p['stock_quantity'] ?>" 
                                           class="form-control form-control-sm mr-2" style="width: 50px;" 
                                           <?= $p['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                                    <?php if($p['stock_quantity'] > 0): ?>
                                        <button type="submit" class="btn btn-success btn-sm px-3">Add</button>
                                    <?php else: ?>
                                        <button disabled class="btn btn-secondary btn-sm">N/A</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                            
                            <a href="product_details.php?id=<?= $p['id'] ?>" class="btn btn-link btn-block btn-sm text-primary mt-3">
                                <i class="fas fa-info-circle mr-1"></i> View Full Specifications
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>