<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/billing.php';

// Fetch Gateway Settings
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $db->query("SELECT setting, value FROM tblpaymentgateways WHERE gateway = 'payhub'");
$gateway_params = [];
while($row = $res->fetch_assoc()) {
    $gateway_params[$row['setting']] = $row['value'];
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// WHMCS style logging
$db->prepare("INSERT INTO tblgatewaylog (gateway, data, status) VALUES ('payhub', ?, ?)")
   ->execute([$input, $data['status'] ?? 'unknown']);

// Validate Hash
$received_hash = $_SERVER['HTTP_X_PAYHUB_SIGNATURE'] ?? '';
$generated_hash = hash_hmac('sha256', $input, $gateway_params['secret_hash']);

if ($received_hash === $generated_hash && $data['status'] === 'success') {
    $invoice_id = (int)$data['invoice_id'];
    $amount = (float)$data['amount'];

    $billing = new Billing();
    $billing->markAsPaid($invoice_id);

    // Log success
    error_log("Payhub Payment Successful for Invoice #$invoice_id");
}

http_response_code(200);
?>
