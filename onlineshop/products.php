<?php
session_start();
include('includes/db.php');

// Check if a search query is present
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$category_name = 'All Products';

if ($search_term !== '') {
    $safe_search = $conn->real_escape_string($search_term);
    $query = "SELECT * FROM products WHERE name LIKE '%$safe_search%'";
    $category_name = "Search results for \"$search_term\"";
} elseif ($category_id > 0) {
    $cat_result = $conn->query("SELECT name FROM categories WHERE id = $category_id");
    if ($cat_result && $cat_row = $cat_result->fetch_assoc()) {
        $category_name = $cat_row['name'];
    } else {
        $category_name = 'Category Not Found';
        $category_id = 0;
    }
    $query = "SELECT * FROM products WHERE category_id = $category_id";
} else {
    $query = "SELECT * FROM products";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products | MyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .product-card {
            transition: 0.2s;
        }
        .product-card:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .product-img {
            height: 180px;
            object-fit: contain;
        }
        .price-original {
            text-decoration: line-through;
            color: #888;
        }
        .price-discount {
            color: #e53935;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .badge-stock {
            font-size: 0.8rem;
        }
        .badge-in-stock {
            background-color: #28a745;
        }
        .badge-few-left {
            background-color: #ffc107;
        }
        .badge-out-of-stock {
            background-color: #dc3545;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand fw-bold" href="index.php">MyShop</a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <a class="btn btn-outline-success" href="<?= isset($_SESSION['user_id']) ? 'cart.php' : 'login.php' ?>">🛒 Cart</a>
        <?php if (isset($_SESSION['username'])): ?>
            <span class="text-dark">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            <a class="btn btn-outline-secondary" href="account.php">Account</a>
            <a class="btn btn-outline-warning" href="favourites.php">❤️ Favourites</a>
            <a class="btn btn-outline-danger" href="logout.php">Logout</a>
        <?php else: ?>
            <a class="btn btn-outline-primary" href="login.php">Login</a>
            <a class="btn btn-primary" href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Product Grid -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= htmlspecialchars($category_name) ?></h2>
        <form class="d-flex" method="GET" action="products.php">
            <input class="form-control me-2" type="search" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search_term) ?>">
            <button class="btn btn-outline-primary" type="submit">Search</button>
        </form>
    </div>

    <a href="index.php" class="btn btn-outline-secondary mb-4">← Back to Categories</a>

    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $price = $row['price'];
                    $discount_percent = $row['discount_percent'] ?? 0;
                    $discounted = $discount_percent > 0 ? $price * (1 - $discount_percent / 100) : $price;

                    $status = $row['status'] ?? 'In stock';
                    $status_class = 'badge-in-stock';
                    if ($status === 'Few left') $status_class = 'badge-few-left';
                    if ($status === 'Out of stock') $status_class = 'badge-out-of-stock';
                ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card">
                        <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($row['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>

                            <?php if ($discount_percent > 0): ?>
                                <p class="card-text mb-1">
                                    <span class="price-original">$<?= number_format($price, 2) ?></span>
                                    <span class="price-discount ms-2">$<?= number_format($discounted, 2) ?></span>
                                </p>
                                <small class="text-success">Save <?= number_format($discount_percent, 0) ?>%</small>
                            <?php else: ?>
                                <p class="card-text text-muted">$<?= number_format($price, 2) ?></p>
                            <?php endif; ?>

                            <div class="mt-2 mb-3">
                                <span class="badge badge-stock <?= $status_class ?>"><?= htmlspecialchars($status) ?></span>
                            </div>

                            <a href="product_detail.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary w-100">View</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
