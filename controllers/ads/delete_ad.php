<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Check if user is super admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$ad_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (!$ad_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid advertisement ID']);
    exit;
}

// Delete advertisement
$stmt = $conn->prepare("DELETE FROM advertisements WHERE id = ?");
$stmt->bind_param("i", $ad_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Advertisement deleted successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting advertisement'
    ]);
}

$conn->close();
