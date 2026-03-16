<?php
class InterserverModule {
    private $user;
    private $api_key;
    private $endpoint = "https://api.interserver.net";

    public function __construct($user, $api_key) {
        $this->user = $user;
        $this->api_key = $api_key;
    }

    private function call($path, $params = [], $method = 'GET') {
        $url = $this->endpoint . $path;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERPWD, "{$this->user}:{$this->api_key}");

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        } else {
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function listVps() {
        return $this->call('/vps');
    }

    public function createVps($hostname, $plan, $os) {
        return $this->call('/vps', [
            'hostname' => $hostname,
            'plan' => $plan,
            'os' => $os
        ], 'POST');
    }

    public function rebootVps($vps_id) {
        return $this->call("/vps/{$vps_id}/reboot", [], 'POST');
    }

    public function terminateVps($vps_id) {
        return $this->call("/vps/{$vps_id}", [], 'DELETE');
    }

    // Interserver Dedicated
    public function listDedicated() {
        return $this->call('/dedicated');
    }
}
