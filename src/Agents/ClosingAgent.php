<?php
namespace RedSea\Agents;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\Providers\LLMProviderManager;
use RedSea\CRM\LeadManager;
use RedSea\Services\NotificationService;

/**
 * ClosingAgent - Master SOP Objection Handler & Human Deal Desk Escalation Router
 * Manages consultative closing, 4-tier objection handling trees, and instant human handoff triggers.
 */
class ClosingAgent {

    /**
     * Detect if the inbound message triggers instant Human Deal Desk Escalation
     * 
     * @param string $message
     * @return array ['escalate' => bool, 'category' => string, 'reason' => string]
     */
    public static function check_human_escalation_triggers($message) {
        $msg = mb_strtolower($message, 'UTF-8');

        // Trigger 1: Custom Pricing & Heavy Negotiation
        if (preg_match('/(خصم|تخفيض|سعر خاص|مراعاة|ارخص|discount|special price|negotiate|custom quote)/iu', $msg)) {
            return [
                'escalate' => true,
                'category' => 'custom_pricing',
                'reason'   => 'طلب أسعار مخصصة وتفاوض مالي (Custom Pricing Negotiation)'
            ];
        }

        // Trigger 2: Contracts, Legal & Terms
        if (preg_match('/(عقد|عقود|اتفاقية|شروط التعاقد|قانوني|بنود|contract|agreement|nda|legal|terms)/iu', $msg)) {
            return [
                'escalate' => true,
                'category' => 'legal_contract',
                'reason'   => 'استفسار عن العقود والبنود القانونية (Legal / Contract Query)'
            ];
        }

        // Trigger 3: Complaints & Dissatisfaction
        if (preg_match('/(شكوى|مشكلة|الغاء|إلغاء|استرجاع|سيء|complaint|cancel|refund|dissatisfied)/iu', $msg)) {
            return [
                'escalate' => true,
                'category' => 'urgent_complaint',
                'reason'   => 'شكوى أو طلب إلغاء فوري (Urgent Complaint / Cancellation)'
            ];
        }

        // Trigger 4: Ready to Pay / Final Deal Closing
        if (preg_match('/(تحويل بنكي|رقم الحساب|طريقة الدفع|عايز اشترك|يلا نبدأ|ready to pay|bank transfer|invoice|iban|payment method)/iu', $msg)) {
            return [
                'escalate' => true,
                'category' => 'deal_closing_payment',
                'reason'   => 'الوصول لمرحلة الدفع والتعاقد النهائي (Ready for Payment / Deal Closing)'
            ];
        }

        return ['escalate' => false, 'category' => '', 'reason' => ''];
    }

    /**
     * Process Inbound WhatsApp Consultation & Handle Objections
     * 
     * @param string $clean_phone
     * @param string $clean_name
     * @param string $clean_text
     * @return string Generated Consultative Reply
     */
    public static function handle_consultation($clean_phone, $clean_name, $clean_text) {
        // 1. Check for Human Deal Desk Escalation
        $escalation = self::check_human_escalation_triggers($clean_text);
        if ($escalation['escalate']) {
            self::execute_human_handoff($clean_phone, $clean_name, $clean_text, $escalation);

            if ($escalation['category'] === 'deal_closing_payment') {
                return "أهلاً بك أستاذ {$clean_name}! يسعدنا جداً البدء معكم. يقوم الآن مهندس الحلول الرقمية (عمرو أحمد) بتجهيز بيانات الفاتورة والتعاقد الرسمي وسيتواصل معكم مباشرة على هذا الرقم خلال دقائق.";
            } elseif ($escalation['category'] === 'custom_pricing') {
                return "مرحباً أستاذ {$clean_name}، نقدر استفساركم تماماً. بما أن الباقات يتم تخصيصها بدقة حسب سعة المنشأة وعدد الغرف، سيتواصل معكم مستشار الحلول الأول مباشرة لمراجعة أفضل عرض استثماري مخصص لكم.";
            } elseif ($escalation['category'] === 'legal_contract') {
                return "أهلاً بك! نولي الجوانب القانونية وحماية البيانات أعلى درجات العناية. يقوم فريق إدارة العقود بمراجعة استفساركم وتزويدكم بمسودة الاتفاقية ونموذج عدم الإفصاح (NDA) فوراً.";
            } else {
                return "شكراً لتواصلك معنا. تم تحويل محادثتكم مباشرة إلى إدارة العمليات وسيتابع معكم أحد مسؤولينا التنفيذيين فورياً.";
            }
        }

        // 2. Objection Handling System Prompt
        $objection_prompt = "You are the Senior Executive Closer & Direct Booking Strategist for RED SEA DIGITAL on WhatsApp.
Your mission is to handle hospitality client inquiries and objections consultatively, calmly, and persuasively in 2-3 sentences.

OBJECTION HANDLING MATRIX:
1. 'Too expensive / غالي': Emphasize that this is not a cost, but an investment recovering $35,000–$95,000/yr in OTA commissions (15-25% saved on every direct booking). It pays for itself within 60 days.
2. 'Skeptical / مش متأكد': Reference their high Google Maps ratings and demand, offer a zero-risk 15-minute live screen demo showing the WhatsApp concierge closing bookings on a mobile phone.
3. 'Happy with current agency / شغالين مع شركة': Clarify that we DO NOT replace their marketing agency, but empower it by providing the 0% commission direct booking engine & WhatsApp concierge that converts their ad traffic (+35% conversion).
4. 'Send more info / ابعت تفاصيل': Send a high-level summary of the Executive Digital Audit Dossier and invite them to a 15-min discovery call to align room rates.

Keep the tone quiet luxury, highly respectful, authoritative, and concise. Never use generic corporate fluff.";

        $raw_reply = LLMProviderManager::generate($clean_text, [], [
            'system_prompt' => $objection_prompt
        ]);

        $clean_reply = strip_tags($raw_reply);
        $clean_reply = preg_replace('/<[^>]*>/', '', $clean_reply);
        $clean_reply = html_entity_decode($clean_reply, ENT_QUOTES, 'UTF-8');
        return trim($clean_reply);
    }

    /**
     * Execute Human Handoff Protocol & Notify Admin
     */
    private static function execute_human_handoff($phone, $name, $msg, $escalation) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_leads';

        // Update pipeline status in database
        $lead_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE contact_phone LIKE %s LIMIT 1",
            '%' . substr($phone, -8) . '%'
        ));

        if ($lead_id) {
            $new_status = ($escalation['category'] === 'deal_closing_payment') ? 'won' : 'negotiating';
            $wpdb->update($table_name, [
                'pipeline_status' => $new_status
            ], ['id' => $lead_id]);
        }

        // Save Booking / CRM Record
        LeadManager::save_booking(
            $name,
            $phone,
            '🚨 تصعيد بشري فوري (Deal Desk): ' . $escalation['category'],
            "سبب التصعيد: {$escalation['reason']}\nنص رسالة العميل: {$msg}"
        );

        // Send Notification via NotificationService
        if (class_exists('\RedSea\Services\NotificationService')) {
            try {
                NotificationService::send_booking_alert([
                    'customer_name'   => $name,
                    'customer_phone'  => $phone,
                    'service_type'    => '🚨 تصعيد صفقة جديد (Deal Desk: ' . $escalation['category'] . ')',
                    'booking_details' => "السبب: {$escalation['reason']}\nالرسالة: {$msg}"
                ]);
            } catch (\Throwable $t) {}
        }
    }
}
