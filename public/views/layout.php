<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Word Search Game' ?></title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512x512.png">
    <link rel="manifest" href="/site.webmanifest">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/app.css" rel="stylesheet">
    
    <!-- Environment Variables -->
    <script>
        window.APP_ENV = '<?php echo getenv('APP_ENV') ?: 'production'; ?>';
    </script>
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
                        <a class="nav-link" href="/history"><i class="bi bi-clock-history me-1"></i>History</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav" id="authNav">
                    <?php
                    // Session is already started in index.php
                    // Debug session state in layout
                    error_log("=== LAYOUT.PHP SESSION DEBUG ===");
                    error_log("Session ID: " . (session_id() ?: 'NO SESSION ID'));
                    error_log("Session status: " . session_status());
                    error_log("Session data: " . print_r($_SESSION, true));
                    error_log("isLoggedIn: " . (isset($_SESSION['user_id']) && isset($_SESSION['username']) ? 'true' : 'false'));
                    error_log("=== END LAYOUT SESSION DEBUG ===");
                    
                    $isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);
                    
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
                    ?>
                    
                    <!-- Admin Navigation -->
                    <?php if ($isLoggedIn && isset($_SESSION['isadmin']) && $_SESSION['isadmin']): ?>
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
                    
                    <!-- Guest Navigation -->
                    <li class="nav-item <?= $isLoggedIn ? 'd-none' : '' ?>" id="guestNav">
                        <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </button>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#registerModal">
                            <i class="bi bi-person-plus me-1"></i>Register
                        </button>
                    </li>
                    
                    <!-- Authenticated Navigation -->
                    <li class="nav-item dropdown <?= $isLoggedIn ? '' : 'd-none' ?>" id="userNav">
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

    <!-- Main Content -->
    <main class="container my-4">
        <?php if (isset($pageContent)): ?>
            <?= $pageContent ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Word Search Game. Built with PHP, Bootstrap, and jQuery.</p>
        </div>
    </footer>

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
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="registerEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="registerPassword" required>
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

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Custom JS -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
