<?php
namespace RedSea\Security;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * RateLimiter - Enterprise Sliding Window Rate Limiter & Anti-Spam Gate
 * Protects AI Inference endpoints and WhatsApp Webhooks using high-performance WordPress Transients.
 */
class RateLimiter {

    /**
     * Get Real Client IP Address (supporting Cloudflare & Reverse Proxies)
     * 
     * @return string
     */
    public static function get_client_ip() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = sanitize_text_field($_SERVER['HTTP_CF_CONNECTING_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']));
            $ip = trim($parts[0]);
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = sanitize_text_field($_SERVER['HTTP_X_REAL_IP']);
        } else {
            $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
    }

    /**
     * Check IP Rate Limit for Web Chat Visitors
     * 
     * @param int $limit Max allowed requests within window (default: 15 req/min)
     * @param int $window_seconds Window duration in seconds (default: 60s)
     * @param string $lang Interface language ('ar' | 'en' | etc.)
     * @return array ['allowed' => bool, 'current' => int, 'limit' => int, 'message' => string]
     */
    public static function check_ip_limit($limit = 15, $window_seconds = 60, $lang = 'ar') {
        $ip = self::get_client_ip();
        $key = 'rsd_ratelimit_ip_' . md5($ip);

        $current_count = (int) get_transient($key);

        if ($current_count >= $limit) {
            $msg = ($lang === 'ar')
                ? 'عزيزي الزائر، تم إرسال عدد كبير من الرسائل في وقت قصير. يرجى الانتظار دقيقة واحدة قبل المحاولة مجدداً.'
                : 'Too many requests. Please wait a minute before sending another message.';

            return [
                'allowed' => false,
                'current' => $current_count,
                'limit'   => $limit,
                'message' => $msg
            ];
        }

        set_transient($key, $current_count + 1, $window_seconds);

        return [
            'allowed' => true,
            'current' => $current_count + 1,
            'limit'   => $limit,
            'message' => 'OK'
        ];
    }

    /**
     * Check Phone Rate Limit for WhatsApp Inbound Messages
     * 
     * @param string $phone Client Phone Number
     * @param int $limit Max allowed messages within window (default: 20 msg/min)
     * @param int $window_seconds Window duration in seconds (default: 60s)
     * @return array ['allowed' => bool, 'current' => int, 'limit' => int, 'message' => string]
     */
    public static function check_phone_limit($phone, $limit = 20, $window_seconds = 60) {
        $clean_phone = sanitize_text_field(preg_replace('/[^0-9]/', '', (string)$phone));
        if (empty($clean_phone)) {
            return ['allowed' => false, 'current' => 0, 'limit' => $limit, 'message' => 'Invalid Phone Number'];
        }

        $key = 'rsd_ratelimit_wa_' . md5($clean_phone);
        $current_count = (int) get_transient($key);

        if ($current_count >= $limit) {
            return [
                'allowed' => false,
                'current' => $current_count,
                'limit'   => $limit,
                'message' => 'Rate limit exceeded for WhatsApp sender'
            ];
        }

        set_transient($key, $current_count + 1, $window_seconds);

        return [
            'allowed' => true,
            'current' => $current_count + 1,
            'limit'   => $limit,
            'message' => 'OK'
        ];
    }
}
