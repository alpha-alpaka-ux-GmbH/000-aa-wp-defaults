<?php

namespace AlphaAlpaka\Defaults;

use function Env\env;

function is_privacy_or_impressum_page()
{
    if (!is_page()) return false;
    $page = get_queried_object();
    return $page && isset($page->post_title) && in_array($page->post_title, ['Datenschutz', 'Impressum'], true);
}

function aa_init_usercentrics()
{
    if (env('WP_ENV') !== 'production' || is_user_logged_in() || empty(env('UC_SETTINGS_ID'))) return;

    // Preconnect/Prefetch/Preload direkt im Head
    add_action('wp_head', function () {
        echo "<link rel='dns-prefetch' href='https://privacy-proxy.usercentrics.eu' />\n";
        echo "<link rel='preconnect' href='//app.usercentrics.eu' />\n";
        echo "<link rel='preconnect' href='//api.usercentrics.eu' />\n";
        echo "<link rel='preconnect' href='//privacy-proxy.usercentrics.eu' />\n";
        echo "<link rel='preload' href='//app.usercentrics.eu/browser-ui/latest/loader.js' as='script' />\n";
        echo "<link rel='preload' href='//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js' as='script' />\n";
    }, 1);

    // Scripts enqueuen
    add_action('wp_enqueue_scripts', function () {
        // Usercentrics Scripts
        // Enqueue the script in a way that adds data-settings-id attribute
        $uc_settings_id = esc_attr(env('UC_SETTINGS_ID'));
        $handle = 'usercentrics-cmp';
        $src = 'https://app.usercentrics.eu/browser-ui/latest/loader.js';

        // Print Usercentrics scripts directly in the head
        add_action('wp_head', function () {
            $settings_id = esc_attr(env('UC_SETTINGS_ID'));
?>
            <script id='usercentrics-cmp' data-settings-id="<?php echo $settings_id; ?>" src='https://app.usercentrics.eu/browser-ui/latest/loader.js' data-tcf-enabled></script>
            <script type='application/javascript' src='https://privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js'></script>
<?php
        }, 2);

        wp_enqueue_script(
            'usercentrics-block',
            'https://privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js',
            [],
            null,
            true
        );

        // Eigene Banner-Logik (u.a. Link um Banner zu öffnen)
        wp_enqueue_script(
            'usercentrics-banner',
            plugin_dir_url(dirname(__DIR__) . '/index.php') . 'aa-wp-defaults/assets/js/usercentrics-banner.js',
            [],
            null,
            true
        );

        // Eigene Styles (u.a. für embedds auf der Datenschutz-Seite)
        wp_enqueue_style(
            'usercentrics-banner',
            plugin_dir_url(dirname(__DIR__) . '/index.php') . 'aa-wp-defaults/assets/css/usercentrics-banner.css',
            [],
            null
        );

        // Dynamische Werte als Inline-Script
        $settings_id = esc_js(env('UC_SETTINGS_ID'));
        $suppress = is_privacy_or_impressum_page() ? 'true' : 'false';
        $inline = "window.UC_SETTINGS_ID = '{$settings_id}'; window.UC_UI_SUPPRESS_CMP_DISPLAY = {$suppress};";
        wp_add_inline_script('usercentrics-banner', $inline);
    });
}
add_action('template_redirect', __NAMESPACE__ . '\\aa_init_usercentrics');
