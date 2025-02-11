<?php
// TODO: this doesnt work anymore. Fix it!

namespace AlphaAlpaka\Defaults\Maintenance;

use function Env\env;

/**
 * alpha alpaka Defaults Maintenance Plugin
 *
 * Handles maintenance mode settings for both the frontend and backend. 
 * Provides a user interface to manage these settings and applies the maintenance rules accordingly.
 */

// Initialize the plugin
add_action('init', __NAMESPACE__ . '\\initialize_plugin');

/**
 * Initialize the plugin functionality
 */
function initialize_plugin()
{
    add_action('template_redirect', __NAMESPACE__ . '\\handleFrontendMaintenance');
    add_action('admin_notices', __NAMESPACE__ . '\\displayFrontendMaintenanceNotice');
    add_action('admin_head', __NAMESPACE__ . '\\handleBackendMaintenance');
    add_action('admin_notices', __NAMESPACE__ . '\\displayBackendMaintenanceNotice');

    // Initialize settings page
    new MaintenanceSettings();
}

/**
 * Handle Frontend Maintenance Mode
 */
function handleFrontendMaintenance()
{
    $options = get_option('alpha_alpaka_maintenance_option_name');

    if (!is_array($options) || !array_key_exists('maintenance_modus_1', $options)) {
        return;
    }

    wp_get_current_user();
    global $current_user, $pagenow;

    // Allow access for specific users or non-production environments
    if (strpos($current_user->user_login, 'alpaka') !== false || strpos($current_user->user_login, 'admin') !== false) {
        return;
    }
    if (is_page('wartung') || $pagenow === 'wp-login.php') {
        return;
    }

    // Redirect to the maintenance page
    wp_redirect(home_url('/wartung/'));
    exit;
}

/**
 * Display Frontend Maintenance Mode Admin Notice
 */
function displayFrontendMaintenanceNotice()
{
    $options = get_option('alpha_alpaka_maintenance_option_name');

    if (!is_array($options) || !array_key_exists('maintenance_modus_1', $options)) {
        return;
    }

    echo '<div class="notice notice-error"><p>Die Admin-Oberfläche befindet sich im Wartungsmodus und ist für andere WP-User aktuell nicht erreichbar.</p></div>';
}

/**
 * Handle Backend Maintenance Mode
 */
function handleBackendMaintenance()
{
    $options = get_option('alpha_alpaka_maintenance_option_name');

    if (!is_array($options) || !array_key_exists('maintenance_modus_admin_2', $options)) {
        return;
    }

    wp_get_current_user();
    global $current_user;

    // Restrict access for users without specific usernames
    if (strpos(strtolower($current_user->user_login), 'alpaka') === false) {
        echo '<style>.updated { margin: 30px !important; }</style>';
        wp_die(
            '<div id="message" class="updated"><p><b>Wartungsmodus</b> Wir führen gerade ein Systemupdate durch. Der Administrationsbereich wird in einigen Stunden wieder erreichbar sein.</p></div>'
        );
    }
}

/**
 * Display Backend Maintenance Mode Admin Notice
 */
function displayBackendMaintenanceNotice()
{
    $options = get_option('alpha_alpaka_maintenance_option_name');

    if (!is_array($options) || !array_key_exists('maintenance_modus_admin_2', $options)) {
        return;
    }

    echo '<div class="notice notice-error"><p>Die Admin-Oberfläche befindet sich im Wartungsmodus und ist für andere WP-User aktuell nicht erreichbar.</p></div>';
}

/**
 * Maintenance Settings Class
 */
class MaintenanceSettings
{
    private $options;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'addPluginPage']);
        add_action('admin_init', [$this, 'pageInit']);
    }

    public function addPluginPage()
    {
        add_options_page(
            'alpha alpaka Maintenance',
            'alpha alpaka Maintenance',
            'manage_options',
            'alpha-alpaka-maintenance',
            [$this, 'createAdminPage']
        );
    }

    public function createAdminPage()
    {
        $this->options = get_option('alpha_alpaka_maintenance_option_name'); ?>
        <div class="wrap">
            <h2>alpha alpaka Maintenance</h2>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('alpha_alpaka_maintenance_option_group');
                do_settings_sections('alpha-alpaka-maintenance-admin');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    public function pageInit()
    {
        register_setting(
            'alpha_alpaka_maintenance_option_group',
            'alpha_alpaka_maintenance_option_name',
            [$this, 'sanitize']
        );

        add_settings_section(
            'maintenance_settings_section',
            'Maintenance Settings',
            [$this, 'sectionInfo'],
            'alpha-alpaka-maintenance-admin'
        );

        add_settings_field(
            'cache_clear_url',
            'URL zum Leeren des Caches',
            [$this, 'cacheClearURLCallback'],
            'alpha-alpaka-maintenance-admin',
            'maintenance_settings_section'
        );

        add_settings_field(
            'frontend_maintenance_mode',
            'Maintenance Modus (Frontend)',
            [$this, 'frontendMaintenanceCallback'],
            'alpha-alpaka-maintenance-admin',
            'maintenance_settings_section'
        );

        add_settings_field(
            'backend_maintenance_mode',
            'Maintenance Modus (Backend)',
            [$this, 'backendMaintenanceCallback'],
            'alpha-alpaka-maintenance-admin',
            'maintenance_settings_section'
        );
    }

    public function sanitize($input)
    {
        $sanitized = [];
        if (isset($input['cache_clear_url'])) {
            $sanitized['cache_clear_url'] = sanitize_text_field($input['cache_clear_url']);
        }
        if (isset($input['frontend_maintenance_mode'])) {
            $sanitized['frontend_maintenance_mode'] = $input['frontend_maintenance_mode'];
        }
        if (isset($input['backend_maintenance_mode'])) {
            $sanitized['backend_maintenance_mode'] = $input['backend_maintenance_mode'];
        }
        return $sanitized;
    }

    public function sectionInfo()
    {
        echo 'Configure the maintenance mode settings below.';
    }

    public function cacheClearURLCallback()
    {
        $url = $this->options['cache_clear_url'] ?? '';
        printf('<input class="regular-text" type="text" name="alpha_alpaka_maintenance_option_name[cache_clear_url]" value="%s">', esc_attr($url));
    }

    public function frontendMaintenanceCallback()
    {
        $checked = isset($this->options['frontend_maintenance_mode']) ? 'checked' : '';
        printf('<input type="checkbox" name="alpha_alpaka_maintenance_option_name[frontend_maintenance_mode]" %s> Enable Frontend Maintenance Mode', $checked);
    }

    public function backendMaintenanceCallback()
    {
        $checked = isset($this->options['backend_maintenance_mode']) ? 'checked' : '';
        printf('<input type="checkbox" name="alpha_alpaka_maintenance_option_name[backend_maintenance_mode]" %s> Enable Backend Maintenance Mode', $checked);
    }
}
