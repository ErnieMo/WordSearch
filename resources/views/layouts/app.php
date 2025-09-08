<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'WordSearch' ?></title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Environment-specific CSS -->
    <?php if (($_ENV['APP_ENV'] ?? 'development') === 'development'): ?>
        <link href="/assets/css/app.dev.css" rel="stylesheet">
    <?php else: ?>
        <link href="/assets/css/app.prod.css" rel="stylesheet">
    <?php endif; ?>
    
    <!-- Base Custom CSS -->
    <link href="/assets/css/app.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1 0 auto;
            min-height: 0;
        }
        
        footer {
            flex-shrink: 0;
        }
        
        /* Remove ALL borders and outlines from ALL dropdowns */
        .navbar .dropdown-toggle,
        .navbar .dropdown-toggle:focus,
        .navbar .dropdown-toggle:active,
        .navbar .dropdown-toggle:hover,
        .navbar .dropdown-toggle:focus-visible {
            outline: none !important;
            box-shadow: none !important;
            border: none !important;
            border-color: transparent !important;
            border-width: 0 !important;
            border-style: none !important;
        }
        
        /* Remove any Bootstrap default borders and focus styles */
        .navbar .dropdown-toggle {
            border: 0 !important;
            outline: 0 !important;
        }
        
        /* Remove focus ring completely from all dropdowns */
        .navbar .dropdown-toggle:focus {
            outline: 0 !important;
            outline-offset: 0 !important;
        }
        
        /* Ensure dropdown arrows are white */
        .navbar .dropdown-toggle::after {
            border-top-color: white !important;
        }
        
        /* Remove any remaining Bootstrap focus styles */
        .navbar .dropdown-toggle:focus,
        .navbar .dropdown-toggle:focus-within {
            outline: none !important;
            box-shadow: none !important;
            border: none !important;
        }
        
        /* Remove borders from dropdown menus themselves */
        .dropdown-menu {
            border: none !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Remove borders from dropdown items */
        .dropdown-item {
            border: none !important;
            outline: none !important;
        }
        
        .dropdown-item:focus,
        .dropdown-item:hover {
            outline: none !important;
            box-shadow: none !important;
            border: none !important;
        }
        
        /* Fix z-index for Sudoku dropdown to appear above content but below modals */
        .navbar-brand.dropdown .dropdown-menu {
            z-index: 1050 !important;
            position: absolute !important;
        }
        
        /* Ensure all dropdowns have proper z-index */
        .navbar .dropdown-menu {
            z-index: 1050 !important;
        }
        
        /* Force the navbar to create a stacking context */
        .navbar {
            position: relative !important;
            z-index: 1050 !important;
        }
        
        /* Ensure the Sudoku dropdown container has proper positioning */
        .navbar-brand.dropdown {
            position: relative !important;
            z-index: 1050 !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <div class="navbar-brand dropdown">
                <a class="nav-link dropdown-toggle text-white text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-search me-2"></i>
                    WordSearch
                    <?php 
                    // if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
                    //     echo '<span class="badge bg-success ms-2">[DEV]</span>';
                    // } 
                    ?>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="hashed_site_redirect.php?url=<?= ($_ENV['APP_ENV'] ?? 'development') === 'development' ? 'https://sudoku.dev.nofinway.com' : 'https://sudoku.nofinway.com' ?>">
                            <i class="fas fa-puzzle-piece me-2"></i>Sudoku
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= ($_ENV['APP_ENV'] ?? 'development') === 'development' ? 'https://wordsearch.dev.nofinway.com' : 'https://wordsearch.nofinway.com' ?>">
                            <i class="fas fa-search me-2"></i>Word Search
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="hashed_site_redirect.php?url=<?= ($_ENV['APP_ENV'] ?? 'development') === 'development' ? 'https://tileslider.dev.nofinway.com' : 'https://tileslider.nofinway.com' ?>">
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
                        <a class="nav-link" href="/"><i class="fas fa-home me-1"></i>Home</a>
                    </li>
                    <?php if (isset($user) && $user): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="showDifficultyModal(); return false;"><i class="fas fa-gamepad me-1"></i>Play</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/create"><i class="fas fa-plus-circle me-1"></i>Create</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/scores"><i class="fas fa-trophy me-1"></i>Scores</a>
                        </li>
                        <?php if (isset($user) && isset($user['isadmin']) && $user['isadmin'] === true): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-shield-alt me-1"></i>Admin
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/admin/database"><i class="fas fa-database me-1"></i>Database</a></li>
                                    <li><a class="dropdown-item" href="/admin/users"><i class="fas fa-users me-1"></i>Users</a></li>
                                    <li><a class="dropdown-item" href="/admin/testing"><i class="fas fa-vial me-1"></i>Testing Suite</a></li>
                                    <?php if (($_ENV['APP_ENV'] ?? 'development') === 'development'): ?>
                                        <li><a class="dropdown-item" href="/admin/deploy"><i class="fas fa-rocket me-1"></i>Deploy</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isset($user) && $user): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($user['username']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/profile"><i class="fas fa-user-cog me-1"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="/logout" class="d-inline">
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register"><i class="fas fa-user-plus me-1"></i>Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($flash_messages) && $flash_messages): ?>
        <div class="container mt-3">
            <?php foreach ($flash_messages as $type => $message): ?>
                <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= $type === 'error' ? 'exclamation-triangle' : ($type === 'success' ? 'check-circle' : 'info-circle') ?> me-2"></i>
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-grow-1">
        <?= $content ?? '' ?>
    </main>

    <!-- jQuery (must be before Bootstrap and custom JS) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS (if any) -->
    <script src="/assets/js/app.js"></script>

    <!-- New Game Modal -->
    <div class="modal fade" id="newGameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>
                        New Game
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Select difficulty level:</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success difficulty-btn" data-difficulty="easy">
                            <i class="fas fa-star me-2"></i>
                            Easy
                        </button>
                        <button class="btn btn-warning difficulty-btn" data-difficulty="medium">
                            <i class="fas fa-star me-2"></i>
                            Medium
                        </button>
                        <button class="btn btn-danger difficulty-btn" data-difficulty="hard">
                            <i class="fas fa-star me-2"></i>
                            Hard
                        </button>
                        <button class="btn btn-dark difficulty-btn" data-difficulty="expert">
                            <i class="fas fa-star me-2"></i>
                            Expert
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- How to Play Modal -->
    <div class="modal fade" id="howToPlayModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-graduation-cap me-2"></i>
                        How to Play Sudoku
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="howToPlayContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading how to play guide...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global function to show difficulty modal and start new game
        function showDifficultyModal() {
            // Check if Bootstrap is available
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap not available');
                return;
            }
            
            const modalElement = document.getElementById('newGameModal');
            if (!modalElement) {
                console.error('Modal element not found');
                return;
            }
            
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }

        // Global function to show how to play modal
        function showHowToPlayModal() {
            // Check if Bootstrap is available
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap not available');
                return;
            }
            
            const modalElement = document.getElementById('howToPlayModal');
            if (!modalElement) {
                console.error('Modal element not found');
                return;
            }
            
            // Show the modal first
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
            // Load the how-to-play content via AJAX
            const contentElement = document.getElementById('howToPlayContent');
            if (contentElement) {
                fetch('/how-to-play')
                    .then(response => response.text())
                    .then(html => {
                        contentElement.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error loading how to play content:', error);
                        contentElement.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Failed to load how to play guide. Please try again.
                            </div>
                        `;
                    });
            }
        }

        // Handle difficulty selection
        document.addEventListener('DOMContentLoaded', function() {
            const difficultyButtons = document.querySelectorAll('.difficulty-btn');
            difficultyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const difficulty = this.getAttribute('data-difficulty');
                    console.log('Difficulty selected:', difficulty);
                    
                    // Close the modal first
                    const modalElement = document.getElementById('newGameModal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                    }
                    
                    // Navigate to new game with selected difficulty
                    window.location.href = `/game/new?difficulty=${difficulty}`;
                });
            });
        });
    </script>

</body>
</html> 