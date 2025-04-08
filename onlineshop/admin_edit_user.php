<?php
session_start();
include('includes/db.php');

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get user ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_manage_users.php");
    exit();
}

$user_id = (int)$_GET['id'];

// Fetch user info
$user_result = $conn->query("SELECT * FROM users WHERE id = $user_id");
if (!$user_result || $user_result->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $user_result->fetch_assoc();
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $user_type = $conn->real_escape_string($_POST['user_type']);
    $password = $_POST['password'];

    if (empty($username) || empty($email)) {
        $message = "Username and email are required.";
    } else {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->query("
                UPDATE users SET username='$username', email='$email', password='$hashed', user_type='$user_type'
                WHERE id = $user_id
            ");
        } else {
            $update = $conn->query("
                UPDATE users SET username='$username', email='$email', user_type='$user_type'
                WHERE id = $user_id
            ");
        }

        if ($update) {
            $message = "✅ User updated successfully.";
            // Refresh the user data
            $user_result = $conn->query("SELECT * FROM users WHERE id = $user_id");
            $user = $user_result->fetch_assoc();
        } else {
            $message = "❌ Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Admin</title>
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
    <a href="admin_manage_users.php" class="text-white fw-bold text-decoration-none">← Back to Users</a>
    <div class="d-flex align-items-center gap-3">
        <span class="text-white">Admin: <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="admin-box">
        <h3 class="mb-4">✏️ Edit User #<?= $user['id'] ?></h3>

        <?php if ($message): ?>
            <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control rounded-pill" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control rounded-pill" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label>New Password (optional)</label>
                <input type="password" name="password" class="form-control rounded-pill" placeholder="Leave blank to keep current password">
            </div>
            <div class="mb-4">
                <label>User Type</label>
                <select name="user_type" class="form-select rounded-pill">
                    <option value="user" <?= $user['user_type'] === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $user['user_type'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success rounded-pill px-4">Save Changes</button>
        </form>
    </div>
</div>

</body>
</html>
