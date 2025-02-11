<?php

namespace AlphaAlpaka\Defaults;
// TODO: add auto sync 
// https://github.com/JAW-Dev/advanced-custom-fields-auto-json-sync/tree/master/includes
// https://gist.github.com/noahduncan/bb658cf8a7c7947af7ea0b00b173686f

/**
 * Class ACFSync
 *
 * Synchronizes ACF fields with the local filesystem.
 */
class ACFSync
{
    /**
     * Initializes the static properties
     */
    public static function initialize()
    {
        // Hide ACF menu item in Production
        if (defined('WP_ENV') && WP_ENV == 'production') {
            add_filter('acf/settings/show_admin', '__return_false');
        }
    }

    /**
     * Set local json save path
     * @param  string $path unmodified local path for acf-json
     * @return string       our modified local path for acf-json
     */

    public static function saveJSON($path)
    {
        // Set Sage9 friendly path at /theme-directory/resources/assets/acf-json

        if (is_dir(get_stylesheet_directory() . '/assets')) {
            // This is Sage 9
            $path = get_stylesheet_directory() . '/assets/acf-json';
        } elseif (is_dir(get_stylesheet_directory() . '/resources/assets')) {
            // This is old Sage 10
            $path = get_stylesheet_directory() . '/resources/assets/acf-json';
        } elseif (is_dir(get_stylesheet_directory() . '/resources')) {
            // This is Sage 10
            $path = get_stylesheet_directory() . '/resources/acf-json';
        } else {
            // This probably isn't Sage
            $path = get_stylesheet_directory() . '/acf-json';
        }

        // If the directory doesn't exist, create it.
        if (!is_dir($path)) {
            mkdir($path);
        }

        // Always return
        return $path;
    }


    /**
     * Set local json load path
     * @param  string $path unmodified local path for acf-json
     * @return string       our modified local path for acf-json
     */

    public static function loadJSON($path)
    {
        // Sage 9 path
        $paths[] = get_stylesheet_directory() . '/assets/acf-json';

        // old Sage 10 path
        $paths[] = get_stylesheet_directory() . '/resources/assets/acf-json';

        // Sage 10 path
        $paths[] = get_stylesheet_directory() . '/resources/acf-json';

        // Failsafe path
        $paths[] = get_stylesheet_directory() . '/acf-json';

        // return
        return $paths;
    }
}

// Initialize the CacheSettings class and add the custom URL parameter functionality.
add_action('init', function () {
    ACFSync::initialize();
});

add_filter('acf/settings/save_json', [ACFSync::class, 'saveJSON']);
add_filter('acf/settings/load_json', [ACFSync::class, 'loadJSON']);
