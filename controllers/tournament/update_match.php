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
$team1_score = isset($_POST['team1_score']) ? (int)$_POST['team1_score'] : 0;
$team2_score = isset($_POST['team2_score']) ? (int)$_POST['team2_score'] : 0;

// Validate input
if (!$match_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid match ID']);
    exit;
}

// Get match and tournament details
$stmt = $conn->prepare("
    SELECT m.*, t.created_by, t.format 
    FROM matches m 
    JOIN tournaments t ON m.tournament_id = t.id 
    WHERE m.id = ?
");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$match = $stmt->get_result()->fetch_assoc();

// Check if user has permission to update this match
if ($match['created_by'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to update this match']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Update match scores and status
    $stmt = $conn->prepare("
        UPDATE matches 
        SET team1_score = ?, 
            team2_score = ?, 
            status = 'completed',
            winner_id = CASE 
                WHEN ? > ? THEN team1_id 
                WHEN ? < ? THEN team2_id 
                ELSE NULL 
            END 
        WHERE id = ?
    ");
    $stmt->bind_param("iiiiiii", 
        $team1_score, 
        $team2_score, 
        $team1_score, 
        $team2_score, 
        $team1_score, 
        $team2_score, 
        $match_id
    );
    $stmt->execute();

    // If this is not a battle royal format, update next match
    if ($match['format'] !== 'battle_royal') {
        // Get next match in the tournament
        $next_round = $match['round'] + 1;
        $current_match_order = $match['match_order'];
        $next_match_order = ceil($current_match_order / 2);

        $stmt = $conn->prepare("
            SELECT id, team1_id, team2_id 
            FROM matches 
            WHERE tournament_id = ? 
            AND round = ? 
            AND match_order = ?
        ");
        $stmt->bind_param("iii", $match['tournament_id'], $next_round, $next_match_order);
        $stmt->execute();
        $next_match = $stmt->get_result()->fetch_assoc();

        if ($next_match) {
            // Determine which team slot to update in next match
            $winner_id = $team1_score > $team2_score ? $match['team1_id'] : $match['team2_id'];
            $team_slot = $current_match_order % 2 === 1 ? 'team1_id' : 'team2_id';

            // Update next match with winner
            $stmt = $conn->prepare("
                UPDATE matches 
                SET $team_slot = ? 
                WHERE id = ?
            ");
            $stmt->bind_param("ii", $winner_id, $next_match['id']);
            $stmt->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Match updated successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error updating match: ' . $e->getMessage()]);
}

$conn->close();
