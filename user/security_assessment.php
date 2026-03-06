<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'user') {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Process form
$score = null;
$risk_level = '';
$recommendations = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $points = 100;

    // Home Type
    $home_type = $_POST['home_type'] ?? '';
    if (in_array($home_type, ['villa', 'large_home', 'independent_house'])) {
        $points -= 18; // More vulnerable areas
    } elseif ($home_type === 'townhouse_portion') {
        $points -= 12;
    }

    // Location risk
    $location_risk = $_POST['location_risk'] ?? 'medium';
    if ($location_risk === 'high')
        $points -= 25;
    elseif ($location_risk === 'medium')
        $points -= 12;

    // Secure doors/windows/grills
    $secure_doors = $_POST['secure_doors_windows'] ?? 'no';
    if ($secure_doors !== 'yes')
        $points -= 20;

    // Boundary wall / gate
    $boundary = $_POST['boundary_wall'] ?? 'no';
    if ($boundary !== 'yes_high')
        $points -= 15;

    // Outdoor lighting
    $lighting = $_POST['outdoor_lighting'] ?? 'poor';
    if ($lighting !== 'good')
        $points -= 12;

    // Has security system
    $has_system = $_POST['has_security_system'] ?? 'no';
    if ($has_system !== 'yes')
        $points -= 22;

    // Street parking
    $street_park = $_POST['street_parking'] ?? 'no';
    if ($street_park === 'yes')
        $points -= 8;

    $score = max(0, min(100, round($points)));

    // Risk message
    if ($score >= 75) {
        $risk_level = 'Low Risk – Your home setup looks reasonably secure!';
    } elseif ($score >= 45) {
        $risk_level = 'Moderate Risk – There is room for improvement.';
    } else {
        $risk_level = 'High Risk – We strongly recommend taking action soon.';
    }

    // ────────────────────────────────────────────────
    // Fetch real product recommendations from database
    // ────────────────────────────────────────────────

    $rec_conditions = [];
    $rec_params = [];
    $rec_types = '';

    // Keyword-based conditions
    if ($score < 70 || $home_type !== 'apartment') {
        $rec_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.features LIKE ?)";
        $rec_params[] = '%camera%';
        $rec_params[] = '%camera%';
        $rec_params[] = '%camera%';
        $rec_types .= 'sss';
    }

    if ($score < 65 || in_array($home_type, ['villa', 'large_home', 'independent_house'])) {
        $rec_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.features LIKE ? OR p.name LIKE ?)";
        $rec_params[] = '%alarm%';
        $rec_params[] = '%sensor%';
        $rec_params[] = '%door%';
        $rec_params[] = '%lock%';
        $rec_types .= 'ssss';
    }

    if ($score < 60) {
        $rec_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.name LIKE ?)";
        $rec_params[] = '%light%';
        $rec_params[] = '%flood%';
        $rec_params[] = '%motion%';
        $rec_types .= 'sss';
    }

    if ($score < 50) {
        $rec_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.name LIKE ?)";
        $rec_params[] = '%kit%';
        $rec_params[] = '%bundle%';
        $rec_params[] = '%smart home%';
        $rec_types .= 'sss';
    }

    // Match home type from JSON column
    if ($home_type) {
        $rec_conditions[] = "JSON_CONTAINS(p.suitable_home_types, ?)";
        $rec_params[] = json_encode($home_type);
        $rec_types .= 's';
    }

    // Only in-stock products
    $rec_conditions[] = "p.stock_quantity > 0";

    // Build and execute query
    if (!empty($rec_conditions)) {
        $rec_query = "SELECT id, name, price, image, stock_quantity, description 
                      FROM products p 
                      WHERE " . implode(" OR ", $rec_conditions) . " 
                      ORDER BY RAND() 
                      LIMIT 6";

        $stmt = mysqli_prepare($conn, $rec_query);
        if ($rec_params) {
            mysqli_stmt_bind_param($stmt, $rec_types, ...$rec_params);
        }
        mysqli_stmt_execute($stmt);
        $rec_result = mysqli_stmt_get_result($stmt);

        while ($prod = mysqli_fetch_assoc($rec_result)) {
            $recommendations[] = $prod;
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Security Risk Analyzer - SafeNest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container">

                <h2 class="mb-4">Home Security Risk Analyzer</h2>
                <p class="lead mb-5">Answer a few quick questions tailored for homes in Karachi and get your
                    <strong>Home Safety Score</strong> + personalized product suggestions.</p>

                <?php if ($score !== null): ?>
                    <div class="card shadow mb-5 border-0">
                        <div class="card-body text-center">
                            <h3>Your Home Safety Score</h3>
                            <div class="progress mb-3 mx-auto" style="height: 35px; width: 70%;">
                                <div class="progress-bar <?= $score >= 75 ? 'bg-success' : ($score >= 45 ? 'bg-warning' : 'bg-danger') ?>"
                                    role="progressbar" style="width: <?= $score ?>%" aria-valuenow="<?= $score ?>"
                                    aria-valuemin="0" aria-valuemax="100">
                                    <?= $score ?>/100
                                </div>
                            </div>
                            <h4
                                class="<?= $score >= 75 ? 'text-success' : ($score >= 45 ? 'text-warning' : 'text-danger') ?>">
                                <?= htmlspecialchars($risk_level) ?>
                            </h4>

                            <?php if (!empty($recommendations)): ?>
                                <h5 class="mt-5 mb-4">Recommended Security Products for Your Home</h5>
                                <div class="row">
                                    <?php foreach ($recommendations as $prod): ?>
                                        <div class="col-md-4 col-lg-3 mb-4">
                                            <div class="card h-100 shadow-sm">
                                                <img src="../admin/<?= htmlspecialchars($prod['image'] ?? 'uploads/default.png') ?>"
                                                    class="card-img-top" alt="<?= htmlspecialchars($prod['name']) ?>"
                                                    style="height: 180px; object-fit: cover;">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title"><?= htmlspecialchars($prod['name']) ?></h6>
                                                    <p class="card-text text-primary fw-bold mb-1">
                                                        PKR <?= number_format($prod['price'], 2) ?>
                                                    </p>
                                                    <p class="card-text text-muted small mb-2">
                                                        Stock: <?= $prod['stock_quantity'] ?>
                                                    </p>
                                                    <div class="mt-auto">
                                                        <a href="product_details.php?id=<?= $prod['id'] ?>"
                                                            class="btn btn-sm btn-outline-primary d-block mb-2">View Details</a>
                                                        <a href="dashboard.php?search=<?= urlencode($prod['name']) ?>"
                                                            class="btn btn-sm btn-success d-block">Add to Cart</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mt-4">No matching products found at the moment.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-body">
                <form method="POST" class="card p-4 shadow">
                    <div class="form-group">
                        <label class="font-weight-bold">1. What type of home do you live in? <span
                                class="text-danger">*</span></label>
                        <select name="home_type" class="form-control" required>
                            <option value="" disabled selected>Select your home type</option>
                            <option value="apartment">Apartment / Flat (in building/society)</option>
                            <option value="townhouse_portion">Townhouse / Portion / Upper Portion</option>
                            <option value="independent_house">Independent House / Bungalow</option>
                            <option value="villa">Villa (detached, upscale)</option>
                            <option value="large_home">Large Home / Farmhouse / Big Plot</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">2. How would you rate your neighborhood security? <span
                                class="text-danger">*</span></label>
                        <select name="location_risk" class="form-control" required>
                            <option value="low">Low risk (gated society, DHA/Bahria, very safe)</option>
                            <option value="medium" selected>Medium (typical Karachi area, some precautions needed)
                            </option>
                            <option value="high">High risk (frequent street incidents, less secure)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">3. Are all doors, windows, and grills properly secured/locked?
                            <span class="text-danger">*</span></label>
                        <select name="secure_doors_windows" class="form-control" required>
                            <option value="yes">Yes – strong locks & grills everywhere</option>
                            <option value="no">No / Only partial</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">4. Do you have a proper boundary wall & secure gate? (for
                            houses/villas)</label>
                        <select name="boundary_wall" class="form-control">
                            <option value="yes_high">Yes – high wall + electric gate/lock</option>
                            <option value="yes_basic">Yes – basic wall/gate</option>
                            <option value="no">No / Open or low boundary</option>
                        </select>
                        <small class="form-text text-muted">Skip if living in apartment/flat</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">5. Outdoor lighting (porch, gate, yard/motion lights)?</label>
                        <select name="outdoor_lighting" class="form-control" required>
                            <option value="good">Good – motion-sensor or bright lights</option>
                            <option value="average">Average</option>
                            <option value="poor">Poor / Dark areas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">6. Do you have any security system (CCTV, alarm,
                            sensors)?</label>
                        <select name="has_security_system" class="form-control" required>
                            <option value="yes">Yes – working cameras/alarm</option>
                            <option value="no">No / Not installed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">7. Is your car usually parked on the street?</label>
                        <select name="street_parking" class="form-control">
                            <option value="no">No – inside gate/garage</option>
                            <option value="yes">Yes – on street</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">
                        Calculate My Safety Score & Get Recommendations
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>