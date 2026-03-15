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
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>System Settings</h2>
        <a href="/admin/index" class="btn btn-secondary">Back</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="card p-4 mb-4">
            <h5>SMTP Configuration</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="settings[smtp_host]" class="form-control" value="<?php echo $settings['smtp_host'] ?? ''; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">SMTP Port</label>
                    <input type="number" name="settings[smtp_port]" class="form-control" value="<?php echo $settings['smtp_port'] ?? '587'; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP User</label>
                    <input type="text" name="settings[smtp_user]" class="form-control" value="<?php echo $settings['smtp_user'] ?? ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP Password</label>
                    <input type="password" name="settings[smtp_pass]" class="form-control" value="<?php echo $settings['smtp_pass'] ?? ''; ?>">
                </div>
            </div>
        </div>

        <div class="card p-4 mb-4">
            <h5>Payment Gateway (Paystack)</h5>
            <div class="mb-3">
                <label class="form-label">Secret Key</label>
                <input type="password" name="settings[paystack_secret_key]" class="form-control" value="<?php echo $settings['paystack_secret_key'] ?? ''; ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary px-5">Save All Settings</button>
    </form>
</div>
</body>
</html>
