<?php
namespace RedSea\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\Database\SchemaManager;
use RedSea\Agents\AgentFactory;
use RedSea\RAG\KnowledgeBaseManager;
use RedSea\CRM\LeadManager;
use RedSea\Radar\LeadRadarEngine;
use RedSea\Providers\LLMProviderManager;
use RedSea\Gateway\WhatsAppGateway;

/**
 * AdminManager - Central Enterprise Admin Dashboard & Management Controller
 * Handles admin menu routing, settings persistence for all 9 tabs, asset enqueueing, and template views.
 */
class AdminManager {

    /**
     * Initialize admin hooks and menu bindings
     */
    public static function init() {
        add_action('admin_menu', [self::class, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [self::class, 'remove_admin_footer_text'], 999);
    }

    /**
     * Register top-level WordPress Admin Menu Page
     */
    public static function add_admin_menu() {
        add_menu_page(
            'RED SEA AI Engine',
            'RED SEA AI Engine',
            'manage_options',
            'redsea-ai-engine',
            [self::class, 'render_admin_dashboard'],
            'dashicons-smart-machine',
            30
        );
    }

    /**
     * Remove footer text on Red Sea AI Engine screens
     */
    public static function remove_admin_footer_text() {
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'redsea-ai') !== false) {
            add_filter('admin_footer_text', '__return_empty_string', 99);
            add_filter('update_footer', '__return_empty_string', 99);
        }
    }

    /**
     * Main Admin Dashboard & Tab Router
     */
    public static function render_admin_dashboard() {
        global $wpdb;

        // Ensure Vector Store & Leads tables exist
        KnowledgeBaseManager::init_vector_store_table();
        SchemaManager::create_tables();

        // Handle POST submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('rsd_crm_settings_nonce');
            if (!current_user_can('manage_options')) wp_die('غير مصرح لك.');

            $tab = sanitize_text_field($_POST['active_tab'] ?? 'overview');

            // 1. WhatsApp Dual-Engine & Gateway Settings
            if (isset($_POST['rsd_save_settings']) || isset($_POST['rsd_whatsapp_api_url']) || isset($_POST['rsd_meta_phone_id'])) {
                if (isset($_POST['rsd_whatsapp_gateway_mode'])) {
                    update_option('rsd_whatsapp_gateway_mode', sanitize_text_field($_POST['rsd_whatsapp_gateway_mode']));
                }
                if (isset($_POST['rsd_meta_app_id'])) {
                    update_option('rsd_meta_app_id', sanitize_text_field($_POST['rsd_meta_app_id']));
                }
                if (isset($_POST['rsd_meta_phone_id'])) {
                    update_option('rsd_meta_phone_id', sanitize_text_field($_POST['rsd_meta_phone_id']));
                }
                if (isset($_POST['rsd_meta_waba_id'])) {
                    update_option('rsd_meta_waba_id', sanitize_text_field($_POST['rsd_meta_waba_id']));
                }
                if (isset($_POST['rsd_meta_access_token'])) {
                    update_option('rsd_meta_access_token', sanitize_text_field($_POST['rsd_meta_access_token']));
                }
                if (isset($_POST['rsd_meta_webhook_verify_token'])) {
                    update_option('rsd_meta_webhook_verify_token', sanitize_text_field($_POST['rsd_meta_webhook_verify_token']));
                }
                if (isset($_POST['rsd_whatsapp_phone'])) {
                    update_option('rsd_whatsapp_phone', sanitize_text_field($_POST['rsd_whatsapp_phone']));
                }
                if (isset($_POST['rsd_whatsapp_instance'])) {
                    update_option('rsd_whatsapp_instance', sanitize_text_field($_POST['rsd_whatsapp_instance']));
                }
                if (isset($_POST['rsd_whatsapp_api_url'])) {
                    update_option('rsd_whatsapp_api_url', esc_url_raw(trim($_POST['rsd_whatsapp_api_url'])));
                }
                if (isset($_POST['rsd_whatsapp_api_key'])) {
                    update_option('rsd_whatsapp_api_key', sanitize_text_field($_POST['rsd_whatsapp_api_key']));
                }
                echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم حفظ إعدادات محرك بوابة الواتساب بنجاح! 💾✨</strong></p></div>';
            }

            if (isset($_POST['rsd_create_custom_agent'])) {
                $agent_name    = sanitize_text_field($_POST['rsd_new_agent_name'] ?? '');
                $agent_mission = sanitize_textarea_field($_POST['rsd_new_agent_mission'] ?? '');
                if (!empty($agent_name) && !empty($agent_mission)) {
                    $new_agent = AgentFactory::create_custom_agent($agent_name, $agent_mission);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم إنشاء وتجهيز الوكيل الذكي [' . esc_html($agent_name) . '] وصياغة السيستم برومبت الخاص به بنجاح.</strong></p></div>';
                }
            }

            // 2. Delete Custom Agent
            if (isset($_POST['rsd_delete_custom_agent'])) {
                $del_id = sanitize_text_field($_POST['rsd_delete_agent_id'] ?? '');
                $custom_agents = get_option('rsd_custom_agents', []);
                if (isset($custom_agents[$del_id])) {
                    unset($custom_agents[$del_id]);
                    update_option('rsd_custom_agents', $custom_agents);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #EF4444;"><p><strong>تم حذف الوكيل بنجاح.</strong></p></div>';
                }
            }

            // 3. Save File Content (RAG File Editor)
            if (isset($_POST['rsd_save_file_content'])) {
                $file_name = sanitize_text_field($_POST['rsd_edit_file_name'] ?? '');
                $content   = wp_unslash($_POST['rsd_edit_file_text'] ?? '');
                if (!empty($file_name)) {
                    KnowledgeBaseManager::save_file_content($file_name, $content);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم حفظ التعديلات على الملف [' . esc_html($file_name) . '] وإعادة فهرسته دلالياً بنجاح.</strong></p></div>';
                }
            }

            // 4. Delete RAG File
            if (isset($_POST['rsd_delete_file'])) {
                $file_name = sanitize_text_field($_POST['rsd_delete_file_name'] ?? '');
                if (!empty($file_name)) {
                    KnowledgeBaseManager::delete_file($file_name);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #EF4444;"><p><strong>تم حذف الملف ومسح مقاطعه من قاعدة المعرفة بنجاح.</strong></p></div>';
                }
            }

            // 5. Upload New RAG File
            if (isset($_FILES['rsd_upload_new_file']) && !empty($_FILES['rsd_upload_new_file']['name'])) {
                $uploaded = $_FILES['rsd_upload_new_file'];
                $ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['md', 'txt', 'json'])) {
                    $content = file_get_contents($uploaded['tmp_name']);
                    KnowledgeBaseManager::save_file_content($uploaded['name'], $content);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم رفع الملف [' . esc_html($uploaded['name']) . '] وفهرسته في قاعدة المعرفة بنجاح.</strong></p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #EF4444;"><p><strong>يرجى رفع ملفات بصيغة .md أو .txt أو .json فقط.</strong></p></div>';
                }
            }

            // 6. Save General Settings
            if (isset($_POST['rsd_save_settings'])) {
                if ($tab === 'models' || $tab === 'settings') {
                    update_option('rsd_ai_provider', sanitize_text_field($_POST['rsd_ai_provider'] ?? 'opencode'));
                    update_option('rsd_ai_model', sanitize_text_field($_POST['rsd_ai_model'] ?? 'deepseek-chat'));
                    update_option('rsd_opencode_api_key', sanitize_text_field($_POST['rsd_opencode_api_key'] ?? ''));
                    update_option('rsd_gemini_api_key', sanitize_text_field($_POST['rsd_gemini_api_key'] ?? ''));
                    update_option('rsd_deepseek_api_key', sanitize_text_field($_POST['rsd_deepseek_api_key'] ?? ''));
                    update_option('rsd_openai_api_key', sanitize_text_field($_POST['rsd_openai_api_key'] ?? ''));
                    update_option('rsd_llm_temperature', floatval($_POST['rsd_llm_temperature'] ?? 0.6));
                    update_option('rsd_llm_max_tokens', intval($_POST['rsd_llm_max_tokens'] ?? 850));
                    if (isset($_POST['rsd_widget_enabled_submitted'])) {
                        update_option('rsd_widget_enabled', isset($_POST['rsd_widget_enabled']) ? '1' : '0');
                    }
                } elseif ($tab === 'company') {
                    update_option('rsd_company_name', sanitize_text_field($_POST['rsd_company_name'] ?? 'RED SEA DIGITAL'));
                    update_option('rsd_company_slogan', sanitize_text_field($_POST['rsd_company_slogan'] ?? ''));
                    update_option('rsd_company_hq', sanitize_text_field($_POST['rsd_company_hq'] ?? 'الغردقة، البحر الأحمر، مصر'));
                    update_option('rsd_booking_url', esc_url_raw($_POST['rsd_booking_url'] ?? ''));
                    update_option('rsd_system_prompt', sanitize_textarea_field($_POST['rsd_system_prompt'] ?? ''));
                } elseif ($tab === 'concierge') {
                    update_option('rsd_sales_tone', sanitize_text_field($_POST['rsd_sales_tone'] ?? 'elite_closer'));
                    update_option('rsd_concierge_commission_preset', intval($_POST['rsd_concierge_commission_preset'] ?? 20));
                    update_option('rsd_enable_response_cache', isset($_POST['rsd_enable_response_cache']) ? '1' : '0');
                } elseif ($tab === 'rag') {
                    update_option('rsd_rag_chunk_size', intval($_POST['rsd_rag_chunk_size'] ?? 350));
                    update_option('rsd_rag_chunk_overlap', intval($_POST['rsd_rag_chunk_overlap'] ?? 50));
                    update_option('rsd_rag_similarity_threshold', floatval($_POST['rsd_rag_similarity_threshold'] ?? 0.65));
                } elseif ($tab === 'voice') {
                    update_option('rsd_voice_lang', sanitize_text_field($_POST['rsd_voice_lang'] ?? 'ar-SA'));
                    update_option('rsd_voice_rate', sanitize_text_field($_POST['rsd_voice_rate'] ?? '1.0'));
                    update_option('rsd_voice_pitch', sanitize_text_field($_POST['rsd_voice_pitch'] ?? '1.0'));
                } elseif ($tab === 'crm') {
                    update_option('rsd_whatsapp_phone', sanitize_text_field($_POST['rsd_whatsapp_phone'] ?? '201028803080'));
                    update_option('rsd_whatsapp_cloud_token', sanitize_text_field($_POST['rsd_whatsapp_cloud_token'] ?? ''));
                    update_option('rsd_whatsapp_phone_number_id', sanitize_text_field($_POST['rsd_whatsapp_phone_number_id'] ?? ''));
                    update_option('rsd_whatsapp_waba_id', sanitize_text_field($_POST['rsd_whatsapp_waba_id'] ?? ''));
                }
                echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم حفظ الإعدادات بنجاح. ✨</strong></p></div>';
            }
        }

        $active_tab   = sanitize_text_field($_GET['tab'] ?? 'overview');
        $edit_file    = sanitize_text_field($_GET['edit_file'] ?? '');
        $kb_files     = KnowledgeBaseManager::list_all_kb_files();
        $total_leads  = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rsd_bookings");
        $total_chunks = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rsd_vector_store");
        $recent_logs  = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}rsd_bookings ORDER BY id DESC LIMIT 50", ARRAY_A);
        $traces       = get_option('rsd_orchestration_logs', []);
        $all_agents   = AgentFactory::get_all_agents();
        ?>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cairo:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

            /* TASKHUB CLEAN ENTERPRISE THEME SYSTEM TOKENS */
            .rsd-taskhub-wrap {
                font-family: 'Cairo', 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif !important;
                color: #0F172A !important;
                background: #F8FAFC !important;
                margin: 20px 20px 20px 0 !important;
                direction: rtl !important;
                box-sizing: border-box !important;
            }

            .rsd-taskhub-layout {
                display: flex !important;
                gap: 24px !important;
                align-items: flex-start !important;
            }

            /* 1. CLEAN FLOATING WHITE SIDEBAR */
            .rsd-taskhub-sidebar {
                width: 280px !important;
                flex-shrink: 0 !important;
                background: #FFFFFF !important;
                border: 1px solid #E2E8F0 !important;
                border-radius: 18px !important;
                padding: 20px 14px !important;
                box-sizing: border-box !important;
                box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.03), 0 2px 6px -1px rgba(15, 23, 42, 0.02) !important;
            }

            .rsd-taskhub-content {
                flex: 1 !important;
                min-width: 0 !important;
            }

            .rsd-sidebar-header {
                padding: 4px 10px 18px 10px !important;
                border-bottom: 1px solid #F1F5F9 !important;
                margin-bottom: 14px !important;
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }

            .rsd-logo-badge {
                width: 42px !important;
                height: 42px !important;
                border-radius: 12px !important;
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                color: #FFFFFF !important;
                font-weight: 900 !important;
                font-size: 1.15rem !important;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25) !important;
            }

            .rsd-sidebar-title {
                font-size: 1.05rem !important;
                font-weight: 800 !important;
                color: #0F172A !important;
                margin: 0 !important;
                line-height: 1.2 !important;
            }

            .rsd-sidebar-sub {
                font-size: 0.76rem !important;
                color: #64748B !important;
                margin: 2px 0 0 0 !important;
                font-weight: 600 !important;
            }

            .rsd-nav-group-label {
                font-size: 0.7rem !important;
                font-weight: 800 !important;
                color: #94A3B8 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.06em !important;
                padding: 14px 10px 6px 10px !important;
            }

            .rsd-sidebar-link {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
                padding: 10px 14px !important;
                border-radius: 12px !important;
                color: #475569 !important;
                text-decoration: none !important;
                font-size: 0.88rem !important;
                font-weight: 600 !important;
                transition: all 0.18s ease-in-out !important;
                margin-bottom: 4px !important;
                border: 1px solid transparent !important;
            }

            .rsd-sidebar-link:hover {
                background: #F8FAFC !important;
                color: #0F172A !important;
            }

            .rsd-sidebar-link.active {
                background: #EFF6FF !important;
                color: #2563EB !important;
                border-color: #BFDBFE !important;
                font-weight: 800 !important;
                box-shadow: 0 1px 3px rgba(37, 99, 235, 0.08) !important;
            }

            .rsd-sidebar-link .rsd-nav-icon {
                font-size: 1.1rem !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 24px !important;
            }

            /* 2. TASKHUB CLEAN WHITE CARDS */
            .rsd-card {
                background: #FFFFFF !important;
                border: 1px solid #E2E8F0 !important;
                border-radius: 18px !important;
                padding: 24px !important;
                box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.03), 0 1px 2px -1px rgba(15, 23, 42, 0.02) !important;
                margin-bottom: 24px !important;
            }

            .rsd-card-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin-bottom: 20px !important;
                border-bottom: 1px solid #F1F5F9 !important;
                padding-bottom: 16px !important;
            }

            .rsd-card-title {
                font-size: 1.12rem !important;
                font-weight: 800 !important;
                color: #0F172A !important;
                margin: 0 !important;
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
            }

            /* 3. TASKHUB KPI STRIP */
            .rsd-telemetry-grid {
                display: grid !important;
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)) !important;
                gap: 16px !important;
                margin-bottom: 24px !important;
            }

            .rsd-telemetry-card {
                background: #FFFFFF !important;
                border: 1px solid #E2E8F0 !important;
                border-radius: 16px !important;
                padding: 20px !important;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03) !important;
                transition: transform 0.15s ease, box-shadow 0.15s ease !important;
            }

            .rsd-telemetry-card:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 6px 16px -2px rgba(15, 23, 42, 0.06) !important;
            }

            .rsd-telemetry-title {
                font-size: 0.82rem !important;
                font-weight: 700 !important;
                color: #64748B !important;
                margin-bottom: 6px !important;
            }

            .rsd-telemetry-val {
                font-size: 1.7rem !important;
                font-weight: 900 !important;
                color: #0F172A !important;
                letter-spacing: -0.02em !important;
                line-height: 1.2 !important;
            }

            .rsd-telemetry-sub {
                font-size: 0.78rem !important;
                color: #94A3B8 !important;
                margin-top: 4px !important;
                font-weight: 600 !important;
            }

            /* 4. TASKHUB FORM ELEMENTS */
            .rsd-form-group { margin-bottom: 20px !important; }
            .rsd-label {
                display: block !important;
                font-weight: 700 !important;
                font-size: 0.88rem !important;
                margin-bottom: 8px !important;
                color: #1E293B !important;
            }

            .rsd-input, .rsd-select, .rsd-textarea {
                width: 100% !important;
                padding: 10px 16px !important;
                border: 1.5px solid #E2E8F0 !important;
                border-radius: 12px !important;
                font-family: inherit !important;
                font-size: 0.9rem !important;
                color: #0F172A !important;
                background: #FFFFFF !important;
                box-sizing: border-box !important;
                transition: all 0.18s ease-in-out !important;
            }

            .rsd-input:focus, .rsd-select:focus, .rsd-textarea:focus {
                border-color: #2563EB !important;
                background: #FFFFFF !important;
                outline: none !important;
                box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.12) !important;
            }

            .rsd-btn {
                background: #2563EB !important;
                color: #FFFFFF !important;
                border: none !important;
                padding: 10px 20px !important;
                border-radius: 10px !important;
                font-weight: 700 !important;
                cursor: pointer !important;
                font-family: inherit !important;
                font-size: 0.88rem !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                transition: all 0.18s ease !important;
                box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2) !important;
                text-decoration: none !important;
            }

            .rsd-btn:hover {
                background: #1D4ED8 !important;
                color: #FFFFFF !important;
                transform: translateY(-1px) !important;
                box-shadow: 0 4px 10px rgba(37, 99, 235, 0.28) !important;
            }

            .rsd-btn-secondary {
                background: #F1F5F9 !important;
                color: #334155 !important;
                border: 1px solid #CBD5E1 !important;
                box-shadow: none !important;
            }

            .rsd-btn-secondary:hover {
                background: #E2E8F0 !important;
                color: #0F172A !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.04) !important;
            }

            .rsd-btn-danger {
                background: #FEF2F2 !important;
                color: #DC2626 !important;
                border: 1px solid #FECACA !important;
                box-shadow: none !important;
            }

            .rsd-btn-danger:hover {
                background: #FEE2E2 !important;
                color: #B91C1C !important;
            }

            /* 5. BADGES */
            .rsd-badge {
                display: inline-flex !important;
                align-items: center !important;
                gap: 5px !important;
                padding: 4px 10px !important;
                border-radius: 9999px !important;
                font-size: 0.78rem !important;
                font-weight: 700 !important;
            }

            .rsd-badge-success { background: #DCFCE7 !important; color: #15803D !important; border: 1px solid #BBF7D0 !important; }
            .rsd-badge-warning { background: #FEF3C7 !important; color: #B45309 !important; border: 1px solid #FDE68A !important; }
            .rsd-badge-info    { background: #EFF6FF !important; color: #1D4ED8 !important; border: 1px solid #BFDBFE !important; }
            .rsd-badge-purple  { background: #F3E8FF !important; color: #7E22CE !important; border: 1px solid #E9D5FF !important; }
            .rsd-badge-danger  { background: #FEE2E2 !important; color: #B91C1C !important; border: 1px solid #FECACA !important; }

            
            /* Card Container */
            .rsd-crm-card {
                background: #FFFFFF !important;
                border: 1px solid #E2E8F0 !important;
                border-radius: 16px !important;
                padding: 20px 24px !important;
                box-sizing: border-box !important;
                width: 100% !important;
            }

            /* Table Wrapper */
            .rsd-crm-table-container {
                width: 100% !important;
                overflow-x: hidden !important; /* Strictly disable horizontal scrollbar */
                margin-top: 16px !important;
            }

            /* Responsive Table Structure */
            table.rsd-crm-table {
                width: 100% !important;
                border-collapse: collapse !important;
                table-layout: fixed !important; /* Enforces strict percentage column boundaries */
                direction: rtl !important;
            }

            table.rsd-crm-table th, 
            table.rsd-crm-table td {
                padding: 10px 6px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #F1F5F9 !important;
                font-size: 12px !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }

            /* Exact 6-Column Percentage Distribution */
            table.rsd-crm-table th:nth-child(1), table.rsd-crm-table td:nth-child(1) { width: 5% !important; text-align: center !important; } /* # ID */
            table.rsd-crm-table th:nth-child(2), table.rsd-crm-table td:nth-child(2) { width: 18% !important; text-align: right !important; font-weight: 600 !important; } /* Client Name */
            table.rsd-crm-table th:nth-child(3), table.rsd-crm-table td:nth-child(3) { width: 17% !important; text-align: center !important; } /* Phone Badge */
            table.rsd-crm-table th:nth-child(4), table.rsd-crm-table td:nth-child(4) { width: 18% !important; text-align: center !important; } /* Service Type */
            table.rsd-crm-table th:nth-child(5), table.rsd-crm-table td:nth-child(5) { width: 26% !important; text-align: right !important; color: #475569 !important; } /* Message */
            table.rsd-crm-table th:nth-child(6), table.rsd-crm-table td:nth-child(6) { width: 16% !important; text-align: left !important; color: #94A3B8 !important; font-size: 11px !important; direction: ltr !important; } /* Date */

            /* Badge Styling */
            .rsd-phone-badge {
                direction: ltr !important;
                display: inline-block !important;
                max-width: 100% !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
                font-family: monospace !important;
                font-size: 10.5px !important;
                background: #DCFCE7 !important;
                color: #15803D !important;
                padding: 3px 8px !important;
                border-radius: 12px !important;
                box-sizing: border-box !important;
            }

            /* 6. TABLES */
            .rsd-table {
                width: 100% !important;
                border-collapse: separate !important;
                border-spacing: 0 !important;
                font-size: 0.88rem !important;
            }

            .rsd-table th {
                background: #F8FAFC !important;
                color: #475569 !important;
                font-weight: 700 !important;
                text-align: right !important;
                padding: 12px 16px !important;
                border-bottom: 1.5px solid #E2E8F0 !important;
            }

            .rsd-table td {
                padding: 14px 16px !important;
                border-bottom: 1px solid #F1F5F9 !important;
                color: #334155 !important;
                vertical-align: middle !important;
            }

            .rsd-table tr:hover td {
                background: #F8FAFC !important;
            }

            /* 7. MODAL DRAWER */
            .rsd-modal-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(4px);
                z-index: 999999;
                align-items: center;
                justify-content: center;
            }
            .rsd-modal-box {
                background: #FFFFFF;
                border-radius: 20px;
                width: 90%;
                max-width: 750px;
                max-height: 85vh;
                overflow-y: auto;
                padding: 24px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            }
        </style>

        <div class="rsd-taskhub-wrap">
            <div class="rsd-taskhub-layout">

                <!-- 1. TASKHUB CLEAN FLOATING SIDEBAR -->
                <div class="rsd-taskhub-sidebar">
                    <div class="rsd-sidebar-header">
                        <div class="rsd-logo-badge">✦</div>
                        <div>
                            <h3 class="rsd-sidebar-title">RED SEA DIGITAL</h3>
                            <p class="rsd-sidebar-sub">AI Engine • Enterprise v4.5</p>
                        </div>
                    </div>

                    <div class="rsd-nav-group-label">المحرك والأوركسترا</div>
                    <a href="?page=redsea-ai-engine&tab=overview" class="rsd-sidebar-link <?php echo $active_tab === 'overview' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">📊</span>
                        <span>نظرة عامة وتليمتري</span>
                    </a>
                    <a href="?page=redsea-ai-engine&tab=agents" class="rsd-sidebar-link <?php echo $active_tab === 'agents' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">🤖</span>
                        <span>منشئ الوكلاء وإدارتها</span>
                    </a>

                    <div class="rsd-nav-group-label">المعرفة والنشاط</div>
                    <a href="?page=redsea-ai-engine&tab=company" class="rsd-sidebar-link <?php echo $active_tab === 'company' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">🏛️</span>
                        <span>هوية المنشأة ومعلومات النشاط</span>
                    </a>
                    <a href="?page=redsea-ai-engine&tab=rag" class="rsd-sidebar-link <?php echo $active_tab === 'rag' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">📚</span>
                        <span>قاعدة المعرفة وإدارة الملفات</span>
                    </a>
                    <a href="?page=redsea-ai-engine&tab=concierge" class="rsd-sidebar-link <?php echo $active_tab === 'concierge' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">⚡</span>
                        <span>وكيل المبيعات وسرعة الرد</span>
                    </a>

                    <div class="rsd-nav-group-label">القنوات والنماذج</div>
                    <a href="?page=redsea-ai-engine&tab=models" class="rsd-sidebar-link <?php echo $active_tab === 'models' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">🧠</span>
                        <span>مركز النماذج والتقييمات</span>
                    </a>
                    <a href="?page=redsea-ai-engine&tab=voice" class="rsd-sidebar-link <?php echo $active_tab === 'voice' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">🎙️</span>
                        <span>استوديو الصوت التوليدي</span>
                    </a>

                    <div class="rsd-nav-group-label">التنقيب والمبيعات</div>
                    <a href="?page=redsea-ai-engine&tab=radar" class="rsd-sidebar-link <?php echo $active_tab === 'radar' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">🎯</span>
                        <span>رادار العملاء وصائد الصفقات</span>
                    </a>
                    <a href="?page=redsea-ai-engine&tab=crm" class="rsd-sidebar-link <?php echo $active_tab === 'crm' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">💬</span>
                        <span>الواتساب وسجل العملاء</span>
                    </a>
                </div>

                <!-- 2. TASKHUB CONTENT MAIN CONTAINER -->
                <div class="rsd-taskhub-content">

                    <!-- TAB 1: OVERVIEW & TELEMETRY -->
                    <?php if ($active_tab === 'overview'): ?>
                        <?php include dirname(dirname(__DIR__)) . '/templates/admin/tab-overview.php'; ?>

                    <!-- TAB 2: AGENTS FORGE -->
                    <?php elseif ($active_tab === 'agents'): ?>
                        <?php include dirname(dirname(__DIR__)) . '/templates/admin/tab-agents.php'; ?>

                    <!-- TAB 3: BUSINESS IDENTITY -->
                    <?php elseif ($active_tab === 'company'): ?>
                        <?php include dirname(dirname(__DIR__)) . '/templates/admin/tab-identity.php'; ?>

                    <!-- TAB 4: RAG KNOWLEDGE FILES -->
                    <?php elseif ($active_tab === 'rag'): ?>
                        <?php include dirname(dirname(__DIR__)) . '/templates/admin/tab-knowledge.php'; ?>

                    <!-- TAB 5: SALES CONCIERGE -->
                    <?php elseif ($active_tab === 'concierge'): ?>
                        <?php include dirname(dirname(__DIR__)) . '/templates/admin/tab-sales.php'; ?>

                    <!-- TAB 6: MODELS HUB -->
                    <?php elseif ($active_tab === 'models'): ?>
                        <?php include dirname(dirname(__DIR__)) . '/templates/admin/tab-models.php'; ?>

                    <!-- TAB 7: VOICE STUDIO -->
                    <?php elseif ($active_tab === 'voice'): ?>
                        <?php include dirname(dirname(__DIR__)) . '/templates/admin/tab-voice.php'; ?>

                    <!-- TAB 8: WHATSAPP BRIDGE & CRM -->
                    <?php elseif ($active_tab === 'crm'): ?>
                        <?php include dirname(dirname(__DIR__)) . '/templates/admin/tab-crm.php'; ?>

                    <!-- TAB 9: AUTONOMOUS LEAD RADAR -->
                    <?php elseif ($active_tab === 'radar'): ?>
                        <?php include dirname(dirname(__DIR__)) . '/templates/admin/tab-radar.php'; ?>

                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- MODAL FOR TRACE JSON INSPECT -->
        <div id="rsdTraceModal" class="rsd-modal-overlay">
            <div class="rsd-modal-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid #E2E8F0;padding-bottom:12px;">
                    <h3 style="margin:0;font-size:1.1rem;font-weight:800;color:#0F172A;">🔍 فحص بيانات الاستدلال الكلية (Trace JSON)</h3>
                    <button type="button" onclick="document.getElementById('rsdTraceModal').style.display='none'" class="rsd-btn rsd-btn-secondary" style="padding:4px 10px;font-size:0.8rem;">✖ إغلاق</button>
                </div>
                <pre id="rsdTraceJsonPre" style="background:#0F172A;color:#38BDF8;padding:16px;border-radius:12px;overflow-x:auto;font-family:'JetBrains Mono',monospace;font-size:0.82rem;line-height:1.5;direction:ltr;"></pre>
            </div>
        </div>

        <!-- MODAL FOR SITE LIVE PREVIEW -->
        <div id="rsdSitePreviewModal" class="rsd-modal-overlay">
            <div class="rsd-modal-box" style="max-width:950px;width:95%;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h3 style="margin:0;font-size:1rem;font-weight:800;color:#0F172A;">🌐 معاينة موقع المنشأة المباشر</h3>
                    <button type="button" onclick="document.getElementById('rsdSitePreviewModal').style.display='none'" class="rsd-btn rsd-btn-secondary" style="padding:4px 10px;font-size:0.8rem;">✖ إغلاق</button>
                </div>
                <iframe id="rsdSiteIframe" src="" style="width:100%;height:550px;border:1px solid #E2E8F0;border-radius:12px;"></iframe>
            </div>
        </div>

        <!-- SCRIPTS FOR RADAR & CRM INTERACTION -->
        <script>
        function rsdInspectTrace(idx) {
            var data = (window.rsdTraceData && window.rsdTraceData[idx]) ? window.rsdTraceData[idx] : {};
            document.getElementById('rsdTraceJsonPre').innerText = JSON.stringify(data, null, 2);
            document.getElementById('rsdTraceModal').style.display = 'flex';
        }

        function rsdPreviewSite(url) {
            document.getElementById('rsdSiteIframe').src = url;
            document.getElementById('rsdSitePreviewModal').style.display = 'flex';
        }

        function rsdSwitchPairMode(mode) {
            var qrSec = document.getElementById('rsdPairModeQr');
            var codeSec = document.getElementById('rsdPairModeCode');
            var btnQr = document.getElementById('tabBtnQr');
            var btnCode = document.getElementById('tabBtnCode');

            if (mode === 'qr') {
                qrSec.style.display = 'block';
                codeSec.style.display = 'none';
                btnQr.style.background = '#FFFFFF';
                btnQr.style.color = '#0F172A';
                btnQr.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)';
                btnCode.style.background = 'transparent';
                btnCode.style.color = '#64748B';
                btnCode.style.boxShadow = 'none';
            } else {
                qrSec.style.display = 'none';
                codeSec.style.display = 'block';
                btnCode.style.background = '#FFFFFF';
                btnCode.style.color = '#0F172A';
                btnCode.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)';
                btnQr.style.background = 'transparent';
                btnQr.style.color = '#64748B';
                btnQr.style.boxShadow = 'none';
            }
        }

        function rsdRefreshQrCode() {
            var img = document.getElementById('rsdQrCodeImg');
            var ph = document.getElementById('rsdQrPlaceholder');
            ph.innerText = '⏳ جاري استدعاء رمز QR...';

            var fd = new FormData();
            fd.append('action', 'rsd_wa_get_qr');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success && d.data.qrcode_url) {
                        img.src = d.data.qrcode_url;
                        img.style.display = 'block';
                        ph.style.display = 'none';
                    } else {
                        ph.innerText = (d.data && d.data.message) ? d.data.message : 'تعذر تحميل رمز QR';
                    }
                });
        }

        function rsdRequestPairingCode() {
            var phone = document.getElementById('rsdPairPhoneInput').value;
            var disp = document.getElementById('rsdPairingCodeDisplay');
            var val = document.getElementById('rsdPairingCodeVal');

            disp.style.display = 'block';
            val.innerText = '⏳ جاري التوليد...';

            var fd = new FormData();
            fd.append('action', 'rsd_wa_get_pairing_code');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
            fd.append('phone', phone);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success && d.data.pairing_code) {
                        val.innerText = d.data.pairing_code;
                    } else {
                        disp.style.display = 'none';
                        alert(d.data && d.data.message ? d.data.message : 'تعذر استلام كود الربط.');
                    }
                });
        }

        function rsdCheckWaStatus() {
            var badge = document.getElementById('rsdWaStatusBadge');
            if (!badge) return;
            badge.innerHTML = '⏳ جاري الفحص...';

            var fd = new FormData();
            fd.append('action', 'rsd_wa_check_status');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success && (d.data.state === 'open' || d.data.state === 'connected')) {
                        badge.className = 'rsd-badge rsd-badge-success';
                        badge.innerHTML = '🟢 متصل: +' + d.data.phone;
                    } else {
                        badge.className = 'rsd-badge rsd-badge-danger';
                        badge.innerHTML = '🔴 غير متصل';
                    }
                });
        }

        if (typeof window !== 'undefined') {
            window.addEventListener('load', function() {
                if (typeof rsdCheckWaStatus === 'function') rsdCheckWaStatus();
            });
        }

        function rsdDisconnectWa() {
            if (!confirm('هل أنت متأكد من رغبتك في فك الارتباط وتسجيل الخروج؟')) return;
            var fd = new FormData();
            fd.append('action', 'rsd_wa_disconnect');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function() {
                    rsdCheckWaStatus();
                    alert('تم قطع الاتصال بنجاح.');
                });
        }

        function rsdRunRadarScan() {
            var btn = document.getElementById('rsdBtnRunRadar');
            var consoleBox = document.getElementById('rsdRadarConsole');
            var niche = document.getElementById('rsdRadarNiche').value;

            btn.disabled = true;
            btn.innerHTML = '⏳ جاري التنقيب والتحليل...';
            consoleBox.style.display = 'block';

            var fd = new FormData();
            fd.append('action', 'rsd_radar_run_discovery');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
            fd.append('niche', niche);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        alert('✅ اكتملت جولة التنقيب بنجاح! تم رصد وتدقيق الفرص.');
                        window.location.reload();
                    } else {
                        alert('حدث خطأ أثناء التنقيب: ' + (d.data && d.data.message ? d.data.message : ''));
                        btn.disabled = false;
                        btn.innerHTML = '🤖 ابدأ جولة التنقيب الآلي الآن';
                    }
                });
        }

        function rsdApproveAndSend(leadId) {
            if (!confirm('هل توافق على اعتماد وإرسال رسالة الواتساب المخصصة لهذا العميل؟')) return;
            var pitch = document.getElementById('pitchText_' + leadId).value;
            var fd = new FormData();
            fd.append('action', 'rsd_radar_approve_lead');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
            fd.append('lead_id', leadId);
            fd.append('pitch', pitch);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        alert('🚀 تم اعتماد العرض وإرساله للعميل بنجاح!');
                        window.location.reload();
                    } else {
                        alert('تعذر الإرسال: ' + (d.data && d.data.message ? d.data.message : ''));
                    }
                });
        }

        function rsdSaveLeadPitch(leadId) {
            var pitch = document.getElementById('pitchText_' + leadId).value;
            var fd = new FormData();
            fd.append('action', 'rsd_radar_edit_pitch');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
            fd.append('lead_id', leadId);
            fd.append('pitch', pitch);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function() { alert('تم حفظ تعديل نص الرسالة بنجاح.'); });
        }

        function rsdRejectLead(leadId) {
            if (!confirm('هل تريد استبعاد هذه الفرصة؟')) return;
            var fd = new FormData();
            fd.append('action', 'rsd_radar_reject_lead');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
            fd.append('lead_id', leadId);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function() {
                    var card = document.getElementById('leadCard_' + leadId);
                    if (card) card.style.opacity = '0.35';
                    alert('تم استبعاد الفرصة.');
                });
        }
        </script>
        <?php
    }
}
