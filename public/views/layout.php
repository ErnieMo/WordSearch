<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Word Search' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="https://wordsearch.dev.nofinway.com/assets/css/app.css" rel="stylesheet">
</head>
<body>
    <!-- Top Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="https://wordsearch.dev.nofinway.com/">
                <i class="bi bi-search-heart me-2"></i>WordSearch
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="https://wordsearch.dev.nofinway.com/">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://wordsearch.dev.nofinway.com/create">
                            <i class="bi bi-plus-circle me-1"></i>Create
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://wordsearch.dev.nofinway.com/scores">
                            <i class="bi bi-trophy me-1"></i>Scores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://wordsearch.dev.nofinway.com/profile">
                            <i class="bi bi-person me-1"></i>Profile
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item" id="authSection">
                        <!-- Auth section will be populated by JavaScript -->
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Development Environment Banner -->
    <div class="alert alert-success text-center mb-0 py-2" role="alert">
        <i class="bi bi-code-slash me-2"></i>
        <strong>Development Environment</strong> - This is a development build of WordSearch
    </div>

    <!-- Main Content -->
    <main class="container-fluid py-4">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 Word Search. Built with Bootstrap 5 & jQuery.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Custom JS -->
    <script src="https://wordsearch.dev.nofinway.com/assets/js/app.js"></script>
    
    <script>
        // Auth state management
        function updateAuthSection() {
            const token = getCookie('auth_token');
            const authSection = document.getElementById('authSection');
            
            if (token) {
                // User is logged in
                authSection.innerHTML = `
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="https://wordsearch.dev.nofinway.com/profile">
                                <i class="bi bi-person me-2"></i>My Profile
                            </a></li>
                            <li><a class="dropdown-item" href="https://wordsearch.dev.nofinway.com/scores">
                                <i class="bi bi-trophy me-2"></i>My Scores
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="logout()">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                `;
            } else {
                // User is not logged in
                authSection.innerHTML = `
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">
                            <i class="bi bi-person-plus me-1"></i>Register
                        </a>
                    </li>
                `;
            }
        }

        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }

        function logout() {
            fetch('/api/auth/logout', {
                method: 'POST',
                credentials: 'include'
            })
            .then(() => {
                document.cookie = 'auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                updateAuthSection();
                window.location.href = '/';
            })
            .catch(error => {
                console.error('Logout error:', error);
            });
        }

        // Initialize auth section on page load
        document.addEventListener('DOMContentLoaded', updateAuthSection);
    </script>
</body>
</html>
