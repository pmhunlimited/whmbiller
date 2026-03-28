<?php
require_once __DIR__ . '/includes/config.php';

$input = file_get_contents('php://input');
$event = json_decode($input, true);

// Verify Paystack Signature (Simplified for demo)
// In production, check HTTP_X_PAYSTACK_SIGNATURE

if ($event['event'] === 'charge.success') {
    $amount = $event['data']['amount'] / 100;
    $email = $event['data']['customer']['email'];
    $ref = $event['data']['reference'];

    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Find user by email
    $stmt = $db->prepare("SELECT id FROM tblclients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $user_id = $user['id'];
        // Prevent double credit using gateway log check
        $stmt = $db->prepare("SELECT id FROM tblgatewaylog WHERE data LIKE ?");
        $ref_search = "%$ref%";
        $stmt->bind_param("s", $ref_search);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $stmt = $db->prepare("UPDATE tblclients SET credit = credit + ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $user_id);
            $stmt->execute();

            // Log transaction
            $stmt = $db->prepare("INSERT INTO tblgatewaylog (gateway, data, status) VALUES ('paystack', ?, 'success')");
            $stmt->bind_param("s", $input);
            $stmt->execute();
        }
    }
}

http_response_code(200);
?>
