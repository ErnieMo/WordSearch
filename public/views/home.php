<?php
$title = 'Home - Word Search';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h2 class="display-4 text-primary mb-3">Welcome to Word Search!</h2>
                <p class="lead">Challenge your mind with our interactive word search puzzles. Choose your difficulty and start playing!</p>
            </div>

            <!-- Difficulty Selection -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-gear"></i> Select Difficulty
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <h4 class="card-title text-primary">Easy</h4>
                                    <p class="card-text">10×10 grid<br>Horizontal & Vertical only</p>
                                    <button class="btn btn-primary btn-difficulty" data-difficulty="easy" data-size="10">
                                        <i class="bi bi-play-circle"></i> Play Easy
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <h4 class="card-title text-warning">Medium</h4>
                                    <p class="card-text">12×12 grid<br>Includes diagonals</p>
                                    <button class="btn btn-warning btn-difficulty" data-difficulty="medium" data-size="12">
                                        <i class="bi bi-play-circle"></i> Play Medium
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-danger">
                                <div class="card-body text-center">
                                    <h4 class="card-title text-danger">Hard</h4>
                                    <p class="card-text">15×15 grid<br>All directions + reverse</p>
                                    <button class="btn btn-danger btn-difficulty" data-difficulty="hard" data-size="15">
                                        <i class="bi bi-play-circle"></i> Play Hard
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theme Selection -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-palette"></i> Choose Theme
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-tree text-success" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Animals</h5>
                                    <p class="card-text">Wildlife and pets</p>
                                    <button class="btn btn-outline-success btn-theme" data-theme="animals">
                                        Select Animals
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-globe text-info" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Geography</h5>
                                    <p class="card-text">Countries and cities</p>
                                    <button class="btn btn-outline-info btn-theme" data-theme="geography">
                                        Select Geography
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-cpu text-warning" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Technology</h5>
                                    <p class="card-text">Computers and gadgets</p>
                                    <button class="btn btn-outline-warning btn-theme" data-theme="technology">
                                        Select Technology
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Start -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-lightning"></i> Quick Start
                    </h3>
                </div>
                <div class="card-body text-center">
                    <p class="mb-3">Ready to jump in? Use our default word list for instant fun!</p>
                    <button class="btn btn-info btn-lg" id="quickStart">
                        <i class="bi bi-play-fill"></i> Start Playing Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Game Options Modal -->
<div class="modal fade" id="gameOptionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Game Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="gameOptionsForm">
                    <div class="mb-3">
                        <label class="form-label">Difficulty: <span id="selectedDifficulty"></span></label>
                        <input type="hidden" id="difficulty" name="difficulty">
                        <input type="hidden" id="gridSize" name="gridSize">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Theme: <span id="selectedTheme"></span></label>
                        <input type="hidden" id="theme" name="theme">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allowDiagonals" name="allowDiagonals">
                            <label class="form-check-label" for="allowDiagonals">
                                Allow diagonal words
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allowReverse" name="allowReverse">
                            <label class="form-check-label" for="allowReverse">
                                Allow reverse words
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="startGame">Start Game</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
