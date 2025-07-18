<?php

/**
 * Estimated Reading Time
 */

function reading_time($post_id, $label = true)
{
    if (!$post_id) :
        $post_id = get_the_id();
    endif;

    $readingTime = get_transient('readingtime_' . $post_id);
    $readingTime = false;
    if (false === $readingTime) :
        $post = get_post($post_id);
        $the_content = apply_filters('the_content', $post->post_content);

        $wordCount = str_word_count(strip_tags($the_content));
        $readingTime = round($wordCount / 140);
        $readingTime = $readingTime + 2;

        set_transient('readingtime_' . $post_id, $readingTime, MONTH_IN_SECONDS);
    endif;

    if ($label === true) :
        return $readingTime . ' Min';
    endif;

    return $readingTime;
}


/**
 * Gets the TermID, if existing. If no term exists, creates a new term
 * return termID
 */

function getOrCreateTermID($termName, $taxonomy)
{
    if (term_exists($termName, $taxonomy)) {
        // Term already exists, retrieve its ID
        $term = get_term_by('name', $termName, $taxonomy);
        $termID = $term->term_id;
    } else {
        // Term does not exist, insert it
        $newTerm = wp_insert_term($termName, $taxonomy);

        if (!is_wp_error($newTerm)) {
            $termID = $newTerm['term_id'];
        } else {
            // There was an error adding the term
            $errorMessage = $newTerm->get_error_message();
            wp_die($errorMessage);
        }
    }

    return $termID;
}


/**
 * Escapes a String so you can use it as URL-Parameter / anchorjump
 */

function escapeUrlString($string)
{
    $string = mb_strtolower($string, 'UTF-8');
    $string = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $string);

    $string = strtolower($string);

    $string = str_replace(
        ['ä', 'ö', 'ü', 'ß'],
        ['ae', 'oe', 'ue', 'ss'],
        $string
    );

    $string = str_replace(' ', '-', $string);
    $string = preg_replace('/[^a-z0-9\-_.]/i', '', $string);

    return $string;
}


/**
 * Better printr
 */

function printr($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}


/**
 * Format number to Euro Currency
 */

/**
 * @param integer|float $oldPrice
 * @return currency $newPrice (return the interger as Currency"EUR")
 */
function getEuro($oldPrice)
{
    $formatter = new \NumberFormatter('de-EU', \NumberFormatter::CURRENCY);
    $newPrice = $formatter->format($oldPrice);
    return $newPrice;
}


/**
 * Add WP Search Disable Option to General Settings
 */
add_filter('admin_init', function () {
    add_settings_field(
        'disable_wp_search',
        'Disable WP Search',
        function () {
            $enabled = get_option('disable_wp_search', false);
?>
        <input type="checkbox" name="disable_wp_search" value="1" <?php checked($enabled, 1); ?> />
        <label for="disable_wp_search">Disable WordPress search</label>
<?php
        },
        'general'
    );
    register_setting('general', 'disable_wp_search', [
        'type' => 'boolean',
        'sanitize_callback' => function ($value) {
            return $value ? 1 : 0;
        },
        'default' => 0,
    ]);
});

if (get_option('disable_wp_search', false)) {
    add_action('parse_query', function ($query) {
        if ($query->is_search) {
            $query->is_search = false;
            $query->is_404 = true;
        }
    });

    add_filter('get_search_form', '__return_empty_string');
}

add_filter('doing_it_wrong_trigger_error', function ($status, $function_name) {
    if ('_load_textdomain_just_in_time' === $function_name) {
        return false;
    }
    return $status;
}, 10, 2);
