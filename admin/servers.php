<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../modules/provisioning/whm.php';
require_once __DIR__ . '/../modules/provisioning/nocix.php';
require_once __DIR__ . '/../modules/provisioning/interserver.php';
require_once __DIR__ . '/../modules/registrars/namecheap.php';
require_once __DIR__ . '/../modules/registrars/upperlink.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$test_result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_server') {
            $name = $_POST['name'];
            $hostname = $_POST['hostname'];
            $username = $_POST['username'];
            $token = $_POST['api_token'];
            $type = $_POST['type'];

            $stmt = $db->prepare("INSERT INTO servers (name, hostname, username, api_token, type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $hostname, $username, $token, $type);
            $stmt->execute();
        } elseif ($_POST['action'] === 'test_connection') {
            $server_id = (int)$_POST['server_id'];
            $stmt = $db->prepare("SELECT * FROM servers WHERE id = ?");
            $stmt->bind_param("i", $server_id);
            $stmt->execute();
            $s = $stmt->get_result()->fetch_assoc();

            try {
                switch($s['type']) {
                    case 'whm':
                        $whm = new WHMModule($s['hostname'], $s['username'], $s['api_token']);
                        // applist is a lightweight call to test
                        $res = $whm->getSessionToken($s['username']);
                        $test_result = (isset($res['metadata']['result']) && $res['metadata']['result'] == 1)
                            ? "Success: Connected to WHM."
                            : "Error: " . ($res['metadata']['reason'] ?? 'Unknown WHM error');
                        break;
                    case 'namecheap':
                        $nc = new NamecheapModule($s['username'], $s['api_token'], $_SERVER['REMOTE_ADDR']);
                        $res = $nc->getNameservers('example.com'); // Mock check
                        $test_result = "Connection initiated. Check Namecheap API logs.";
                        break;
                    default:
                        $test_result = "Test not implemented for " . $s['type'] . " yet, but API is configured.";
                }
            } catch (Exception $e) {
                $test_result = "Exception: " . $e->getMessage();
            }
        }
    }
}

$servers = $db->query("SELECT * FROM servers");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Server Management - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fc; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-hdd-network text-primary"></i> WHM & API Servers</h2>
        <a href="/admin/index" class="btn btn-secondary">Back</a>
    </div>

    <?php if($test_result): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?php echo htmlspecialchars($test_result); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-4 mb-4">
                <h5>Add New Server / API</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="add_server">
                    <div class="mb-3">
                        <label class="form-label">Server Name (Internal)</label>
                        <input type="text" name="name" class="form-control" placeholder="Production Server 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="whm">WHM/cPanel</option>
                            <option value="nocix">Nocix API</option>
                            <option value="interserver">Interserver API</option>
                            <option value="namecheap">Namecheap API</option>
                            <option value="upperlink">Upperlink API</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hostname / Endpoint</label>
                        <input type="text" name="hostname" class="form-control" placeholder="server.example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Username / Email</label>
                        <input type="text" name="username" class="form-control" value="root">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Token / Key</label>
                        <textarea name="api_token" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Server</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-4">
                <h5>Configured Servers</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Hostname</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($s = $servers->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                                <td><span class="badge bg-info"><?php echo strtoupper($s['type']); ?></span></td>
                                <td><?php echo htmlspecialchars($s['hostname']); ?></td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="test_connection">
                                        <input type="hidden" name="server_id" value="<?php echo $s['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Test Connection</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($servers->num_rows == 0): ?>
                            <tr><td colspan="5" class="text-center text-muted">No servers configured.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
