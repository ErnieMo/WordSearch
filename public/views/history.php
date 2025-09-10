<?php
$pageTitle = 'Game History - Word Search Game';

// Check if user is logged in and is admin
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// If logged in but isadmin not in session, fetch it from database
if ($isLoggedIn && !isset($_SESSION['isadmin'])) {
    try {
        $db = new \App\Services\DatabaseService();
        $user = $db->fetchOne(
            'SELECT isadmin FROM users WHERE id = :id',
            ['id' => $_SESSION['user_id']]
        );
        if ($user) {
            $_SESSION['isadmin'] = $user['isadmin'];
        }
    } catch (Exception $e) {
        error_log("Error fetching admin status: " . $e->getMessage());
    }
}

$pageContent = '
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4 history-header">
                <h1 class="h2 mb-0 history-title">
                    <i class="bi bi-clock-history me-2 text-primary"></i>
                    Game History
                </h1>
            </div>

            <!-- Game History Table (Desktop) -->
            <div class="card history-table">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        Your Games
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Game</th>
                                    <th>Theme</th>
                                    <th>Difficulty</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Started</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <tr id="loadingHistory">
                                    <td colspan="8" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading your game history...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Game History Cards (Mobile) -->
            <div class="history-cards">
                <div id="loadingHistoryCards" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading your game history...</p>
                </div>
                
                <div id="historyCardsContainer">
                    <!-- Cards will be loaded here -->
                </div>
            </div>
            
            <!-- No Games Message -->
            <div id="noGames" class="text-center py-5" style="display: none;">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="text-muted mt-3">No Games Yet</h4>
                <p class="text-muted">Start playing to see your game history here!</p>
                <a href="/" class="btn btn-primary">
                    <i class="bi bi-play-circle me-2"></i>Start New Game
                </a>
            </div>
        </div>
    </div>
</div>


';

include 'layout.php';
?>

<script>
let gameHistory = [];

// Load game history when page loads
$(document).ready(function() {
    // Setup event listeners first
    setupHistoryEventListeners();
    
    // Check authentication status
    checkAuthStatus();
});

function setupHistoryEventListeners() {
    // No event listeners needed for history page
}

function loadGameHistory() {
    // Check if user is logged in via PHP session
    const isLoggedIn = <?= json_encode($isLoggedIn) ?>;
    if (!isLoggedIn) {
        showNoAuthMessage();
        return;
    }
    
    $('#loadingHistory').show();
    $('#loadingHistoryCards').show();
    $('#noGames').hide();
    
    $.ajax({
        url: '/api/history',
        method: 'GET',
        success: function(response) {
            $('#loadingHistory').hide();
            $('#loadingHistoryCards').hide();
            
            if (response.success && response.games.length > 0) {
                gameHistory = response.games;
                renderGameHistory();
            } else {
                showNoGamesMessage();
            }
        },
        error: function(xhr) {
            $('#loadingHistory').hide();
            $('#loadingHistoryCards').hide();
            if (xhr.status === 401) {
                showNoAuthMessage();
            } else {
                showError('Failed to load game history');
            }
        }
    });
}

function renderGameHistory() {
    // Render table view (desktop)
    const tbody = $('#historyTableBody');
    tbody.empty();
    
    gameHistory.forEach((game, index) => {
        const row = createGameHistoryRow(game, index);
        tbody.append(row);
    });
    
    // Render card view (mobile)
    const cardsContainer = $('#historyCardsContainer');
    cardsContainer.empty();
    
    gameHistory.forEach((game, index) => {
        const card = createGameHistoryCard(game, index);
        cardsContainer.append(card);
    });
}

function createGameHistoryRow(game, index) {
    const statusBadge = getStatusBadge(game.status);
    const progressBar = getProgressBar(game.words_found, game.total_words);
    const startedDate = formatDate(game.created_at);
    const gameTime = formatGameTime(game);
    const actions = getActionButtons(game);
    
    return $(`
        <tr>
            <td class="ps-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-secondary rounded-pill">#${index + 1}</span>
                    </div>
                    <div>
                        <strong class="d-block">${game.theme.charAt(0).toUpperCase() + game.theme.slice(1)} Puzzle</strong>
                        <small class="text-muted">ID: ${game.puzzle_id.substring(0, 8)}...</small>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-light text-dark">${game.theme}</span>
            </td>
            <td>
                <span class="badge ${getDifficultyColor(game.difficulty)}">${game.difficulty}</span>
            </td>
            <td>${statusBadge}</td>
            <td>
                <div class="d-flex flex-column">
                    <div class="d-flex align-items-center mb-1">
                        ${progressBar}
                        <small class="text-muted ms-2">${game.words_found}/${game.total_words}</small>
                    </div>
                    ${getWordsFoundDisplay(game)}
                </div>
            </td>
            <td>
                <small class="text-muted">${startedDate}</small>
            </td>
            <td>
                <small class="text-muted">${gameTime}</small>
            </td>
            <td>${actions}</td>
        </tr>
    `);
}

function createGameHistoryCard(game, index) {
    const statusBadge = getStatusBadge(game.status);
    const progressPercentage = game.total_words > 0 ? (game.words_found / game.total_words) * 100 : 0;
    const startedDate = formatDate(game.created_at);
    const gameTime = formatGameTime(game);
    const actions = getActionButtons(game);
    
    return $(`
        <div class="history-card">
            <div class="history-card-header">
                <div class="game-info">
                    <div>
                        <h6 class="game-title">${game.theme.charAt(0).toUpperCase() + game.theme.slice(1)} Puzzle</h6>
                        <div class="game-meta">
                            <span class="badge ${getDifficultyColor(game.difficulty)}">${game.difficulty}</span>
                            <span class="text-muted">${startedDate}</span>
                        </div>
                    </div>
                    <div class="game-status">
                        ${statusBadge}
                    </div>
                </div>
            </div>
            <div class="history-card-body">
                <div class="progress-section">
                    <div class="progress-info">
                        <span class="progress-text">${game.words_found}/${game.total_words} words found</span>
                        <span class="progress-text">${gameTime}</span>
                    </div>
                    <div class="progress-bar-mobile">
                        <div class="progress-fill" style="width: ${progressPercentage}%"></div>
                    </div>
                    ${getWordsFoundDisplay(game)}
                </div>
                
                <div class="game-actions">
                    ${actions}
                </div>
            </div>
        </div>
    `);
}

function getStatusBadge(status) {
    switch (status) {
        case 'completed':
            return '<span class="badge bg-success">Completed</span>';
        case 'active':
            return '<span class="badge bg-warning">In Progress</span>';
        case 'abandoned':
            return '<span class="badge bg-secondary">Abandoned</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

function getProgressBar(found, total) {
    const percentage = total > 0 ? (found / total) * 100 : 0;
    const progressClass = percentage === 100 ? 'bg-success' : 'bg-warning';
    
    return `
        <div class="progress" style="width: 80px; height: 8px;">
            <div class="progress-bar ${progressClass}" role="progressbar" 
                 style="width: ${percentage}%" aria-valuenow="${percentage}" 
                 aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    `;
}

function getWordsFoundDisplay(game) {
    if (!game.words_found_data || game.words_found_data.length === 0) {
        return `<small class="text-muted">No words found yet</small>`;
    }
    
    // Parse the JSON data if it's a string
    let wordsData = game.words_found_data;
    if (typeof wordsData === 'string') {
        try {
            wordsData = JSON.parse(wordsData);
        } catch (e) {
            wordsData = [];
        }
    }
    
    if (wordsData.length === 0) {
        return `<small class="text-muted">No words found yet</small>`;
    }
    
    // Show first few words found
    const displayWords = wordsData.slice(0, 3);
    const remaining = wordsData.length - 3;
    
    let display = displayWords.join(', ');
    if (remaining > 0) {
        display += ` +${remaining} more`;
    }
    
    return `<small class="text-success">${display}</small>`;
}

function getActionButtons(game) {
    if (game.status === 'completed') {
        return `
            <button class="btn btn-sm btn-outline-primary" onclick="reviewGame('${game.puzzle_id}')">
                <i class="bi bi-eye me-1"></i>Review
            </button>
        `;
    } else {
        return `
            <button class="btn btn-sm btn-success" onclick="continueGame('${game.puzzle_id}')">
                <i class="bi bi-play me-1"></i>Continue
            </button>
        `;
    }
}

function getDifficultyColor(difficulty) {
    switch (difficulty) {
        case 'easy': return 'bg-success';
        case 'medium': return 'bg-warning';
        case 'hard': return 'bg-danger';
        case 'expert': return 'bg-dark';
        default: return 'bg-secondary';
    }
}

function reviewGame(puzzleId) {
    // Redirect to play page with puzzle ID for review mode
    window.location.href = `/play?id=${puzzleId}&mode=review`;
}

function continueGame(puzzleId) {
    // Redirect to play page to continue the game
    window.location.href = `/play?id=${puzzleId}`;
}

function formatDate(dateString) {
    if (!dateString) return '--';
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function formatGameTime(game) {
    // Check if game has completion time (for completed games)
    if (game.completed_at && game.created_at) {
        const startTime = new Date(game.created_at);
        const endTime = new Date(game.completed_at);
        const duration = Math.floor((endTime - startTime) / 1000); // Duration in seconds
        
        if (duration < 60) {
            return `${duration}s`;
        } else if (duration < 3600) {
            const minutes = Math.floor(duration / 60);
            const seconds = duration % 60;
            return `${minutes}m ${seconds}s`;
        } else {
            const hours = Math.floor(duration / 3600);
            const minutes = Math.floor((duration % 3600) / 60);
            return `${hours}h ${minutes}m`;
        }
    }
    
    // Check if game has elapsed_time field (for active games)
    if (game.elapsed_time && game.elapsed_time > 0) {
        const seconds = parseInt(game.elapsed_time);
        if (seconds < 60) {
            return `${seconds}s`;
        } else if (seconds < 3600) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}m ${remainingSeconds}s`;
        } else {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return `${hours}h ${minutes}m`;
        }
    }
    
    // Check if game has time_spent field (legacy support)
    if (game.time_spent) {
        const seconds = parseInt(game.time_spent);
        if (seconds < 60) {
            return `${seconds}s`;
        } else if (seconds < 3600) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}m ${remainingSeconds}s`;
        } else {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return `${hours}h ${minutes}m`;
        }
    }
    
    // If no time data available
    if (game.status === 'completed') {
        return '--';
    } else if (game.status === 'active') {
        return 'In Progress';
    } else {
        return '--';
    }
}

function showNoAuthMessage() {
    $('#historyTableBody').html(`
        <tr>
            <td colspan="8" class="text-center py-5">
                <i class="bi bi-lock display-4 text-muted"></i>
                <h4 class="text-muted mt-3">Authentication Required</h4>
                <p class="text-muted">Please log in to view your game history.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Log In
                </button>
            </td>
        </tr>
    `);
}

function showNoGamesMessage() {
    $('#noGames').show();
}

function showError(message) {
    $('#historyTableBody').html(`
        <tr>
            <td colspan="8" class="text-center py-5">
                <i class="bi bi-exclamation-triangle display-4 text-danger"></i>
                <h4 class="text-danger mt-3">Error</h4>
                <p class="text-muted">${message}</p>
                <button class="btn btn-primary" onclick="loadGameHistory()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Try Again
                </button>
            </td>
        </tr>
    `);
}

function checkAuthStatus() {
    // Check if user is logged in via PHP session
    const isLoggedIn = <?= json_encode($isLoggedIn) ?>;
    
    if (isLoggedIn) {
        // User is logged in
        $('#guestNav').addClass('d-none');
        $('#userNav').removeClass('d-none');
        
        // Load game history using session-based auth
        loadGameHistory();
    } else {
        // User is not logged in
        $('#guestNav').removeClass('d-none');
        $('#userNav').addClass('d-none');
        showNoAuthMessage();
    }
}

// Handle logout
$('#logoutBtn').on('click', function(e) {
    e.preventDefault();
    // Redirect to logout page to clear PHP session
    window.location.href = '/logout';
});
</script>