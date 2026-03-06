<?php
session_start();
include('config.php');
if (!isset($_SESSION['id']) || $_SESSION['role']!=='user') exit;

$user_id = $_SESSION['id'];
$product_id = $_POST['product_id'];
$quantity = max(1,intval($_POST['quantity']));

// Check or create cart
$cart_res = mysqli_query($conn,"SELECT * FROM cart WHERE user_id=$user_id");
if(mysqli_num_rows($cart_res)==0){
    mysqli_query($conn,"INSERT INTO cart(user_id) VALUES($user_id)");
    $cart_id = mysqli_insert_id($conn);
}else{
    $cart = mysqli_fetch_assoc($cart_res);
    $cart_id = $cart['id'];
}

// Check if item already in cart
$exists = mysqli_query($conn,"SELECT * FROM cart_items WHERE cart_id=$cart_id AND product_id=$product_id");
if(mysqli_num_rows($exists)>0){
    mysqli_query($conn,"UPDATE cart_items SET quantity=quantity+$quantity WHERE cart_id=$cart_id AND product_id=$product_id");
}else{
    $price = mysqli_fetch_assoc(mysqli_query($conn,"SELECT price FROM products WHERE id=$product_id"))['price'];
    mysqli_query($conn,"INSERT INTO cart_items(cart_id, product_id, quantity, price) VALUES($cart_id,$product_id,$quantity,$price)");
}

header("Location: dashboard.php");