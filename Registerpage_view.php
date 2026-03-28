<?php require_once 'csrf.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DevSprint - Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Link to your registration-specific CSS -->
    <link rel="stylesheet" href="regstyles.css">
</head>
<body>

<div class="main-wrapper">
    <div class="card">

        <!-- Header -->
        <div class="header">
            <img src="https://i.postimg.cc/wjfrK4K0/WP-Logo-Reg.png" alt="Logo">
            <h1>DevSprint</h1>
            <p>Find hackathons near you</p>
        </div>

        <hr>

        <!-- Form Section -->
        <div class="form-section">
            <h2>User Registration</h2>

            <!-- FORM: POST to register.php -->
            <form id="registerForm" method="POST" action="register.php">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">


                <!-- First Name -->
                <label>First Name</label>
                <input type="text" id="firstName" name="firstName">
                <small class="error" id="firstNameError"></small>

                <!-- Middle Name -->
                <label>Middle Name</label>
                <input type="text" id="middleName" name="middleName">

                <!-- Last Name -->
                <label>Last Name</label>
                <input type="text" id="lastName" name="lastName">
                <small class="error" id="lastNameError"></small>

                <!-- Email -->
                <label>Email</label>
                <input type="text" id="email" name="email">
                <small class="error" id="emailError"></small>

                <!-- Phone -->
                <label>Mobile Number</label>
                <input type="text" id="phone" name="phone">
                <small class="error" id="phoneError"></small>

                <!-- Password -->
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="e.g. Password@123">
                </div>
                <div id="passwordRules" class="password-rules" style="font-size: 0.85em; color: #94a3b8; margin-top:5px; margin-bottom:10px;">Password must contain at least 8 characters, a number, and a special character.</div>
                <small class="error" id="passwordError"></small>

                <!-- Confirm Password -->
                <label>Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirmPassword" name="confirmPassword">
                </div>
                <small class="error" id="confirmError"></small>

                <!-- City -->
                <label>City</label>
                <input type="text" id="city" name="city">
                <small class="error" id="cityError"></small>

                <!-- Buttons -->
                <div class="button-group">
                    <input type="submit" value="Register" name="register">
                    <input type="reset" value="Clear">
                </div>

            </form>
        </div>

    </div>
</div>

<!-- JS -->
<script src="reglogic.js"></script>

</body>
</html>