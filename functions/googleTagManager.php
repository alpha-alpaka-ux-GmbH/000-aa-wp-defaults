<?php

namespace AlphaAlpaka\Defaults;

/**
 * Prevent UC / usercentrics on Datenschutz or Impressum Page
 */

if (env('WP_ENV') === 'production' && !is_user_logged_in()) :
    if((env('UC_SETTINGS_ID') && env('UC_SETTINGS_ID') !== '')):
add_action('wp_head', function(){
    ?>
        {{-- <!-- User Centrics Cookie Consent - only in Production --> --}}
    <link rel='dns-prefetch' href='https://privacy-proxy.usercentrics.eu' />
    <link rel='preconnect' href='//app.usercentrics.eu' />
    <link rel='preconnect' href='//api.usercentrics.eu' />
    <link rel='preconnect' href='//privacy-proxy.usercentrics.eu' />
    <link rel='preload' href='//app.usercentrics.eu/browser-ui/latest/loader.js' as='script' />
    <link rel='preload' href='//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js' as='script' />
    <script id='usercentrics-cmp' data-settings-id="<? echo env('UC_SETTINGS_ID'); ?>"
        src='https://app.usercentrics.eu/browser-ui/latest/loader.js' data-tcf-enabled></script>
    <script type='application/javascript' src='https://privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js'></script>
    <?

    add_action('wp_footer', function () {
    ?>
    <script type="application/javascript">
    document.addEventListener('DOMContentLoaded', async () => {

        /**
         * UC Banner
         */

        var elementsWithin = document.querySelectorAll('.js__trigger_uc a');
        var elementsWithClass = document.querySelectorAll('a.js__trigger_uc');
        var elementsWithHash = document.querySelectorAll('a[href*="#js__trigger_uc"]');

        var ucTriggers = Array.from(elementsWithin).concat(Array.from(elementsWithClass)).concat(Array.from(elementsWithHash));

        if (ucTriggers) {
            ucTriggers.forEach(function (element) {
                element.addEventListener('click', function (e) {
                    // console.log('trigger uc-banner')
                    e.preventDefault();

                    if (typeof UC_UI !== 'undefined' && UC_UI) {
                        UC_UI.showSecondLayer()
                    } else {
                        // console.log('UC Banner not loaded')
                    }
                });
            });
        }
    });
    </script>
    <?

    echo '
    <style>
        .uc-embed {
            .uc-checkbox {
                input {
                @apply inline-block p-4;
                }
            }

            div[data-testid="uc-embed-service"] {
                @apply p-4;

                &:first-of-type {
                @apply mt-0;
                }
            }


            h4 {
                @apply p-4;
            }

            .uc-embed-subelement {
                @apply p-4;
            }
        }
    </style>
    ';


    if (is_page()) {
        $page = get_queried_object();
        if ($page->post_title == 'Datenschutz' || $page->post_title == 'Impressum') {
?>
            <script type="application/javascript">
                var UC_UI_SUPPRESS_CMP_DISPLAY = true;
            </script>
<?php
        }
    }
endif;


        if ((env('GTM_PROPERTY_ID') && env('GTM_PROPERTY_ID') !== '')):
            ?>
    <script>
        // create dataLayer
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        // set „denied" as default for both ad and analytics storage,
        gtag("consent", "default", {
            ad_user_data: "denied",
            ad_personalization: "denied",
            ad_storage: "denied",
            analytics_storage: "denied",
            wait_for_update: 2000 // milliseconds to wait for update
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
        })(window, document, 'script', 'dataLayer', "<?php echo env('GTM_PROPERTY_ID'); ?>");
    </script>
            <?php
        endif;
});











