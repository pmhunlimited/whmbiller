<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_reseller') {
        $user_id = (int)$_POST['user_id'];
        $discount = (float)$_POST['discount'];
        $stmt = $db->prepare("INSERT INTO reseller_settings (user_id, wholesale_discount) VALUES (?, ?) ON DUPLICATE KEY UPDATE wholesale_discount = ?");
        $stmt->bind_param("idd", $user_id, $discount, $discount);
        $stmt->execute();
    }
}

$resellers = $db->query("SELECT u.id, u.username, u.email, rs.wholesale_discount FROM users u LEFT JOIN reseller_settings rs ON u.id = rs.user_id WHERE u.role = 'reseller'");
$clients = $db->query("SELECT id, username FROM users WHERE role = 'client'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reseller Management - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fc; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Reseller Wholesale Pricing</h2>
        <a href="/admin/index" class="btn btn-secondary">Back</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-4 mb-4">
                <h5>Assign Reseller Discount</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="update_reseller">
                    <div class="mb-3">
                        <label class="form-label">Select Client</label>
                        <select name="user_id" class="form-select">
                            <?php while($c = $clients->fetch_assoc()): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['username']); ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div class="form-text">Changing a client to reseller happens here (simplified for demo).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wholesale Discount (%)</label>
                        <input type="number" step="0.01" name="discount" class="form-control" placeholder="e.g. 10.00" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Set Discount</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-4">
                <h5>Current Resellers</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Wholesale Discount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r = $resellers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['username']); ?></td>
                            <td><?php echo number_format($r['wholesale_discount'], 2); ?>%</td>
                            <td><a href="#" class="btn btn-sm btn-outline-primary">Edit</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
