<?php
if (!isset($_SESSION)) {
    session_start();
}

$pageTitle = 'Play Word Search - Word Search Game';

// Session is already started in index.php
// Debug session state
error_log("\n=== PLAY.PHP SESSION DEBUG ===", 3, '/var/www/html/Logs/wordsearch_debug.log');
error_log("\nSession ID: " . (session_id() ?: 'NO SESSION ID'));
error_log("\nSession status: " . session_status());
error_log("\nSession data: " . print_r($_SESSION, true));
error_log("\nCookie data: " . print_r($_COOKIE, true));
error_log("\nPHPSESSID cookie: " . ($_COOKIE['PHPSESSID'] ?? 'NOT SET'));
error_log("\nAll cookies: " . print_r($_COOKIE, true));

// Check if user is logged in via session

$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);
$currentUsername = $_SESSION['username'] ?? '';

error_log("\nisLoggedIn: " . ($isLoggedIn ? 'true' : 'false'));
error_log("\ncurrentUsername: " . $currentUsername, 3, '/var/www/html/Logs/wordsearch_debug.log');
error_log("\nSession user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("\nSession username: " . ($_SESSION['username'] ?? 'NOT SET'));
error_log("\nisset(\$_SESSION['user_id']): " . (isset($_SESSION['user_id']) ? 'true' : 'false'));
error_log("\nisset(\$_SESSION['username']): " . (isset($_SESSION['username']) ? 'true' : 'false'));
error_log("\n=== END SESSION DEBUG ===", 3, '/var/www/html/Logs/wordsearch_debug.log');

// Get puzzle ID from URL parameter
$puzzleId = $_GET['id'] ?? '';

if (empty($puzzleId)) {
    $pageContent = '
    <div class="alert alert-danger">
        <h4>No Puzzle Selected</h4>
        <p>Please go back to the home page and start a new game.</p>
        <a href="/" class="btn btn-primary">Go Home</a>
    </div>';
} else {
    $pageContent = '
    <div class="row">
        <div class="col-lg-8">
            <!-- Game Grid -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Word Search Grid (<span id="gridSize">Loading...</span>)</h5>
                </div>
                <div class="card-body text-center">
                    <div id="gameGrid" class="mb-3">
                        <!-- Grid will be loaded here -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Loading puzzle...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="game-controls">
                <!-- Timer -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Timer</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="timer" id="gameTimer">00:00</div>
                    </div>
                </div>
                
                <!-- Verification Status -->
                <!--
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="bi bi-check-circle me-2"></i>Verification</h6>
                    </div>
                    <div class="card-body text-center">
                        <div id="verificationStatus" class="mb-2">
                            <div class="spinner-border spinner-border-sm text-warning" role="status">
                                <span class="visually-hidden">Verifying...</span>
                            </div>
                            <small class="text-muted">Verifying words...</small>
                        </div>
                    </div>
                </div>
                -->
                
                <!-- Progress -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Progress</h6>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" id="progressBar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted">
                            <span id="wordsFound">0</span> of <span id="totalWords">0</span> words found
                        </small>
                    </div>
                </div>
                
                <!-- Word List -->
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Find These Words</h6>
                    </div>
                    <div class="card-body">
                        <div class="word-list" id="wordList">
                            <!-- Words will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <!-- Game Controls -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="bi bi-controller me-2"></i>Controls</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" id="hintBtn">
                                <i class="bi bi-lightbulb me-2"></i>Hint
                            </button>
                            <button class="btn btn-outline-warning" id="resetBtn">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reset Selection
                            </button>
                            <button class="btn btn-outline-info" id="showSolutionBtn">
                                <i class="bi bi-eye me-2"></i>Show Solution
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Game Actions -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" id="newGameBtn">
                                <i class="bi bi-plus-circle me-2"></i>New Game
                            </button>
                            <a href="/" class="btn btn-outline-secondary">
                                <i class="bi bi-house me-2"></i>Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Game Completion Modal for Logged-in Users -->
    <div class="modal fade" id="completionModalLoggedIn" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-trophy me-2"></i>Congratulations!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You completed the puzzle!</p>
                    <div class="row text-center">
                        <div class="col-6">
                            <h6>Time</h6>
                            <p class="h4 text-success" id="completionTimeLoggedIn">00:00</p>
                        </div>
                        <div class="col-6">
                            <h6>Hints Used</h6>
                            <p class="h4 text-warning" id="hintsUsedLoggedIn">0</p>
                        </div>
                    </div>
                    
                    <!-- Score saved confirmation -->
                    <div class="mt-3">
                        <div class="text-center">
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Score saved to scoreboard!</strong>
                                <br>
                                <small class="text-muted">Your completion time has been recorded.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="playAgainBtnLoggedIn">Play Again</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Game Completion Modal for Guest Users -->
    <div class="modal fade" id="completionModalGuest" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-trophy me-2"></i>Congratulations!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You completed the puzzle!</p>
                    <div class="row text-center">
                        <div class="col-6">
                            <h6>Time</h6>
                            <p class="h4 text-success" id="completionTimeGuest">00:00</p>
                        </div>
                        <div class="col-6">
                            <h6>Hints Used</h6>
                            <p class="h4 text-warning" id="hintsUsedGuest">0</p>
                        </div>
                    </div>
                    
                    <!-- Login to save score message -->
                    <div class="mt-3">
                        <div class="text-center">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Login to save your score to the scoreboard!</strong>
                                <br>
                                <small class="text-muted">Your score is temporarily saved and will be lost if decide not to login / register.</small>
                            </div>
                            <div class="d-grid gap-2 d-md-block">
                                <button class="btn btn-primary me-md-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                                </button>
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#registerModal">
                                    <i class="bi bi-person-plus me-2"></i>Register
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="playAgainBtnGuest">Play Again</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden puzzle data will be set after layout loads -->';
    
    // Add development-only button if in development mode
    if (getenv('APP_ENV') === 'development') {
        $pageContent = str_replace(
            '<!-- Game Controls -->',
            '<!-- Game Controls -->
                <!-- Development Controls -->
                <div class="card mb-3" id="devControls" style="display: none;">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="bi bi-bug me-2"></i>Development Tools</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-success" id="almostCompleteBtn">
                                <i class="bi bi-fast-forward me-2"></i>Almost Complete
                            </button>
                            <button class="btn btn-outline-primary" id="completeBtn">
                                <i class="bi bi-check-circle me-2"></i>Complete
                            </button>
                        </div>
                    </div>
                </div>',
            $pageContent
        );
    }
}

include 'layout.php';

// Add session debug information to the completion modal if in development mode
if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
    echo '<script>
        // Add session debug info to the completion modal
        $(document).ready(function() {
            const sessionDebugHtml = `
                <div class="mt-3">
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-bug me-2"></i>Session Debug Info</h6>
                        <pre class="mb-0">' . print_r($_SESSION, true) . '</pre>
                    </div>
                </div>
            `;
            
            // Insert the debug info after the scoreSavingSection
            $("#scoreSavingSection").after(sessionDebugHtml);
        });
    </script>';
}
?>

<!-- Set puzzle ID and authentication state before loading game JavaScript -->
<script>
    // Set authentication state from server-side session IMMEDIATELY
    window.serverAuthState = {
        isLoggedIn: <?= $isLoggedIn ? 'true' : 'false' ?>,
        username: "<?= htmlspecialchars($currentUsername) ?>"
    };
    
    // Set global login state immediately
    window.isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    
    window.puzzleId = "<?= htmlspecialchars($puzzleId) ?>";
    window.gameId = window.puzzleId; // Set gameId for score saving
    
    console.log('Server authentication state:', window.serverAuthState);
    console.log('Global login state set to:', window.isLoggedIn);
    console.log('Type of window.isLoggedIn:', typeof window.isLoggedIn);
    console.log('Type of window.serverAuthState.isLoggedIn:', typeof window.serverAuthState.isLoggedIn);
    
    // Debug the modal display logic
    console.log('=== MODAL DISPLAY DEBUG ===');
    console.log('PHP $isLoggedIn value:', <?= $isLoggedIn ? 'true' : 'false' ?>);
    console.log('PHP $currentUsername value:', "<?= htmlspecialchars($currentUsername) ?>");
    console.log('Modal should show:', <?= $isLoggedIn ? '"Score saved message"' : '"Login message"' ?>);
    console.log('=== END MODAL DEBUG ===');
</script>
<script src="/assets/js/game.js"></script>
