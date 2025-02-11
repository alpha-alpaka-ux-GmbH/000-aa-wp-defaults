<?php

namespace AlphaAlpaka\Defaults;

use function Env\env;

/**
 * Alternative MU-Plugin Activation Hook
 *
 * This is a workaround for the default activation hook because this plugin is used as an MU-plugin.
 * The hook runs only once when the option is first set.
 * 
 * TODO: Add a manual trigger (e.g., a button in the admin area) to re-run this function for setup validation.
 */

/**
 * Alternative MU-Plugin Activation Hook
 * 
 * Ensures that the plugin's setup logic is executed only once by checking a persistent option.
 */
function handleMUPluginActivation()
{
    // Run activation logic only if the plugin hasn't been initialized yet
    if (!get_option('aa_wp_defaults_initialized')) {
        runDefaultActivation();
        update_option('aa_wp_defaults_initialized', true);
    }
}

/**
 * Runs the default activation logic for the MU-plugin.
 * Handles specific actions required during plugin initialization.
 */
function runDefaultActivation()
{
    // Example: Deactivate caching plugins in non-production environments
    if (env('WP_ENV') !== 'production') {
        CacheSettings::deactivateCachingPlugins();
    }
}

/**
 * Placeholder for potential deactivation logic.
 * Note: MU-Plugins cannot use standard WordPress deactivation hooks.
 */
function handleMUPluginDeactivation()
{
    // MU-Plugins are always loaded and cannot be "deactivated" in the traditional sense
    return;
}

// Trigger the activation hook on the 'admin_init' action
add_action('admin_init', 'AlphaAlpaka\Defaults\handleMUPluginActivation');
