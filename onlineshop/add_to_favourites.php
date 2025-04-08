<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Check if already favourited
$exists = $conn->query("
    SELECT * FROM favourites 
    WHERE user_id = $user_id AND product_id = $product_id
");

if ($exists->num_rows === 0) {
    $conn->query("
        INSERT INTO favourites (user_id, product_id) 
        VALUES ($user_id, $product_id)
    ");
    $_SESSION['fav_message'] = "❤️ Added to your favourites!";
} else {
    $_SESSION['fav_message'] = "⚠️ This product is already in your favourites.";
}

// Redirect back to product detail
header("Location: product_detail.php?id=$product_id");
exit();
