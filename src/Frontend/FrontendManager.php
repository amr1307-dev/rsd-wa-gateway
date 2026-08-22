<?php
namespace RedSea\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FrontendManager - Central Presentation & UI Layout Manager
 * Handles chat widget rendering, universal headers/footers, CSS injection, and layout filters.
 */
class FrontendManager {

    /**
     * Initialize and bind all frontend actions and filters
     */
    public static function init() {
        // Enqueue high-performance uncompressed CSS
        add_action('wp_enqueue_scripts', [self::class, 'render_nuclear_centering_css'], 999);

        // Inject global chat head script
        add_action('wp_head', [self::class, 'inject_head_chat_script'], 999);

        // Inject universal master header
        add_action('wp_head', [self::class, 'inject_universal_master_header'], 2);

        // Inject frontend AI floating chat widget
        add_action('wp_footer', [self::class, 'inject_frontend_widget'], 999);

        // Render universal master footer
        add_action('wp_footer', [self::class, 'render_universal_master_footer'], 100);

        // Filter rendered HTML for 3D Liquid Glass Cards on Methodology & Manifesto containers
        add_filter('the_content', [self::class, 'filter_homepage_content'], 9999);
    }

    /**
     * Render uncompressed pristine layout CSS
     */
    public static function render_nuclear_centering_css() {
        $template_file = dirname(dirname(__DIR__)) . '/templates/frontend/nuclear-css.php';
        if (file_exists($template_file)) {
            include $template_file;
        }
    }

    /**
     * Inject global head script for input auto-resize & widget toggle
     */
    public static function inject_head_chat_script() {
        if (get_option('rsd_widget_enabled', '1') !== '1') return;
        ?>
        <script id="rsd-global-chat-head-script">
        window.autoResizeRsdInput = function(elem) {
            if (!elem) return;
            elem.style.height = '44px';
            var scrollH = elem.scrollHeight;
            if (scrollH > 120) {
                elem.style.height = '120px';
                elem.style.overflowY = 'auto';
            } else if (scrollH > 44) {
                elem.style.height = scrollH + 'px';
                elem.style.overflowY = 'hidden';
            } else {
                elem.style.height = '44px';
                elem.style.overflowY = 'hidden';
            }
        };

        window.closeRsdChatWidget = function(e) {
            if (e) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
            }
            var win = document.getElementById('rsdModalWindow');
            if (!win) return;
            win.classList.remove('active');
            win.style.setProperty('display', 'none', 'important');
            win.style.setProperty('opacity', '0', 'important');
            win.style.setProperty('visibility', 'hidden', 'important');
            win.style.setProperty('pointer-events', 'none', 'important');
        };

        window.toggleRsdChatWidget = function(e) {
            if (e) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
            }
            var win = document.getElementById('rsdModalWindow');
            if (!win) return;

            var isCurrentlyOpen = win.classList.contains('active') || (win.style.display === 'flex');
            if (!isCurrentlyOpen) {
                win.classList.add('active');
                win.style.setProperty('display', 'flex', 'important');
                win.style.setProperty('opacity', '1', 'important');
                win.style.setProperty('visibility', 'visible', 'important');
                win.style.setProperty('pointer-events', 'all', 'important');
                win.style.setProperty('z-index', '999999999', 'important');
                var inp = document.getElementById('rsdChatInput') || document.getElementById('rsdInputField');
                if (inp) setTimeout(function() { inp.focus(); }, 120);
            } else {
                window.closeRsdChatWidget(e);
            }
        };
        </script>
        <?php
    }

    /**
     * Universal Master Header
     */
    public static function inject_universal_master_header() {
        if (is_admin()) return;
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_ar = (strpos($request_uri, '/ar') !== false);

        $home_url = $is_ar ? 'https://redseadigital.pro/ar-home/' : 'https://redseadigital.pro/';
        $work_url = $is_ar ? 'https://redseadigital.pro/ar-work/' : 'https://redseadigital.pro/work/';
        
        $ar_url = 'https://redseadigital.pro/ar-home/';
        $en_url = 'https://redseadigital.pro/';

        if (strpos($request_uri, '/work') !== false || strpos($request_uri, '/ar-work') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-work/';
            $en_url = 'https://redseadigital.pro/work/';
        } elseif (strpos($request_uri, 'yallatrip') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-yallatrip/';
            $en_url = 'https://redseadigital.pro/yallatrip/';
        } elseif (strpos($request_uri, 'asl-leather') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-asl-leather/';
            $en_url = 'https://redseadigital.pro/asl-leather/';
        } elseif (strpos($request_uri, 'paradise-spa') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-paradise-spa/';
            $en_url = 'https://redseadigital.pro/paradise-spa/';
        } elseif (strpos($request_uri, 'life-pets') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-life-pets/';
            $en_url = 'https://redseadigital.pro/life-pets/';
        }

        $logo_url = 'https://redseadigital.pro/wp-content/uploads/2026/08/red_sea_digital_logo_ultra_cropped.webp';
        
        $nav_items_ar = '<li><a href="' . $home_url . '">الرئيسية</a></li><li><a href="' . $work_url . '">أعمالنا</a></li><li><a href="' . $home_url . '#process">آلية العمل</a></li><li><a href="' . $home_url . '#capabilities">القدرات</a></li><li><a href="' . $home_url . '#why-us">لماذا نحن</a></li>';
        $nav_items_en = '<li><a href="' . $home_url . '">Home</a></li><li><a href="' . $work_url . '">Selected Work</a></li><li><a href="' . $home_url . '#process">Process</a></li><li><a href="' . $home_url . '#capabilities">Capabilities</a></li><li><a href="' . $home_url . '#why-us">Why Us</a></li>';
        
        $nav_items = $is_ar ? $nav_items_ar : $nav_items_en;
        $btn_text = $is_ar ? 'المساعد ↗' : 'Concierge ↗';
        $dir = $is_ar ? 'rtl' : 'ltr';
        $ar_active = $is_ar ? 'active' : '';
        $en_active = !$is_ar ? 'active' : '';
        ?>
        <style id="rsd-master-header-suppress-theme-css">
            .entry-title, h1.entry-title, header.entry-header, .page-header, .site-main > h1:first-child,
            header.site-header:not(#rsdUniversalHeader), header.hello-header:not(#rsdUniversalHeader),
            .site-header:not(#rsdUniversalHeader), #site-header:not(#rsdUniversalHeader),
            .elementor-location-header:not(#rsdUniversalHeader),
            footer.site-footer:not(#rsd-master-footer), footer.hello-footer:not(#rsd-master-footer),
            .site-footer:not(#rsd-master-footer), #site-footer:not(#rsd-master-footer),
            .elementor-location-footer:not(#rsd-master-footer) {
                display: none !important;
            }

            #rsdUniversalHeader {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                height: 84px !important;
                z-index: 999999 !important;
                background: rgba(251, 251, 249, 0.96) !important;
                backdrop-filter: blur(16px) !important;
                -webkit-backdrop-filter: blur(16px) !important;
                border-bottom: 1px solid rgba(229, 229, 224, 0.8) !important;
                display: flex !important;
                align-items: center !important;
                box-sizing: border-box !important;
                margin: 0 !important;
                padding: 0 !important;
                direction: <?php echo $dir; ?> !important;
            }

            .rsd-master-header-container {
                width: 100% !important;
                max-width: 1320px !important;
                height: 100% !important;
                margin: 0 auto !important;
                padding: 0 24px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                box-sizing: border-box !important;
            }

            .rsd-master-logo-link {
                display: flex !important;
                align-items: center !important;
                text-decoration: none !important;
            }

            .rsd-master-logo-img {
                height: 64px !important;
                width: auto !important;
                max-height: 68px !important;
                object-fit: contain !important;
            }

            .rsd-master-nav {
                display: flex !important;
                align-items: center !important;
                gap: 28px !important;
                list-style: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .rsd-master-nav a {
                color: #0F172A !important;
                text-decoration: none !important;
                font-family: <?php echo $is_ar ? "'Cairo', 'Tajawal', sans-serif" : "Inter, system-ui, sans-serif"; ?> !important;
                font-weight: 600 !important;
                font-size: 0.95rem !important;
                transition: color 0.2s ease !important;
            }

            .rsd-master-nav a:hover {
                color: #2563EB !important;
            }

            .rsd-header-action-wrap {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }

            .rsd-master-btn-black {
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                color: #FFFFFF !important;
                border: none !important;
                padding: 10px 24px !important;
                border-radius: 50px !important;
                font-weight: 700 !important;
                font-size: 0.9rem !important;
                cursor: pointer !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 6px !important;
                box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25) !important;
                transition: all 0.25s ease !important;
            }

            .rsd-master-btn-black:hover {
                background: #2563EB !important;
                color: #0F172A !important;
                transform: translateY(-1px) !important;
            }

            .rsd-hamburger-btn {
                display: none !important;
                background: rgba(17, 17, 17, 0.06) !important;
                border: 1px solid rgba(17, 17, 17, 0.12) !important;
                border-radius: 12px !important;
                width: 44px !important;
                height: 44px !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                padding: 0 !important;
                transition: background 0.2s ease !important;
            }

            .rsd-hamburger-btn svg {
                width: 22px !important;
                height: 22px !important;
                fill: #0F172A !important;
            }

            #rsdMobileDrawer {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                background: rgba(17, 17, 17, 0.96) !important;
                backdrop-filter: blur(20px) !important;
                -webkit-backdrop-filter: blur(20px) !important;
                z-index: 9999999 !important;
                display: flex !important;
                flex-direction: column !important;
                padding: 24px 32px !important;
                box-sizing: border-box !important;
                opacity: 0 !important;
                pointer-events: none !important;
                transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
                direction: <?php echo $dir; ?> !important;
            }

            #rsdMobileDrawer.active {
                opacity: 1 !important;
                pointer-events: all !important;
            }

            .rsd-drawer-hdr {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                padding-bottom: 24px !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            }

            .rsd-drawer-close-btn {
                background: rgba(255, 255, 255, 0.1) !important;
                border: none !important;
                color: #FFFFFF !important;
                width: 40px !important;
                height: 40px !important;
                border-radius: 50% !important;
                font-size: 1.4rem !important;
                cursor: pointer !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .rsd-drawer-nav {
                display: flex !important;
                flex-direction: column !important;
                gap: 24px !important;
                margin-top: 40px !important;
                list-style: none !important;
                padding: 0 !important;
            }

            .rsd-drawer-nav a {
                color: #FFFFFF !important;
                text-decoration: none !important;
                font-size: 1.5rem !important;
                font-weight: 700 !important;
                font-family: <?php echo $is_ar ? "'Cairo', 'Tajawal', sans-serif" : "Inter, system-ui, sans-serif"; ?> !important;
                transition: color 0.2s ease !important;
            }

            .rsd-drawer-nav a:hover {
                color: #2563EB !important;
            }

            @media (max-width: 900px) {
                .rsd-master-nav, .rsd-master-btn-black { display: none !important; }
                .rsd-hamburger-btn { display: flex !important; }
                .rsd-master-header-container { padding: 0 16px !important; }
                .rsd-master-logo-img { height: 50px !important; }
            }
        </style>

        <script id="rsd-universal-header-js">
        (function() {
            window.toggleRsdMobileDrawer = function(e) {
                if (e) {
                    if (typeof e.preventDefault === 'function') e.preventDefault();
                    if (typeof e.stopPropagation === 'function') e.stopPropagation();
                }
                var drawer = document.getElementById('rsdMobileDrawer');
                if (!drawer) return;
                if (drawer.classList.contains('active')) {
                    drawer.classList.remove('active');
                } else {
                    drawer.classList.add('active');
                }
            };

            function injectMasterHeader() {
                if (document.getElementById('rsdUniversalHeader')) return;
                
                var headerHTML = '<div class="rsd-master-header-container">' +
                    '<a href="<?php echo $home_url; ?>" class="rsd-master-logo-link">' +
                        '<img src="<?php echo $logo_url; ?>" alt="Red Sea Digital Logo" class="rsd-master-logo-img" />' +
                    '</a>' +
                    '<nav><ul class="rsd-master-nav">' +
                        '<?php echo $nav_items; ?>' +
                        '<li style="display: inline-flex; align-items: center;">' +
                            '<div class="rsd-sleek-lang-toggle">' +
                                '<a href="<?php echo $ar_url; ?>" class="<?php echo $ar_active; ?>">AR</a>' +
                                '<span style="color:#cbd5e1;">|</span>' +
                                '<a href="<?php echo $en_url; ?>" class="<?php echo $en_active; ?>">EN</a>' +
                            '</div>' +
                        '</li>' +
                    '</ul></nav>' +
                    '<div class="rsd-header-action-wrap">' +
                        '<button onclick="var el=document.getElementById(&apos;rsd-booking-calendar&apos;);if(el){el.scrollIntoView({behavior:&apos;smooth&apos;});}else{window.toggleRsdChatWidget(event);}" class="rsd-master-btn-black">[ <?php echo $btn_text; ?> ]</button>' +
                        '<button onclick="window.toggleRsdMobileDrawer(event)" class="rsd-hamburger-btn" aria-label="Open Navigation Menu">' +
                            '<svg viewBox="0 0 24 24"><path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/></svg>' +
                        '</button>' +
                    '</div>' +
                '</div>';

                var headerElem = document.createElement('header');
                headerElem.id = 'rsdUniversalHeader';
                headerElem.innerHTML = headerHTML;

                var drawerHTML = '<div class="rsd-drawer-hdr">' +
                    '<a href="<?php echo $home_url; ?>"><img src="<?php echo $logo_url; ?>" style="height:44px; width:auto; filter:brightness(0) invert(1);" /></a>' +
                    '<div style="display:flex; align-items:center; gap:16px;">' +
                        '<div class="rsd-sleek-lang-toggle" style="background:rgba(255,255,255,0.1); border-color:rgba(197,160,89,0.5);">' +
                            '<a href="<?php echo $ar_url; ?>" class="<?php echo $ar_active; ?>" style="color:#fff !important;">AR</a>' +
                            '<span style="color:#64748b;">|</span>' +
                            '<a href="<?php echo $en_url; ?>" class="<?php echo $en_active; ?>" style="color:#fff !important;">EN</a>' +
                        '</div>' +
                        '<button onclick="window.toggleRsdMobileDrawer(event)" class="rsd-drawer-close-btn">✕</button>' +
                    '</div>' +
                '</div>' +
                '<ul class="rsd-drawer-nav">' +
                    '<?php echo $nav_items; ?>' +
                '</ul>' +
                '<div style="margin-top:auto; padding-top:24px;">' +
                    '<button onclick="window.toggleRsdMobileDrawer(event); setTimeout(function(){ window.toggleRsdChatWidget(); }, 200);" class="rsd-master-btn-black" style="width:100%; justify-content:center; padding:14px;">[ <?php echo $btn_text; ?> ]</button>' +
                '</div>';

                var drawerElem = document.createElement('div');
                drawerElem.id = 'rsdMobileDrawer';
                drawerElem.innerHTML = drawerHTML;

                if (document.body) {
                    document.body.insertBefore(headerElem, document.body.firstChild);
                    document.body.appendChild(drawerElem);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', injectMasterHeader);
            } else {
                injectMasterHeader();
            }
            setTimeout(injectMasterHeader, 300);
            setTimeout(injectMasterHeader, 1000);
        })();
        </script>
        <?php
    }

    /**
     * Filter Content for 3D Glass Cards & Homepage Layouts
     */
    public static function filter_homepage_content($content) {
        if (empty($content)) return $content;

        if (class_exists('\Elementor\Plugin')) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) {
                return $content;
            }
        }

        // Target Manifesto child containers
        $manifesto_ids = [
            'p39b5ag' => 'rsd-liquid-glass-manifesto-card rsd-standard-card',
            'yj5atgg' => 'rsd-liquid-glass-manifesto-card rsd-conventional-card'
        ];
        foreach ($manifesto_ids as $mid => $mcls) {
            $content = str_replace(
                'elementor-element-' . $mid . ' e-flex',
                'elementor-element-' . $mid . ' ' . $mcls . ' e-flex',
                $content
            );
        }

        return $content;
    }

    /**
     * Universal Master Footer
     */
    public static function render_universal_master_footer() {
        if (is_admin()) return;
        $is_ar = (is_page(163) || strpos($_SERVER['REQUEST_URI'] ?? '', '/ar-') !== false);
        
        $desc = $is_ar ? 'استوديو متقدم لتصميم وتطوير أنظمة الحجز المباشر والمتاجر الإلكترونية المخصصة.' : 'Boutique digital architecture studio engineering direct booking systems, bespoke e-commerce & AI concierge infrastructure.';
        $status_txt = $is_ar ? 'الأنظمة تعمل بكفاءة 100%' : 'All Systems Operational';
        $nav_title = $is_ar ? 'التنقل السريع' : 'Navigation';
        $link_work = $is_ar ? 'أعمالنا المختارة' : 'Selected Work';
        $link_systems = $is_ar ? 'الأنظمة والهندسة' : 'Systems & Engine';
        $link_studio = $is_ar ? 'عن الاستوديو' : 'The Studio';
        $link_privacy = $is_ar ? 'سياسة الخصوصية' : 'Privacy Policy';
        $contact_title = $is_ar ? 'التواصل والاستشارات' : 'Direct Inquiries';
        $lbl_concierge = $is_ar ? 'المكتب الخاص & المستشار:' : 'Private Office & Concierge:';
        $lbl_locations = $is_ar ? 'نطاق العمليات:' : 'Global Operations:';
        $val_locations = $is_ar ? 'الغردقة • القاهرة • دبي • لندن' : 'Hurghada • Cairo • Dubai • London';
        $lbl_wa = $is_ar ? 'الخط المباشر (واتساب):' : 'Direct Line (WhatsApp):';
        $connect_title = $is_ar ? 'الربط والشبكات' : 'Connect';
        $cr_text = $is_ar ? 'جميع الحقوق محفوظة. تم التطوير بواسطة استوديو Red Sea Digital.' : 'All rights reserved. Engineered by Red Sea Digital Architecture Studio.';
        ?>
        <footer id="rsd-master-footer" class="rsd-master-footer-wrap <?php echo $is_ar ? 'rsd-rtl' : 'rsd-ltr'; ?>">
            <div class="rsd-footer-container rsd-footer-inner">
                <div class="rsd-footer-col rsd-footer-brand-col">
                    <div class="rsd-footer-logo-wrap">
                        <img src="https://redseadigital.pro/wp-content/uploads/2026/08/red_sea_digital_logo_ultra_cropped.webp" alt="Red Sea Digital Logo" class="rsd-footer-logo" />
                    </div>
                    <p class="rsd-footer-desc"><?php echo $desc; ?></p>
                    <div class="rsd-footer-status">
                        <span class="rsd-status-dot"></span>
                        <span class="rsd-status-text"><?php echo $status_txt; ?></span>
                    </div>
                </div>

                <div class="rsd-footer-col">
                    <h4 class="rsd-footer-heading"><?php echo $nav_title; ?></h4>
                    <ul class="rsd-footer-links">
                        <li><a href="#work"><?php echo $link_work; ?></a></li>
                        <li><a href="#systems"><?php echo $link_systems; ?></a></li>
                        <li><a href="#studio"><?php echo $link_studio; ?></a></li>
                        <li><a href="/privacy-policy/"><?php echo $link_privacy; ?></a></li>
                    </ul>
                </div>

                <div class="rsd-footer-col">
                    <h4 class="rsd-footer-heading"><?php echo $contact_title; ?></h4>
                    <p class="rsd-footer-info">
                        <strong><?php echo $lbl_concierge; ?></strong><br>
                        <a href="mailto:concierge@redseadigital.pro" class="rsd-footer-email">concierge@redseadigital.pro</a>
                    </p>
                    <p class="rsd-footer-info" style="margin-top: 14px;">
                        <strong><?php echo $lbl_locations; ?></strong><br>
                        <span class="rsd-footer-text-muted"><?php echo $val_locations; ?></span>
                    </p>
                    <p class="rsd-footer-info" style="margin-top: 14px;">
                        <strong><?php echo $lbl_wa; ?></strong><br>
                        <a href="https://wa.me/201028803080" target="_blank" rel="noopener" class="rsd-footer-wa">+20 102 880 3080 ↗</a>
                    </p>
                </div>

                <div class="rsd-footer-col">
                    <h4 class="rsd-footer-heading"><?php echo $connect_title; ?></h4>
                    <ul class="rsd-footer-links">
                        <li><a href="https://linkedin.com" target="_blank" rel="noopener">LinkedIn ↗</a></li>
                        <li><a href="https://github.com" target="_blank" rel="noopener">GitHub ↗</a></li>
                    </ul>
                </div>
            </div>

            <div class="rsd-footer-bottom">
                <div class="rsd-footer-bottom-inner">
                    <p class="rsd-cr-text">© <?php echo date('Y'); ?> RED SEA DIGITAL. <?php echo $cr_text; ?></p>
                </div>
            </div>
        </footer>
        <?php
    }

    /**
     * Inject Floating Luxury AI Chat Widget
     */
    public static function inject_frontend_widget() {
        if (get_option('rsd_widget_enabled', '1') !== '1') return;

        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_ar = (strpos($request_uri, '/ar') !== false) || (isset($_GET['lang']) && $_GET['lang'] === 'ar');
        if (!$is_ar && function_exists('pll_current_language')) {
            $is_ar = (pll_current_language() === 'ar');
        }

        $whatsapp_phone = esc_attr(get_option('rsd_whatsapp_phone', '201028803080'));
        $chat_dir = $is_ar ? 'rtl' : 'ltr';

        $template_file = dirname(dirname(__DIR__)) . '/templates/frontend/chat-widget.php';
        if (file_exists($template_file)) {
            include $template_file;
        }
    }
}
