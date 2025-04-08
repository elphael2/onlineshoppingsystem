<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$order_id = (int)$_POST['order_id'];
$status = $conn->real_escape_string($_POST['status']);

$conn->query("UPDATE orders SET status = '$status' WHERE id = $order_id");

header("Location: admin_manage_orders.php");
exit();
