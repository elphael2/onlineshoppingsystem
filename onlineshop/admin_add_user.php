<?php
session_start();
include('includes/db.php');

// Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $user_type = $conn->real_escape_string($_POST['user_type']);

    // Simple validation
    if (empty($username) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert = $conn->query("
            INSERT INTO users (username, email, password, user_type) 
            VALUES ('$username', '$email', '$hashed_password', '$user_type')
        ");

        if ($insert) {
            $message = "✅ User added successfully.";
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
    <title>Add User | Admin</title>
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
        <h3 class="mb-4">➕ Add New User</h3>

        <?php if ($message): ?>
            <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control rounded-pill" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control rounded-pill" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control rounded-pill" required>
            </div>
            <div class="mb-4">
                <label>User Type</label>
                <select name="user_type" class="form-select rounded-pill" required>
                    <option value="user" selected>User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4">Add User</button>
        </form>
    </div>
</div>

</body>
</html>
