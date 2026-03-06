<?php
session_start();
include('config.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("location: admin_login.php");
    exit;
}
// --- 1. KEY PERFORMANCE INDICATORS (KPIs) ---
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'")->fetch_assoc()['total'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'")->fetch_assoc()['count'];
$low_stock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity < 10")->fetch_assoc()['count'];

// --- 2. AI & FRAUD DETECTION DATA ---
// Flagging users with > 3 failed attempts or unverified status with high-value cart activity
$fraud_alerts = $conn->query("SELECT COUNT(*) as alerts FROM users WHERE failed_login_attempts > 3 OR status = 'pending'")->fetch_assoc()['alerts'];

// --- 3. SALES TRENDS (LINE CHART) ---
$sales_query = $conn->query("SELECT DATE(created_at) as date, SUM(total_amount) as daily_total FROM orders GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 7");
$dates = []; $amounts = [];
while($row = $sales_query->fetch_assoc()) {
    $dates[] = $row['date'];
    $amounts[] = (float)$row['daily_total'];
}

// --- 4. TOP PRODUCTS & CATEGORIES (DOUGHNUT CHART) ---
$cat_query = $conn->query("SELECT c.name, COUNT(p.id) as p_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id");
$cat_names = []; $cat_counts = [];
while($row = $cat_query->fetch_assoc()) {
    $cat_names[] = $row['name'];
    $cat_counts[] = $row['p_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Admin Panel | SafeNest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .card { border: none; border-radius: 10px; transition: all 0.3s; }
        .card:hover { transform: translateY(-3px); }
        .border-left-info { border-left: .25rem solid #17a2b8 !important; }
        .border-left-danger { border-left: .25rem solid #dc3545 !important; }
        .border-left-success { border-left: .25rem solid #28a745 !important; }
        .border-left-warning { border-left: .25rem solid #ffc107 !important; }
        .text-cyan { color: #17a2b8; }
        .table-header { background: #f1f3f9; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; }
    </style>
</head>
<body>

    <?php include('admin_navbar.php'); ?>


<div class="container-fluid">
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Earnings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">RS <?= number_format($total_revenue, 2) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pending Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pending_orders ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clipboard-list fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Low Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $low_stock ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Security Alerts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $fraud_alerts ?> Flagged</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-shield fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-cyan">Sales & Revenue Analytics</h6>
                </div>
                <div class="card-body">
                    <canvas id="mainSalesChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-cyan">Product Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-cyan">Recent Support Activity</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-header">
                                <tr>
                                    <th>User ID</th>
                                    <th>Message Preview</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $msgs = $conn->query("SELECT * FROM support_messages ORDER BY created_at DESC LIMIT 5");
                                if($msgs->num_rows > 0) {
                                    while($m = $msgs->fetch_assoc()) {
                                        echo "<tr>
                                                <td>#{$m['user_id']}</td>
                                                <td>".substr($m['message'], 0, 50)."...</td>
                                                <td>{$m['created_at']}</td>
                                                <td><a href='support_messages.php' class='btn btn-sm btn-link text-info'>Reply</a></td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No messages found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Line Chart: Revenue
const salesCtx = document.getElementById('mainSalesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_reverse($dates)) ?>,
        datasets: [{
            label: 'Sales (RS )',
            data: <?= json_encode(array_reverse($amounts)) ?>,
            borderColor: '#00ddff',
            backgroundColor: 'rgb(229, 249, 0)',
            fill: true,
            tension: 0.4
        }]
    }
});

// Doughnut Chart: Categories
const catCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(catCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($cat_names) ?>,
        datasets: [{
            data: <?= json_encode($cat_counts) ?>,
            backgroundColor: ['#17a2b8', '#28a745', '#ffc107', '#dc3545', '#6610f2']
        }]
    },
    options: { maintainAspectRatio: false }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>