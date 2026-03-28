<?php
require_once __DIR__ . '/../includes/config.php';

function payhub_MetaData() {
    return [
        'DisplayName' => 'Payhub Payment Gateway',
        'APIVersion' => '1.1',
    ];
}

function payhub_config() {
    return [
        'FriendlyName' => ['Type' => 'System', 'Value' => 'Payhub'],
        'api_key' => ['FriendlyName' => 'API Key', 'Type' => 'password', 'Size' => '40'],
        'merchant_id' => ['FriendlyName' => 'Merchant ID', 'Type' => 'text', 'Size' => '20'],
        'secret_hash' => ['FriendlyName' => 'Secret Hash', 'Type' => 'text', 'Size' => '40'],
    ];
}

function payhub_link($params) {
    // $params are pulled from tblpaymentgateways by the billing system
    $url = "https://payhub.datagifting.com.ng/api/checkout";

    $postfields = [
        'merchant_id' => $params['merchant_id'],
        'invoice_id'  => $params['invoiceid'],
        'amount'      => $params['amount'],
        'currency'    => $params['currency'],
        'email'       => $params['clientdetails']['email'],
        'return_url'  => $params['systemurl'] . '/client/invoices',
        'callback_url'=> $params['systemurl'] . '/modules/gateways/callback/payhub.php',
    ];

    $code = '<form method="POST" action="' . $url . '">';
    foreach ($postfields as $k => $v) {
        $code .= '<input type="hidden" name="' . $k . '" value="' . htmlspecialchars($v) . '" />';
    }
    $code .= '<button type="submit" class="btn btn-primary">Pay Now via Payhub</button>';
    $code .= '</form>';

    return $code;
}
