<?php require_once 'csrf.php'; ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | DevSprint</title>

<link rel="stylesheet" href="styles.css">

<style>
body{
margin:0;padding:16px;font-family:Arial,sans-serif;
background:url("spacebg.jpg") center/cover no-repeat fixed;
min-height:100vh}
body:before{
content:"";position:fixed;inset:0;
background:rgba(0,0,0,0.6);z-index:-1}

header,nav,footer{text-align:center;color:white}
header{padding:30px 20px;margin-bottom:20px}
header img{
border-radius:10px;margin-bottom:12px;
box-shadow:0 4px 10px rgba(255,255,255,0.2)}
header h1{
margin:10px 0 8px;font-size:2.2rem;font-weight:700;
color:#ffffff;text-shadow:2px 2px 6px rgba(0,0,0,0.5)}
header p{
margin:0;font-size:1.05rem;color:#e0e0e0;font-weight:500}

nav{padding:15px 0;margin-bottom:20px}
nav a{
color:white;font-weight:600;text-decoration:none;
padding:8px 12px;margin:0 5px;border-radius:6px;
transition:.3s}
nav a:hover{
background:rgba(255,105,180,0.8)}

hr{
border:none;height:2px;
background:linear-gradient(90deg,transparent,rgba(255,255,255,0.3),transparent);
margin:20px 0}

.login-container{
max-width:480px;margin:30px auto;
background:linear-gradient(135deg,rgba(255,255,255,0.95) 0%,rgba(240,253,244,0.95) 100%);
padding:40px 35px;border-radius:16px;
box-shadow:0 12px 40px rgba(0,0,0,0.5);
border:2px solid #FFC0CB}

.login-header{
text-align:center;margin-bottom:30px;
padding-bottom:20px;border-bottom:2px solid #FFC0CB}
.login-header h2{
color:#C71585;font-size:2rem;margin-bottom:8px;font-weight:700}
.login-header p{font-size:.95rem;margin:0;color:#6b7280}

.form-group{margin-bottom:24px}
.form-group label{
display:block;font-weight:600;margin-bottom:8px;
color:#48002e;font-size:.95rem}
.form-group input{
width:100%;padding:14px 16px;
border:2px solid #e5e7eb;border-radius:10px;
font-size:1rem;background:#ffffff;box-sizing:border-box;
transition:.3s}
.form-group input:focus{
outline:none;border-color:#FF69B4;
box-shadow:0 0 0 4px rgba(255,105,180,0.2)}

.login-btn{
width:100%;padding:14px 20px;
background:linear-gradient(180deg,#FF69B4,#C71585);
color:#fff;border-radius:10px;font-size:1.05rem;
font-weight:700;cursor:pointer;margin-top:10px;
text-transform:uppercase;letter-spacing:.5px;
transition:.3s;
box-shadow:0 6px 15px rgba(255,105,180,0.3)}
.login-btn:hover{
background:linear-gradient(135deg,#FF69B4,#C71585);
transform:translateY(-2px);
box-shadow:0 8px 20px rgba(199,21,133,0.4)}

.social-divider{
text-align:center;margin:30px 0 20px;
font-size:.95rem;font-weight:600;color:#6b7280}

.social-login{
display:flex;justify-content:center;align-items:center;
gap:20px;padding:20px;
background:rgba(230,251,240,0.5);
border-radius:12px;margin-bottom:25px}
.social-login a{
background:white;padding:12px;border-radius:50%;
border:2px solid #FFC0CB;transition:.3s;
box-shadow:0 4px 10px rgba(0,0,0,0.1)}
.social-login a:hover{
transform:translateY(-4px) scale(1.1);
box-shadow:0 8px 15px rgba(25,135,84,0.2)}
.social-login img{width:32px;height:32px}

.signup-prompt{
text-align:center;margin-top:25px;
padding-top:20px;border-top:1px solid rgba(107,114,128,0.2)}
.signup-prompt p{
color:#6b003a;margin-bottom:12px;font-size:.95rem;font-weight:500}
.signup-prompt a{
text-decoration:none;color:#C71585;font-weight:600;
padding:8px 16px;border-radius:8px;transition:.3s}
.signup-prompt a:hover{
background:#FFC0CB;color:#a0135a}

footer{
padding:25px 20px;margin-top:30px;
box-shadow:0 4px 15px rgba(0,0,0,0.4)}
footer p{margin:5px 0;font-size:.9rem}
footer p:first-child{font-weight:600;font-size:1rem}
</style>
</head>

<body>

<header style="box-shadow:0 6px 20px rgba(0,0,0,0.5);">
<img src="logo.png" width="120"
style="border:2px solid rgba(255,255,255,0.3);">
<h1 style="letter-spacing:1px;">DevSprint</h1>
<p style="text-shadow:1px 1px 3px rgba(0,0,0,0.5);">
Find Nearby Hackathons & Coding Events
</p>
</header>

<hr>

<nav>
<a href="index.html">Home</a> |
<a href="hackathons.html">Hackathons</a> |
<a href="contact.html">Contact</a> |
<a href="login_view.php">Login</a>
</nav>

<hr>

<main class="login-container">
<div class="login-header">
<h2>User Login</h2>
<p>Please enter your login details to continue.</p>
</div>

<form action="login.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

<div class="form-group">
<label>Email Address:</label>
<input type="email" name="email" placeholder="example@gmail.com" required>
</div>

<div class="form-group">
<label>Password:</label>
<input type="password" name="password" placeholder="Enter password" required>
</div>
<input type="submit" value="Login" class="login-btn">
</form>

<h3 class="social-divider">Or join using social media</h3>

<div class="social-login">
<a href="#"><img src="google.png"></a>
<a href="#"><img src="git.png"></a>
<a href="#"><img src="linkedin.png"></a>
</div>

<div class="signup-prompt">
<p>Don't have an account?</p>
<a href="Registerpage_view.php">Create a new account</a>
</div>
</main>

<hr style="margin-top:40px;">

<footer>
<p style="text-shadow:1px 1px 3px rgba(0,0,0,0.4);">
&copy; 2026 DevSprint
</p>
<p>All rights reserved.</p>
</footer>

</body>
</html>
