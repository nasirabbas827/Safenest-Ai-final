<?php
session_start();
include('config.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("location: admin_login.php");
    exit;
}

$id = intval($_GET['id']);
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$id"));
if (!$product) {
    die("Product not found.");
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

// Function to validate JSON
function is_valid_json($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

if (isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $features = !empty($_POST['features']) ? $_POST['features'] : NULL;
    $specifications = !empty($_POST['specifications']) ? $_POST['specifications'] : NULL;
    $suitable_home_types = !empty($_POST['suitable_home_types']) ? $_POST['suitable_home_types'] : NULL;

    // Validate JSON fields
    foreach (['features' => $features, 'specifications' => $specifications, 'suitable_home_types' => $suitable_home_types] as $key => $val) {
        if ($val && !is_valid_json($val)) {
            die("Invalid JSON in $key field.");
        }
    }

    // Handle image upload
    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0755, true);
            $file_name = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $target_file;
            }
        } else {
            die("Invalid image type. Only JPG, JPEG, PNG allowed.");
        }
    }

    // Safe update query
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE products SET 
        name=?, description=?, price=?, category_id=?, stock_quantity=?, image=?, brand=?, 
        features=?, specifications=?, suitable_home_types=? WHERE id=?"
    );
    mysqli_stmt_bind_param(
        $stmt,
        "ssdissssssi",
        $name,
        $description,
        $price,
        $category_id,
        $stock_quantity,
        $image,
        $brand,
        $features,
        $specifications,
        $suitable_home_types,
        $id
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: view_product.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 800px;">
            <div class="card-body">
                <h3>Edit Product</h3>
                <form method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control"
                            value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description"
                            class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Price</label>
                            <input type="number" step="0.01" name="price" class="form-control"
                                value="<?= $product['price'] ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control"
                                value="<?= $product['stock_quantity'] ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Brand</label>
                            <input type="text" name="brand" class="form-control"
                                value="<?= htmlspecialchars($product['brand']) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php while ($cat = mysqli_fetch_assoc($categories)) {
                                $selected = ($cat['id'] == $product['category_id']) ? 'selected' : ''; ?>
                                <option value="<?= $cat['id'] ?>" <?= $selected ?>><?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Current Image</label><br>
                        <img src="<?= $product['image'] ?>" width="120">
                    </div>

                    <div class="form-group">
                        <label>Change Image</label>
                        <input type="file" name="image" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Features (JSON)</label>
                        <textarea name="features" class="form-control"><?= $product['features'] ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Specifications (JSON)</label>
                        <textarea name="specifications"
                            class="form-control"><?= $product['specifications'] ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Suitable Home Types (JSON)</label>
                        <textarea name="suitable_home_types"
                            class="form-control"><?= $product['suitable_home_types'] ?></textarea>
                    </div>

                    <input type="submit" name="update_product" class="btn btn-success" value="Update Product">
                    <a class="btn btn-dark" href="view_product.php">Cancel</a>
                </form>
            </div>
        </div>
    </div>

</body>

</html>