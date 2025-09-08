<?php
/**
 * Trusted Device Auto-Login Message
 * 
 * This view is shown when a user is automatically logged in via a trusted device cookie.
 * It gives them the option to continue or remove the trusted device preference.
 * 
 * @author System
 * @last_modified 2024-01-XX
 */

// This file is included by the AuthController, so it's safe to proceed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Back - Trusted Device Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .trusted-device-card {
            max-width: 500px;
            margin: 50px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            border: none;
        }
        .trusted-device-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 30px;
            text-align: center;
        }
        .trusted-device-body {
            padding: 30px;
        }
        .device-info {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .btn-continue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            margin-right: 15px;
        }
        .btn-remove {
            background-color: #dc3545;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-continue:hover, .btn-remove:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .flash-message {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="card trusted-device-card">
            <div class="trusted-device-header">
                <i class="fas fa-shield-check fa-3x mb-3"></i>
                <h2>Welcome Back!</h2>
                <p class="mb-0">You've been automatically logged in via a trusted device</p>
            </div>
            
            <div class="trusted-device-body">
                <?php if (!empty($flash_messages)): ?>
                    <?php foreach ($flash_messages as $type => $messages): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show flash-message" role="alert">
                                <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="text-center mb-4">
                    <h4>Hello, <?= htmlspecialchars($user['username']) ?>!</h4>
                    <p class="text-muted">You're logged in from a device you previously marked as trusted.</p>
                </div>

                <div class="device-info">
                    <h6><i class="fas fa-info-circle text-primary"></i> Device Information</h6>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Device Name:</small><br>
                            <strong><?= htmlspecialchars($trusted_device_record['device_name'] ?? 'Unknown Device') ?></strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Trusted Since:</small><br>
                            <strong><?= date('M j, Y', strtotime($trusted_device_record['created_at'] ?? 'now')) ?></strong>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <small class="text-muted">IP Address:</small><br>
                            <strong><?= htmlspecialchars($trusted_device_record['ip_address'] ?? 'Unknown') ?></strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Expires:</small><br>
                            <strong><?= date('M j, Y', strtotime($trusted_device_record['expires_at'] ?? 'now')) ?></strong>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <p class="text-muted mb-4">
                        <i class="fas fa-lightbulb text-warning"></i>
                        This device will remain trusted for 30 days unless you remove it.
                    </p>
                    
                    <div class="d-flex justify-content-center">
                        <a href="/dashboard" class="btn btn-primary btn-continue">
                            <i class="fas fa-arrow-right"></i> Continue to Dashboard
                        </a>
                        
                        <form method="POST" action="/remove-trusted-device" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to remove this device as trusted? You will need to login again on this device.')">
                            <button type="submit" class="btn btn-danger btn-remove">
                                <i class="fas fa-times"></i> Remove Trusted Device
                            </button>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i>
                        Your trusted device cookie is encrypted and secure. 
                        It only contains device identification information.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
