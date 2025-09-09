<?php
//error_log(__FILE__ . PHP_EOL, 3, __DIR__ . '/../../../../Logs/included_files.log');
?>

<style>
.device-info { font-size: 0.85em; }
.table-responsive { max-height: 600px; overflow-y: auto; }
.stats-card { transition: transform 0.2s; }
.stats-card:hover { transform: translateY(-2px); }
.avatar-sm { width: 32px; height: 32px; font-size: 14px; }
.table th { font-weight: 600; color: #495057; }
.table-hover tbody tr:hover { background-color: rgba(0,123,255,0.05); }
</style>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-users text-primary me-2"></i>
                    User Management
                </h2>
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh
                    </button>

                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-1"><?= count($users) ?></h4>
                                    <p class="card-text mb-0">Total Users</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-1"><?= count($active_users) ?></h4>
                                    <p class="card-text mb-0">Active Users (24h)</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-check fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-info stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-1"><?= count($trusted_devices) ?></h4>
                                    <p class="card-text mb-0">Trusted Devices</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-shield-alt fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-1"><?= count(array_filter($users, fn($u) => isset($u['isadmin']) && $u['isadmin'] === true)) ?></h4>
                                    <p class="card-text mb-0">Administrators</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-shield fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Users Section -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check me-2 text-success"></i>
                        Currently Active Users (Last 24 Hours)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($active_users)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">No users have been active in the last 24 hours.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Device</th>
                                        <th>IP Address</th>
                                        <th>Last Activity</th>
                                        <th>User Agent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_users as $active_user): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($active_user['username']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($active_user['email']) ?></small>
                                                        <?php if (isset($active_user['admin']) && $active_user['admin'] === true): ?>
                                                            <span class="badge bg-danger ms-2">Admin</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-primary ms-2">User</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="device-info"><?= htmlspecialchars($active_user['device_name'] ?? 'Unknown') ?></span>
                                            </td>
                                            <td>
                                                <code class="bg-light px-2 py-1 rounded"><?= htmlspecialchars($active_user['ip_address'] ?? 'Unknown') ?></code>
                                            </td>
                                            <td>
                                                <?php if ($active_user['last_used_at']): ?>
                                                    <span title="<?= $active_user['last_used_at'] ?>" class="text-success">
                                                        <?= $active_user['last_used_formatted'] ?? 'Just now' ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Never</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="device-info" title="<?= htmlspecialchars($active_user['user_agent'] ?? '') ?>">
                                                    <?= $this->truncateUserAgent($active_user['user_agent'] ?? '') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- All Users Section -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2 text-primary"></i>
                        All Users - Login History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Last Login</th>
                                    <th>Trusted Devices</th>
                                    <th>Last Device Activity</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user_item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($user_item['username']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($user_item['email']) ?></small>
                                                    <?php if (isset($user_item['isadmin']) && $user_item['isadmin'] === true): ?>
                                                        <span class="badge bg-danger ms-2">Admin</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary ms-2">User</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($user_item['last_login_at']): ?>
                                                <span title="<?= $user_item['last_login_at'] ?>" class="text-success">
                                                    <?= $user_item['last_login_formatted'] ?? 'Just now' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $user_item['trusted_devices_count'] ?></span>
                                        </td>
                                        <td>
                                            <?php if ($user_item['last_device_activity']): ?>
                                                <span title="<?= $user_item['last_device_activity'] ?>" class="text-info">
                                                    <?= $user_item['last_device_activity_formatted'] ?? 'Just now' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span title="<?= $user_item['created_at'] ?>" class="text-secondary">
                                                <?= $user_item['created_formatted'] ?? 'Unknown' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Trusted Devices Section -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt me-2 text-info"></i>
                        Trusted Devices
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($trusted_devices)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shield-alt fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">No trusted devices found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Device Name</th>
                                        <th>Status</th>
                                        <th>IP Address</th>
                                        <th>Last Used</th>
                                        <th>Expires</th>
                                        <th>Device Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trusted_devices as $device): ?>
                                        <tr class="<?= $device['is_expired'] ? 'table-warning' : '' ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-info rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="fas fa-mobile-alt text-white"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($device['username']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($device['email']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="device-info"><?= htmlspecialchars($device['device_name'] ?? 'Unknown') ?></span>
                                            </td>
                                            <td>
                                                <?php if ($device['status'] === 'Active'): ?>
                                                    <span class="badge bg-success"><?= htmlspecialchars($device['status']) ?></span>
                                                <?php elseif ($device['status'] === 'Expired'): ?>
                                                    <span class="badge bg-warning"><?= htmlspecialchars($device['status']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-info"><?= htmlspecialchars($device['status']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code class="bg-light px-2 py-1 rounded"><?= htmlspecialchars($device['ip_address'] ?? 'Unknown') ?></code>
                                            </td>
                                            <td>
                                                <?php if ($device['last_used_at']): ?>
                                                    <span title="<?= $device['last_used_at'] ?>" class="text-info">
                                                        <?= $device['last_used_formatted'] ?? 'Just now' ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Never</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($device['expires_at']): ?>
                                                    <span title="<?= $device['expires_at'] ?>" class="text-warning">
                                                        <?= $device['expires_formatted'] ?? 'Unknown' ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">No Expiry</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="device-info">
                                                    <?php if ($device['screen_resolution']): ?>
                                                        <div><strong>Screen:</strong> <?= htmlspecialchars($device['screen_resolution']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($device['browser_version']): ?>
                                                        <div><strong>Browser:</strong> <?= htmlspecialchars($device['browser_version']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($device['os_version']): ?>
                                                        <div><strong>OS:</strong> <?= htmlspecialchars($device['os_version']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Login History Section -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2 text-secondary"></i>
                        Recent Login History
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_logins)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-history fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">No recent login activity found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Device</th>
                                        <th>IP Address</th>
                                        <th>Login Time</th>
                                        <th>User Agent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_logins as $login): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="fas fa-sign-in-alt text-white"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($login['username']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($login['email']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="device-info"><?= htmlspecialchars($login['device_name']) ?></span>
                                            </td>
                                            <td>
                                                <code class="bg-light px-2 py-1 rounded"><?= htmlspecialchars($login['ip_address']) ?></code>
                                            </td>
                                            <td>
                                                <span title="<?= $login['login_timestamp'] ?>" class="text-success">
                                                    <?= $login['login_formatted'] ?? 'Just now' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="device-info" title="<?= htmlspecialchars($login['user_agent']) ?>">
                                                    <?= $this->truncateUserAgent($login['user_agent']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshData() {
    location.reload();
}

// Auto-refresh every 5 minutes
setInterval(refreshData, 5 * 60 * 1000);
</script>
