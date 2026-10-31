<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Content_Shield_Admin {

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
        add_filter('plugin_action_links_' . plugin_basename(WP_CONTENT_SHIELD_FILE), array(__CLASS__, 'add_action_links'));
    }

    public static function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wp-content-shield') . '">' . __('Settings', 'wp-content-shield') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public static function add_admin_menu() {
        add_menu_page(
            __('Content Shield Pro', 'wp-content-shield'),
            __('Content Shield', 'wp-content-shield'),
            'manage_options',
            'wp-content-shield',
            array(__CLASS__, 'render_admin_page'),
            'dashicons-shield-alt',
            81
        );
    }

    public static function register_settings() {
        register_setting(
            'wp_content_shield_group',
            WP_Content_Shield_Core::OPTION_KEY,
            array(__CLASS__, 'sanitize_options')
        );
    }

    public static function sanitize_options($input) {
        $defaults = WP_Content_Shield_Core::get_default_options();
        $output = array();

        // Boolean fields
        $boolean_fields = array(
            'enable_protection',
            'disable_right_click',
            'disable_text_selection',
            'disable_copy',
            'disable_cut',
            'disable_paste',
            'disable_select_all',
            'allow_input_selection',
            'disable_view_source',
            'disable_save_page',
            'disable_print',
            'disable_devtools',
            'enable_devtools_detector',
            'enable_debugger_freeze',
            'enable_devtools_overlay',
            'enable_dom_nuking',
            'enable_anti_offline',
            'disable_image_drag',
            'protect_images_overlay',
            'disable_link_drag',
            'disable_media_download',
            'anti_iframe',
            'protect_rss_feed',
            'watermark_clipboard',
            'show_warning_toast',
            'toast_on_right_click',
            'exclude_admins',
            'exclude_logged_in',
        );

        foreach ($boolean_fields as $field) {
            $output[$field] = !empty($input[$field]) ? 1 : 0;
        }

        // Dropdown and numeric fields
        $output['toast_theme'] = in_array($input['toast_theme'], array('dark', 'light', 'glass', 'danger')) ? $input['toast_theme'] : 'dark';
        $output['toast_duration'] = !empty($input['toast_duration']) ? absint($input['toast_duration']) : 2800;

        // Text fields
        $text_fields = array(
            'toast_message_right_click',
            'toast_message_copy',
            'toast_message_drag',
            'toast_message_shortcuts',
            'toast_message_devtools',
            'clipboard_watermark_text',
            'excluded_ids',
        );

        foreach ($text_fields as $field) {
            $output[$field] = isset($input[$field]) ? sanitize_text_field($input[$field]) : $defaults[$field];
        }

        // Post types
        $output['excluded_post_types'] = !empty($input['excluded_post_types']) && is_array($input['excluded_post_types'])
            ? array_map('sanitize_key', $input['excluded_post_types'])
            : array();

        return $output;
    }

    public static function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_wp-content-shield') {
            return;
        }

        wp_enqueue_style(
            'wp-content-shield-admin',
            WP_CONTENT_SHIELD_URL . 'admin/css/admin.css',
            array(),
            WP_CONTENT_SHIELD_VERSION
        );

        wp_enqueue_script(
            'wp-content-shield-admin',
            WP_CONTENT_SHIELD_URL . 'admin/js/admin.js',
            array('jquery'),
            WP_CONTENT_SHIELD_VERSION,
            true
        );
    }

    public static function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $options = WP_Content_Shield_Core::get_options();
        $is_active = !empty($options['enable_protection']);
        $post_types = get_post_types(array('public' => true), 'objects');
        ?>
        <div class="wrap wp-shield-admin-wrap">
            <!-- Header & Status Pill -->
            <div class="wp-shield-header">
                <div class="wp-shield-brand">
                    <span class="wp-shield-brand-icon dashicons dashicons-shield-alt"></span>
                    <div>
                        <h1>WP Content Shield Pro <span class="wp-shield-version">v<?php echo WP_CONTENT_SHIELD_VERSION; ?></span></h1>
                        <p class="wp-shield-subtitle">Ultra Anti-Copy & Advanced Content Theft Protection</p>
                    </div>
                </div>
                <div class="wp-shield-header-actions">
                    <div class="wp-shield-status-pill <?php echo $is_active ? 'active' : 'inactive'; ?>">
                        <span class="status-dot"></span>
                        <span class="status-label"><?php echo $is_active ? 'PROTECTION ACTIVE' : 'PROTECTION DISABLED'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick Presets Banner -->
            <div class="wp-shield-presets-banner">
                <div class="presets-info">
                    <strong>⚡ Quick Presets:</strong> Apply recommended security levels with a single click.
                </div>
                <div class="presets-buttons">
                    <button type="button" class="button wp-shield-preset-btn" data-preset="max">
                        🔒 Maximum Protection (Lock All)
                    </button>
                    <button type="button" class="button wp-shield-preset-btn" data-preset="balanced">
                        ⚖️ Balanced Protection (Recommended)
                    </button>
                    <button type="button" class="button wp-shield-preset-btn" data-preset="reset">
                        🔄 Reset to Defaults
                    </button>
                </div>
            </div>

            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') : ?>
                <div class="notice notice-success is-dismissible wp-shield-notice">
                    <p><strong>✅ Success!</strong> Security and anti-copy settings have been updated.</p>
                </div>
            <?php endif; ?>

            <!-- Settings Form -->
            <form method="post" action="options.php" id="wp-shield-form">
                <?php settings_fields('wp_content_shield_group'); ?>

                <div class="wp-shield-container">
                    <!-- Tab Navigation -->
                    <div class="wp-shield-tabs">
                        <button type="button" class="wp-shield-tab-nav active" data-tab="tab-general">
                            <span class="dashicons dashicons-lock"></span> General & Mouse
                        </button>
                        <button type="button" class="wp-shield-tab-nav" data-tab="tab-shortcuts">
                            <span class="dashicons dashicons-editor-code"></span> Keyboard & DevTools
                        </button>
                        <button type="button" class="wp-shield-tab-nav" data-tab="tab-media">
                            <span class="dashicons dashicons-format-image"></span> Images & Media
                        </button>
                        <button type="button" class="wp-shield-tab-nav" data-tab="tab-advanced">
                            <span class="dashicons dashicons-admin-site-alt3"></span> Advanced & Scrapers
                        </button>
                        <button type="button" class="wp-shield-tab-nav" data-tab="tab-toast">
                            <span class="dashicons dashicons-megaphone"></span> Notifications & Alerts
                        </button>
                        <button type="button" class="wp-shield-tab-nav" data-tab="tab-exclusions">
                            <span class="dashicons dashicons-groups"></span> Exemptions
                        </button>
                        <button type="button" class="wp-shield-tab-nav" data-tab="tab-sandbox">
                            <span class="dashicons dashicons-art"></span> Live Sandbox
                        </button>
                    </div>

                    <!-- Tab Content Container -->
                    <div class="wp-shield-tab-content-container">

                        <!-- TAB 1: General & Mouse -->
                        <div class="wp-shield-tab-pane active" id="tab-general">
                            <div class="wp-shield-card">
                                <h3>🛡️ Master Switch</h3>
                                <div class="wp-shield-setting-row highlight">
                                    <div class="setting-meta">
                                        <label for="enable_protection">Enable Protection System</label>
                                        <p class="description">Master toggle to activate or deactivate all protection mechanisms across your site.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="enable_protection" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[enable_protection]" value="1" <?php checked(1, $options['enable_protection']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="wp-shield-card">
                                <h3>🖱️ Mouse & Selection Defense</h3>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_right_click">Disable Right-Click Context Menu</label>
                                        <p class="description">Prevents visitors from opening the context menu on any element or image across the website.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_right_click" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_right_click]" value="1" <?php checked(1, $options['disable_right_click']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_text_selection">Disable Text Selection & Highlight</label>
                                        <p class="description">Prevents dragging with mouse or touch to highlight and select text (enforced via CSS and JS capture phases).</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_text_selection" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_text_selection]" value="1" <?php checked(1, $options['disable_text_selection']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_copy">Block Copy & Clipboard Operations</label>
                                        <p class="description">Intercepts copy events, clears clipboard data, and blocks unauthorized content extraction.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_copy" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_copy]" value="1" <?php checked(1, $options['disable_copy']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_cut">Block Cut Operations (Ctrl + X / Cmd + X)</label>
                                        <p class="description">Prevents cutting content from the webpage.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_cut" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_cut]" value="1" <?php checked(1, $options['disable_cut']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_select_all">Block Select All Shortcut (Ctrl + A / Cmd + A)</label>
                                        <p class="description">Prevents visitors from selecting the entire page content with a single key combination.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_select_all" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_select_all]" value="1" <?php checked(1, $options['disable_select_all']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="allow_input_selection">Allow Selection in Form Fields (Recommended)</label>
                                        <p class="description">Allows visitors to type, select, and edit text inside search boxes, contact forms, and comment areas.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="allow_input_selection" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[allow_input_selection]" value="1" <?php checked(1, $options['allow_input_selection']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: Keyboard & DevTools -->
                        <div class="wp-shield-tab-pane" id="tab-shortcuts">
                            <div class="wp-shield-card">
                                <h3>⌨️ Keyboard Shortcuts & DevTools Blockers</h3>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_view_source">Block View Page Source (Ctrl + U / Cmd + Opt + U)</label>
                                        <p class="description">Blocks hotkeys that open the browser's raw HTML view source window.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_view_source" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_view_source]" value="1" <?php checked(1, $options['disable_view_source']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_save_page">Block Save Page (Ctrl + S / Cmd + S)</label>
                                        <p class="description">Prevents saving the page as offline HTML or Web Archive onto visitors' computers.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_save_page" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_save_page]" value="1" <?php checked(1, $options['disable_save_page']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_print">Block Printing & Save to PDF (Ctrl + P)</label>
                                        <p class="description">Disables printer requests, window.print, and blanks print previews via @media print CSS.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_print" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_print]" value="1" <?php checked(1, $options['disable_print']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_devtools">Block Developer Tools (F12, Ctrl+Shift+I, J, C)</label>
                                        <p class="description">Disables keyboard shortcuts for Inspect Element and Web Console across all browsers.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_devtools" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_devtools]" value="1" <?php checked(1, $options['disable_devtools']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="enable_devtools_detector">Active DevTools Detector & Console Cleaner</label>
                                        <p class="description">Detects open developer consoles via viewport delta checks, flushes console logs, and outputs copyright warnings.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="enable_devtools_detector" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[enable_devtools_detector]" value="1" <?php checked(1, $options['enable_devtools_detector']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="enable_debugger_freeze">Freeze DevTools via Debugger Loop (Even from Browser Menu)</label>
                                        <p class="description">Traps and locks developer tools with an execution debugger loop if a visitor opens DevTools from browser menus or shortcuts, rendering inspection impossible.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="enable_debugger_freeze" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[enable_debugger_freeze]" value="1" <?php checked(1, !empty($options['enable_debugger_freeze'])); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="enable_devtools_overlay">Full-Screen Security Curtain on DevTools Open</label>
                                        <p class="description">Displays a full-screen protective curtain over the website while developer inspection tools remain open.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="enable_devtools_overlay" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[enable_devtools_overlay]" value="1" <?php checked(1, !empty($options['enable_devtools_overlay'])); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row highlight">
                                    <div class="setting-meta">
                                        <label for="enable_dom_nuking">💥 Purge Source Code from Memory on DevTools (DOM Nuking)</label>
                                        <p class="description"><strong>Ultra Security:</strong> When F12 or Developer Tools are detected, the entire page content, images, and text are immediately purged from browser memory (DOM). The Elements inspector displays <u>only the warning container</u> and zero site code!</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="enable_dom_nuking" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[enable_dom_nuking]" value="1" <?php checked(1, !empty($options['enable_dom_nuking'])); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: Images & Media -->
                        <div class="wp-shield-tab-pane" id="tab-media">
                            <div class="wp-shield-card">
                                <h3>🖼️ Image & Media Protection</h3>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_image_drag">Block Image Drag & Drop</label>
                                        <p class="description">Prevents visitors from dragging pictures to their desktop, folder, or a new browser tab.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_image_drag" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_image_drag]" value="1" <?php checked(1, $options['disable_image_drag']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_link_drag">Block Link & Block Dragging</label>
                                        <p class="description">Prevents dragging links, text boxes, and container blocks.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_link_drag" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_link_drag]" value="1" <?php checked(1, $options['disable_link_drag']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="protect_images_overlay">Smart Image Shield Layer</label>
                                        <p class="description">Applies a transparent protective DOM overlay over images to prevent direct saving.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="protect_images_overlay" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[protect_images_overlay]" value="1" <?php checked(1, $options['protect_images_overlay']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="disable_media_download">Hide Video & Audio Download Buttons</label>
                                        <p class="description">Removes download and Picture-in-Picture controls from HTML5 media elements.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="disable_media_download" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[disable_media_download]" value="1" <?php checked(1, $options['disable_media_download']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: Advanced & Scrapers -->
                        <div class="wp-shield-tab-pane" id="tab-advanced">
                            <div class="wp-shield-card">
                                <h3>🌐 Advanced Security & Bot Defense</h3>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="anti_iframe">Anti-iFrame / Frame-Buster Defense</label>
                                        <p class="description">Prevents malicious sites from embedding your website inside hidden iframes (guards against clickjacking and spoofing).</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="anti_iframe" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[anti_iframe]" value="1" <?php checked(1, $options['anti_iframe']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="enable_anti_offline">Anti-Offline Self-Destruct Layer (Anti-Save Defense)</label>
                                        <p class="description">Even if a visitor uses browser menus (File > Save Page As) to save HTML, the content self-destructs into a copyright warning whenever opened offline or locally.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="enable_anti_offline" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[enable_anti_offline]" value="1" <?php checked(1, !empty($options['enable_anti_offline'])); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="protect_rss_feed">RSS / Feed Scraper Defense</label>
                                        <p class="description">Prevents automated scraper bots from pulling full articles via RSS; converts feeds into brief excerpts with back-links.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="protect_rss_feed" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[protect_rss_feed]" value="1" <?php checked(1, $options['protect_rss_feed']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="watermark_clipboard">Append Clipboard Watermark</label>
                                        <p class="description">If text is copied through any workaround, automatically appends your website's canonical source link and copyright notice.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="watermark_clipboard" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[watermark_clipboard]" value="1" <?php checked(1, $options['watermark_clipboard']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="clipboard_watermark_text">Clipboard Watermark Template</label>
                                        <p class="description">Text appended to clipboard upon copy events.</p>
                                    </div>
                                    <div class="setting-control wide">
                                        <input type="text" id="clipboard_watermark_text" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[clipboard_watermark_text]" value="<?php echo esc_attr($options['clipboard_watermark_text']); ?>" class="regular-text">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: Notifications & Alerts -->
                        <div class="wp-shield-tab-pane" id="tab-toast">
                            <div class="wp-shield-card">
                                <h3>🔔 Notifications & Alert Banners</h3>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="show_warning_toast">Show Toast Warning Popup</label>
                                        <p class="description">Displays a modern floating glassmorphism alert banner when an unauthorized action is blocked.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="show_warning_toast" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[show_warning_toast]" value="1" <?php checked(1, $options['show_warning_toast']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="toast_on_right_click">Show Warning on Right-Click</label>
                                        <p class="description"><strong>Disabled (Recommended):</strong> Right-clicking is blocked silently without showing any popup or alert to visitors.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="toast_on_right_click" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[toast_on_right_click]" value="1" <?php checked(1, !empty($options['toast_on_right_click'])); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="toast_theme">Toast Card Theme</label>
                                        <p class="description">Visual design and color palette of the alert banner.</p>
                                    </div>
                                    <div class="setting-control">
                                        <select id="toast_theme" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[toast_theme]">
                                            <option value="dark" <?php selected('dark', $options['toast_theme']); ?>>🌙 Dark Modern</option>
                                            <option value="glass" <?php selected('glass', $options['toast_theme']); ?>>✨ Glassmorphism (Frosted Glass)</option>
                                            <option value="light" <?php selected('light', $options['toast_theme']); ?>>☀️ Light Minimal</option>
                                            <option value="danger" <?php selected('danger', $options['toast_theme']); ?>>🚨 Danger Red Alert</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="toast_duration">Display Duration (Milliseconds)</label>
                                        <p class="description">Time the notification remains on screen (e.g., 2800 = 2.8 seconds).</p>
                                    </div>
                                    <div class="setting-control">
                                        <input type="number" id="toast_duration" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[toast_duration]" value="<?php echo esc_attr($options['toast_duration']); ?>" min="1000" max="10000" step="100">
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="toast_message_right_click">Right-Click Alert Message</label>
                                    </div>
                                    <div class="setting-control wide">
                                        <input type="text" id="toast_message_right_click" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[toast_message_right_click]" value="<?php echo esc_attr($options['toast_message_right_click']); ?>" class="large-text">
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="toast_message_copy">Copy Alert Message</label>
                                    </div>
                                    <div class="setting-control wide">
                                        <input type="text" id="toast_message_copy" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[toast_message_copy]" value="<?php echo esc_attr($options['toast_message_copy']); ?>" class="large-text">
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="toast_message_drag">Image & Drag Alert Message</label>
                                    </div>
                                    <div class="setting-control wide">
                                        <input type="text" id="toast_message_drag" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[toast_message_drag]" value="<?php echo esc_attr($options['toast_message_drag']); ?>" class="large-text">
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="toast_message_shortcuts">Shortcut Alert Message</label>
                                    </div>
                                    <div class="setting-control wide">
                                        <input type="text" id="toast_message_shortcuts" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[toast_message_shortcuts]" value="<?php echo esc_attr($options['toast_message_shortcuts']); ?>" class="large-text">
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="toast_message_devtools">DevTools Alert Message</label>
                                    </div>
                                    <div class="setting-control wide">
                                        <input type="text" id="toast_message_devtools" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[toast_message_devtools]" value="<?php echo esc_attr($options['toast_message_devtools']); ?>" class="large-text">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 6: Exemptions -->
                        <div class="wp-shield-tab-pane" id="tab-exclusions">
                            <div class="wp-shield-card">
                                <h3>👥 Roles & Page Exemptions</h3>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="exclude_admins">Exclude Administrators (Recommended)</label>
                                        <p class="description">When logged in as an administrator, all protection is bypassed so you can manage and edit your site without interference.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="exclude_admins" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[exclude_admins]" value="1" <?php checked(1, $options['exclude_admins']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="exclude_logged_in">Exclude All Logged-In Members</label>
                                        <p class="description">Disables content protection for all registered logged-in users.</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="wp-shield-switch">
                                            <input type="checkbox" id="exclude_logged_in" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[exclude_logged_in]" value="1" <?php checked(1, $options['exclude_logged_in']); ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label for="excluded_ids">Excluded Page / Post IDs</label>
                                        <p class="description">Comma-separated list of page or post IDs where protection should not apply (e.g., <code>15, 42, 108</code>).</p>
                                    </div>
                                    <div class="setting-control wide">
                                        <input type="text" id="excluded_ids" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[excluded_ids]" value="<?php echo esc_attr($options['excluded_ids']); ?>" placeholder="e.g. 12, 45, 90" class="regular-text">
                                    </div>
                                </div>

                                <div class="wp-shield-setting-row">
                                    <div class="setting-meta">
                                        <label>Excluded Post Types</label>
                                        <p class="description">Select post types where content protection should be disabled.</p>
                                    </div>
                                    <div class="setting-control wide checkbox-grid">
                                        <?php foreach ($post_types as $pt) : ?>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="<?php echo WP_Content_Shield_Core::OPTION_KEY; ?>[excluded_post_types][]" value="<?php echo esc_attr($pt->name); ?>" <?php checked(in_array($pt->name, (array)$options['excluded_post_types'])); ?>>
                                                <?php echo esc_html($pt->label); ?> (<code><?php echo esc_html($pt->name); ?></code>)
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 7: Live Sandbox -->
                        <div class="wp-shield-tab-pane" id="tab-sandbox">
                            <div class="wp-shield-card">
                                <h3>🧪 Interactive Live Sandbox</h3>
                                <p class="description">Test your protection rules, right-click suppression, text selection, image dragging, and alert notifications in real time right inside this box.</p>

                                <div class="wp-shield-sandbox-box" id="wp-shield-sandbox-area">
                                    <div class="sandbox-badge">🛡️ Live Protection Sandbox</div>
                                    <h4>🔒 Try selecting or copying this text!</h4>
                                    <p>
                                        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. This protected text demonstrates how content theft is blocked.
                                        Notice that mouse dragging, highlighting, and copying are prevented, and test your notification themes directly below."
                                    </p>

                                    <div class="sandbox-media-test">
                                        <div class="sandbox-img-wrap">
                                            <img src="<?php echo esc_url(includes_url('images/w-logo-blue.png')); ?>" alt="Test Image" id="sandbox-test-img">
                                            <span class="sandbox-img-label">Try dragging or right-clicking this image</span>
                                        </div>
                                    </div>

                                    <div class="sandbox-form-test">
                                        <label for="sandbox-input">Form Input Usability Test (Forms must remain editable):</label>
                                        <input type="text" id="sandbox-input" value="You can freely select and edit text inside this form field." class="regular-text">
                                    </div>

                                    <div class="sandbox-actions">
                                        <button type="button" class="button button-secondary" id="sandbox-trigger-toast-btn">
                                            🔔 Trigger Test Toast Notification
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Sticky Footer Save Bar -->
                <div class="wp-shield-footer-bar">
                    <button type="submit" class="button button-primary button-hero">
                        <span class="dashicons dashicons-saved"></span> Save Settings & Apply Protection
                    </button>
                </div>
            </form>
        </div>
        <?php
    }
}
