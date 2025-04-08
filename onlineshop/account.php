<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$message = "";

// Handle billing address update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['billing_address'])) {
    $billing_address = $conn->real_escape_string($_POST['billing_address']);
    $conn->query("UPDATE users SET billing_address = '$billing_address' WHERE id = $user_id");
    $message = "Billing address updated successfully.";
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $new_username = $conn->real_escape_string($_POST['username']);
    $new_email = $conn->real_escape_string($_POST['email']);

    if (!empty($_POST['password'])) {
        $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET username='$new_username', email='$new_email', password='$new_password' WHERE id = $user_id");
    } else {
        $conn->query("UPDATE users SET username='$new_username', email='$new_email' WHERE id = $user_id");
    }

    $_SESSION['username'] = $new_username;
    $_SESSION['profile_message'] = "Profile updated successfully.";
    header("Location: account.php");
    exit();
}

// Get current billing address
$result = $conn->query("SELECT billing_address FROM users WHERE id = $user_id");
$row = $result->fetch_assoc();
$current_address = $row['billing_address'] ?? '';

// Get user info for profile editing
$userInfo = $conn->query("SELECT username, email FROM users WHERE id = $user_id")->fetch_assoc();

// Get order history
$orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account | MyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .account-box {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        textarea {
            resize: none;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand fw-bold" href="index.php">MyShop</a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <a class="btn btn-outline-success" href="cart.php">🛒 Cart</a>
        <?php if (isset($_SESSION['username'])): ?>
            <span class="text-dark">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            <a class="btn btn-outline-danger" href="logout.php">Logout</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Main Container -->
<div class="container mt-5">
    <h2 class="mb-4">👤 My Account</h2>

    <!-- Alerts -->
    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['profile_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['profile_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['profile_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['checkout_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['checkout_success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['checkout_success']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Billing Address -->
        <div class="col-md-6">
            <div class="account-box mb-4">
                <h5>🏠 Billing Address</h5>
                <form method="POST">
                    <textarea name="billing_address" class="form-control rounded-3 mb-3" rows="4" required><?= htmlspecialchars($current_address) ?></textarea>
                    <button type="submit" class="btn btn-primary rounded-pill">Save Address</button>
                </form>
            </div>
        </div>

        <!-- Order History -->
        <div class="col-md-6">
            <div class="account-box">
                <h5>📦 Order History</h5>
                <?php if ($orders && $orders->num_rows > 0): ?>
                    <ul class="list-group">
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Order #<?= $order['id'] ?></strong><br>
                                    <small><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?> — <?= $order['status'] ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="d-block">$<?= number_format($order['total'], 2) ?></span>
                                    <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary mt-1">View</a>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No orders yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Profile -->
    <div class="row mb-5 mt-4">
        <div class="col-md-12">
            <div class="account-box">
                <h5>🧾 Edit Profile</h5>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control rounded-3" value="<?= htmlspecialchars($userInfo['username']) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control rounded-3" value="<?= htmlspecialchars($userInfo['email']) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>New Password <small class="text-muted">(leave blank to keep current)</small></label>
                            <input type="password" name="password" class="form-control rounded-3">
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary rounded-pill mt-2">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
