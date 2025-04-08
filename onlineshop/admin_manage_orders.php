<?php
session_start();
include('includes/db.php');

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch all orders
$orders = $conn->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .order-box {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            padding: 20px;
        }
    </style>
</head>
<body>

<!-- Admin Top Navbar -->
<nav class="navbar navbar-dark bg-dark px-4">
    <a class="navbar-brand fw-bold" href="admin_panel.php">← Admin Panel</a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-light">Admin: <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
    </div>
</nav>


<!-- Main Content -->
<div class="container mt-5">
    <h2 class="mb-4">📦 Manage Orders</h2>

    <?php if ($orders && $orders->num_rows > 0): ?>
        <div class="table-responsive order-box">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Order #</th>
                        <th>User</th>
                        <th>Total</th>
                        <th>Shipping</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Details</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['username']) ?></td>
                            <td>$<?= number_format($order['total'], 2) ?></td>
                            <td><?= htmlspecialchars($order['shipping_company']) ?></td>
                            <td>
                                <form method="POST" action="update_order_status.php" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach (['Confirmed', 'Processing', 'Shipping', 'Delivering','Delivered', 'Cancelled'] as $status): ?>
                                            <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                                                <?= $status ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></td>
                            <td><a href="admin_view_order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                            <td><button class="btn btn-sm btn-success">Save</button></form></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No orders found.</div>
    <?php endif; ?>
</div>

</body>
</html>
