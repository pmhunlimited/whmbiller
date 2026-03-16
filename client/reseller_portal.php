<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'reseller') {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$user_id = $_SESSION['user_id'];

$reseller_stmt = $db->prepare("SELECT * FROM reseller_settings WHERE user_id = ?");
$reseller_stmt->bind_param("i", $user_id);
$reseller_stmt->execute();
$reseller_data = $reseller_stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_markup') {
        $markup = (float)$_POST['markup'];
        $stmt = $db->prepare("UPDATE reseller_settings SET retail_markup = ? WHERE user_id = ?");
        $stmt->bind_param("di", $markup, $user_id);
        $stmt->execute();
        $reseller_data['retail_markup'] = $markup;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reseller Portal - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <span class="navbar-brand fw-bold">Reseller Portal</span>
        <div class="ms-auto text-white">
            Welcome, <?php echo $_SESSION['username']; ?> | <a href="/logout" class="text-white">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card p-4 mb-4">
                <h5>My Profit Settings</h5>
                <p class="text-muted">Wholesale Discount: <?php echo number_format($reseller_data['wholesale_discount'], 2); ?>%</p>
                <form method="POST">
                    <input type="hidden" name="action" value="update_markup">
                    <div class="mb-3">
                        <label class="form-label">My Retail Markup (%)</label>
                        <input type="number" step="0.01" name="markup" class="form-control" value="<?php echo $reseller_data['retail_markup']; ?>">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Save Markup</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-4">
                <h5>Tier 2 Customer Management</h5>
                <p>Manage your own clients and their services here.</p>
                <div class="alert alert-info">Reseller isolation enabled. You only see your sub-clients.</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Services</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="3" class="text-center text-muted">No sub-clients found.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
