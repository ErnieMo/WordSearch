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
        console.log('DOM ready, puzzleId:', window.puzzleId);

        // Check if user is logged in
        checkLoginStatus();

        if (window.puzzleId) {
            loadPuzzle(window.puzzleId);
            setupGameEventListeners();
        } else {
            console.error('No puzzleId found');
        }
    });

    // Check if user is logged in and set global variable
    function checkLoginStatus() {
        console.log('checkLoginStatus called with serverAuthState:', window.serverAuthState);
        console.log('Current window.isLoggedIn value:', window.isLoggedIn);

        // If window.isLoggedIn is already set by server, respect that value
        if (typeof window.isLoggedIn !== 'undefined') {
            console.log('Respecting server-set login state:', window.isLoggedIn);
            return;
        }

        // First check server-side authentication state (more reliable)
        if (window.serverAuthState && window.serverAuthState.isLoggedIn) {
            window.isLoggedIn = true;
            console.log('User logged in via server session:', window.serverAuthState.username);
        } else {
            // Fallback to localStorage token check
            const token = localStorage.getItem('authToken');
            window.isLoggedIn = !!token;
            console.log('User login status from localStorage:', window.isLoggedIn);
        }

        console.log('Final window.isLoggedIn value:', window.isLoggedIn);
    }

    // Load puzzle data
    function loadPuzzle(puzzleId) {
        console.log('Loading puzzle:', puzzleId);

        $.ajax({
            url: `/api/puzzle/${puzzleId}`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    console.log('Puzzle response:', response);
                    gameState.puzzle = response.puzzle;
                    gameState.grid = response.puzzle.grid;
                    gameState.words = response.puzzle.words;
                    gameState.placedWords = response.puzzle.placed_words || [];

                    console.log('Game state after loading:', gameState);
                    console.log('Placed words loaded:', gameState.placedWords);

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

        // Update grid size display
        updateGridSize();

        // Verify all words are in the grid and highlight them
        verifyAllWords();
    }

    // Update the grid size display in the title
    function updateGridSize() {
        if (gameState.grid && gameState.grid.length > 0) {
            const gridSize = gameState.grid.length;
            $('#gridSize').text(`${gridSize} × ${gridSize}`);
        } else {
            $('#gridSize').text('Loading...');
        }
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
                const cellContent = gameState.grid[r][c] === ' ' ? '&nbsp;' : gameState.grid[r][c];
                const cell = $(`<td data-r="${r}" data-c="${c}">${cellContent}</td>`);
                if (gameState.grid[r][c] === ' ') {
                    cell.addClass('space-cell');
                }
                row.append(cell);
            }
            tbody.append(row);
        }

        table.append(tbody);
        gridContainer.append(table);

        // Setup grid interaction
        setupGridInteraction();

        // Test basic event binding
        $('.word-grid').on('click', 'td', function () {
            console.log('Basic click test - cell clicked:', $(this).data('r'), $(this).data('c'));
            // Also test if we can add/remove classes
            $(this).toggleClass('test-highlight');
            setTimeout(() => {
                $(this).removeClass('test-highlight');
            }, 500);
        });

        // Auto-highlight words in development mode
        // if (window.APP_ENV === 'development') {
        //     highlightAllPlacedWords();
        // }
    }

    // Highlight all placed words in development mode
    function highlightAllPlacedWords() {
        if (!gameState.placedWords || gameState.placedWords.length === 0) {
            console.log('No placed words to highlight');
            return;
        }

        console.log('Development mode: Highlighting all placed words with light yellow');

        gameState.placedWords.forEach(placedWord => {
            const cells = getCellsInLine(placedWord.start, placedWord.end, placedWord.direction);
            if (cells && cells.length > 0) {
                cells.forEach(cell => {
                    const $cell = $(`.word-grid td[data-r="${cell.r}"][data-c="${cell.c}"]`);
                    if ($cell.length > 0) {
                        $cell.addClass('dev-highlight');
                        console.log(`Highlighted cell [${cell.r}, ${cell.c}] for word "${placedWord.word}"`);
                    }
                });
            }
        });
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

    // Verify all words are in the grid and highlight them
    function verifyAllWords() {
        console.log('Verifying all words are in the grid...');

        const verifiedWords = [];
        const unverifiedWords = [];

        gameState.words.forEach(word => {
            if (isWordInGrid(word)) {
                verifiedWords.push(word);
                // Highlight the word in the word list
                $(`.word-item[data-word="${word}"]`).addClass('verified');
            } else {
                unverifiedWords.push(word);
                console.warn(`Word "${word}" not found in grid!`);
            }
        });

        // Update verification status display
        updateVerificationStatus(verifiedWords.length, gameState.words.length);

        // Show verification results
        if (verifiedWords.length === gameState.words.length) {
            console.log(`✅ All ${verifiedWords.length} words verified in grid`);
            showAlert(`All ${verifiedWords.length} words verified and ready to find!`, 'success');
        } else {
            console.warn(`⚠️ Only ${verifiedWords.length}/${gameState.words.length} words verified`);
            showAlert(`Warning: Only ${verifiedWords.length}/${gameState.words.length} words verified in grid`, 'warning');
        }

        // Store verification results
        gameState.verifiedWords = verifiedWords;
        gameState.unverifiedWords = unverifiedWords;
    }

    // Check if a word exists in the grid (in any direction)
    function isWordInGrid(word) {
        const directions = [
            { dr: 0, dc: 1 },   // horizontal
            { dr: 1, dc: 0 },   // vertical
            { dr: 1, dc: 1 },   // diagonal down-right
            { dr: 1, dc: -1 },  // diagonal down-left
            { dr: -1, dc: 1 },  // diagonal up-right
            { dr: -1, dc: -1 }  // diagonal up-left
        ];

        const grid = gameState.grid;
        const rows = grid.length;
        const cols = grid[0].length;

        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                for (const dir of directions) {
                    if (checkWordAtPosition(word, r, c, dir.dr, dir.dc)) {
                        return true;
                    }
                    // Also check reverse
                    if (checkWordAtPosition(word.split('').reverse().join(''), r, c, dir.dr, dir.dc)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    // Check if a word exists at a specific position and direction
    function checkWordAtPosition(word, startR, startC, dr, dc) {
        const grid = gameState.grid;
        const rows = grid.length;
        const cols = grid[0].length;

        // Check if word fits in this direction
        const endR = startR + (word.length - 1) * dr;
        const endC = startC + (word.length - 1) * dc;

        if (endR < 0 || endR >= rows || endC < 0 || endC >= cols) {
            return false;
        }

        // Check each character
        for (let i = 0; i < word.length; i++) {
            const r = startR + i * dr;
            const c = startC + i * dc;
            if (grid[r][c] !== word[i]) {
                return false;
            }
        }

        return true;
    }

    // Setup grid interaction (click and drag for desktop, touch for mobile)
    function setupGridInteraction() {
        let isSelecting = false;
        let startCell = null;
        let currentTouchCell = null;
        // More accurate touch device detection
        let isTouchDevice = 'ontouchstart' in window && navigator.maxTouchPoints > 0;

        // Additional check: if we're on desktop and have a mouse, prefer mouse events
        if (navigator.userAgent.includes('Windows') || navigator.userAgent.includes('Mac')) {
            isTouchDevice = false;
        }

        if (isTouchDevice) {
            // Touch device handling (iPhone, iPad, etc.)

            $('.word-grid').on('touchstart', 'td', function (e) {
                e.preventDefault();
                isSelecting = true;
                startCell = { r: $(this).data('r'), c: $(this).data('c') };
                currentTouchCell = startCell;
                gameState.selectedCells = [startCell];

                $(this).addClass('selected');
                updateSelection();
            });

            $('.word-grid').on('touchmove', 'td', function (e) {
                if (!isSelecting) return;

                e.preventDefault();

                // Get touch position and find the cell
                const touch = e.originalEvent.touches[0];
                const element = document.elementFromPoint(touch.clientX, touch.clientY);
                const cell = $(element).closest('td');

                if (cell.length && cell.closest('.word-grid').length > 0) {
                    const newCell = { r: cell.data('r'), c: cell.data('c') };

                    // Only update if we're on a different cell
                    if (newCell.r !== currentTouchCell.r || newCell.c !== currentTouchCell.c) {
                        currentTouchCell = newCell;
                        updateSelectionLine(startCell, currentTouchCell);
                    }
                }
            });

            $('.word-grid').on('touchend', 'td', function (e) {
                if (isSelecting) {
                    finishSelection();
                }
                isSelecting = false;
                startCell = null;
                currentTouchCell = null;
            });

            // Also handle document touchend for safety
            $(document).on('touchend', function (e) {
                if (isSelecting) {
                    finishSelection();
                }
                isSelecting = false;
                startCell = null;
                currentTouchCell = null;
            });
        } else {
            // Desktop mouse handling

            // Use event delegation for better performance
            $('.word-grid').on('mousedown', 'td', function (e) {
                e.preventDefault();
                isSelecting = true;
                startCell = { r: $(this).data('r'), c: $(this).data('c') };
                gameState.selectedCells = [startCell];

                $(this).addClass('selected');
                updateSelection();
            });

            $('.word-grid').on('mouseenter', 'td', function (e) {
                if (isSelecting && startCell) {
                    const currentCell = { r: $(this).data('r'), c: $(this).data('c') };
                    updateSelectionLine(startCell, currentCell);
                }
            });

            // Also capture mousemove on the entire grid for better drag tracking
            $('.word-grid').on('mousemove', function (e) {
                if (isSelecting && startCell) {
                    // Find which cell the mouse is currently over
                    const element = document.elementFromPoint(e.clientX, e.clientY);
                    const cell = $(element).closest('td');

                    if (cell.length && cell.closest('.word-grid').length > 0) {
                        const currentCell = { r: cell.data('r'), c: cell.data('c') };

                        // Only update if we're on a different cell
                        if (currentCell.r !== startCell.r || currentCell.c !== startCell.c) {
                            updateSelectionLine(startCell, currentCell);
                        }
                    }
                }
            });

            $(document).on('mouseup', function () {
                if (isSelecting) {
                    finishSelection();
                }
                isSelecting = false;
                startCell = null;
            });
        }
    }

    // Update selection line between two cells
    function updateSelectionLine(start, end) {
        // Clear previous selection
        $('.word-grid td').removeClass('selected');
        gameState.selectedCells = [];

        // Calculate cells in the line
        const cells = getCellsInLine(start, end);

        cells.forEach(cell => {
            $(`.word-grid td[data-r="${cell.r}"][data-c="${cell.c}"]`).addClass('selected');
            gameState.selectedCells.push(cell);
        });

        updateSelection();
    }

    // Finish the current selection
    function finishSelection() {
        // Validate selection
        if (gameState.selectedCells.length > 1) {
            validateSelection();
        }

        // Clear selection
        $('.word-grid td').removeClass('selected');
        gameState.selectedCells = [];
        updateSelection();
    }

    // Get cells in a straight line between two points
    function getCellsInLine(start, end) {
        const cells = [];

        // Handle both object format {r, c} and array format [r, c]
        const startR = Array.isArray(start) ? start[0] : start.r;
        const startC = Array.isArray(start) ? start[1] : start.c;
        const endR = Array.isArray(end) ? end[0] : end.r;
        const endC = Array.isArray(end) ? end[1] : end.c;

        const dr = endR - startR;
        const dc = endC - startC;

        if (dr === 0) {
            // Horizontal line
            const step = dc > 0 ? 1 : -1;
            for (let c = startC; c !== endC + step; c += step) {
                cells.push({ r: startR, c: c });
            }
        } else if (dc === 0) {
            // Vertical line
            const step = dr > 0 ? 1 : -1;
            for (let r = startR; r !== endR + step; r += step) {
                cells.push({ r: r, c: startC });
            }
        } else if (Math.abs(dr) === Math.abs(dc)) {
            // Diagonal line
            const stepR = dr > 0 ? 1 : -1;
            const stepC = dc > 0 ? 1 : -1;
            for (let i = 0; i <= Math.abs(dr); i++) {
                cells.push({
                    r: startR + (i * stepR),
                    c: startC + (i * stepC)
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

        // Update completion modal with game stats (both modals)
        $('#completionTimeLoggedIn').text(timeString);
        $('#hintsUsedLoggedIn').text(gameState.hintsUsed);
        $('#completionTimeGuest').text(timeString);
        $('#hintsUsedGuest').text(gameState.hintsUsed);

        // Save game completion data
        const gameData = {
            game_id: window.gameId,
            completion_time: elapsed,
            hints_used: gameState.hintsUsed,
            words_found: gameState.foundWords.length,
            total_words: gameState.words.length
        };

        // Debug the gameData object
        console.log('Game data being sent:', gameData);
        console.log('Individual values:', {
            game_id: window.gameId,
            completion_time: elapsed,
            hints_used: gameState.hintsUsed,
            words_found: gameState.foundWords.length,
            total_words: gameState.words.length
        });

        // Check if user is logged in
        console.log('Game completion - Authentication check:', {
            windowIsLoggedIn: window.isLoggedIn,
            serverAuthState: window.serverAuthState,
            localStorageToken: !!localStorage.getItem('authToken'),
            windowIsLoggedInType: typeof window.isLoggedIn,
            serverAuthStateType: typeof window.serverAuthState
        });

        // Debug: Check if the values are what we expect
        console.log('Raw values:', {
            windowIsLoggedIn: window.isLoggedIn,
            serverAuthState: window.serverAuthState
        });

        // Debug: Check what the modal should display
        console.log('=== MODAL DISPLAY CHECK ===');
        console.log('Current window.isLoggedIn:', window.isLoggedIn);
        console.log('Current serverAuthState.isLoggedIn:', window.serverAuthState?.isLoggedIn);
        console.log('Modal should show:', window.isLoggedIn ? 'Score saved message' : 'Login message');
        console.log('=== END MODAL DISPLAY CHECK ===');

        if (window.isLoggedIn) {
            // User is logged in, save score immediately
            console.log('User is logged in, saving score...');
            saveScore(gameData);
        } else {
            // User is not logged in, save to session (PHP will show appropriate message)
            console.log('User is not logged in, saving to session...');
            saveGameToSession(gameData);
        }

        // Show the appropriate completion modal based on login status
        console.log('=== SHOWING COMPLETION MODAL ===');
        console.log('About to show modal with authentication state:', window.isLoggedIn);

        if (window.isLoggedIn) {
            // User is logged in - show logged-in modal
            console.log('Showing logged-in modal');
            $('#completionModalLoggedIn').modal('show');
        } else {
            // User is not logged in - show guest modal
            console.log('Showing guest modal');
            $('#completionModalGuest').modal('show');
        }

        console.log('=== END MODAL SHOW ===');
    }

    // Save score to database (for logged-in users)
    function saveScore(gameData) {
        console.log('saveScore called with gameData:', gameData);
        console.log('gameData type:', typeof gameData);
        console.log('gameData JSON:', JSON.stringify(gameData));

        const token = localStorage.getItem('authToken');

        if (!token && window.serverAuthState && window.serverAuthState.isLoggedIn) {
            // User is logged in via session but no token in localStorage
            // This could happen if the page was refreshed or token expired
            console.log('User logged in via session but no token, attempting to save score without token');

            // Try to save score without token (server should recognize session)
            $.ajax({
                url: '/api/scores/save',
                method: 'POST',
                data: JSON.stringify(gameData),
                contentType: 'application/json',
                success: function (response) {
                    console.log('API response received:', response);
                    if (response.success) {
                        console.log('Score saved successfully via session:', response);
                        // PHP already shows the correct message, no need to change display
                    } else {
                        console.error('Failed to save score. Response:', response);
                        console.error('Error field:', response.error);
                        console.error('Full response object:', JSON.stringify(response));
                        // PHP already shows the correct message, no need to change display
                    }
                },
                error: function (xhr) {
                    console.error('Error saving score via session:', xhr.responseText);
                    // PHP already shows the correct message, no need to change display
                }
            });
        } else if (token) {
            // User has token, use it for authentication
            $.ajax({
                url: '/api/scores/save',
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                data: JSON.stringify(gameData),
                contentType: 'application/json',
                success: function (response) {
                    console.log('API response received (token):', response);
                    if (response.success) {
                        console.log('Score saved successfully via token:', response);
                        // PHP already shows the correct message, no need to change display
                    } else {
                        console.error('Failed to save score (token). Response:', response);
                        console.error('Error field:', response.error);
                        console.error('Full response object:', JSON.stringify(response));
                        // PHP already shows the correct message, no need to change display
                    }
                },
                error: function (xhr) {
                    console.error('Error saving score via token:', xhr.responseText);
                    // PHP already shows the correct message, no need to change display
                }
            });
        } else {
            // No authentication available
            console.error('No authentication available for saving score');
            // PHP already shows the correct message, no need to change display
        }
    }

    // Save game data to session (for guest users)
    function saveGameToSession(gameData) {
        // Store in localStorage as temporary data
        localStorage.setItem('tempGameData', JSON.stringify(gameData));
        localStorage.setItem('tempGameTimestamp', Date.now().toString());
        console.log('Game data saved to session:', gameData);
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

        // Play again buttons for both modals
        $('#playAgainBtnLoggedIn, #playAgainBtnGuest').on('click', function () {
            // Hide whichever modal is currently open
            $('#completionModalLoggedIn, #completionModalGuest').modal('hide');
            window.location.href = '/';
        });

        // Setup development controls if in development mode
        setupDevelopmentControls();
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
        console.log('Showing solution...');
        console.log('Placed words:', gameState.placedWords);
        console.log('Game state:', gameState);

        // Highlight all placed words
        if (gameState.placedWords && gameState.placedWords.length > 0) {
            gameState.placedWords.forEach(placedWord => {
                console.log('Processing placed word:', placedWord);
                console.log('Start coordinates:', placedWord.start, 'Type:', typeof placedWord.start);
                console.log('End coordinates:', placedWord.end, 'Type:', typeof placedWord.end);

                const start = placedWord.start;
                const end = placedWord.end;
                const cells = getCellsInLine(start, end);
                console.log('Cells in line:', cells);

                cells.forEach(cell => {
                    const selector = `.word-grid td[data-r="${cell.r}"][data-c="${cell.c}"]`;
                    const element = $(selector);
                    console.log('Adding found class to:', selector, element.length > 0 ? 'found' : 'not found');
                    element.addClass('found');
                });
            });
        } else {
            console.log('No placed words found in gameState');
        }

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

    // Update verification status display
    function updateVerificationStatus(verifiedCount, totalCount) {
        const statusContainer = $('#verificationStatus');

        if (verifiedCount === totalCount) {
            statusContainer.html(`
                <div class="text-success">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                    <div class="mt-2">
                        <strong>All ${verifiedCount} words verified!</strong>
                        <br>
                        <small class="text-muted">Ready to play</small>
                    </div>
                </div>
            `);
        } else {
            statusContainer.html(`
                <div class="text-warning">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    <div class="mt-2">
                        <strong>${verifiedCount}/${totalCount} words verified</strong>
                        <br>
                        <small class="text-muted">Some words may be missing</small>
                    </div>
                </div>
            `);
        }
    }

    // Show alert function for game.js
    function showAlert(message, type = 'info') {
        const alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;">
                <i class="bi bi-info-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(alert);

        // Auto-dismiss after 4 seconds
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, 4000);
    }

    // Development mode: Almost Complete button functionality
    function setupDevelopmentControls() {
        if (window.APP_ENV === 'development') {
            // Show the development controls
            $('#devControls').show();

            // Setup Almost Complete button
            $('#almostCompleteBtn').on('click', function () {
                almostCompletePuzzle();
            });

            // Setup Complete button
            $('#completeBtn').on('click', function () {
                completePuzzle();
            });
        }
    }

    // Make puzzle almost complete (find all but first word)
    function almostCompletePuzzle() {
        if (!gameState.words || gameState.words.length === 0) {
            showAlert('No words available', 'warning');
            return;
        }

        // Get all words except the first one
        const wordsToFind = gameState.words.slice(1);
        console.log('Development: Making puzzle almost complete. Finding words:', wordsToFind);

        // Find each word (except the first)
        wordsToFind.forEach(word => {
            if (gameState.placedWords && gameState.placedWords.length > 0) {
                const placedWord = gameState.placedWords.find(pw => pw.word === word);
                if (placedWord) {
                    // Highlight the word in the grid
                    const cells = getCellsInLine(placedWord.start, placedWord.end, placedWord.direction);
                    cells.forEach(cell => {
                        const $cell = $(`.word-grid td[data-r="${cell.r}"][data-c="${cell.c}"]`);
                        if ($cell.length > 0) {
                            $cell.addClass('found');
                        }
                    });

                    // Mark word as found in the word list
                    $(`.word-item[data-word="${word}"]`).addClass('found');
                }
            }
        });

        // Update game state
        gameState.foundWords = wordsToFind;
        updateProgress();

        // Show completion message
        showAlert(`Development: Puzzle almost complete! ${wordsToFind.length} out of ${gameState.words.length} words found.`, 'success');

        // Update verification status to show progress
        updateVerificationStatus(wordsToFind.length, gameState.words.length);
    }

    // Development mode: Complete puzzle immediately
    function completePuzzle() {
        if (!gameState.words || gameState.words.length === 0) {
            showAlert('No words available', 'warning');
            return;
        }

        console.log('Development: Completing puzzle immediately. Finding all words:', gameState.words);

        // Find all words
        if (gameState.placedWords && gameState.placedWords.length > 0) {
            gameState.placedWords.forEach(placedWord => {
                // Highlight the word in the grid
                const cells = getCellsInLine(placedWord.start, placedWord.end, placedWord.direction);
                cells.forEach(cell => {
                    const $cell = $(`.word-grid td[data-r="${cell.r}"][data-c="${cell.c}"]`);
                    if ($cell.length > 0) {
                        $cell.addClass('found');
                    }
                });

                // Mark word as found in the word list
                $(`.word-item[data-word="${placedWord.word}"]`).addClass('found');
            });
        }

        // Update game state to show all words found
        gameState.foundWords = [...gameState.words];
        updateProgress();

        // Show completion message
        showAlert(`Development: Puzzle completed! All ${gameState.words.length} words found.`, 'success');

        // Complete the game immediately
        completeGame();
    }

})();
