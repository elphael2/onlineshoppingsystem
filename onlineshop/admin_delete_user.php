<?php
session_start();
include('includes/db.php');

// Check admin permission
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Ensure user ID is passed
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_manage_users.php");
    exit();
}

$user_id = (int)$_GET['id'];

// Prevent deleting your own admin account
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['user_message'] = "❌ You cannot delete your own account.";
    header("Location: admin_manage_users.php");
    exit();
}

// Attempt to delete user
$delete = $conn->query("DELETE FROM users WHERE id = $user_id");

if ($delete) {
    $_SESSION['user_message'] = "✅ User deleted successfully.";
} else {
    $_SESSION['user_message'] = "❌ Failed to delete user.";
}

header("Location: admin_manage_users.php");
exit();
