<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$match_id = isset($_POST['match_id']) ? (int)$_POST['match_id'] : 0;
$stream_url = isset($_POST['stream_url']) ? filter_var($_POST['stream_url'], FILTER_SANITIZE_URL) : '';

// Validate input
if (!$match_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid match ID']);
    exit;
}

if (!empty($stream_url) && !filter_var($stream_url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid stream URL']);
    exit;
}

// Check if user has permission to update this match
$stmt = $conn->prepare("
    SELECT t.created_by 
    FROM matches m 
    JOIN tournaments t ON m.tournament_id = t.id 
    WHERE m.id = ?
");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result || $result['created_by'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to update this match']);
    exit;
}

// Update stream URL
$stmt = $conn->prepare("UPDATE matches SET stream_url = ? WHERE id = ?");
$stmt->bind_param("si", $stream_url, $match_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Stream URL updated successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating stream URL'
    ]);
}

$conn->close();
