<?php
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
    header('Location: index.php');
    exit;
}

// Get user's tournaments
$stmt = $conn->prepare("
    SELECT t.*, 
           COUNT(DISTINCT tm.id) as team_count,
           COUNT(DISTINCT m.id) as match_count 
    FROM tournaments t 
    LEFT JOIN teams tm ON t.id = tm.tournament_id 
    LEFT JOIN matches m ON t.id = m.tournament_id 
    WHERE t.created_by = ? 
    GROUP BY t.id 
    ORDER BY t.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$tournaments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user's active advertisements if super admin
$advertisements = [];
if ($_SESSION['user_role'] === 'super_admin') {
    $result = $conn->query("
        SELECT * FROM advertisements 
        ORDER BY created_at DESC
    ");
    if ($result) {
        $advertisements = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<div class="admin-dashboard fade-in">
    <h1>Admin Dashboard</h1>

    <!-- Create Tournament Section -->
    <div class="card">
        <h2>Create New Tournament</h2>
        <form id="createTournamentForm" class="form">
            <div class="form-group">
                <label for="tournament_name">Tournament Name:</label>
                <input type="text" id="tournament_name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="tournament_format">Format:</label>
                <select id="tournament_format" name="format" class="form-control" required>
                    <option value="single_elimination">Single Elimination</option>
                    <option value="double_elimination">Double Elimination</option>
                    <option value="round_robin">Round Robin</option>
                    <option value="battle_royal">Battle Royal</option>
                </select>
            </div>

            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="datetime-local" id="start_date" name="start_date" class="form-control">
            </div>

            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="datetime-local" id="end_date" name="end_date" class="form-control">
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Create Tournament</button>
        </form>
    </div>

    <!-- My Tournaments Section -->
    <div class="card">
        <h2>My Tournaments</h2>
        <div class="tournament-list">
            <?php if (!empty($tournaments)): ?>
                <?php foreach ($tournaments as $tournament): ?>
                    <div class="tournament-item">
                        <h3><?php echo htmlspecialchars($tournament['name']); ?></h3>
                        <div class="tournament-stats">
                            <span>Teams: <?php echo $tournament['team_count']; ?></span>
                            <span>Matches: <?php echo $tournament['match_count']; ?></span>
                            <span>Status: <?php echo ucfirst($tournament['status']); ?></span>
                        </div>
                        <div class="tournament-actions">
                            <a href="index.php?page=tournament&id=<?php echo $tournament['id']; ?>" 
                               class="btn btn-primary">View</a>
                            <button onclick="openEditTournamentModal(<?php echo $tournament['id']; ?>)" 
                                    class="btn btn-primary">Edit</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No tournaments created yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($_SESSION['user_role'] === 'super_admin'): ?>
    <!-- Advertisement Management Section -->
    <div class="card">
        <h2>Advertisement Management</h2>
        <button onclick="openCreateAdModal()" class="btn btn-primary">Create New Ad</button>
        
        <div class="ad-list">
            <?php if (!empty($advertisements)): ?>
                <?php foreach ($advertisements as $ad): ?>
                    <div class="ad-item">
                        <div class="ad-info">
                            <h3><?php echo htmlspecialchars($ad['title']); ?></h3>
                            <p>Status: <?php echo ucfirst($ad['status']); ?></p>
                            <p>Period: <?php echo date('M d, Y', strtotime($ad['start_date'])); ?> - 
                                      <?php echo date('M d, Y', strtotime($ad['end_date'])); ?></p>
                        </div>
                        <div class="ad-preview">
                            <img src="<?php echo htmlspecialchars($ad['banner_url']); ?>" 
                                 alt="Ad Preview" style="max-width: 200px;">
                        </div>
                        <div class="ad-actions">
                            <button onclick="openEditAdModal(<?php echo $ad['id']; ?>)" 
                                    class="btn btn-primary">Edit</button>
                            <button onclick="deleteAd(<?php echo $ad['id']; ?>)" 
                                    class="btn btn-primary">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No advertisements created yet.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.admin-dashboard {
    padding: 2rem 0;
}

.tournament-list, .ad-list {
    margin-top: 1rem;
}

.tournament-item, .ad-item {
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    background: white;
}

.tournament-stats {
    margin: 0.5rem 0;
}

.tournament-stats span {
    margin-right: 1rem;
    color: #666;
}

.tournament-actions, .ad-actions {
    margin-top: 1rem;
}

.tournament-actions .btn, .ad-actions .btn {
    margin-right: 0.5rem;
}

.ad-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ad-info {
    flex: 1;
}

.ad-preview {
    margin: 0 1rem;
}

.ad-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .ad-item {
        flex-direction: column;
    }
    
    .ad-preview {
        margin: 1rem 0;
    }
    
    .ad-actions {
        flex-direction: row;
        justify-content: flex-start;
    }
}
</style>

<script>
// Tournament management functions
function openEditTournamentModal(tournamentId) {
    // Implementation will be added
}

// Advertisement management functions
function openCreateAdModal() {
    // Implementation will be added
}

function openEditAdModal(adId) {
    // Implementation will be added
}

function deleteAd(adId) {
    if (confirm('Are you sure you want to delete this advertisement?')) {
        $.ajax({
            url: 'controllers/ads/delete_ad.php',
            type: 'POST',
            data: { id: adId },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            }
        });
    }
}
</script>
