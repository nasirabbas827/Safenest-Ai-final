<?php
session_start();
include('config.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("location: admin_login.php");
    exit;
}

/* SAVE (INSERT or UPDATE) */
if (isset($_POST['save_expert'])) {
    $name     = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $usertype = mysqli_real_escape_string($conn, $_POST['usertype']);
    $status   = mysqli_real_escape_string($conn, $_POST['status']);

    $password_sql = "";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ", password = '$password'";
    }

    if (empty($_POST['expert_id'])) {
        // INSERT
        if (empty($_POST['password'])) {
            // You can decide: force password on create or allow without
            $_SESSION['error'] = "Password is required when creating a new expert.";
            header("Location: manage_securityexperts.php");
            exit;
        }
        $query = "INSERT INTO securityexpert (name, email, password, usertype, status)
                  VALUES ('$name', '$email', '$password', '$usertype', '$status')";
        mysqli_query($conn, $query);
    } else {
        // UPDATE
        $id = (int)$_POST['expert_id'];
        $query = "UPDATE securityexpert SET 
                    name = '$name',
                    email = '$email',
                    usertype = '$usertype',
                    status = '$status'
                    $password_sql
                  WHERE id = $id";
        mysqli_query($conn, $query);
    }

    header("Location: manage_securityexperts.php");
    exit;
}

/* DELETE */
if (isset($_POST['delete_expert'])) {
    $id = (int)$_POST['delete_id'];
    mysqli_query($conn, "DELETE FROM securityexpert WHERE id = $id");
    header("Location: manage_securityexperts.php");
    exit;
}

/* FETCH ALL */
$result = mysqli_query($conn, "SELECT * FROM securityexpert ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Security Experts</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Manage Security Experts</h3>
        <button class="btn btn-success" data-toggle="modal" data-target="#expertModal" onclick="addExpert()">
            Add New Expert
        </button>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-striped text-center">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Password</th>
                <th>Type</th>
                <th>Created</th>
                <th>Status</th>
                <th width="220">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><code>hashed</code></td>
                <td><?= htmlspecialchars(ucfirst($row['usertype'])) ?></td>
                <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                <td>
                    <span class="badge badge-<?= $row['status'] === 'active' ? 'success' : 'danger' ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </td>
                <td>
                    <button class="btn btn-warning btn-sm"
                        onclick="editExpert(
                            '<?= $row['id'] ?>',
                            '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($row['usertype']) ?>',
                            '<?= htmlspecialchars($row['status']) ?>'
                        )"
                        data-toggle="modal" data-target="#expertModal">
                        Edit
                    </button>
                    <button class="btn btn-danger btn-sm"
                        onclick="deleteExpert('<?= $row['id'] ?>')"
                        data-toggle="modal" data-target="#deleteModal">
                        Delete
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- ADD/EDIT MODAL -->
<div class="modal fade" id="expertModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Security Expert</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="expert_id" id="expert_id">

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" id="expert_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="expert_email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Password 
                            <small id="passwordHelp" class="form-text text-muted">
                                (leave blank to keep existing password when editing)
                            </small>
                        </label>
                        <input type="password" name="password" id="expert_password" class="form-control">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>User Type</label>
                            <select name="usertype" id="expert_usertype" class="form-control" required>
                                <option value="expert" selected>Security Expert</option>
                                <option value="staff">staff</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Status</label>
                            <select name="status" id="expert_status" class="form-control" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="save_expert" class="btn btn-success">Save</button>
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
                    <h5 class="modal-title">Delete Security Expert</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this expert record?<br>
                       <strong>This cannot be undone.</strong></p>
                    <input type="hidden" name="delete_id" id="delete_id">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="delete_expert" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function addExpert() {
    document.getElementById("modalTitle").textContent = "Add Security Expert";
    document.getElementById("expert_id").value = "";
    document.getElementById("expert_name").value = "";
    document.getElementById("expert_email").value = "";
    document.getElementById("expert_password").value = "";
    document.getElementById("expert_usertype").value = "expert";
    document.getElementById("expert_status").value = "active";
    document.getElementById("passwordHelp").style.display = "block";
}

function editExpert(id, name, email, usertype, status) {
    document.getElementById("modalTitle").textContent = "Edit Security Expert";
    document.getElementById("expert_id").value = id;
    document.getElementById("expert_name").value = name;
    document.getElementById("expert_email").value = email;
    document.getElementById("expert_password").value = "";
    document.getElementById("expert_usertype").value = usertype;
    document.getElementById("expert_status").value = status;
    document.getElementById("passwordHelp").style.display = "block";
}

function deleteExpert(id) {
    document.getElementById("delete_id").value = id;
}
</script>
</body>
</html>