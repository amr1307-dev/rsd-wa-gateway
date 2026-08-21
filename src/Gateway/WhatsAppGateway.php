<?php
namespace RedSea\Gateway;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\Providers\LLMProviderManager;
use RedSea\CRM\LeadManager;
use RedSea\Orchestrator\ChiefOrchestrator;
use WP_REST_Response;

/**
 * WhatsAppGateway - Dual-Engine WhatsApp Gateway (Official Meta Cloud API & Local Socket Bridge)
 * Provides enterprise resilience with dynamic switching between Meta Cloud API and Local QR/Socket Bridge.
 */
class WhatsAppGateway {

    /**
     * Get Current Active Gateway Mode
     * @return string 'official_cloud' | 'local_bridge'
     */
    public static function get_gateway_mode() {
        $mode = get_option('rsd_whatsapp_gateway_mode', '');
        if (!empty($mode)) {
            return $mode;
        }

        // Automatic Backward Compatibility Detection:
        // If an existing installation already has Socket Gateway URL configured, preserve local_bridge.
        $has_socket = !empty(get_option('rsd_whatsapp_api_url', ''));
        $has_cloud  = !empty(get_option('rsd_meta_phone_id', '')) || !empty(get_option('rsd_whatsapp_cloud_token', ''));

        if ($has_cloud) {
            return 'official_cloud';
        }
        if ($has_socket) {
            return 'local_bridge';
        }

        return 'official_cloud'; // Default for new setups
    }

    /**
     * Dispatch WhatsApp message to remote recipient using the active gateway mode
     * 
     * @param string|int $phone Recipient phone number
     * @param string|array $message Text string or structured payload
     * @return bool
     */
    public static function send_message($phone, $message) {
        $clean_phone = sanitize_text_field(preg_replace('/[^0-9]/', '', (string)$phone));
        if (empty($clean_phone)) {
            return false;
        }

        $mode = self::get_gateway_mode();

        if ($mode === 'official_cloud') {
            $sent = self::send_cloud_api_message($clean_phone, $message);
            if ($sent) return true;
            // Graceful fallback to local socket if cloud is unconfigured but socket exists
            if (!empty(get_option('rsd_whatsapp_api_url', ''))) {
                return self::send_local_bridge_message($clean_phone, is_array($message) ? ($message['text']['body'] ?? '') : $message);
            }
            return false;
        } else {
            $text_body = is_array($message) ? ($message['text']['body'] ?? json_encode($message)) : $message;
            return self::send_local_bridge_message($clean_phone, $text_body);
        }
    }

    /**
     * Engine 1: Official Meta Cloud API Dispatcher (Enterprise Standard)
     * 
     * @param string $clean_phone
     * @param string|array $payload Text string or custom Meta JSON structure
     * @return bool
     */
    public static function send_cloud_api_message($clean_phone, $payload) {
        $phone_id     = get_option('rsd_meta_phone_id', get_option('rsd_whatsapp_phone_number_id', ''));
        $access_token = get_option('rsd_meta_access_token', get_option('rsd_whatsapp_cloud_token', ''));

        if (empty($phone_id) || empty($access_token)) {
            return false;
        }

        $endpoint = "https://graph.facebook.com/v19.0/{$phone_id}/messages";

        if (is_array($payload)) {
            $body_data = array_merge([
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to'                => $clean_phone,
            ], $payload);
        } else {
            $body_data = [
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to'                => $clean_phone,
                'type'              => 'text',
                'text'              => [
                    'preview_url' => false,
                    'body'        => (string)$payload
                ]
            ];
        }

        $response = wp_remote_post($endpoint, [
            'method'    => 'POST',
            'blocking'  => false,
            'headers'   => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ],
            'body'      => json_encode($body_data),
            'timeout'   => 10
        ]);

        return !is_wp_error($response);
    }

    /**
     * Engine 2: Local Socket / Evolution Bridge Dispatcher (Human Typing Simulation & Anti-Ban)
     * 
     * @param string $clean_phone
     * @param string $message
     * @return bool
     */
    public static function send_local_bridge_message($clean_phone, $message) {
        $gateway_url = rtrim(get_option('rsd_whatsapp_api_url', ''), '/');
        $instance    = get_option('rsd_whatsapp_instance', 'rsd_live');
        $api_key     = get_option('rsd_whatsapp_api_key', '');

        if (empty($gateway_url) || empty(trim($message))) {
            return false;
        }

        // Anti-Ban Human Typing Simulation: Calculate dynamic typing delay (2-4.5s)
        $msg_len = mb_strlen($message);
        $dynamic_delay = min(4500, max(2000, 2000 + ($msg_len * 15) + rand(100, 400)));

        $send_url = strpos($gateway_url, '/message/sendText') !== false 
            ? $gateway_url 
            : "{$gateway_url}/message/sendText/{$instance}";

        $response = wp_remote_post($send_url, [
            'method'    => 'POST',
            'blocking'  => false,
            'headers'   => [
                'Content-Type'  => 'application/json',
                'apikey'        => $api_key,
                'Authorization' => 'Bearer ' . $api_key
            ],
            'body'      => json_encode([
                'number'  => $clean_phone,
                'options' => [
                    'delay'       => $dynamic_delay,
                    'presence'    => 'composing',
                    'linkPreview' => false
                ],
                'text'    => $message
            ]),
            'timeout'   => 10
        ]);

        return !is_wp_error($response);
    }

    /**
     * Inbound Webhook Processor for both Meta Cloud API & Local Evolution Bridge
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public static function handle_inbound_webhook($request) {
        // 1. GET Challenge & Webhook Verification (Meta Hub Challenge & Socket Handshake)
        if ($request->get_method() === 'GET') {
            $configured_verify_token = get_option('rsd_meta_webhook_verify_token', get_option('rsd_whatsapp_api_key', 'rsd_secret_token_2026'));
            $token     = $request->get_param('hub_verify_token') ?? ($request->get_param('verify_token') ?? $request->get_param('token'));
            $challenge = $request->get_param('hub_challenge') ?? $request->get_param('challenge');
            
            if (!empty($configured_verify_token) && !empty($token) && !hash_equals($configured_verify_token, (string)$token)) {
                return new WP_REST_Response(['status' => 'error', 'message' => 'Unauthorized: Invalid Verification Token'], 403);
            }
            if (!empty($challenge)) {
                return new WP_REST_Response((int)$challenge, 200);
            }
            return new WP_REST_Response([
                'status'   => 'active',
                'service'  => 'RED SEA DIGITAL — Dual-Engine WhatsApp Gateway',
                'mode'     => self::get_gateway_mode(),
                'security' => 'Meta Cloud & Socket Anti-Ban Shield Active',
                'time'     => current_time('mysql')
            ], 200);
        }

        $params = $request->get_json_params() ?: $request->get_body_params();
        if (empty($params)) {
            $raw = file_get_contents('php://input');
            $params = json_decode($raw, true) ?: [];
        }

        // 2. Payload Extraction (Dual Format Detection)
        $sender_phone = '';
        $message_text = '';
        $push_name    = 'عميل واتساب';
        $from_me      = false;

        // Meta Cloud API Format
        if (isset($params['entry'][0]['changes'][0]['value']['messages'][0])) {
            $meta_msg     = $params['entry'][0]['changes'][0]['value']['messages'][0];
            $sender_phone = $meta_msg['from'] ?? '';
            $push_name    = $params['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? $push_name;
            
            if (isset($meta_msg['text']['body'])) {
                $message_text = $meta_msg['text']['body'];
            } elseif (isset($meta_msg['interactive']['button_reply']['title'])) {
                $message_text = $meta_msg['interactive']['button_reply']['title'];
            } elseif (isset($meta_msg['interactive']['list_reply']['title'])) {
                $message_text = $meta_msg['interactive']['list_reply']['title'];
            } elseif (isset($meta_msg['button']['text'])) {
                $message_text = $meta_msg['button']['text'];
            }
        }
        // Evolution API / Baileys Socket Format
        elseif (isset($params['data'])) {
            $data = $params['data'];
            $from_me      = !empty($data['key']['fromMe']);
            $sender_phone = $data['key']['remoteJid'] ?? '';
            $message_text = $data['message']['conversation'] ?? ($data['message']['extendedTextMessage']['text'] ?? '');
            $push_name    = $data['pushName'] ?? $push_name;
        }
        // Generic Webhook JSON
        else {
            $sender_phone = $params['phone'] ?? ($params['from'] ?? ($params['number'] ?? ''));
            $message_text = $params['message'] ?? ($params['text'] ?? ($params['body'] ?? ''));
            $push_name    = $params['name'] ?? ($params['pushName'] ?? $push_name);
            $from_me      = !empty($params['fromMe']);
        }

        $clean_phone = sanitize_text_field(preg_replace('/[^0-9]/', '', str_replace('@s.whatsapp.net', '', (string)$sender_phone)));
        $clean_name  = sanitize_text_field(wp_unslash((string)$push_name));
        $clean_text  = sanitize_textarea_field(wp_unslash((string)$message_text));

        if ($from_me || empty($clean_phone) || empty(trim($clean_text))) {
            return new WP_REST_Response(['status' => 'ignored', 'reason' => 'empty or outbound message'], 200);
        }

        // 3. Anti-Ban Loop Breaker & Cooldown Shield
        $phone_hash = md5($clean_phone);
        $cooldown_key = 'rsd_wa_cooldown_' . $phone_hash;
        if (get_transient($cooldown_key)) {
            return new WP_REST_Response([
                'status'  => 'cooldown',
                'message' => 'Sender in human handoff cooldown mode'
            ], 200);
        }

        $turns_key = 'rsd_wa_turns_' . $phone_hash;
        $turns_count = (int) get_transient($turns_key);
        if ($turns_count >= 5) {
            set_transient($cooldown_key, 1, 600); // 10-minute pause
            delete_transient($turns_key);
            
            $handoff_msg = "عزيزي العميل، تم إشعار فريق الاستشارات والحلول الرقمية وسيتواصل معك مستشارك المختص مباشرة لمتابعة كافة التفاصيل.";
            self::send_message($clean_phone, $handoff_msg);
            LeadManager::save_booking($clean_name, $clean_phone, 'تحويل لفريق المبيعات (Circuit Breaker)', 'تم تحويل المحادثة بعد تكرار الاستفسارات السريعة');

            return new WP_REST_Response([
                'status'  => 'circuit_breaker_triggered',
                'message' => 'Bot loop breaker activated'
            ], 200);
        }
        set_transient($turns_key, $turns_count + 1, 60);

        // 4. Multi-Agent AI Response Generation (if enabled)
        $ai_enabled = get_option('rsd_whatsapp_ai_enabled', '1');
        $reply_text = '';

        if ($ai_enabled === '1') {
            $system_prompt = "You are the Senior Luxury Direct Booking Consultant for RED SEA DIGITAL on WhatsApp. 
Keep replies concise (2-3 sentences), highly professional, polite, and persuasive. 
Guide the client on eliminating OTA commissions (15-30%) and building direct booking engines. 
Provide clear answers and invite them to schedule a 15-min strategy call.";

            $raw_reply = LLMProviderManager::generate($clean_text, [], [
                'system_prompt' => $system_prompt
            ]);

            $clean_reply = strip_tags($raw_reply);
            $clean_reply = preg_replace('/<[^>]*>/', '', $clean_reply);
            $clean_reply = html_entity_decode($clean_reply, ENT_QUOTES, 'UTF-8');
            $reply_text  = trim($clean_reply);

            self::send_message($clean_phone, $reply_text);
        }

        // 5. Lead Capture & CRM Storage
        if (preg_match('/(حجز|سعر|استشارة|booking|price|quote|meeting|consultation)/iu', $clean_text)) {
            LeadManager::save_booking($clean_name, $clean_phone, 'استفسار واتساب مباشر', $clean_text);
        }

        return new WP_REST_Response([
            'status'       => 'success',
            'sender'       => $clean_phone,
            'push_name'    => $clean_name,
            'reply'        => $reply_text,
            'mode'         => self::get_gateway_mode(),
            'auto_replied' => ($ai_enabled === '1')
        ], 200);
    }

    /**
     * Dispatch Proactive WhatsApp Outbound Pitch
     * 
     * @param string $customer_name
     * @param string $customer_phone
     * @param string $service_type
     * @return bool
     */
    public static function trigger_outbound($customer_name, $customer_phone, $service_type) {
        $clean_phone = sanitize_text_field(preg_replace('/[^0-9]/', '', (string)$customer_phone));
        $clean_name  = sanitize_text_field((string)$customer_name);
        $clean_srv   = sanitize_text_field((string)$service_type);

        if (empty($clean_phone)) return false;

        $prompt = "Write a warm, high-converting WhatsApp message to a VIP business lead named {$clean_name} interested in {$clean_srv}. Introduce RED SEA DIGITAL, explain how we eliminate 15-30% OTA commissions with custom direct booking tech, and invite them to schedule a 15-min discovery call. Keep it concise, friendly, and persuasive.";

        $outbound_text = LLMProviderManager::generate($prompt, [], [
            'system_prompt' => "You are Amr Ahmed, Founder & Lead Solution Architect at RED SEA DIGITAL. Output high-converting Arabic business copy directly without preamble or meta commentary."
        ]);

        $clean_pitch = strip_tags(html_entity_decode($outbound_text, ENT_QUOTES, 'UTF-8'));
        return self::send_message($clean_phone, trim($clean_pitch));
    }
}
