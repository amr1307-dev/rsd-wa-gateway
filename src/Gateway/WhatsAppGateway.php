<?php
namespace RedSea\Gateway;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\Providers\LLMProviderManager;
use RedSea\CRM\LeadManager;

/**
 * WhatsAppGateway - WhatsApp 2-Way Socket Bridge & Anti-Ban Gate
 * Interfaces Evolution API, Baileys WebSocket instances, Meta Cloud API, and human handoff breakers.
 */
class WhatsAppGateway {

    /**
     * Dispatch WhatsApp message to remote recipient
     */
    public static function send_message($phone, $message) {
        $clean_phone = sanitize_text_field(preg_replace('/[^0-9]/', '', (string)$phone));
        if (empty($clean_phone) || empty(trim($message))) return false;

        $gateway_url = rtrim(get_option('rsd_whatsapp_api_url', ''), '/');
        $instance    = get_option('rsd_whatsapp_instance', 'rsd_live');
        $api_key     = get_option('rsd_whatsapp_api_key', '');

        // Anti-Ban Human Typing Simulation: Calculate dynamic typing delay proportional to length (2-4.5s)
        $msg_len = mb_strlen($message);
        $dynamic_delay = min(4500, max(2000, 2000 + ($msg_len * 15) + rand(100, 400)));

        // 1. Evolution API / Baileys Socket Gateway
        if (!empty($gateway_url)) {
            $send_url = strpos($gateway_url, '/message/sendText') !== false 
                ? $gateway_url 
                : "{$gateway_url}/message/sendText/{$instance}";

            wp_remote_post($send_url, [
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
                'timeout'   => 8
            ]);
            return true;
        }

        // 2. Meta Cloud API Fallback
        $cloud_token = get_option('rsd_whatsapp_cloud_token', '');
        $phone_id    = get_option('rsd_whatsapp_phone_number_id', '');

        if (!empty($cloud_token) && !empty($phone_id)) {
            $meta_url = "https://graph.facebook.com/v19.0/{$phone_id}/messages";
            wp_remote_post($meta_url, [
                'method'    => 'POST',
                'blocking'  => false,
                'headers'   => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $cloud_token
                ],
                'body'      => json_encode([
                    'messaging_product' => 'whatsapp',
                    'to'                => $clean_phone,
                    'type'              => 'text',
                    'text'              => ['body' => $message]
                ]),
                'timeout'   => 8
            ]);
            return true;
        }

        return false;
    }

    /**
     * Dispatch Proactive WhatsApp Outbound Pitch
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
