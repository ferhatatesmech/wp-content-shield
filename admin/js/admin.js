/**
 * WP Content Shield Pro - Admin Dashboard JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // ==========================================
        // 1. Tab Navigation
        // ==========================================
        $('.wp-shield-tab-nav').on('click', function(e) {
            e.preventDefault();
            var targetTab = $(this).data('tab');

            $('.wp-shield-tab-nav').removeClass('active');
            $(this).addClass('active');

            $('.wp-shield-tab-pane').removeClass('active');
            $('#' + targetTab).addClass('active');

            // Update URL hash
            window.location.hash = targetTab;
        });

        // Open hash tab on load if present
        if (window.location.hash) {
            var hashTab = window.location.hash.substring(1);
            var $targetNav = $('.wp-shield-tab-nav[data-tab="' + hashTab + '"]');
            if ($targetNav.length) {
                $targetNav.trigger('click');
            }
        }

        // ==========================================
        // 2. Master Switch Visual Feedback
        // ==========================================
        $('#enable_protection').on('change', function() {
            var isChecked = $(this).is(':checked');
            var $pill = $('.wp-shield-status-pill');
            if (isChecked) {
                $pill.removeClass('inactive').addClass('active');
                $pill.find('.status-label').text('PROTECTION ACTIVE');
            } else {
                $pill.removeClass('active').addClass('inactive');
                $pill.find('.status-label').text('PROTECTION DISABLED');
            }
        });

        // ==========================================
        // 3. Quick Presets
        // ==========================================
        $('.wp-shield-preset-btn').on('click', function(e) {
            e.preventDefault();
            var preset = $(this).data('preset');

            if (preset === 'max') {
                // Maximum Protection
                $('#enable_protection').prop('checked', true).trigger('change');
                $('#disable_right_click').prop('checked', true);
                $('#disable_text_selection').prop('checked', true);
                $('#disable_copy').prop('checked', true);
                $('#disable_cut').prop('checked', true);
                $('#disable_paste').prop('checked', true);
                $('#disable_select_all').prop('checked', true);
                $('#allow_input_selection').prop('checked', true);

                $('#disable_view_source').prop('checked', true);
                $('#disable_save_page').prop('checked', true);
                $('#disable_print').prop('checked', true);
                $('#disable_devtools').prop('checked', true);
                $('#enable_devtools_detector').prop('checked', true);
                $('#enable_debugger_freeze').prop('checked', true);
                $('#enable_devtools_overlay').prop('checked', true);
                $('#enable_anti_offline').prop('checked', true);

                $('#disable_image_drag').prop('checked', true);
                $('#protect_images_overlay').prop('checked', true);
                $('#disable_link_drag').prop('checked', true);
                $('#disable_media_download').prop('checked', true);

                $('#anti_iframe').prop('checked', true);
                $('#protect_rss_feed').prop('checked', true);
                $('#show_warning_toast').prop('checked', true);
                $('#toast_on_right_click').prop('checked', false);
                $('#exclude_admins').prop('checked', true);

                showAdminToast('🔒 Maximum Protection Preset applied! Remember to save changes.');
            } else if (preset === 'balanced') {
                // Balanced Protection
                $('#enable_protection').prop('checked', true).trigger('change');
                $('#disable_right_click').prop('checked', true);
                $('#disable_text_selection').prop('checked', true);
                $('#disable_copy').prop('checked', true);
                $('#disable_cut').prop('checked', true);
                $('#disable_paste').prop('checked', false);
                $('#disable_select_all').prop('checked', true);
                $('#allow_input_selection').prop('checked', true);

                $('#disable_view_source').prop('checked', true);
                $('#disable_save_page').prop('checked', true);
                $('#disable_print').prop('checked', true);
                $('#disable_devtools').prop('checked', true);
                $('#enable_devtools_detector').prop('checked', false);
                $('#enable_debugger_freeze').prop('checked', true);
                $('#enable_devtools_overlay').prop('checked', false);
                $('#enable_anti_offline').prop('checked', true);

                $('#disable_image_drag').prop('checked', true);
                $('#protect_images_overlay').prop('checked', true);
                $('#disable_link_drag').prop('checked', true);
                $('#disable_media_download').prop('checked', true);

                $('#anti_iframe').prop('checked', true);
                $('#protect_rss_feed').prop('checked', true);
                $('#show_warning_toast').prop('checked', true);
                $('#toast_on_right_click').prop('checked', false);
                $('#exclude_admins').prop('checked', true);

                showAdminToast('⚖️ Balanced Protection Preset applied! Remember to save changes.');
            } else if (preset === 'reset') {
                if (confirm('Are you sure you want to reset all settings to factory defaults?')) {
                    $('#enable_protection').prop('checked', true).trigger('change');
                    $('#disable_right_click').prop('checked', true);
                    $('#disable_text_selection').prop('checked', true);
                    $('#disable_copy').prop('checked', true);
                    $('#disable_cut').prop('checked', true);
                    $('#disable_paste').prop('checked', false);
                    $('#disable_select_all').prop('checked', true);
                    $('#allow_input_selection').prop('checked', true);
                    $('#disable_view_source').prop('checked', true);
                    $('#disable_save_page').prop('checked', true);
                    $('#disable_print').prop('checked', true);
                    $('#disable_devtools').prop('checked', true);
                    $('#enable_devtools_detector').prop('checked', true);
                    $('#enable_debugger_freeze').prop('checked', true);
                    $('#enable_devtools_overlay').prop('checked', true);
                    $('#enable_anti_offline').prop('checked', true);
                    $('#disable_image_drag').prop('checked', true);
                    $('#protect_images_overlay').prop('checked', true);
                    $('#disable_link_drag').prop('checked', true);
                    $('#disable_media_download').prop('checked', true);
                    $('#anti_iframe').prop('checked', true);
                    $('#protect_rss_feed').prop('checked', true);
                    $('#watermark_clipboard').prop('checked', false);
                    $('#show_warning_toast').prop('checked', true);
                    $('#toast_on_right_click').prop('checked', false);
                    $('#toast_theme').val('dark');
                    $('#exclude_admins').prop('checked', true);
                    $('#exclude_logged_in').prop('checked', false);

                    showAdminToast('🔄 Factory default settings restored. Remember to save changes.');
                }
            }
        });

        // ==========================================
        // 4. Admin Toast Notification Helper
        // ==========================================
        function showAdminToast(msg, theme) {
            var selectedTheme = theme || $('#toast_theme').val() || 'dark';
            var $toast = $('#wp-shield-admin-preview-toast');

            if (!$toast.length) {
                $('body').append('<div id="wp-shield-admin-preview-toast" class="wp-shield-toast-container"></div>');
                $toast = $('#wp-shield-admin-preview-toast');
            }

            $toast.attr('class', 'wp-shield-toast-container ' + selectedTheme);
            $toast.html('<div class="wp-shield-toast-content"><span class="wp-shield-toast-icon">🛡️</span><span class="wp-shield-toast-text">' + msg + '</span></div>');
            $toast.addClass('wp-shield-toast-visible');

            setTimeout(function() {
                $toast.removeClass('wp-shield-toast-visible');
            }, 2800);
        }

        // ==========================================
        // 5. Interactive Live Sandbox
        // ==========================================
        var $sandbox = $('#wp-shield-sandbox-area');

        // Sandbox right-click test
        $sandbox.on('contextmenu', function(e) {
            if ($(e.target).is('#sandbox-input')) return;
            e.preventDefault();
            if ($('#toast_on_right_click').is(':checked')) {
                var msg = $('#toast_message_right_click').val() || '⚠️ Right-clicking is disabled on this website.';
                showAdminToast(msg);
            }
        });

        // Sandbox image drag test
        $('#sandbox-test-img').on('dragstart', function(e) {
            e.preventDefault();
            var msg = $('#toast_message_drag').val() || '⚠️ Dragging images is disabled.';
            showAdminToast(msg);
        });

        // Sandbox text selection test
        $sandbox.on('selectstart', function(e) {
            if ($(e.target).is('#sandbox-input')) return;
            e.preventDefault();
        });

        // Sandbox copy test
        $sandbox.on('copy', function(e) {
            if ($(e.target).is('#sandbox-input')) return;
            e.preventDefault();
            var msg = $('#toast_message_copy').val() || '⚠️ Copying content is prohibited.';
            showAdminToast(msg);
        });

        // Test trigger button
        $('#sandbox-trigger-toast-btn').on('click', function(e) {
            e.preventDefault();
            var currentTheme = $('#toast_theme').val();
            showAdminToast('🛡️ Live Test: This is a sample content protection alert!', currentTheme);
        });

        // Live preview on theme change
        $('#toast_theme').on('change', function() {
            var currentTheme = $(this).val();
            showAdminToast('Theme selected: ' + currentTheme, currentTheme);
        });

    });
})(jQuery);
