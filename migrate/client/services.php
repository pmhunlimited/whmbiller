<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$products = $db->query("SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Services - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f4f7f6; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Services</h2>
        <a href="/client/index" class="btn btn-secondary">Back</a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card p-4 mb-4">
                <h5>Active Services</h5>
                <div class="alert alert-info">You don't have any active services yet.</div>
            </div>

            <h4 class="mb-3">Order New Service</h4>
            <div class="row">
                <?php while($prod = $products->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card p-3 text-center">
                        <h5 class="fw-bold"><?php echo htmlspecialchars($prod['name']); ?></h5>
                        <p class="text-muted small"><?php echo htmlspecialchars($prod['description']); ?></p>
                        <h3 class="text-primary">₦<?php echo number_format($prod['price'], 2); ?></h3>
                        <p class="small text-muted"><?php echo ucfirst($prod['recurring_period']); ?></p>
                        <button class="btn btn-primary w-100">Order Now</button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
