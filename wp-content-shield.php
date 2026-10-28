<?php
/**
 * Plugin Name:       WP Content Shield Pro - Ultra Anti-Copy & Content Protection
 * Plugin URI:        https://github.com/ferhat/wp-content-shield
 * Description:       Enterprise-grade content theft protection for WordPress. Prevents text selection, silent right-click blocking, image drag-and-drop, printing, devtools inspection, and web scrapers.
 * Version:           1.0.0
 * Author:            Ferhat
 * Author URI:        https://github.com/ferhat
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-content-shield
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WP_CONTENT_SHIELD_VERSION', '1.0.0');
define('WP_CONTENT_SHIELD_FILE', __FILE__);
define('WP_CONTENT_SHIELD_PATH', plugin_dir_path(__FILE__));
define('WP_CONTENT_SHIELD_URL', plugin_dir_url(__FILE__));

// Load core classes
require_once WP_CONTENT_SHIELD_PATH . 'includes/class-shield-core.php';
require_once WP_CONTENT_SHIELD_PATH . 'includes/class-shield-admin.php';
require_once WP_CONTENT_SHIELD_PATH . 'includes/class-shield-public.php';

// Plugin bootstrap
function wp_content_shield_init() {
    // Initialize core
    WP_Content_Shield_Core::init();

    // Initialize admin dashboard
    if (is_admin()) {
        WP_Content_Shield_Admin::init();
    }

    // Initialize frontend protection
    WP_Content_Shield_Public::init();
}
add_action('plugins_loaded', 'wp_content_shield_init');

// Activation hook
register_activation_hook(__FILE__, array('WP_Content_Shield_Core', 'activate'));
