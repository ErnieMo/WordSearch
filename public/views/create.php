<?php
$pageTitle = 'Create Word Search - Word Search Game';

$pageContent = '
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="text-center mb-5">
                <h1 class="display-4 text-success mb-3">
                    <i class="bi bi-plus-circle me-3"></i>
                    Create Word Search
                </h1>
                <p class="lead text-muted">Coming Soon - Custom Word Search Creation Tool</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Coming Soon Section -->
        <div class="col-lg-8 mx-auto">
            <div class="card border-warning mb-4">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Coming in Next Update
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle me-2"></i>Feature Under Development</h5>
                        <p class="mb-0">The custom word search creation tool is currently being developed and will be available in the next major update.</p>
                    </div>
                    
                    <h5 class="text-primary mb-3">Planned Features:</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Custom word list input</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Grid size selection (10x10 to 20x20)</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Difficulty level settings</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Theme customization</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Diagonal word placement options</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Reverse word support</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Save and share puzzles</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Export to PDF</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Print-friendly layouts</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Community puzzle sharing</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- How to Create Section -->
        <div class="col-lg-10 mx-auto">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-book me-2"></i>
                        How to Create a Word Search
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary">Step-by-Step Guide</h5>
                            <div class="timeline">
                                <div class="timeline-item mb-4">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold">1. Choose Your Theme</h6>
                                        <p class="text-muted small">Select a topic or theme for your word search (e.g., Animals, Food, Geography)</p>
                                    </div>
                                </div>
                                <div class="timeline-item mb-4">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold">2. Create Word List</h6>
                                        <p class="text-muted small">Compile 10-20 words related to your theme. Keep words between 3-12 letters.</p>
                                    </div>
                                </div>
                                <div class="timeline-item mb-4">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold">3. Select Grid Size</h6>
                                        <p class="text-muted small">Choose appropriate grid size based on your word list (larger grids for more words)</p>
                                    </div>
                                </div>
                                <div class="timeline-item mb-4">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold">4. Set Difficulty</h6>
                                        <p class="text-muted small">Configure placement options: horizontal, vertical, diagonal, and reverse words</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-danger"></div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold">5. Generate & Test</h6>
                                        <p class="text-muted small">Generate your puzzle and test it to ensure all words are findable</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary">Tips for Great Word Searches</h5>
                            <div class="accordion" id="tipsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#tip1">
                                            <i class="bi bi-lightbulb me-2"></i>Word Selection
                                        </button>
                                    </h2>
                                    <div id="tip1" class="accordion-collapse collapse show" data-bs-parent="#tipsAccordion">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>Use words of varying lengths</li>
                                                <li>Avoid words with uncommon letters (Q, X, Z)</li>
                                                <li>Include both common and challenging words</li>
                                                <li>Ensure all words relate to your theme</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tip2">
                                            <i class="bi bi-gear me-2"></i>Grid Design
                                        </button>
                                    </h2>
                                    <div id="tip2" class="accordion-collapse collapse" data-bs-parent="#tipsAccordion">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>Start with larger grids for easier placement</li>
                                                <li>Use diagonal words sparingly for difficulty</li>
                                                <li>Balance horizontal and vertical words</li>
                                                <li>Test your puzzle before sharing</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tip3">
                                            <i class="bi bi-palette me-2"></i>Presentation
                                        </button>
                                    </h2>
                                    <div id="tip3" class="accordion-collapse collapse" data-bs-parent="#tipsAccordion">
                                        <div class="accordion-body">
                                            <ul class="mb-0">
                                                <li>Use clear, readable fonts</li>
                                                <li>Provide a word list for reference</li>
                                                <li>Include instructions for players</li>
                                                <li>Add a title and theme description</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Current Features -->
        <div class="col-lg-8 mx-auto">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-play-circle me-2"></i>
                        Available Now
                    </h4>
                </div>
                <div class="card-body">
                    <p class="lead">While the custom creation tool is being developed, you can enjoy our pre-made word searches:</p>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-puzzle display-4 text-primary mb-3"></i>
                                    <h5>Pre-made Puzzles</h5>
                                    <p class="text-muted">Play from our collection of themed word searches</p>
                                    <a href="/" class="btn btn-primary">Play Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-trophy display-4 text-warning mb-3"></i>
                                    <h5>Score Tracking</h5>
                                    <p class="text-muted">Track your progress and compete with others</p>
                                    <a href="/scores" class="btn btn-warning">View Scores</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-clock-history display-4 text-info mb-3"></i>
                                    <h5>Game History</h5>
                                    <p class="text-muted">Review your past games and achievements</p>
                                    <a href="/history" class="btn btn-info">View History</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Newsletter Signup -->
        <div class="col-lg-6 mx-auto">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-envelope me-2"></i>
                        Stay Updated
                    </h4>
                </div>
                <div class="card-body text-center">
                    <p class="mb-3">Get notified when the custom word search creation tool is released!</p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Expected Release:</strong> Q2 2024
                    </div>
                    <p class="text-muted small">We\'re working hard to bring you the best word search creation experience. Follow our development progress and be the first to try new features!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: \'\';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.accordion-button:not(.collapsed) {
    background-color: #e7f1ff;
    border-color: #b3d7ff;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
';

include 'layout.php';
?>
