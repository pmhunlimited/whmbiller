<?php
/**
 * WHMBiller Hook System
 */

class Hooks {
    private static $hooks = [];

    /**
     * Register a new hook function
     */
    public static function add_hook($hook_name, $priority, $function) {
        self::$hooks[$hook_name][$priority][] = $function;
        ksort(self::$hooks[$hook_name]);
    }

    /**
     * Trigger all functions registered for a hook
     */
    public static function run_hook($hook_name, $args = []) {
        if (!isset(self::$hooks[$hook_name])) return $args;

        foreach (self::$hooks[$hook_name] as $priority => $functions) {
            foreach ($functions as $function) {
                if (is_callable($function)) {
                    $args = call_user_func($function, $args);
                }
            }
        }
        return $args;
    }
}

// Example usage:
// Hooks::add_hook('InvoicePaid', 10, function($vars) {
//    error_log("Hook: Invoice #" . $vars['invoiceid'] . " was paid.");
//    return $vars;
// });
