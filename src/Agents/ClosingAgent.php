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
  1. استفسارات المتاجر الإلكترونية (E-Commerce): اشرح حلول بناء المتاجر فائقة السرعة، ربط بوابات الدفع الإلكتروني (فيزا/انستاباي/فوري)، واسترجاع السلات المتروكة بالواتساب.
  2. استفسارات الفنادق والضيافة: اشرح محرك الحجز المباشر وتوفير عمولات الـ OTAs (15-25%) مع كونسيرج الواتساب.
  3. استفسارات الشركات والـ Lead Gen: اشرح صفحات الهبوط الموجهة للإعلانات ولوحات الـ CRM لإدارة العملاء.
  4. اعتراض 'السعر مرتفع': وضح أنه استثمار ذكي يسترد تكلفته سريعاً من زيادة المبيعات واسترداد العمولات.
  5. اعتراض 'مش متأكد': اعرض استعراض ديمو حي ومكالمة سريعة لمدة 15 دقيقة لمشاهدة المنظومة تعمل على الموبايل.
  6. اعتراض 'شغالين مع وكالة': وضح أننا نكامل حلول الوكالة بتوفير منصة البيع/الحجز المؤتمتة التي ترفع تحويل حملاتهم (+35%).

  قاعدة صارمة: لا ترفض أي نشاط إطلاقاً، وتحدث بأسلوب مستشار مبيعات خبير وودود في 2 إلى 3 جمل مركزة.
</consultative_closing_matrix>";

        $system_prompt = $base_prompt . "\n\n" . $objection_matrix;

        $raw_reply = LLMProviderManager::generate($clean_text, [], [
            'system_prompt' => $system_prompt
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
