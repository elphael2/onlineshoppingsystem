<?php
include('includes/db.php');
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = 'user';

    $check = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $message = "Email is already registered.";
    } else {
        $sql = "INSERT INTO users (username, email, password, user_type)
                VALUES ('$username', '$email', '$password', '$user_type')";
        if ($conn->query($sql)) {
            $message = "Registration successful. <a href='login.php'>Login here</a>";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | MyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f3f4f6;
        }
        .form-container {
            max-width: 450px;
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
    <h3 class="text-center mb-4">Create an Account</h3>
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
        <div class="mb-4">
            <label>Password</label>
            <input type="password" name="password" class="form-control rounded-pill" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 rounded-pill">Register</button>
        <div class="text-center mt-3">
            <small>Already have an account? <a href="login.php">Login here</a></small>
            <div class="text-center mt-3">
    <a href="index.php" class="btn btn-outline-secondary rounded-pill w-100">← Return to Home</a>
    </div>
        </div>
    </form>
</div>

</body>
</html>
