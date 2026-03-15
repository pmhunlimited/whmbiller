<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /admin/authorize');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)$_POST['product_id'];
    $settings = json_encode($_POST['module']);
    $stmt = $db->prepare("UPDATE products SET module_settings = ? WHERE id = ?");
    $stmt->bind_param("si", $settings, $pid);
    $stmt->execute();
    $msg = "Module settings updated!";
}

$products = $db->query("SELECT * FROM products WHERE type IN ('hosting', 'reseller')");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>cPanel Module Configuration - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fc; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>cPanel Extended Configuration</h2>
        <a href="/admin/products" class="btn btn-secondary">Back to Products</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <?php while($prod = $products->fetch_assoc()):
            $m = json_decode($prod['module_settings'], true) ?? [];
        ?>
        <div class="col-md-6 mb-4">
            <div class="card p-4">
                <h5 class="fw-bold"><?php echo htmlspecialchars($prod['name']); ?></h5>
                <form method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">WHM Package Name</label>
                        <input type="text" name="module[package]" class="form-control" value="<?php echo $m['package'] ?? ''; ?>" placeholder="e.g. silver_plan">
                    </div>
                    <hr>
                    <h6>CloudLinux Resource Limits</h6>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="small">CPU (%)</label>
                            <input type="number" name="module[cl_cpu]" class="form-control form-control-sm" value="<?php echo $m['cl_cpu'] ?? '100'; ?>">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small">RAM (MB)</label>
                            <input type="number" name="module[cl_ram]" class="form-control form-control-sm" value="<?php echo $m['cl_ram'] ?? '1024'; ?>">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small">IO (KB/s)</label>
                            <input type="number" name="module[cl_io]" class="form-control form-control-sm" value="<?php echo $m['cl_io'] ?? '1024'; ?>">
                        </div>
                    </div>
                    <hr>
                    <h6>Allowed Client Features</h6>
                    <div class="form-check">
                        <input type="checkbox" name="module[feat_email]" class="form-check-input" <?php if($m['feat_email'] ?? true) echo 'checked'; ?>>
                        <label class="form-check-label small">Email Management</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="module[feat_db]" class="form-check-input" <?php if($m['feat_db'] ?? true) echo 'checked'; ?>>
                        <label class="form-check-label small">Database Management</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="module[feat_wp]" class="form-check-input" <?php if($m['feat_wp'] ?? true) echo 'checked'; ?>>
                        <label class="form-check-label small">WordPress Manager</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary mt-3">Update Product</button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
