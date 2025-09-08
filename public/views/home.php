<?php
$pageTitle = 'Home - Word Search Game';

// Set default values if not provided
$theme = $theme ?? 'animals';
$difficulty = $difficulty ?? 'medium';
$diagonals = $diagonals ?? true;
$reverse = $reverse ?? false;

$pageContent = '
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="text-center mb-5">
            <h1 class="display-4 text-success mb-3">
                <i class="bi bi-search-heart me-3"></i>
                Welcome to Word Search!
            </h1>
            <p class="lead text-muted">Choose a theme, select difficulty, and start playing!</p>
        </div>

        <!-- Theme Selection -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-palette me-2"></i>Choose Your Theme</h5>
            </div>
            <div class="card-body">
                <div class="row" id="themeGrid">
                    <!-- Themes will be loaded dynamically -->
                </div>
            </div>
        </div>

        <!-- Difficulty Selection -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Select Difficulty</h5>
            </div>
            <div class="card-body">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="d-grid gap-3">
                            <button type="button" class="btn btn-lg difficulty-btn" data-difficulty="easy" style="background-color: #28a745; color: white; border: none; border-radius: 12px; padding: 15px 20px; font-weight: 600;">
                                <i class="bi bi-star-fill me-2"></i>Easy (10×10)
                            </button>
                            <button type="button" class="btn btn-lg difficulty-btn" data-difficulty="medium" style="background-color: #ffc107; color: black; border: none; border-radius: 12px; padding: 15px 20px; font-weight: 600;">
                                <i class="bi bi-star-fill me-2"></i>Medium (15×15)
                            </button>
                            <button type="button" class="btn btn-lg difficulty-btn" data-difficulty="hard" style="background-color: #dc3545; color: white; border: none; border-radius: 12px; padding: 15px 20px; font-weight: 600;">
                                <i class="bi bi-star-fill me-2"></i>Hard (20×20)
                            </button>
                            <button type="button" class="btn btn-lg difficulty-btn" data-difficulty="expert" style="background-color: #343a40; color: white; border: none; border-radius: 12px; padding: 15px 20px; font-weight: 600;">
                                <i class="bi bi-star-fill me-2"></i>Expert (25×25)
                            </button>
                        </div>
                        <!-- Hidden input to store selected difficulty -->
                        <input type="hidden" name="difficulty" id="selectedDifficulty" value="' . $difficulty . '">
                    </div>
                </div>
            </div>
        </div>

        <!-- Game Options -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Game Options</h5>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="diagonalsEnabled"' . ($diagonals ? ' checked' : '') . '>
                            <label class="form-check-label" for="diagonalsEnabled">
                                <strong>Diagonal Words</strong><br>
                                <small class="text-muted">Include diagonal word placement</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="reverseEnabled"' . ($reverse ? ' checked' : '') . '>
                            <label class="form-check-label" for="reverseEnabled">
                                <strong>Reverse Words</strong><br>
                                <small class="text-muted">Some words may be backwards</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Start Game Button -->
        <div class="text-center">
            <button class="btn btn-success btn-lg px-5" id="startGameBtn" disabled>
                <i class="bi bi-play-circle me-2"></i>
                Start New Game
            </button>
        </div>

        <!-- Game Stats -->
        <div class="row mt-5">
            <div class="col-md-4 text-center">
                <div class="card border-success">
                    <div class="card-body">
                        <i class="bi bi-puzzle text-success" style="font-size: 2rem;"></i>
                        <h5 class="mt-2" id="totalPuzzles">0</h5>
                        <p class="text-muted mb-0">Puzzles Created</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="card border-primary">
                    <div class="card-body">
                        <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                        <h5 class="mt-2" id="totalPlayers">0</h5>
                        <p class="text-muted mb-0">Active Players</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="card border-warning">
                    <div class="card-body">
                        <i class="bi bi-trophy text-warning" style="font-size: 2rem;"></i>
                        <h5 class="mt-2" id="bestTime">--</h5>
                        <p class="text-muted mb-0">Best Time</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-success mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6>Generating Puzzle...</h6>
                <p class="text-muted mb-0">Please wait while we create your word search.</p>
            </div>
        </div>
    </div>
</div>
';

include 'layout.php';
?>

<script>
$(document).ready(function() {
    // Initialize difficulty selection
    const currentDifficulty = $('#selectedDifficulty').val();
    $('.difficulty-btn').removeClass('selected');
    $(`.difficulty-btn[data-difficulty="${currentDifficulty}"]`).addClass('selected');
    
    // Handle difficulty button clicks
    $('.difficulty-btn').on('click', function() {
        const difficulty = $(this).data('difficulty');
        
        // Update hidden input
        $('#selectedDifficulty').val(difficulty);
        
        // Update button states
        $('.difficulty-btn').removeClass('selected');
        $(this).addClass('selected');
        
        console.log('Difficulty selected:', difficulty);
    });
});
</script>

<style>
.difficulty-btn {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.difficulty-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.difficulty-btn.selected {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    border: 3px solid #007bff !important;
}

.difficulty-btn.selected::before {
    content: "✓";
    position: absolute;
    top: 8px;
    right: 12px;
    font-size: 18px;
    font-weight: bold;
}
</style>
