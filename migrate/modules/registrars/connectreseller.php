<?php
class ConnectResellerModule {
    private $api_key;

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    private function call($command, $params = []) {
        $url = "https://api.connectreseller.com/api/v1/" . $command;
        $params['APIKey'] = $this->api_key;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . "?" . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public function registerDomain($domain, $years) {
        return $this->call('order/domain', ['domainname' => $domain, 'duration' => $years]);
    }
}
