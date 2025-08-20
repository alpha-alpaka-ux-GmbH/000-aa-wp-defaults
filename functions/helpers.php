<?php

namespace AlphaAlpaka\Defaults;

/**
 * Disables WP-Plugins
 * 
 * @param array $pluginsToDisable
 */

function disablePlugins($pluginsToDisable)
{
    if (empty($pluginsToDisable) || !is_array($pluginsToDisable)) {
        return;
    }

    if (function_exists('is_plugin_active') && function_exists('deactivate_plugins')) {
        foreach ($pluginsToDisable as $pluginToDisable) {
            if (is_plugin_active($pluginToDisable)) {
                deactivate_plugins($pluginToDisable);
            }
        }
    }
}

if (defined('DEV_DISABLED_PLUGINS')) {
    add_action('admin_init', function () {
        disablePlugins(\unserialize(constant('DEV_DISABLED_PLUGINS')));
    });
}

/**
 * PHP Logger
 */

function php_logger($data)
{
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    // print the result into the JavaScript console
    echo "<script>console.log( 'PHP LOG: " . $output . "' );</script>";
}
