<?php
session_start();
include('includes/db.php');

// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int)$_GET['id'];

// Fetch product
$query = "SELECT * FROM products WHERE id = $product_id";
$result = $conn->query($query);

if (!$result || $result->num_rows == 0) {
    echo "<p>Product not found.</p>";
    exit();
}

$product = $result->fetch_assoc();

// Category name
$category_name = "Uncategorized";
if (!empty($product['category_id'])) {
    $cat_result = $conn->query("SELECT name FROM categories WHERE id = " . (int)$product['category_id']);
    if ($cat_row = $cat_result->fetch_assoc()) {
        $category_name = $cat_row['name'];
    }
}

// Discount calculation
$discount_percent = (float)($product['discount_percent'] ?? 0);
$original_price = $product['price'];
$discount_amount = $original_price * ($discount_percent / 100);
$final_price = $original_price - $discount_amount;
if ($final_price < 0) $final_price = 0;

// Stock status
$status = $product['status'] ?? 'In stock';
$status_class = 'text-success';
if ($status === 'Few left') $status_class = 'text-warning';
if ($status === 'Out of stock') $status_class = 'text-danger';

// Fetch extra product images
$image_result = $conn->query("SELECT image_url FROM product_images WHERE product_id = $product_id");
$images = [];
if ($image_result && $image_result->num_rows > 0) {
    while ($img = $image_result->fetch_assoc()) {
        $images[] = $img['image_url'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> | MyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .product-img-large {
        max-height: 400px;
        width: 100%;
        object-fit: contain;
        border-radius: 12px;
    }
    .price-original {
        text-decoration: line-through;
        color: #888;
    }
    .price-discount {
        color: #e53935;
        font-weight: bold;
        font-size: 1.4rem;
    }
    .badge-status {
        font-size: 0.9rem;
        padding: 6px 12px;
        border-radius: 12px;
    }

    /* Carousel styling */
    #productCarousel {
        max-width: 100%;
        margin-top: 30px;
    }
    #productCarousel .carousel-inner {
        border-radius: 12px;
        background-color: #f9f9f9;
    }
    #productCarousel img {
        height: 350px;
        object-fit: contain;
        padding: 10px;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(128, 128, 128, 0.5);
        border-radius: 50%;
        transition: background-color 0.3s ease;
    }

    .carousel-control-prev:hover .carousel-control-prev-icon,
    .carousel-control-next:hover .carousel-control-next-icon {
        background-color: rgba(100, 100, 100, 0.7);
    }

    .carousel-control-prev,
    .carousel-control-next {
        width: 6%;
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

<!-- Product Detail Section -->
<div class="container mt-5">
    <?php if (isset($_SESSION['fav_message'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= $_SESSION['fav_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['fav_message']); ?>
    <?php endif; ?>

    <a href="products.php?category_id=<?= (int)$product['category_id'] ?>" class="btn btn-outline-secondary mb-4">
        ← Back to Products
    </a>

    <div class="row">
        <!-- Main Image -->
        <div class="col-md-6">
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img-large">
            
            <!-- Image Carousel -->
            <?php if (!empty($images)): ?>
                <div class="text-center">
                    <div id="productCarousel" class="carousel slide mt-4 mx-auto" data-bs-ride="carousel" style="max-width: 600px;">
                        <div class="carousel-inner rounded shadow-sm">
                            <?php foreach ($images as $index => $img_url): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="<?= htmlspecialchars($img_url) ?>" class="d-block w-100" alt="Additional Image <?= $index + 1 ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Info -->
        <div class="col-md-6">
            <h2><?= htmlspecialchars($product['name']) ?></h2>

            <?php if ($discount_percent > 0): ?>
                <p>
                    <span class="price-original">$<?= number_format($original_price, 2) ?></span>
                    <span class="price-discount ms-2">$<?= number_format($final_price, 2) ?></span><br>
                    <small class="text-success">You save <?= number_format($discount_percent, 0) ?>%!</small>
                </p>
            <?php else: ?>
                <h4 class="text-danger">$<?= number_format($original_price, 2) ?></h4>
            <?php endif; ?>

            <p><strong>Category:</strong> <?= htmlspecialchars($category_name) ?></p>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <p class="mb-3"><strong>Status:</strong> 
                <span class="badge badge-status <?= $status_class ?> bg-opacity-25"><?= htmlspecialchars($status) ?></span>
            </p>

            <?php if ($status === 'Out of stock'): ?>
                <button class="btn btn-secondary btn-lg rounded-pill mt-2" disabled>Out of Stock</button>
            <?php else: ?>
                <a href="<?= isset($_SESSION['user_id']) ? 'add_to_cart.php?id=' . $product['id'] : 'login.php' ?>" class="btn btn-primary btn-lg rounded-pill mt-2">
                    Add to Cart
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="add_to_favourites.php" method="POST" class="d-inline">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill mt-3">
                        ❤️ Add to Favourites
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
