<?php
session_start();
include('includes/db.php');

// Admin-only access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch categories for dropdown
$categories = $conn->query("SELECT id, name FROM categories");

// Add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = (float)$_POST['price'];
    $image = $conn->real_escape_string($_POST['image']);
    $category_id = (int)$_POST['category_id'];
    $status = $conn->real_escape_string($_POST['status']);

    if ($name && $price && $image && $category_id && $status) {
        $conn->query("
            INSERT INTO products (name, description, price, image, category_id, status)
            VALUES ('$name', '$description', $price, '$image', $category_id, '$status')
        ");
        $success = "Product added successfully.";
    } else {
        $error = "Please fill out all required fields.";
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $product_id = (int)$_POST['product_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE products SET status = '$status' WHERE id = $product_id");
    header("Location: admin_manage_products.php");
    exit();
}

// Delete product
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: admin_manage_products.php");
    exit();
}

// Fetch all products
$products = $conn->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .admin-box {
            background-color: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-top: 40px;
        }
        .product-img-preview {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-4">
    <a class="navbar-brand fw-bold" href="admin_panel.php">← Admin Panel</a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-light">Admin: <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="admin-box mt-4">
        <h3 class="mb-4">📦 Manage Products</h3>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Add Product Form -->
        <form method="POST" class="row g-3 mb-5">
            <div class="col-md-3">
                <input type="text" name="name" class="form-control" placeholder="Product Name" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="price" class="form-control" placeholder="Price" required>
            </div>
            <div class="col-md-6">
                <input type="text" name="image" class="form-control" placeholder="Image URL" required>
            </div>
            <div class="col-md-6">
                <select name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6">
                <select name="status" class="form-select" required>
                    <option value="">Select Stock Status</option>
                    <option value="In stock">In stock</option>
                    <option value="Few left">Few left</option>
                    <option value="Out of stock">Out of stock</option>
                </select>
            </div>
            <div class="col-md-12">
                <textarea name="description" class="form-control" placeholder="Product Description" rows="2" required></textarea>
            </div>
            <div class="col-md-12">
                <button type="submit" name="add_product" class="btn btn-primary w-100">Add Product</button>
            </div>
        </form>

        <!-- Product Table -->
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><img src="<?= htmlspecialchars($row['image']) ?>" class="product-img-preview"></td>
                        <td>$<?= number_format($row['price'], 2) ?></td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td>
                            <form method="POST" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                <select name="status" class="form-select form-select-sm">
                                    <?php foreach (['In stock', 'Few left', 'Out of stock'] as $status_option): ?>
                                        <option value="<?= $status_option ?>" <?= $row['status'] === $status_option ? 'selected' : '' ?>>
                                            <?= $status_option ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-success">Save</button>
                            </form>
                        </td>
                        <td>
                            <a href="admin_edit_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>
