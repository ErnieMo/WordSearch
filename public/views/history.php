<?php
// Start session to access user data
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game History - Word Search Game</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <div class="navbar-brand dropdown">
                <a class="nav-link dropdown-toggle text-white text-decoration-none fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-search-heart me-2"></i>
                    Word Search
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="/hashed_site_redirect.php?url=<?= ($_ENV['APP_ENV'] ?? 'development') === 'development' ? 'https://sudoku.dev.nofinway.com' : 'https://sudoku.nofinway.com' ?>">
                            <i class="fas fa-puzzle-piece me-2"></i>Sudoku
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= ($_ENV['APP_ENV'] ?? 'development') === 'development' ? 'https://wordsearch.dev.nofinway.com' : 'https://wordsearch.nofinway.com' ?>">
                            <i class="fas fa-search me-2"></i>Word Search
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="/hashed_site_redirect.php?url=<?= ($_ENV['APP_ENV'] ?? 'development') === 'development' ? 'https://tileslider.dev.nofinway.com' : 'https://tileslider.nofinway.com' ?>">
                            <i class="fas fa-th me-2"></i>Tile Slider
                        </a>
                    </li>
                </ul>
            </div>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/"><i class="bi bi-house me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="playLink"><i class="bi bi-play-circle me-1"></i>Play</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/create"><i class="bi bi-plus-circle me-1"></i>Create</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/scores"><i class="bi bi-trophy me-1"></i>Scores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/history"><i class="bi bi-clock-history me-1"></i>History</a>
                    </li>
                </ul>
                
                <!-- Admin Navigation -->
                <?php 
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
                
                if ($isLoggedIn && isset($_SESSION['isadmin']) && $_SESSION['isadmin']): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-gear me-1"></i>Admin
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/admin/database">
                            <i class="bi bi-database me-2"></i>Database
                        </a></li>
                        <li><a class="dropdown-item" href="/admin/testing">
                            <i class="bi bi-bug me-2"></i>Testing Suite
                        </a></li>
                        <li><a class="dropdown-item" href="/admin/users">
                            <i class="bi bi-people me-2"></i>Users
                        </a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <ul class="navbar-nav" id="authNav">
                    <!-- Guest Navigation -->
                    <li class="nav-item d-none" id="guestNav">
                        <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </button>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#registerModal">
                            <i class="bi bi-person-plus me-1"></i>Register
                        </button>
                    </li>
                    
                    <!-- Authenticated Navigation -->
                    <li class="nav-item dropdown" id="userNav">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <span id="userDisplayName"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0">
                    <i class="bi bi-clock-history me-2 text-primary"></i>
                    Game History
                </h1>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="refreshHistory">
                        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Game History Table -->
            <div class="card">
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <tr id="loadingHistory">
                                    <td colspan="7" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading your game history...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
    </div>
</div>

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
    $('#refreshHistory').on('click', function() {
        loadGameHistory();
    });
}

function loadGameHistory() {
    const token = localStorage.getItem('authToken');
    if (!token) {
        showNoAuthMessage();
        return;
    }
    
    $('#loadingHistory').show();
    $('#noGames').hide();
    
    $.ajax({
        url: '/api/history',
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`
        },
        success: function(response) {
            $('#loadingHistory').hide();
            
            if (response.success && response.games.length > 0) {
                gameHistory = response.games;
                renderGameHistory();
            } else {
                showNoGamesMessage();
            }
        },
        error: function(xhr) {
            $('#loadingHistory').hide();
            if (xhr.status === 401) {
                showNoAuthMessage();
                // Clear invalid token
                localStorage.removeItem('authToken');
                checkAuthStatus();
            } else {
                showError('Failed to load game history');
            }
        }
    });
}

function renderGameHistory() {
    const tbody = $('#historyTableBody');
    tbody.empty();
    
    gameHistory.forEach((game, index) => {
        const row = createGameHistoryRow(game, index);
        tbody.append(row);
    });
}

function createGameHistoryRow(game, index) {
    const statusBadge = getStatusBadge(game.status);
    const progressBar = getProgressBar(game.words_found, game.total_words);
    const startedDate = formatDate(game.created_at);
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
            <td>${actions}</td>
        </tr>
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

function showNoAuthMessage() {
    $('#historyTableBody').html(`
        <tr>
            <td colspan="7" class="text-center py-5">
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
            <td colspan="7" class="text-center py-5">
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
</script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-box-arrow-in-right me-2"></i>Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label">Username or Email</label>
                            <input type="text" class="form-control" id="loginUsername" required>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="loginPassword" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Register</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="registerForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="registerFirstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="registerFirstName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="registerLastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="registerLastName" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="registerUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="registerUsername" required>
                            <div class="form-text">Username must be unique</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="registerEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="registerPassword" required>
                            <div class="form-text">Minimum 6 characters</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-person-plus me-2"></i>Register
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Initialize authentication state -->
    <script>
        // Check authentication status on page load
        $(document).ready(function() {
            checkAuthStatus();
        });
        
        function checkAuthStatus() {
            const token = localStorage.getItem('authToken');
            if (token) {
                // User is logged in
                $('#guestNav').addClass('d-none');
                $('#userNav').removeClass('d-none');
                
                // Get user info from token
                getUserInfo(token);
                
                // Load game history
                loadGameHistory();
            } else {
                // User is not logged in
                $('#guestNav').removeClass('d-none');
                $('#userNav').addClass('d-none');
                showNoAuthMessage();
            }
        }
        
        function getUserInfo(token) {
            $.ajax({
                url: '/api/auth/profile',
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                success: function(response) {
                    if (response.success && response.profile) {
                        const username = response.profile.username || response.profile.first_name || 'User';
                        $('#userDisplayName').text(username);
                    } else {
                        $('#userDisplayName').text('User');
                    }
                },
                error: function(xhr) {
                    console.error('Failed to get user info:', xhr.responseText);
                    $('#userDisplayName').text('User');
                }
            });
        }
        
        // Handle logout
        $('#logoutBtn').on('click', function(e) {
            e.preventDefault();
            localStorage.removeItem('authToken');
            window.location.href = '/';
        });
        
        // Handle login form
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const username = $('#loginUsername').val();
            const password = $('#loginPassword').val();
            
            if (!username || !password) {
                alert('Please fill in all fields');
                return;
            }
            
            $.ajax({
                url: '/api/auth/login',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ username, password }),
                success: function(response) {
                    if (response.success) {
                        localStorage.setItem('authToken', response.token);
                        $('#loginModal').modal('hide');
                        checkAuthStatus(); // Refresh auth state
                    } else {
                        alert(response.error || 'Login failed');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    alert(response?.error || 'Login failed');
                }
            });
        });
        
        // Handle register form
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                username: $('#registerUsername').val(),
                email: $('#registerEmail').val(),
                password: $('#registerPassword').val(),
                first_name: $('#registerFirstName').val(),
                last_name: $('#registerLastName').val()
            };
            
            if (!formData.username || !formData.email || !formData.password || !formData.first_name || !formData.last_name) {
                alert('Please fill in all fields');
                return;
            }
            
            $.ajax({
                url: '/api/auth/register',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.success) {
                        alert('Registration successful! Please log in.');
                        $('#registerModal').modal('hide');
                        $('#loginModal').modal('show');
                    } else {
                        alert(response.error || 'Registration failed');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    alert(response?.error || 'Registration failed');
                }
            });
        });
    </script>
    
    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Word Search Game. Built with PHP, Bootstrap, and jQuery.</p>
        </div>
    </footer>
</body>
</html>
