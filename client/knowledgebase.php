<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Knowledgebase - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .kb-card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s; cursor: pointer; }
        .kb-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="p-5">
    <div class="container">
        <h2 class="mb-4">Knowledgebase</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card kb-card p-4 h-100">
                    <h5><i class="bi bi-gear-wide-connected text-primary me-2"></i> Getting Started</h5>
                    <p class="text-muted small">Learn how to set up your account and order your first service.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card kb-card p-4 h-100">
                    <h5><i class="bi bi-hdd-network text-primary me-2"></i> Hosting Setup</h5>
                    <p class="text-muted small">Step-by-step guides for cPanel, FTP, and WordPress installation.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card kb-card p-4 h-100">
                    <h5><i class="bi bi-wallet2 text-primary me-2"></i> Billing & Payments</h5>
                    <p class="text-muted small">Information about Paystack, Payhub, and manual deposit methods.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
