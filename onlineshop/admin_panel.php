<?php
session_start();

// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel | MyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .admin-box {
            background-color: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-top: 60px;
        }
        .admin-links a {
            display: block;
            padding: 15px;
            margin-bottom: 15px;
            font-size: 1.1rem;
            border-radius: 8px;
            background-color: #f1f1f1;
            text-decoration: none;
            color: #333;
            transition: all 0.2s;
        }
        .admin-links a:hover {
            background-color: #e9ecef;
            text-decoration: none;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
    <a class="navbar-brand fw-bold" href="#">Admin Panel</a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-light">Hello, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
        <a href="index.php" class="btn btn-outline-light">← Back to Shop</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<!-- Admin Content -->
<div class="container">
    <div class="admin-box mx-auto col-md-8 text-center">
        <h2 class="mb-4">🛠 Admin Dashboard</h2>
        <p class="lead mb-4">Choose an action below to manage your store:</p>

        <div class="admin-links text-start">
            <a href="admin_manage_categories.php">📂 Manage Categories</a>
            <a href="admin_manage_products.php">📦 Manage Products</a>
            <a href="admin_product_images.php">📷 Manage Products Photos</a>
            <a href="admin_manage_orders.php">📝 Manage Orders</a>
            <a href="admin_manage_users.php">👻 Manage Users</a>
        </div>
    </div>
</div>

</body>
</html>
