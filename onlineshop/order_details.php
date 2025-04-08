<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if this order belongs to the logged-in user
$order_result = $conn->query("
    SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id
");

if (!$order_result || $order_result->num_rows == 0) {
    echo "<p class='text-danger'>Order not found or access denied.</p>";
    exit();
}

$order = $order_result->fetch_assoc();

// Note: If you don’t have an order_items table, we’ll simulate the items from session
// In real setup, use a dedicated order_items table to store itemized details

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?= $order['id'] ?> | MyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand fw-bold" href="index.php">MyShop</a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <a class="btn btn-outline-success" href="cart.php">🛒 Cart</a>
        <span class="text-dark">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
        <a class="btn btn-outline-danger" href="logout.php">Logout</a>
    </div>
</nav>

<!-- Order Detail -->
<div class="container mt-5">
    <div class="card p-4 shadow-sm">
        <h4 class="mb-3">🧾 Order Details</h4>
        <p><strong>Order ID:</strong> #<?= $order['id'] ?></p>
        <p><strong>Date:</strong> <?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></p>
        <p><strong>Status:</strong> <?= $order['status'] ?></p>
        <p><strong>Shipping Company:</strong> <?= $order['shipping_company'] ?></p>
        <hr>
        <h5>Order Summary</h5>

        <h5 class="mt-4">🛍 Items in this order</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $items = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
                $total_items = 0;
                while ($item = $items->fetch_assoc()):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total_items += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-end">$<?= number_format($item['price'], 2) ?></td>
                    <td class="text-end">$<?= number_format($subtotal, 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>


        <p><em>(Note: Itemized product list not available in demo. You can implement `order_items` table for that.)</em></p>

        <h4 class="mt-3">Total: $<?= number_format($order['total'], 2) ?></h4>
                    <!-- Enquiry Section -->
        <hr class="my-4">
        <h5>❓ Have a question about this order?</h5>

        <form action="submit_enquiry.php" method="POST" class="mb-4">
            <input type="hidden" name="order_id" value="<?= $order_id ?>">
            <textarea name="message" class="form-control mb-2" rows="3" required placeholder="Type your question here..."></textarea>
            <button type="submit" class="btn btn-primary">Send Enquiry</button>
        </form>

        <?php
        $enquiry_result = $conn->query("
            SELECT * FROM order_enquiries 
            WHERE order_id = $order_id AND user_id = $user_id 
            ORDER BY created_at DESC
        ");

        if ($enquiry_result && $enquiry_result->num_rows > 0):
        ?>
            <h6 class="mb-3">📬 Your Previous Enquiries</h6>
            <ul class="list-group">
                <?php while ($row = $enquiry_result->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <strong>You:</strong> <?= nl2br(htmlspecialchars($row['message'])) ?><br>
                        <small class="text-muted"><?= $row['created_at'] ?></small>
                        <?php if (!empty($row['reply'])): ?>
                            <hr>
                            <strong class="text-success">Reply:</strong> <?= nl2br(htmlspecialchars($row['reply'])) ?>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>

        <a href="account.php" class="btn btn-outline-secondary mt-3">← Back to Account</a>
    </div>
</div>


</body>
</html>
