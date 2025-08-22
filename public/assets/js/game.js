// Word Search Game - Game Logic
(function () {
    'use strict';

    // Game state
    let gameState = {
        puzzle: null,
        grid: [],
        words: [],
        placedWords: [],
        foundWords: [],
        selectedCells: [],
        startTime: null,
        timer: null,
        hintsUsed: 0
    };

    // DOM ready
    $(document).ready(function () {
        if (window.puzzleId) {
            loadPuzzle(window.puzzleId);
            setupGameEventListeners();
        }
    });

    // Load puzzle data
    function loadPuzzle(puzzleId) {
        console.log('Loading puzzle:', puzzleId);

        $.ajax({
            url: `/api/puzzle/${puzzleId}`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    gameState.puzzle = response.puzzle;
                    gameState.grid = response.puzzle.grid;
                    gameState.words = response.puzzle.words;
                    gameState.placedWords = response.puzzle.placed_words || [];

                    renderGame();
                    startTimer();
                } else {
                    showGameError('Failed to load puzzle: ' + (response.error || 'Unknown error'));
                }
            },
            error: function (xhr) {
                showGameError('Failed to load puzzle. Please try again.');
                console.error('Puzzle load error:', xhr.responseText);
            }
        });
    }

    // Render the game interface
    function renderGame() {
        renderGrid();
        renderWordList();
        updateProgress();
    }

    // Render the word search grid
    function renderGrid() {
        const gridContainer = $('#gameGrid');
        gridContainer.empty();

        if (!gameState.grid || gameState.grid.length === 0) {
            gridContainer.html('<div class="alert alert-danger">Invalid grid data</div>');
            return;
        }

        const table = $('<table class="word-grid"></table>');
        const tbody = $('<tbody></tbody>');

        for (let r = 0; r < gameState.grid.length; r++) {
            const row = $('<tr></tr>');
            for (let c = 0; c < gameState.grid[r].length; c++) {
                const cell = $(`<td data-r="${r}" data-c="${c}">${gameState.grid[r][c]}</td>`);
                row.append(cell);
            }
            tbody.append(row);
        }

        table.append(tbody);
        gridContainer.append(table);

        // Setup grid interaction
        setupGridInteraction();
    }

    // Render the word list
    function renderWordList() {
        const wordListContainer = $('#wordList');
        wordListContainer.empty();

        if (!gameState.words || gameState.words.length === 0) {
            wordListContainer.html('<div class="text-muted">No words available</div>');
            return;
        }

        gameState.words.forEach(word => {
            const wordItem = $(`<div class="word-item" data-word="${word}">${word}</div>`);
            wordListContainer.append(wordItem);
        });

        // Update total words count
        $('#totalWords').text(gameState.words.length);
    }

    // Setup grid interaction (click and drag)
    function setupGridInteraction() {
        let isSelecting = false;
        let startCell = null;

        $('.word-grid td').on('mousedown touchstart', function (e) {
            e.preventDefault();
            isSelecting = true;
            startCell = { r: $(this).data('r'), c: $(this).data('c') };
            gameState.selectedCells = [startCell];

            $(this).addClass('selected');
            updateSelection();
        });

        $('.word-grid td').on('mouseenter touchenter', function (e) {
            if (isSelecting && startCell) {
                const currentCell = { r: $(this).data('r'), c: $(this).data('c') };

                // Clear previous selection
                $('.word-grid td').removeClass('selected');
                gameState.selectedCells = [];

                // Calculate cells in the line
                const cells = getCellsInLine(startCell, currentCell);
                cells.forEach(cell => {
                    $(`.word-grid td[data-r="${cell.r}"][data-c="${cell.c}"]`).addClass('selected');
                    gameState.selectedCells.push(cell);
                });

                updateSelection();
            }
        });

        $(document).on('mouseup touchend', function () {
            if (isSelecting) {
                isSelecting = false;
                startCell = null;

                // Validate selection
                if (gameState.selectedCells.length > 1) {
                    validateSelection();
                }

                // Clear selection
                $('.word-grid td').removeClass('selected');
                gameState.selectedCells = [];
                updateSelection();
            }
        });
    }

    // Get cells in a straight line between two points
    function getCellsInLine(start, end) {
        const cells = [];
        const dr = end.r - start.r;
        const dc = end.c - start.c;

        if (dr === 0) {
            // Horizontal line
            const step = dc > 0 ? 1 : -1;
            for (let c = start.c; c !== end.c + step; c += step) {
                cells.push({ r: start.r, c: c });
            }
        } else if (dc === 0) {
            // Vertical line
            const step = dr > 0 ? 1 : -1;
            for (let r = start.r; r !== end.r + step; r += step) {
                cells.push({ r: r, c: start.c });
            }
        } else if (Math.abs(dr) === Math.abs(dc)) {
            // Diagonal line
            const stepR = dr > 0 ? 1 : -1;
            const stepC = dc > 0 ? 1 : -1;
            for (let i = 0; i <= Math.abs(dr); i++) {
                cells.push({
                    r: start.r + (i * stepR),
                    c: start.c + (i * stepC)
                });
            }
        }

        return cells;
    }

    // Update selection display
    function updateSelection() {
        // This function can be used to show visual feedback during selection
    }

    // Validate the current selection
    function validateSelection() {
        if (gameState.selectedCells.length < 2) return;

        // Extract word from selection
        const word = gameState.selectedCells
            .map(cell => gameState.grid[cell.r][cell.c])
            .join('');

        // Check if word exists in the word list
        const wordIndex = gameState.words.indexOf(word);
        const reverseWordIndex = gameState.words.indexOf(word.split('').reverse().join(''));

        if (wordIndex !== -1 || reverseWordIndex !== -1) {
            const foundWord = wordIndex !== -1 ? gameState.words[wordIndex] : gameState.words[reverseWordIndex];

            if (!gameState.foundWords.includes(foundWord)) {
                // Mark word as found
                gameState.foundWords.push(foundWord);

                // Mark cells as found
                gameState.selectedCells.forEach(cell => {
                    $(`.word-grid td[data-r="${cell.r}"][data-c="${cell.c}"]`)
                        .removeClass('selected')
                        .addClass('found');
                });

                // Update word list
                $(`.word-item[data-word="${foundWord}"]`).addClass('found');

                // Update progress
                updateProgress();

                // Check if game is complete
                if (gameState.foundWords.length === gameState.words.length) {
                    completeGame();
                }

                // Show success feedback
                showWordFound(foundWord);
            }
        }
    }

    // Show word found feedback
    function showWordFound(word) {
        const alert = $(`
            <div class="alert alert-success alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Found:</strong> ${word}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(alert);

        // Auto-dismiss after 2 seconds
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, 2000);
    }

    // Update progress display
    function updateProgress() {
        const found = gameState.foundWords.length;
        const total = gameState.words.length;
        const percentage = total > 0 ? (found / total) * 100 : 0;

        $('#wordsFound').text(found);
        $('#totalWords').text(total);
        $('#progressBar').css('width', percentage + '%');
    }

    // Start the game timer
    function startTimer() {
        gameState.startTime = Date.now();
        gameState.timer = setInterval(updateTimer, 1000);
    }

    // Update the timer display
    function updateTimer() {
        if (!gameState.startTime) return;

        const elapsed = Math.floor((Date.now() - gameState.startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;

        $('#gameTimer').text(
            (minutes < 10 ? '0' : '') + minutes + ':' +
            (seconds < 10 ? '0' : '') + seconds
        );
    }

    // Complete the game
    function completeGame() {
        clearInterval(gameState.timer);

        const elapsed = Math.floor((Date.now() - gameState.startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        const timeString = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;

        $('#completionTime').text(timeString);
        $('#hintsUsed').text(gameState.hintsUsed);

        // Show completion modal
        $('#completionModal').modal('show');
    }

    // Setup game event listeners
    function setupGameEventListeners() {
        // Hint button
        $('#hintBtn').on('click', function () {
            if (gameState.hintsUsed < 3) {
                gameState.hintsUsed++;
                showHint();
            } else {
                showGameError('No more hints available!');
            }
        });

        // Reset selection button
        $('#resetBtn').on('click', function () {
            $('.word-grid td').removeClass('selected');
            gameState.selectedCells = [];
        });

        // Show solution button
        $('#showSolutionBtn').on('click', function () {
            if (confirm('Are you sure you want to see the solution? This will end the game.')) {
                showSolution();
            }
        });

        // New game button
        $('#newGameBtn').on('click', function () {
            window.location.href = '/';
        });

        // Play again button
        $('#playAgainBtn').on('click', function () {
            $('#completionModal').modal('hide');
            window.location.href = '/';
        });
    }

    // Show a hint
    function showHint() {
        const unfoundWords = gameState.words.filter(word => !gameState.foundWords.includes(word));
        if (unfoundWords.length === 0) return;

        const randomWord = unfoundWords[Math.floor(Math.random() * unfoundWords.length)];
        const alert = $(`
            <div class="alert alert-info alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;">
                <i class="bi bi-lightbulb me-2"></i>
                <strong>Hint:</strong> Look for "${randomWord}"
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(alert);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, 3000);
    }

    // Show solution
    function showSolution() {
        // Highlight all placed words
        gameState.placedWords.forEach(placedWord => {
            const start = placedWord.start;
            const end = placedWord.end;
            const cells = getCellsInLine(start, end);

            cells.forEach(cell => {
                $(`.word-grid td[data-r="${cell.r}"][data-c="${cell.c}"]`).addClass('found');
            });
        });

        // Mark all words as found
        gameState.foundWords = [...gameState.words];
        updateProgress();

        // End game
        clearInterval(gameState.timer);
        showGameError('Game ended. Solution shown.');
    }

    // Show game error
    function showGameError(message) {
        const alert = $(`
            <div class="alert alert-danger alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;">
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(alert);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, 5000);
    }

})();
