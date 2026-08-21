<?php
namespace RedSea\Services;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\Gateway\WhatsAppGateway;

/**
 * NotificationService - Central Dispatcher for Email, Telegram, Chatwoot & Webhook Alerts
 * Handles lead distribution, auto-confirmations, and multi-channel alerting.
 */
class NotificationService {

    /**
     * Initialize notification hooks
     */
    public static function init() {
        // Notification hooks & async listeners
    }

    /**
     * Send new booking / lead alert across all active channels
     * 
     * @param array $booking_data
     * @return bool
     */
    public static function send_booking_alert($booking_data) {
        $name    = sanitize_text_field($booking_data['customer_name'] ?? 'عميل جديد');
        $phone   = sanitize_text_field($booking_data['customer_phone'] ?? '');
        $service = sanitize_text_field($booking_data['service_type'] ?? 'استفسار مباشر');
        $details = sanitize_textarea_field($booking_data['booking_details'] ?? '');

        // 1. Email Alert to Admin / Concierge
        self::send_email_alert($name, $phone, $service, $details);

        // 2. Chatwoot Handoff if configured
        self::trigger_chatwoot_handoff($name, $phone, $service, $details);

        // 3. Outbound WhatsApp Confirmation to Customer
        self::trigger_whatsapp_outbound($name, $phone, $service);

        // 4. External Webhook Dispatch
        self::trigger_lead_webhook($booking_data);

        return true;
    }

    /**
     * Send lead alert (for Radar / Prospecting engine)
     */
    public static function send_lead_alert($lead_data) {
        return self::send_booking_alert($lead_data);
    }

    /**
     * Send Email Alert via wp_mail
     */
    public static function send_email_alert($name, $phone, $service, $details) {
        $to = get_option('admin_email');
        $subject = "🔥 [RED SEA DIGITAL] عميل جديد مهتم بـ {$service} — {$name}";
        $body = "تم استقبال طلب جديد من الموقع:\n\n" .
                "👤 الاسم: {$name}\n" .
                "📞 الهاتف: {$phone}\n" .
                "🏨 الخدمة: {$service}\n" .
                "📝 التفاصيل: {$details}\n\n" .
                "التوقيت: " . current_time('mysql');

        wp_mail($to, $subject, $body);
    }

    /**
     * Chatwoot Live CRM Integration
     */
    public static function trigger_chatwoot_handoff($customer_name, $customer_phone, $service_type, $details) {
        $chatwoot_enabled = get_option('rsd_chatwoot_enabled', '0');
        $chatwoot_url     = rtrim(get_option('rsd_chatwoot_url', ''), '/');
        $account_id       = get_option('rsd_chatwoot_account_id', '');
        $inbox_token      = get_option('rsd_chatwoot_inbox_token', '');
        $access_token     = get_option('rsd_chatwoot_access_token', '');

        if ($chatwoot_enabled !== '1' || empty($chatwoot_url) || empty($account_id) || empty($access_token)) {
            return;
        }

        $clean_phone = preg_replace('/[^0-9]/', '', (string)$customer_phone);

        // 1. Create / Find Contact
        $contact_url = "{$chatwoot_url}/api/v1/accounts/{$account_id}/contacts";
        $contact_res = wp_remote_post($contact_url, [
            'headers' => [
                'Content-Type'     => 'application/json',
                'api_access_token' => $access_token
            ],
            'body' => json_encode([
                'name'         => $customer_name,
                'phone_number' => '+' . $clean_phone,
                'identifier'   => md5($clean_phone)
            ]),
            'timeout' => 8
        ]);

        $contact_id = null;
        if (!is_wp_error($contact_res)) {
            $body = json_decode(wp_remote_retrieve_body($contact_res), true);
            $contact_id = $body['payload']['contact']['id'] ?? $body['id'] ?? null;
        }

        // 2. Create Conversation & Push Lead
        if ($contact_id) {
            $conv_url = "{$chatwoot_url}/api/v1/accounts/{$account_id}/conversations";
            wp_remote_post($conv_url, [
                'headers' => [
                    'Content-Type'     => 'application/json',
                    'api_access_token' => $access_token
                ],
                'body' => json_encode([
                    'source_id'  => $contact_id,
                    'inbox_id'   => $inbox_token,
                    'contact_id' => $contact_id,
                    'message'    => [
                        'content' => "🔥 *New Lead Captured from Red Sea AI Engine!*\n\n👤 *Client:* {$customer_name}\n📞 *Phone:* {$customer_phone}\n🏨 *Service/Trip:* {$service_type}\n📝 *Details:* {$details}"
                    ]
                ]),
                'timeout' => 8,
                'blocking' => false
            ]);
        }
    }

    /**
     * Outbound WhatsApp Auto-Confirmation
     */
    public static function trigger_whatsapp_outbound($customer_name, $customer_phone, $service_type) {
        $is_enabled = get_option('rsd_whatsapp_enabled', get_option('rsd_wa_autoresponder_enabled', '0'));
        if ($is_enabled !== '1') {
            return false;
        }

        $default_template = "أهلاً بك أستاذ {name}! ✨\nتم استلام طلبك لـ ({service}) بنجاح عبر Red Sea Digital.\nسيتواصل معك مستشارك في أقرب وقت.";
        $template = get_option('rsd_whatsapp_template', $default_template);

        $message_text = str_replace(
            ['{name}', '{service}'],
            [$customer_name, $service_type],
            $template
        );

        return WhatsAppGateway::send_message($customer_phone, $message_text);
    }

    /**
     * Generic External Lead Webhook Dispatcher
     */
    public static function trigger_lead_webhook($booking_data, $client_id = '') {
        $webhook_enabled = get_option('rsd_webhook_enabled', '0');
        $webhook_url     = get_option('rsd_webhook_url', '');
        $webhook_key     = get_option('rsd_webhook_api_key', '');

        if ($webhook_enabled !== '1' || empty($webhook_url)) {
            return;
        }

        $payload = [
            'event'          => 'lead_captured',
            'client_id'      => $client_id,
            'customer_name'  => sanitize_text_field($booking_data['customer_name'] ?? ''),
            'customer_phone' => sanitize_text_field($booking_data['customer_phone'] ?? ''),
            'service_type'   => sanitize_text_field($booking_data['service_type'] ?? ''),
            'booking_details'=> sanitize_text_field($booking_data['booking_details'] ?? ''),
            'created_at'     => current_time('mysql'),
            'site_url'       => get_site_url()
        ];

        wp_remote_post($webhook_url, [
            'method'  => 'POST',
            'headers' => [
                'Content-Type'  => 'application/json',
                'X-Api-Key'     => $webhook_key,
                'Authorization' => 'Bearer ' . $webhook_key
            ],
            'body'    => json_encode($payload),
            'timeout' => 8,
            'blocking'=> false
        ]);
    }
}
