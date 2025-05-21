<?php
session_start();
require_once 'config/database.php';

// Function to sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Default page is home
$page = isset($_GET['page']) ? sanitize($_GET['page']) : 'home';

// Header
include 'views/templates/header.php';

// Main content
switch($page) {
    case 'home':
        include 'views/pages/home.php';
        break;
    case 'tournament':
        include 'views/pages/tournament.php';
        break;
    case 'admin':
        if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'super_admin')) {
            include 'views/pages/admin/dashboard.php';
        } else {
            header('Location: index.php');
        }
        break;
    default:
        include 'views/pages/home.php';
}

// Footer
include 'views/templates/footer.php';
