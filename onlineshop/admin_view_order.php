<?php
session_start();
include('includes/db.php');

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Order ID validation
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_manage_orders.php");
    exit();
}

$order_id = (int)$_GET['id'];

// Fetch order and user info
$order_query = $conn->query("
    SELECT o.*, u.username, u.email, u.billing_address 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = $order_id
");

if (!$order_query || $order_query->num_rows === 0) {
    echo "<div class='container mt-5 alert alert-danger'>Order not found.</div>";
    exit();
}

$order = $order_query->fetch_assoc();

// Fetch order items
$items_query = $conn->query("
    SELECT * FROM order_items WHERE order_id = $order_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Order #<?= $order_id ?> | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">  
    <style>
        body {
            background-color: #f8f9fa;
        }

        .admin-navbar {
            background-color: #212529;
            padding: 12px 30px;
        }

        .admin-navbar .text-white {
            margin-bottom: 0;
        }

        .order-box {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            padding: 25px;
        }

        .section-title {
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            padding-bottom: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar admin-navbar d-flex justify-content-between align-items-center">
    <a href="admin_manage_orders.php" class="text-white fw-bold text-decoration-none">← Admin Panel</a>
    <div class="d-flex align-items-center gap-3">
        <span class="text-white">Admin: <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<!-- Content -->
<div class="container mt-5">
    <div class="order-box">
        <h3 class="mb-4">🧾 Order #<?= $order['id'] ?> Details</h3>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="section-title">Customer Info</div>
                <p><strong>Username:</strong> <?= htmlspecialchars($order['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                <p><strong>Billing Address:</strong><br><?= nl2br(htmlspecialchars($order['billing_address'])) ?></p>
            </div>
            <div class="col-md-6">
                <div class="section-title">Order Info</div>
                <p><strong>Date:</strong> <?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
                <p><strong>Shipping:</strong> <?= htmlspecialchars($order['shipping_company']) ?></p>
                <p><strong>Total:</strong> <span class="text-danger fw-bold">$<?= number_format($order['total'], 2) ?></span></p>
            </div>
        </div>

        <div class="section-title">Order Items</div>
        <?php if ($items_query && $items_query->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items_query->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
                    <!-- Enquiry Section -->
        <div class="section-title mt-5">Customer Enquiries</div>

        <?php
        $enquiry_result = $conn->query("
            SELECT * FROM order_enquiries 
            WHERE order_id = $order_id 
            ORDER BY created_at DESC
        ");

        if ($enquiry_result && $enquiry_result->num_rows > 0):
        ?>
            <div class="list-group">
                <?php while ($enquiry = $enquiry_result->fetch_assoc()): ?>
                    <div class="list-group-item mb-3 border rounded">
                        <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($enquiry['message'])) ?></p>
                        <p class="text-muted"><small>Sent on <?= $enquiry['created_at'] ?></small></p>

                        <?php if (!empty($enquiry['reply'])): ?>
                            <div class="border-start ps-3 mt-2">
                                <strong class="text-success">Reply:</strong><br>
                                <?= nl2br(htmlspecialchars($enquiry['reply'])) ?>
                            </div>
                        <?php else: ?>
                            <form action="reply_enquiry.php" method="POST" class="mt-3">
                                <input type="hidden" name="enquiry_id" value="<?= $enquiry['id'] ?>">
                                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                                <textarea name="reply" class="form-control mb-2" rows="2" placeholder="Reply to this enquiry..." required></textarea>
                                <button type="submit" class="btn btn-sm btn-success">Send Reply</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">No enquiries for this order.</p>
        <?php endif; ?>

        <?php else: ?>
            <p>No items found for this order.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
