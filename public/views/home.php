<?php
$title = 'Word Search - Home';
$content = '
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Welcome Section -->
            <div class="text-center mb-5">
                <h1 class="display-4 text-primary mb-3">
                    <i class="bi bi-search-heart"></i> Welcome to Word Search!
                </h1>
                <p class="lead text-muted">Choose your theme and difficulty to begin!</p>
            </div>

            <!-- Choose Theme Section -->
            <h4 class="mb-3 text-center">
                <i class="bi bi-palette"></i> Choose Theme
            </h4>
            <div class="row mb-4">
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 theme-card border-primary" data-theme="animals">
                        <div class="card-body text-center position-relative">
                            <div class="theme-checkmark position-absolute top-0 end-0 m-2">
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
                    <div class="card h-100 difficulty-card border-primary" data-difficulty="easy">
                        <div class="card-body text-center position-relative">
                            <div class="difficulty-checkmark position-absolute top-0 end-0 m-2">
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
                <p class="text-muted mt-2">Animals theme and Easy difficulty are pre-selected. Click Start Game to begin!</p>
            </div>
        </div>
    </div>

    <!-- Quick Start -->
    <div class="row justify-content-center mt-5">
        <div class="col-lg-8">
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

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label for="loginUsername" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="loginUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <p class="mb-0">Don\'t have an account? 
                    <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>Register
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="registerForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="registerFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="registerFirstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="registerLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="registerLastName" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="registerUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="registerUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="registerEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="registerPassword" required minlength="6">
                        <div class="form-text">Password must be at least 6 characters long</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-person-plus me-2"></i>Register
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <p class="mb-0">Already have an account? 
                    <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>
';

require_once 'layout.php';
?>
