<?php
session_start();
include('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    $_SESSION['checkout_message'] = "Your cart is empty!";
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$shipping = $conn->real_escape_string($_POST['shipping_company']);
$order_date = date('Y-m-d H:i:s');

// Calculate total using discounted prices
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $discount_percent = $item['discount_percent'] ?? 0;
    $discounted_price = $item['price'] * (1 - $discount_percent / 100);
    $total += $discounted_price * $item['quantity'];
}

// Insert into orders table
$insert_order = "
    INSERT INTO orders (user_id, order_date, total, status, shipping_company)
    VALUES ($user_id, '$order_date', $total, 'Confirmed', '$shipping')
";

if ($conn->query($insert_order)) {
    $order_id = $conn->insert_id;

    // Insert order items
    foreach ($_SESSION['cart'] as $item) {
        $product_id = (int)$item['id'];
        $product_name = $conn->real_escape_string($item['name']);
        $quantity = (int)$item['quantity'];
        $discount_percent = $item['discount_percent'] ?? 0;
        $discounted_price = $item['price'] * (1 - $discount_percent / 100);

        $conn->query("
            INSERT INTO order_items (order_id, product_id, product_name, quantity, price)
            VALUES ($order_id, $product_id, '$product_name', $quantity, $discounted_price)
        ");
    }

    // Clear cart
    unset($_SESSION['cart']);

    // Set success message
    $_SESSION['checkout_success'] = "🎉 Order #$order_id placed successfully with $shipping!";
    header("Location: account.php");
    exit();
} else {
    echo "❌ Error placing order: " . $conn->error;
}
