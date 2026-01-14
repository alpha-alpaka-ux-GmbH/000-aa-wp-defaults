<?php

namespace AlphaAlpaka\Defaults;

function aa_is_privacy_or_impressum_page()
{
    if (!is_page()) return false;
    $page = get_queried_object();
    return $page && isset($page->post_title) && in_array($page->post_title, ['Datenschutz', 'Impressum'], true);
}

function aa_enqueue_usercentrics_assets()
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

    // Usercentrics Scripts
    wp_enqueue_script(
        'usercentrics-cmp',
        'https://app.usercentrics.eu/browser-ui/latest/loader.js',
        [],
        null,
        true
    );
    wp_enqueue_script(
        'usercentrics-block',
        'https://privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js',
        [],
        null,
        true
    );

    // Eigene Banner-Logik
    wp_enqueue_script(
        'usercentrics-banner',
        plugin_dir_url(__FILE__) . 'assets/js/usercentrics-banner.js',
        [],
        null,
        true
    );

    // Eigene Styles
    wp_enqueue_style(
        'usercentrics-banner',
        plugin_dir_url(__FILE__) . 'assets/css/usercentrics-banner.css',
        [],
        null
    );

    // Dynamische Werte als Inline-Script
    $settings_id = esc_js(env('UC_SETTINGS_ID'));
    $suppress = aa_is_privacy_or_impressum_page() ? 'true' : 'false';
    $inline = "window.UC_SETTINGS_ID = '{$settings_id}'; window.UC_UI_SUPPRESS_CMP_DISPLAY = {$suppress};";
    wp_add_inline_script('usercentrics-banner', $inline);

    // GTM nur wenn vorhanden
    $gtm_id = env('GTM_PROPERTY_ID');
    if (!empty($gtm_id)) {
        $gtm_js = "window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('consent', 'default', {
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            ad_storage: 'denied',
            analytics_storage: 'denied',
            wait_for_update: 2000
        });
        gtag('set', 'ads_data_redaction', true);
        (function(w,d,s,l,i){
            w[l]=w[l]||[];
            w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});
            var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
            j.async=true;
            j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
            f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer', '{$gtm_id}');";
        wp_add_inline_script('usercentrics-banner', $gtm_js);
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\aa_enqueue_usercentrics_assets');
