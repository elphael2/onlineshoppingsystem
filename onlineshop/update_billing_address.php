<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$new_address = $conn->real_escape_string($_POST['new_address']);

if (!empty($new_address)) {
    $conn->query("UPDATE users SET billing_address = '$new_address' WHERE id = $user_id");
}

header("Location: checkout.php");
exit();
