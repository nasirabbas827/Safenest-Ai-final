<?php
include('config.php');
session_start();

$email = $password = "";
$email_err = $password_err = $login_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($email_err) && empty($password_err)) {

        $sql = "SELECT id, full_name, email, password, status, failed_login_attempts 
                FROM users WHERE email = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {

                mysqli_stmt_bind_result(
                    $stmt,
                    $id,
                    $name,
                    $email_db,
                    $hashed_password,
                    $status,
                    $failed_attempts
                );

                mysqli_stmt_fetch($stmt);

                if ($failed_attempts >= 5) {

                    $login_msg = '<div class="alert alert-danger text-center">
                    Your account is locked due to too many failed login attempts.
                    </div>';

                } else {

                    if ($status != 'verified') {

                        $login_msg = '<div class="alert alert-warning text-center">
                        Please verify your email before logging in.
                        </div>';

                    } else {

                        if (password_verify($password, $hashed_password)) {

                            // reset failed attempts
                            $reset_sql = "UPDATE users SET failed_login_attempts = 0 WHERE id=?";
                            $reset_stmt = mysqli_prepare($conn, $reset_sql);
                            mysqli_stmt_bind_param($reset_stmt, "i", $id);
                            mysqli_stmt_execute($reset_stmt);
                            mysqli_stmt_close($reset_stmt);

                            $_SESSION["id"] = $id;
                            $_SESSION["name"] = $name;
                            $_SESSION["email"] = $email_db;
                            $_SESSION["role"] = 'user';

                            header("Location: user/dashboard.php");
                            exit();

                        } else {

                            $failed_attempts++;

                            $update_sql = "UPDATE users SET failed_login_attempts=? WHERE id=?";
                            $update_stmt = mysqli_prepare($conn, $update_sql);
                            mysqli_stmt_bind_param($update_stmt, "ii", $failed_attempts, $id);
                            mysqli_stmt_execute($update_stmt);
                            mysqli_stmt_close($update_stmt);

                            $password_err = "Invalid password.";
                        }
                    }
                }

            } else {

                $email_err = "No account found with that email.";

            }
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto shadow-sm" style="max-width: 500px;">
        <div class="card-body">
            <h3 class="text-center mb-4">User Login</h3>
            <?php echo $login_msg; ?>

            <form method="post" action="">

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Email</label>
                        <input type="email" name="email"
                               class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                               value="<?php echo $email; ?>" required>
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Password</label>
                        <input type="password" name="password"
                               class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                </div>

                <div class="form-row text-center mt-4">
                    <div class="form-group col-md-6">
                        <input type="submit" value="Login" class="btn btn-dark btn-block">
                    </div>
                    <div class="form-group col-md-6">
                        <input type="reset" value="Clear All" class="btn btn-secondary btn-block">
                    </div>
                </div>

            </form>

            <p class="text-center mt-3">Don't have an account? <a href="register.php">Register here</a></p>
             <p class="text-center mt-3"> <a href="expert_login.php">Login as Expert?</a></p>
        </div>
    </div>
</div>

</body>
</html>


