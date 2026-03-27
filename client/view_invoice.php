<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../modules/gateways/payhub.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$invoice_id = (int)$_GET['id'];

$stmt = $db->prepare("SELECT i.*, u.email FROM invoices i JOIN users u ON i.user_id = u.id WHERE i.id = ? AND i.user_id = ?");
$user_id = $_SESSION['user_id'];
$stmt->bind_param("ii", $invoice_id, $user_id);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();

if (!$inv) {
    die("Invoice not found or access denied.");
}

// Fetch Payhub Settings
$res = $db->query("SELECT setting, value FROM tblpaymentgateways WHERE gateway = 'payhub'");
$gateway_params = [];
while($row = $res->fetch_assoc()) {
    $gateway_params[$row['setting']] = $row['value'];
}

$gateway_params['invoiceid'] = $inv['id'];
$gateway_params['amount'] = $inv['amount'];
$gateway_params['currency'] = $inv['currency'];
$gateway_params['clientdetails']['email'] = $inv['email'];
$gateway_params['systemurl'] = "http://" . $_SERVER['HTTP_HOST'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Invoice - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .invoice-card { border-radius: 20px; border: none; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        .invoice-header { background: #0d6efd; color: #fff; padding: 40px; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="invoice-card">
                <div class="invoice-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">#INV-<?php echo $inv['id']; ?></h2>
                        <span class="opacity-75">Issued on <?php echo date('M j, Y', strtotime($inv['created_at'])); ?></span>
                    </div>
                    <div class="text-end">
                        <?php if($inv['status'] === 'paid'): ?>
                            <h3 class="badge bg-success fs-4">PAID</h3>
                        <?php else: ?>
                            <h3 class="badge bg-danger fs-4">UNPAID</h3>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="p-5">
                    <div class="row mb-5">
                        <div class="col-6">
                            <h6 class="text-muted text-uppercase small fw-bold">Billed To</h6>
                            <p class="mb-0 fw-bold"><?php echo $_SESSION['username']; ?></p>
                            <p class="text-muted"><?php echo $inv['email']; ?></p>
                        </div>
                        <div class="col-6 text-end">
                            <h6 class="text-muted text-uppercase small fw-bold">Due Date</h6>
                            <p class="fw-bold"><?php echo date('M j, Y', strtotime($inv['due_date'])); ?></p>
                        </div>
                    </div>

                    <table class="table mb-5">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Service Subscription / Registration</td>
                                <td class="text-end fw-bold">₦<?php echo number_format($inv['amount'], 2); ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row align-items-center">
                        <div class="col-6">
                             <?php if($inv['status'] === 'unpaid'): ?>
                                <h6 class="text-muted small mb-2">Select Payment Method</h6>
                                <?php echo payhub_link($gateway_params); ?>
                             <?php endif; ?>
                        </div>
                        <div class="col-6 text-end">
                            <h4 class="mb-0 text-muted">Total</h4>
                            <h2 class="fw-bold text-primary">₦<?php echo number_format($inv['amount'], 2); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="/client/invoices" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Back to My Invoices</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
