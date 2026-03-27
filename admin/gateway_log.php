<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$logs = $db->query("SELECT * FROM tblgatewaylog ORDER BY date DESC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gateway Log - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fc; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gateway Transaction Log</h2>
        <a href="/admin/index" class="btn btn-secondary">Back</a>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Gateway</th>
                        <th>Status</th>
                        <th>Debug Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($l = $logs->fetch_assoc()): ?>
                    <tr>
                        <td class="small"><?php echo $l['date']; ?></td>
                        <td><span class="badge bg-secondary"><?php echo strtoupper($l['gateway']); ?></span></td>
                        <td><span class="badge bg-info"><?php echo $l['status']; ?></span></td>
                        <td><code class="small"><?php echo htmlspecialchars($l['data']); ?></code></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
