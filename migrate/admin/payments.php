<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/billing.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$billing = new Billing();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'approve_payment') {
        $invoice_id = (int)$_POST['invoice_id'];
        $billing->markAsPaid($invoice_id);
        // Also add funds to user if it was a credit purchase
        $stmt = $db->prepare("SELECT user_id, amount FROM invoices WHERE id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $inv = $stmt->get_result()->fetch_assoc();

        $stmt = $db->prepare("INSERT INTO credit_transactions (user_id, amount, type, description) VALUES (?, ?, 'add', 'Manual payment approval')");
        $stmt->bind_param("id", $inv['user_id'], $inv['amount']);
        $stmt->execute();
    }
}

$pending_invoices = $db->query("SELECT i.*, u.username FROM invoices i JOIN users u ON i.user_id = u.id WHERE i.status = 'unpaid' ORDER BY i.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manual Payment Approval - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fc; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manual Payment Approval</h2>
        <a href="/admin/index" class="btn btn-secondary">Back</a>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($inv = $pending_invoices->fetch_assoc()): ?>
                    <tr>
                        <td>#INV-<?php echo $inv['id']; ?></td>
                        <td><?php echo htmlspecialchars($inv['username']); ?></td>
                        <td><?php echo $inv['currency']; ?> <?php echo number_format($inv['amount'], 2); ?></td>
                        <td><?php echo $inv['created_at']; ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="invoice_id" value="<?php echo $inv['id']; ?>">
                                <input type="hidden" name="action" value="approve_payment">
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
