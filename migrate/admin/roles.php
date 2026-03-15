<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_role') {
        $name = $_POST['name'];
        $perms = json_encode($_POST['perms'] ?? []);
        $stmt = $db->prepare("INSERT INTO roles (name, permissions) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $perms);
        $stmt->execute();
    }
}

$roles = $db->query("SELECT * FROM roles");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Roles & ACL - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fc; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Staff Roles & ACL</h2>
        <a href="/admin/index" class="btn btn-secondary">Back</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-4">
                <h5>Create New Role</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="add_role">
                    <div class="mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="perms[]" value="manage_clients">
                            <label class="form-check-label">Manage Clients</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="perms[]" value="manage_products">
                            <label class="form-check-label">Manage Products</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="perms[]" value="manage_billing">
                            <label class="form-check-label">Manage Billing</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="perms[]" value="manage_security">
                            <label class="form-check-label">Manage Security</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Role</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-4">
                <h5>Existing Roles</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Role Name</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($role = $roles->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($role['name']); ?></td>
                            <td>
                                <?php
                                $ps = json_decode($role['permissions'], true);
                                foreach($ps as $p) echo '<span class="badge bg-secondary me-1">'.$p.'</span>';
                                ?>
                            </td>
                            <td><a href="#" class="btn btn-sm btn-outline-danger">Delete</a></td>
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
