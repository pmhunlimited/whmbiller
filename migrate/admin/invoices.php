<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$invoices = $db->query("SELECT i.*, u.username FROM invoices i JOIN users u ON i.user_id = u.id ORDER BY i.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Management - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fc; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-text text-primary"></i> All Invoices</h2>
        <a href="/admin/index" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($inv = $invoices->fetch_assoc()): ?>
                    <tr>
                        <td>#INV-<?php echo $inv['id']; ?></td>
                        <td><?php echo htmlspecialchars($inv['username']); ?></td>
                        <td><?php echo $inv['currency']; ?> <?php echo number_format($inv['amount'], 2); ?></td>
                        <td>
                            <span class="badge <?php
                                echo $inv['status'] === 'paid' ? 'bg-success' :
                                    ($inv['status'] === 'unpaid' ? 'bg-warning' : 'bg-secondary');
                            ?>">
                                <?php echo ucfirst($inv['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $inv['due_date']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($inv['created_at'])); ?></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="#" class="btn btn-sm btn-outline-secondary">Download PDF</a>
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
