<?php
class Translator {
    private static $translations = [];
    private static $lang = 'en';

    public static function load($lang = 'en') {
        self::$lang = $lang;
        $file = __DIR__ . "/../lang/{$lang}.json";
        if (file_exists($file)) {
            self::$translations = json_decode(file_get_contents($file), true);
        }
    }

    public static function trans($key) {
        return self::$translations[$key] ?? $key;
    }
}

// lang/en.json
// {
//    "DASHBOARD": "Dashboard",
//    "SERVICES": "Services"
// }
