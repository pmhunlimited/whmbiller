<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WHMBiller Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary-color: #4e73df; --bg-color: #f8f9fc; }
        body { background: var(--bg-color); font-family: 'Segoe UI', sans-serif; }
        .sidebar { min-height: 100vh; background: #fff; border-right: 1px solid #eee; }
        .sidebar .nav-link { color: #333; padding: 12px 20px; border-radius: 0; }
        .sidebar .nav-link.active { color: var(--primary-color); background: #f0f3ff; border-right: 3px solid var(--primary-color); }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
        .stat-card { border-left: 4px solid var(--primary-color); }
        .navbar { background: #fff; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar px-0">
            <div class="p-3"><h4 class="text-primary fw-bold">WHMBiller</h4></div>
            <div class="nav flex-column mt-3">
                <a class="nav-link active" href="/admin/index"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a class="nav-link" href="/admin/clients"><i class="bi bi-people me-2"></i> Clients</a>
                <a class="nav-link" href="/admin/products"><i class="bi bi-box-seam me-2"></i> Products</a>
                <a class="nav-link" href="/admin/servers"><i class="bi bi-hdd-network me-2"></i> WHM Servers</a>
                <a class="nav-link" href="/admin/gateways"><i class="bi bi-credit-card me-2"></i> Gateways</a>
                <a class="nav-link" href="/admin/invoices"><i class="bi bi-file-earmark-text me-2"></i> Invoices</a>
                <a class="nav-link" href="/admin/payments"><i class="bi bi-cash-stack me-2"></i> Payments</a>
                <a class="nav-link" href="/admin/gateway_log"><i class="bi bi-list-check me-2"></i> Gateway Log</a>
                <a class="nav-link" href="/admin/security"><i class="bi bi-shield-check me-2"></i> Security</a>
                <a class="nav-link" href="/admin/settings"><i class="bi bi-gear me-2"></i> Settings</a>
                <a class="nav-link text-danger mt-4" href="/logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-4">
            <nav class="navbar navbar-expand-lg px-0 mb-4">
                <div class="container-fluid px-0">
                    <span class="navbar-brand">Admin Dashboard</span>
                    <div class="ms-auto d-flex align-items-center">
                        <span class="me-3 small">Admin: <strong><?php echo $_SESSION['username']; ?></strong></span>
                        <img src="https://ui-avatars.com/api/?name=Admin&background=4e73df&color=fff" class="rounded-circle" width="35" alt="avatar">
                    </div>
                </div>
            </nav>

            <!-- Stat Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card p-3 mb-3">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1 small">Total Clients</div>
                        <div class="h5 mb-0 font-weight-bold">124</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 mb-3" style="border-left-color: #1cc88a;">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1 small">Active Services</div>
                        <div class="h5 mb-0 font-weight-bold">458</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 mb-3" style="border-left-color: #36b9cc;">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1 small">Unpaid Invoices</div>
                        <div class="h5 mb-0 font-weight-bold">12</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 mb-3" style="border-left-color: #f6c23e;">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1 small">MTD Revenue</div>
                        <div class="h5 mb-0 font-weight-bold">₦250,000</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card p-4 mb-4">
                        <h5 class="mb-4">Recent Activity Log</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td class="small text-muted">2 mins ago</td><td>Client <strong>john_doe</strong> opened ticket #1023</td></tr>
                                    <tr><td class="small text-muted">15 mins ago</td><td>Invoice #INV-452 paid via <strong>Payhub</strong></td></tr>
                                    <tr><td class="small text-muted">1 hour ago</td><td>New server <strong>US-EAST-1</strong> added</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4">
                        <h5>System Status</h5>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between">Cron Status <span class="text-success">OK</span></li>
                            <li class="list-group-item d-flex justify-content-between">PHP Version <span>8.3.6</span></li>
                            <li class="list-group-item d-flex justify-content-between">MySQL Version <span>10.6</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
