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
    <title>Support Tickets - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Support Tickets</h2>
        <a href="/client/index" class="btn btn-secondary">Back</a>
    </div>

    <div class="card p-4 mb-4 text-center">
        <h5>Need help?</h5>
        <p>Our support team is available 24/7 to assist you.</p>
        <button class="btn btn-primary px-5">Open New Ticket</button>
    </div>

    <div class="card p-4">
        <h5>My Tickets</h5>
        <div class="alert alert-light border mt-2">No active tickets found.</div>
    </div>
</div>
</body>
</html>
