<?php
include('includes/db.php');
session_start();

$token = $_GET['token'] ?? '';
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $conn->real_escape_string($_POST['token']);
    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $result = $conn->query("SELECT * FROM users WHERE reset_token='$token' AND reset_expires > NOW()");

    if ($result->num_rows > 0) {
        $conn->query("UPDATE users SET password='$new_pass', reset_token=NULL, reset_expires=NULL WHERE reset_token='$token'");
        $message = "✅ Password reset successfully. <a href='login.php'>Login</a>";
    } else {
        $message = "❌ Invalid or expired reset token.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 400px;">
    <h4 class="mb-3">🔑 Reset Password</h4>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="mb-3">
            <label>New Password</label>
            <input type="password" name="password" class="form-control rounded-pill" required>
        </div>
        <button type="submit" class="btn btn-success w-100 rounded-pill">Update Password</button>
    </form>
</div>
</body>
</html>
