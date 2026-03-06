<?php
session_start();
include('config.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("location: admin_login.php");
    exit;
}

/* Fetch Categories for Dropdown */
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

/* Handle Form Submission */
if (isset($_POST['save_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock_quantity = $_POST['stock_quantity'];
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $features = !empty($_POST['features']) ? json_encode($_POST['features']) : NULL;
    $specifications = !empty($_POST['specifications']) ? json_encode($_POST['specifications']) : NULL;
    $suitable_home_types = !empty($_POST['suitable_home_types']) ? json_encode($_POST['suitable_home_types']) : NULL;

    $image = 'uploads/default.png';
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
        }
    }

    mysqli_query($conn, "INSERT INTO products 
        (name, description, price, category_id, stock_quantity, image, brand, features, specifications, suitable_home_types)
        VALUES ('$name','$description','$price','$category_id','$stock_quantity','$image','$brand','$features','$specifications','$suitable_home_types')");

    header("Location: view_product.php");
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Add Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>

<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 800px;">
            <div class="card-body">
                <h3>Add Product</h3>
                <form method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Price</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control" value="0">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Brand</label>
                            <input type="text" name="brand" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="image" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Features (JSON)</label>
                        <textarea name="features" class="form-control"
                            placeholder='{"resolution":"4K","night_vision":true}'></textarea>
                    </div>

                    <div class="form-group">
                        <label>Specifications (JSON)</label>
                        <textarea name="specifications" class="form-control"
                            placeholder='{"weight":"1kg","color":"black"}'></textarea>
                    </div>

                    <div class="form-group">
                        <label>Suitable Home Types (JSON Array)</label>
                        <textarea name="suitable_home_types" class="form-control"
                            placeholder='["apartment","villa"]'></textarea>
                    </div>

                    <input type="submit" name="save_product" class="btn btn-success" value="Save Product">
                    <a class="btn btn-dark" href="view_product.php">Cancel</a>

                </form>
            </div>
        </div>
    </div>

</body>

</html>