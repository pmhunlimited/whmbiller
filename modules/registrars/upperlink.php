<?php
class UpperlinkModule {
    private $username;
    private $api_key;
    private $endpoint = "https://client.upperlink.ng/clients/modules/addons/DomainsReseller/api/index.php";

    public function __construct($username, $api_key) {
        $this->username = $username;
        $this->api_key = $api_key;
    }

    private function getHeaders() {
        // base64_encode(hash_hmac("sha256", "<api-key>", "<email>:<gmdate("y-m-d H")>)"))
        $token = base64_encode(hash_hmac("sha256", $this->api_key, $this->username . ":" . gmdate("y-m-d H")));
        return [
            "username: " . $this->username,
            "token: " . $token
        ];
    }

    private function call($action, $params = [], $method = 'POST') {
        $url = $this->endpoint . $action;
        $ch = curl_init();

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } else {
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function registerDomain($domain, $years, $nameservers, $contacts) {
        return $this->call('/order/domains/register', [
            "domain" => $domain,
            "regperiod" => $years,
            "nameservers" => $nameservers,
            "contacts" => $contacts
        ]);
    }

    public function renewDomain($domain, $years) {
        return $this->call('/order/domains/renew', [
            "domain" => $domain,
            "regperiod" => $years
        ]);
    }

    public function transferDomain($domain, $eppcode, $years, $nameservers, $contacts) {
        return $this->call('/order/domains/transfer', [
            "domain" => $domain,
            "eppcode" => $eppcode,
            "regperiod" => $years,
            "nameservers" => $nameservers,
            "contacts" => $contacts
        ]);
    }

    public function getEPPCode($domain) {
        return $this->call("/domains/{$domain}/eppcode", [], 'GET');
    }

    public function getNameservers($domain) {
        return $this->call("/domains/{$domain}/nameservers", [], 'GET');
    }

    public function saveNameservers($domain, $ns) {
        // ns1, ns2, ns3...
        return $this->call("/domains/{$domain}/nameservers", array_merge(["domain" => $domain], $ns));
    }
}
