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
    <title>WordPress Management - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">WordPress Management Console</h2>
    <div class="row">
        <div class="col-md-12">
            <div class="card p-4">
                <h5>My Installations</h5>
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Site URL</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>https://mywordpress.com</td>
                            <td>6.4.2 <span class="badge bg-warning">Update Available</span></td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary">One-Click Login</button>
                                <button class="btn btn-sm btn-outline-secondary">Update Core</button>
                                <button class="btn btn-sm btn-outline-info">Manage Plugins</button>
                                <button class="btn btn-sm btn-outline-warning">Maintenance Mode</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
