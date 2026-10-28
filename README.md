# 🛡️ WP Content Shield Pro

> **Enterprise-Grade Anti-Copy, Anti-Theft & Content Theft Protection Plugin for WordPress.**

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-green.svg)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)

**WP Content Shield Pro** is a comprehensive multi-layered security and anti-plagiarism system designed to protect your articles, exclusive content, source code, and media assets against copying, scraping, dragging, printing, and developer inspection.

---

## ⚡ Highlights

- **Multi-Layer Defense:** Enforces rules via early `<head>` CSS (active even if visitors disable JavaScript), JavaScript capture-phase event listeners, and dynamic DOM MutationObservers.
- **Silent Right-Click Protection:** Silently suppresses context menus across all elements and images without irritating visitors with intrusive popups (can optionally be enabled via settings).
- **Text Selection & Highlight Blocker:** Prevents highlighting, dragging, double-clicking, and triple-clicking text across all modern browsers and mobile touch devices.
- **Image & Asset Shield:** Disables image drag-and-drop to desktop or new tabs, applies protective DOM layers, and disables HTML5 video/audio download controls.
- **Keyboard Shortcut Defense:** Disables `Ctrl+C`, `Ctrl+X`, `Ctrl+A`, `Ctrl+U` (View Source), `Ctrl+S` (Save Page), `Ctrl+P` (Print / PDF), and DevTools hotkeys (`F12`, `Ctrl+Shift+I/J/C`).
- **Active DevTools Detector:** Monitors console activity via viewport dimension delta analysis, flushes console logs, and outputs copyright warnings.
- **Scraper & Bot Defense:** Includes an anti-iFrame Frame Buster (`X-Frame-Options: SAMEORIGIN`) and automatically truncates RSS feeds into excerpts with canonical back-links.
- **Form Usability:** Intelligent form exemption allows visitors to type, select, and edit text inside search bars, login boxes, and comment forms.
- **Interactive Live Sandbox:** Test your settings in real time directly from the WordPress administration dashboard.

---

## 🚀 Features Breakdown

### 🖱️ 1. Mouse & Selection Protection
| Feature | Description |
| :--- | :--- |
| **Silent Right-Click Blocking** | Disables context menus without displaying annoying alerts by default. |
| **Text Selection Prevention** | Injects strict CSS `user-select: none !important;` and intercepts `selectstart` & `selectionchange` events. |
| **Clipboard & Copy Interception** | Automatically clears clipboard data upon copy or cut attempts. |
| **Select All Lock** | Disables `Ctrl + A` / `Cmd + A` shortcuts across the document. |
| **Form Usability Exception** | Preserves normal text editing and selection inside `<input>`, `<textarea>`, and contenteditable areas. |

### 🖼️ 2. Images & Media Protection
| Feature | Description |
| :--- | :--- |
| **Drag & Drop Prevention** | Blocks dragging images and media to desktop or folder (`draggable="false"`). |
| **Link Dragging Defense** | Prevents dragging hyperlinks and content blocks into new tabs. |
| **Protective Image Overlay** | Adds DOM-level protection over images to prevent direct saving. |
| **Media Download Lock** | Enforces `controlsList="nodownload noplaybackrate"` on HTML5 video and audio tags. |
| **Dynamic MutationObserver** | Automatically binds protection to newly loaded images and content (Infinite Scroll, Ajax). |

### ⌨️ 3. Shortcuts & Developer Inspection Defense
| Feature | Description |
| :--- | :--- |
| **View Page Source Lock** | Blocks `Ctrl + U` and `Cmd + Opt + U`. |
| **Save Page Lock** | Blocks `Ctrl + S` and `Cmd + S` page saving. |
| **Print & PDF Lock** | Disables `Ctrl + P`, `window.print()`, and blanks pages during print requests via `@media print`. |
| **DevTools Hotkeys Lock** | Disables `F12`, `Ctrl + Shift + I/J/C`, and `Ctrl + Shift + K`. |
| **DevTools Detection** | Viewport delta analysis triggers console clearing and security banners. |

### 🌐 4. Advanced Security & Scrapers Defense
| Feature | Description |
| :--- | :--- |
| **Anti-iFrame Frame Buster** | Blocks clickjacking and unauthorized embedding inside third-party iframes (`X-Frame-Options: SAMEORIGIN`). |
| **RSS Feed Protection** | Converts full RSS feed items into brief summaries with canonical source links to deter scraper bots. |
| **Clipboard Watermark** | (Optional) Automatically appends your website's canonical source link and copyright notice if text is copied. |

### 🔔 5. Modern Glassmorphism Toast Alerts
- Floating animated notification banner on blocked actions.
- 4 built-in themes: **Dark Modern**, **Glassmorphism (Frosted Glass)**, **Light Minimal**, and **Danger Red Alert**.
- Configurable display duration (ms) and custom alert messages.

### 👥 6. Role & Page Exemptions
- **Administrator Exemption:** Logged-in administrators bypass all protections so site owners can manage and edit their website without restrictions.
- **Logged-In Users:** Optional toggle to exempt registered members.
- **Specific Page & Post IDs:** Comma-separated list of IDs to exclude (e.g. `12, 45, 108`).
- **Post Types Exemption:** Exclude custom post types (e.g. WooCommerce products, documentation).

---

## 📥 Installation

### Method 1: Upload via WordPress Admin Dashboard (.zip)
1. Download `wp-content-shield.zip` from the [Releases](https://github.com/ferhat/wp-content-shield/releases) section.
2. In your WordPress dashboard, navigate to **Plugins > Add New > Upload Plugin**.
3. Select the downloaded `.zip` file and click **Install Now**.
4. Click **Activate Plugin**.
5. Access the configuration panel from the sidebar menu: **Content Shield**.

### Method 2: Manual FTP / Directory Installation
1. Clone or extract the `wp-content-shield` folder into your site's plugin directory:
   ```bash
   wp-content/plugins/wp-content-shield/
   ```
2. In your WordPress admin dashboard, navigate to **Plugins** and activate **WP Content Shield Pro**.

---

## 📂 Project Architecture

```
wp-content-shield/
├── admin/
│   ├── css/
│   │   └── admin.css          # Modern SaaS-style admin dashboard styling
│   └── js/
│       └── admin.js           # Tab navigation, quick presets, and sandbox logic
├── includes/
│   ├── class-shield-admin.php # Settings registration, menu handler, and UI rendering
│   ├── class-shield-core.php  # Default options, sanitization, and exemption validators
│   └── class-shield-public.php# Script/style enqueuing, early head CSS injection, and headers
├── public/
│   ├── css/
│   │   └── content-shield.css # CSS user-select locks, print blockers, and toast themes
│   └── js/
│       └── content-shield.js  # Capture-phase event interceptors and DevTools detector
├── .gitignore
├── LICENSE                    # GNU General Public License v2.0
├── README.md                  # Plugin documentation
└── wp-content-shield.php      # Main bootstrap plugin file
```

---

## ⚡ Quick Presets

The administration panel features one-click preset buttons:
- **🔒 Maximum Protection:** Locks every vector (right-click, selection, devtools, media, shortcuts, scrapers).
- **⚖️ Balanced Protection:** Recommended for general websites; protects all content while keeping form fields accessible and maintaining an optimal visitor experience.
- **🔄 Reset to Defaults:** Restores all settings to factory recommended defaults.

---

## 🧪 Interactive Live Sandbox

The plugin includes an embedded sandbox within the administration dashboard. Test your settings, notification themes, right-click suppression, and text selection immediately without opening a new browser tab or incognito session.

---

## 📄 License

This project is licensed under the **GNU General Public License v2.0 (GPL-2.0)** - see the [LICENSE](LICENSE) file for details. 100% compliant with WordPress open-source guidelines.
