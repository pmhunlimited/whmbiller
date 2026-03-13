<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/billing.php';

echo "WHMBiller Cron Job Started: " . date('Y-m-d H:i:s') . "\n";

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$billing = new Billing();

// 1. Generate Invoices for upcoming renewals
// Mock logic: Find active services due in 7 days
echo "Checking for renewals...\n";

// 2. Suspend overdue services
echo "Checking for overdue services...\n";
$overdue_invoices = $db->query("SELECT * FROM invoices WHERE status = 'unpaid' AND due_date < NOW()");
while($inv = $overdue_invoices->fetch_assoc()) {
    echo "Processing overdue invoice #{$inv['id']} for User #{$inv['user_id']}\n";
    // Trigger suspension logic...
}

// 3. Cleanup old logs
echo "Cleaning up old security logs...\n";
$db->query("DELETE FROM login_logs WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 30 DAY)");

echo "Cron Job Finished.\n";
