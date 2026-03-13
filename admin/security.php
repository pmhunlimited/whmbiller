<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_blacklist_ip') {
        $ip = $_POST['ip'];
        $stmt = $db->prepare("INSERT INTO ip_protection (ip_address, status, block_until) VALUES (?, 'blacklist', DATE_ADD(NOW(), INTERVAL 1 YEAR)) ON DUPLICATE KEY UPDATE status='blacklist', block_until=DATE_ADD(NOW(), INTERVAL 1 YEAR)");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
    }
}

$ips = $db->query("SELECT * FROM ip_protection ORDER BY last_attempt DESC");
$countries = $db->query("SELECT * FROM country_protection ORDER BY country_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Management - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fc; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-shield-lock text-primary"></i> Security Management</h2>
        <a href="/admin/index" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="row">
        <!-- Brute Force Settings -->
        <div class="col-md-4 mb-4">
            <div class="card p-4">
                <h5>Brute Force Settings</h5>
                <form method="POST" action="/admin/settings">
                    <div class="mb-3">
                        <label class="form-label">Max Failures (Account)</label>
                        <input type="number" name="settings[bf_max_user_failures]" class="form-control" value="5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Failures (IP)</label>
                        <input type="number" name="settings[bf_max_ip_failures]" class="form-control" value="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Protection Period (Mins)</label>
                        <input type="number" name="settings[bf_period]" class="form-control" value="15">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="settings[bf_lock_admin]" class="form-check-input" value="1">
                        <label class="form-check-label">Lock "admin" user</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Settings</button>
                </form>
            </div>
        </div>

        <!-- IP Protection -->
        <div class="col-md-8">
            <div class="card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>IP Protection & Blacklist</h5>
                    <form class="d-flex" method="POST">
                        <input type="hidden" name="action" value="add_blacklist_ip">
                        <input type="text" name="ip" class="form-control form-control-sm me-2" placeholder="Add IP to blacklist">
                        <button type="submit" class="btn btn-sm btn-danger">Block</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>Failures</th>
                                <th>Sessions</th>
                                <th>Blocked Until</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($ip = $ips->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ip['ip_address']); ?></td>
                                <td>
                                    <?php if($ip['status'] === 'whitelist'): ?>
                                        <span class="badge bg-success">Whitelisted</span>
                                    <?php elseif($ip['status'] === 'blacklist'): ?>
                                        <span class="badge bg-danger">Blacklisted</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tracked</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $ip['failed_attempts']; ?></td>
                                <td><?php echo $ip['successful_sessions']; ?></td>
                                <td><?php echo $ip['block_until'] ?? '-'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Country Protection -->
            <div class="card p-4">
                <h5>Country Protection</h5>
                <div class="mb-3">
                    <input type="text" id="countrySearch" class="form-control" placeholder="Search country...">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm" id="countryTable">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($country = $countries->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($country['country_name']); ?></td>
                                <td>
                                    <select class="form-select form-select-sm">
                                        <option value="not_specified" <?php echo $country['status'] === 'not_specified' ? 'selected' : ''; ?>>Not Specified</option>
                                        <option value="whitelisted" <?php echo $country['status'] === 'whitelisted' ? 'selected' : ''; ?>>Whitelisted</option>
                                        <option value="blacklisted" <?php echo $country['status'] === 'blacklisted' ? 'selected' : ''; ?>>Blacklisted</option>
                                    </select>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('countrySearch').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let trs = document.getElementById('countryTable').getElementsByTagName('tr');
    for (let i = 1; i < trs.length; i++) {
        let td = trs[i].getElementsByTagName('td')[0];
        if (td) {
            let text = td.textContent || td.innerText;
            trs[i].style.display = text.toUpperCase().indexOf(filter) > -1 ? "" : "none";
        }
    }
});
</script>
</body>
</html>
