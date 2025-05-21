<?php
// Fetch active tournaments
$query = "SELECT t.*, COUNT(m.id) as match_count 
          FROM tournaments t 
          LEFT JOIN matches m ON t.id = m.tournament_id 
          WHERE t.status = 'active' 
          GROUP BY t.id 
          ORDER BY t.created_at DESC";
$result = $conn->query($query);
?>

<div class="home-container fade-in">
    <h1>Active Tournaments</h1>
    
    <div class="tournament-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($tournament = $result->fetch_assoc()): ?>
                <div class="card tournament-card">
                    <h2><?php echo htmlspecialchars($tournament['name']); ?></h2>
                    <div class="tournament-details">
                        <p>Format: <?php echo str_replace('_', ' ', ucfirst($tournament['format'])); ?></p>
                        <p>Matches: <?php echo $tournament['match_count']; ?></p>
                        <?php if ($tournament['start_date']): ?>
                            <p>Starts: <?php echo date('M d, Y', strtotime($tournament['start_date'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="index.php?page=tournament&id=<?php echo $tournament['id']; ?>" class="btn btn-primary">
                        View Tournament
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card">
                <p>No active tournaments at the moment.</p>
                <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'super_admin')): ?>
                    <a href="index.php?page=admin&section=tournaments" class="btn btn-primary">Create Tournament</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="card register-prompt">
            <h3>Want to create your own tournament?</h3>
            <p>Register now to start organizing competitions!</p>
            <button class="btn btn-primary" onclick="openRegisterModal()">Register Now</button>
        </div>
    <?php endif; ?>
</div>

<style>
.home-container {
    padding: 2rem 0;
}

.tournament-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.tournament-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.tournament-details {
    margin: 1rem 0;
}

.tournament-details p {
    margin: 0.5rem 0;
    color: #666;
}

.register-prompt {
    text-align: center;
    padding: 2rem;
    margin-top: 2rem;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
}

.register-prompt h3 {
    margin-bottom: 1rem;
}

.register-prompt .btn {
    background: white;
    color: var(--primary-color);
    margin-top: 1rem;
}

.register-prompt .btn:hover {
    background: var(--accent-color);
    color: white;
}
</style>
