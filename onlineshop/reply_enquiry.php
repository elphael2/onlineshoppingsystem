<?php
session_start();
include('includes/db.php');

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$enquiry_id = (int)$_POST['enquiry_id'];
$reply = $conn->real_escape_string(trim($_POST['reply']));
$order_id = (int)$_POST['order_id'];

if (!empty($reply)) {
    $conn->query("UPDATE order_enquiries SET reply = '$reply' WHERE id = $enquiry_id");
}

header("Location: admin_view_order.php?id=$order_id");
exit();
