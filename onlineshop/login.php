<?php
include('includes/db.php');
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE LOWER(email) = LOWER('$email')");

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];

            // Redirect based on user type
            if ($user['user_type'] === 'admin') {
                header("Location: admin_panel.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $message = "Invalid password.";
        }
    } else {
        $message = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | MyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f3f4f6;
        }
        .form-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #ff5000;
            border: none;
        }
        .btn-primary:hover {
            background-color: #e04400;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h3 class="text-center mb-4">Login to MyShop</h3>

    <?php if ($message): ?>
        <div class="alert alert-warning"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control rounded-pill" required>
        </div>
        <div class="mb-4">
            <label>Password</label>
            <input type="password" name="password" class="form-control rounded-pill" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 rounded-pill">Login</button>
        <div class="text-center mt-3">
            <small>Don't have an account? <a href="register.php">Register here</a></small>
            <div class="text-center mt-3">
    <a href="index.php" class="btn btn-outline-secondary rounded-pill w-100">← Return to Home</a>
    </div>
        </div>
    </form>
    <div class="text-center mt-2">
    <a href="forgot_password.php">Forgot Password?</a>
</div>

</div>

</body>
</html>

