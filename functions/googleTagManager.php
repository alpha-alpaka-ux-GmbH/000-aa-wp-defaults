<?php

namespace AlphaAlpaka\Defaults;

use function Env\env;

/**
 * Prevent UC / usercentrics on Datenschutz or Impressum Page
 */

add_action(
    'init',
    function () {
        if (env('WP_ENV') === 'development' && !\is_user_logged_in()) :
            if ((env('UC_SETTINGS_ID') && env('UC_SETTINGS_ID') !== '')):
                add_action('wp_head', function () {
?>
                <link rel='dns-prefetch' href='https://privacy-proxy.usercentrics.eu' />
                <link rel='preconnect' href='//app.usercentrics.eu' />
                <link rel='preconnect' href='//api.usercentrics.eu' />
                <link rel='preconnect' href='//privacy-proxy.usercentrics.eu' />
                <link rel='preload' href='//app.usercentrics.eu/browser-ui/latest/loader.js' as='script' />
                <link rel='preload' href='//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js' as='script' />
                <script id='usercentrics-cmp' data-settings-id="<? echo env('UC_SETTINGS_ID'); ?>" src='https://app.usercentrics.eu/browser-ui/latest/loader.js' data-tcf-enabled></script>
                <script type='application/javascript' src='https://privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js'></script>
            <?php
                }, 1);
            ?>


            <?php
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
                            ucTriggers.forEach(function(element) {
                                element.addEventListener('click', function(e) {
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
                <style>
                    .uc-embed {
                        .uc-checkbox {
                            input {
                                display: inline-block;
                                padding: 32px;
                            }
                        }

                        div[data-testid="uc-embed-service"] {
                            padding: 32px;

                            &:first-of-type {
                                margin-top: 0;
                            }
                        }


                        h4 {
                            padding: 32px;
                        }

                        .uc-embed-subelement {
                            padding: 32px;
                        }
                    }
                </style>
                <?php
                    if (is_page()) :
                        $page = get_queried_object();
                        if ($page->post_title == 'Datenschutz' || $page->post_title == 'Impressum') :
                ?>
                        <script type="application/javascript">
                            var UC_UI_SUPPRESS_CMP_DISPLAY = true;
                        </script>
                <?php
                        endif;
                    endif;
                }, 100);
            endif;


            if ((env('GTM_PROPERTY_ID') && env('GTM_PROPERTY_ID') !== '')):
                add_action('wp_head', function () {
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
                }, 1);

                add_action('wp_body_open', function () {
            ?>
                <!-- Google Tag Manager (noscript) -->
                <noscript>
                    <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo env('GTM_PROPERTY_ID'); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe>
                </noscript>
                <!-- End Google Tag Manager (noscript) -->
<?php
                }, 1);
            endif;
        endif;
    }
);
