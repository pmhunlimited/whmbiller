<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'submit_payment') {
        $amount = (float)$_POST['amount'];
        $method = $_POST['method'];
        $ref = $_POST['reference'];
        $details = $_POST['details'];

        $stmt = $db->prepare("INSERT INTO payment_notifications (user_id, amount, method, reference, details) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idsss", $user_id, $amount, $method, $ref, $details);
        $stmt->execute();
        $msg = "Payment notification submitted! Admin will review and approve soon.";
    }
}

$balance_stmt = $db->prepare("SELECT SUM(CASE WHEN type='add' THEN amount ELSE -amount END) as balance FROM credit_transactions WHERE user_id = ?");
$balance_stmt->bind_param("i", $user_id);
$balance_stmt->execute();
$balance_res = $balance_stmt->get_result()->fetch_assoc();
$balance = $balance_res['balance'] ?? 0.00;

$notifs = $db->prepare("SELECT * FROM payment_notifications WHERE user_id = ? ORDER BY created_at DESC");
$notifs->bind_param("i", $user_id);
$notifs->execute();
$notifs_res = $notifs->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manual Deposit - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manual Deposit & History</h2>
        <a href="/client/billing" class="btn btn-secondary">Back</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-5">
            <div class="card p-4 mb-4">
                <h5>Submit Payment Notification</h5>
                <p class="text-muted small">After making a Bank Transfer or Crypto payment, please fill this form.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="submit_payment">
                    <div class="mb-3">
                        <label class="form-label">Amount (NGN)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="method" class="form-select">
                            <option value="bank">Bank Transfer</option>
                            <option value="crypto">Cryptocurrency</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction Reference / Hash</label>
                        <input type="text" name="reference" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Details</label>
                        <textarea name="details" class="form-control" placeholder="Bank name, Sender name, etc."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit Notification</button>
                </form>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card p-4">
                <h5>Recent Notifications</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($n = $notifs_res->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($n['created_at'])); ?></td>
                                <td><?php echo ucfirst($n['method']); ?></td>
                                <td>₦<?php echo number_format($n['amount'], 2); ?></td>
                                <td>
                                    <?php if($n['status'] === 'pending'): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php elseif($n['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php endif; ?>
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
