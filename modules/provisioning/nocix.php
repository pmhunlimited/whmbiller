<?php
class NocixModule {
    private $api_key;
    private $endpoint = "https://my.nocix.net/api";

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    private function call($path, $params = [], $method = 'GET') {
        $url = $this->endpoint . $path;
        $ch = curl_init();

        $headers = ["X-API-KEY: " . $this->api_key];

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
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function getServers() {
        return $this->call('/servers');
    }

    public function rebootServer($server_id) {
        return $this->call("/servers/{$server_id}/reboot", [], 'POST');
    }
}
