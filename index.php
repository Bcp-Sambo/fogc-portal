<?php
// index.php - Login Page
session_start();
// In a real app, logic would go here to check credentials
// For now, it's just the Layout.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - FOGC Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; align-items: center; min-height: 100vh;">

    <div class="container">
        <div class="card">
            <div class="logo">
                FOGC PORTAL
            </div>

            <form id="loginForm" action="api/login_mock.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>

                <button type="submit" class="btn btn-primary" id="loginBtn">
                    <span id="btnText">Login Details</span>
                    <!-- Loader hidden by default -->
                    <span id="btnLoader" style="display:none; margin-left:10px;">...</span>
                </button>
            </form>

            <div style="margin-top: 24px; text-align: center; color: var(--color-text-muted); font-size: 0.9rem;">
                <p>Forgot Password?</p>
                <p style="color: var(--color-role-gold);">Contact Super Admin</p>
            </div>
        </div>
    </div>

    <!-- Feedback Toast -->
    <div id="toast" class="toast"></div>

    <script src="assets/js/login.js"></script>
</body>
</html>
<!-- Deployment Trigger v1.0 -->
