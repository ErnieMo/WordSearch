// Word Search Game - Main JavaScript File
(function() {
    'use strict';

    // Global state
    let currentUser = null;
    let selectedTheme = null;
    let currentGame = null;
    let gameTimer = null;
    let gameStartTime = null;

    // DOM ready
    $(document).ready(function() {
        initializeApp();
        setupEventListeners();
        loadThemes();
        checkAuthStatus();
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

    // Setup event listeners
    function setupEventListeners() {
        // Authentication forms
        $('#loginForm').on('submit', handleLogin);
        $('#registerForm').on('submit', handleRegister);
        $('#logoutBtn').on('click', handleLogout);

        // Game controls
        $('#startGameBtn').on('click', startNewGame);
        
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
            success: function(response) {
                if (response.success) {
                    localStorage.setItem('authToken', response.token);
                    currentUser = {
                        id: response.user_id,
                        username: response.username,
                        firstName: response.first_name,
                        lastName: response.last_name
                    };
                    
                    updateAuthUI();
                    $('#loginModal').modal('hide');
                    showAlert('Login successful!', 'success');
                    
                    // Clear form
                    $('#loginForm')[0].reset();
                } else {
                    showAlert(response.error || 'Login failed', 'danger');
                }
            },
            error: function(xhr) {
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
            success: function(response) {
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
                    
                    // Clear form
                    $('#registerForm')[0].reset();
                } else {
                    showAlert(response.error || 'Registration failed', 'danger');
                }
            },
            error: function(xhr) {
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
                success: function() {
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
            success: function(response) {
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
            error: function() {
                localStorage.removeItem('authToken');
            }
        });
    }

    function updateAuthUI() {
        if (currentUser) {
            $('#guestNav').addClass('d-none');
            $('#userNav').removeClass('d-none');
            $('#userDisplayName').text(currentUser.firstName || currentUser.username);
        } else {
            $('#guestNav').removeClass('d-none');
            $('#userNav').addClass('d-none');
        }
    }

    function checkAuthStatus() {
        const token = localStorage.getItem('authToken');
        if (token) {
            validateToken(token);
        }
    }

    // Theme management
    function loadThemes() {
        console.log('Loading themes from API...');
        $.ajax({
            url: '/api/themes',
            method: 'GET',
            success: function(response) {
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
            error: function(xhr, status, error) {
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
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary">
                                    <i class="bi bi-list-ul me-1"></i>
                                    ${theme.word_count} words
                                </string>
                                <span class="badge bg-${getDifficultyColor(theme.difficulty)}">
                                    <i class="bi bi-speedometer2 me-1"></i>
                                    ${theme.difficulty}
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
        
        const difficulty = $('input[name="difficulty"]:checked').val();
        const diagonals = $('#diagonalsEnabled').is(':checked');
        const reverse = $('#reverseEnabled').is(':checked');
        
        // Show loading modal
        $('#loadingModal').modal('show');
        
        // Get theme words from the loaded themes
        const theme = window.loadedThemes ? window.loadedThemes.find(t => t.id === selectedTheme) : null;
        if (theme && theme.words && Array.isArray(theme.words)) {
            console.log('Using cached theme words:', theme.words.length, 'words');
            generatePuzzle(theme.words, { difficulty, diagonals, reverse });
        } else {
            console.log('Theme words not in cache, fetching from API...');
            // Fallback: try to get words from API
            $.ajax({
                url: '/api/themes',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const theme = response.themes.find(t => t.id === selectedTheme);
                        if (theme && theme.words && Array.isArray(theme.words)) {
                            console.log('API returned theme words:', theme.words.length, 'words');
                            generatePuzzle(theme.words, { difficulty, diagonals, reverse });
                        } else {
                            $('#loadingModal').modal('hide');
                            console.error('Theme words not available from API:', theme);
                            showAlert('Theme words not available', 'danger');
                        }
                    } else {
                        $('#loadingModal').modal('hide');
                        showAlert('Failed to load theme data', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    $('#loadingModal').modal('hide');
                    console.error('API error:', status, error, xhr.responseText);
                    showAlert('Failed to load theme words', 'danger');
                }
            });
        }
    }

    function generatePuzzle(words, options) {
        // Safety check: ensure words is defined and is an array
        if (!words || !Array.isArray(words) || words.length === 0) {
            $('#loadingModal').modal('hide');
            showAlert('No words available for this theme', 'danger');
            return;
        }
        
        // Determine grid size based on difficulty
        const sizeMap = { easy: 10, medium: 15, hard: 20 };
        const gridSize = sizeMap[options.difficulty] || 15;
        
        // Filter words based on difficulty
        const filteredWords = words.filter(word => {
            if (options.difficulty === 'easy') return word.length <= 6;
            if (options.difficulty === 'medium') return word.length <= 10;
            return true; // hard difficulty - all words
        });
        
        // Take first 10-15 words depending on difficulty
        const wordCount = options.difficulty === 'easy' ? 10 : 15;
        const selectedWords = filteredWords.slice(0, wordCount);
        
        const puzzleData = {
            words: selectedWords,
            options: {
                size: gridSize,
                diagonals: options.diagonals,
                reverse: options.reverse
            }
        };
        
        $.ajax({
            url: '/api/generate',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(puzzleData),
            success: function(response) {
                $('#loadingModal').modal('hide');
                
                if (response.success) {
                    currentGame = response.puzzle;
                    // Redirect to play page with puzzle ID
                    window.location.href = `/play?id=${response.id}`;
                } else {
                    showAlert(response.error || 'Failed to generate puzzle', 'danger');
                }
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                const response = xhr.responseJSON;
                showAlert(response?.error || 'Failed to generate puzzle', 'danger');
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

    // Export functions for use in other scripts
    window.WordSearchApp = {
        currentUser,
        selectedTheme,
        currentGame,
        showAlert,
        updateAuthUI
    };

})();
