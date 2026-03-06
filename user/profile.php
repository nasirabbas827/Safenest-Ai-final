<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'user') {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];

$update_msg = "";
$display_picture_err = "";

/* Fetch user data */
$sql = "SELECT full_name,email,display_picture,cnic,study_program,about_me,
street,city,state,postal_code,country
FROM users WHERE id=?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST['full_name']);
    $cnic = trim($_POST['cnic']);
    $study_program = trim($_POST['study_program']);
    $about_me = trim($_POST['about_me']);

    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);

    $display_picture = $user['display_picture'];

    /* Profile Picture Upload */
    if (isset($_FILES['display_picture']) && $_FILES['display_picture']['error'] == 0) {

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];

        if (!in_array($_FILES['display_picture']['type'], $allowed_types)) {
            $display_picture_err = "Only JPG, JPEG, PNG allowed.";
        } else {

            $upload_dir = "../uploads/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = time() . '_' . basename($_FILES['display_picture']['name']);
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['display_picture']['tmp_name'], $target_file)) {
                $display_picture = "uploads/" . $file_name;
            } else {
                $display_picture_err = "Image upload failed.";
            }
        }
    }

    if (empty($display_picture_err)) {

        $sql = "UPDATE users SET
        full_name=?,
        cnic=?,
        study_program=?,
        about_me=?,
        display_picture=?,
        street=?,
        city=?,
        state=?,
        postal_code=?,
        country=?
        WHERE id=?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssssssi",
            $full_name,
            $cnic,
            $study_program,
            $about_me,
            $display_picture,
            $street,
            $city,
            $state,
            $postal_code,
            $country,
            $user_id
        );

        if (mysqli_stmt_execute($stmt)) {
            $update_msg = '<div class="alert alert-success text-center">Profile updated successfully.</div>';
            $_SESSION["name"] = $full_name;
        } else {
            $update_msg = '<div class="alert alert-danger text-center">Update failed.</div>';
        }

        mysqli_stmt_close($stmt);
    }
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

    <?php include('navbar.php'); ?>

    <div class="container mt-5">

        <div class="card mx-auto shadow-sm" style="max-width: 900px;">


            <div class="card-body">

                <h3 class="text-center mb-4">User Profile</h3>

                <?php echo $update_msg; ?>

                <form method="post" enctype="multipart/form-data">

                    <div class="text-center mb-4">

                        <img src="../<?= htmlspecialchars($user['display_picture']) ?>"
                            style="width:120px;height:120px;border-radius:50%;object-fit:cover;">

                        <br><br>

                        <input type="file" name="display_picture" class="form-control-file">

                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-6">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control"
                                value="<?= htmlspecialchars($user['full_name']) ?>">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Email (readonly)</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>"
                                readonly>
                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-6">
                            <label>CNIC</label>
                            <input type="text" name="cnic" class="form-control"
                                value="<?= htmlspecialchars($user['cnic']) ?>">
                        </div>

                        <div class="form-group col-md-6">

                            <label>Study Program</label>

                            <select name="study_program" class="form-control">

                                <?php
                                $programs = ['BSCS', 'BSIT', 'BSSE', 'BSDS', 'BSMGT', 'BSAI', 'MCS', 'MIT', 'ADPCS', 'MSCS'];

                                foreach ($programs as $prog) {

                                    $selected = ($user['study_program'] == $prog) ? 'selected' : '';

                                    echo "<option value='$prog' $selected>$prog</option>";
                                }
                                ?>

                            </select>

                        </div>

                    </div>

                    <div class="form-group">

                        <label>About Me</label>

                        <textarea name="about_me"
                            class="form-control"><?= htmlspecialchars($user['about_me']) ?></textarea>

                    </div>

                    <hr>

                    <h5 class="mb-3">Address Information</h5>

                    <div class="form-row">

                        <div class="form-group col-md-6">
                            <label>Street</label>
                            <input type="text" name="street" class="form-control"
                                value="<?= htmlspecialchars($user['street']) ?>">
                        </div>

                        <div class="form-group col-md-6">
                            <label>City</label>
                            <input type="text" name="city" class="form-control"
                                value="<?= htmlspecialchars($user['city']) ?>">
                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-4">
                            <label>State</label>
                            <input type="text" name="state" class="form-control"
                                value="<?= htmlspecialchars($user['state']) ?>">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Postal Code</label>
                            <input type="text" name="postal_code" class="form-control"
                                value="<?= htmlspecialchars($user['postal_code']) ?>">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Country</label>
                            <input type="text" name="country" class="form-control"
                                value="<?= htmlspecialchars($user['country']) ?>">
                        </div>

                    </div>

                    <div class="text-center">

                        <button type="submit" class="btn btn-dark px-5">
                            Update Profile
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</body>

</html>