<?php
add_action('init', function () {
    function addCustomBodyClasses($classes)
    {
        // loop through all registered blocks and add them to body-class to get proper routing for js files
        $block_types = \WP_Block_Type_Registry::get_instance()->get_all_registered();
        foreach ($block_types as $key) {
            if (has_block($key->name)) :
                $classes[] = str_replace("/", "-", $key->name);
            endif;
        }

        if (is_front_page()) :
            $classes[] = 'frontpage';
        endif;

        return $classes;
    }

    add_filter('body_class', __NAMESPACE__ . '\\addCustomBodyClasses');
    // add_filter('admin_body_class', __NAMESPACE__ . '\\addCustomBodyClasses');
});
