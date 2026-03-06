<?php
session_start();
include('../config.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("location: manage_users.php");
}

$id = intval($_GET['id']);

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User not found");
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>User Profile</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">


</head>

<body>

    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5 mb-5">

        <div class="card shadow">

            <div class="card-body">

                <h3 class="mb-4 text-center">User Profile</h3>

                <div class="text-center mb-4">

                    <img src="../<?= htmlspecialchars($user['display_picture']) ?>"
                        style="width:130px;height:130px;border-radius:50%;object-fit:cover;">

                </div>

                <table class="table table-bordered">

                    <tr>
                        <th>Full Name</th>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                    </tr>

                    <tr>
                        <th>Email</th>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                    </tr>

                    <tr>
                        <th>CNIC</th>
                        <td><?= htmlspecialchars($user['cnic']) ?></td>
                    </tr>

                    <tr>
                        <th>Study Program</th>
                        <td><?= htmlspecialchars($user['study_program']) ?></td>
                    </tr>

                    <tr>
                        <th>About Me</th>
                        <td><?= htmlspecialchars($user['about_me']) ?></td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td><?= htmlspecialchars($user['status']) ?></td>
                    </tr>

                    <tr>
                        <th>Failed Login Attempts</th>
                        <td><?= $user['failed_login_attempts'] ?></td>
                    </tr>

                    <tr>
                        <th>Street</th>
                        <td><?= htmlspecialchars($user['street']) ?></td>
                    </tr>

                    <tr>
                        <th>City</th>
                        <td><?= htmlspecialchars($user['city']) ?></td>
                    </tr>

                    <tr>
                        <th>State</th>
                        <td><?= htmlspecialchars($user['state']) ?></td>
                    </tr>

                    <tr>
                        <th>Postal Code</th>
                        <td><?= htmlspecialchars($user['postal_code']) ?></td>
                    </tr>

                    <tr>
                        <th>Country</th>
                        <td><?= htmlspecialchars($user['country']) ?></td>
                    </tr>

                    <tr>
                        <th>Account Created</th>
                        <td><?= $user['created_at'] ?></td>
                    </tr>

                </table>

                <a href="manage_users.php" class="btn btn-secondary">
                    Back
                </a>

            </div>

        </div>

    </div>

</body>

</html>