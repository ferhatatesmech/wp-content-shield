/**
 * WP Content Shield Pro - Ultra Anti-Copy & Anti-Theft Protection Engine
 * Capture-phase event interception & DOM-level defense
 */
(function() {
    'use strict';

    var cfg = window.wpContentShieldConfig || {};
    if (!cfg.active) {
        return;
    }

    var msgs = cfg.messages || {};

    // ========================================================
    // 1. Toast Notification Manager
    // ========================================================
    var toastTimeout = null;
    function showToast(message) {
        if (!cfg.showWarningToast || !message) return;

        var toast = document.getElementById('wp-shield-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'wp-shield-toast';
            toast.className = 'wp-shield-toast-container ' + (cfg.toastTheme || 'dark');
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            document.body.appendChild(toast);
        }

        toast.innerHTML = '<div class="wp-shield-toast-content">' +
            '<span class="wp-shield-toast-icon">🛡️</span>' +
            '<span class="wp-shield-toast-text">' + message + '</span>' +
            '</div>';

        // Animate in
        toast.classList.add('wp-shield-toast-visible');

        if (toastTimeout) {
            clearTimeout(toastTimeout);
        }

        toastTimeout = setTimeout(function() {
            toast.classList.remove('wp-shield-toast-visible');
        }, parseInt(cfg.toastDuration, 10) || 2800);
    }

    // ========================================================
    // 2. Helper Functions
    // ========================================================
    function isFormInput(el) {
        if (!el) return false;
        if (el.closest && el.closest('.wp-shield-allow-copy')) {
            return true;
        }
        var tag = el.tagName ? el.tagName.toLowerCase() : '';
        if (tag === 'input' || tag === 'textarea' || el.isContentEditable) {
            return true;
        }
        return false;
    }

    function blockEvent(e, message) {
        if (e.preventDefault) e.preventDefault();
        if (e.stopPropagation) e.stopPropagation();
        if (e.stopImmediatePropagation) e.stopImmediatePropagation();
        e.returnValue = false;
        if (message) {
            showToast(message);
        }
        return false;
    }

    // ========================================================
    // 3. Right-Click (Context Menu) Protection
    // ========================================================
    if (cfg.disableRightClick) {
        var onContextMenu = function(e) {
            if (cfg.allowInputSelection && isFormInput(e.target)) {
                return; // Allow inside form elements
            }
            // Silent by default unless toastOnRightClick is specifically enabled
            var msg = cfg.toastOnRightClick ? (msgs.rightClick || 'Right-clicking is disabled.') : null;
            return blockEvent(e, msg);
        };

        window.addEventListener('contextmenu', onContextMenu, { capture: true, passive: false });
        document.addEventListener('contextmenu', onContextMenu, { capture: true, passive: false });
    }

    // ========================================================
    // 4. Text Selection Protection
    // ========================================================
    if (cfg.disableTextSelection) {
        var onSelectStart = function(e) {
            if (cfg.allowInputSelection && isFormInput(e.target)) {
                return;
            }
            return blockEvent(e);
        };

        window.addEventListener('selectstart', onSelectStart, { capture: true, passive: false });
        document.addEventListener('selectstart', onSelectStart, { capture: true, passive: false });

        // Reset any accidental dynamic text selection
        document.addEventListener('selectionchange', function() {
            var active = document.activeElement;
            if (cfg.allowInputSelection && isFormInput(active)) {
                return;
            }
            var sel = window.getSelection ? window.getSelection() : null;
            if (sel && !sel.isCollapsed && sel.rangeCount > 0) {
                var range = sel.getRangeAt(0);
                var commonAncestor = range.commonAncestorContainer;
                if (commonAncestor && commonAncestor.nodeType === 3) {
                    commonAncestor = commonAncestor.parentNode;
                }
                if (cfg.allowInputSelection && commonAncestor && isFormInput(commonAncestor)) {
                    return;
                }
                sel.removeAllRanges();
            }
        });
    }

    // ========================================================
    // 5. Copy, Cut & Clipboard Interception
    // ========================================================
    if (cfg.disableCopy) {
        var onCopy = function(e) {
            if (cfg.allowInputSelection && isFormInput(e.target)) {
                return;
            }

            if (e.clipboardData) {
                if (cfg.watermarkClipboard && cfg.watermarkText) {
                    e.clipboardData.setData('text/plain', cfg.watermarkText);
                } else {
                    e.clipboardData.clearData();
                }
            }

            return blockEvent(e, msgs.copy || 'Copying content is prohibited.');
        };

        window.addEventListener('copy', onCopy, { capture: true, passive: false });
        document.addEventListener('copy', onCopy, { capture: true, passive: false });
    }

    if (cfg.disableCut) {
        var onCut = function(e) {
            if (cfg.allowInputSelection && isFormInput(e.target)) {
                return;
            }
            return blockEvent(e, 'Cutting content is prohibited.');
        };

        window.addEventListener('cut', onCut, { capture: true, passive: false });
        document.addEventListener('cut', onCut, { capture: true, passive: false });
    }

    if (cfg.disablePaste) {
        var onPaste = function(e) {
            if (cfg.allowInputSelection && isFormInput(e.target)) {
                return;
            }
            return blockEvent(e, 'Pasting content is prohibited.');
        };

        window.addEventListener('paste', onPaste, { capture: true, passive: false });
        document.addEventListener('paste', onPaste, { capture: true, passive: false });
    }

    // ========================================================
    // 6. Image & Media Drag and Drop Protection
    // ========================================================
    if (cfg.disableImageDrag) {
        var onDragStart = function(e) {
            var target = e.target;
            var tag = target && target.tagName ? target.tagName.toLowerCase() : '';

            var isMedia = tag === 'img' || tag === 'picture' || tag === 'svg' || tag === 'video' || tag === 'canvas';
            var isLink = cfg.disableLinkDrag && (tag === 'a' || (target.closest && target.closest('a')));

            if (isMedia || isLink) {
                return blockEvent(e, msgs.drag || 'Dragging images or content is disabled.');
            }
        };

        window.addEventListener('dragstart', onDragStart, { capture: true, passive: false });
        document.addEventListener('dragstart', onDragStart, { capture: true, passive: false });
    }

    // ========================================================
    // 7. Keyboard Shortcuts Defense
    // ========================================================
    var onKeyDown = function(e) {
        var isCtrl = e.ctrlKey || e.metaKey; // Windows Ctrl or Mac Command
        var isShift = e.shiftKey;
        var isAlt = e.altKey;
        var key = e.key ? e.key.toLowerCase() : '';
        var keyCode = e.keyCode || e.which;
        var target = e.target;
        var inInput = cfg.allowInputSelection && isFormInput(target);

        // F12 - DevTools
        if (cfg.disableDevtools && (keyCode === 123 || key === 'f12')) {
            return blockEvent(e, msgs.devtools || 'Developer tools are disabled.');
        }

        // Ctrl + Shift + I / J / C (DevTools Inspect & Console)
        if (cfg.disableDevtools && isCtrl && isShift && (key === 'i' || key === 'j' || key === 'c' || keyCode === 73 || keyCode === 74 || keyCode === 67)) {
            return blockEvent(e, msgs.devtools || 'Inspection tools are disabled.');
        }

        // Ctrl + Shift + K (Firefox Console) & Ctrl + Shift + E (Network)
        if (cfg.disableDevtools && isCtrl && isShift && (key === 'k' || key === 'e' || keyCode === 75 || keyCode === 69)) {
            return blockEvent(e, msgs.devtools || 'Developer console is disabled.');
        }

        // Mac Option + Cmd + I / J / C / U
        if (cfg.disableDevtools && isAlt && (e.metaKey || e.ctrlKey) && (key === 'i' || key === 'j' || key === 'c' || key === 'u')) {
            return blockEvent(e, msgs.devtools || 'Developer console is disabled.');
        }

        // Ctrl + U (View Source)
        if (cfg.disableViewSource && isCtrl && (key === 'u' || keyCode === 85)) {
            return blockEvent(e, msgs.shortcuts || 'Viewing page source is disabled.');
        }

        // Ctrl + S (Save Page)
        if (cfg.disableSavePage && isCtrl && (key === 's' || keyCode === 83)) {
            return blockEvent(e, msgs.shortcuts || 'Saving page is disabled.');
        }

        // Ctrl + P (Print)
        if (cfg.disablePrint && isCtrl && (key === 'p' || keyCode === 80)) {
            return blockEvent(e, msgs.shortcuts || 'Printing page is disabled.');
        }

        // Ctrl + A (Select All)
        if (cfg.disableSelectAll && isCtrl && (key === 'a' || keyCode === 65)) {
            if (!inInput) {
                return blockEvent(e, msgs.shortcuts || 'Selecting all is disabled.');
            }
        }

        // Ctrl + C (Copy)
        if (cfg.disableCopy && isCtrl && (key === 'c' || keyCode === 67)) {
            if (!inInput) {
                return blockEvent(e, msgs.copy || 'Copying content is prohibited.');
            }
        }

        // Ctrl + X (Cut)
        if (cfg.disableCut && isCtrl && (key === 'x' || keyCode === 88)) {
            if (!inInput) {
                return blockEvent(e, msgs.shortcuts || 'Cutting content is prohibited.');
            }
        }
    };

    window.addEventListener('keydown', onKeyDown, { capture: true, passive: false });
    document.addEventListener('keydown', onKeyDown, { capture: true, passive: false });

    // ========================================================
    // 8. Printing & PDF Export Interception
    // ========================================================
    if (cfg.disablePrint) {
        window.addEventListener('beforeprint', function() {
            document.body.style.display = 'none';
            alert('Printing this page is not permitted due to copyright protection.');
        });

        window.addEventListener('afterprint', function() {
            document.body.style.display = '';
        });

        try {
            window.print = function() {
                showToast('Printing is disabled.');
                return false;
            };
        } catch (e) {}
    }

    // ========================================================
    // 9. Media Shield & Dynamic DOM Observer
    // ========================================================
    function protectMediaElements(root) {
        var base = root || document;

        // Protect images
        if (cfg.disableImageDrag) {
            var imgs = base.querySelectorAll('img, picture, svg');
            for (var i = 0; i < imgs.length; i++) {
                imgs[i].setAttribute('draggable', 'false');
                imgs[i].setAttribute('oncontextmenu', 'return false;');
            }
        }

        // Protect audio and video downloads
        if (cfg.disableMediaDownload) {
            var media = base.querySelectorAll('video, audio');
            for (var j = 0; j < media.length; j++) {
                media[j].setAttribute('controlsList', 'nodownload noplaybackrate');
                media[j].setAttribute('disablePictureInPicture', 'true');
                media[j].setAttribute('oncontextmenu', 'return false;');
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            protectMediaElements(document);
        });
    } else {
        protectMediaElements(document);
    }

    // Dynamic content observer (Infinite Scroll, Ajax, etc.)
    if (window.MutationObserver) {
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                    for (var i = 0; i < mutation.addedNodes.length; i++) {
                        var node = mutation.addedNodes[i];
                        if (node.nodeType === 1) {
                            protectMediaElements(node);
                        }
                    }
                }
            });
        });

        observer.observe(document.documentElement, {
            childList: true,
            subtree: true
        });
    }

    // ========================================================
    // 10. Active DevTools Detector & Console Cleaner
    // ========================================================
    if (cfg.enableDevtoolsDetector) {
        var devtoolsOpen = false;
        var threshold = 160;

        // Viewport dimension delta analysis
        var checkDevTools = function() {
            var widthThreshold = window.outerWidth - window.innerWidth > threshold;
            var heightThreshold = window.outerHeight - window.innerHeight > threshold;
            var isDocked = widthThreshold || heightThreshold;

            if (isDocked && !devtoolsOpen) {
                devtoolsOpen = true;
                showToast(msgs.devtools || 'Developer tools detected! Content is protected.');
            } else if (!isDocked && devtoolsOpen) {
                devtoolsOpen = false;
            }
        };

        window.addEventListener('resize', checkDevTools);
        setInterval(checkDevTools, 1500);

        // Periodic console cleanup and copyright banner
        setInterval(function() {
            try {
                console.clear();
                console.log(
                    '%c🛡️ STOP! SECURITY & COPYRIGHT NOTICE %c\n\n' +
                    'All written content, designs, and media on this website are legally protected by copyright law.\n' +
                    'Unauthorized copying, reverse engineering, or data scraping is strictly prohibited.',
                    'color: #ffffff; background: #dc2626; font-size: 16px; font-weight: bold; padding: 6px 12px; border-radius: 4px;',
                    'color: #374151; font-size: 13px; font-family: sans-serif;'
                );
            } catch (e) {}
        }, 3000);
    }

})();
