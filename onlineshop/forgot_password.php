<?php
include('includes/db.php');
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $user_query = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($user_query->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
        $conn->query("UPDATE users SET reset_token='$token', reset_expires='$expiry' WHERE email='$email'");

        $reset_link = "http://yourdomain.com/reset_password.php?token=$token";
        
        // Normally you'd send an email, but for now:
        $message = "Reset link (for demo): <a href='$reset_link'>$reset_link</a>";
    } else {
        $message = "No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 400px;">
    <h4 class="mb-3">🔐 Forgot Password</h4>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control rounded-pill" required>
        </div>
        <button class="btn btn-primary w-100 rounded-pill">Send Reset Link</button>
        <div class="text-center mt-3">
            <a href="login.php" class="btn btn-outline-secondary rounded-pill w-100">← Back to Login</a>
        </div>
    </form>
</div>
</body>
</html>
