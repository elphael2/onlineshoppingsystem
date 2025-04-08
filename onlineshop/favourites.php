<?php
session_start();
include('includes/db.php');

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch user's favourite products
$fav_query = $conn->query("SELECT f.product_id, p.* FROM favourites f JOIN products p ON f.product_id = p.id WHERE f.user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Favourites | MyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-img {
            height: 160px;
            object-fit: contain;
        }
        .product-card {
            transition: 0.2s;
        }
        .product-card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

<div class="container mt-5">
    <h2 class="mb-4">❤️ Your Favourite Products</h2>

    <?php if ($fav_query && $fav_query->num_rows > 0): ?>
        <div class="row">
            <?php while ($product = $fav_query->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card">
                        <img src="<?= htmlspecialchars($product['image']) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="text-danger fw-bold">$<?= number_format($product['price'], 2) ?></p>
                            <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary w-100">View</a>
                            <a href="remove_from_favourites.php?product_id=<?= $product['id'] ?>" 
                            class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Remove this product from favourites?')">
                            Remove
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You have no favourites yet. Browse <a href="products.php">products</a> to add some!</div>
    <?php endif; ?>
</div>

</body>
</html>