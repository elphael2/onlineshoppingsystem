<?php
session_start();
include('includes/db.php');

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle cart actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];

    if ($_GET['action'] === 'increase') {
        $_SESSION['cart'][$product_id]['quantity'] += 1;
    } elseif ($_GET['action'] === 'decrease') {
        $_SESSION['cart'][$product_id]['quantity'] -= 1;
        if ($_SESSION['cart'][$product_id]['quantity'] < 1) {
            unset($_SESSION['cart'][$product_id]);
        }
    } elseif ($_GET['action'] === 'remove') {
        unset($_SESSION['cart'][$product_id]);
    }

    header("Location: cart.php");
    exit();
}

// Calculate total with discount
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart | MyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-img {
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        .quantity-btn {
            border: none;
            background-color: transparent;
            font-weight: bold;
            font-size: 1.2rem;
            color: #ff5000;
        }
        .cart-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
        }
        .text-line-through {
            text-decoration: line-through;
            color: #999;
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
            <a class="btn btn-outline-secondary" href="account.php">Account</a>
            <a class="btn btn-outline-warning" href="favourites.php">❤️ Favourites</a>
            <a class="btn btn-outline-danger" href="logout.php">Logout</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Cart Content -->
<div class="container mt-5">
    <h2 class="mb-4">🛒 Your Shopping Cart</h2>

    <?php if (!empty($_SESSION['cart'])): ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-md-8">
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <?php
                        $discount_percent = $item['discount_percent'] ?? 0;
                        $unit_price = $item['price'];
                        $discounted_price = $unit_price * (1 - $discount_percent / 100);
                        $subtotal = $discounted_price * $item['quantity'];
                        $total += $subtotal;
                    ?>
                    <div class="card mb-3 p-3 shadow-sm">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-2">
                                <img src="<?= htmlspecialchars($item['image']) ?>" class="cart-img" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="col-md-4">
                                <h5><?= htmlspecialchars($item['name']) ?></h5>
                                <p class="mb-1">
                                    <?php if ($discount_percent > 0): ?>
                                        <span class="text-line-through">$<?= number_format($unit_price, 2) ?></span>
                                        <span class="text-danger fw-bold ms-2">$<?= number_format($discounted_price, 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">$<?= number_format($unit_price, 2) ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-3 text-center">
                                <form method="get" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="action" value="decrease">
                                    <button type="submit" class="quantity-btn">−</button>
                                </form>
                                <span class="mx-2"><?= $item['quantity'] ?></span>
                                <form method="get" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="action" value="increase">
                                    <button type="submit" class="quantity-btn">＋</button>
                                </form>
                            </div>
                            <div class="col-md-2 text-end">
                                <p>$<?= number_format($subtotal, 2) ?></p>
                            </div>
                            <div class="col-md-1 text-end">
                                <a href="cart.php?action=remove&id=<?= $item['id'] ?>" class="text-danger" title="Remove">🗑️</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary -->
            <div class="col-md-4">
                <div class="cart-summary shadow-sm">
                    <h4>Total: $<?= number_format($total, 2) ?></h4>
                    <a href="index.php" class="btn btn-outline-secondary w-100 mb-2">Continue Shopping</a>
                    <a href="checkout.php" class="btn btn-primary w-100">Checkout</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center mt-5">
    <h4 class="mt-4">Your cart is empty 😔</h4>
    <p class="text-muted">Looks like you haven't added anything yet.</p>
    <a href="index.php" class="btn btn-primary rounded-pill px-4 mt-3">Start Shopping</a>
    </div>
    <?php endif; ?>
</div>

</body>
</html>

