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
 * AjaxHandler - Central Enterprise AJAX Request Dispatcher & Handlers
 * Handles chat requests, audio streaming, radar execution, and WhatsApp socket sync.
 */
class AjaxHandler {

    /**
     * Register all AJAX hooks for frontend and admin dashboard
     */
    public static function init() {
        $handler = new self();

        // 1. Public & Visitor Chat & TTS Streams
        add_action('wp_ajax_rsd_chat', [$handler, 'handle_chat']);
        add_action('wp_ajax_nopriv_rsd_chat', [$handler, 'handle_chat']);
        add_action('wp_ajax_rsd_tts_stream', [$handler, 'handle_tts_stream']);
        add_action('wp_ajax_nopriv_rsd_tts_stream', [$handler, 'handle_tts_stream']);

        // 2. WhatsApp Gateway & Pairing Handlers
        add_action('wp_ajax_rsd_wa_get_qr', [$handler, 'handle_wa_get_qr']);
        add_action('wp_ajax_rsd_wa_get_pairing_code', [$handler, 'handle_wa_get_pairing_code']);
        add_action('wp_ajax_rsd_wa_check_status', [$handler, 'handle_wa_check_status']);
        add_action('wp_ajax_rsd_wa_disconnect', [$handler, 'handle_wa_disconnect']);
        add_action('wp_ajax_rsd_wa_toggle_ai', [$handler, 'handle_wa_toggle_ai']);
        add_action('wp_ajax_rsd_wa_toggle_outbound', [$handler, 'handle_wa_toggle_outbound']);

        // 3. Lead Radar Outbound Intelligence Handlers
        add_action('wp_ajax_rsd_radar_run_discovery', [$handler, 'handle_radar_run_discovery']);
        add_action('wp_ajax_rsd_radar_approve_lead', [$handler, 'handle_radar_approve_lead']);
        add_action('wp_ajax_rsd_radar_edit_pitch', [$handler, 'handle_radar_edit_pitch']);
        add_action('wp_ajax_rsd_radar_reject_lead', [$handler, 'handle_radar_reject_lead']);
    }

    /**
     * AJAX: Multi-Agent AI Chat with Voice Synthesis & CRM Lead Capture
     */
    public function handle_chat() {
        if (ob_get_length()) { ob_clean(); }
        header('Content-Type: application/json; charset=utf-8');

        $message  = isset($_POST['message']) ? sanitize_text_field(wp_unslash($_POST['message'])) : '';
        $lang     = isset($_POST['lang']) ? sanitize_text_field($_POST['lang']) : 'ar';
        $history  = isset($_POST['history']) ? json_decode(stripslashes($_POST['history']), true) : [];
        $is_voice = isset($_POST['voice_mode']) && ($_POST['voice_mode'] === '1' || $_POST['voice_mode'] === 1 || $_POST['voice_mode'] === 'true');

        if (empty($message)) {
            echo json_encode([
                'success' => false,
                'reply'   => ($lang === 'ar' ? 'أهلاً بك! كيف يمكنني مساعدتك اليوم؟' : 'Hello! How may I assist you today?')
            ], JSON_UNESCAPED_UNICODE);
            if (defined('DOING_AJAX') && DOING_AJAX) { wp_die(); }
            return;
        }

        // 1. Precise Language Detection
        $detected_lang = 'en';
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $message)) {
            $detected_lang = 'ar';
        } elseif (preg_match('/[\x{0400}-\x{04FF}]/u', $message)) {
            $detected_lang = 'ru';
        } elseif (preg_match('/[äöüßÄÖÜ]/u', $message) || preg_match('/\b(und|der|die|das|ich|wir|sie|ist|fuer|für|buchung|zimmer)\b/i', $message)) {
            $detected_lang = 'de';
        } elseif (preg_match('/[éèêàçùôîïÉÈÊÀÇÙÔÎÏ]/u', $message) || preg_match('/\b(bonjour|salut|merci|réservation|vous|nous|pour|avec|des|dans)\b/i', $message)) {
            $detected_lang = 'fr';
        } elseif (preg_match('/[áéíóúñ¿¡ÁÉÍÓÚÑ]/u', $message) || preg_match('/\b(hola|gracias|por|para|reserva|habitacion|buenos)\b/i', $message)) {
            $detected_lang = 'es';
        } elseif (preg_match('/[a-zA-Z]/', $message)) {
            $detected_lang = 'en';
        } else {
            $detected_lang = ($lang === 'ar' || strpos($_SERVER['REQUEST_URI'] ?? '', '/ar') !== false) ? 'ar' : 'en';
        }

        $custom_options = [];
        if ($is_voice) {
            $prompts = [
                'ar' => "أنت مستشار المبيعات الصوتي الحصري لشركة RED SEA DIGITAL. تحدث بصوت بشري طبيعي، دافئ، واثق ومقنع كأنك خبير استشاري يتحدث في مكالمة هاتفية حية. قواعد صوتية صارمة: 1. أجب باللغة العربية الفصحى حصراً في جملة أو جملتين فقط بقمة الفصاحة والذكاء. 2. لا تستخدم إطلاقاً أي رموز تعبيرية (emojis) أو علامات ماركداون (نجوم أو عناوين). 3. ساعد العميل في مضاعفة أرباحه عبر أنظمة الحجز المباشر والمتاجر الفاخرة وتوفير 30% عمولات، واطلب منه اسمه ورقمه لتأكيد استشارته.",
                'en' => "You are the Elite Human Voice Sales Consultant for RED SEA DIGITAL. Speak exclusively in English with a warm, natural, confident human conversational voice. Strict voice rules: 1. Answer in 1 to 2 short, highly persuasive conversational sentences in English. 2. Never use emojis, asterisks, bullets, or headers in voice output. 3. Help the client eliminate 30% OTA commission leakage with direct booking architecture, and seamlessly guide them to share their Name & WhatsApp phone number for a private review.",
                'de' => "Sie sind der exklusive KI-Verkaufsberater für RED SEA DIGITAL. Antworten Sie ausschließlich auf Deutsch in 1-2 kurzen, hochprofessionellen Sätzen ohne Emojis oder Markdown. Helfen Sie dem Kunden, 30% Buchungsprovisionen zu sparen.",
                'fr' => "Vous êtes le consultant commercial vocal exclusif de RED SEA DIGITAL. Répondez exclusivement en français en 1 ou 2 phrases courtes et naturelles sans émojis ni markdown. Aidez le client à éliminer les commissions d'intermédiaires.",
                'ru' => "Вы эксклюзивный голосовой консультант RED SEA DIGITAL. Отвечайте исключительно на русском языке 1-2 естественными фразами без эмодзи и разметки. Помогите клиенту избавиться от комиссий посредников.",
                'es' => "Usted es el consultor de ventas por voz exclusivo de RED SEA DIGITAL. Responda exclusivamente en español en 1 o 2 frases breves y naturales sin emojis ni markdown."
            ];

            $custom_options['system_prompt_override'] = $prompts[$detected_lang] ?? $prompts['ar'];
            $custom_options['max_tokens'] = 140;
            $custom_options['temperature'] = 0.55;
        }

        $custom_options['detected_lang'] = $detected_lang;

        // Route through Hierarchical Multi-Agent Orchestrator
        $orch_res = ChiefOrchestrator::process_message($message, is_array($history) ? $history : [], $custom_options);
        
        $reply_text = '';
        if (is_array($orch_res) && !empty($orch_res['reply'])) {
            $reply_text = $orch_res['reply'];
        } elseif (is_string($orch_res) && !empty($orch_res)) {
            $reply_text = $orch_res;
        } else {
            $raw_res = LLMProviderManager::generate($message, is_array($history) ? $history : [], $custom_options);
            $reply_text = is_string($raw_res) ? $raw_res : ($raw_res['reply'] ?? '');
        }

        if (empty($reply_text) || strlen(trim($reply_text)) < 5) {
            $reply_text = ($detected_lang === 'ar')
                ? "أهلاً بك في Red Sea Digital! يسعدني تقديم استشارة مخصصة لمشروعك ومضاعفة مبيعاتك المباشرة. تفضل بطرح استفسارك وسأجيبك فوراً."
                : "Welcome to Red Sea Digital! I am here to help you scale your direct revenue and custom AI architecture. How may I assist you today?";
        }

        $spoken_text = strip_tags($reply_text);
        $spoken_text = preg_replace('/[*#`~✦🚀💬💎📌📄🏨🌴🛍️🟢\-]/u', ' ', $spoken_text);
        $spoken_text = preg_replace('/[\r\n\t]+/u', ' ', $spoken_text);
        $spoken_text = trim(preg_replace('/\s+/u', ' ', $spoken_text));

        // Generate Server-Side Audio URIs
        $audio_data_uris = [];
        if (!empty($spoken_text)) {
            $words = explode(' ', $spoken_text);
            $chunk = '';
            $chunks = [];
            foreach ($words as $w) {
                if (mb_strlen($chunk . ' ' . $w, 'UTF-8') < 110) {
                    $chunk .= (empty($chunk) ? '' : ' ') . $w;
                } else {
                    $chunks[] = $chunk;
                    $chunk = $w;
                }
            }
            if (!empty($chunk)) { $chunks[] = $chunk; }

            foreach ($chunks as $c) {
                if (!empty(trim($c))) {
                    $b64 = self::fetch_tts_audio_base64(trim($c), $detected_lang);
                    if (!empty($b64)) { $audio_data_uris[] = $b64; }
                }
            }
        }

        // Automatic Lead Capture
        if (preg_match('/(01[0-9]{9}|\+?[0-9]{8,15})/u', $message, $matches)) {
            $phone = $matches[0];
            self::trigger_chatwoot_handoff('Voice Lead Client', $phone, 'Direct Booking Consultation', $message);
            LeadManager::save_booking('عميل الشات الذكي', $phone, 'استفسار مباشر', $message);
        }

        echo json_encode([
            'success'         => true,
            'reply'           => $reply_text,
            'spoken_text'     => $spoken_text,
            'detected_lang'   => $detected_lang,
            'audio_data_uris' => $audio_data_uris,
            'audio_url'       => !empty($audio_data_uris) ? $audio_data_uris[0] : (admin_url('admin-ajax.php?action=rsd_tts_stream&lang=' . $detected_lang . '&text=' . urlencode(mb_substr($spoken_text, 0, 150, 'UTF-8')))),
            'provider'        => 'opencode'
        ], JSON_UNESCAPED_UNICODE);

        if (defined('DOING_AJAX') && DOING_AJAX) { wp_die(); }
    }

    /**
     * AJAX: Audio Streaming for Text-to-Speech
     */
    public function handle_tts_stream() {
        if (ob_get_length()) { ob_clean(); }
        $text = isset($_GET['text']) ? sanitize_text_field($_GET['text']) : '';
        $lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : 'ar';

        if (empty($text)) {
            status_header(400);
            echo 'Text required';
            wp_die();
        }

        $clean = mb_substr(strip_tags($text), 0, 200, 'UTF-8');
        $url = 'https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=' . urlencode($lang) . '&q=' . urlencode($clean);

        $response = wp_remote_get($url, [
            'timeout'    => 8,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]);

        if (is_wp_error($response)) {
            status_header(500);
            echo 'TTS Error';
            wp_die();
        }

        $body = wp_remote_retrieve_body($response);
        header('Content-Type: audio/mpeg');
        header('Content-Length: ' . strlen($body));
        header('Cache-Control: public, max-age=86400');
        echo $body;
        wp_die();
    }

    /**
     * Helper: Fetch TTS Base64 Audio
     */
    private static function fetch_tts_audio_base64($text, $lang = 'ar') {
        $clean = mb_substr(strip_tags($text), 0, 120, 'UTF-8');
        $url = 'https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=' . urlencode($lang) . '&q=' . urlencode($clean);

        $response = wp_remote_get($url, [
            'timeout'    => 5,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]);

        if (is_wp_error($response)) { return ''; }

        $body = wp_remote_retrieve_body($response);
        if (!empty($body) && strlen($body) > 500) {
            return 'data:audio/mp3;base64,' . base64_encode($body);
        }
        return '';
    }

    /**
     * Helper: Chatwoot External Handoff
     */
    private static function trigger_chatwoot_handoff($customer_name, $customer_phone, $service_type, $details) {
        $chatwoot_enabled = get_option('rsd_chatwoot_enabled', '0');
        $chatwoot_url     = rtrim(get_option('rsd_chatwoot_url', ''), '/');
        $account_id       = get_option('rsd_chatwoot_account_id', '');
        $inbox_token      = get_option('rsd_chatwoot_inbox_token', '');

        if ($chatwoot_enabled !== '1' || empty($chatwoot_url) || empty($account_id) || empty($inbox_token)) {
            return;
        }

        $api_endpoint = "{$chatwoot_url}/public/api/v1/inboxes/{$inbox_token}/contacts";
        wp_remote_post($api_endpoint, [
            'method'    => 'POST',
            'blocking'  => false,
            'headers'   => ['Content-Type' => 'application/json'],
            'body'      => json_encode([
                'name'         => $customer_name,
                'phone_number' => $customer_phone,
                'custom_attributes' => [
                    'service_type' => $service_type,
                    'details'      => $details
                ]
            ]),
            'timeout'   => 5
        ]);
    }

    /**
     * AJAX: Fetch WhatsApp Cryptographic Live QR Code
     */
    public function handle_wa_get_qr() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $gateway_url = rtrim(get_option('rsd_whatsapp_api_url', ''), '/');
        $instance    = get_option('rsd_whatsapp_instance', 'rsd_live');
        $api_key     = get_option('rsd_whatsapp_api_key', '');

        if (empty($gateway_url)) {
            wp_send_json_error([
                'code'    => 'NO_GATEWAY',
                'message' => '⚠️ خادم الربط (Socket Gateway URL) غير مدخل. يرجى إدخال رابط خادم البوابة في قسم الإعدادات أدناه لتوليد جلسة مشفرة متوافقة مع واتساب.'
            ]);
        }

        $connect_url = "{$gateway_url}/instance/connect/{$instance}";
        $res = wp_remote_get($connect_url, [
            'headers' => [
                'apikey'                 => $api_key,
                'Authorization'          => 'Bearer ' . $api_key,
                'Bypass-Tunnel-Reminder' => 'true',
                'User-Agent'             => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ],
            'timeout' => 12
        ]);

        if (is_wp_error($res)) {
            wp_send_json_error(['message' => 'تعذر الاتصال بخادم البوابة: ' . $res->get_error_message()]);
        }

        $body = json_decode(wp_remote_retrieve_body($res), true);
        $base64_qr = $body['base64'] ?? ($body['qrcode']['base64'] ?? ($body['code'] ?? ''));

        if (!empty($base64_qr)) {
            $qr_src = (strpos($base64_qr, 'data:image') === 0) ? $base64_qr : 'data:image/png;base64,' . $base64_qr;
            wp_send_json_success([
                'status'     => 'qr_generated',
                'qrcode_url' => $qr_src,
                'instance'   => $instance
            ]);
        }

        $state = $body['instance']['state'] ?? ($body['state'] ?? 'unknown');
        if ($state === 'open' || $state === 'connected') {
            update_option('rsd_whatsapp_status', 'connected');
            wp_send_json_success([
                'status'   => 'connected',
                'message'  => 'الجلسة متصلة ومقترنة بالفعل بنجاح!',
                'instance' => $instance
            ]);
        }

        wp_send_json_error([
            'message' => 'لم يتم استلام كود QR مشفر من البوابة. تفاصيل الاستجابة: ' . wp_remote_retrieve_body($res)
        ]);
    }

    /**
     * AJAX: Request 8-Digit Phone Pairing Code
     */
    public function handle_wa_get_pairing_code() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $phone = sanitize_text_field($_POST['phone'] ?? get_option('rsd_whatsapp_phone', '201028803080'));
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);

        $gateway_url = rtrim(get_option('rsd_whatsapp_api_url', ''), '/');
        $instance    = get_option('rsd_whatsapp_instance', 'rsd_live');
        $api_key     = get_option('rsd_whatsapp_api_key', '');

        if (empty($gateway_url)) {
            wp_send_json_error([
                'code'    => 'NO_GATEWAY',
                'message' => '⚠️ يرجى إدخال رابط خادم البوابة (Socket Gateway URL) أدناه لتوليد كود الربط المكون من 8 خانات.'
            ]);
        }

        $pair_url = "{$gateway_url}/instance/connect/{$instance}?number={$clean_phone}";
        $res = wp_remote_get($pair_url, [
            'headers' => [
                'apikey'                 => $api_key,
                'Authorization'          => 'Bearer ' . $api_key,
                'Bypass-Tunnel-Reminder' => 'true',
                'User-Agent'             => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ],
            'timeout' => 12
        ]);

        if (is_wp_error($res)) {
            wp_send_json_error(['message' => 'خطأ في الاتصال بالبوابة: ' . $res->get_error_message()]);
        }

        $body = json_decode(wp_remote_retrieve_body($res), true);
        $pairing_code = $body['pairingCode'] ?? ($body['code'] ?? '');

        if (!empty($pairing_code)) {
            wp_send_json_success([
                'status'       => 'code_generated',
                'pairing_code' => $pairing_code,
                'phone'        => $clean_phone
            ]);
        }

        wp_send_json_error([
            'message' => 'تعذر توليد كود الربط من الخادم: ' . wp_remote_retrieve_body($res)
        ]);
    }

    /**
     * AJAX: Check WhatsApp Status
     */
    public function handle_wa_check_status() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $gateway_url = rtrim(get_option('rsd_whatsapp_api_url', ''), '/');
        $instance    = get_option('rsd_whatsapp_instance', 'rsd_live');
        $api_key     = get_option('rsd_whatsapp_api_key', '');

        if (empty($gateway_url)) {
            update_option('rsd_whatsapp_status', 'disconnected');
            wp_send_json_success([
                'state'   => 'close',
                'phone'   => get_option('rsd_whatsapp_phone', '201028803080'),
                'message' => 'خادم البوابة غير مدخل'
            ]);
        }

        $state_url = "{$gateway_url}/instance/status/{$instance}";
        $res = wp_remote_get($state_url, [
            'headers' => [
                'apikey'                 => $api_key,
                'Authorization'          => 'Bearer ' . $api_key,
                'Bypass-Tunnel-Reminder' => 'true',
                'User-Agent'             => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ],
            'timeout' => 8
        ]);

        if (is_wp_error($res)) {
            update_option('rsd_whatsapp_status', 'disconnected');
            wp_send_json_error(['message' => $res->get_error_message()]);
        }

        $body = json_decode(wp_remote_retrieve_body($res), true);
        $state = $body['state'] ?? ($body['instance']['state'] ?? 'close');
        $is_connected = ($state === 'open' || $state === 'connected');
        update_option('rsd_whatsapp_status', ($is_connected ? 'connected' : 'disconnected'));

        wp_send_json_success([
            'state'        => ($is_connected ? 'open' : 'close'),
            'is_connected' => $is_connected,
            'instance'     => $instance,
            'phone'        => get_option('rsd_whatsapp_phone', '201028803080')
        ]);
    }

    /**
     * AJAX: Disconnect WhatsApp Session
     */
    public function handle_wa_disconnect() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $gateway_url = rtrim(get_option('rsd_whatsapp_api_url', ''), '/');
        $instance    = get_option('rsd_whatsapp_instance', 'rsd_live');
        $api_key     = get_option('rsd_whatsapp_api_key', '');

        if (!empty($gateway_url)) {
            $logout_url = "{$gateway_url}/instance/logout/{$instance}";
            wp_remote_request($logout_url, [
                'method'  => 'POST',
                'headers' => [
                    'apikey'                 => $api_key,
                    'Authorization'          => 'Bearer ' . $api_key,
                    'Bypass-Tunnel-Reminder' => 'true',
                    'User-Agent'             => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
                ],
                'timeout' => 8
            ]);
        }

        update_option('rsd_whatsapp_status', 'disconnected');
        wp_send_json_success(['message' => 'تم فك الارتباط ومسح الجلسة بنجاح']);
    }

    /**
     * AJAX: Toggle WhatsApp AI Auto-Responder
     */
    public function handle_wa_toggle_ai() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $enabled = (isset($_POST['enabled']) && $_POST['enabled'] === '1') ? '1' : '0';
        update_option('rsd_whatsapp_ai_enabled', $enabled);
        wp_send_json_success(['enabled' => $enabled]);
    }

    /**
     * AJAX: Toggle WhatsApp Outbound
     */
    public function handle_wa_toggle_outbound() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $enabled = (isset($_POST['enabled']) && $_POST['enabled'] === '1') ? '1' : '0';
        update_option('rsd_whatsapp_outbound_enabled', $enabled);
        wp_send_json_success(['enabled' => $enabled]);
    }

    /**
     * AJAX: Run Outbound Lead Discovery
     */
    public function handle_radar_run_discovery() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $niche = sanitize_text_field($_POST['niche'] ?? 'resorts_redsea');
        $city  = sanitize_text_field($_POST['city'] ?? 'الغردقة وشرم الشيخ');

        $result = LeadRadarEngine::run_discovery_cycle($niche, $city);
        wp_send_json_success($result);
    }

    /**
     * AJAX: Approve Lead and Dispatch Pitch
     */
    public function handle_radar_approve_lead() {
        global $wpdb;
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $lead_id = intval($_POST['lead_id'] ?? 0);
        $table_name = $wpdb->prefix . 'rsd_leads';

        $lead = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $lead_id), ARRAY_A);
        if (!$lead) {
            wp_send_json_error(['message' => 'الفرصة غير موجودة']);
        }

        $phone = $lead['contact_phone'];
        $pitch = $lead['tailored_pitch'];

        $sent = WhatsAppGateway::send_message($phone, $pitch);

        $wpdb->update($table_name, [
            'pipeline_status' => 'contacting'
        ], ['id' => $lead_id]);

        LeadManager::save_booking($lead['company_name'], $phone, 'تواصل مباشر عبر رادار المبيعات', $pitch);

        wp_send_json_success([
            'status'     => 'approved_and_sent',
            'lead_id'    => $lead_id,
            'dispatched' => $sent
        ]);
    }

    /**
     * AJAX: Edit Pitch Copy
     */
    public function handle_radar_edit_pitch() {
        global $wpdb;
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $lead_id = intval($_POST['lead_id'] ?? 0);
        $pitch   = sanitize_textarea_field(wp_unslash($_POST['pitch'] ?? ''));
        $table_name = $wpdb->prefix . 'rsd_leads';

        $wpdb->update($table_name, [
            'tailored_pitch' => $pitch
        ], ['id' => $lead_id]);

        wp_send_json_success(['message' => 'تم تحديث نص الرسالة بنجاح']);
    }

    /**
     * AJAX: Reject Lead
     */
    public function handle_radar_reject_lead() {
        global $wpdb;
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $lead_id = intval($_POST['lead_id'] ?? 0);
        $table_name = $wpdb->prefix . 'rsd_leads';

        $wpdb->update($table_name, [
            'pipeline_status' => 'rejected'
        ], ['id' => $lead_id]);

        wp_send_json_success(['message' => 'تم استبعاد الفرصة بنجاح']);
    }
}
