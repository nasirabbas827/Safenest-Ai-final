<?php
session_start();
include('config.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("location: admin_login.php");
    exit;
}

/* HANDLE DELETE */
if (isset($_POST['delete_product'])) {
    $delete_id = $_POST['delete_id'];
    mysqli_query($conn, "DELETE FROM products WHERE id=$delete_id");
    header("Location: view_product.php");
    exit;
}

/* FETCH PRODUCTS */
$result = mysqli_query($conn, "SELECT p.*, c.name AS category_name FROM products p 
                               LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html>

<head>
    <title>View Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>

<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5">
        <h3>All Products</h3>
        <a href="add_product.php" class="btn btn-primary mb-3">Add New Product</a>

        <table class="table table-bordered table-striped text-center">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td><?= number_format($row['price'], 2) ?></td>
                        <td><?= $row['stock_quantity'] ?></td>
                        <td>
                            <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <button class="btn btn-danger btn-sm"
                                onclick="deleteProduct('<?= $row['id'] ?>', '<?= htmlspecialchars($row['name']) ?>')"
                                data-toggle="modal" data-target="#deleteModal">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- DELETE MODAL -->
    <div class="modal fade" id="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Delete Product</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong id="productName"></strong>?</p>
                        <input type="hidden" name="delete_id" id="delete_id">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="delete_product" class="btn btn-danger">Delete</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function deleteProduct(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('productName').textContent = name;
        }
    </script>
</body>

</html>