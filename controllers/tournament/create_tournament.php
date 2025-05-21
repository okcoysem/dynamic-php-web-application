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

// Validate and sanitize input
$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
$format = $_POST['format'];
$status = $_POST['status'];
$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
$end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

// Validate tournament format
$valid_formats = ['single_elimination', 'double_elimination', 'round_robin', 'battle_royal'];
if (!in_array($format, $valid_formats)) {
    echo json_encode(['success' => false, 'message' => 'Invalid tournament format']);
    exit;
}

// Validate status
$valid_statuses = ['active', 'inactive'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Create tournament
    $stmt = $conn->prepare("
        INSERT INTO tournaments (
            name, format, status, created_by, start_date, end_date
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssiss", 
        $name, 
        $format, 
        $status, 
        $_SESSION['user_id'],
        $start_date,
        $end_date
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Error creating tournament');
    }
    
    $tournament_id = $conn->insert_id;

    // Create initial matches structure based on format
    // This will be populated with actual teams later
    switch ($format) {
        case 'single_elimination':
            // Create placeholder matches for a single elimination bracket
            $stmt = $conn->prepare("
                INSERT INTO matches (
                    tournament_id, round, match_order, status
                ) VALUES (?, ?, ?, 'pending')
            ");
            
            // Start with 8 matches in first round (can accommodate up to 16 teams)
            for ($round = 1; $round <= 4; $round++) {
                $matches_in_round = pow(2, 4-$round);
                for ($order = 1; $order <= $matches_in_round; $order++) {
                    $stmt->bind_param("iii", $tournament_id, $round, $order);
                    if (!$stmt->execute()) {
                        throw new Exception('Error creating match structure');
                    }
                }
            }
            break;

        case 'double_elimination':
            // Similar to single elimination but with additional losers bracket
            // Implementation will be handled when teams are added
            break;

        case 'round_robin':
            // Matches will be created when teams are added
            break;

        case 'battle_royal':
            // Create single match for battle royal
            $stmt = $conn->prepare("
                INSERT INTO matches (
                    tournament_id, round, match_order, status
                ) VALUES (?, 1, 1, 'pending')
            ");
            $stmt->bind_param("i", $tournament_id);
            if (!$stmt->execute()) {
                throw new Exception('Error creating battle royal match');
            }
            break;
    }

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Tournament created successfully',
        'tournament_id' => $tournament_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
