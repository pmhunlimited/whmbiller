<?php
require_once __DIR__ . '/config.php';

class Billing {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    /**
     * Calculate the final retail price for a product.
     *
     * @param int $product_id
     * @param int|null $reseller_id If null, returns the default public price.
     * @return float
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

        // Fetch reseller settings
        $stmt = $this->db->prepare("SELECT wholesale_discount, retail_markup FROM reseller_settings WHERE user_id = ?");
        $stmt->bind_param("i", $reseller_id);
        $stmt->execute();
        $stmt->bind_result($wholesale_discount, $retail_markup);
        if ($stmt->fetch()) {
            $stmt->close();
            // Wholesale price = Public price - (Public price * Wholesale Discount)
            $wholesale_price = $public_price * (1 - ($wholesale_discount / 100));
            // Final Retail Price = Wholesale price + (Wholesale price * Retail Markup)
            $final_price = $wholesale_price * (1 + ($retail_markup / 100));
            return (float)$final_price;
        }
        $stmt->close();

        return (float)$public_price;
    }

    public function getWholesalePrice($product_id, $reseller_id) {
        $stmt = $this->db->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($public_price);
        $stmt->fetch();
        $stmt->close();

        $stmt = $this->db->prepare("SELECT wholesale_discount FROM reseller_settings WHERE user_id = ?");
        $stmt->bind_param("i", $reseller_id);
        $stmt->execute();
        $stmt->bind_result($wholesale_discount);
        if ($stmt->fetch()) {
            $stmt->close();
            return $public_price * (1 - ($wholesale_discount / 100));
        }
        $stmt->close();
        return (float)$public_price;
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
