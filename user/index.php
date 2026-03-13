<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$user_id = $_SESSION['user_id'];

$balance_stmt = $db->prepare("SELECT SUM(CASE WHEN type='add' THEN amount ELSE -amount END) as balance FROM credit_transactions WHERE user_id = ?");
$balance_stmt->bind_param("i", $user_id);
$balance_stmt->execute();
$balance_res = $balance_stmt->get_result()->fetch_assoc();
$balance = $balance_res['balance'] ?? 0.00;

$invoices = $db->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$invoices->bind_param("i", $user_id);
$invoices->execute();
$invoices_res = $invoices->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Dashboard - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary-color: #0d6efd; --bg-color: #f4f7f6; }
        body { background: var(--bg-color); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .stat-card { background: #fff; padding: 25px; border-radius: 15px; margin-bottom: 20px; }
        .navbar { background: #fff; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg px-4 mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="#">WHMBiller</a>
        <div class="ms-auto d-flex align-items-center">
            <a href="/user/profile" class="me-3 text-dark text-decoration-none"><i class="bi bi-person-circle"></i> Profile</a>
            <a href="/logout" class="btn btn-sm btn-outline-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="text-muted small mb-2">Available Balance</div>
                <h2 class="fw-bold">₦<?php echo number_format($balance, 2); ?></h2>
                <a href="/user/billing" class="btn btn-sm btn-primary mt-2">Add Funds</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="text-muted small mb-2">Active Services</div>
                <h2 class="fw-bold">0</h2>
                <a href="/user/services" class="btn btn-sm btn-outline-primary mt-2">View Services</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="text-muted small mb-2">Unpaid Invoices</div>
                <h2 class="fw-bold">0</h2>
                <a href="/user/billing" class="btn btn-sm btn-outline-danger mt-2">Pay Now</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card p-4">
                <h5 class="mb-4">Recent Invoices</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($inv = $invoices_res->fetch_assoc()): ?>
                            <tr>
                                <td>#INV-<?php echo $inv['id']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($inv['created_at'])); ?></td>
                                <td>₦<?php echo number_format($inv['amount'], 2); ?></td>
                                <td><span class="badge bg-warning"><?php echo ucfirst($inv['status']); ?></span></td>
                                <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($invoices_res->num_rows == 0): ?>
                            <tr><td colspan="5" class="text-center text-muted">No invoices found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="list-group list-group-flush">
                    <a href="/user/services" class="list-group-item list-group-item-action border-0 px-0">
                        <i class="bi bi-plus-circle me-2 text-primary"></i> Order New Service
                    </a>
                    <a href="/user/domains" class="list-group-item list-group-item-action border-0 px-0">
                        <i class="bi bi-globe me-2 text-primary"></i> Register Domain
                    </a>
                    <a href="/user/support" class="list-group-item list-group-item-action border-0 px-0">
                        <i class="bi bi-headset me-2 text-primary"></i> Open Ticket
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
