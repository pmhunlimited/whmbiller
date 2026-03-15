<?php
class NamecheapModule {
    private $api_user;
    private $api_key;
    private $client_ip;
    private $sandbox;

    public function __construct($api_user, $api_key, $client_ip, $sandbox = true) {
        $this->api_user = $api_user;
        $this->api_key = $api_key;
        $this->client_ip = $client_ip;
        $this->sandbox = $sandbox;
    }

    private function call($command, $params = []) {
        $base = $this->sandbox ? "https://api.sandbox.namecheap.com/xml.response" : "https://api.namecheap.com/xml.response";
        $default = [
            'ApiUser' => $this->api_user,
            'ApiKey' => $this->api_key,
            'UserName' => $this->api_user,
            'ClientIp' => $this->client_ip,
            'Command' => $command
        ];
        $url = $base . "?" . http_build_query(array_merge($default, $params));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return simplexml_load_string($result);
    }

    public function registerDomain($sld, $tld, $years = 1) {
        return $this->call('namecheap.domains.create', [
            'Sld' => $sld,
            'Tld' => $tld,
            'Years' => $years,
            // Additional required contact params would go here
        ]);
    }

    public function renewDomain($sld, $tld, $years = 1) {
        return $this->call('namecheap.domains.renew', [
            'Sld' => $sld,
            'Tld' => $tld,
            'Years' => $years
        ]);
    }
}
