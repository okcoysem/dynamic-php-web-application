// Tournament Management Functions
class TournamentManager {
    constructor() {
        this.currentTournament = null;
        this.matches = [];
    }

    // Initialize tournament
    initTournament(format, teams) {
        switch(format) {
            case 'single_elimination':
                return this.createSingleElimination(teams);
            case 'double_elimination':
                return this.createDoubleElimination(teams);
            case 'round_robin':
                return this.createRoundRobin(teams);
            case 'battle_royal':
                return this.createBattleRoyal(teams);
            default:
                throw new Error('Invalid tournament format');
        }
    }

    // Single Elimination Tournament
    createSingleElimination(teams) {
        const numTeams = teams.length;
        const rounds = Math.ceil(Math.log2(numTeams));
        const totalMatches = Math.pow(2, rounds) - 1;
        const matches = [];

        // First round matches
        let matchIndex = 0;
        for (let i = 0; i < numTeams / 2; i++) {
            matches.push({
                round: 1,
                match_order: i + 1,
                team1_id: teams[i * 2]?.id || null,
                team2_id: teams[i * 2 + 1]?.id || null,
                status: 'pending'
            });
            matchIndex++;
        }

        // Create subsequent rounds
        for (let round = 2; round <= rounds; round++) {
            const matchesInRound = Math.pow(2, rounds - round);
            for (let i = 0; i < matchesInRound; i++) {
                matches.push({
                    round: round,
                    match_order: i + 1,
                    team1_id: null,
                    team2_id: null,
                    status: 'pending'
                });
                matchIndex++;
            }
        }

        return matches;
    }

    // Double Elimination Tournament
    createDoubleElimination(teams) {
        const winnersMatches = this.createSingleElimination(teams);
        const losersMatches = [];
        const rounds = Math.ceil(Math.log2(teams.length));

        // Create losers bracket
        for (let round = 1; round < rounds; round++) {
            const matchesInRound = Math.pow(2, rounds - round - 1);
            for (let i = 0; i < matchesInRound; i++) {
                losersMatches.push({
                    round: round,
                    match_order: i + 1,
                    bracket: 'losers',
                    team1_id: null,
                    team2_id: null,
                    status: 'pending'
                });
            }
        }

        // Final matches
        losersMatches.push({
            round: rounds,
            match_order: 1,
            bracket: 'final',
            team1_id: null,
            team2_id: null,
            status: 'pending'
        });

        return [...winnersMatches, ...losersMatches];
    }

    // Round Robin Tournament
    createRoundRobin(teams) {
        const matches = [];
        const numTeams = teams.length;

        for (let i = 0; i < numTeams; i++) {
            for (let j = i + 1; j < numTeams; j++) {
                matches.push({
                    round: 1,
                    match_order: matches.length + 1,
                    team1_id: teams[i].id,
                    team2_id: teams[j].id,
                    status: 'pending'
                });
            }
        }

        return matches;
    }

    // Battle Royal Tournament
    createBattleRoyal(teams) {
        return [{
            round: 1,
            match_order: 1,
            team1_id: null, // Will store all team IDs in the match details
            team2_id: null,
            status: 'pending',
            battle_royal: true,
            teams: teams.map(t => t.id)
        }];
    }

    // Update match result
    updateMatch(matchId, team1Score, team2Score) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: 'controllers/tournament/update_match.php',
                type: 'POST',
                data: {
                    match_id: matchId,
                    team1_score: team1Score,
                    team2_score: team2Score
                },
                success: function(response) {
                    resolve(JSON.parse(response));
                },
                error: function(xhr, status, error) {
                    reject(error);
                }
            });
        });
    }

    // Update streaming URL
    updateStreamUrl(matchId, streamUrl) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: 'controllers/tournament/update_stream.php',
                type: 'POST',
                data: {
                    match_id: matchId,
                    stream_url: streamUrl
                },
                success: function(response) {
                    resolve(JSON.parse(response));
                },
                error: function(xhr, status, error) {
                    reject(error);
                }
            });
        });
    }
}

// Initialize tournament manager
const tournamentManager = new TournamentManager();

// Event handlers for tournament management
$(document).ready(function() {
    // Create tournament form handler
    $('#createTournamentForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.ajax({
            url: 'controllers/tournament/create_tournament.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    window.location.href = 'index.php?page=tournament&id=' + data.tournament_id;
                } else {
                    alert(data.message);
                }
            }
        });
    });

    // Match update handler
    $('.update-match-form').on('submit', function(e) {
        e.preventDefault();
        const matchId = $(this).data('match-id');
        const team1Score = $(this).find('[name="team1_score"]').val();
        const team2Score = $(this).find('[name="team2_score"]').val();

        tournamentManager.updateMatch(matchId, team1Score, team2Score)
            .then(response => {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            })
            .catch(error => {
                alert('Error updating match: ' + error);
            });
    });

    // Stream URL update handler
    $('.update-stream-form').on('submit', function(e) {
        e.preventDefault();
        const matchId = $(this).data('match-id');
        const streamUrl = $(this).find('[name="stream_url"]').val();

        tournamentManager.updateStreamUrl(matchId, streamUrl)
            .then(response => {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            })
            .catch(error => {
                alert('Error updating stream URL: ' + error);
            });
    });
});
