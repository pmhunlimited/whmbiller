<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['gateways'] as $gateway => $settings) {
        foreach ($settings as $key => $val) {
            $stmt = $db->prepare("INSERT INTO tblpaymentgateways (gateway, setting, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = ?");
            $stmt->bind_param("ssss", $gateway, $key, $val, $val);
            $stmt->execute();
        }
    }
    $msg = "Gateway settings updated successfully!";
}

$res = $db->query("SELECT * FROM tblpaymentgateways");
$gateways = [];
while($row = $res->fetch_assoc()) {
    $gateways[$row['gateway']][$row['setting']] = $row['value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Gateway Config - WHMBiller</title>
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
        <h2><i class="bi bi-wallet2 text-primary"></i> Payment Gateways</h2>
        <a href="/admin/index" class="btn btn-secondary">Back</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row">
            <!-- Payhub -->
            <div class="col-md-6 mb-4">
                <div class="card p-4">
                    <h5>Payhub (Custom Gateway)</h5>
                    <div class="mb-3">
                        <label class="form-label">Merchant ID</label>
                        <input type="text" name="gateways[payhub][merchant_id]" class="form-control" value="<?php echo $gateways['payhub']['merchant_id'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Key</label>
                        <input type="password" name="gateways[payhub][api_key]" class="form-control" value="<?php echo $gateways['payhub']['api_key'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Secret Hash</label>
                        <input type="text" name="gateways[payhub][secret_hash]" class="form-control" value="<?php echo $gateways['payhub']['secret_hash'] ?? ''; ?>">
                    </div>
                    <div class="form-text small text-muted">Callback URL: <?php echo "http://" . $_SERVER['HTTP_HOST'] . "/modules/gateways/callback/payhub.php"; ?></div>
                </div>
            </div>

            <!-- Paystack -->
            <div class="col-md-6 mb-4">
                <div class="card p-4">
                    <h5>Paystack (Standard)</h5>
                    <div class="mb-3">
                        <label class="form-label">Public Key</label>
                        <input type="text" name="gateways[paystack][public_key]" class="form-control" value="<?php echo $gateways['paystack']['public_key'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Secret Key</label>
                        <input type="password" name="gateways[paystack][secret_key]" class="form-control" value="<?php echo $gateways['paystack']['secret_key'] ?? ''; ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg px-5">Save Gateway Settings</button>
        </div>
    </form>
</div>
</body>
</html>
