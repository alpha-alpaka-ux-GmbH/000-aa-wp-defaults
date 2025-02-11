<?php

namespace AlphaAlpaka\Defaults;

add_action('init', function () {

    // Disable Emoji Support
    add_filter('emoji_svg_url', '__return_false');
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');

    add_filter('tiny_mce_plugins', function ($plugins) {
        return is_array($plugins) ? array_diff($plugins, array('wpemoji')) : array();
    });

    //Remove generator
    remove_action('wp_head', 'wp_generator');

    // remove Really Simple Discovery service endpoint
    remove_action('wp_head', 'rsd_link');

    // remove Windows Live Writer manifest file
    remove_action('wp_head', 'wlwmanifest_link');

    // Disable xmlrpc.php
    add_filter('xmlrpc_enabled', '__return_false');

    // Remove Recent Comments Style Tag
    add_action('widgets_init', function () {
        global $wp_widget_factory;
        remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
    });

    //Remove shortlink
    remove_action('wp_head', 'wp_shortlink_wp_head');

    // Remove Output Links
    remove_action('wp_head', 'rest_output_link_wp_head', 10);

    // Remove oEmbed Auto discovery
    remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);

    //Remove REST API in HTTP Headers
    remove_action('template_redirect', 'rest_output_link_header', 11, 0);
});


/**
 * Close comments on the front-end
 */

add_filter('comments_open', '__return_false', 20, 2);


/**
 * Remove Pingbacks
 */

add_filter('pings_open', '__return_false', 20, 2);

/**
 * remove duotone support for Gutenberg blocks and therefore remove the SVG-shit that has been added to the body
 */

add_action('after_setup_theme', function () {

    // WordPress Version 5.9.0 or less:
    remove_action('wp_footer', 'wp_enqueue_global_styles', 1);

    // WordPress Version 5.9.1 and above:
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');

    // Remove render_block filters and duotones
    remove_filter('render_block', 'wp_render_duotone_support');
    remove_filter('render_block', 'wp_restore_group_inner_container');
    remove_filter('render_block', 'wp_render_layout_support_flag');

    // The following line removes the Gutenberg-Styles and CSS-Selectors that are responsible for inline-stlyes like has-text-color or has-link-color
    // remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
});


/**
 * Dequeue WordPress / Gutenberg Basis Styles
 */

add_action('wp_enqueue_scripts', function () {
    // Removes the global styles defined via theme.json from WP-Head, @link https://developer.wordpress.org/reference/functions/wp_enqueue_global_styles/
    wp_dequeue_style('global-styles');

    // Handles the enqueueing of block scripts and styles that are common to both the editor and the front-end.
    // @link https://developer.wordpress.org/reference/functions/wp_common_block_scripts_and_styles/
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');

    // Disable the CSS styles of WooCommerce blocks (front-end), WooCommerce > v5.8.
    // Note: WooCommerce changed wc-block-style into wc-blocks-style since v5.8
    wp_dequeue_style('wc-blocks-style');
    // If you are on WooCommerce < v5.8 please use:
    // wp_dequeue_style( 'wc-block-style' );

    // Disable the WC-Storefront theme Gutenberg blocks
    wp_dequeue_style('storefront-gutenberg-blocks');
}, 100);


/**
 * Deque Footer Scripts
 */

add_action('wp_footer', function () {
    // Deque Strange CSS-Element with ID "core-block-supports-inline-css"
    wp_dequeue_style('core-block-supports');

    // Disable wp-embed
    wp_dequeue_script('wp-embed');
});
