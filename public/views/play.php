<?php
$title = 'Play - Word Search';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Game Grid -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-grid-3x3"></i> Word Search Grid
                    </h3>
                    <div>
                        <span class="badge bg-light text-dark me-2">
                            <i class="bi bi-clock"></i> <span id="timer">00:00</span>
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-check-circle"></i> <span id="foundCount">0</span>/<span id="totalCount">0</span>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div id="gameGrid" class="d-inline-block"></div>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-outline-primary me-2" id="hintBtn">
                            <i class="bi bi-lightbulb"></i> Hint
                        </button>
                        <button class="btn btn-outline-secondary me-2" id="newGameBtn">
                            <i class="bi bi-arrow-clockwise"></i> New Game
                        </button>
                        <button class="btn btn-outline-success" id="printBtn">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Word List and Controls -->
        <div class="col-lg-4">
            <!-- Word List -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white">
                    <h4 class="h6 mb-0">
                        <i class="bi bi-list-ul"></i> Find These Words
                    </h4>
                </div>
                <div class="card-body">
                    <div id="wordList" class="row g-2"></div>
                </div>
            </div>

            <!-- Game Info -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h4 class="h6 mb-0">
                        <i class="bi bi-info-circle"></i> Game Information
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Difficulty:</strong> <span id="gameDifficulty">-</span>
                    </div>
                    <div class="mb-2">
                        <strong>Grid Size:</strong> <span id="gameSize">-</span>×<span id="gameSize2">-</span>
                    </div>
                    <div class="mb-2">
                        <strong>Theme:</strong> <span id="gameTheme">-</span>
                    </div>
                    <div class="mb-2">
                        <strong>Share Link:</strong>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="shareLink" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="copyLink">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="h6 mb-0">
                        <i class="bi bi-question-circle"></i> How to Play
                    </h4>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Mouse/Touch:</strong> Click and drag to select letters
                    </p>
                    <p class="small mb-2">
                        <strong>Goal:</strong> Find all words in the list
                    </p>
                    <p class="small mb-0">
                        <strong>Words can be:</strong> Horizontal, vertical, or diagonal
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Win Modal -->
<div class="modal fade" id="winModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-trophy"></i> Congratulations!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <h4 class="text-success mb-3">You've completed the puzzle!</h4>
                <p class="mb-3">
                    <strong>Time:</strong> <span id="finalTime">-</span><br>
                    <strong>Words Found:</strong> <span id="finalWords">-</span>
                </p>
                <div class="alert alert-info">
                    <i class="bi bi-share"></i> Share this puzzle with friends using the link above!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="playAgain">Play Again</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">Generating your puzzle...</p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
