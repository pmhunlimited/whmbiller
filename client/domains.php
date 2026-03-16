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
    <title>Domain Management - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Domain Management</h2>
        <a href="/client/index" class="btn btn-secondary">Back</a>
    </div>

    <div class="card p-4 mb-4">
        <h5>Search for a new domain</h5>
        <form class="row g-3 mt-2">
            <div class="col-md-9">
                <input type="text" class="form-control" placeholder="example.com">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Check Availability</button>
            </div>
        </form>
    </div>

    <div class="card p-4">
        <h5>My Domains</h5>
        <div class="alert alert-info mt-2">You don't have any registered domains yet.</div>
    </div>
</div>
</body>
</html>
