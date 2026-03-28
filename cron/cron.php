<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/billing.php';

class Automation {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    public function run() {
        $this->generateInvoices();
        $this->processSuspensions();
        $this->processTerminations();
    }

    private function generateInvoices() {
        // Find services due in 7 days
        $res = $this->db->query("SELECT * FROM tblhosting WHERE domainstatus = 'Active' AND nextduedate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
        while($s = $res->fetch_assoc()) {
            $stmt = $this->db->prepare("SELECT id FROM tblinvoices WHERE userid = ? AND duedate = ?");
            $stmt->execute([$s['userid'], $s['nextduedate']]);
            if ($stmt->get_result()->num_rows === 0) {
                $inv = $this->db->prepare("INSERT INTO tblinvoices (userid, date, duedate, total, status) VALUES (?, CURDATE(), ?, ?, 'Unpaid')");
                $inv->execute([$s['userid'], $s['nextduedate'], $s['amount']]);
            }
        }
    }

    private function processSuspensions() {
        // Suspend 3 days late
        $res = $this->db->query("SELECT * FROM tblhosting WHERE domainstatus = 'Active' AND nextduedate < DATE_SUB(CURDATE(), INTERVAL 3 DAY)");
        while($s = $res->fetch_assoc()) {
            $this->db->query("UPDATE tblhosting SET domainstatus = 'Suspended' WHERE id = " . $s['id']);
        }
    }

    private function processTerminations() {
        // Terminate 30 days late
        $res = $this->db->query("SELECT * FROM tblhosting WHERE domainstatus = 'Suspended' AND nextduedate < DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        while($s = $res->fetch_assoc()) {
            $this->db->query("UPDATE tblhosting SET domainstatus = 'Terminated' WHERE id = " . $s['id']);
        }
    }
}

if (php_sapi_name() === 'cli') {
    $auto = new Automation();
    $auto->run();
    echo "WHMCS-Replica Cron Completed.\n";
}
