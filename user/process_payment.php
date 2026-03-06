<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'user') {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $order_id = intval($_POST['order_id']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $transaction_id = mysqli_real_escape_string($conn, $_POST['transaction_id']);

    // Check order belongs to user and is pending
    $order_res = mysqli_query($conn, "SELECT * FROM orders WHERE id=$order_id AND user_id=$user_id AND payment_status='pending'");
    if(mysqli_num_rows($order_res) == 0) {
        $_SESSION['payment_error'] = "Invalid order or payment already completed.";
        header("Location: view_orders.php");
        exit;
    }

    // Handle file upload
    $transaction_image = null;
    if(isset($_FILES['transaction_image']) && $_FILES['transaction_image']['error'] === 0) {
        $allowed_types = ['image/jpeg','image/jpg','image/png','image/gif'];
        if(in_array($_FILES['transaction_image']['type'], $allowed_types)) {
            $upload_dir = '../uploads/transactions/';
            if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $file_name = time().'_'.basename($_FILES['transaction_image']['name']);
            $target_file = $upload_dir.$file_name;
            if(move_uploaded_file($_FILES['transaction_image']['tmp_name'], $target_file)) {
                $transaction_image = 'uploads/transactions/'.$file_name;
            }
        }
    }

    // Insert into payments table
    $stmt = mysqli_prepare($conn, "INSERT INTO payments(order_id, payment_method, transaction_id, transaction_image) VALUES(?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "isss", $order_id, $payment_method, $transaction_id, $transaction_image);
    mysqli_stmt_execute($stmt);

    if(mysqli_stmt_affected_rows($stmt) > 0) {
        // Update order payment status
        mysqli_query($conn, "UPDATE orders SET payment_status='paid' WHERE id=$order_id");

        $_SESSION['payment_success'] = "Payment submitted successfully!";
        header("Location: view_orders.php");
        exit;
    } else {
        $_SESSION['payment_error'] = "Failed to submit payment. Please try again.";
        header("Location: view_orders.php");
        exit;
    }
}