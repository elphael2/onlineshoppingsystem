<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Delete from favourites table
$conn->query("DELETE FROM favourites WHERE user_id = $user_id AND product_id = $product_id");

// Optional success message
$_SESSION['fav_message'] = "❌ Removed from your favourites.";

// Redirect back to the referring page
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'favourites.php';
header("Location: $redirect");
exit();
