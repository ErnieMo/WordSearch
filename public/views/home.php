<?php
$title = 'Home - Word Search';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h2 class="display-4 text-primary mb-3">Welcome to Word Search!</h2>
                <p class="lead">Challenge your mind with our interactive word search puzzles. Choose your theme and difficulty to begin!</p>
            </div>



            <!-- Theme Selection -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-palette"></i> Choose Theme
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Choose Theme Section -->
                    <h4 class="mb-3 text-center">
                        <i class="bi bi-palette"></i> Choose Theme
                    </h4>
                    <div class="row mb-4">
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 theme-card" data-theme="animals">
                                <div class="card-body text-center position-relative">
                                    <div class="theme-checkmark position-absolute top-0 end-0 m-2" style="display: none;">
                                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                    </div>
                                    <i class="bi bi-tree text-success" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Animals</h5>
                                    <p class="card-text">Wildlife and pets</p>
                                    <button class="btn btn-outline-success btn-theme" data-theme="animals">
                                        Select Animals
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 theme-card" data-theme="geography">
                                <div class="card-body text-center position-relative">
                                    <div class="theme-checkmark position-absolute top-0 end-0 m-2" style="display: none;">
                                        <i class="bi bi-check-circle-fill text-info fs-4"></i>
                                    </div>
                                    <i class="bi bi-globe text-info" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Geography</h5>
                                    <p class="card-text">Countries and cities</p>
                                    <button class="btn btn-outline-info btn-theme" data-theme="geography">
                                        Select Geography
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 theme-card" data-theme="technology">
                                <div class="card-body text-center position-relative">
                                    <div class="theme-checkmark position-absolute top-0 end-0 m-2" style="display: none;">
                                        <i class="bi bi-check-circle-fill text-warning fs-4"></i>
                                    </div>
                                    <i class="bi bi-cpu text-warning" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Technology</h5>
                                    <p class="card-text">Computers and gadgets</p>
                                    <button class="btn btn-outline-warning btn-theme" data-theme="technology">
                                        Select Technology
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 theme-card" data-theme="food">
                                <div class="card-body text-center position-relative">
                                    <div class="theme-checkmark position-absolute top-0 end-0 m-2" style="display: none;">
                                        <i class="bi bi-check-circle-fill text-danger fs-4"></i>
                                    </div>
                                    <i class="bi bi-cup-hot text-danger" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Food</h5>
                                    <p class="card-text">Delicious dishes and ingredients</p>
                                    <button class="btn btn-outline-danger btn-theme" data-theme="food">
                                        Select Food
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 theme-card" data-theme="automotive">
                                <div class="card-body text-center position-relative">
                                    <div class="theme-checkmark position-absolute top-0 end-0 m-2" style="display: none;">
                                        <i class="bi bi-check-circle-fill text-dark fs-4"></i>
                                    </div>
                                    <i class="bi bi-car-front text-dark" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Automotive</h5>
                                    <p class="card-text">Cars, trucks, and vehicles</p>
                                    <button class="btn btn-outline-dark btn-theme" data-theme="automotive">
                                        Select Automotive
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 theme-card" data-theme="medical">
                                <div class="card-body text-center position-relative">
                                    <div class="theme-checkmark position-absolute top-0 end-0 m-2" style="display: none;">
                                        <i class="bi bi-check-circle-fill text-danger fs-4"></i>
                                    </div>
                                    <i class="bi bi-heart-pulse text-danger" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Medical</h5>
                                    <p class="card-text">Healthcare and medicine</p>
                                    <button class="btn btn-outline-danger btn-theme" data-theme="medical">
                                        Select Medical
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Select Difficulty Section -->
                    <h4 class="mb-3 text-center">
                        <i class="bi bi-speedometer2"></i> Select Difficulty
                    </h4>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 difficulty-card" data-difficulty="easy">
                                <div class="card-body text-center position-relative">
                                    <div class="difficulty-checkmark position-absolute top-0 end-0 m-2" style="display: none;">
                                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                    </div>
                                    <i class="bi bi-emoji-smile text-success" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Easy</h5>
                                    <p class="card-text">10x10 grid, 8-10 words</p>
                                    <button class="btn btn-outline-success btn-difficulty" data-difficulty="easy">
                                        Select Easy
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 difficulty-card" data-difficulty="medium">
                                <div class="card-body text-center position-relative">
                                    <div class="difficulty-checkmark position-absolute top-0 end-0 m-2" style="display: none;">
                                        <i class="bi bi-check-circle-fill text-warning fs-4"></i>
                                    </div>
                                    <i class="bi bi-emoji-neutral text-warning" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Medium</h5>
                                    <p class="card-text">15x15 grid, 12-15 words</p>
                                    <button class="btn btn-outline-warning btn-difficulty" data-difficulty="medium">
                                        Select Medium
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 difficulty-card" data-difficulty="hard">
                                <div class="card-body text-center position-relative">
                                    <div class="difficulty-checkmark position-absolute top-0 end-0 m-2" style="display: none;">
                                        <i class="bi bi-check-circle-fill text-danger fs-4"></i>
                                    </div>
                                    <i class="bi bi-emoji-frown text-danger" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-2">Hard</h5>
                                    <p class="card-text">20x20 grid, 18-20 words</p>
                                    <button class="btn btn-outline-danger btn-difficulty" data-difficulty="hard">
                                        Select Hard
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Start Game Button -->
                    <div class="text-center mt-4">
                        <button id="startGameBtn" class="btn btn-primary btn-lg" disabled>
                            <i class="bi bi-play-circle"></i> Start Game
                        </button>
                        <p class="text-muted mt-2">Select a theme and difficulty to begin</p>
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
