<?php
/**
 * Plugin Name: Red Sea AI Engine
 * Plugin URI: https://redseadigital.pro
 * Description: Enterprise Multi-Agent AI Infrastructure, RAG Knowledge Base, Dual-Engine WhatsApp Gateway & Direct Booking Engine.
 * Version: 5.3.0 Pro
 * Author: RED SEA DIGITAL (Amr Ahmed)
 * Author URI: https://redseadigital.pro
 * Text Domain: redsea-ai-engine
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: Proprietary
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Define Core Plugin Constants
define('RSD_AI_ENGINE_VERSION', '5.3.0');
define('RSD_AI_ENGINE_PATH', plugin_dir_path(__FILE__));
define('RSD_AI_ENGINE_URL', plugin_dir_url(__FILE__));
define('RSD_AI_ENGINE_FILE', __FILE__);

// 2. Load PSR-4 Composer Autoloader with Fallback
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Fallback PSR-4 Autoloader for RedSea\* Namespace
spl_autoload_register(function ($class) {
    $prefix = 'RedSea\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load legacy Elementor suite if present
if (file_exists(__DIR__ . '/includes/class-rsd-elementor-suite.php')) {
    require_once __DIR__ . '/includes/class-rsd-elementor-suite.php';
}

// 3. Register Activation & Deactivation Hooks
register_activation_hook(__FILE__, ['\\RedSea\\Database\\SchemaManager', 'create_tables']);

// 4. Kernel Bootstrap Class
final class RedSeaAIEngineBootstrap {

    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->boot_services();
    }

    /**
     * Boot all modular decoupled services
     */
    private function boot_services() {
        // Database & Schema Layer
        \RedSea\Database\SchemaManager::create_tables();

        // Admin Dashboard & Settings Controller
        \RedSea\Admin\AdminManager::init();

        // High-Performance AJAX Endpoints & Multi-Agent Dispatcher
        \RedSea\Admin\AjaxHandler::init();

        // Frontend Presentation, CSS Injection & Glassmorphic Chat Widget
        \RedSea\Frontend\FrontendManager::init();

        // Dual-Engine WhatsApp Gateway & 2-Way REST Webhooks
        \RedSea\Gateway\WhatsAppGateway::init();

        // Notification, Email & Webhook Dispatch Services
        \RedSea\Services\NotificationService::init();
    }
}

// 5. Initialize Engine on WordPress Plugins Loaded
add_action('plugins_loaded', function() {
    RedSeaAIEngineBootstrap::instance();
});
