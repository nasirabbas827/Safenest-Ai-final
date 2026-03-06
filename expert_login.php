<?php
include('config.php');
session_start();

$email = "";
$email_err = $password_err = $login_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Basic input validation
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    }

    // Proceed only if no basic errors
    if (empty($email_err) && empty($password_err)) {

        $sql = "SELECT id, name, email, password, usertype 
                FROM securityexpert 
                WHERE email = ? AND status = 'active'";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {

            mysqli_stmt_bind_result($stmt, $id, $name, $email_db, $hashed_password, $usertype);
            mysqli_stmt_fetch($stmt);

            if (password_verify($_POST["password"], $hashed_password)) {

                // Login successful → set session
                $_SESSION["id"]       = $id;
                $_SESSION["name"]     = $name;
                $_SESSION["email"]    = $email_db;
                $_SESSION["usertype"] = $usertype;   // 'staff' or 'expert'
                // $_SESSION["role"]  = $usertype;   // optional - if your code uses 'role'

                header("Location: expert/dashboard.php");
                exit();

            } else {
                $password_err = "Invalid email or password.";
            }

        } else {
            $email_err = "Invalid email or password.";
        }

        mysqli_stmt_close($stmt);
    }

    // Generic error message (better UX than showing "no account found")
    if (!empty($email_err) || !empty($password_err)) {
        $login_msg = '<div class="alert alert-danger text-center">
            Invalid email or password. Please try again.
        </div>';
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expert / Staff Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto shadow-sm" style="max-width: 480px;">
        <div class="card-body">
            <h3 class="text-center mb-4">Expert / Staff Login</h3>

            <?php if ($login_msg) echo $login_msg; ?>

            <form method="post" action="">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email"
                           class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                           value="<?php echo htmlspecialchars($email); ?>" required autofocus>
                    <div class="invalid-feedback"><?php echo $email_err; ?></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password"
                           class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                    <div class="invalid-feedback"><?php echo $password_err; ?></div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-dark btn-block">Login</button>
                </div>

            </form>

            <div class="text-center mt-3 small">
                <a href="login.php">User Login</a> • 
                <!-- Uncomment if you have forgot password or registration -->
                <!-- <a href="forgot-password.php">Forgot Password?</a> -->
            </div>

        </div>
    </div>
</div>

</body>
</html>