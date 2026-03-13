<?php
require_once __DIR__ . '/config.php';

class Billing {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    public function createInvoice($user_id, $amount, $currency = 'NGN', $due_date = null) {
        if (!$due_date) {
            $due_date = date('Y-m-d', strtotime('+7 days'));
        }
        $stmt = $this->db->prepare("INSERT INTO invoices (user_id, amount, currency, due_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user_id, $amount, $currency, $due_date);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function markAsPaid($invoice_id) {
        $stmt = $this->db->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
        $stmt->bind_param("i", $invoice_id);
        return $stmt->execute();
    }

    public function generatePDF($invoice_id) {
        require_once __DIR__ . '/../vendor/autoload.php';
        $stmt = $this->db->prepare("SELECT i.*, u.username, u.email FROM invoices i JOIN users u ON i.user_id = u.id WHERE i.id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();

        if (!$invoice) return false;

        $html = "
            <h1>Invoice #INV-{$invoice['id']}</h1>
            <p><strong>Date:</strong> {$invoice['created_at']}</p>
            <p><strong>Client:</strong> {$invoice['username']} ({$invoice['email']})</p>
            <p><strong>Amount:</strong> {$invoice['currency']} " . number_format($invoice['amount'], 2) . "</p>
            <p><strong>Status:</strong> " . strtoupper($invoice['status']) . "</p>
        ";

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = __DIR__ . "/../assets/invoices/invoice_{$invoice_id}.pdf";
        if (!is_dir(__DIR__ . "/../assets/invoices")) mkdir(__DIR__ . "/../assets/invoices", 0777, true);
        file_put_contents($filename, $dompdf->output());
        return $filename;
    }
}
