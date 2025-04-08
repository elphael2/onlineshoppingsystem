<?php
session_start();
include('includes/db.php');

// Fetch categories
$categories = [];
$cat_result = $conn->query("SELECT * FROM categories");
if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch 5 random products for the image carousel
$product_slider = [];
$prod_result = $conn->query("SELECT id, name, image FROM products ORDER BY RAND() LIMIT 5");
if ($prod_result && $prod_result->num_rows > 0) {
    while ($row = $prod_result->fetch_assoc()) {
        $product_slider[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Online Shop</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .category-card {
            transition: 0.3s;
        }

        .category-card:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .category-card img {
            height: 180px;
            object-fit: contain;
            width: 100%;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }

        .hero-banner {
            background: url('images/banner.jpg') center/cover no-repeat;
            height: 300px;
            color: black;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
        }

        .carousel img {
            height: 300px;
            object-fit: contain;
        }

        .carousel-caption {
            bottom: 1rem;
            left: 1rem;
            right: auto;
            text-align: left;
        }
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(0, 0, 0, 0.3); /* base background */
            border-radius: 50%;
            transition: background-color 0.3s ease;
        }

        .carousel-control-prev:hover .carousel-control-prev-icon,
        .carousel-control-next:hover .carousel-control-next-icon {
            background-color: rgba(128, 128, 128, 0.7); /* gray hover background */
        }


    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand fw-bold" href="#">MyShop</a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <a class="btn btn-outline-success" href="<?= isset($_SESSION['user_id']) ? 'cart.php' : 'login.php' ?>">🛒 Cart</a>

        <?php if (isset($_SESSION['username'])): ?>
            <span class="text-dark">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>

            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
                <a class="btn btn-warning" href="admin_panel.php">Admin Panel</a>
            <?php endif; ?>

                <a class="btn btn-outline-secondary" href="account.php">Account</a>
                <a class="btn btn-outline-warning" href="favourites.php">❤️ Favourites</a>
                <a class="btn btn-outline-danger" href="logout.php">Logout</a>
            <?php else: ?>
                <a class="btn btn-outline-primary" href="login.php">Login</a>
                <a class="btn btn-primary" href="register.php">Register</a>
            <?php endif; ?>

    </div>
</nav>

<!-- Hero Banner -->
<div class="hero-banner">
    Welcome to Our Online Shop
</div>

<!-- Product Image Carousel -->
<div class="container my-4">
    <?php if (!empty($product_slider)): ?>
        <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner rounded shadow-sm">
                <?php foreach ($product_slider as $index => $product): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <a href="product_detail.php?id=<?= $product['id'] ?>">
                            <img src="<?= htmlspecialchars($product['image']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($product['name']) ?>">
                        </a>
                        <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded px-2 py-1">
                            <h5 class="text-white"><?= htmlspecialchars($product['name']) ?></h5>
                        </div>
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
    <?php endif; ?>
</div>

<!-- Categories Section -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Product Categories</h2>

        <!-- 🔍 Search Bar -->
        <form class="d-flex" method="GET" action="products.php">
            <input class="form-control me-2" type="search" name="search" placeholder="Search products..." aria-label="Search">
            <button class="btn btn-outline-primary" type="submit">Search</button>
        </form>
    </div>

    <div class="row">
        <?php if (empty($categories)): ?>
            <p>No categories found.</p>
        <?php else: ?>
            <?php foreach ($categories as $cat): ?>
                <div class="col-md-3 mb-4">
                    <div class="card category-card">
                        <img src="<?= htmlspecialchars($cat['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($cat['name']) ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($cat['name']) ?></h5>
                            <a href="products.php?category_id=<?= $cat['id'] ?>" class="btn btn-outline-primary">Shop Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

