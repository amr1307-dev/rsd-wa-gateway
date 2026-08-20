<?php
namespace RedSea\CRM;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * LeadManager - CRM, Contact & Booking Persistence Layer
 * Handles database operations for wp_rsd_bookings, wp_rsd_leads, CSV export, and webhook dispatches.
 */
class LeadManager {

    /**
     * Store new customer booking or inquiry
     */
    public static function save_booking($name, $phone, $service, $details = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_bookings';

        $clean_name    = sanitize_text_field(wp_unslash((string)$name));
        $clean_phone   = sanitize_text_field(preg_replace('/[^0-9]/', '', (string)$phone));
        $clean_service = sanitize_text_field(wp_unslash((string)$service));
        $clean_details = sanitize_textarea_field(wp_unslash((string)$details));

        if (empty($clean_phone) || empty($clean_name)) {
            return false;
        }

        // Anti-Duplicate Lead Throttle (Prevent duplicate inserts within 3 minutes)
        $recent_duplicate = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE customer_phone = %s AND created_at > (NOW() - INTERVAL 3 MINUTE) LIMIT 1",
            $clean_phone
        ));

        if ($recent_duplicate) {
            return (int) $recent_duplicate;
        }

        $inserted = $wpdb->insert(
            $table_name,
            [
                'customer_name'   => $clean_name,
                'customer_phone'  => $clean_phone,
                'service_type'    => $clean_service ?: 'استفسار عبر الشات الذكي',
                'booking_details' => $clean_details ?: 'طلب استشارة من الموقع',
                'created_at'      => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );

        if ($inserted) {
            $insert_id = $wpdb->insert_id;
            
            // Dispatch webhook if configured
            self::trigger_lead_webhook([
                'id'              => $insert_id,
                'customer_name'   => $clean_name,
                'customer_phone'  => $clean_phone,
                'service_type'    => $clean_service,
                'booking_details' => $clean_details,
                'created_at'      => current_time('mysql')
            ]);

            return $insert_id;
        }

        return false;
    }

    /**
     * Dispatch External CRM Webhook (Make / Zapier / Telegram)
     */
    public static function trigger_lead_webhook($booking_data) {
        $webhook_url = get_option('rsd_lead_webhook_url', '');
        if (empty($webhook_url) || !filter_var($webhook_url, FILTER_VALIDATE_URL)) {
            return;
        }

        wp_remote_post($webhook_url, [
            'method'    => 'POST',
            'blocking'  => false,
            'headers'   => ['Content-Type' => 'application/json'],
            'body'      => json_encode($booking_data),
            'timeout'   => 5
        ]);
    }

    /**
     * Get Total Leads Count
     */
    public static function get_total_leads_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_bookings';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            return 0;
        }
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    }

    /**
     * Get Recent CRM Records
     */
    public static function get_recent_leads($limit = 50) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_bookings';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            return [];
        }
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} ORDER BY id DESC LIMIT %d", $limit), ARRAY_A);
    }
}
