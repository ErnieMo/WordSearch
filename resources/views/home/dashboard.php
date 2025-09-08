<?php
$page_title = 'Dashboard - Sudoku';
?>

<div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-4">
                    <i class="fas fa-tachometer-alt text-primary"></i>
                    Dashboard
                </h1>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-play-circle fa-3x text-success mb-3"></i>
                                <h5 class="card-title">Start New Game</h5>
                                <p class="card-text">Begin a new Sudoku puzzle with your preferred difficulty level.</p>
                                <a href="#" onclick="showDifficultyModal(); return false;" class="btn btn-success">
                                    <i class="fas fa-play me-2"></i>
                                    Play Now
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                                <h5 class="card-title">Leaderboards</h5>
                                <p class="card-text">View your rankings and compare scores with other players.</p>
                                <a href="/scores" class="btn btn-warning">
                                    <i class="fas fa-trophy me-2"></i>
                                    View Scores
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle me-2"></i>
                            Profile
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-user fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($user['username']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                            </div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary"><?= $stats['total_games'] ?? 0 ?></h4>
                                <small class="text-muted">Games Played</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success"><?= $stats['completed_games'] ?? 0 ?></h4>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <a href="/profile" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-cog me-2"></i>
                                Edit Profile
                            </a>
                            <a href="/logout" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($recent_games)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Recent Games
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Filter Controls -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status Filter:</label>
                                <div class="btn-group" role="group" aria-label="Status filter">
                                    <input type="radio" class="btn-check" name="statusFilter" id="statusAll" value="all" checked>
                                    <label class="btn btn-outline-primary btn-sm" for="statusAll">All</label>
                                    
                                    <input type="radio" class="btn-check" name="statusFilter" id="statusCompleted" value="completed">
                                    <label class="btn btn-outline-success btn-sm" for="statusCompleted">Complete</label>
                                    
                                    <input type="radio" class="btn-check" name="statusFilter" id="statusActive" value="active">
                                    <label class="btn btn-outline-warning btn-sm" for="statusActive">Active</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Difficulty Filter:</label>
                                <div class="btn-group" role="group" aria-label="Difficulty filter">
                                    <input type="radio" class="btn-check" name="difficultyFilter" id="difficultyAll" value="all" checked>
                                    <label class="btn btn-outline-primary btn-sm" for="difficultyAll">All</label>
                                    
                                    <input type="radio" class="btn-check" name="difficultyFilter" id="difficultyEasy" value="easy">
                                    <label class="btn btn-outline-success btn-sm" for="difficultyEasy">Easy</label>
                                    
                                    <input type="radio" class="btn-check" name="difficultyFilter" id="difficultyMedium" value="medium">
                                    <label class="btn btn-outline-warning btn-sm" for="difficultyMedium">Medium</label>
                                    
                                    <input type="radio" class="btn-check" name="difficultyFilter" id="difficultyHard" value="hard">
                                    <label class="btn btn-outline-danger btn-sm" for="difficultyHard">Hard</label>
                                    
                                    <input type="radio" class="btn-check" name="difficultyFilter" id="difficultyExpert" value="expert">
                                    <label class="btn btn-outline-dark btn-sm" for="difficultyExpert">Expert</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php foreach ($recent_games as $game): ?>
                            <div class="col-md-6 col-lg-4 mb-3" data-status="<?= $game['status'] ?>" data-difficulty="<?= $game['difficulty'] ?>">
                                <div class="card h-100 border dashboard-game-card <?= $game['status'] === 'completed' ? 'completed' : 'active' ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-<?= $game['difficulty'] === 'easy' ? 'success' : ($game['difficulty'] === 'medium' ? 'warning' : ($game['difficulty'] === 'hard' ? 'danger' : 'dark')) ?> me-2">
                                                    <?= ucfirst($game['difficulty']) ?>
                                                </span>
                                                <span class="badge bg-<?= $game['status'] === 'completed' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($game['status']) ?>
                                                </span>
                                                <span class="badge bg-info me-1">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php
                                                    if ($game['elapsed_time'] > 0) {
                                                        $hours = floor($game['elapsed_time'] / 3600);
                                                        $minutes = floor(($game['elapsed_time'] % 3600) / 60);
                                                        $seconds = $game['elapsed_time'] % 60;
                                                        
                                                        if ($hours > 0) {
                                                            echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                                        } else {
                                                            echo sprintf('%02d:%02d', $minutes, $seconds);
                                                        }
                                                    } else {
                                                        echo 'N/A';
                                                    } ?>
                                                </span>
                                                <span class="badge bg-warning me-1">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    <?= $game['errors_count'] ?? 0 ?>
                                                </span>
                                                <span class="badge bg-primary me-1">
                                                    <i class="fas fa-lightbulb me-1"></i>
                                                    <?= $game['hints_used'] ?? 0 ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <small class="text-muted">
                                                <strong>Game ID:</strong> <?= $game['id'] ?>
                                            </small>
                                            <small class="text-muted">
                                                <?= date('M j, Y g:i A', strtotime($game['created_at'])) ?>
                                            </small>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="/game?load=<?= $game['id'] ?>" class="btn btn-outline-primary btn-sm flex-fill">
                                                <i class="fas fa-play me-1"></i>
                                                <?= $game['status'] === 'completed' ? 'Review' : 'Continue' ?>
                                            </a>
                                            <!-- <button onclick="deleteGame('<?= $game['id'] ?>', '<?= $game['difficulty'] ?>')" class="btn btn-outline-danger btn-sm flex-fill" title="Delete this game">
                                                <i class="fas fa-trash me-1"></i>
                                                Delete
                                            </button> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Filter radio button event handlers
        document.addEventListener('DOMContentLoaded', function() {
            // Apply initial styling to game cards
            updateGameCardStyling();
            // Check if jQuery is available
            if (typeof $ === 'undefined') {
                console.error('jQuery not available for filter handlers');
                return;
            }
            
            // Status filter event handlers
            $('input[name="statusFilter"]').on('change', function() {
                const selectedValue = $(this).val();
                console.log('Status filter clicked:', selectedValue);
                applyFilters();
            });
            
            // Difficulty filter event handlers
            $('input[name="difficultyFilter"]').on('change', function() {
                const selectedValue = $(this).val();
                console.log('Difficulty filter clicked:', selectedValue);
                applyFilters();
            });
        });
        
        function applyFilters() {
            const selectedStatus = $('input[name="statusFilter"]:checked').val();
            const selectedDifficulty = $('input[name="difficultyFilter"]:checked').val();
            
            console.log('Applying filters - Status:', selectedStatus, 'Difficulty:', selectedDifficulty);
            
            // Hide all game cards first
            $('.col-md-6.col-lg-4').hide();
            
            // Build the selector based on selected filters
            let selector = '.col-md-6.col-lg-4';
            
            // Add status filter
            if (selectedStatus !== 'all') {
                selector += `[data-status="${selectedStatus}"]`;
            }
            
            // Add difficulty filter
            if (selectedDifficulty !== 'all') {
                selector += `[data-difficulty="${selectedDifficulty}"]`;
            }
            
            console.log('Using selector:', selector);
            
            // Show matching cards
            $(selector).show();
            
            // Count visible cards
            const visibleCount = $(selector).length;
            const totalCount = $('.col-md-6.col-lg-4').length;
            
            console.log(`Showing ${visibleCount} of ${totalCount} games`);
            
            // Show message if no games match filters
            if (visibleCount === 0) {
                showAlert('No games match the selected filters.', 'info');
            }
            
            // Update styling after filtering
            updateGameCardStyling();
        }
        
        function updateGameCardStyling() {
            const gameElements = document.querySelectorAll('.col-md-6.col-lg-4');
            
            gameElements.forEach(function(element) {
                const status = element.getAttribute('data-status');
                const cardElement = element.querySelector('.card');
                
                // Remove existing status classes
                cardElement.classList.remove('completed', 'active');
                
                // Add appropriate status class
                if (status === 'completed') {
                    cardElement.classList.add('completed');
                } else {
                    cardElement.classList.add('active');
                }
            });
        }
        
        function deleteGame(gameId, difficulty) {
            // Check if jQuery is available
            if (typeof $ === 'undefined') {
                console.error('jQuery not available for deleteGame function');
                showAlert('jQuery not available. Please refresh the page.', 'danger');
                return;
            }
            
            $.post('/game/delete', {
                game_id: gameId
            })
            .done(function(response) {
                if (response.success) {
                    // Remove the game element from the DOM
                    const gameElement = document.querySelector(`[onclick*="${gameId}"]`).closest('.col-md-6.col-lg-4');
                    if (gameElement) {
                        gameElement.remove();
                    }
                    
                    // Show success message
                    showAlert('Game deleted successfully!', 'success');
                    
                    // Update stats without page reload
                    updateDashboardStats();
                } else {
                    showAlert('Failed to delete game: ' + (response.error || 'Unknown error'), 'danger');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Delete request failed:', error);
                const response = xhr.responseJSON || {};
                showAlert('Failed to delete game: ' + (response.error || 'Network error'), 'danger');
            });
        }
        
        function updateDashboardStats() {
            // Update the stats display without reloading the page
            // This is a simple approach - in a real app you might want to fetch updated stats via AJAX
            const gameElements = document.querySelectorAll('.col-md-6.col-lg-4');
            const totalGames = gameElements.length;
            const completedGames = Array.from(gameElements).filter(el => 
                el.textContent.includes('Completed')
            ).length;
            
            // Update the stats display if the elements exist
            const totalGamesElement = document.querySelector('.text-primary');
            const completedGamesElement = document.querySelector('.text-success');
            
            if (totalGamesElement && totalGamesElement.textContent.includes('Games Played')) {
                totalGamesElement.textContent = totalGames;
            }
            if (completedGamesElement && completedGamesElement.textContent.includes('Completed')) {
                completedGamesElement.textContent = completedGames;
            }
        }
        
        function showAlert(message, type) {
            // Create alert element as a floating overlay
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = `
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                max-width: 500px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border-radius: 8px;
                margin: 0;
            `;
            alertDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : type === 'info' ? 'info-circle' : 'info-circle'} me-2"></i>
                    <span class="flex-grow-1">${message}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Add to body as a floating overlay
            document.body.appendChild(alertDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
            
            // Add click to dismiss functionality
            alertDiv.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-close') || e.target.classList.contains('alert')) {
                    this.remove();
                }
            });
        }
    </script> 