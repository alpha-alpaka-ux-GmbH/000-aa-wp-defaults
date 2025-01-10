<?php
/*
    Plugin Name: alpha alpaka WP Defaults
    Description: WP Defaults (necessary but also and opiononated) for alpha alpaka basic WP Setups
    Author: NH | alpha-alpaka
    Author URI: https://alpha-alpaka.de
    Version: 1.0.0
*/

namespace AlphaAlpaka\Defaults;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require plugin_dir_path(__FILE__) . 'functions.php';

// as this is an MU-Plugin, this will never be called
register_activation_hook(__FILE__, 'aaDefaultActivation');
register_deactivation_hook(__FILE__, 'aaDefaultDeactivation');

// Example usage to get the cache-clearing URL elsewhere in the code
add_action('wp_footer', function () {
    echo '<!-- Clear Cache URL: ' . CacheSettings::getClearCacheURL() . ' -->';
});
