<?php
// Enable error logging for debugging
if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
// error_log("\n\n" . __FILE__ . PHP_EOL, 3, __DIR__ . '/../../../logs/included_files.log');
}

//error_log("=== LOGIN VIEW RENDERING START ===\n");
//error_log("Login view file: " . __FILE__ . "\n");
//error_log("Flash messages: " . json_encode($flash_messages ?? []) . "\n");
//error_log("Session data: " . json_encode($_SESSION ?? []) . "\n");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sudoku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-puzzle-piece fa-3x text-primary mb-3"></i>
                            <h2 class="h3 mb-0">Welcome Back</h2>
                            <p class="text-muted">Sign in to your Sudoku account</p>
                        </div>

                        <?php if (isset($flash_messages['error'])): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($flash_messages['error']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($flash_messages['success'])): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($flash_messages['success']) ?>
                            </div>
                        <?php endif; ?>

                         <form method="POST" action="/login" id="loginForm" class="fingerprint-enabled">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <!-- Hidden fingerprinting fields -->
                            <input type="hidden" id="device_fingerprint" name="device_fingerprint">
                            <input type="hidden" id="screen_resolution" name="screen_resolution">
                            <input type="hidden" id="viewport_size" name="viewport_size">
                            <input type="hidden" id="device_pixel_ratio" name="device_pixel_ratio">
                            <input type="hidden" id="color_depth" name="color_depth">
                            <input type="hidden" id="hardware_cores" name="hardware_cores">
                            <input type="hidden" id="timezone" name="timezone">
                            <input type="hidden" id="canvas_fingerprint" name="canvas_fingerprint">
                            <input type="hidden" id="webgl_renderer" name="webgl_renderer">
                            <input type="hidden" id="audio_sample_rate" name="audio_sample_rate">
                            <input type="hidden" id="device_memory" name="device_memory">
                            <input type="hidden" id="max_touch_points" name="max_touch_points">
                            <input type="hidden" id="available_fonts" name="available_fonts">
                            <input type="hidden" id="font_count" name="font_count">
                            <input type="hidden" id="plugin_count" name="plugin_count">
                            <input type="hidden" id="mime_type_count" name="mime_type_count">
                            <input type="hidden" id="media_capabilities" name="media_capabilities">
                            <input type="hidden" id="battery_api" name="battery_api">
                            <input type="hidden" id="connection_type" name="connection_type">
                            <input type="hidden" id="network_speed" name="network_speed">
                            <input type="hidden" id="browser_version" name="browser_version">
                            <input type="hidden" id="os_version" name="os_version">

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="trust_device" name="trust_device" value="1">
                                    <label class="form-check-label" for="trust_device">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Trust this device
                                    </label>
                                    <div class="form-text">
                                        This will keep you signed in on this device for 30 days. Only use this on devices you trust.
                                    </div>
                                </div>
                            </div>

                            <!-- Device Fingerprint Information -->
                            <?php if (($_ENV['APP_ENV'] ?? 'development') === 'development'): ?>
                            <div class="mb-3 device-fingerprint-info" style="display: none;">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white py-2">
                                        <i class="fas fa-fingerprint me-2"></i>
                                        Device Fingerprint Information
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-info mb-3">Display & Graphics</h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">Screen Resolution:</small><br>
                                                    <span id="device-screen-resolution" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Viewport Size:</small><br>
                                                    <span id="device-viewport-size" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Device Pixel Ratio:</small><br>
                                                    <span id="device-pixel-ratio" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Color Depth:</small><br>
                                                    <span id="device-color-depth" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Canvas Fingerprint:</small><br>
                                                    <span id="device-canvas-fingerprint" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">WebGL Renderer:</small><br>
                                                    <span id="device-webgl-renderer" class="text-monospace small">Detecting...</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-info mb-3">System & Hardware</h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">Hardware Cores:</small><br>
                                                    <span id="device-hardware-cores" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Device Memory:</small><br>
                                                    <span id="device-memory" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Max Touch Points:</small><br>
                                                    <span id="device-max-touch-points" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Audio Sample Rate:</small><br>
                                                    <span id="device-audio-sample-rate" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Timezone:</small><br>
                                                    <span id="device-timezone" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Battery API:</small><br>
                                                    <span id="device-battery-api" class="text-monospace small">Detecting...</span>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="my-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-info mb-3">Software & Browser</h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">Browser Version:</small><br>
                                                    <span id="device-browser-version" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">OS Version:</small><br>
                                                    <span id="device-os-version" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Connection Type:</small><br>
                                                    <span id="device-connection-type" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Network Speed:</small><br>
                                                    <span id="device-network-speed" class="text-monospace small">Detecting...</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-info mb-3">Security & Identity</h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">Fingerprint Hash:</small><br>
                                                    <span id="device-fingerprint-hash" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Hash Preview:</small><br>
                                                    <span id="device-fingerprint-preview" class="text-monospace small">Detecting...</span>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="my-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-info mb-3">Fonts & Typography</h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">Available Fonts:</small><br>
                                                    <span id="device-available-fonts" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Font Count:</small><br>
                                                    <span id="device-font-count" class="text-monospace small">Detecting...</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-info mb-3">Plugins & Media</h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">Plugin Count:</small><br>
                                                    <span id="device-plugin-count" class="text-monospace small">Detecting...</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Media APIs:</small><br>
                                                    <span id="device-media-apis" class="text-monospace small">Detecting...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Sign In
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-2">
                                <a href="/forgot-password" class="text-decoration-none">
                                    <i class="fas fa-key me-1"></i>
                                    Forgot your password?
                                </a>
                            </p>
                            <p class="mb-0">
                                Don't have an account? 
                                <a href="/register" class="text-decoration-none">
                                    <i class="fas fa-user-plus me-1"></i>
                                    Sign up
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted">
                        <i class="fas fa-puzzle-piece me-2"></i>
                        Sudoku
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/device-fingerprint.js"></script>
    <script>
        // Initialize device fingerprinting display
        $(document).ready(function() {
            // Helper function to parse user agent into readable format
            function getReadableBrowserInfo(userAgent) {
                if (!userAgent) return 'Not available';
                
                try {
                    // Extract browser info
                    let browser = 'Unknown';
                    let version = '';
                    
                    if (userAgent.includes('Chrome')) {
                        browser = 'Chrome';
                        const match = userAgent.match(/Chrome\/(\d+)/);
                        if (match) version = match[1];
                    } else if (userAgent.includes('Firefox')) {
                        browser = 'Firefox';
                        const match = userAgent.match(/Firefox\/(\d+)/);
                        if (match) version = match[1];
                    } else if (userAgent.includes('Safari')) {
                        browser = 'Safari';
                        const match = userAgent.match(/Version\/(\d+)/);
                        if (match) version = match[1];
                    } else if (userAgent.includes('Edge')) {
                        browser = 'Edge';
                        const match = userAgent.match(/Edge\/(\d+)/);
                        if (match) version = match[1];
                    }
                    
                    return version ? `${browser} ${version}` : browser;
                } catch (e) {
                    return userAgent.substring(0, 50) + '...';
                }
            }
            
            // Helper function to summarize media API capabilities
            function getMediaAPISummary(media) {
                if (!media) return 'Not available';
                
                const capabilities = [];
                if (media.hasGetUserMedia) capabilities.push('getUserMedia');
                if (media.hasMediaRecorder) capabilities.push('MediaRecorder');
                if (media.hasWebRTC) capabilities.push('WebRTC');
                if (media.supportedMimeTypes && media.supportedMimeTypes.length > 0) {
                    capabilities.push(`${media.supportedMimeTypes.length} codecs`);
                }
                
                return capabilities.length > 0 ? capabilities.join(', ') : 'Basic support only';
            }
            
            // Function to populate hidden fingerprinting fields
            function populateFingerprintingFields(fingerprint) {
                // Populate hidden form fields
                $('#device_fingerprint').val(fingerprint.hash || '');
                $('#screen_resolution').val(fingerprint.screen?.width + 'x' + fingerprint.screen?.height || '');
                $('#viewport_size').val(fingerprint.viewport?.width + 'x' + fingerprint.viewport?.height || '');
                $('#device_pixel_ratio').val(fingerprint.viewport?.devicePixelRatio || '');
                $('#color_depth').val(fingerprint.screen?.colorDepth || '');
                $('#hardware_cores').val(fingerprint.browser?.hardwareConcurrency || '');
                $('#timezone').val(fingerprint.timezone?.timezone || '');
                $('#canvas_fingerprint').val(fingerprint.canvas || '');
                $('#webgl_renderer').val(fingerprint.webgl?.renderer || '');
                $('#audio_sample_rate').val(fingerprint.audio?.sampleRate || '');
                $('#device_memory').val(fingerprint.browser?.deviceMemory || '');
                $('#max_touch_points').val(fingerprint.browser?.maxTouchPoints || '');
                $('#available_fonts').val(fingerprint.fonts?.available ? fingerprint.fonts.available.join(',') : '');
                $('#font_count').val(fingerprint.fonts?.count || '');
                $('#plugin_count').val(fingerprint.plugins?.pluginCount || '');
                $('#mime_type_count').val(fingerprint.plugins?.mimeTypeCount || '');
                $('#media_capabilities').val(JSON.stringify(fingerprint.media || {}));
                $('#battery_api').val(fingerprint.battery || '');
                $('#connection_type').val(fingerprint.browser?.connection?.effectiveType || '');
                $('#network_speed').val(fingerprint.browser?.connection?.downlink || '');
                $('#browser_version').val(getReadableBrowserInfo(fingerprint.userAgent) || '');
                $('#os_version').val(fingerprint.platform || '');
            }
            
            // Show device fingerprint info after a short delay
            setTimeout(function() {
                if (window.deviceFingerprint && window.deviceFingerprint.getFingerprint()) {
                    const fingerprint = window.deviceFingerprint.getFingerprint();
                    
                    // Populate hidden form fields for server submission
                    populateFingerprintingFields(fingerprint);
                    
                    // Update Display & Graphics section
                    $('#device-screen-resolution').text(fingerprint.screen?.width + ' × ' + fingerprint.screen?.height || 'Not available');
                    $('#device-viewport-size').text(fingerprint.viewport?.width + ' × ' + fingerprint.viewport?.height || 'Not available');
                    $('#device-pixel-ratio').text(fingerprint.viewport?.devicePixelRatio || 'Not available');
                    $('#device-color-depth').text(fingerprint.screen?.colorDepth ? fingerprint.screen.colorDepth + ' bit' : 'Not available');
                    $('#device-canvas-fingerprint').text(fingerprint.canvas && fingerprint.canvas !== 'unsupported' && fingerprint.canvas !== 'error' ? fingerprint.canvas.substring(0, 20) + '...' : 'Not available');
                    $('#device-webgl-renderer').text(fingerprint.webgl?.renderer || 'Not available');
                    
                    // Update System & Hardware section
                    $('#device-hardware-cores').text(fingerprint.browser?.hardwareConcurrency || 'Not available');
                    $('#device-memory').text(fingerprint.browser?.deviceMemory ? fingerprint.browser.deviceMemory + ' GB' : 'Not available');
                    $('#device-max-touch-points').text(fingerprint.browser?.maxTouchPoints || 'Not available');
                    $('#device-audio-sample-rate').text(fingerprint.audio?.sampleRate ? fingerprint.audio.sampleRate + ' Hz' : 'Not available');
                    $('#device-timezone').text(fingerprint.timezone?.timezone || 'Not available');
                    $('#device-battery-api').text(fingerprint.battery || 'Not available');
                    
                    // Update Software & Browser section
                    $('#device-browser-version').text(getReadableBrowserInfo(fingerprint.userAgent) || 'Not available');
                    $('#device-os-version').text(fingerprint.platform || 'Not available');
                    $('#device-connection-type').text(fingerprint.browser?.connection?.effectiveType || 'Not available');
                    $('#device-network-speed').text(fingerprint.browser?.connection?.downlink ? fingerprint.browser.connection.downlink + ' Mbps' : 'Not available');
                    
                    // Update Security & Identity section
                    $('#device-fingerprint-hash').text(fingerprint.hash || 'Not available');
                    $('#device-fingerprint-preview').text(fingerprint.hash ? fingerprint.hash.substring(0, 8) + '...' : 'Not available');
                    
                    // Update Fonts & Typography section
                    $('#device-available-fonts').text(fingerprint.fonts?.available ? fingerprint.fonts.available.slice(0, 5).join(', ') + (fingerprint.fonts.available.length > 5 ? '...' : '') : 'Not available');
                    $('#device-font-count').text(fingerprint.fonts?.count || 'Not available');
                    
                    // Update Plugins & Media section
                    $('#device-plugin-count').text(fingerprint.plugins?.pluginCount || 'Not available');
                    $('#device-mime-types').text(fingerprint.plugins?.mimeTypeCount || 'Not available');
                    $('#device-media-apis').text(getMediaAPISummary(fingerprint.media) || 'Not available');
                    
                    // Show the fingerprint info
                    $('.device-fingerprint-info').fadeIn();
                }
            }, 1000);
        });
    </script>
</body>
</html>
<?php
//error_log("=== LOGIN VIEW RENDERING COMPLETE ===\n");
?> 