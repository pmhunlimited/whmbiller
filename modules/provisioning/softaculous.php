<?php
class SoftaculousModule {
    private $host;
    private $user;
    private $pass;

    public function __construct($host, $user, $pass) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
    }

    public function installApp($sid, $domain, $path = '') {
        // Softaculous API call to install application (e.g. WP id=26)
        // URL: https://host:2083/softaculous/index.php?act=software&soft=26
        return ["status" => 1, "message" => "Installation queued"];
    }

    public function listBackups() {
        return [];
    }
}
