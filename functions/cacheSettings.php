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
     * @return bool True if the cache was cleared, false if WP Rocket is unavailable.
     */
    public static function clearWPRocketCache()
    {
        if (
            !function_exists('rocket_clean_domain') ||
            !function_exists('rocket_clean_minify')
        ) {
            return false;
        }

        rocket_clean_domain();
        rocket_clean_minify();

        return true;
    }

    /**
     * Clears the compiled Blade views.
     *
     * They survive a deploy, so without this the previously compiled version keeps
     * being rendered and template changes only show up much later.
     *
     * @return int Number of deleted files.
     */
    public static function clearCompiledViews()
    {
        $compiled = null;

        // Acorn knows its own path - do not guess it here.
        if (function_exists('Roots\\app')) {
            try {
                $compiled = \Roots\app('config')->get('view.compiled');
            } catch (\Throwable $e) {
                $compiled = null;
            }
        }

        if (!is_string($compiled) || $compiled === '') {
            $compiled = WP_CONTENT_DIR . '/cache/acorn/framework/views';
        }

        if (!is_dir($compiled)) {
            return 0;
        }

        $cleared = 0;

        foreach (glob(rtrim($compiled, '/') . '/*.php') ?: [] as $file) {
            if (@unlink($file)) {
                $cleared++;
            }
        }

        return $cleared;
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
            self::clearCompiledViews();

            if (self::clearWPRocketCache()) {
                return;
            }

            // Auf Staging/Dev ist WP Rocket absichtlich deaktiviert - nur auf Production
            // ist ein wirkungsloser Purge ein Fehler, den der Deploy sehen muss.
            if (env('WP_ENV') === 'production') {
                wp_die(
                    'Cache clear failed: WP Rocket functions are unavailable.',
                    'Cache clear failed',
                    ['response' => 500]
                );
            }
        }
    }
}

// Initialize the CacheSettings class and add the custom URL parameter functionality.
add_action('init', function () {
    CacheSettings::initialize();
    CacheSettings::addURLToPurgeCache();
});
