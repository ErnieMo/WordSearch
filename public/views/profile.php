<?php
$title = 'Word Search - Profile';
$content = '
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 text-primary mb-3">
                    <i class="bi bi-person-circle"></i> My Profile
                </h1>
                <p class="lead text-muted">Manage your account and view your statistics</p>
            </div>

            <!-- Profile Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-info-circle me-2"></i>Account Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">First Name</label>
                                <p class="form-control-plaintext" id="profileFirstName">Loading...</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Last Name</label>
                                <p class="form-control-plaintext" id="profileLastName">Loading...</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Username</label>
                                <p class="form-control-plaintext" id="profileUsername">Loading...</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <p class="form-control-plaintext" id="profileEmail">Loading...</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Member Since</label>
                                <p class="form-control-plaintext" id="profileCreated">Loading...</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Account Status</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-success" id="profileStatus">Active</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="bi bi-pencil me-2"></i>Edit Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Game Statistics -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-graph-up me-2"></i>Game Statistics
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="display-6 text-primary" id="totalGames">-</div>
                                <small class="text-muted">Games Played</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="display-6 text-success" id="totalWords">-</div>
                                <small class="text-muted">Words Found</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="display-6 text-info" id="avgTime">-</div>
                                <small class="text-muted">Avg. Time</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="display-6 text-warning" id="totalHints">-</div>
                                <small class="text-muted">Hints Used</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Games -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Games
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Theme</th>
                                    <th scope="col">Difficulty</th>
                                    <th scope="col" class="text-center">Score</th>
                                    <th scope="col" class="text-center">Time</th>
                                    <th scope="col" class="text-center">Date</th>
                                </tr>
                            </thead>
                            <tbody id="recentGamesTableBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-hourglass-split fs-1 d-block mb-2"></i>
                                        Loading recent games...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Account Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-gear me-2"></i>Account Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <button class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="bi bi-key me-2"></i>Change Password
                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button class="btn btn-outline-danger w-100" onclick="logout()">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Edit Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="editFirstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="editLastName" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="editUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-key me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" required minlength="6">
                        <div class="form-text">Password must be at least 6 characters long</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" required minlength="6">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle me-2"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Profile page functionality
document.addEventListener("DOMContentLoaded", function() {
    loadProfile();
    loadGameStats();
    loadRecentGames();
    
    // Form handlers
    document.getElementById("editProfileForm").addEventListener("submit", handleEditProfile);
    document.getElementById("changePasswordForm").addEventListener("submit", handleChangePassword);
});

function loadProfile() {
    fetch("/api/auth/profile", {
        headers: {
            "Authorization": `Bearer ${getCookie("auth_token")}`
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("profileFirstName").textContent = data.data.first_name;
                document.getElementById("profileLastName").textContent = data.data.last_name;
                document.getElementById("profileUsername").textContent = data.data.username;
                document.getElementById("profileEmail").textContent = data.data.email;
                document.getElementById("profileCreated").textContent = formatDate(data.data.created_at);
                
                // Populate edit form
                document.getElementById("editFirstName").value = data.data.first_name;
                document.getElementById("editLastName").value = data.data.last_name;
                document.getElementById("editUsername").value = data.data.username;
                document.getElementById("editEmail").value = data.data.email;
            } else {
                // Redirect to login if not authenticated
                window.location.href = "/";
            }
        })
        .catch(error => {
            console.error("Error loading profile:", error);
            window.location.href = "/";
        });
}

function loadGameStats() {
    fetch("/api/scores/my/stats", {
        headers: {
            "Authorization": `Bearer ${getCookie("auth_token")}`
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("totalGames").textContent = data.stats.total_games;
                document.getElementById("totalWords").textContent = data.stats.total_words;
                document.getElementById("avgTime").textContent = formatTime(data.stats.avg_time);
                document.getElementById("totalHints").textContent = data.stats.total_hints;
            }
        })
        .catch(error => {
            console.error("Error loading game stats:", error);
        });
}

function loadRecentGames() {
    fetch("/api/scores/my?limit=5", {
        headers: {
            "Authorization": `Bearer ${getCookie("auth_token")}`
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRecentGames(data.scores);
            }
        })
        .catch(error => {
            console.error("Error loading recent games:", error);
        });
}

function displayRecentGames(games) {
    const tbody = document.getElementById("recentGamesTableBody");
    
    if (!games || games.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    You haven\'t played any games yet
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = games.map(game => `
        <tr>
            <td>
                <span class="badge bg-secondary">${game.theme}</span>
            </td>
            <td>
                <span class="badge bg-${getDifficultyColor(game.difficulty)}">${game.difficulty}</span>
            </td>
            <td class="text-center">
                <strong>${game.words_found}/${game.total_words}</strong>
            </td>
            <td class="text-center">${formatTime(game.elapsed_time)}</td>
            <td class="text-center">${formatDate(game.created_at)}</td>
        </tr>
    `).join("");
}

function handleEditProfile(e) {
    e.preventDefault();
    
    const firstName = document.getElementById("editFirstName").value;
    const lastName = document.getElementById("editLastName").value;
    const username = document.getElementById("editUsername").value;
    const email = document.getElementById("editEmail").value;
    
    fetch("/api/auth/profile/update", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${getCookie("auth_token")}`
        },
        body: JSON.stringify({ first_name: firstName, last_name: lastName, username, email })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update display
                document.getElementById("profileFirstName").textContent = firstName;
                document.getElementById("profileLastName").textContent = lastName;
                document.getElementById("profileUsername").textContent = username;
                document.getElementById("profileEmail").textContent = email;
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById("editProfileModal")).hide();
                
                // Show success message
                showAlert("Profile updated successfully!", "success");
            } else {
                showAlert(data.message || "Failed to update profile", "danger");
            }
        })
        .catch(error => {
            console.error("Error updating profile:", error);
            showAlert("Failed to update profile", "danger");
        });
}

function handleChangePassword(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById("currentPassword").value;
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    
    if (newPassword !== confirmPassword) {
        showAlert("New passwords do not match", "danger");
        return;
    }
    
    fetch("/api/auth/password/change", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${getCookie("auth_token")}`
        },
        body: JSON.stringify({ current_password: currentPassword, new_password: newPassword })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear form
                document.getElementById("changePasswordForm").reset();
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById("changePasswordModal")).hide();
                
                // Show success message
                showAlert("Password changed successfully!", "success");
            } else {
                showAlert(data.message || "Failed to change password", "danger");
            }
        })
        .catch(error => {
            console.error("Error changing password:", error);
            showAlert("Failed to change password", "danger");
        });
}

function getDifficultyColor(difficulty) {
    switch (difficulty) {
        case "easy": return "success";
        case "medium": return "warning";
        case "hard": return "danger";
        default: return "secondary";
    }
}

function formatTime(seconds) {
    if (!seconds) return "-";
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, "0")}`;
}

function formatDate(dateString) {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
}

function showAlert(message, type) {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector(".container").insertBefore(alertDiv, document.querySelector(".container").firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function logout() {
    fetch("/api/auth/logout", {
        method: "POST",
        credentials: "include"
    })
        .then(() => {
            document.cookie = "auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            window.location.href = "/";
        })
        .catch(error => {
            console.error("Logout error:", error);
            window.location.href = "/";
        });
}
</script>
';

require_once 'layout.php';
?>
