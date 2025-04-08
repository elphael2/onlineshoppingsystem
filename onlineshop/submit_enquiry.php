<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$order_id = (int)$_POST['order_id'];
$message = $conn->real_escape_string(trim($_POST['message']));

if ($message !== '') {
    $conn->query("
        INSERT INTO order_enquiries (order_id, user_id, message)
        VALUES ($order_id, $user_id, '$message')
    ");
}

header("Location: order_details.php?id=$order_id");
exit();
