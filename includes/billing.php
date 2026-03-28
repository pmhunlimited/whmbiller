<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/hooks.php';

// Explicitly require autoloader for Dompdf
$autoload_path = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
}

use Dompdf\Dompdf;
use Dompdf\Options;

class Billing {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    public function calculateFinalPrice($product_id, $reseller_id = null) {
        $stmt = $this->db->prepare("SELECT price FROM tblpricing WHERE type='product' AND relid = ?");
        // WHMCS logic: pricing is complex, this is simplified
        // ... (omitted for brevity, assume simple price for now)
        return 1000.00;
    }

    public function markAsPaid($invoice_id) {
        $stmt = $this->db->prepare("UPDATE tblinvoices SET status = 'Paid' WHERE id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $stmt->close();

        // Trigger Hook
        Hooks::run_hook('InvoicePaid', ['invoiceid' => $invoice_id]);
    }

    public function generateInvoicePDF($invoice_id) {
        $stmt = $this->db->prepare("SELECT i.*, u.username, u.email FROM tblinvoices i JOIN tblclients u ON i.userid = u.id WHERE i.id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$invoice) return false;

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $html = "<h1>Invoice #{$invoice['id']}</h1><p>Status: {$invoice['status']}</p>"; // Simplified
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $filepath = __DIR__ . "/../assets/invoices/invoice_{$invoice_id}.pdf";
        file_put_contents($filepath, $output);
        return $filepath;
    }

    public function convertCurrency($amount, $from = 'NGN', $to = 'USD') {
        $stmt = $this->db->prepare("SELECT value FROM tblconfiguration WHERE setting = 'ExchangeRate'");
        $stmt->execute();
        $stmt->bind_result($rate);
        $stmt->fetch();
        $stmt->close();

        $rate = (float)($rate ?: 1500);
        return ($from === 'NGN' && $to === 'USD') ? $amount / $rate : $amount * $rate;
    }
}
