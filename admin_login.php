<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    // Simple hardcoded admin for v1
    if ($user === 'admin' && $pass === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | DevSprint</title>
<link rel="stylesheet" href="styles.css">
<style>
body { background: #0a0e27; display: flex; justify-content: center; align-items: center; height: 100vh; color: white; margin: 0; font-family: 'Inter', sans-serif;}
.login-box { background: rgba(255,255,255,0.05); padding: 40px; border-radius: 12px; border: 1px solid rgba(139,92,246,0.2); width: 100%; max-width: 400px;}
.login-box h2 { text-align: center; margin-bottom: 20px; color: #fff;}
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; color: #cbd5e1;}
.form-group input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #475569; background: #1e293b; color: white; box-sizing: border-box;}
.btn { width: 100%; padding: 12px; background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1rem;}
</style>
</head>
<body>
<div class="login-box">
    <h2>Admin Login</h2>
    <?php if(isset($error)) echo "<p style='color:#ef4444;text-align:center;'>$error</p>"; ?>
    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
    <div style="text-align:center; margin-top:20px;">
        <a href="index.php" style="color: #cbd5e1; text-decoration: none;">&larr; Back to Site</a>
    </div>
</div>
</body>
</html>
