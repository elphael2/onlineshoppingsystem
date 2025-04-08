<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get billing address
$billing_result = $conn->query("SELECT billing_address FROM users WHERE id = $user_id");
$billing_row = $billing_result->fetch_assoc();
$current_address = $billing_row['billing_address'] ?? '';

// Cart must not be empty
if (empty($_SESSION['cart'])) {
    $_SESSION['checkout_message'] = "Your cart is empty!";
    header("Location: cart.php");
    exit();
}

// Calculate total and savings
$total = 0;
$saved = 0;
foreach ($_SESSION['cart'] as $item) {
    $discount_percent = $item['discount_percent'] ?? 0;
    $original_total = $item['price'] * $item['quantity'];
    $discounted_price = $item['price'] * (1 - $discount_percent / 100);
    $item_total = $discounted_price * $item['quantity'];
    $total += $item_total;
    $saved += ($original_total - $item_total);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | MyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .checkout-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        textarea {
            resize: none;
        }
        .text-line-through {
            text-decoration: line-through;
            color: #888;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand fw-bold" href="index.php">MyShop</a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <a class="btn btn-outline-success" href="cart.php">🛒 Cart</a>
        <span class="text-dark">Welcome, <strong><?= htmlspecialchars($username) ?></strong></span>
        <a class="btn btn-outline-secondary" href="account.php">Account</a>
        <a class="btn btn-outline-warning" href="favourites.php">❤️ Favourites</a>
        <a class="btn btn-outline-danger" href="logout.php">Logout</a>
    </div>
</nav>

<!-- Checkout Content -->
<div class="container mt-5">
    <h2 class="mb-4">💳 Checkout</h2>
    <a href="cart.php" class="btn btn-outline-secondary mb-4">← Return to Cart</a>

    <div class="row">
        <!-- Left: Address & Shipping -->
        <div class="col-md-6">
            <div class="checkout-box mb-4">
                <h5>🏠 Billing Address</h5>
                <form method="POST" action="update_billing_address.php">
                    <label><strong>Current Address:</strong></label>
                    <textarea class="form-control mb-3" rows="3" disabled><?= htmlspecialchars($current_address) ?></textarea>

                    <label for="new_address">Replace with new address:</label>
                    <textarea name="new_address" class="form-control mb-3" rows="3" placeholder="Leave blank to keep current"></textarea>
                    <button type="submit" class="btn btn-outline-primary rounded-pill">Update Address</button>
                </form>
            </div>

            <div class="checkout-box">
                <h5>🚚 Choose Shipping</h5>
                <form id="shipping-form">
                    <select class="form-select" name="shipping_company" id="shipping_company" required>
                        <option value="SF Express">SF Express</option>
                        <option value="DHL">DHL</option>
                        <option value="FedEx">FedEx</option>
                        <option value="Hongkong Post">Hongkong Post</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Right: Order Summary + PayPal -->
        <div class="col-md-6">
            <div class="checkout-box">
                <h5>🧾 Order Summary</h5>
                <ul class="list-group mb-3">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <?php
                            $discount_percent = $item['discount_percent'] ?? 0;
                            $unit_price = $item['price'];
                            $discounted_price = $unit_price * (1 - $discount_percent / 100);
                            $item_total = $discounted_price * $item['quantity'];
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?><br>
                                <?php if ($discount_percent > 0): ?>
                                    <small class="text-muted text-line-through">$<?= number_format($unit_price * $item['quantity'], 2) ?></small>
                                    <small class="text-success ms-2">Save <?= number_format($discount_percent, 0) ?>%</small>
                                <?php endif; ?>
                            </div>
                            <span class="text-end fw-bold text-danger">$<?= number_format($item_total, 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <h4>Total: $<?= number_format($total, 2) ?></h4>
                <?php if ($saved > 0): ?>
                    <p class="text-success">🎉 You saved $<?= number_format($saved, 2) ?> on this order!</p>
                <?php endif; ?>

                <!-- PayPal Button -->
                <div id="paypal-button-container" class="mt-4"></div>
            </div>
        </div>
    </div>
</div>

<!-- Dummy PayPal Button -->
<script src="https://www.paypal.com/sdk/js?client-id=sb&currency=USD"></script>
<script>
paypal.Buttons({
    createOrder: function(data, actions) {
        return actions.order.create({
            purchase_units: [{ amount: { value: '<?= number_format($total, 2, '.', '') ?>' } }]
        });
    },
    onApprove: function(data, actions) {
        alert("✅ Demo payment approved. Please click 'Finish Checkout' below to place your order.");
    }
}).render('#paypal-button-container');
</script>

<!-- Real Finish Checkout Button -->
<form id="checkout-form" method="POST" action="complete_order.php" class="mt-3 text-center">
    <input type="hidden" name="shipping_company" id="shipping_company_input">
    <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill mt-3">Finish Checkout</button>
</form>

<script>
document.getElementById("checkout-form").addEventListener("submit", function (e) {
    const shippingSelect = document.getElementById("shipping_company");
    const billingAddress = <?= json_encode(trim($current_address)) ?>;

    if (!billingAddress) {
        alert("❗ Please enter your billing address before checking out.");
        e.preventDefault(); // stop form submission
        return;
    }

    document.getElementById("shipping_company_input").value = shippingSelect.value;
});
</script>


</body>
</html>
