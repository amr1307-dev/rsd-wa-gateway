<?php
/**
 * Plugin Name: Red Sea AI Chatbot Widget (Next-Gen SaaS UX)
 * Plugin URI: https://redseadigital.pro
 * Description: Cutting-edge modern Glassmorphic AI Chatbot Widget with Visual Viewport Mobile Keyboard Handling, Interactive Choice Chips, and Multi-Provider AI Routing.
 * Version: 4.0.0
 * Author: Red Sea Digital (Amr Ahmed)
 * Text Domain: redsea-ai-widget
 */

if (!defined('ABSPATH')) exit;

define('RSD_WIDGET_PATH', plugin_dir_path(__FILE__));
define('RSD_WIDGET_URL', plugin_dir_url(__FILE__));
define('RSD_WIDGET_VERSION', '4.0.0');

// Require Modular Includes
require_once RSD_WIDGET_PATH . 'includes/class-rsd-widget-api.php';
require_once RSD_WIDGET_PATH . 'includes/class-rsd-admin-page.php';

/**
 * Main Initialization Class
 */
class RedSeaAIWidgetPlugin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register hooks
        add_action('init', [$this, 'init_plugin']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp_footer', [$this, 'render_frontend_widget']);

        // Initialize Admin & REST API Modules
        RSD_Widget_API::init();
        if (is_admin()) {
            RSD_Admin_Page::init();
        }
    }

    public function init_plugin() {
        load_plugin_textdomain('redsea-ai-widget', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function enqueue_frontend_assets() {
        $enabled = get_option('rsd_widget_enabled', '1');
        if ($enabled !== '1') return;

        // Enqueue CSS
        wp_enqueue_style(
            'rsd-widget-style',
            RSD_WIDGET_URL . 'assets/css/widget-style.css',
            [],
            RSD_WIDGET_VERSION
        );

        // Enqueue JS Engine
        wp_enqueue_script(
            'rsd-widget-engine',
            RSD_WIDGET_URL . 'assets/js/widget-engine.js',
            ['jquery'],
            RSD_WIDGET_VERSION,
            true
        );

        // Pass Settings to Frontend JS
        $brand_color = get_option('rsd_brand_color', '#0284c7');
        $accent_color = get_option('rsd_accent_color', '#10b981');
        $welcome_msg = get_option('rsd_welcome_message', 'أهلاً بك في Red Sea Digital ✨ كيف يمكنني مساعدتك في استعادة أرباحك وتوفير عمولات المنصات اليوم؟');
        $chips_str = get_option('rsd_quick_chips', "طلب استشارة الباقة الملكية ($499)\nعرض تفاصيل باقة الفنادق\nحجز موعد تواصل عبر الواتساب");
        
        $chips_arr = array_values(array_filter(array_map('trim', explode("\n", $chips_str))));

        wp_localize_script('rsd_widget_engine', 'rsdWidgetConfig', [
            'restUrl' => esc_url_raw(rest_url('rsd-ai-widget/v1/chat')),
            'nonce'   => wp_create_nonce('wp_rest'),
            'brandColor' => esc_attr($brand_color),
            'accentColor' => esc_attr($accent_color),
            'welcomeMessage' => esc_html($welcome_msg),
            'quickChips' => $chips_arr,
            'agencyName' => esc_html(get_bloginfo('name')),
            'whatsappNumber' => esc_attr(get_option('rsd_whatsapp_phone', '01028803080'))
        ]);
    }

    public function render_frontend_widget() {
        $enabled = get_option('rsd_widget_enabled', '1');
        if ($enabled !== '1') return;

        $template_file = RSD_WIDGET_PATH . 'templates/widget-frontend.php';
        if (file_exists($template_file)) {
            include $template_file;
        }
    }
}

// Instantiate Plugin Singleton
RedSeaAIWidgetPlugin::get_instance();
