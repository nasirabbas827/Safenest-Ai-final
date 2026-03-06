<?php
session_start();
include('config.php');

$vuid = $_SESSION['vuid'] ?? 'BC240440958';

// Fetch Featured Products for the Landing Page
$featured_query = "SELECT p.*, c.name as cat_name FROM products p 
                   JOIN categories c ON p.category_id = c.id 
                   ORDER BY p.created_at DESC LIMIT 4";
$featured_result = mysqli_query($conn, $featured_query);

// Fetch Categories for the Explorer
$cat_query = "SELECT * FROM categories LIMIT 6";
$cat_result = mysqli_query($conn, $cat_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeNest | Future of Home Security</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">

    <style>
        :root {
            --primary: #4a90e2;
            --accent: #6f42c1;
            --dark: #2d3436;
            --light: #f8f9fa;
        }

        body { font-family: 'Inter', 'Segoe UI', sans-serif; color: var(--dark); overflow-x: hidden; }

        /* Modern Hero Section */
        .hero-section {
            padding: 120px 0;
            background: linear-gradient(135deg, rgba(44, 62, 80, 0.9), rgba(111, 66, 193, 0.8)), 
                        url('https://images.unsplash.com/photo-1558002038-1055907df827?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            clip-path: ellipse(150% 100% at 50% 0%);
        }

        .hero-badge {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 20px;
        }

        /* Product Cards */
        .product-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: 0.4s;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 45px rgba(0,0,0,0.1);
        }
        .product-img {
            height: 200px;
            object-fit: contain;
            padding: 20px;
            background: #fbfbfb;
        }

        /* Category Icons */
        .cat-pill {
            padding: 15px 25px;
            background: white;
            border-radius: 50px;
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
            color: var(--dark);
            text-decoration: none;
        }
        .cat-pill:hover {
            background: var(--primary);
            color: white;
            text-decoration: none;
        }

        /* Section Styling */
        .section-padding { padding: 80px 0; }
        .title-underline {
            width: 60px;
            height: 4px;
            background: var(--accent);
            margin: 15px auto 40px;
            border-radius: 2px;
        }

        .btn-rounded { border-radius: 50px; padding: 12px 35px; font-weight: 600; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<header class="hero-section text-center">
    <div class="container">
        <span class="hero-badge">VUID: <?= htmlspecialchars($vuid); ?></span>
        <h1 class="display-3 font-weight-bold mb-4 text-white">Your Home, <span class="text-warning">Smarter</span> & Safer</h1>
        <p class="lead mb-5 px-md-5">SafeNest leverages AI-driven technology to protect what matters most. Shop the latest in smart cameras, biometric locks, and automated sensors.</p>
        <div class="d-flex justify-content-center">
            <a href="#shop" class="btn btn-warning btn-lg btn-rounded mr-3 shadow">Shop Products</a>
            <a href="chatbot.php" class="btn btn-outline-light btn-lg btn-rounded">Talk to Nestor AI</a>
        </div>
    </div>
</header>

<section class="section-padding bg-light">
    <div class="container text-center">
        <h2 class="font-weight-bold">Security Categories</h2>
        <div class="title-underline"></div>
        <div class="row">
            <?php while($cat = mysqli_fetch_assoc($cat_result)): ?>
            <div class="col-md-4 col-lg-2">
                <a href="dashboard.php?category=<?= $cat['id'] ?>" class="cat-pill">
                    <i class="fas fa-layer-group mr-2"></i> <?= htmlspecialchars($cat['name']) ?>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>


<section id="shop" class="section-padding">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="font-weight-bold mb-0">New Arrivals</h2>
                <p class="text-muted">The latest AI security tech for your home</p>
            </div>
            <a href="login.php" class="btn btn-link text-primary font-weight-bold">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="row">
            <?php while($prod = mysqli_fetch_assoc($featured_result)): ?>
            <div class="col-md-3 mb-4">
                <div class="product-card h-100">
                    <img src="admin/<?= htmlspecialchars($prod['image']) ?>" class="product-img w-100" alt="Product">
                    <div class="card-body">
                        <small class="text-accent font-weight-bold"><?= $prod['cat_name'] ?></small>
                        <h5 class="card-title font-weight-bold mt-1"><?= htmlspecialchars($prod['name']) ?></h5>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="h5 mb-0 font-weight-bold text-primary">PKR <?= number_format($prod['price']) ?></span>
                            <a href="product_details.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-dark rounded-circle">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="section-padding py-0">
    <div class="container">
        <div class="card bg-primary text-white p-5 border-0 rounded-lg shadow-lg overflow-hidden position-relative">
            <div class="row align-items-center" style="z-index: 2;">
                <div class="col-md-8">
                    <h2 class="font-weight-bold">Is your home truly safe?</h2>
                    <p class="lead opacity-75">Take our AI-powered risk assessment and get a customized security plan in 2 minutes.</p>
                    <a href="security_analyzer.php" class="btn btn-light btn-rounded text-primary mt-3">Start Free Assessment</a>
                </div>
                <div class="col-md-4 text-center d-none d-md-block">
                    <i class="fas fa-shield-virus fa-10x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                <h5>Fast Installation</h5>
                <p class="text-muted small">Professional setup across Pakistan within 48 hours.</p>
            </div>
            <div class="col-md-4 mb-4">
                <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                <h5>2-Year Warranty</h5>
                <p class="text-muted small">We stand behind our products with full replacement support.</p>
            </div>
            <div class="col-md-4 mb-4">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h5>24/7 AI Monitoring</h5>
                <p class="text-muted small">Our systems alert you before a breach even happens.</p>
            </div>
        </div>
    </div>
</section>

<footer class="bg-dark text-white pt-5 pb-3">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h4 class="font-weight-bold text-white">SafeNest</h4>
                <p class="text-muted mt-3 small">SafeNest is Pakistan's leading smart home security provider, merging Artificial Intelligence with physical safety.</p>
                <div class="mt-4">
                    <a href="#" class="text-white mr-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white mr-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4">
                <h6 class="text-uppercase font-weight-bold">Shop</h6>
                <ul class="list-unstyled mt-3 small">
                    <li><a href="#" class="text-muted">Cameras</a></li>
                    <li><a href="#" class="text-muted">Sensors</a></li>
                    <li><a href="#" class="text-muted">Smart Locks</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h6 class="text-uppercase font-weight-bold">Contact Support</h6>
                <p class="text-muted small mt-3">
                    <i class="fas fa-envelope mr-2"></i> support@safenest.pk<br>
                    <i class="fas fa-phone mr-2"></i> +92 300 1234567
                </p>
            </div>
            <div class="col-md-3 mb-4">
                <h6 class="text-uppercase font-weight-bold">Payment Methods</h6>
                <div class="mt-3">
                    <span class="badge badge-light p-2 mb-2">JazzCash</span>
                    <span class="badge badge-light p-2 mb-2">EasyPaisa</span>
                    <span class="badge badge-light p-2 mb-2">HBL Pay</span>
                </div>
            </div>
        </div>
        <hr class="border-secondary">
        <p class="text-center text-muted small">© 2026 SafeNest. Designed for VUID <?= htmlspecialchars($vuid); ?>.</p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>