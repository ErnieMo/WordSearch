<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simulate the same logic as play.php
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);
$currentUsername = $_SESSION['username'] ?? '';

echo "=== PHP Session Debug ===\n";
echo "Session data:\n";
print_r($_SESSION);
echo "\n";

echo "=== Variables ===\n";
echo "\$isLoggedIn: " . ($isLoggedIn ? 'true' : 'false') . "\n";
echo "\$currentUsername: " . $currentUsername . "\n";
echo "\n";

echo "=== JavaScript Output ===\n";
echo "window.serverAuthState = {\n";
echo "    isLoggedIn: " . ($isLoggedIn ? 'true' : 'false') . ",\n";
echo "    username: \"" . htmlspecialchars($currentUsername) . "\"\n";
echo "};\n";
echo "\n";

echo "window.isLoggedIn = " . ($isLoggedIn ? 'true' : 'false') . ";\n";
echo "\n";

echo "=== PHP Conditional Test ===\n";
if ($isLoggedIn) {
    echo "User IS logged in - should show 'Score saved to scoreboard!'\n";
} else {
    echo "User is NOT logged in - should show 'Login to save your score'\n";
}
echo "\n";

echo "=== HTML Output Simulation ===\n";
if ($isLoggedIn) {
    echo "<!-- Logged-in user message - show score saved confirmation -->\n";
    echo "<div id=\"userScoreMessage\">\n";
    echo "    <div class=\"alert alert-success\">\n";
    echo "        <i class=\"bi bi-check-circle me-2\"></i>\n";
    echo "        <strong>Score saved to scoreboard!</strong>\n";
    echo "        <br>\n";
    echo "        <small class=\"text-muted\">Your completion time has been recorded.</small>\n";
    echo "    </div>\n";
    echo "</div>\n";
} else {
    echo "<!-- Guest user message - show login/register options -->\n";
    echo "<div id=\"guestScoreMessage\">\n";
    echo "    <div class=\"alert alert-info\">\n";
    echo "        <i class=\"bi bi-info-circle me-2\"></i>\n";
    echo "        <strong>Login to save your score to the scoreboard!</strong>\n";
    echo "        <br>\n";
    echo "        <small class=\"text-muted\">Your score is temporarily saved and will be lost if you close this page.</small>\n";
    echo "    </div>\n";
    echo "</div>\n";
}
