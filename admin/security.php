<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_blacklist_ip') {
        $ip = $_POST['ip'];
        $duration = $_POST['duration'] ?? '1 day';
        $block_until = date('Y-m-d H:i:s', strtotime('+' . $duration));
        $stmt = $db->prepare("INSERT INTO tblipblocks (ip_address, status, block_until) VALUES (?, 'blacklist', ?) ON DUPLICATE KEY UPDATE status='blacklist', block_until=?");
        $stmt->bind_param("sss", $ip, $block_until, $block_until);
        $stmt->execute();
        $msg = "IP $ip blacklisted for $duration.";
    } elseif ($action === 'update_country') {
        $code = $_POST['country_code'];
        $status = $_POST['status'];
        $stmt = $db->prepare("UPDATE country_protection SET status = ? WHERE country_code = ?");
        $stmt->bind_param("ss", $status, $code);
        $stmt->execute();
        // For AJAX response
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode(['status' => 'success']);
            exit;
        }
    } elseif ($action === 'suspend_user') {
        $username = $_POST['username'];
        $stmt = $db->prepare("UPDATE tblclients SET status = 'Inactive' WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $msg = "User $username suspended.";
    }
}

$ips = $db->query("SELECT * FROM tblipblocks ORDER BY last_attempt DESC");
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
        body { background: #f8f9fc; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
        .king-icon { color: #28a745; margin-left: 5px; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-shield-lock text-primary"></i> Security Management</h2>
        <a href="/admin/index" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

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
                    <div class="mb-3">
                        <label class="form-label">Default Block Duration</label>
                        <select name="settings[bf_block_duration]" class="form-select">
                            <option value="1 day">One Day</option>
                            <option value="1 week">One Week</option>
                            <option value="1 month">One Month</option>
                            <option value="1 year">One Year</option>
                        </select>
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
                        <input type="text" name="ip" class="form-control form-control-sm me-2" placeholder="IP Address" required>
                        <select name="duration" class="form-select form-select-sm me-2" style="width: auto;">
                            <option value="1 day">1 Day</option>
                            <option value="1 week">One Week</option>
                            <option value="1 month">One Month</option>
                            <option value="1 year">One Year</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-danger text-nowrap">Block IP</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
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
                                <td>
                                    <?php echo htmlspecialchars($ip['ip_address']); ?>
                                    <?php if($ip['status'] === 'whitelist' || $ip['successful_sessions'] >= 5): ?>
                                        <i class="bi bi-award-fill king-icon" title="Trusted IP"></i>
                                    <?php endif; ?>
                                </td>
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
                                <td class="small text-muted"><?php echo $ip['block_until'] ?: '-'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Country Protection -->
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Country Protection</h5>
                    <div style="width: 250px;">
                        <input type="text" id="countrySearch" class="form-control form-control-sm" placeholder="Search country...">
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover" id="countryTable">
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
                                    <select class="form-select form-select-sm country-status" data-code="<?php echo $country['country_code']; ?>">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

document.querySelectorAll('.country-status').forEach(select => {
    select.addEventListener('change', function() {
        const code = this.getAttribute('data-code');
        const status = this.value;
        const formData = new FormData();
        formData.append('action', 'update_country');
        formData.append('country_code', code);
        formData.append('status', status);

        fetch('/admin/security', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                this.classList.add('is-valid');
                setTimeout(() => this.classList.remove('is-valid'), 1000);
            }
        });
    });
});
</script>
</body>
</html>
