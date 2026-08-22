<?php
/**
 * RED SEA DIGITAL — Elementor WYSIWYG Suite & Direction-Aware Motion Engine
 */

if (!defined('ABSPATH')) exit;

class RSD_Elementor_Suite {

    public static function init() {
        add_action('elementor/elements/categories_registered', [__CLASS__, 'register_categories']);
        add_action('elementor/widgets/register', [__CLASS__, 'register_widgets']);
        add_action('wp_head', [__CLASS__, 'render_motion_engine_css'], 99);
        add_action('wp_footer', [__CLASS__, 'render_scroll_motion_engine_script'], 99);
    }

    public static function register_categories($elements_manager) {
        if (!is_object($elements_manager) || !method_exists($elements_manager, 'add_category')) return;
        $elements_manager->add_category(
            'redsea-digital-suite',
            [
                'title' => esc_html__('RED SEA DIGITAL — Luxury SaaS', 'redsea-ai-engine'),
                'icon'  => 'eicon-slideshow',
            ]
        );
    }

    public static function register_widgets($widgets_manager) {
        if (!class_exists('\Elementor\Widget_Base') || !is_object($widgets_manager)) return;

        $widgets_path = plugin_dir_path(__FILE__) . 'class-rsd-elementor-widgets.php';
        if (file_exists($widgets_path)) {
            require_once $widgets_path;
        }

        if (class_exists('RSD_Elementor_Hero_Widget')) $widgets_manager->register(new RSD_Elementor_Hero_Widget());
        if (class_exists('RSD_Elementor_Trust_Bar_Widget')) $widgets_manager->register(new RSD_Elementor_Trust_Bar_Widget());
        if (class_exists('RSD_Elementor_ROI_Calculator_Widget')) $widgets_manager->register(new RSD_Elementor_ROI_Calculator_Widget());
        if (class_exists('RSD_Elementor_Roadmap_Widget')) $widgets_manager->register(new RSD_Elementor_Roadmap_Widget());
        if (class_exists('RSD_Elementor_Testimonials_Widget')) $widgets_manager->register(new RSD_Elementor_Testimonials_Widget());
        if (class_exists('RSD_Elementor_Matrix_Widget')) $widgets_manager->register(new RSD_Elementor_Matrix_Widget());
        if (class_exists('RSD_Elementor_FAQ_Widget')) $widgets_manager->register(new RSD_Elementor_FAQ_Widget());
        if (class_exists('RSD_Elementor_Cal_Booking_Widget')) $widgets_manager->register(new RSD_Elementor_Cal_Booking_Widget());
        if (class_exists('RSD_Elementor_Final_CTA_Widget')) $widgets_manager->register(new RSD_Elementor_Final_CTA_Widget());
    }

    public static function render_motion_engine_css() {
        ?>
        <style id="rsd-scroll-motion-engine-css">
        /* ==========================================================================
           SCROLL-DRIVEN MOTION ENGINE (FADE IN ON SCROLL DOWN & GENTLE UP DRIFT)
           ========================================================================== */
        .rsd-scroll-reveal {
            opacity: 0 !important;
            transform: translateY(28px) scale(0.985) !important;
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1) !important;
            will-change: opacity, transform !important;
        }
        .rsd-scroll-reveal.rsd-in-view {
            opacity: 1 !important;
            transform: translateY(0) scale(1) !important;
        }
        body[data-scroll-dir='up'] .rsd-scroll-reveal:not(.rsd-in-view) {
            transform: translateY(-20px) scale(0.985) !important;
        }
        .rsd-stagger-1 { transition-delay: 0.06s !important; }
        .rsd-stagger-2 { transition-delay: 0.12s !important; }
        .rsd-stagger-3 { transition-delay: 0.18s !important; }
        .rsd-stagger-4 { transition-delay: 0.24s !important; }
        </style>
        <?php
    }

    public static function render_scroll_motion_engine_script() {
        ?>
        <script id="rsd-scroll-motion-engine-js">
        (function() {
            var lastScrollY = window.pageYOffset || document.documentElement.scrollTop;
            var ticking = false;

            function updateScrollDirection() {
                var currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
                var dir = (currentScrollY > lastScrollY) ? 'down' : 'up';
                document.body.setAttribute('data-scroll-dir', dir);
                lastScrollY = (currentScrollY <= 0) ? 0 : currentScrollY;
                ticking = false;
            }

            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(updateScrollDirection);
                    ticking = true;
                }
            }, { passive: true });

            if ('IntersectionObserver' in window) {
                var observerOptions = {
                    root: null,
                    rootMargin: '0px 0px -30px 0px',
                    threshold: 0.08
                };

                var motionObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('rsd-in-view');
                        } else {
                            var rect = entry.target.getBoundingClientRect();
                            if (rect.top > window.innerHeight || rect.bottom < 0) {
                                entry.target.classList.remove('rsd-in-view');
                            }
                        }
                    });
                }, observerOptions);

                function attachScrollMotion() {
                    var selectors = [
                        '.rsd-saas-pill',
                        '.rsd-saas-h1',
                        '.rsd-saas-subtext',
                        '.rsd-hero-showcase-wrapper',
                        '.rsd-saas-cta-group',
                        '.rsd-hero-trust-bar',
                        '.rsd-roi-box',
                        '.rsd-modular-card',
                        '.rsd-protocol-card',
                        '.rsd-t-card',
                        '.rsd-unified-matrix-card',
                        '.rsd-cal-frame-wrapper',
                        '.rsd-saas-cta-sec'
                    ];

                    var elements = document.querySelectorAll(selectors.join(', '));
                    elements.forEach(function(el, idx) {
                        el.classList.add('rsd-scroll-reveal');
                        if (el.classList.contains('rsd-protocol-card') || el.classList.contains('rsd-modular-card')) {
                            var staggerClass = 'rsd-stagger-' + ((idx % 4) + 1);
                            el.classList.add(staggerClass);
                        }
                        motionObserver.observe(el);
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', attachScrollMotion);
                } else {
                    attachScrollMotion();
                }

                if (window.elementorFrontend && window.elementorFrontend.hooks) {
                    window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function() {
                        attachScrollMotion();
                    });
                }
            }
        })();
        </script>
        <?php
    }
}

add_action('plugins_loaded', ['RSD_Elementor_Suite', 'init'], 20);
