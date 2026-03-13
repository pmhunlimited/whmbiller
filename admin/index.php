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
        body { background: var(--bg-color); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background: #fff; border-right: 1px solid #eee; }
        .sidebar .nav-link { color: #333; padding: 12px 20px; border-radius: 0; }
        .sidebar .nav-link.active { color: var(--primary-color); background: #f0f3ff; border-right: 3px solid var(--primary-color); }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
        .stat-card { border-left: 4px solid var(--primary-color); }
        .navbar { background: #fff; border-bottom: 1px solid #eee; }
        .king-icon { color: #ffd700; font-size: 1.2rem; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar px-0">
            <div class="p-3">
                <h4 class="text-primary">WHMBiller</h4>
            </div>
            <div class="nav flex-column mt-3">
                <a class="nav-link active" href="/admin/index"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a class="nav-link" href="/admin/clients"><i class="bi bi-people me-2"></i> Clients</a>
                <a class="nav-link" href="/admin/products"><i class="bi bi-box-seam me-2"></i> Products</a>
                <a class="nav-link" href="/admin/invoices"><i class="bi bi-file-earmark-text me-2"></i> Invoices</a>
                <a class="nav-link" href="/admin/security"><i class="bi bi-shield-check me-2"></i> Security</a>
                <a class="nav-link" href="/admin/settings"><i class="bi bi-gear me-2"></i> Settings</a>
                <a class="nav-link text-danger" href="/logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-4">
            <nav class="navbar navbar-expand-lg px-0 mb-4">
                <div class="container-fluid px-0">
                    <span class="navbar-brand">Admin Dashboard</span>
                    <div class="ms-auto">
                        <span class="me-3">Welcome, <?php echo $_SESSION['username']; ?></span>
                        <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['username']; ?>&background=random" class="rounded-circle" width="35" alt="avatar">
                    </div>
                </div>
            </nav>

            <!-- Stat Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card p-3 mb-3">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Clients</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">124</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 mb-3" style="border-left-color: #1cc88a;">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Services</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">458</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 mb-3" style="border-left-color: #36b9cc;">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending Invoices</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 mb-3" style="border-left-color: #f6c23e;">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Revenue (MTD)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">₦250,000</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card p-4">
                        <h5>Recent Logins & Security</h5>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>IP Address</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                    $logs = $db->query("SELECT l.*, p.status as ip_status, p.successful_sessions FROM login_logs l LEFT JOIN ip_protection p ON l.ip_address = p.ip_address ORDER BY attempt_time DESC LIMIT 10");
                                    while($log = $logs->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['username']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($log['ip_address']); ?>
                                            <?php if ($log['ip_status'] === 'whitelist' || $log['successful_sessions'] >= 5): ?>
                                                <i class="bi bi-award king-icon" title="Whitelisted/Trusted IP"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $log['status'] === 'success' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo ucfirst($log['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $log['attempt_time']; ?></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-secondary">Details</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
