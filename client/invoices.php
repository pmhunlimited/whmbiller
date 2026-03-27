<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$user_id = $_SESSION['user_id'];

$invoices = $db->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY created_at DESC");
$invoices->bind_param("i", $user_id);
$invoices->execute();
$invoices_res = $invoices->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Invoices - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-text text-primary"></i> My Invoices</h2>
        <a href="/client/index" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($inv = $invoices_res->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#INV-<?php echo $inv['id']; ?></strong></td>
                        <td><?php echo date('M j, Y', strtotime($inv['created_at'])); ?></td>
                        <td><?php echo date('M j, Y', strtotime($inv['due_date'])); ?></td>
                        <td>₦<?php echo number_format($inv['amount'], 2); ?></td>
                        <td>
                            <?php if($inv['status'] === 'paid'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($inv['status'] === 'unpaid'): ?>
                                <a href="/client/view_invoice?id=<?php echo $inv['id']; ?>" class="btn btn-sm btn-primary">Pay Now</a>
                            <?php else: ?>
                                <a href="/client/view_invoice?id=<?php echo $inv['id']; ?>" class="btn btn-sm btn-outline-secondary">View</a>
                            <?php endif; ?>
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
