<?php
/**
 * Profile Settings View
 * This view is designed to work with the shared layout system
 * It only contains the content that gets rendered inside the main section
 * 
 * @author Sudoku App
 * @version 1.0.0
 * @last_modified 2024-01-01
 */

// This view is rendered via BaseController::render() which includes the shared layout
// The $user variable and $flash_messages are passed from the controller
?>

<div class="container py-4">
    <div class="profile-container p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-user text-primary"></i>
                Profile Settings
            </h1>
            <a href="/dashboard" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <form method="POST" action="/profile/update">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            <i class="fas fa-lock"></i> New Password (leave blank to keep current)
                        </label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <div class="form-text">Password must be at least 6 characters long</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock"></i> Confirm New Password
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle"></i> Account Info
                        </h5>
                        <p class="card-text">
                            <strong>Member since:</strong><br>
                            <?= isset($user['created_at']) ? date('M j, Y', strtotime($user['created_at'])) : 'Unknown' ?>
                        </p>
                        <p class="card-text">
                            <strong>Last updated:</strong><br>
                            <?= isset($user['updated_at']) ? date('M j, Y', strtotime($user['updated_at'])) : 'Never' ?>
                        </p>
                        <p class="card-text">
                            <strong>Email verified:</strong><br>
                            <span class="badge bg-<?= ($user['email_verified'] ?? false) ? 'success' : 'warning' ?>">
                                <?= ($user['email_verified'] ?? false) ? 'Yes' : 'No' ?>
                            </span>
                        </p>
                        <p class="card-text">
                            <strong>Last login:</strong><br>
                            <?= isset($user['last_login_at']) ? date('M j, Y g:i A', strtotime($user['last_login_at'])) : 'Never' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-container {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
</style> 