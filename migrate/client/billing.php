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
    if (isset($_POST['action']) && $_POST['action'] === 'add_funds') {
        $amount = (float)$_POST['amount'];
        $stmt = $db->prepare("INSERT INTO credit_transactions (user_id, amount, type, description) VALUES (?, ?, 'add', 'Added funds via portal')");
        $stmt->bind_param("id", $user_id, $amount);
        $stmt->execute();
        $msg = "Funds successfully added!";
    }
}

$balance_stmt = $db->prepare("SELECT SUM(CASE WHEN type='add' THEN amount ELSE -amount END) as balance FROM credit_transactions WHERE user_id = ?");
$balance_stmt->bind_param("i", $user_id);
$balance_stmt->execute();
$balance_res = $balance_stmt->get_result()->fetch_assoc();
$balance = $balance_res['balance'] ?? 0.00;

$txs = $db->prepare("SELECT * FROM credit_transactions WHERE user_id = ? ORDER BY created_at DESC");
$txs->bind_param("i", $user_id);
$txs->execute();
$txs_res = $txs->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing & Credits - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Billing & Credits</h2>
        <a href="/client/index" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-4 mb-4">
                <h5>Current Balance</h5>
                <h2 class="fw-bold text-primary">₦<?php echo number_format($balance, 2); ?></h2>
                <hr>
                <h6>Add Funds</h6>
                <form method="POST">
                    <input type="hidden" name="action" value="add_funds">
                    <div class="mb-3">
                        <label class="form-label">Amount (NGN)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required min="100">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Deposit</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-4">
                <h5>Transaction History</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($tx = $txs_res->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M j, Y H:i', strtotime($tx['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($tx['description']); ?></td>
                                <td>
                                    <span class="badge <?php echo $tx['type'] === 'add' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ucfirst($tx['type']); ?>
                                    </span>
                                </td>
                                <td>₦<?php echo number_format($tx['amount'], 2); ?></td>
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
