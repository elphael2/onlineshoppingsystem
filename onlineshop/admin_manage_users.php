<?php
session_start();
include('includes/db.php');

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .admin-navbar {
            background-color: #212529;
            padding: 12px 30px;
        }
        .admin-box {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            padding: 25px;
        }
    </style>
</head>
<body>

<!-- Admin Top Bar -->
<nav class="navbar admin-navbar d-flex justify-content-between align-items-center">
    <a href="admin_panel.php" class="text-white fw-bold text-decoration-none">← Admin Panel</a>
    <div class="d-flex align-items-center gap-3">
        <span class="text-white">Admin: <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="admin-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>👥 Manage Users</h3>
            <a href="admin_add_user.php" class="btn btn-success">+ Add User</a>
        </div>

        <?php if (isset($_SESSION['user_message'])): ?>
            <div class="alert alert-info"><?= $_SESSION['user_message'] ?></div>
            <?php unset($_SESSION['user_message']); ?>
        <?php endif; ?>

        <?php if ($users && $users->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Billing Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= ucfirst($user['user_type']) ?></td>
                                <td><?= nl2br(htmlspecialchars($user['billing_address'])) ?></td>
                                <td>
                                    <a href="admin_edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                    <a href="admin_delete_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this user?');">
                                       Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

