<?php
namespace RedSea\Core;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\CRM\LeadManager;

/**
 * OutputCleaner - Robust Output & Response Sanitizer
 * Handles HTML stripping, markdown cleanup, XSS protection, and plain text normalization.
 */
class OutputCleaner {
    public static function clean($text) {
        if (empty($text)) return '';

        // 1. Strip raw script tags and XSS payloads
        $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $text);

        // 2. Extract and Strip structured JSON blocks (prevent code leak to visitor)
        if (preg_match('/\{\s*"(?:booking|customer_name|service_type|customer_phone)"[^}]*\}/u', $text, $matches)) {
            $json_str = $matches[0];
            $lead_data = json_decode($json_str, true);
            if (!empty($lead_data['customer_phone']) && preg_match('/[0-9]{8,}/', $lead_data['customer_phone']) && $lead_data['customer_phone'] !== 'Valid Phone Number') {
                if (class_exists(LeadManager::class)) {
                    LeadManager::save_booking(
                        $lead_data['customer_name'] ?? 'عميل جديد',
                        $lead_data['customer_phone'],
                        $lead_data['service_type'] ?? 'استشارة حجز مباشر',
                        $lead_data['booking_details'] ?? 'استفسار عبر الشات'
                    );
                } elseif (class_exists('RedSeaAIEngine')) {
                    \RedSeaAIEngine::save_booking(
                        $lead_data['customer_name'] ?? 'عميل جديد',
                        $lead_data['customer_phone'],
                        $lead_data['service_type'] ?? 'استشارة حجز مباشر',
                        $lead_data['booking_details'] ?? 'استفسار عبر الشات'
                    );
                }
            }
            $text = str_replace($json_str, '', $text);
        }

        // 3. Strip code block markers
        $text = str_replace(['```json', '```'], '', $text);

        // 4. Convert Markdown Bold (**text** or __text__) to clean strong tags (done before bullet removal to preserve **bold**)
        $text = preg_replace('/\*\*(.*?)\*\*/u', '<strong style="color:#0F172A;font-weight:800;">$1</strong>', $text);
        $text = preg_replace('/__(.*?)__/u', '<strong style="color:#0F172A;font-weight:800;">$1</strong>', $text);

        // 5. Remove leading bullet symbols (dots, dashes, single asterisks, emojis)
        $text = preg_replace('/^[\-\•\⁃\–\🔹]\s*|^\*(?!\*)\s*/mu', '', $text);

        // 6. Convert line breaks and blocks into clean, well-spaced organized paragraphs
        $text = trim($text);
        $paragraphs = preg_split('/\n\s*\n/', $text);
        $formatted_paragraphs = [];
        foreach ($paragraphs as $p) {
            $p = trim($p);
            if (!empty($p)) {
                // Also clean any leftover mid-line bullet symbols
                $p = preg_replace('/^[\-\•\⁃\–\🔹]\s*|^\*(?!\*)\s*/mu', '', $p);
                $p = nl2br($p);
                $formatted_paragraphs[] = "<p style='margin:0 0 12px 0;line-height:1.65;'>" . $p . "</p>";
            }
        }

        return trim(implode('', $formatted_paragraphs));
    }
}
