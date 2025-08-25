<?php
$pageTitle = 'Edit Profile - Word Search Game';
$pageContent = '
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>Edit Profile</h4>
            </div>
            <div class="card-body">
                <form id="profileForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="defaultTheme" class="form-label">Default Theme</label>
                            <select class="form-select" id="defaultTheme" name="default_theme">
                                <option value="animals">Animals</option>
                                <option value="automotive">Automotive</option>
                                <option value="food">Food</option>
                                <option value="geography">Geography</option>
                                <option value="medical">Medical</option>
                                <option value="technology">Technology</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="defaultLevel" class="form-label">Default Difficulty</label>
                            <select class="form-select" id="defaultLevel" name="default_level">
                                <option value="easy">Easy (10×10 grid, 10 words)</option>
                                <option value="medium">Medium (15×15 grid, 15 words)</option>
                                <option value="hard">Hard (20×20 grid, 20 words)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="defaultDiagonals" class="form-label">Default Diagonal Words</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="defaultDiagonals" name="default_diagonals" value="1">
                                <label class="form-check-label" for="defaultDiagonals">
                                    Include diagonal word placement
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="defaultReverse" class="form-label">Default Reverse Words</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="defaultReverse" name="default_reverse" value="1">
                                <label class="form-check-label" for="defaultReverse">
                                    Some words may be backwards
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-secondary me-md-2" onclick="window.location.href=\'/\'">
                            <i class="bi bi-arrow-left me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form id="passwordForm">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required minlength="6">
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required minlength="6">
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>';

include 'layout.php';
?>

<script>
$(document).ready(function() {
    // Check if user is authenticated
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '/';
        return;
    }
    
    // Load current profile data
    loadProfile();
    
    // Handle profile form submission
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });
    
    // Handle password form submission
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        changePassword();
    });
});

function loadProfile() {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '/';
        return;
    }
    
    $.ajax({
        url: '/api/auth/profile',
        method: 'GET',
        headers: { 'Authorization': `Bearer ${token}` },
        success: function(response) {
            if (response.success && response.profile) {
                const profile = response.profile;
                
                // Populate form fields
                $('#firstName').val(profile.first_name || '');
                $('#lastName').val(profile.last_name || '');
                $('#email').val(profile.email || '');
                $('#defaultTheme').val(profile.default_theme || 'animals');
                $('#defaultLevel').val(profile.default_level || 'medium');
                $('#defaultDiagonals').prop('checked', profile.default_diagonals !== false);
                $('#defaultReverse').prop('checked', profile.default_reverse !== false);
            }
        },
        error: function(xhr) {
            console.error('Failed to load profile:', xhr.responseText);
            showAlert('Failed to load profile data', 'danger');
        }
    });
}

function updateProfile() {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '/';
        return;
    }
    
    const formData = {
        first_name: $('#firstName').val(),
        last_name: $('#lastName').val(),
        email: $('#email').val(),
        default_theme: $('#defaultTheme').val(),
        default_level: $('#defaultLevel').val(),
        default_diagonals: $('#defaultDiagonals').is(':checked'),
        default_reverse: $('#defaultReverse').is(':checked')
    };
    
    $.ajax({
        url: '/api/auth/profile/update',
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` },
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                showAlert('Profile updated successfully!', 'success');
                
                // Update localStorage with new defaults
                localStorage.setItem('userDefaultTheme', formData.default_theme);
                localStorage.setItem('userDefaultLevel', formData.default_level);
                localStorage.setItem('userDefaultDiagonals', formData.default_diagonals);
                localStorage.setItem('userDefaultReverse', formData.default_reverse);
                
                // Update user display name in navbar
                if (window.WordSearchApp && window.WordSearchApp.updateAuthUI) {
                    window.WordSearchApp.updateAuthUI();
                }
            } else {
                showAlert(response.error || 'Failed to update profile', 'danger');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            showAlert(response?.error || 'Failed to update profile', 'danger');
        }
    });
}

function changePassword() {
    const token = localStorage.getItem('authToken');
    if (!token) {
        window.location.href = '/';
        return;
    }
    
    const newPassword = $('#newPassword').val();
    const confirmPassword = $('#confirmPassword').val();
    
    if (newPassword !== confirmPassword) {
        showAlert('New passwords do not match', 'danger');
        return;
    }
    
    const formData = {
        current_password: $('#currentPassword').val(),
        new_password: newPassword
    };
    
    $.ajax({
        url: '/api/auth/password/change',
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` },
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                showAlert('Password changed successfully!', 'success');
                $('#passwordForm')[0].reset();
            } else {
                showAlert(response.error || 'Failed to change password', 'danger');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            showAlert(response?.error || 'Failed to change password', 'danger');
        }
    });
}

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
</script>
