<?php
session_start();
include('../config.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("location: admin_login.php");
    exit;
}

/* RESET LOGIN ATTEMPTS */
if (isset($_GET['reset'])) {
    $id = intval($_GET['reset']);
    mysqli_query($conn, "UPDATE users SET failed_login_attempts=0 WHERE id=$id");
    header("Location: manage_users.php");
}

/* UPDATE STATUS */
if (isset($_POST['update_status'])) {
    $id = intval($_POST['user_id']);
    $status = $_POST['status'];

    $stmt = mysqli_prepare($conn, "UPDATE users SET status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    mysqli_stmt_execute($stmt);
}

$result = mysqli_query($conn, "SELECT id,full_name,email,cnic,study_program,status,failed_login_attempts,created_at FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>

<head>

    <title>Manage Users</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">


</head>

<body>

    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5">

        <h2 class="mb-4">User Management</h2>

        <div class="table-responsive">

            <table class="table table-bordered table-striped text-center">

                <thead class="thead-dark">

                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>CNIC</th>
                        <th>Program</th>
                        <th>Status</th>
                        <th>Failed Logins</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                        <tr>

                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['cnic']) ?></td>
                            <td><?= htmlspecialchars($row['study_program']) ?></td>

                            <td>

                                <form method="post" class="form-inline justify-content-center">

                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">

                                    <select name="status" class="form-control mr-2">

                                        <option value="pending" <?= $row['status'] == "pending" ? 'selected' : '' ?>>Pending
                                        </option>
                                        <option value="verified" <?= $row['status'] == "verified" ? 'selected' : '' ?>>Verified
                                        </option>
                                        <option value="rejected" <?= $row['status'] == "rejected" ? 'selected' : '' ?>>Rejected
                                        </option>

                                    </select>

                                    <button type="submit" name="update_status" class="m-2 btn btn-sm btn-primary">
                                        Update
                                    </button>

                                </form>

                            </td>

                            <td>

                                <?= $row['failed_login_attempts'] ?>

                                <br>

                                <a href="?reset=<?= $row['id'] ?>" class="btn btn-sm btn-warning mt-1">
                                    Reset
                                </a>

                            </td>

                            <td>

                                <a href="view_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">
                                    View Profile
                                </a>

                            </td>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</body>

</html>