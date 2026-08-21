<?php
namespace RedSea\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FrontendManager - Central Presentation & UI Layout Manager
 * Handles chat widget rendering, head scripts, master footers, and CSS injection.
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

        // Inject frontend AI floating chat widget
        add_action('wp_footer', [self::class, 'inject_frontend_widget'], 999);

        // Render universal master footer
        add_action('wp_footer', [self::class, 'render_universal_master_footer'], 100);
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
                var inp = document.getElementById('rsdInputField');
                if (inp) setTimeout(function() { inp.focus(); }, 120);
            } else {
                win.classList.remove('active');
                win.style.setProperty('display', 'none', 'important');
                win.style.setProperty('opacity', '0', 'important');
                win.style.setProperty('pointer-events', 'none', 'important');
            }
        };
        </script>
        <?php
    }

    /**
     * Universal Master Footer
     */
    public static function render_universal_master_footer() {
        if (is_admin()) return;
        $is_ar = (is_page(163) || strpos($_SERVER['REQUEST_URI'] ?? '', '/ar-') !== false);
        
        $desc = $is_ar ? 'استوديو متقدم لتصميم وتطوير أنظمة الحجز المباشر والمتاجر الإلكترونية المخصصة.' : 'Boutique digital architecture studio engineering direct booking systems, bespoke e-commerce & AI concierge infrastructure.';
        $status_txt = $is_ar ? 'جميع أنظمة المبيعات تعمل بنجاح' : 'All Revenue Systems Operational';
        $nav_title = $is_ar ? 'روابط التصفح' : 'NAVIGATION';
        $link_work = $is_ar ? 'أحدث أعمالنا' : 'Selected Work';
        $link_systems = $is_ar ? 'خدماتنا وأنظمتنا' : 'Capabilities & Systems';
        $link_studio = $is_ar ? 'عن الشركة' : 'Studio Manifesto';
        $link_privacy = $is_ar ? 'سياسة الخصوصية' : 'Privacy Policy';
        
        $contact_title = $is_ar ? 'التواصل المباشر' : 'STUDIO & CONCIERGE';
        $lbl_concierge = $is_ar ? 'الدعم والتواصل المباشر:' : 'Direct Concierge:';
        $lbl_locations = $is_ar ? 'الفروع:' : 'Locations:';
        $val_locations = $is_ar ? 'ساحل البحر الأحمر والقاهرة' : 'Red Sea Coast & Cairo';
        $lbl_wa = $is_ar ? 'تواصل عبر الواتساب:' : 'WhatsApp Direct:';
        
        $connect_title = $is_ar ? 'تواصل معنا' : 'CONNECT';
        $copyright = $is_ar ? '© 2026 RED SEA DIGITAL. جميع الحقوق محفوظة.' : '© 2026 RED SEA DIGITAL. All Rights Reserved. Quiet Luxury Digital Architecture Studio.';
        $sub_tagline = $is_ar ? 'تصميم وتطوير بدقة عالية وبدون أي اشتراكات شهرية.' : 'Architected with Precision & Zero SaaS Dependencies.';
        ?>
        <footer class="rsd-master-footer">
            <div class="rsd-footer-watermark" aria-hidden="true">RED SEA DIGITAL</div>
            <div class="rsd-footer-inner">
                <!-- Column 1: Brand & Positioning -->
                <div class="rsd-footer-col rsd-footer-brand">
                    <h3 class="rsd-footer-logo">RED SEA DIGITAL</h3>
                    <p class="rsd-footer-desc"><?php echo $desc; ?></p>
                    <div class="rsd-footer-status">
                        <span class="rsd-status-dot"></span>
                        <span class="rsd-status-text"><?php echo $status_txt; ?></span>
                    </div>
                </div>

                <!-- Column 2: Navigation -->
                <div class="rsd-footer-col">
                    <h4 class="rsd-footer-heading"><?php echo $nav_title; ?></h4>
                    <ul class="rsd-footer-links">
                        <li><a href="#work"><?php echo $link_work; ?></a></li>
                        <li><a href="#systems"><?php echo $link_systems; ?></a></li>
                        <li><a href="#studio"><?php echo $link_studio; ?></a></li>
                        <li><a href="/privacy-policy/"><?php echo $link_privacy; ?></a></li>
                    </ul>
                </div>

                <!-- Column 3: Contact & Location -->
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

                <!-- Column 4: Connect & Systems -->
                <div class="rsd-footer-col">
                    <h4 class="rsd-footer-heading"><?php echo $connect_title; ?></h4>
                    <ul class="rsd-footer-links">
                        <li><a href="https://linkedin.com" target="_blank" rel="noopener">LinkedIn ↗</a></li>
                        <li><a href="https://github.com" target="_blank" rel="noopener">GitHub ↗</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="rsd-footer-bottom">
                <div class="rsd-footer-bottom-inner">
                    <p class="rsd-copyright"><?php echo $copyright; ?></p>
                    <p class="rsd-footer-tagline"><?php echo $sub_tagline; ?></p>
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
