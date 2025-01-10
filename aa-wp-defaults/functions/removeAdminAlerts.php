<?php

namespace AlphaAlpaka\Defaults;

/**
 * prevent showing update-nugs and notification for admins
 */

add_action('admin_head', function () {
    echo '<style>
    .otgs-installer-notice-wpml, #ewww-image-optimizer-review,
    .wpmltm-notice {
        display: none !important;
    }
</style>';
});
