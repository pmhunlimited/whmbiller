<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_product') {
        $name = $_POST['name'];
        $desc = $_POST['description'];
        $price = $_POST['price'];
        $period = $_POST['period'];
        $type = $_POST['type'];

        $stmt = $db->prepare("INSERT INTO products (name, description, price, recurring_period, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $name, $desc, $price, $period, $type);
        $stmt->execute();
    }
}

$products = $db->query("SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fc; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Product & Service Management</h2>
        <a href="/admin/index" class="btn btn-secondary">Back</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-4">
                <h5>Add New Product</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="add_product">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (NGN)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Recurring Period</label>
                        <select name="period" class="form-select">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="semi_annually">Semi-Annually</option>
                            <option value="annually">Annually</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="hosting">Shared Hosting</option>
                            <option value="domain">Domain Registration</option>
                            <option value="vps">VPS Hosting</option>
                            <option value="dedicated">Dedicated Server</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Product</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-4">
                <h5>Existing Products</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Period</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($prod = $products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod['name']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo ucfirst($prod['type']); ?></span></td>
                                <td>₦<?php echo number_format($prod['price'], 2); ?></td>
                                <td><?php echo ucfirst($prod['recurring_period']); ?></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
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
