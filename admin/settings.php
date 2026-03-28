<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
    }
    $msg = "Settings saved successfully!";
}

$res = $db->query("SELECT * FROM settings");
$settings = [];
while($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>General Settings - WHMBiller</title>
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
        <h2><i class="bi bi-gear text-primary"></i> System Settings</h2>
        <a href="/admin/index" class="btn btn-secondary">Back</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card p-4 h-100">
                    <h5>SMTP Configuration</h5>
                    <div class="mb-3">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="settings[smtp_host]" class="form-control" value="<?php echo $settings['smtp_host'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" name="settings[smtp_port]" class="form-control" value="<?php echo $settings['smtp_port'] ?? '587'; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SMTP User</label>
                        <input type="text" name="settings[smtp_user]" class="form-control" value="<?php echo $settings['smtp_user'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="settings[smtp_pass]" class="form-control" value="<?php echo $settings['smtp_pass'] ?? ''; ?>">
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card p-4 h-100">
                    <h5>Payment & Currency</h5>
                    <div class="mb-3">
                        <label class="form-label">Paystack Secret Key</label>
                        <input type="password" name="settings[paystack_secret_key]" class="form-control" value="<?php echo $settings['paystack_secret_key'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Base Currency</label>
                        <input type="text" name="settings[base_currency]" class="form-control" value="<?php echo $settings['base_currency'] ?? 'NGN'; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Exchange Rate (1 USD = ? Base)</label>
                        <input type="number" step="0.01" name="settings[exchange_rate]" class="form-control" value="<?php echo $settings['exchange_rate'] ?? '1500'; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reseller Upgrade Fee (Base)</label>
                        <input type="number" step="0.01" name="settings[reseller_upgrade_fee]" class="form-control" value="<?php echo $settings['reseller_upgrade_fee'] ?? '5000'; ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary px-5 btn-lg">Save All Settings</button>
        </div>
    </form>
</div>
</body>
</html>
