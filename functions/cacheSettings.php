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

    /**
     * Initializes the cache settings
     */
    public static function initialize()
    {
        // disables caching on development and staging envs (if WP_CACHE is not already defined)
        if (env('WP_ENV') !== 'production' && !defined('WP_CACHE')) {
            define('WP_CACHE', false);
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
        if (isset($_GET[self::CLEAR_CACHE_PARAMETER])) {
            // Der Key gehört in die .env - dieses Repo ist öffentlich.
            $key = env('CLEAR_CACHE_KEY');
            $given = wp_unslash($_GET[self::CLEAR_CACHE_PARAMETER]);

            // 403 statt still weiterrendern, sonst merkt ein Deploy mit altem Key nicht,
            // dass nichts geleert wurde. Leerer Key darf nie passen (hash_equals('', '')).
            if (!is_string($key) || $key === '' || !is_string($given) || !hash_equals($key, $given)) {
                wp_die('Cache clear denied.', 'Forbidden', ['response' => 403]);
            }

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
