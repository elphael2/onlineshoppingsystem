<?php
session_start();
include('includes/db.php');

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle add image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_image'])) {
    $product_id = (int)$_POST['product_id'];
    $image_url = $conn->real_escape_string($_POST['image_url']);

    if ($product_id > 0 && $image_url) {
        $conn->query("INSERT INTO product_images (product_id, image_url) VALUES ($product_id, '$image_url')");
        $message = "Image added successfully!";
    } else {
        $error = "All fields are required.";
    }
}

// Handle delete image
if (isset($_GET['delete'])) {
    $img_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM product_images WHERE id = $img_id");
    header("Location: admin_product_images.php");
    exit();
}

// Fetch all images with product name
$images = $conn->query("
    SELECT pi.id, pi.product_id, pi.image_url, p.name AS product_name 
    FROM product_images pi 
    JOIN products p ON pi.product_id = p.id
    ORDER BY pi.id DESC
");

// Fetch all products for dropdown
$products = $conn->query("SELECT id, name FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Product Images | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .img-thumb {
            height: 60px;
            width: auto;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-4">
    <a class="navbar-brand fw-bold" href="admin_panel.php">← Admin Panel</a>
    <div class="ms-auto text-white">Admin: <?= htmlspecialchars($_SESSION['username']) ?></div>
</nav>

<div class="container mt-5">
    <h3 class="mb-4">🖼 Manage Product Carousel Images</h3>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Add Image Form -->
    <form method="POST" class="row g-3 mb-5">
        <input type="hidden" name="add_image" value="1">
        <div class="col-md-4">
            <select name="product_id" class="form-select" required>
                <option value="">Select Product</option>
                <?php while ($p = $products->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-6">
            <input type="text" name="image_url" class="form-control" placeholder="Image URL" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Add Image</button>
        </div>
    </form>

    <!-- Image List -->
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>URL</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($img = $images->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($img['product_name']) ?></td>
                    <td><img src="<?= htmlspecialchars($img['image_url']) ?>" class="img-thumb" alt=""></td>
                    <td><?= htmlspecialchars($img['image_url']) ?></td>
                    <td>
                        <a href="?delete=<?= $img['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this image?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
