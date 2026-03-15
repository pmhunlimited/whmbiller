<?php
class NOCIXModule {
    private $api_user;
    private $api_pass;

    public function __construct($api_user, $api_pass) {
        $this->api_user = $api_user;
        $this->api_pass = $api_pass;
    }

    private function call($endpoint, $method = 'GET', $data = []) {
        $url = "https://api.nocix.net/v1/" . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->api_user . ":" . $this->api_pass);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public function getStatus($server_id) {
        return $this->call("server/{$server_id}/status");
    }

    public function rebootServer($server_id) {
        return $this->call("server/{$server_id}/reboot", 'POST');
    }
}
