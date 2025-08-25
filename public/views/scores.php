<?php
$pageTitle = 'Leaderboards - Word Search Game';
$pageContent = '
<div class="container-fluid">
    <!-- Header with Navigation -->
    <div class="row mb-4">
        <div class="col-6">
            <h1 class="display-5 fw-bold text-dark">
                <i class="bi bi-trophy text-warning me-3"></i>
                Leaderboards
            </h1>
        </div>
        <div class="col-6 text-end">
            <a href="/" class="btn btn-outline-primary me-2">
                <i class="bi bi-arrow-left me-2"></i>Dashboard
            </a>
            <a href="/" class="btn btn-success">
                <i class="bi bi-play-circle me-2"></i>New Game
            </a>
        </div>
    </div>

    <!-- Difficulty Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                <div class="btn-group" role="group" id="difficultyTabs">
                    <button type="button" class="btn btn-outline-secondary difficulty-tab active" data-difficulty="easy">
                        <i class="bi bi-star-fill text-success me-2"></i>Easy
                    </button>
                    <button type="button" class="btn btn-outline-secondary difficulty-tab" data-difficulty="medium">
                        <i class="bi bi-star-fill text-warning me-2"></i>Medium
                    </button>
                    <button type="button" class="btn btn-outline-secondary difficulty-tab" data-difficulty="hard">
                        <i class="bi bi-star-fill text-danger me-2"></i>Hard
                    </button>
                    <button type="button" class="btn btn-outline-secondary difficulty-tab" data-difficulty="expert">
                        <i class="bi bi-star-fill text-dark me-2"></i>Expert
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Leaderboard -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h4 class="mb-0 text-success">
                        <i class="bi bi-star-fill text-success me-2"></i>
                        <span id="leaderboardTitle">Easy Leaderboard</span>
                    </h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 ps-4" style="width: 80px;">Rank</th>
                                    <th class="border-0" style="width: 200px;">Player</th>
                                    <th class="border-0" style="width: 120px;">Time</th>
                                    <th class="border-0" style="width: 120px;">Hints Used</th>
                                    <th class="border-0" style="width: 150px;">Date</th>
                                </tr>
                            </thead>
                            <tbody id="scoresTableBody">
                                <!-- Scores will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Loading State -->
                    <div id="loadingScores" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading leaderboard...</p>
                    </div>
                    
                    <!-- No Scores State -->
                    <div id="noScores" class="text-center py-5" style="display: none;">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No scores found</h5>
                        <p class="text-muted">Be the first to complete a puzzle on this difficulty!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Stats Cards -->
    <div class="row mt-4">
        <!-- Your Best Times -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 text-muted">
                        <i class="bi bi-graph-up me-2"></i>Your Best Times
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3">
                                <h6 class="text-success mb-1">Easy</h6>
                                <h4 class="mb-0" id="bestTimeEasy">--</h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3">
                                <h6 class="text-warning mb-1">Medium</h6>
                                <h4 class="mb-0" id="bestTimeMedium">--</h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3">
                                <h6 class="text-danger mb-1">Hard</h6>
                                <h4 class="mb-0" id="bestTimeHard">--</h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3">
                                <h6 class="text-dark mb-1">Expert</h6>
                                <h4 class="mb-0" id="bestTimeExpert">--</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Achievements -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 text-muted">
                        <i class="bi bi-trophy me-2"></i>Achievements
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3">
                                <h6 class="text-primary mb-1">Games Completed</h6>
                                <h4 class="mb-0" id="gamesCompleted">0</h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3">
                                <h6 class="text-success mb-1">Average Time</h6>
                                <h4 class="mb-0" id="avgTime">--</h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3">
                                <h6 class="text-warning mb-1">Hints Used</h6>
                                <h4 class="mb-0" id="totalHints">0</h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3">
                                <h6 class="text-info mb-1">Success Rate</h6>
                                <h4 class="mb-0" id="successRate">0%</h4>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';

include 'layout.php';
?>

<script>
// Set authentication state for scores page
window.serverAuthState = {
    isLoggedIn: <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>,
    username: "<?= htmlspecialchars($_SESSION['username'] ?? '') ?>"
};

// Initialize scores page
$(document).ready(function() {
    loadScores();
    
    // Show my stats if user is logged in
    if (window.serverAuthState.isLoggedIn) {
        loadMyStats();
        $("#myStatsCard").show();
    }
    
    // Setup filter event listeners
    $("#applyFilters").on("click", loadScores);
    $("#resetFilters").on("click", resetFilters);
    
    // Auto-apply filters when selections change
    $("#themeFilter, #difficultyFilter, #timeFilter").on("change", loadScores);
});

// Load scores with current filters
function loadScores(page = 1) {
    const filters = {
        theme: $("#themeFilter").val(),
        difficulty: $("#difficultyFilter").val(),
        timeRange: $("#timeFilter").val(),
        page: page
    };
    
    $("#loadingScores").show();
    $("#scoresTableBody").empty();
    $("#noScores").hide();
    $("#scoresPagination").hide();
    
    $.ajax({
        url: "/api/scores",
        method: "GET",
        data: filters,
        success: function(response) {
            $("#loadingScores").hide();
            
            if (response.success && response.scores.length > 0) {
                renderScores(response.scores);
                renderPagination(response.pagination);
            } else {
                $("#noScores").show();
            }
        },
        error: function(xhr) {
            $("#loadingScores").hide();
            $("#noScores").show();
            console.error("Failed to load scores:", xhr.responseText);
        }
    });
}

// Render scores in the table
function renderScores(scores) {
    const tbody = $("#scoresTableBody");
    tbody.empty();
    
    scores.forEach((score, index) => {
        const rankBadge = getRankBadge(index + 1);
        const row = $(`
            <tr>
                <td class="ps-4">
                    <span class="badge ${rankBadge} fs-6">#${index + 1}</span>
                </td>
                <td>
                    <i class="bi bi-person-circle me-2"></i>
                    ${score.username || "Guest"}
                </td>
                <td>
                    <i class="bi bi-stopwatch me-1"></i>
                    <strong>${formatTime(score.elapsed_time)}</strong>
                </td>
                <td>
                    <i class="bi bi-lightbulb me-1"></i>
                    <span class="text-success">${score.hints_used}</span>
                </td>
                <td>
                    <i class="bi bi-calendar me-1"></i>
                    <small class="text-muted">${formatDate(score.created_at)}</small>
                </td>
            </tr>
        `);
        tbody.append(row);
    });
}

// Load user statistics
// Load user statistics for both cards
function loadUserStats() {
    if (!window.serverAuthState.isLoggedIn) return;
    
    $.ajax({
        url: "/api/scores/my/stats",
        method: "GET",
        headers: {
            "Authorization": `Bearer ${localStorage.getItem("authToken")}`
        },
        success: function(response) {
            if (response.success) {
                updateBestTimes(response.stats);
                updateAchievements(response.stats);
            }
        },
        error: function(xhr) {
            console.error("Failed to load user stats:", xhr.responseText);
        }
    });
}

// Update Best Times card
function updateBestTimes(stats) {
    $("#bestTimeEasy").text(stats.best_time_easy ? formatTime(stats.best_time_easy) : "--");
    $("#bestTimeMedium").text(stats.best_time_medium ? formatTime(stats.best_time_medium) : "--");
    $("#bestTimeHard").text(stats.best_time_hard ? formatTime(stats.best_time_hard) : "--");
    $("#bestTimeExpert").text(stats.best_time_expert ? formatTime(stats.best_time_expert) : "--");
}

// Update Achievements card
function updateAchievements(stats) {
    $("#gamesCompleted").text(stats.total_games || 0);
    $("#avgTime").text(stats.avg_time ? formatTime(stats.avg_time) : "--");
    $("#totalHints").text(stats.total_hints || 0);
    $("#successRate").text(stats.success_rate ? `${stats.success_rate}%` : "0%");
}

// Reset all filters
function resetFilters() {
    $("#themeFilter").val("");
    $("#difficultyFilter").val("");
    $("#timeFilter").val("all");
    loadScores();
}

// Get rank badge styling
function getRankBadge(rank) {
    if (rank === 1) return "bg-warning text-dark";
    if (rank === 2) return "bg-secondary text-white";
    if (rank === 3) return "bg-danger text-white";
    return "bg-primary text-white";
}

// Utility functions
function getDifficultyColor(difficulty) {
    switch (difficulty) {
        case "easy": return "success";
        case "medium": return "warning";
        case "hard": return "danger";
        default: return "secondary";
    }
}

function formatTime(seconds) {
    if (!seconds) return "--";
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, "0")}:${secs.toString().padStart(2, "0")}`;
}

function formatDate(dateString) {
    if (!dateString) return "--";
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function renderPagination(pagination) {
    if (!pagination || pagination.total_pages <= 1) return;
    
    const paginationEl = $("#scoresPagination");
    const ul = paginationEl.find("ul");
    ul.empty();
    
    // Previous button
    if (pagination.current_page > 1) {
        ul.append(`
            <li class="page-item">
                <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
            </li>
        `);
    }
    
    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        const active = i === pagination.current_page ? "active" : "";
        ul.append(`
            <li class="page-item ${active}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `);
    }
    
    // Next button
    if (pagination.current_page < pagination.total_pages) {
        ul.append(`
            <li class="page-item">
                <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
            </li>
        `);
    }
    
    // Handle pagination clicks
    ul.on("click", ".page-link", function(e) {
        e.preventDefault();
        const page = $(this).data("page");
        if (page) {
            loadScores(page);
        }
    });
    
    paginationEl.show();
}
</script>
