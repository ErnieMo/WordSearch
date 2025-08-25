// Word Search Game - Main JavaScript File
(function () {
    'use strict';

    // Global state
    let currentUser = null;
    let selectedTheme = null;
    let currentGame = null;
    let gameTimer = null;
    let gameStartTime = null;

    // DOM ready
    $(document).ready(function () {
        initializeApp();
        setupEventListeners();
        loadThemes();
        checkAuthStatus();
        applyUserDefaults();
    });

    // Initialize application
    function initializeApp() {
        console.log('Word Search Game initialized');

        // Check if user is already logged in
        const token = localStorage.getItem('authToken');
        if (token) {
            validateToken(token);
        }
    }

    // Apply user defaults to the UI
    function applyUserDefaults() {
        // The server-side preferences are already applied to the HTML
        // We just need to sync the JavaScript state with the UI

        // Get the currently selected difficulty from the UI
        const selectedDifficulty = $('input[name="difficulty"]:checked').val();
        if (selectedDifficulty) {
            console.log('Difficulty pre-selected by server:', selectedDifficulty);
        }

        // Get the current checkbox states from the UI
        const diagonalsEnabled = $('#diagonalsEnabled').is(':checked');
        const reverseEnabled = $('#reverseEnabled').is(':checked');

        console.log('Game options pre-selected by server - Diagonals:', diagonalsEnabled, 'Reverse:', reverseEnabled);

        // Store these preferences for future use
        localStorage.setItem('guestLastDifficulty', selectedDifficulty);
        localStorage.setItem('guestLastDiagonals', diagonalsEnabled);
        localStorage.setItem('guestLastReverse', reverseEnabled);
    }

    // Handle play link click - use user defaults if available
    function handlePlayLink(e) {
        e.preventDefault();

        const defaultTheme = localStorage.getItem('userDefaultTheme') || 'animals';
        const defaultLevel = localStorage.getItem('userDefaultLevel') || 'medium';
        const defaultDiagonals = localStorage.getItem('userDefaultDiagonals');
        const defaultReverse = localStorage.getItem('userDefaultReverse');

        // Set the defaults if not already set
        if (!selectedTheme) {
            $(`.theme-card[data-theme-id="${defaultTheme}"]`).addClass('selected');
            selectedTheme = defaultTheme;
        }

        if (!$('input[name="difficulty"]:checked').length) {
            $(`input[name="difficulty"][value="${defaultLevel}"]`).prop('checked', true);
        }

        if (defaultDiagonals !== null && !$('#diagonalsEnabled').is(':checked')) {
            $('#diagonalsEnabled').prop('checked', defaultDiagonals === 'true');
        }

        if (defaultReverse !== null && !$('#reverseEnabled').is(':checked')) {
            $('#reverseEnabled').prop('checked', defaultReverse === 'true');
        }

        // Start the game with current selection
        startNewGame();
    }

    // Setup event listeners
    function setupEventListeners() {
        // Authentication forms
        $('#loginForm').on('submit', handleLogin);
        $('#registerForm').on('submit', handleRegister);
        $('#logoutBtn').on('click', handleLogout);

        // Game controls
        $('#startGameBtn').on('click', startNewGame);
        $('#playLink').on('click', handlePlayLink);

        // Theme selection
        $(document).on('click', '.theme-card', selectTheme);

        // Difficulty and options
        $('input[name="difficulty"]').on('change', updateGameOptions);
        $('#diagonalsEnabled, #reverseEnabled').on('change', updateGameOptions);
    }

    // Authentication functions
    function handleLogin(e) {
        e.preventDefault();

        const username = $('#loginUsername').val();
        const password = $('#loginPassword').val();

        if (!username || !password) {
            showAlert('Please fill in all fields', 'danger');
            return;
        }

        $.ajax({
            url: '/api/auth/login',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ username, password }),
            success: function (response) {
                if (response.success) {
                    localStorage.setItem('authToken', response.token);
                    currentUser = {
                        id: response.user_id,
                        username: response.username,
                        firstName: response.first_name,
                        lastName: response.last_name
                    };

                    // Store user defaults
                    localStorage.setItem('userDefaultTheme', response.default_theme || 'animals');
                    localStorage.setItem('userDefaultLevel', response.default_level || 'medium');
                    localStorage.setItem('userDefaultDiagonals', response.default_diagonals !== false);
                    localStorage.setItem('userDefaultReverse', response.default_reverse !== false);

                    updateAuthUI();
                    $('#loginModal').modal('hide');
                    showAlert('Login successful!', 'success');

                    // Check if there's saved game data to process
                    checkForSavedGameData();

                    // Clear form
                    $('#loginForm')[0].reset();
                } else {
                    showAlert(response.error || 'Login failed', 'danger');
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                showAlert(response?.error || 'Login failed', 'danger');
            }
        });
    }

    function handleRegister(e) {
        e.preventDefault();

        const formData = {
            username: $('#registerUsername').val(),
            email: $('#registerEmail').val(),
            password: $('#registerPassword').val(),
            first_name: $('#registerFirstName').val(),
            last_name: $('#registerLastName').val()
        };

        if (Object.values(formData).some(val => !val)) {
            showAlert('Please fill in all fields', 'danger');
            return;
        }

        $.ajax({
            url: '/api/auth/register',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function (response) {
                if (response.success) {
                    localStorage.setItem('authToken', response.token);
                    currentUser = {
                        id: response.user_id,
                        username: response.username,
                        firstName: response.first_name,
                        lastName: response.last_name
                    };

                    updateAuthUI();
                    $('#registerModal').modal('hide');
                    showAlert('Registration successful! You are now logged in.', 'success');

                    // Check if there's saved game data to process
                    checkForSavedGameData();

                    // Clear form
                    $('#registerForm')[0].reset();
                } else {
                    showAlert(response.error || 'Registration failed', 'danger');
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                showAlert(response?.error || 'Registration failed', 'danger');
            }
        });
    }

    function handleLogout() {
        const token = localStorage.getItem('authToken');

        if (token) {
            $.ajax({
                url: '/api/auth/logout',
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` },
                success: function () {
                    // Clear local data
                    localStorage.removeItem('authToken');
                    currentUser = null;
                    updateAuthUI();
                    showAlert('Logged out successfully', 'info');
                }
            });
        } else {
            localStorage.removeItem('authToken');
            currentUser = null;
            updateAuthUI();
        }
    }

    function validateToken(token) {
        $.ajax({
            url: '/api/auth/profile',
            method: 'GET',
            headers: { 'Authorization': `Bearer ${token}` },
            success: function (response) {
                if (response.success && response.profile) {
                    currentUser = {
                        id: response.profile.id,
                        username: response.profile.username,
                        firstName: response.profile.first_name,
                        lastName: response.profile.last_name
                    };
                    updateAuthUI();
                } else {
                    localStorage.removeItem('authToken');
                }
            },
            error: function () {
                localStorage.removeItem('authToken');
            }
        });
    }

    function updateAuthUI() {
        if (currentUser) {
            // Only update if the server-side state doesn't already show the user
            if ($('#guestNav').is(':visible')) {
                $('#guestNav').addClass('d-none');
                $('#userNav').removeClass('d-none');
                $('#userDisplayName').text(currentUser.firstName || currentUser.username);
            }
        } else {
            // Only update if the server-side state doesn't already show the guest
            if ($('#userNav').is(':visible')) {
                $('#guestNav').removeClass('d-none');
                $('#userNav').addClass('d-none');
            }
        }
    }

    function checkAuthStatus() {
        // Check if server-side already shows user as logged in
        if ($('#userNav').is(':visible') && $('#guestNav').is(':hidden')) {
            // User is already logged in via session, just get the token for API calls
            const token = localStorage.getItem('authToken');
            if (!token) {
                // Need to get a fresh token since session exists but no token in localStorage
                // This could happen if the page was refreshed
                console.log('Session exists but no token, need to refresh authentication');
                // For now, just update the UI to match server state
                currentUser = {
                    username: $('#userDisplayName').text()
                };
            }
            // Apply defaults for already logged in users
            setTimeout(applyUserDefaults, 100);
        } else {
            // Check localStorage token
            const token = localStorage.getItem('authToken');
            if (token) {
                validateToken(token);
            } else {
                updateAuthUI();
            }
        }
    }

    // Theme management
    function loadThemes() {
        console.log('Loading themes from API...');
        $.ajax({
            url: '/api/themes',
            method: 'GET',
            success: function (response) {
                console.log('Themes API response:', response);
                if (response.success) {
                    // Store full theme data including words for later use
                    window.loadedThemes = response.themes;
                    console.log('Stored themes in cache:', response.themes.length, 'themes');
                    renderThemes(response.themes);
                    updateStats(response.stats);
                } else {
                    console.error('Themes API returned success=false');
                }
            },
            error: function (xhr, status, error) {
                console.error('Failed to load themes:', status, error, xhr.responseText);
                showAlert('Failed to load themes', 'danger');
            }
        });
    }

    function renderThemes(themes) {
        const themeGrid = $('#themeGrid');
        themeGrid.empty();

        // Define theme icons and colors
        const themeIcons = {
            'animals': 'bi-egg-fried',
            'automotive': 'bi-car-front',
            'food': 'bi-cup-hot',
            'geography': 'bi-globe',
            'medical': 'bi-heart-pulse',
            'technology': 'bi-laptop'
        };

        const themeColors = {
            'animals': 'success',
            'automotive': 'dark',
            'food': 'warning',
            'geography': 'info',
            'medical': 'danger',
            'technology': 'primary'
        };

        themes.forEach(theme => {
            const icon = themeIcons[theme.id] || 'bi-palette';
            const color = themeColors[theme.id] || 'secondary';

            const themeCard = $(`
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card theme-card h-100" data-theme-id="${theme.id}">
                        <div class="card-header bg-${color} text-white text-center">
                            <h6 class="mb-0">
                                <i class="bi ${icon} me-2"></i>
                                ${theme.name}
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-muted small mb-2">${theme.description}</p>
                            <div class="d-flex justify-content-center align-items-center">
                                <span class="badge bg-secondary">
                                    <i class="bi bi-list-ul me-1"></i>
                                    ${theme.word_count} words
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `);

            themeGrid.append(themeCard);
        });

        // Log the themes that were loaded
        console.log(`Rendered ${themes.length} themes:`, themes.map(t => t.name));

        // Auto-select the default theme (Animals) if no theme is currently selected
        if (!selectedTheme) {
            const defaultTheme = 'animals';
            $(`.theme-card[data-theme-id="${defaultTheme}"]`).addClass('selected');
            selectedTheme = defaultTheme;
            updateStartButton();
            console.log('Auto-selected default theme:', defaultTheme);
        }
    }

    function getDifficultyColor(difficulty) {
        switch (difficulty) {
            case 'easy': return 'success';
            case 'medium': return 'warning';
            case 'hard': return 'danger';
            default: return 'secondary';
        }
    }

    function selectTheme() {
        $('.theme-card').removeClass('selected');
        $(this).addClass('selected');

        selectedTheme = $(this).data('theme-id');
        updateStartButton();
    }

    function updateStartButton() {
        const canStart = selectedTheme !== null;
        $('#startGameBtn').prop('disabled', !canStart);
    }

    function updateGameOptions() {
        // This function can be used to update game options in real-time
        console.log('Game options updated');
    }

    // Game functions
    function startNewGame() {
        if (!selectedTheme) {
            showAlert('Please select a theme first', 'warning');
            return;
        }

        // Get authentication token (optional - users can play without logging in)
        const token = localStorage.getItem('authToken');

        const difficulty = $('input[name="difficulty"]:checked').val();
        const diagonals = $('#diagonalsEnabled').is(':checked');
        const reverse = $('#reverseEnabled').is(':checked');

        // Show loading modal
        $('#loadingModal').modal('show');

        // Send theme ID and options directly to backend for word selection and randomization
        const puzzleData = {
            theme_id: selectedTheme,
            options: {
                difficulty: difficulty,
                diagonals: diagonals,
                reverse: reverse
            }
        };

        // Prepare headers (only include Authorization if token exists)
        const headers = {
            'Content-Type': 'application/json'
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        $.ajax({
            url: '/api/generate',
            method: 'POST',
            headers: headers,
            data: JSON.stringify(puzzleData),
            success: function (response) {
                $('#loadingModal').modal('hide');

                if (response.success) {
                    currentGame = response.puzzle;
                    // Store game ID for tracking
                    localStorage.setItem('currentGameId', response.game_id);
                    // Redirect to play page with puzzle ID
                    window.location.href = `/play?id=${response.id}`;
                } else {
                    showAlert(response.error || 'Failed to generate puzzle', 'danger');
                }
            },
            error: function (xhr) {
                $('#loadingModal').modal('hide');
                const response = xhr.responseJSON;
                if (xhr.status === 401) {
                    showAlert('Authentication required. Please log in to save your score to the leaderboard.', 'info');
                    // Don't force login - user can still play as guest
                } else {
                    showAlert(response?.error || 'Failed to generate puzzle', 'danger');
                }
            }
        });
    }



    // Utility functions
    function showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Remove existing alerts
        $('.alert').remove();

        // Add new alert at the top of the main content
        $('main').prepend(alertHtml);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }

    function updateStats(stats) {
        $('#totalPuzzles').text(stats.total_puzzles || 0);
        $('#totalPlayers').text(stats.total_themes || 0);
        $('#bestTime').text('--');
    }

    // Check for saved game data after login/registration
    function checkForSavedGameData() {
        const tempGameData = localStorage.getItem('tempGameData');
        const tempGameTimestamp = localStorage.getItem('tempGameTimestamp');

        if (tempGameData && tempGameTimestamp) {
            // Check if the saved data is recent (within last hour)
            const savedTime = parseInt(tempGameTimestamp);
            const currentTime = Date.now();
            const oneHour = 60 * 60 * 1000; // 1 hour in milliseconds

            if (currentTime - savedTime < oneHour) {
                try {
                    const gameData = JSON.parse(tempGameData);
                    console.log('Found saved game data after login:', gameData);

                    // Save the score now that user is logged in
                    saveScoreAfterLogin(gameData);

                    // Clear temporary data
                    localStorage.removeItem('tempGameData');
                    localStorage.removeItem('tempGameTimestamp');

                    showAlert('Your previous game score has been saved to the scoreboard!', 'success');
                } catch (e) {
                    console.error('Error processing saved game data:', e);
                    localStorage.removeItem('tempGameData');
                    localStorage.removeItem('tempGameTimestamp');
                }
            } else {
                // Data is too old, remove it
                localStorage.removeItem('tempGameData');
                localStorage.removeItem('tempGameTimestamp');
            }
        }
    }

    // Save score after user logs in (for previously completed games)
    function saveScoreAfterLogin(gameData) {
        const token = localStorage.getItem('authToken');
        if (!token) {
            console.error('No auth token available for saving score');
            return;
        }

        $.ajax({
            url: '/api/scores/save',
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(gameData),
            success: function (response) {
                if (response.success) {
                    console.log('Previously completed game score saved:', response);
                } else {
                    console.error('Failed to save previous game score:', response.error);
                }
            },
            error: function (xhr) {
                console.error('Error saving previous game score:', xhr.responseText);
            }
        });
    }

    // Export functions for use in other scripts
    window.WordSearchApp = {
        currentUser,
        selectedTheme,
        currentGame,
        showAlert,
        updateAuthUI
    };

})();
