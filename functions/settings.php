<?php

namespace AlphaAlpaka\Defaults;

use function Env\env;

/**
 * Hide WordPress update nag to all but admins
 */

add_action('admin_head', function () {
    if (!current_user_can('update_core')) {
        remove_action('admin_notices', 'update_nag', 3);
    }
}, 1);


/**
 * Remove Archives for Dates and Authors
 */

add_action('template_redirect', function () {
    // If we are on category or tag or date or author archive to add Categories add is_category()
    if (is_date() || is_author() || is_attachment()) {
        global $wp_query;
        $wp_query->set_404(); // set to 404 not found page
    }
});


/**
 * Insert custom login logo
 */

add_action('login_head', function () {
    if (function_exists('get_field') && \get_field('logo_svg', 'options')) {
        $logopathID = \get_field('logo_svg', 'options');
        $logopath = wp_get_attachment_url($logopathID);
    } else {
        $logopath = get_template_directory_uri() . '/resources/images/logo/logo.svg';
    }

    echo '
        <style>
            .login h1 a {
                background-image: url(' . $logopath . ') !important;
                background-repeat: no-repeat;
                background-size: contain;
                width: 100%;
                height: 75px;
                display:block;
            }
        </style>
    ';
});


/**
 * Insert custom login text
 */

add_filter('login_headertext', function ($title) {
    $title = get_bloginfo('name');
    return $title;
});


/**
 * Insert custom login URL for linked logo
 */

add_filter('login_headerurl', function ($url) {
    $url = home_url('/');
    return $url;
});


/**
 * Modify admin footer text
 */

add_filter('admin_footer_text', function () {
    echo __('alpha alpaka ux GmbH', 'alpha-alpaka');
});


/**
 * Change Read More link
 */

add_filter('the_content_more_link', function () {
    return '<a href="' . get_permalink() . '">' . __('read more', 'sage') . '</a>';
});


/**
 * Sanitize File Names
 */

add_filter('sanitize_file_name', function ($filename) {

    $sanitized_filename = remove_accents($filename); // Convert to ASCII

    // Standard replacements
    $invalid = array(
        ' '   => '-',
        '%20' => '-',
        '_'   => '-',
    );

    $sanitized_filename = str_replace(array_keys($invalid), array_values($invalid), $sanitized_filename);

    $sanitized_filename = preg_replace('/[^A-Za-z0-9-\. ]/', '', $sanitized_filename); // Remove all non-alphanumeric except .
    $sanitized_filename = preg_replace('/\.(?=.*\.)/', '', $sanitized_filename); // Remove all but last .
    $sanitized_filename = preg_replace('/-+/', '-', $sanitized_filename); // Replace any more than one - in a row
    $sanitized_filename = str_replace('-.', '.', $sanitized_filename); // Remove last - if at the end
    $sanitized_filename = strtolower($sanitized_filename); // Lowercase

    return $sanitized_filename;
}, 10, 1);


/**
 * Automatically set the image Title, Alt-Text, Caption & Description upon upload
 */

add_action('add_attachment', function ($post_ID) {
    // Check if uploaded file is an image, else do nothing
    if (wp_attachment_is_image($post_ID)) {

        $my_image_title = get_post($post_ID)->post_title;

        // Sanitize the title
        // $my_image_title = upload_sanitize_file_name($my_image_title);

        // Create an array with the image meta (Title, Caption, Description) to be updated
        // Note:  comment out the Excerpt/Caption or Content/Description lines if not needed
        $my_image_meta = array(
            'ID'        => $post_ID,            // Specify the image (ID) to be updated
            'post_title'    => $my_image_title,        // Set image Title to sanitized title
            'post_excerpt'    => $my_image_title,        // Set image Caption (Excerpt) to sanitized title
            'post_content'    => $my_image_title,        // Set image Description (Content) to sanitized title
        );

        // Set the image Alt-Text
        update_post_meta($post_ID, '_wp_attachment_image_alt', $my_image_title);

        // Set the image meta (e.g. Title, Excerpt, Content)
        wp_update_post($my_image_meta);
    }
});


/**
 * Add SVG Type to allowed upload mimes
 */

add_filter('upload_mimes', function ($svg_mime) {
    $svg_mime['svg'] = 'image/svg+xml';
    return $svg_mime;
});


/**
 * Ignore WP Upload-Restrictions
 */

add_filter('wp_check_filetype_and_ext', function ($checked, $file, $filename, $mimes) {

    if (!$checked['type']) {
        $wp_filetype = wp_check_filetype($filename, $mimes);
        $ext = $wp_filetype['ext'];
        $type = $wp_filetype['type'];
        $proper_filename = $filename;

        if ($type && 0 === strpos($type, 'image/') && $ext !== 'svg') {
            $ext = $type = false;
        }

        $checked = compact('ext', 'type', 'proper_filename');
    }

    return $checked;
}, 10, 4);


/**
 * Disable Search Engines for Dev and Staging
 */

if (defined('WP_ENV') && \WP_ENV !== 'production' && !is_admin()) {
    add_action('pre_option_blog_public', '__return_zero');
}


/**
 * Register Custom Searchform File
 */

// add_filter('get_search_form', function () {
//     $form = '';
//     echo template('partials.searchform');
//     return $form;
// });


/**
 * add filter "reverse" to wp_nav_menu to get the reverse order of menuitems
 */

add_filter('wp_nav_menu_objects', function ($menu, $args) {
    if (isset($args->reverse) && $args->reverse) {
        return array_reverse($menu);
    }
    return $menu;
}, 10, 2);


/**
 * Log if Mail Error
 */
add_action('wp_mail_failed', function ($wp_error) {
    return error_log(print_r($wp_error, true));
}, 10, 1);


/**
 * Remove WP Shortlink
 */

add_filter('after_setup_theme', function () {
    // remove HTML meta tag
    // <link rel='shortlink' href='http://example.com/?p=25' />
    remove_action('wp_head', 'wp_shortlink_wp_head', 10);

    // remove HTTP header
    // Link: <https://example.com/?p=25>; rel=shortlink
    remove_action('template_redirect', 'wp_shortlink_header', 11);
});


/**
 * Getting rid of archive “label”
 */

add_filter('get_the_archive_title', function ($title) {
    if (is_category()) {
        $title = single_cat_title('', false);
    } elseif (is_tag()) {
        $title = single_tag_title('', false);
    } elseif (is_author()) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    } elseif (is_post_type_archive()) {
        $title = post_type_archive_title('', false);
    } elseif (is_tax()) {
        $title = single_term_title('', false);
    } elseif (is_home()) {
        $title = single_post_title('', false);
    }

    return $title;
});


/**
 * Getting rid of archive “label” for SEO Framework
 */

add_filter('the_seo_framework_title_from_generation', function ($title) {
    if (is_category()) {
        $title = single_cat_title('', false);
    } elseif (is_tag()) {
        $title = single_tag_title('', false);
    } elseif (is_author()) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    } elseif (is_post_type_archive()) {
        $title = post_type_archive_title('', false);
    } elseif (is_tax()) {
        $title = single_term_title('', false);
    } elseif (is_home()) {
        $title = single_post_title('', false);
    }

    return $title;
});


/**
 * Highlight Archive Menu Items when is_single
 */

add_filter('nav_menu_css_class', function ($classes = array(), $menu_item = false) {
    if (get_post_type() === 'page') :
        return $classes;
    endif;

    if (\is_object($menu_item)):
        if (get_post_type() === $menu_item->object) :
            $classes[] = 'current-menu-item';
        endif;

        if (get_post_type() === strtolower($menu_item->title)) :
            $classes[] = 'current-menu-item';
        endif;
    endif;

    return $classes;
}, 10, 2);


/**
 * check if it's local and disable local ssl verify check
 */

if (env('WP_ENV') === 'development') :
    add_filter('https_ssl_verify', '__return_false');
endif;


/**
 * Disabled Access-Control-Allow for Dev-Env (prevent CORS Errors on localhost)
 *
 */

add_action('init', function () {
    if (env('WP_ENV') !== 'development') :
        return;
    endif;

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Credentials: true');
}, 1);


/**
 * Removes comments from admin menu
 */

add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
});


/**
 * Removes comments and trackbacks from post types
 */

add_action('admin_init', function () {
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}, 100);


/**
 * Fix acf_field_post_id on preview
 */

add_filter('acf/validate_post_id', function ($post_id, $original_post_id) {
    // Don't do anything to options
    if (is_string($post_id) && str_contains($post_id, 'option')) {
        return $post_id;
    }
    // Don't do anything to blocks
    if (is_string($original_post_id) && str_contains($original_post_id, 'block')) {
        return $post_id;
    }

    // if post_id and original_post_id are the same
    if ($post_id === $original_post_id) {
        return $original_post_id;
    }

    // This should only affect on post meta fields
    if (is_preview()) {
        return get_the_ID();
    }

    return $post_id;
}, 10, 2);


// /**
//  * Removes Search Function
//  */

// add_action('parse_query', function ($query, $error = true) {

//     if (is_search() & !is_admin()) {
//         $query->is_search = false;
//         $query->query_vars['s'] = false;
//         $query->query['s'] = false;

//         // to error
//         if ($error == true)
//             $query->is_404 = true;
//     }
// });

// add_filter('get_search_form', function ($a) {
//     return null;
// });


// /**
//  * Modifies Password Form
//  */

// add_filter('the_password_form', function () {
//     global $post;
//     $label = 'pwbox-' . (empty($post->ID) ? rand() : $post->ID);
//     $o = '<div class="container mx-auto">';
//     $o .= '<form action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '" method="post">';
//     $o .= '<p>' . __("To view this protected post, enter the password below:") . '</p>';
//     $o .= '<br>';
//     $o .= ' <label for="' . $label . '">' . __("Password:") . ' </label>';
//     $o .= '<input class="border border-blue-30" name="post_password" id="' . $label . '" type="password" size="20" maxlength="20" />';
//     $o .= '<br>';
//     $o .= '<input class="btn btn--ghost border-blue before:content-none hover:bg-blue cursor-pointer mt-2" type="submit" name="Submit" value="' . esc_attr__("Submit") . '" />';
//     $o .= '</form>';
//     $o .= '</div>';
//     return $o;
// });


/**
 * adds tax and terms to body-class
 */

add_filter('body_class', function ($classes) {
    if (!is_archive()) {
        return $classes;
    }

    $post_type = get_post_type();

    $term = get_queried_object();
    if ($term && isset($term->taxonomy) && isset($term->slug)) {
        $taxonomy = str_replace('_', '-', $term->taxonomy);

        if ($term->parent > 0) {
            $parent_term = get_term($term->parent, $term->taxonomy);
            if ($parent_term && !is_wp_error($parent_term)) {
                $term_slug = str_replace('_', '-', $parent_term->slug);
            }
        } else {
            $term_slug = str_replace('_', '-', $term->slug);
        }

        $classes[] = 'archive-' . $post_type . '_' . $taxonomy . '_' . $term_slug;
    }

    return $classes;
});
