<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Content_Shield_Public {

    public static function init() {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'), 9999);
        add_action('wp_head', array(__CLASS__, 'inject_critical_head_protection'), 1);
        add_filter('body_class', array(__CLASS__, 'add_body_classes'));
        add_action('send_headers', array(__CLASS__, 'send_security_headers'));

        // RSS feed scraper defense
        add_filter('the_content_feed', array(__CLASS__, 'protect_rss_content'));
        add_filter('the_excerpt_rss', array(__CLASS__, 'protect_rss_content'));
    }

    public static function enqueue_assets() {
        if (!WP_Content_Shield_Core::is_protection_active()) {
            return;
        }

        $options = WP_Content_Shield_Core::get_options();

        // Enqueue styles
        wp_enqueue_style(
            'wp-content-shield',
            WP_CONTENT_SHIELD_URL . 'public/css/content-shield.css',
            array(),
            WP_CONTENT_SHIELD_VERSION
        );

        // Enqueue protection script in footer
        wp_enqueue_script(
            'wp-content-shield',
            WP_CONTENT_SHIELD_URL . 'public/js/content-shield.js',
            array(),
            WP_CONTENT_SHIELD_VERSION,
            true
        );

        // Pass configuration to frontend JS
        $config = array(
            'active'                 => true,
            'disableRightClick'      => !empty($options['disable_right_click']),
            'disableTextSelection'   => !empty($options['disable_text_selection']),
            'disableCopy'            => !empty($options['disable_copy']),
            'disableCut'             => !empty($options['disable_cut']),
            'disablePaste'           => !empty($options['disable_paste']),
            'disableSelectAll'       => !empty($options['disable_select_all']),
            'allowInputSelection'    => !empty($options['allow_input_selection']),

            'disableViewSource'      => !empty($options['disable_view_source']),
            'disableSavePage'        => !empty($options['disable_save_page']),
            'disablePrint'           => !empty($options['disable_print']),
            'disableDevtools'        => !empty($options['disable_devtools']),
            'enableDevtoolsDetector' => !empty($options['enable_devtools_detector']),
            'enableDebuggerFreeze'   => !empty($options['enable_debugger_freeze']),
            'enableDevtoolsOverlay'  => !empty($options['enable_devtools_overlay']),

            'disableImageDrag'       => !empty($options['disable_image_drag']),
            'protectImagesOverlay'   => !empty($options['protect_images_overlay']),
            'disableLinkDrag'        => !empty($options['disable_link_drag']),
            'disableMediaDownload'   => !empty($options['disable_media_download']),

            'antiIframe'             => !empty($options['anti_iframe']),
            'watermarkClipboard'     => !empty($options['watermark_clipboard']),
            'watermarkText'          => esc_js($options['clipboard_watermark_text']),

            'showWarningToast'       => !empty($options['show_warning_toast']),
            'toastOnRightClick'      => !empty($options['toast_on_right_click']),
            'toastTheme'             => esc_attr($options['toast_theme']),
            'toastDuration'          => intval($options['toast_duration']),
            'messages'               => array(
                'rightClick' => esc_html($options['toast_message_right_click']),
                'copy'       => esc_html($options['toast_message_copy']),
                'drag'       => esc_html($options['toast_message_drag']),
                'shortcuts'  => esc_html($options['toast_message_shortcuts']),
                'devtools'   => esc_html($options['toast_message_devtools']),
            ),
        );

        wp_localize_script('wp-content-shield', 'wpContentShieldConfig', $config);
    }

    public static function inject_critical_head_protection() {
        if (!WP_Content_Shield_Core::is_protection_active()) {
            return;
        }

        $options = WP_Content_Shield_Core::get_options();
        ?>
        <!-- WP Content Shield Pro - Early Head Defense Layer -->
        <style id="wp-shield-early-protection">
            <?php if (!empty($options['disable_text_selection'])) : ?>
            /* Browser engine level text selection lock (even if JS is disabled) */
            html, body, *:not(input):not(textarea):not([contenteditable="true"]) {
                -webkit-touch-callout: none !important;
                -webkit-user-select: none !important;
                -khtml-user-select: none !important;
                -moz-user-select: none !important;
                -ms-user-select: none !important;
                user-select: none !important;
            }
            <?php endif; ?>

            <?php if (!empty($options['disable_image_drag'])) : ?>
            /* Prevent dragging elements via pure CSS */
            img, picture, svg, video, a {
                -webkit-user-drag: none !important;
                user-drag: none !important;
            }
            <?php endif; ?>

            <?php if (!empty($options['disable_print'])) : ?>
            /* Prevent printing and saving as PDF */
            @media print {
                html, body {
                    display: none !important;
                    visibility: hidden !important;
                }
                body::after {
                    content: "Printing and saving as PDF is prohibited due to copyright restrictions." !important;
                    display: block !important;
                    visibility: visible !important;
                    font-size: 24px !important;
                    font-weight: bold !important;
                    text-align: center !important;
                    margin-top: 50px !important;
                    color: #d32f2f !important;
                }
            }
            <?php endif; ?>
        </style>
        <?php if (!empty($options['anti_iframe'])) : ?>
        <script>
            // Early Frame-Buster - Prevents clickjacking and third-party iframe embedding
            if (window.top !== window.self) {
                try {
                    window.top.location = window.self.location;
                } catch (e) {
                    window.location = "about:blank";
                }
            }
        </script>
        <?php endif; ?>

        <?php if (!empty($options['enable_anti_offline'])) : 
            $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
        ?>
        <!-- Anti-Offline / Anti-Save Self-Destruct Layer -->
        <script id="wp-shield-anti-offline">
            (function() {
                try {
                    var isLocal = window.location.protocol === 'file:';
                    var curHost = window.location.hostname;
                    var origHost = "<?php echo esc_js($site_host); ?>";
                    var isMismatch = Boolean(curHost && origHost && curHost !== origHost && curHost !== 'localhost' && curHost !== '127.0.0.1');

                    if (isLocal || isMismatch) {
                        document.documentElement.innerHTML = '<head><meta charset="UTF-8"><title>Protected Content</title><style>body{background:#090d16;color:#f87171;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;padding:20px;box-sizing:border-box;}.card{background:#1e293b;padding:40px;border-radius:16px;border:1px solid #ef4444;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);max-width:540px;}.icon{font-size:52px;margin-bottom:15px;}h2{color:#f87171;margin-top:0;}p{color:#cbd5e1;line-height:1.6;font-size:15px;}</style></head><body><div class="card"><div class="icon">🛡️</div><h2>Protected Content</h2><p>This web page is protected by copyright law and cannot be saved locally or viewed offline.<br><br><em>Please access the content via the official live website.</em></p></div></body>';
                        if (window.stop) { window.stop(); }
                    }
                } catch(e) {}
            })();
        </script>
        <?php endif; ?>
        <?php
    }

    public static function add_body_classes($classes) {
        if (WP_Content_Shield_Core::is_protection_active()) {
            $classes[] = 'wp-shield-protected';
            $options = WP_Content_Shield_Core::get_options();
            if (!empty($options['disable_text_selection'])) {
                $classes[] = 'wp-shield-no-select';
            }
            if (!empty($options['disable_image_drag'])) {
                $classes[] = 'wp-shield-no-drag';
            }
            if (!empty($options['disable_print'])) {
                $classes[] = 'wp-shield-no-print';
            }
        }
        return $classes;
    }

    public static function send_security_headers() {
        if (headers_sent()) {
            return;
        }

        $options = WP_Content_Shield_Core::get_options();

        if (!empty($options['anti_iframe'])) {
            header('X-Frame-Options: SAMEORIGIN');
        }
    }

    public static function protect_rss_content($content) {
        $options = WP_Content_Shield_Core::get_options();
        if (empty($options['protect_rss_feed'])) {
            return $content;
        }

        // Truncate full articles to excerpt and attach canonical link for automated scrapers
        $excerpt = wp_trim_words(strip_shortcodes(strip_tags($content)), 45, '...');
        $source_link = sprintf(
            '<p><a href="%s" rel="bookmark">Read the full article on the website &raquo;</a></p>',
            esc_url(get_permalink())
        );

        return $excerpt . $source_link;
    }
}
