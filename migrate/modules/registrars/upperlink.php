<?php
class UpperlinkModule {
    private $username;
    private $api_token;

    public function __construct($username, $api_token) {
        $this->username = $username;
        $this->api_token = $api_token;
    }

    private function call($endpoint, $data = []) {
        $url = "https://api.upperlink.ng/v1/" . $endpoint;
        $data['username'] = $this->username;
        $data['token'] = $this->api_token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public function registerDomain($domain, $years) {
        return $this->call('domain/register', ['domain' => $domain, 'period' => $years]);
    }
}
