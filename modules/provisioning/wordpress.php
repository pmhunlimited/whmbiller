<?php
class WordPressConsole {
    private $cpanel_host;
    private $cpanel_user;
    private $cpanel_token;

    public function __construct($host, $user, $token) {
        $this->cpanel_host = $host;
        $this->cpanel_user = $user;
        $this->cpanel_token = $token;
    }

    private function callUAPI($module, $function, $params = []) {
        $url = "https://{$this->cpanel_host}:2083/execute/{$module}/{$function}?" . http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: cpanel {$this->cpanel_user}:{$this->cpanel_token}"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public function listInstallations() {
        // Mocking WP-CLI logic via UAPI
        return $this->callUAPI('WordPressBackup', 'list_installations');
    }

    public function updateCore($path) {
        // In reality, this would trigger a WP-CLI command through cPanel API
        return $this->callUAPI('WordPress', 'update_core', ['path' => $path]);
    }

    public function toggleMaintenanceMode($path, $enable = true) {
        $status = $enable ? 'enable' : 'disable';
        return $this->callUAPI('WordPress', 'maintenance_mode', ['path' => $path, 'status' => $status]);
    }
}
