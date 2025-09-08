<?php
$page_title = 'WordSearch - Welcome';
?>

<!-- Hero Section -->
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="hero-section p-5 text-center">
                <h1 class="main-title mb-4">
                    <i class="fas fa-search puzzle-icon"></i>
                    Welcome to WordSearch
                </h1>
                <p class="description-text mb-5">
                    Challenge your mind with our interactive WordSearch puzzles. 
                    Multiple themes, difficulty levels, and leaderboards await!
                </p>

                <?php if (isset($flash_messages['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($flash_messages['error']) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($flash_messages['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($flash_messages['success']) ?>
                    </div>
                <?php endif; ?>

                <div class="row mb-5">
                    <div class="col-md-4 mb-4">
                        <div class="feature-card p-4 h-100">
                            <div class="feature-icon brain">
                                <i class="fas fa-palette"></i>
                            </div>
                            <h4 class="feature-title">Multiple Themes</h4>
                            <p class="feature-description">Animals, Food, Technology, Geography, Medical, and Automotive themes.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="feature-card p-4 h-100">
                            <div class="feature-icon clock">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h4 class="feature-title">Timer Tracking</h4>
                            <p class="feature-description">Track your completion times and compete on leaderboards.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="feature-card p-4 h-100">
                            <div class="feature-icon save">
                                <i class="fas fa-save"></i>
                            </div>
                            <h4 class="feature-title">Save & Resume</h4>
                            <p class="feature-description">Save your progress and continue playing anytime.</p>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-3 d-md-flex justify-content-md-center mb-5">
                    <?php if (isset($user)): ?>
                        <a href="/dashboard" class="btn btn-custom btn-purple btn-lg">
                            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                        </a>
                        <a href="#" onclick="showDifficultyModal(); return false;" class="btn btn-custom btn-green btn-lg">
                            <i class="fas fa-play"></i> Start New Game
                        </a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-custom btn-purple btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </a>
                        <a href="/register" class="btn btn-custom btn-green btn-lg">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                </div>

                <!-- How to Play Section -->
                <div class="text-center mb-5">
                    <h3 class="mb-4 text-dark">
                        <i class="fas fa-graduation-cap text-primary me-2"></i>
                        New to WordSearch?
                    </h3>
                    <button onclick="showHowToPlayModal(); return false;" class="btn btn-custom btn-info btn-lg">
                        <i class="fas fa-question-circle me-2"></i>
                        How to Play WordSearch
                    </button>
                    <p class="text-muted mt-3">
                        Learn the rules, strategies, and tips to become a WordSearch master!
                    </p>
                </div>

                <div class="features-section">
                    <h5 class="features-title text-center">Features</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="feature-item"><i class="fas fa-check"></i> Real-time validation</li>
                                <li class="feature-item"><i class="fas fa-check"></i> Smart hints system</li>
                                <li class="feature-item"><i class="fas fa-check"></i> Mobile responsive</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="feature-item"><i class="fas fa-check"></i> Global leaderboards</li>
                                <li class="feature-item"><i class="fas fa-check"></i> Personal statistics</li>
                                <li class="feature-item"><i class="fas fa-check"></i> Secure authentication</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background: linear-gradient(135deg, #e8e4f3 0%, #f3f0f9 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .hero-section {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(103, 126, 234, 0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .main-title {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
        font-size: 3.5rem;
    }
    
    .feature-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        transition: all 0.3s ease;
        border: 1px solid rgba(103, 126, 234, 0.1);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }
    
    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(103, 126, 234, 0.2);
    }
    
    .feature-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2rem;
        color: white;
    }
    
    .feature-icon.brain {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .feature-icon.clock {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    }
    
    .feature-icon.save {
        background: linear-gradient(135deg, #3498db 0%, #5dade2 100%);
    }
    
    .btn-custom {
        border-radius: 12px;
        padding: 12px 30px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .btn-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .btn-purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-purple:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        color: white;
    }
    
    .btn-green {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        color: white;
    }
    
    .btn-green:hover {
        background: linear-gradient(135deg, #229954 0%, #27ae60 100%);
        color: white;
    }
    
    .features-section {
        margin-top: 3rem;
    }
    
    .features-title {
        color: #667eea;
        font-weight: 600;
        margin-bottom: 2rem;
    }
    
    .feature-item {
        padding: 0.5rem 0;
        color: #555;
        font-size: 1.1rem;
    }
    
    .feature-item i {
        color: #27ae60;
        margin-right: 0.5rem;
        width: 20px;
    }
    
    .description-text {
        font-size: 1.3rem;
        color: #666;
        line-height: 1.6;
    }
    
    .feature-title {
        color: #333;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .feature-description {
        color: #666;
        line-height: 1.5;
    }
    
    .puzzle-icon {
        margin-right: 0.5rem;
    }
</style> 