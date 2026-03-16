<?php
class ResellerClubModule {
    private $auth_id;
    private $api_key;
    private $sandbox;

    public function __construct($auth_id, $api_key, $sandbox = true) {
        $this->auth_id = $auth_id;
        $this->api_key = $api_key;
        $this->sandbox = $sandbox;
    }

    private function call($method, $path, $params = []) {
        $base = $this->sandbox ? "https://test.httpapi.com/api/" : "https://httpapi.com/api/";
        $default = [
            'auth-id' => $this->auth_id,
            'api-key' => $this->api_key
        ];
        $url = $base . $path . ".json?" . http_build_query(array_merge($default, $params));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method === 'POST') curl_setopt($ch, CURLOPT_POST, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public function registerDomain($domain, $years, $ns) {
        return $this->call('POST', 'domains/register', [
            'domain-name' => $domain,
            'years' => $years,
            'ns' => $ns,
            // contact-ids...
        ]);
    }
}
