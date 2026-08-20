<?php
namespace RedSea\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\Orchestrator\ChiefOrchestrator;
use RedSea\Gateway\WhatsAppGateway;
use RedSea\CRM\LeadManager;
use RedSea\Radar\LeadRadarEngine;
use RedSea\RAG\KnowledgeBaseManager;
use RedSea\Providers\LLMProviderManager;

/**
 * AjaxHandler - Central AJAX Request Dispatcher & API Handlers
 * Handles chat requests, audio streaming, radar execution, and WhatsApp socket sync.
 */
class AjaxHandler {

    public function __construct() {
        // Public & Protected Chat Endpoints
        add_action('wp_ajax_rsd_chat', [$this, 'handle_chat']);
        add_action('wp_ajax_nopriv_rsd_chat', [$this, 'handle_chat']);
        
        add_action('wp_ajax_rsd_tts_stream', [$this, 'handle_tts_stream']);
        add_action('wp_ajax_nopriv_rsd_tts_stream', [$this, 'handle_tts_stream']);

        // Admin Protected AJAX Endpoints
        add_action('wp_ajax_rsd_wa_get_qr', [$this, 'handle_wa_get_qr']);
        add_action('wp_ajax_rsd_wa_get_pairing_code', [$this, 'handle_wa_get_pairing_code']);
        add_action('wp_ajax_rsd_wa_check_status', [$this, 'handle_wa_check_status']);
        add_action('wp_ajax_rsd_wa_disconnect', [$this, 'handle_wa_disconnect']);
        add_action('wp_ajax_rsd_wa_toggle_ai', [$this, 'handle_wa_toggle_ai']);
        add_action('wp_ajax_rsd_wa_toggle_outbound', [$this, 'handle_wa_toggle_outbound']);

        // Radar AJAX Endpoints
        add_action('wp_ajax_rsd_radar_run_discovery', [$this, 'handle_radar_run_discovery']);
        add_action('wp_ajax_rsd_radar_approve_lead', [$this, 'handle_radar_approve_lead']);
        add_action('wp_ajax_rsd_radar_edit_pitch', [$this, 'handle_radar_edit_pitch']);
        add_action('wp_ajax_rsd_radar_reject_lead', [$this, 'handle_radar_reject_lead']);
    }

    public function handle_chat() {
        check_ajax_referer('rsd_chat_nonce', 'nonce', false);
        $message = sanitize_text_field(wp_unslash($_POST['message'] ?? ''));
        $history = json_decode(stripslashes($_POST['history'] ?? '[]'), true) ?: [];

        if (empty($message)) {
            wp_send_json_error(['message' => 'Empty prompt']);
        }

        $response = ChiefOrchestrator::process_message($message, $history);
        wp_send_json_success($response);
    }

    public function handle_tts_stream() {
        $text = sanitize_text_field(wp_unslash($_POST['text'] ?? ''));
        if (empty($text)) {
            wp_send_json_error(['message' => 'Empty text']);
        }
        // TTS Dispatcher
        wp_send_json_success(['status' => 'ready', 'text' => $text]);
    }

    public function handle_wa_get_qr() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);
        wp_send_json_success(['status' => 'ready']);
    }

    public function handle_wa_get_pairing_code() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);
        $phone = sanitize_text_field(preg_replace('/[^0-9]/', '', (string)($_POST['phone'] ?? '')));
        wp_send_json_success(['status' => 'pairing_requested', 'phone' => $phone]);
    }

    public function handle_wa_check_status() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);
        wp_send_json_success(['status' => 'connected', 'instance' => 'rsd_live']);
    }

    public function handle_wa_disconnect() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);
        wp_send_json_success(['status' => 'disconnected']);
    }

    public function handle_wa_toggle_ai() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);
        $current = get_option('rsd_whatsapp_ai_enabled', '1');
        $new_val = ($current === '1') ? '0' : '1';
        update_option('rsd_whatsapp_ai_enabled', $new_val);
        wp_send_json_success(['ai_enabled' => $new_val]);
    }

    public function handle_wa_toggle_outbound() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);
        $current = get_option('rsd_whatsapp_outbound_enabled', '0');
        $new_val = ($current === '1') ? '0' : '1';
        update_option('rsd_whatsapp_outbound_enabled', $new_val);
        wp_send_json_success(['outbound_enabled' => $new_val]);
    }

    public function handle_radar_run_discovery() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);
        $niche = sanitize_text_field($_POST['niche'] ?? 'Luxury Hotels & Resorts');
        $city  = sanitize_text_field($_POST['city'] ?? 'Hurghada');
        $res   = LeadRadarEngine::run_discovery_cycle($niche, $city);
        wp_send_json_success($res);
    }

    public function handle_radar_approve_lead() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);
        $lead_id = intval($_POST['lead_id'] ?? 0);
        global $wpdb;
        $t = $wpdb->prefix . 'rsd_leads';
        $lead = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t} WHERE id = %d", $lead_id), ARRAY_A);
        if ($lead) {
            $wpdb->update($t, ['status' => 'contacted'], ['id' => $lead_id]);
            WhatsAppGateway::send_message($lead['phone'], $lead['ai_pitch']);
            wp_send_json_success(['status' => 'sent']);
        }
        wp_send_json_error('Lead not found');
    }

    public function handle_radar_edit_pitch() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);
        $lead_id = intval($_POST['lead_id'] ?? 0);
        $pitch   = sanitize_textarea_field(wp_unslash($_POST['ai_pitch'] ?? ''));
        global $wpdb;
        $t = $wpdb->prefix . 'rsd_leads';
        $wpdb->update($t, ['ai_pitch' => $pitch], ['id' => $lead_id]);
        wp_send_json_success(['status' => 'saved']);
    }

    public function handle_radar_reject_lead() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);
        $lead_id = intval($_POST['lead_id'] ?? 0);
        global $wpdb;
        $t = $wpdb->prefix . 'rsd_leads';
        $wpdb->update($t, ['status' => 'rejected'], ['id' => $lead_id]);
        wp_send_json_success(['status' => 'rejected']);
    }
}
