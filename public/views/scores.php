<?php
$title = 'Word Search - Scores';
$content = '
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 text-primary mb-3">
                    <i class="bi bi-trophy"></i> Leaderboards
                </h1>
                <p class="lead text-muted">See how you rank against other players!</p>
            </div>

            <!-- Filter Controls -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="themeFilter" class="form-label">Filter by Theme</label>
                    <select class="form-select" id="themeFilter">
                        <option value="">All Themes</option>
                        <option value="animals">Animals</option>
                        <option value="geography">Geography</option>
                        <option value="technology">Technology</option>
                        <option value="food">Food</option>
                        <option value="automotive">Automotive</option>
                        <option value="medical">Medical</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="difficultyFilter" class="form-label">Filter by Difficulty</label>
                    <select class="form-select" id="difficultyFilter">
                        <option value="">All Difficulties</option>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="timeFilter" class="form-label">Filter by Time</label>
                    <select class="form-select" id="timeFilter">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
            </div>

            <!-- Scores Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-list-ol me-2"></i>Top Scores
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center">#</th>
                                    <th scope="col">Player</th>
                                    <th scope="col">Theme</th>
                                    <th scope="col">Difficulty</th>
                                    <th scope="col" class="text-center">Words Found</th>
                                    <th scope="col" class="text-center">Time</th>
                                    <th scope="col" class="text-center">Hints Used</th>
                                    <th scope="col" class="text-center">Date</th>
                                </tr>
                            </thead>
                            <tbody id="scoresTableBody">
                                <!-- Scores will be loaded here -->
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-hourglass-split fs-1 d-block mb-2"></i>
                                        Loading scores...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- My Scores Section (if logged in) -->
            <div class="card shadow-sm mt-4" id="myScoresSection" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-person-check me-2"></i>My Scores
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Theme</th>
                                    <th scope="col">Difficulty</th>
                                    <th scope="col" class="text-center">Words Found</th>
                                    <th scope="col" class="text-center">Time</th>
                                    <th scope="col" class="text-center">Hints Used</th>
                                    <th scope="col" class="text-center">Date</th>
                                </tr>
                            </thead>
                            <tbody id="myScoresTableBody">
                                <!-- My scores will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mt-5">
                <div class="col-md-3 mb-3">
                    <div class="card text-center border-primary">
                        <div class="card-body">
                            <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-2" id="totalPlayers">-</h5>
                            <p class="card-text text-muted">Total Players</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <i class="bi bi-controller text-success" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-2" id="totalGames">-</h5>
                            <p class="card-text text-muted">Games Played</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <i class="bi bi-search text-info" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-2" id="totalWords">-</h5>
                            <p class="card-text text-muted">Words Found</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-2" id="avgTime">-</h5>
                            <p class="card-text text-muted">Avg. Time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Scores page functionality
document.addEventListener("DOMContentLoaded", function() {
    loadScores();
    loadStatistics();
    checkAuthStatus();
    
    // Filter change handlers
    document.getElementById("themeFilter").addEventListener("change", loadScores);
    document.getElementById("difficultyFilter").addEventListener("change", loadScores);
    document.getElementById("timeFilter").addEventListener("change", loadScores);
});

function loadScores() {
    const theme = document.getElementById("themeFilter").value;
    const difficulty = document.getElementById("difficultyFilter").value;
    const timeFilter = document.getElementById("timeFilter").value;
    
    // Show loading state
    document.getElementById("scoresTableBody").innerHTML = `
        <tr>
            <td colspan="8" class="text-center text-muted py-4">
                <i class="bi bi-hourglass-split fs-1 d-block mb-2"></i>
                Loading scores...
            </td>
        </tr>
    `;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (theme) params.append("theme", theme);
    if (difficulty) params.append("difficulty", difficulty);
    if (timeFilter) params.append("time", timeFilter);
    
    fetch(`/api/scores?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayScores(data.scores);
            } else {
                showError("Failed to load scores");
            }
        })
        .catch(error => {
            console.error("Error loading scores:", error);
            showError("Failed to load scores");
        });
}

function displayScores(scores) {
    const tbody = document.getElementById("scoresTableBody");
    
    if (!scores || scores.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No scores found for the selected filters
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = scores.map((score, index) => `
        <tr>
            <td class="text-center">
                ${index + 1}
                ${index < 3 ? `<i class="bi bi-trophy-fill text-warning ms-1"></i>` : ""}
            </td>
            <td><strong>${score.username}</strong></td>
            <td>
                <span class="badge bg-secondary">${score.theme}</span>
            </td>
            <td>
                <span class="badge bg-${getDifficultyColor(score.difficulty)}">${score.difficulty}</span>
            </td>
            <td class="text-center">
                <strong>${score.words_found}/${score.total_words}</strong>
            </td>
            <td class="text-center">${formatTime(score.elapsed_time)}</td>
            <td class="text-center">${score.hints_used}</td>
            <td class="text-center">${formatDate(score.created_at)}</td>
        </tr>
    `).join("");
}

function loadStatistics() {
    fetch("/api/scores/stats")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("totalPlayers").textContent = data.stats.total_players;
                document.getElementById("totalGames").textContent = data.stats.total_games;
                document.getElementById("totalWords").textContent = data.stats.total_words;
                document.getElementById("avgTime").textContent = formatTime(data.stats.avg_time);
            }
        })
        .catch(error => {
            console.error("Error loading statistics:", error);
        });
}

function checkAuthStatus() {
    const token = getCookie("auth_token");
    if (token) {
        document.getElementById("myScoresSection").style.display = "block";
        loadMyScores();
    }
}

function loadMyScores() {
    fetch("/api/scores/my", {
        headers: {
            "Authorization": `Bearer ${getCookie("auth_token")}`
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMyScores(data.scores);
            }
        })
        .catch(error => {
            console.error("Error loading my scores:", error);
        });
}

function displayMyScores(scores) {
    const tbody = document.getElementById("myScoresTableBody");
    
    if (!scores || scores.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    You haven\'t played any games yet
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = scores.map(score => `
        <tr>
            <td>
                <span class="badge bg-secondary">${score.theme}</span>
            </td>
            <td>
                <span class="badge bg-${getDifficultyColor(score.difficulty)}">${score.difficulty}</span>
            </td>
            <td class="text-center">
                <strong>${score.words_found}/${score.total_words}</strong>
            </td>
            <td class="text-center">${formatTime(score.elapsed_time)}</td>
            <td class="text-center">${score.hints_used}</td>
            <td class="text-center">${formatDate(score.created_at)}</td>
        </tr>
    `).join("");
}

function getDifficultyColor(difficulty) {
    switch (difficulty) {
        case "easy": return "success";
        case "medium": return "warning";
        case "hard": return "danger";
        default: return "secondary";
    }
}

function formatTime(seconds) {
    if (!seconds) return "-";
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, "0")}`;
}

function formatDate(dateString) {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
}

function showError(message) {
    document.getElementById("scoresTableBody").innerHTML = `
        <tr>
            <td colspan="8" class="text-center text-danger py-4">
                <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                ${message}
            </td>
        </tr>
    `;
}
</script>
';

require_once 'layout.php';
?>
