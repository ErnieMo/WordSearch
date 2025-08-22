<?php
$pageTitle = 'Play Word Search - Word Search Game';

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
                    <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Word Search Grid</h5>
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
    
    <!-- Game Completion Modal -->
    <div class="modal fade" id="completionModal" tabindex="-1">
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
                            <p class="h4 text-success" id="completionTime">00:00</p>
                        </div>
                        <div class="col-6">
                            <h6>Hints Used</h6>
                            <p class="h4 text-warning" id="hintsUsed">0</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="playAgainBtn">Play Again</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden puzzle data will be set after layout loads -->';
}

include 'layout.php';
?>

<!-- Set puzzle ID and load game JavaScript after jQuery is available -->
<script>
    window.puzzleId = "<?= htmlspecialchars($puzzleId) ?>";
</script>
<script src="/assets/js/game.js"></script>
