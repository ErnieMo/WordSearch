/**
 * Word Search Game - Main JavaScript
 */

(function ($) {
    'use strict';

    // Game state
    let gameState = {
        puzzle: null,
        foundWords: new Set(),
        startTime: null,
        timer: null,
        isSelecting: false,
        selectionStart: null,
        currentSelection: []
    };

    // Preset word lists
    const presetWords = {
        animals: ['ELEPHANT', 'GIRAFFE', 'LION', 'TIGER', 'MONKEY', 'ZEBRA', 'PANDA', 'KOALA', 'KANGAROO', 'RHINO'],
        geography: ['AMERICA', 'EUROPE', 'ASIA', 'AFRICA', 'AUSTRALIA', 'CANADA', 'BRAZIL', 'CHINA', 'INDIA', 'JAPAN'],
        technology: ['COMPUTER', 'KEYBOARD', 'MONITOR', 'MOUSE', 'LAPTOP', 'TABLET', 'SMARTPHONE', 'INTERNET', 'SOFTWARE', 'HARDWARE'],
        food: ['PIZZA', 'BURGER', 'SALAD', 'SUSHI', 'PASTA', 'STEAK', 'CHICKEN', 'FISH', 'BREAD', 'CHEESE']
    };

    // Initialize when DOM is ready
    $(document).ready(function () {
        initializeGame();
        setupEventListeners();
    });

    function initializeGame() {
        // Check if we're on the play page and have a puzzle ID
        const urlParams = new URLSearchParams(window.location.search);
        const puzzleId = urlParams.get('id');

        if (puzzleId && window.location.pathname === '/play') {
            loadPuzzle(puzzleId);
        }
    }

    function setupEventListeners() {
        // Home page events
        $('.btn-difficulty').on('click', handleDifficultySelection);
        $('.btn-theme').on('click', handleThemeSelection);
        $('#quickStart').on('click', handleQuickStart);
        $('#startGame').on('click', startGameFromModal);

        // Create page events
        $('#createPuzzleForm').on('submit', handleCreatePuzzle);
        $('#gridSize').on('change', handleGridSizeChange);
        $('#wordList').on('input', updateWordCount);
        $('[data-preset]').on('click', handlePresetSelection);
        $('#playPuzzleBtn').on('click', playCreatedPuzzle);
        $('#sharePuzzleBtn').on('click', shareCreatedPuzzle);

        // Play page events
        $('#hintBtn').on('click', showHint);
        $('#newGameBtn').on('click', startNewGame);
        $('#printBtn').on('click', printPuzzle);
        $('#playAgain').on('click', startNewGame);
        $('#copyLink').on('click', copyShareLink);

        // Grid interaction events
        $(document).on('mousedown touchstart', '.game-grid td', startSelection);
        $(document).on('mousemove touchmove', '.game-grid td', updateSelection);
        $(document).on('mouseup touchend', '.game-grid td', endSelection);
    }

    // Home page functions
    function handleDifficultySelection() {
        const difficulty = $(this).data('difficulty');
        const size = $(this).data('size');

        $('#selectedDifficulty').text(difficulty.charAt(0).toUpperCase() + difficulty.slice(1));
        $('#difficulty').val(difficulty);
        $('#gridSize').val(size);

        // Set options based on difficulty
        if (difficulty === 'easy') {
            $('#allowDiagonals').prop('checked', false);
            $('#allowReverse').prop('checked', false);
        } else if (difficulty === 'medium') {
            $('#allowDiagonals').prop('checked', true);
            $('#allowReverse').prop('checked', false);
        } else {
            $('#allowDiagonals').prop('checked', true);
            $('#allowReverse').prop('checked', true);
        }

        $('#gameOptionsModal').modal('show');
    }

    function handleThemeSelection() {
        const theme = $(this).data('theme');
        $('#selectedTheme').text(theme.charAt(0).toUpperCase() + theme.slice(1));
        $('#theme').val(theme);
    }

    function handleQuickStart() {
        // Use default settings for quick start
        $('#difficulty').val('medium');
        $('#gridSize').val('12');
        $('#theme').val('technology');
        $('#allowDiagonals').prop('checked', true);
        $('#allowReverse').prop('checked', false);

        $('#gameOptionsModal').modal('show');
    }

    function startGameFromModal() {
        const options = {
            difficulty: $('#difficulty').val(),
            gridSize: parseInt($('#gridSize').val()),
            theme: $('#theme').val(),
            allowDiagonals: $('#allowDiagonals').is(':checked'),
            allowReverse: $('#allowReverse').is(':checked')
        };

        $('#gameOptionsModal').modal('hide');
        startGame(options);
    }

    function startGame(options) {
        // Show loading modal
        $('#loadingModal').modal('show');

        // Get words for the selected theme
        const words = presetWords[options.theme] || presetWords.technology;

        // Generate puzzle
        $.ajax({
            url: 'https://wordsearch.dev.nofinway.com/api/generate',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                words: words,
                options: {
                    size: options.gridSize,
                    diagonals: options.allowDiagonals,
                    reverse: options.allowReverse
                }
            }),
            success: function (response) {
                $('#loadingModal').modal('hide');

                // Store puzzle and redirect to play page
                localStorage.setItem('currentPuzzle', JSON.stringify(response));
                window.location.href = 'https://wordsearch.dev.nofinway.com/play?id=' + response.id;
            },
            error: function (xhr) {
                $('#loadingModal').modal('hide');
                alert('Error generating puzzle: ' + (xhr.responseJSON?.error || 'Unknown error'));
            }
        });
    }

    // Create page functions
    function handleCreatePuzzle(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const words = formData.get('wordList')
            .split(/[\n,]/)
            .map(word => word.trim().toUpperCase())
            .filter(word => word.length > 0);

        if (words.length === 0) {
            alert('Please enter at least one word.');
            return;
        }

        const gridSize = formData.get('gridSize') === 'custom'
            ? parseInt(formData.get('customSize'))
            : parseInt(formData.get('gridSize'));

        const options = {
            size: gridSize,
            diagonals: formData.get('allowDiagonals') === 'on',
            reverse: formData.get('allowReverse') === 'on'
        };

        // Show loading modal
        $('#loadingModal').modal('show');

        // Generate puzzle
        $.ajax({
            url: 'https://wordsearch.dev.nofinway.com/api/generate',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                words: words,
                options: options
            }),
            success: function (response) {
                $('#loadingModal').modal('hide');
                showPuzzlePreview(response);
            },
            error: function (xhr) {
                $('#loadingModal').modal('hide');
                alert('Error generating puzzle: ' + (xhr.responseJSON?.error || 'Unknown error'));
            }
        });
    }

    function showPuzzlePreview(puzzle) {
        // Render preview grid
        const gridHtml = renderGrid(puzzle.grid, false);
        $('#previewGrid').html(gridHtml);

        // Show word list
        const wordsHtml = puzzle.words.map(word =>
            `<div class="word-item">${word}</div>`
        ).join('');
        $('#previewWords').html(wordsHtml);

        // Store puzzle for later use
        localStorage.setItem('previewPuzzle', JSON.stringify(puzzle));

        // Show preview section
        $('#previewSection').show();

        // Scroll to preview
        $('#previewSection')[0].scrollIntoView({ behavior: 'smooth' });
    }

    function handleGridSizeChange() {
        if ($(this).val() === 'custom') {
            $('#customSizeGroup').show();
        } else {
            $('#customSizeGroup').hide();
        }
    }

    function updateWordCount() {
        const text = $(this).val();
        const words = text.split(/[\n,]/).filter(word => word.trim().length > 0);
        const maxLength = Math.max(...words.map(word => word.trim().length), 0);

        $('#wordCount').text(words.length);
        $('#maxWordLength').text(maxLength);
    }

    function handlePresetSelection() {
        const preset = $(this).data('preset');

        if (preset === 'clear') {
            $('#wordList').val('');
            updateWordCount();
        } else if (presetWords[preset]) {
            $('#wordList').val(presetWords[preset].join('\n'));
            updateWordCount();
        }
    }

    function playCreatedPuzzle() {
        const puzzle = JSON.parse(localStorage.getItem('previewPuzzle'));
        if (puzzle) {
            localStorage.setItem('currentPuzzle', JSON.stringify(puzzle));
            window.location.href = 'https://wordsearch.dev.nofinway.com/play?id=' + puzzle.id;
        }
    }

    function shareCreatedPuzzle() {
        const puzzle = JSON.parse(localStorage.getItem('previewPuzzle'));
        if (puzzle) {
            const shareUrl = window.location.origin + '/play?id=' + puzzle.id;
            navigator.clipboard.writeText(shareUrl).then(() => {
                alert('Share link copied to clipboard!');
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = shareUrl;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Share link copied to clipboard!');
            });
        }
    }

    // Play page functions
    function loadPuzzle(puzzleId) {
        $.ajax({
            url: 'https://wordsearch.dev.nofinway.com/api/puzzle/' + puzzleId,
            method: 'GET',
            success: function (response) {
                gameState.puzzle = response;
                renderGame();
                startTimer();
            },
            error: function (xhr) {
                alert('Error loading puzzle: ' + (xhr.responseJSON?.error || 'Unknown error'));
                window.location.href = 'https://wordsearch.dev.nofinway.com/';
            }
        });
    }

    function renderGame() {
        if (!gameState.puzzle) return;

        // Render grid
        const gridHtml = renderGrid(gameState.puzzle.grid, true);
        $('#gameGrid').html(gridHtml);

        // Render word list
        const wordsHtml = gameState.puzzle.words.map(word =>
            `<div class="word-item" data-word="${word}">${word}</div>`
        ).join('');
        $('#wordList').html(wordsHtml);

        // Update game info
        $('#gameDifficulty').text(getDifficultyText(gameState.puzzle.size));
        $('#gameSize, #gameSize2').text(gameState.puzzle.size);
        $('#gameTheme').text('Custom');
        $('#totalCount').text(gameState.puzzle.words.length);

        // Set share link
        const shareUrl = window.location.origin + '/play?id=' + gameState.puzzle.id;
        $('#shareLink').val(shareUrl);

        // Add fade-in animation
        $('.card').addClass('fade-in');
    }

    function renderGrid(grid, interactive) {
        let html = '<div class="game-grid"><table>';

        for (let row = 0; row < grid.length; row++) {
            html += '<tr>';
            for (let col = 0; col < grid[row].length; col++) {
                const cellClass = interactive ? 'class="grid-cell"' : '';
                const dataAttrs = interactive ? `data-r="${row}" data-c="${col}" data-ch="${grid[row][col]}"` : '';
                html += `<td ${cellClass} ${dataAttrs}>${grid[row][col]}</td>`;
            }
            html += '</tr>';
        }

        html += '</table></div>';
        return html;
    }

    function getDifficultyText(size) {
        if (size <= 10) return 'Easy';
        if (size <= 12) return 'Medium';
        return 'Hard';
    }

    function startTimer() {
        gameState.startTime = Date.now();
        gameState.timer = setInterval(updateTimer, 1000);
    }

    function updateTimer() {
        if (!gameState.startTime) return;

        const elapsed = Math.floor((Date.now() - gameState.startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;

        $('#timer').text(
            (minutes < 10 ? '0' : '') + minutes + ':' +
            (seconds < 10 ? '0' : '') + seconds
        );
    }

    // Grid interaction functions
    function startSelection(e) {
        if (!gameState.puzzle) return;

        e.preventDefault();
        gameState.isSelecting = true;
        gameState.selectionStart = { row: $(this).data('r'), col: $(this).data('c') };
        gameState.currentSelection = [gameState.selectionStart];

        $(this).addClass('selected');
    }

    function updateSelection(e) {
        if (!gameState.isSelecting || !gameState.puzzle) return;

        e.preventDefault();
        const currentCell = { row: $(this).data('r'), col: $(this).data('c') };

        // Clear previous selection
        $('.game-grid td').removeClass('selected');

        // Calculate selection path
        const path = calculateSelectionPath(gameState.selectionStart, currentCell);
        gameState.currentSelection = path;

        // Highlight selection
        path.forEach(pos => {
            $(`.game-grid td[data-r="${pos.row}"][data-c="${pos.col}"]`).addClass('selected');
        });
    }

    function endSelection(e) {
        if (!gameState.isSelecting || !gameState.puzzle) return;

        e.preventDefault();
        gameState.isSelecting = false;

        // Clear selection highlighting
        $('.game-grid td').removeClass('selected');

        // Check if selection forms a valid word
        if (gameState.currentSelection.length > 1) {
            checkWordSelection();
        }
    }

    function calculateSelectionPath(start, end) {
        const path = [];
        const dr = end.row - start.row;
        const dc = end.col - start.col;

        // Determine direction
        const steps = Math.max(Math.abs(dr), Math.abs(dc));
        if (steps === 0) return [start];

        const stepR = dr / steps;
        const stepC = dc / steps;

        for (let i = 0; i <= steps; i++) {
            const row = Math.round(start.row + (stepR * i));
            const col = Math.round(start.col + (stepC * i));
            path.push({ row, col });
        }

        return path;
    }

    function checkWordSelection() {
        if (!gameState.puzzle) return;

        // Get the word from the selection
        const word = gameState.currentSelection
            .map(pos => gameState.puzzle.grid[pos.row][pos.col])
            .join('');

        // Check if it's a valid word (forward or reverse)
        const validWords = gameState.puzzle.words;
        const isValid = validWords.includes(word) || validWords.includes(word.split('').reverse().join(''));

        if (isValid && !gameState.foundWords.has(word)) {
            // Mark word as found
            gameState.foundWords.add(word);

            // Highlight found cells
            gameState.currentSelection.forEach(pos => {
                $(`.game-grid td[data-r="${pos.row}"][data-c="${pos.col}"]`).addClass('found');
            });

            // Mark word in list as found
            $(`.word-item[data-word="${word}"]`).addClass('found');

            // Update found count
            $('#foundCount').text(gameState.foundWords.size);

            // Check for win
            if (gameState.foundWords.size === validWords.length) {
                setTimeout(showWinModal, 500);
            }
        }
    }

    function showHint() {
        if (!gameState.puzzle) return;

        // Find a random unfound word
        const unfoundWords = gameState.puzzle.words.filter(word => !gameState.foundWords.has(word));
        if (unfoundWords.length === 0) return;

        const hintWord = unfoundWords[Math.floor(Math.random() * unfoundWords.length)];

        // Find the word in the grid and highlight it temporarily
        const wordInfo = gameState.puzzle.placed.find(p => p.word === hintWord);
        if (wordInfo) {
            // Highlight the word cells
            const direction = wordInfo.direction;
            const startRow = wordInfo.startRow || 0;
            const startCol = wordInfo.startCol || 0;

            for (let i = 0; i < hintWord.length; i++) {
                const row = startRow + (direction[0] * i);
                const col = startCol + (direction[1] * i);
                $(`.game-grid td[data-r="${row}"][data-c="${col}"]`).addClass('hint');
            }

            // Remove hint after 3 seconds
            setTimeout(() => {
                $('.game-grid td').removeClass('hint');
            }, 3000);
        }
    }

    function startNewGame() {
        window.location.href = 'https://wordsearch.dev.nofinway.com/';
    }

    function printPuzzle() {
        window.print();
    }

    function copyShareLink() {
        const shareUrl = $('#shareLink').val();
        navigator.clipboard.writeText(shareUrl).then(() => {
            // Show feedback
            const originalText = $('#copyLink').html();
            $('#copyLink').html('<i class="bi bi-check"></i>');
            setTimeout(() => {
                $('#copyLink').html(originalText);
            }, 2000);
        });
    }

    function showWinModal() {
        // Stop timer
        if (gameState.timer) {
            clearInterval(gameState.timer);
        }

        // Update final stats
        $('#finalTime').text($('#timer').text());
        $('#finalWords').text(gameState.foundWords.size);

        // Show modal
        $('#winModal').modal('show');

        // Add celebration effect
        $('.game-grid').addClass('celebration');
    }

    // Utility functions
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Expose functions globally for debugging
    window.WordSearchGame = {
        gameState,
        showHint,
        startNewGame,
        printPuzzle
    };

})(jQuery);
