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
 * Enforces calm, realistic human consultation, zero emojis, and zero marketing fluff.
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
                return "أهلاً بك أستاذ {$clean_name}. يسعدنا البدء معكم. مهندس الحلول الرقمية عمرو أحمد يجهز حالياً تفاصيل الفاتورة والتعاقد وسيتواصل معك مباشرة على هذا الرقم خلال دقائق.";
            } elseif ($escalation['category'] === 'custom_pricing') {
                return "أهلاً بك أستاذ {$clean_name}. تفاصيل الباقات والتسعير المخصص يتم تحديدها بدقة حسب حجم المشروع، وسيتواصل معك مستشار الحلول لمراجعة الخطة المناسبة لك.";
            } elseif ($escalation['category'] === 'legal_contract') {
                return "أهلاً بك. بالنسبة للعقود ونماذج عدم الإفصاح (NDA)، سنزودك بمسودة الاتفاقية وشروط التعاقد للاطلاع عليها ومراجعتها.";
            } else {
                return "شكراً لتواصلك. تم تحويل استفسارك للإدارة المختصة وسيتابع معك أحد مسؤولينا مباشرة.";
            }
        }

        // 2. Multi-Domain Objection Handling & Consultation System Prompt
        if (!class_exists('\RedSea\Identity\SystemPromptBuilder')) {
            $builder_path = dirname(__DIR__) . '/Identity/SystemPromptBuilder.php';
            if (file_exists($builder_path)) require_once $builder_path;
        }

        $base_prompt = class_exists('\RedSea\Identity\SystemPromptBuilder')
            ? \RedSea\Identity\SystemPromptBuilder::build('', 'closer')
            : LLMProviderManager::build_system_prompt('', 'closer');

        $objection_matrix = "
<consultative_closing_matrix>
  قواعد صياغة الرد:
  - ممنوع استخدام الإيموجيز نهائياً.
  - ممنوع العبارات التسويقية المبتذلة والوعود المبالغ فيها.
  - الرد في جملتين إلى ثلاث جمل واضحة وواقعية.

  التعامل مع الاستفسارات والاعتراضات:
  1. استفسارات المتاجر الإلكترونية: وضح أننا نبني متاجر سريعة مع ربط بوابات الدفع (فيزا، انستاباي، فوري) ونظام متابعة الطلبات بالواتساب.
  2. استفسارات الفنادق والضيافة: وضح ميزات محرك الحجز المباشر بدون عمولات وتكامل كونسيرج الواتساب.
  3. استفسارات الشركات وصفحات الهبوط: وضح تصميم صفحات الهبوط الإعلانية ولوحة إدارة العملاء.
  4. اعتراض 'السعر مرتفع': وضح بهدوء أن التكلفة لمرة واحدة بدون اشتراكات متكررة وتغطي بناء وتجهيز المنظومة كاملة.
  5. اعتراض 'مش متأكد': اعرض استعراض نموذج عملي حي (Demo) للاطلاع على طريقة عمل المنظومة على الموبايل.
  6. اعتراض 'شغالين مع وكالة تسويق': وضح أن حلولنا برمجية تكمل عمل وكالة التسويق لتسهيل استقبال الطلبات وتأكيدها.
</consultative_closing_matrix>";

        $system_prompt = $base_prompt . "\n\n" . $objection_matrix;

        $raw_reply = LLMProviderManager::generate($clean_text, [], [
            'system_prompt' => $system_prompt
        ]);

        $clean_reply = strip_tags($raw_reply);
        $clean_reply = preg_replace('/<[^>]*>/', '', $clean_reply);
        // Strip any accidental emojis
        $clean_reply = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $clean_reply);
        $clean_reply = html_entity_decode($clean_reply, ENT_QUOTES, 'UTF-8');
        return trim($clean_reply);
    }

    /**
     * Execute Human Handoff Protocol & Notify Admin
     */
    private static function execute_human_handoff($phone, $name, $msg, $escalation) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_leads';

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

        LeadManager::save_booking(
            $name,
            $phone,
            'تصعيد بشري: ' . $escalation['category'],
            "السبب: {$escalation['reason']}\nالرسالة: {$msg}"
        );

        if (class_exists('\RedSea\Services\NotificationService')) {
            try {
                NotificationService::send_booking_alert([
                    'customer_name'   => $name,
                    'customer_phone'  => $phone,
                    'service_type'    => 'تصعيد صفقة: ' . $escalation['category'],
                    'booking_details' => "السبب: {$escalation['reason']}\nالرسالة: {$msg}"
                ]);
            } catch (\Throwable $t) {}
        }
    }
}
