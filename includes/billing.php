<?php
require_once __DIR__ . '/config.php';

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

    /**
     * Calculate the final retail price for a product.
     */
    public function calculateFinalPrice($product_id, $reseller_id = null) {
        $stmt = $this->db->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($public_price);
        $stmt->fetch();
        $stmt->close();

        if (!$reseller_id) {
            return (float)$public_price;
        }

        $stmt = $this->db->prepare("SELECT wholesale_discount, retail_markup FROM reseller_settings WHERE user_id = ?");
        $stmt->bind_param("i", $reseller_id);
        $stmt->execute();
        $stmt->bind_result($wholesale_discount, $retail_markup);
        if ($stmt->fetch()) {
            $stmt->close();
            $wholesale_price = $public_price * (1 - ($wholesale_discount / 100));
            $final_price = $wholesale_price * (1 + ($retail_markup / 100));
            return (float)$final_price;
        }
        $stmt->close();

        return (float)$public_price;
    }

    public function generateInvoicePDF($invoice_id) {
        $stmt = $this->db->prepare("SELECT i.*, u.username, u.email FROM invoices i JOIN users u ON i.user_id = u.id WHERE i.id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$invoice) return false;

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $html = "
        <style>
            body { font-family: sans-serif; color: #333; }
            .header { text-align: right; margin-bottom: 50px; }
            .invoice-title { font-size: 24px; font-weight: bold; color: #0d6efd; }
            .details { margin-bottom: 30px; }
            .table { width: 100%; border-collapse: collapse; }
            .table th { background: #f8f9fa; padding: 10px; border: 1px solid #ddd; text-align: left; }
            .table td { padding: 10px; border: 1px solid #ddd; }
            .total { text-align: right; font-size: 18px; font-weight: bold; margin-top: 20px; }
        </style>
        <div class='header'>
            <div class='invoice-title'>INVOICE</div>
            <div>#INV-{$invoice['id']}</div>
            <div>Date: " . date('M j, Y', strtotime($invoice['created_at'])) . "</div>
        </div>
        <div class='details'>
            <strong>Billed To:</strong><br>
            {$invoice['username']}<br>
            {$invoice['email']}
        </div>
        <table class='table'>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Service Renewal / Registration</td>
                    <td>{$invoice['currency']} " . number_format($invoice['amount'], 2) . "</td>
                </tr>
            </tbody>
        </table>
        <div class='total'>Total: {$invoice['currency']} " . number_format($invoice['amount'], 2) . "</div>
        ";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $filepath = dirname(__DIR__) . "/assets/invoices/invoice_{$invoice_id}.pdf";
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        file_put_contents($filepath, $output);

        return $filepath;
    }

    public function convertCurrency($amount, $from = 'NGN', $to = 'USD') {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'exchange_rate'");
        $stmt->execute();
        $stmt->bind_result($rate);
        $stmt->fetch();
        $stmt->close();

        $rate = (float)($rate ?: 1500);

        if ($from === 'NGN' && $to === 'USD') {
            return $amount / $rate;
        } elseif ($from === 'USD' && $to === 'NGN') {
            return $amount * $rate;
        }
        return $amount;
    }
}
