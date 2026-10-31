<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Content_Shield_Core {
    const OPTION_KEY = 'wp_content_shield_options';

    public static function init() {
        // Core initialization hooks
    }

    public static function activate() {
        if (!get_option(self::OPTION_KEY)) {
            update_option(self::OPTION_KEY, self::get_default_options());
        }
    }

    public static function get_default_options() {
        return array(
            'enable_protection'          => 1, // Master Switch

            // Basic Mouse & Copy Protections
            'disable_right_click'        => 1, // Disable right-click context menu
            'disable_text_selection'     => 1, // Disable text selection via mouse & touch
            'disable_copy'               => 1, // Block copy event (Ctrl+C, Cmd+C, etc.)
            'disable_cut'                => 1, // Block cut event (Ctrl+X)
            'disable_paste'              => 0, // Block paste event (disabled by default for forms)
            'disable_select_all'         => 1, // Block select all (Ctrl+A / Cmd+A)
            'allow_input_selection'      => 1, // Allow typing and selecting inside form inputs/textareas

            // Keyboard & Developer Shortcuts
            'disable_view_source'        => 1, // Block View Page Source (Ctrl+U)
            'disable_save_page'          => 1, // Block Save Page (Ctrl+S)
            'disable_print'              => 1, // Block Print & Save as PDF (Ctrl+P & window.print)
            'disable_devtools'           => 1, // Block F12, Ctrl+Shift+I/J/C, Cmd+Opt+I/J/C

            // Media & Images
            'disable_image_drag'         => 1, // Block dragging images to desktop or new tab
            'protect_images_overlay'     => 1, // Protect images with DOM layer
            'disable_link_drag'          => 1, // Block dragging links and content blocks
            'disable_media_download'     => 1, // Hide download option on HTML5 video & audio

            // Advanced & Scrapers
            'enable_devtools_detector'   => 1, // Periodic devtools detection & console cleaner
            'enable_debugger_freeze'     => 1, // Infinite debugger loop to freeze devtools even if opened from browser menu
            'enable_devtools_overlay'    => 1, // Full-screen security curtain overlay when devtools is detected
            'enable_dom_nuking'          => 1, // Memory purge / DOM Nuking when DevTools opens (clears all page code)
            'enable_anti_offline'        => 1, // Anti-Save self-destruct layer if HTML is opened locally or offline
            'anti_iframe'                => 1, // Frame Buster: Prevent embedding site in foreign iframes
            'protect_rss_feed'           => 1, // Truncate RSS feed into summary to prevent bot scraping
            'watermark_clipboard'        => 0, // Append source copyright link if text is copied
            'clipboard_watermark_text'   => "\n\n[Source: " . get_bloginfo('name') . " - " . home_url() . "]",

            // Notifications & Toast
            'show_warning_toast'         => 1, // Display modern toast popup on blocked actions
            'toast_on_right_click'       => 0, // 0 = Silent right-click block (no popup)
            'toast_theme'                => 'dark', // 'dark', 'light', 'glass', 'danger'
            'toast_duration'             => 2800, // Duration in milliseconds
            'toast_message_right_click'  => '⚠️ Right-clicking is protected on this website.',
            'toast_message_copy'         => '⚠️ Content copying is prohibited on this website.',
            'toast_message_drag'         => '⚠️ Dragging images and content is disabled.',
            'toast_message_shortcuts'    => '⚠️ This keyboard shortcut has been disabled for security reasons.',
            'toast_message_devtools'     => '⚠️ Developer console inspection is disabled.',

            // Exemptions
            'exclude_admins'             => 1, // Exclude administrators (allows site owner to manage site)
            'exclude_logged_in'          => 0, // Exclude all logged-in members
            'excluded_ids'               => '', // Excluded Page / Post IDs (e.g., 12, 45)
            'excluded_post_types'        => array(), // Excluded custom post types
        );
    }

    public static function get_options() {
        $saved = get_option(self::OPTION_KEY, array());
        $defaults = self::get_default_options();
        return wp_parse_args($saved, $defaults);
    }

    public static function is_protection_active() {
        $options = self::get_options();

        // 1. Master switch check
        if (empty($options['enable_protection'])) {
            return false;
        }

        // 2. Administrator exemption
        if (!empty($options['exclude_admins']) && current_user_can('manage_options')) {
            return false;
        }

        // 3. Logged-in user exemption
        if (!empty($options['exclude_logged_in']) && is_user_logged_in()) {
            return false;
        }

        // 4. Page/Post ID exemption
        if (!empty($options['excluded_ids'])) {
            $current_id = get_queried_object_id();
            if ($current_id) {
                $ids = array_map('intval', explode(',', $options['excluded_ids']));
                if (in_array((int)$current_id, $ids, true)) {
                    return false;
                }
            }
        }

        // 5. Post Type exemption
        if (!empty($options['excluded_post_types']) && is_singular()) {
            $post_type = get_post_type();
            if (in_array($post_type, (array)$options['excluded_post_types'], true)) {
                return false;
            }
        }

        return true;
    }
}
