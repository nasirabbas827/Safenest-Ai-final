<?php
session_start();
include('config.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("location: admin_login.php");
    exit;
}

/* INSERT CATEGORY */
if (isset($_POST['save_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);

    if ($_POST['category_id'] == "") {
        mysqli_query($conn, "INSERT INTO categories(name) VALUES('$name')");
    } else {
        $id = $_POST['category_id'];
        mysqli_query($conn, "UPDATE categories SET name='$name' WHERE id=$id");
    }

    header("Location: manage_categories.php");
}

/* DELETE CATEGORY */
if (isset($_POST['delete_category'])) {
    $id = $_POST['delete_id'];
    mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
    header("Location: manage_categories.php");
}

/* FETCH DATA */
$result = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>

<head>

    <title>Manage Categories</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">


</head>

<body>

    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5">

        <div>
            <h3>Manage Categories</h3>

            <button class="btn btn-success" data-toggle="modal" data-target="#categoryModal" onclick="addCategory()">
                Add Category
            </button>

        </div>


        <table class="table table-bordered table-striped text-center">

            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th width="200">Action</th>
                </tr>
            </thead>

            <tbody>

                <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                    <tr>

                        <td><?= $row['id'] ?></td>

                        <td><?= htmlspecialchars($row['name']) ?></td>

                        <td>

                            <button class="btn btn-warning btn-sm"
                                onclick="editCategory('<?= $row['id'] ?>','<?= htmlspecialchars($row['name']) ?>')"
                                data-toggle="modal" data-target="#categoryModal">
                                Edit
                            </button>

                            <button class="btn btn-danger btn-sm" onclick="deleteCategory('<?= $row['id'] ?>')"
                                data-toggle="modal" data-target="#deleteModal">
                                Delete
                            </button>

                        </td>

                    </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>


    <!-- ADD / EDIT MODAL -->

    <div class="modal fade" id="categoryModal">

        <div class="modal-dialog">

            <div class="modal-content">

                <form method="POST">

                    <div class="modal-header">

                        <h5 class="modal-title" id="modalTitle">Add Category</h5>

                        <button type="button" class="close" data-dismiss="modal">&times;</button>

                    </div>


                    <div class="modal-body">

                        <input type="hidden" name="category_id" id="category_id">

                        <div class="form-group">

                            <label>Category Name</label>

                            <input type="text" name="name" id="category_name" class="form-control" required>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button type="submit" name="save_category" class="btn btn-success">Save</button>

                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>

                    </div>

                </form>

            </div>

        </div>

    </div>



    <!-- DELETE MODAL -->

    <div class="modal fade" id="deleteModal">

        <div class="modal-dialog">

            <div class="modal-content">

                <form method="POST">

                    <div class="modal-header bg-danger text-white">

                        <h5 class="modal-title">Delete Category</h5>

                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>

                    </div>

                    <div class="modal-body">

                        <p>Are you sure you want to delete this category?</p>

                        <input type="hidden" name="delete_id" id="delete_id">

                    </div>

                    <div class="modal-footer">

                        <button type="submit" name="delete_category" class="btn btn-danger">Delete</button>

                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>


    <script>

        function addCategory() {

            document.getElementById("modalTitle").innerHTML = "Add Category";
            document.getElementById("category_id").value = "";
            document.getElementById("category_name").value = "";

        }

        function editCategory(id, name) {

            document.getElementById("modalTitle").innerHTML = "Edit Category";
            document.getElementById("category_id").value = id;
            document.getElementById("category_name").value = name;

        }

        function deleteCategory(id) {

            document.getElementById("delete_id").value = id;

        }

    </script>


</body>

</html>