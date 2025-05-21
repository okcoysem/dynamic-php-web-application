<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("SELECT id, email, role FROM users WHERE email = ? AND password = MD5(?)");
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
}

$stmt->close();
$conn->close();
