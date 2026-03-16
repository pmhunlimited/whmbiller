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
    if (isset($_POST['action']) && $_POST['action'] === 'approve_notification') {
        $notif_id = (int)$_POST['notif_id'];

        $stmt = $db->prepare("SELECT user_id, amount FROM payment_notifications WHERE id = ?");
        $stmt->bind_param("i", $notif_id);
        $stmt->execute();
        $notif = $stmt->get_result()->fetch_assoc();

        if ($notif) {
            // Update status
            $stmt = $db->prepare("UPDATE payment_notifications SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $notif_id);
            $stmt->execute();

            // Add funds to user wallet
            $stmt = $db->prepare("INSERT INTO credit_transactions (user_id, amount, type, description) VALUES (?, ?, 'add', 'Approved Manual Deposit')");
            $stmt->bind_param("id", $notif['user_id'], $notif['amount']);
            $stmt->execute();

            $msg = "Payment notification approved and funds credited.";
        }
    }
}

$pending_notifs = $db->query("SELECT n.*, u.username FROM payment_notifications n JOIN users u ON n.user_id = u.id WHERE n.status = 'pending' ORDER BY n.created_at DESC");
$pending_invoices = $db->query("SELECT i.*, u.username FROM invoices i JOIN users u ON i.user_id = u.id WHERE i.status = 'unpaid' ORDER BY i.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Management - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fc; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manual Payment & Invoice Management</h2>
        <a href="/admin/index" class="btn btn-secondary">Back</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card p-4">
                <h5>Pending Payment Notifications (Manual Deposits)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Details</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($n = $pending_notifs->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($n['username']); ?></strong></td>
                                <td><span class="badge bg-info"><?php echo strtoupper($n['method']); ?></span></td>
                                <td>₦<?php echo number_format($n['amount'], 2); ?></td>
                                <td><code><?php echo htmlspecialchars($n['reference']); ?></code></td>
                                <td class="small"><?php echo htmlspecialchars($n['details']); ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
                                        <input type="hidden" name="action" value="approve_notification">
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($pending_notifs->num_rows == 0): ?>
                            <tr><td colspan="6" class="text-center text-muted">No pending notifications.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card p-4">
                <h5>Unpaid Invoices</h5>
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
                                    <button class="btn btn-sm btn-outline-primary">Mark Paid</button>
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
</body>
</html>
