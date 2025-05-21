<?php
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$tournament_id = (int)$_GET['id'];

// Get tournament details
$query = "SELECT t.*, u.email as creator_email 
          FROM tournaments t 
          LEFT JOIN users u ON t.created_by = u.id 
          WHERE t.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();

if (!$tournament) {
    header('Location: index.php');
    exit;
}

// Get matches
$query = "SELECT m.*, 
          t1.name as team1_name, 
          t2.name as team2_name 
          FROM matches m 
          LEFT JOIN teams t1 ON m.team1_id = t1.id 
          LEFT JOIN teams t2 ON m.team2_id = t2.id 
          WHERE m.tournament_id = ? 
          ORDER BY m.round, m.match_order";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="tournament-container fade-in">
    <h1><?php echo htmlspecialchars($tournament['name']); ?></h1>
    
    <div class="tournament-info card">
        <p>Format: <?php echo str_replace('_', ' ', ucfirst($tournament['format'])); ?></p>
        <p>Status: <?php echo ucfirst($tournament['status']); ?></p>
        <?php if ($tournament['start_date']): ?>
            <p>Start Date: <?php echo date('M d, Y', strtotime($tournament['start_date'])); ?></p>
        <?php endif; ?>
        <p>Created by: <?php echo htmlspecialchars($tournament['creator_email']); ?></p>
    </div>

    <?php if ($tournament['format'] !== 'battle_royal'): ?>
    <div class="bracket-container">
        <div class="bracket">
            <?php
            $currentRound = 0;
            echo '<div class="round">';
            foreach ($matches as $match) {
                if ($match['round'] != $currentRound) {
                    echo '</div><div class="round">';
                    $currentRound = $match['round'];
                }
                ?>
                <div class="match <?php echo $match['status']; ?>">
                    <div class="team">
                        <?php echo $match['team1_name'] ?? 'TBD'; ?>
                        <?php if ($match['status'] !== 'pending'): ?>
                            <span class="score"><?php echo $match['team1_score']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="team">
                        <?php echo $match['team2_name'] ?? 'TBD'; ?>
                        <?php if ($match['status'] !== 'pending'): ?>
                            <span class="score"><?php echo $match['team2_score']; ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($match['schedule']): ?>
                        <div class="match-time">
                            <?php echo date('M d, H:i', strtotime($match['schedule'])); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($match['stream_url']): ?>
                        <a href="<?php echo htmlspecialchars($match['stream_url']); ?>" 
                           target="_blank" 
                           class="stream-link">
                            Watch Live
                        </a>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['user_role']) && 
                            ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'super_admin') && 
                            $tournament['created_by'] == $_SESSION['user_id']): ?>
                        <button class="btn btn-primary" 
                                onclick="openMatchUpdateModal(<?php echo $match['id']; ?>)">
                            Update Match
                        </button>
                    <?php endif; ?>
                </div>
                <?php
            }
            echo '</div>';
            ?>
        </div>
    </div>
    <?php else: ?>
    <!-- Battle Royal Format -->
    <div class="battle-royal-container">
        <div class="card">
            <h3>Battle Royal Match</h3>
            <?php
            $match = $matches[0]; // Battle Royal has only one match
            if ($match['status'] === 'completed'): ?>
                <div class="winner">
                    <h4>Winner</h4>
                    <p><?php echo htmlspecialchars($match['winner_name']); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($match['stream_url']): ?>
                <a href="<?php echo htmlspecialchars($match['stream_url']); ?>" 
                   target="_blank" 
                   class="btn btn-primary">
                    Watch Live
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Match Update Modal -->
<div id="matchUpdateModal" class="modal">
    <div class="modal-content">
        <h2>Update Match</h2>
        <form id="updateMatchForm" class="update-match-form">
            <input type="hidden" name="match_id" id="match_id">
            <div class="form-group">
                <label>Team 1 Score:</label>
                <input type="number" name="team1_score" required min="0">
            </div>
            <div class="form-group">
                <label>Team 2 Score:</label>
                <input type="number" name="team2_score" required min="0">
            </div>
            <div class="form-group">
                <label>Stream URL:</label>
                <input type="url" name="stream_url">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>

<script src="assets/js/tournament.js"></script>

<style>
.tournament-container {
    padding: 2rem 0;
}

.tournament-info {
    margin-bottom: 2rem;
}

.bracket-container {
    overflow-x: auto;
    padding: 2rem 0;
}

.match {
    position: relative;
    margin: 1rem 0;
}

.team {
    padding: 0.5rem;
    border-bottom: 1px solid #ddd;
}

.score {
    float: right;
    font-weight: bold;
}

.match-time {
    font-size: 0.8rem;
    color: #666;
    margin-top: 0.5rem;
}

.stream-link {
    display: inline-block;
    margin-top: 0.5rem;
    color: var(--primary-color);
    text-decoration: none;
}

.stream-link:hover {
    text-decoration: underline;
}

.battle-royal-container {
    text-align: center;
    padding: 2rem;
}

.winner {
    margin: 1rem 0;
    padding: 1rem;
    background: var(--accent-color);
    border-radius: var(--border-radius);
}
</style>
