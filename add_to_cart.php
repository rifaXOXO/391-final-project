<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = (int) $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "Invalid product";
    exit();
}

// initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// add or update
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['qty']++;
} else {
    $_SESSION['cart'][$product_id] = [
        "name"  => $product['name'],
        "price" => $product['price'],
        "image" => $product['image'],
        "qty"   => 1
    ];
}

header("Location: cart.php");
exit();
?>