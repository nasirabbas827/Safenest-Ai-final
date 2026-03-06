<?php
include('config.php');

if(isset($_GET['email'])){

    $email=$_GET['email'];

    $sql="UPDATE users SET status='verified' WHERE email=?";
    $stmt=mysqli_prepare($conn,$sql);

    mysqli_stmt_bind_param($stmt,"s",$email);

    if(mysqli_stmt_execute($stmt)){
        echo "<h2>Email Verified Successfully. You can now login.</h2>";
    } else {
        echo "Verification failed.";
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>