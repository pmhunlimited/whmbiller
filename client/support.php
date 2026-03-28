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
    $subject = $_POST['subject'];
    $dept_id = (int)$_POST['dept_id'];
    $priority = $_POST['priority'];
    $message = $_POST['message'];

    $stmt = $db->prepare("INSERT INTO tbltickets (user_id, dept_id, subject, message, priority) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $dept_id, $subject, $message, $priority);
    $stmt->execute();
    $msg = "Ticket opened successfully!";
}

$depts = $db->query("SELECT * FROM tblticketdepartments");
$tickets = $db->prepare("SELECT t.*, d.name as dept_name FROM tbltickets t LEFT JOIN tblticketdepartments d ON t.dept_id = d.id WHERE t.user_id = ? ORDER BY t.last_reply DESC");
$tickets->bind_param("i", $user_id);
$tickets->execute();
$tickets_res = $tickets->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support Tickets - WHMBiller</title>
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
        <h2><i class="bi bi-headset text-primary"></i> Support Center</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#openTicket">Open New Ticket</button>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <h5>My Tickets</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Last Update</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($t = $tickets_res->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($t['subject']); ?></strong></td>
                        <td><?php echo htmlspecialchars($t['dept_name']); ?></td>
                        <td>
                            <span class="badge <?php echo $t['status'] === 'open' ? 'bg-warning' : 'bg-success'; ?>">
                                <?php echo ucfirst($t['status']); ?>
                            </span>
                        </td>
                        <td class="small text-muted"><?php echo $t['last_reply']; ?></td>
                        <td><a href="/client/support/view?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($tickets_res->num_rows == 0): ?>
                    <tr><td colspan="5" class="text-center text-muted">No tickets found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="openTicket" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Open New Support Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Department</label>
                            <select name="dept_id" class="form-select">
                                <?php while($d = $depts->fetch_assoc()): ?>
                                    <option value="<?php echo $d['id']; ?>"><?php echo $d['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="6" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
