<?php
$title = 'Create - Word Search';
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <h2 class="display-5 text-primary">Create Custom Word Search</h2>
                <p class="lead">Build your own puzzle with custom words and settings</p>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-pencil-square"></i> Puzzle Settings
                    </h3>
                </div>
                <div class="card-body">
                    <form id="createPuzzleForm">
                        <!-- Grid Size -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="gridSize" class="form-label">Grid Size</label>
                                <select class="form-select" id="gridSize" name="gridSize" required>
                                    <option value="10">10×10 (Easy)</option>
                                    <option value="12" selected>12×12 (Medium)</option>
                                    <option value="15">15×15 (Hard)</option>
                                    <option value="custom">Custom Size</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="customSizeGroup" style="display: none;">
                                <label for="customSize" class="form-label">Custom Size</label>
                                <input type="number" class="form-control" id="customSize" name="customSize" min="8" max="20" value="12">
                            </div>
                        </div>

                        <!-- Word Options -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="allowDiagonals" name="allowDiagonals" checked>
                                    <label class="form-check-label" for="allowDiagonals">
                                        Allow diagonal words
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="allowReverse" name="allowReverse">
                                    <label class="form-check-label" for="allowReverse">
                                        Allow reverse words
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Word List -->
                        <div class="mb-3">
                            <label for="wordList" class="form-label">Word List</label>
                            <textarea class="form-control" id="wordList" name="wordList" rows="8" 
                                      placeholder="Enter your words, one per line or separated by commas&#10;Example:&#10;COMPUTER&#10;KEYBOARD&#10;MONITOR&#10;MOUSE" required></textarea>
                            <div class="form-text">
                                Enter words in uppercase letters. One word per line or separated by commas.
                            </div>
                        </div>

                        <!-- Word Count Info -->
                        <div class="alert alert-info mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Words entered:</strong> <span id="wordCount">0</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Max word length:</strong> <span id="maxWordLength">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Preset Word Lists -->
                        <div class="mb-3">
                            <label class="form-label">Quick Presets</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="animals">
                                    <i class="bi bi-tree"></i> Animals
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="geography">
                                    <i class="bi bi-globe"></i> Geography
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="technology">
                                    <i class="bi bi-cpu"></i> Technology
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="food">
                                    <i class="bi bi-cup-hot"></i> Food
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="clear">
                                    <i class="bi bi-trash"></i> Clear
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg" id="createPuzzleBtn">
                                <i class="bi bi-magic"></i> Generate Puzzle
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="card shadow-sm mt-4" id="previewSection" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-eye"></i> Puzzle Preview
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="previewGrid" class="text-center"></div>
                        </div>
                        <div class="col-md-4">
                            <h6>Words to Find:</h6>
                            <div id="previewWords" class="small"></div>
                            <hr>
                            <div class="d-grid gap-2">
                                <button class="btn btn-success" id="playPuzzleBtn">
                                    <i class="bi bi-play-circle"></i> Play This Puzzle
                                </button>
                                <button class="btn btn-outline-primary" id="sharePuzzleBtn">
                                    <i class="bi bi-share"></i> Share Link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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
                <p class="mb-0">Generating your custom puzzle...</p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
