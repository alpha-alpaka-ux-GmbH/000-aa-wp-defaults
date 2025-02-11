<?php


namespace AlphaAlpaka\Defaults;

use function Env\env;

/**
 * Class CacheSettings
 *
 * Utility Class to handle Cache management and custom URL-based cache clearing.
 */
class CacheSettings
{
    // Constants for cache-clearing parameters
    private const CLEAR_CACHE_PARAMETER = 'clearcache';
    private const CLEAR_CACHE_KEY = 'qwd12e12r23f23f';

    // Static property for the cache-clearing URL
    private static $clearCacheURL;

    /**
     * Initializes the static properties
     */
    public static function initialize()
    {
        // disables caching on development and staging envs (if WP_CACHE is not already defined)
        if (env('WP_ENV') !== 'production' && !defined('WP_CACHE')) {
            define('WP_CACHE', false);
        }

        // set cache clearing URL
        if (!isset(self::$clearCacheURL)) {
            $cacheQuery = http_build_query([
                self::CLEAR_CACHE_PARAMETER => self::CLEAR_CACHE_KEY,
            ]);
            self::$clearCacheURL = esc_url(get_home_url() . '?' . $cacheQuery);
        }
    }

    /**
     * Deactivates caching plugins if ENV is not production.
     */

    public static function deactivateCachingPlugins()
    {
        $pluginsToDisable = array(
            'autoptimize/autoptimize.php',
            'wp-rocket/wp-rocket.php',
            'redis-cache/redis-cache.php',
        );

        disablePlugins($pluginsToDisable);
    }

    /**
     * Gets the cache-clearing URL.
     *
     * @return string Cache-clearing URL.
     */
    public static function getClearCacheURL()
    {
        self::initialize(); // Ensure the URL is initialized
        return self::$clearCacheURL;
    }

    /**
     * Clears the WP Rocket Cache if the required functions are available.
     *
     * @return void
     */
    public static function clearWPRocketCache()
    {
        if (
            !function_exists('rocket_clean_domain') ||
            !function_exists('rocket_clean_minify')
        ) {
            return;
        }

        rocket_clean_domain();
        rocket_clean_minify();
    }

    /**
     * Checks the URL parameter to clear WP Rocket Cache and triggers cache clearing.
     *
     * @return void
     */
    public static function addURLToPurgeCache()
    {
        // Check if the custom parameter exists and matches the key
        if (
            isset($_GET[self::CLEAR_CACHE_PARAMETER]) &&
            sanitize_text_field($_GET[self::CLEAR_CACHE_PARAMETER]) === self::CLEAR_CACHE_KEY
        ) {
            self::clearWPRocketCache();
        }
    }
}

// Initialize the CacheSettings class and add the custom URL parameter functionality.
add_action('init', function () {
    CacheSettings::initialize();
    CacheSettings::addURLToPurgeCache();
});
