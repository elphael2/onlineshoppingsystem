<?php
session_start();
include('includes/db.php');

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $image = $conn->real_escape_string($_POST['image']);
    if ($name && $image) {
        $conn->query("INSERT INTO categories (name, image) VALUES ('$name', '$image')");
        $success = "Category added successfully.";
    } else {
        $error = "Please fill in both fields.";
    }
}

// Update category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $image = $conn->real_escape_string($_POST['image']);
    $conn->query("UPDATE categories SET name = '$name', image = '$image' WHERE id = $id");
    header("Location: admin_manage_categories.php");
    exit();
}

// Delete category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM categories WHERE id = $id");
    header("Location: admin_manage_categories.php");
    exit();
}

// Get category to edit
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM categories WHERE id = $edit_id");
    if ($edit_result && $edit_result->num_rows === 1) {
        $edit_category = $edit_result->fetch_assoc();
    }
}

// Fetch all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories | Admin</title>
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
    <a class="navbar-brand fw-bold" href="admin_panel.php">← Admin Panel</a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-light">Admin: <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="admin-box mt-4">
        <h3 class="mb-4">📂 Manage Categories</h3>

        <!-- Feedback -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Add or Edit Form -->
        <?php if ($edit_category): ?>
            <form method="POST" class="row g-3 mb-4">
                <input type="hidden" name="id" value="<?= $edit_category['id'] ?>">
                <div class="col-md-4">
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($edit_category['name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <input type="text" name="image" class="form-control" value="<?= htmlspecialchars($edit_category['image']) ?>" required>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" name="update_category" class="btn btn-success w-100">Update</button>
                    <a href="admin_manage_categories.php" class="btn btn-secondary w-100">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <form method="POST" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="name" class="form-control" placeholder="Category Name" required>
                </div>
                <div class="col-md-6">
                    <input type="text" name="image" class="form-control" placeholder="Image URL" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_category" class="btn btn-primary w-100">Add</button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Category Table -->
        <table class="table table-bordered table-striped mt-3">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Image</th>
                    <th style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <tr>
                        <td><?= $cat['id'] ?></td>
                        <td><?= htmlspecialchars($cat['name']) ?></td>
                        <td><img src="<?= htmlspecialchars($cat['image']) ?>" width="100" height="60" style="object-fit: cover;"></td>
                        <td>
                            <a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                            <a href="?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>
