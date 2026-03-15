<?php
require_once __DIR__ . '/../config.php';

class Paystack {
    private $secret_key;
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        // Get key from settings table
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'paystack_secret_key'");
        $stmt->execute();
        $stmt->bind_result($this->secret_key);
        $stmt->fetch();
        $stmt->close();
    }

    public function initializeTransaction($email, $amount, $currency = 'NGN') {
        $url = "https://api.paystack.co/transaction/initialize";
        $fields = [
            'email' => $email,
            'amount' => $amount * 100, // Paystack expects amount in kobo/cents
            'currency' => $currency
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->secret_key,
            "Cache-Control: no-cache",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    public function verifyTransaction($reference) {
        $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->secret_key,
            "Cache-Control: no-cache"
        ]);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}
