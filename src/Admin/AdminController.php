<?php
namespace RedSea\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\CRM\LeadManager;
use RedSea\RAG\KnowledgeBaseManager;
use RedSea\Radar\LeadRadarEngine;
use RedSea\Providers\LLMProviderManager;
use RedSea\Gateway\WhatsAppGateway;

/**
 * AdminController - Administrative Routing, Menus & Control Panel Controller
 * Handles WordPress admin menu registration, asset enqueuing, and tab views routing.
 */
class AdminController {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
    }

    /**
     * Register Top-Level Menu & Submenus
     */
    public function register_admin_menu() {
        add_menu_page(
            'Red Sea AI Engine Pro',
            'Red Sea AI Pro',
            'manage_options',
            'redsea-ai-engine',
            [$this, 'render_dashboard'],
            'dashicons-superhero-alt',
            25
        );
    }

    /**
     * Handle Admin Actions (e.g. settings save, CSV export)
     */
    public function handle_admin_actions() {
        // CSRF and capability checks handled per tab action
    }

    /**
     * Render Master Dashboard View
     */
    public function render_dashboard() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'redsea-ai-engine'));
        }

        // Delegate to main render engine for full 9-tab Taskhub UI
        if (class_exists('\RedSeaAIEngine')) {
            $engine = \RedSeaAIEngine::get_instance();
            if (method_exists($engine, 'render_crm_page')) {
                $engine->render_crm_page();
                return;
            }
        }
    }
}
