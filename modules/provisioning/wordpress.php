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
        // WordPressToolkit::list is a common cPanel module for WP
        return $this->callUAPI('WordPressToolkit', 'list');
    }

    public function updateCore($installation_id) {
        return $this->callUAPI('WordPressToolkit', 'update_core', ['installation_id' => $installation_id]);
    }

    public function toggleMaintenanceMode($installation_id, $enable = true) {
        $status = $enable ? 'on' : 'off';
        return $this->callUAPI('WordPressToolkit', 'set_maintenance_mode', [
            'installation_id' => $installation_id,
            'status' => $status
        ]);
    }

    public function getOneClickLoginUrl($installation_id) {
        $res = $this->callUAPI('WordPressToolkit', 'get_login_url', ['installation_id' => $installation_id]);
        return $res['data']['url'] ?? '';
    }

    public function managePlugins($installation_id, $plugin, $action) {
        // Actions: activate, deactivate, update, remove
        return $this->callUAPI('WordPressToolkit', 'manage_plugin', [
            'installation_id' => $installation_id,
            'plugin' => $plugin,
            'plugin_action' => $action
        ]);
    }
}
