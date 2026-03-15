<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$user_id = $_SESSION['user_id'];
$sid = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT s.*, p.name as product_name, p.module_settings, srv.hostname FROM user_services s JOIN products p ON s.product_id = p.id JOIN servers srv ON s.server_id = srv.id WHERE s.id = ? AND s.user_id = ?");
$stmt->bind_param("ii", $sid, $user_id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();

if (!$service) {
    die("Service not found.");
}

$m = json_decode($service['module_settings'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Service - <?php echo htmlspecialchars($service['domain']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f4f7f6; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .cpanel-icon { text-align: center; padding: 20px; border-radius: 12px; background: #fff; border: 1px solid #eee; transition: all 0.2s; cursor: pointer; color: #333; text-decoration: none; display: block; }
        .cpanel-icon:hover { border-color: #ff6c2c; color: #ff6c2c; box-shadow: 0 5px 15px rgba(255,108,44,0.1); }
        .cpanel-icon i { font-size: 2rem; display: block; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-gear-wide-connected text-primary"></i> Manage Service</h2>
        <a href="/client/index" class="btn btn-secondary">Back</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1"><?php echo htmlspecialchars($service['product_name']); ?></h4>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($service['domain']); ?> (<?php echo $service['hostname']; ?>)</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-success py-2 px-3">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Icons -->
        <div class="col-md-9">
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <a href="#" class="cpanel-icon"><i class="bi bi-envelope"></i> Email Accounts</a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="#" class="cpanel-icon"><i class="bi bi-database"></i> MySQL Databases</a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="#" class="cpanel-icon"><i class="bi bi-folder2-open"></i> File Manager</a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="#" class="cpanel-icon"><i class="bi bi-globe"></i> Subdomains</a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="#" class="cpanel-icon"><i class="bi bi-shield-lock"></i> SSL/TLS Status</a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="#" class="cpanel-icon"><i class="bi bi-wordpress"></i> WordPress Manager</a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="#" class="cpanel-icon"><i class="bi bi-bar-chart"></i> AWStats</a>
                </div>
                <div class="col-md-3 col-6">
                    <a href="#" class="cpanel-icon"><i class="bi bi-cpu"></i> Resource Usage</a>
                </div>
            </div>

            <div class="card p-4 mt-4">
                <h5>Application Auto-Installer (Softaculous)</h5>
                <p class="text-muted small">Install popular applications like WordPress, Joomla, and PrestaShop with one click.</p>
                <div class="row text-center mt-3">
                    <div class="col-md-2 col-4">
                        <img src="https://www.softaculous.com/images/wp_logo.png" width="40" alt="WP"><br><span class="small">WordPress</span>
                    </div>
                    <div class="col-md-2 col-4">
                        <img src="https://www.softaculous.com/images/joomla_logo.png" width="40" alt="Joomla"><br><span class="small">Joomla</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="col-md-3">
            <div class="card p-4">
                <h6 class="fw-bold mb-3">One-Click Login</h6>
                <button class="btn btn-outline-primary w-100 mb-2">Log in to cPanel</button>
                <button class="btn btn-outline-secondary w-100 mb-2">Webmail</button>
                <button class="btn btn-outline-info w-100 mb-2">phpMyAdmin</button>
                <hr>
                <h6 class="fw-bold mb-3">Service Actions</h6>
                <button class="btn btn-warning btn-sm w-100 mb-2">Change Password</button>
                <button class="btn btn-danger btn-sm w-100">Request Termination</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
