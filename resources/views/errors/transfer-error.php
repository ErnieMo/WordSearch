<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Error - WordSearch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Transfer Error
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?= htmlspecialchars($message ?? 'An error occurred during transfer') ?></p>
                        
                        <div class="d-grid gap-2">
                            <a href="/login" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-1"></i>Go to Login
                            </a>
                            <a href="/" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-1"></i>Go Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
