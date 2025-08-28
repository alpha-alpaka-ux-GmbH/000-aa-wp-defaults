<?php

namespace AlphaAlpaka\Defaults;

/**
 * Remove all dashboard widgets
 */

add_action('wp_dashboard_setup', function () {
    global $wp_meta_boxes;

    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);

    remove_meta_box('dashboard_activity', 'dashboard', 'normal');
});

add_action('admin_bar_menu', function ($wp_admin_bar) {
    if (!function_exists('env')) {
        return;
    }

    $env = env('WP_ENV') ?: '???';
    $label = strtoupper($env);

    $color = match ($env) {
        'production' => '#dc3545',
        'staging'    => '#fd7e14',
        'development' => '#28a745',
        default      => '#6c757d',
    };

    $wp_admin_bar->add_node([
        'id'    => 'wp-env-indicator',
        'title' => "<span style='color: {$color};'><b>{$label}</b></span>",
        'href'  => false,
        'meta'  => [
            'title' => "ENV: {$env}",
        ]
    ]);
}, 100);
