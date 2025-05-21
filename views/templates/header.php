<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>X Competition Apps</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="index.php" class="logo">X Competition</a>
            <div class="nav-buttons">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <button class="btn btn-primary" onclick="openLoginModal()">Login</button>
                <?php else: ?>
                    <?php if ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'super_admin'): ?>
                        <a href="index.php?page=admin" class="btn btn-primary">Admin Panel</a>
                    <?php endif; ?>
                    <a href="controllers/auth/logout.php" class="btn btn-primary">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <h2>Login</h2>
            <form id="loginForm" action="controllers/auth/login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <p>Don't have an account? <a href="#" onclick="openRegisterModal()">Register here</a></p>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <h2>Register</h2>
            <form id="registerForm" action="controllers/auth/register.php" method="POST">
                <div class="form-group">
                    <label for="reg_email">Email:</label>
                    <input type="email" id="reg_email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">Password:</label>
                    <input type="password" id="reg_password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <p>Already have an account? <a href="#" onclick="openLoginModal()">Login here</a></p>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="container">
        <!-- Content will be injected here -->
