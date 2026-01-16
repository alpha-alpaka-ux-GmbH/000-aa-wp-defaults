<?php

namespace AlphaAlpaka\Defaults;

use function Env\env;

function aa_init_google_tag_manager()
{
    // Check if GTM ID is set and valid
    $gtm_id = env('GTM_PROPERTY_ID');
    if (!$gtm_id || $gtm_id === '') {
        return;
    }

    // Scripts enqueuen
    add_action('wp_enqueue_scripts', function () use ($gtm_id) {
        // Dummy Script registrieren, um Inline-Script anzuhängen
        // Dies erlaubt uns, wp_add_inline_script zu nutzen
        wp_register_script('aa-gtm', false);
        wp_enqueue_script('aa-gtm');

        $gtm_id_esc = esc_js($gtm_id);

        $script = <<<JS
            // create dataLayer
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }

            // set "denied" as default for both ad and analytics storage
            gtag("consent", "default", {
                ad_user_data: "denied",
                ad_personalization: "denied",
                ad_storage: "denied",
                analytics_storage: "denied",
                wait_for_update: 2000
            });

            // Enable ads data redaction by default [optional]
            gtag("set", "ads_data_redaction", true);

            // Google Tag Manager
            (function(w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start': new Date().getTime(),
                    event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', "{$gtm_id_esc}");
JS;

        wp_add_inline_script('aa-gtm', $script);
    });
}
add_action('template_redirect', __NAMESPACE__ . '\\aa_init_google_tag_manager');
