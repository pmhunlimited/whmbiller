<?php
class Time4VPSModule {
    private $api_user;
    private $api_pass;

    public function __construct($api_user, $api_pass) {
        $this->api_user = $api_user;
        $this->api_pass = $api_pass;
    }

    private function call($endpoint, $method = 'GET', $data = []) {
        $url = "https://billing.time4vps.com/api/" . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->api_user . ":" . $this->api_pass);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public function getStatus($vps_id) {
        return $this->call("vps/{$vps_id}");
    }

    public function reinstall($vps_id, $os_id) {
        return $this->call("vps/{$vps_id}/reinstall", 'POST', ['os' => $os_id]);
    }
}
