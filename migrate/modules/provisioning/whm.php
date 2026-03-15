<?php
class WHMModule {
    private $host;
    private $user;
    private $api_token;

    public function __construct($host, $user, $api_token) {
        $this->host = $host;
        $this->user = $user;
        $this->api_token = $api_token;
    }

    private function call($command, $params = []) {
        $url = "https://{$this->host}:2087/json-api/{$command}?" . http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: whm {$this->user}:{$this->api_token}"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public function createAccount($domain, $username, $password, $plan) {
        return $this->call('createacct', [
            'domain' => $domain,
            'username' => $username,
            'password' => $password,
            'plan' => $plan
        ]);
    }

    public function suspendAccount($username, $reason = "Overdue Payment") {
        return $this->call('suspendacct', ['user' => $username, 'reason' => $reason]);
    }

    public function unsuspendAccount($username) {
        return $this->call('unsuspendacct', ['user' => $username]);
    }

    public function terminateAccount($username) {
        return $this->call('removeacct', ['user' => $username]);
    }

    public function changePackage($username, $pkgname) {
        return $this->call('changepackage', ['user' => $username, 'pkgname' => $pkgname]);
    }

    public function modifyAccount($username, $params) {
        // e.g. QUOTA, BWLIMIT, MAXFTP, etc.
        $params['user'] = $username;
        return $this->call('modifyacct', $params);
    }

    public function getSessionToken($username, $service = 'cpanel') {
        // Use CreateUserSession to get SSO link
        return $this->call('create_user_session', [
            'user' => $username,
            'service' => $service
        ]);
    }

    public function setCloudLinuxLimits($username, $limits) {
        // Mock implementation for LVE integration
        // In real WHM, would use 'lveinfo' or similar via CLI/API if available
        return ['status' => 1, 'message' => 'CloudLinux limits updated for ' . $username];
    }
}
