<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../modules/provisioning/wordpress.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login');
    exit;
}

// In a real scenario, we'd fetch the server details for the user's service
// For now, we'll use a placeholder logic
$wp = new WordPressConsole('whm.yourserver.com', 'cpanel_user', 'api_token');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['installation_id'] ?? '';

    switch ($action) {
        case 'login':
            $url = $wp->getOneClickLoginUrl($id);
            if ($url) {
                header("Location: $url");
                exit;
            }
            break;
        case 'update':
            $wp->updateCore($id);
            $msg = "Core update initiated.";
            break;
        case 'maintenance':
            $status = $_POST['status'] === 'on';
            $wp->toggleMaintenanceMode($id, $status);
            $msg = "Maintenance mode " . ($status ? "enabled" : "disabled") . ".";
            break;
    }
}

// Mock installations for display if API fails
$installations = [
    [
        'id' => '1',
        'url' => 'https://mysite.com',
        'version' => '6.4.2',
        'update_available' => true,
        'status' => 'active',
        'maintenance' => false
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WordPress Management - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .btn-wp { border-radius: 8px; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-wordpress text-primary"></i> WordPress Management Console</h2>
        <a href="/client/index" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-info"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card p-4">
                <h5>My Installations</h5>
                <div class="table-responsive">
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
                            <?php foreach ($installations as $inst): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $inst['url']; ?></td>
                                <td>
                                    <?php echo $inst['version']; ?>
                                    <?php if($inst['update_available']): ?>
                                        <span class="badge bg-warning text-dark ms-1">Update Available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?php echo ucfirst($inst['status']); ?></span>
                                    <?php if($inst['maintenance']): ?>
                                        <span class="badge bg-danger ms-1">Maintenance</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="login">
                                            <input type="hidden" name="installation_id" value="<?php echo $inst['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary btn-wp me-1">
                                                <i class="bi bi-box-arrow-in-right"></i> Login
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="installation_id" value="<?php echo $inst['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary btn-wp me-1">
                                                <i class="bi bi-arrow-repeat"></i> Update
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="maintenance">
                                            <input type="hidden" name="installation_id" value="<?php echo $inst['id']; ?>">
                                            <input type="hidden" name="status" value="<?php echo $inst['maintenance'] ? 'off' : 'on'; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning btn-wp me-1">
                                                <i class="bi bi-tools"></i> Maintenance
                                            </button>
                                        </form>
                                        <a href="/client/wordpress_plugins?id=<?php echo $inst['id']; ?>" class="btn btn-sm btn-outline-info btn-wp">
                                            <i class="bi bi-plug"></i> Plugins
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
