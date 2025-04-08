<?php
session_start();
include('includes/db.php');

// Admin-only access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_manage_products.php");
    exit();
}

$product_id = (int)$_GET['id'];
$product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit();
}

// Fetch categories for dropdown
$categories = $conn->query("SELECT id, name FROM categories");

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = (float)$_POST['price'];
    $discount_percent = (float)$_POST['discount'];
    $image = $conn->real_escape_string($_POST['image']);
    $category_id = (int)$_POST['category_id'];

    $conn->query("
        UPDATE products 
        SET name='$name', description='$description', price=$price, 
            discount_percent=$discount_percent, image='$image', category_id=$category_id
        WHERE id = $product_id
    ");

    header("Location: admin_manage_products.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .admin-box {
            background-color: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-top: 40px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-4">
    <a class="navbar-brand fw-bold" href="admin_manage_products.php">← Back to Products</a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-light">Admin: <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="admin-box mt-4">
        <h3 class="mb-4">✏️ Edit Product</h3>

        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label>Product Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="col-md-6">
                <label>Category</label>
                <select name="category_id" class="form-select" required>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label>Price ($)</label>
                <input type="number" name="price" step="0.01" class="form-control" value="<?= $product['price'] ?>" required>
            </div>

            <div class="col-md-4">
                <label>Discount (%)</label>
                <input type="number" name="discount" step="0.01" class="form-control" value="<?= $product['discount_percent'] ?>">
            </div>

            <div class="col-md-4">
                <label>Image URL</label>
                <input type="text" name="image" class="form-control" value="<?= htmlspecialchars($product['image']) ?>" required>
            </div>

            <div class="col-md-12">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="col-md-12 d-flex justify-content-between">
                <button type="submit" class="btn btn-success px-4">💾 Save Changes</button>
                <a href="admin_manage_products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        <?php
            $discount_amount = $product['price'] * ($product['discount_percent'] / 100);
            $final_price = $product['price'] - $discount_amount;
            if ($final_price < 0) $final_price = 0;
        ?>

        <div class="mt-4">
            <p><strong>Original Price:</strong> $<?= number_format($product['price'], 2) ?></p>
            <p><strong>Discount:</strong> <?= number_format($product['discount_percent'], 2) ?>%</p>
            <p><strong>Final Price:</strong> $<?= number_format($final_price, 2) ?></p>
        </div>

    </div>
</div>

</body>
</html>
