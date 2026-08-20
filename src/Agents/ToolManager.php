<?php
namespace RedSea\Agents;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\RAG\KnowledgeBaseManager;
use RedSea\CRM\LeadManager;

/**
 * ToolManager - Agent Tool Registry & Execution Dispatcher
 */
class ToolManager {

    public static function get_available_tools() {
        return array(
            'check_live_catalog' => array(
                'name' => 'check_live_catalog',
                'description' => 'سحب واستعلام برامج الرحلات والغرف الفندقية ومنتجات المتجر الحية مع الأسعار والروابط المباشرة',
            ),
            'vector_rag_search' => array(
                'name' => 'vector_rag_search',
                'description' => 'البحث الدلالي المتجهي الفائق في قاعدة المعرفة لاستخراج المعلومات التفصيلية والضمانات',
            ),
            'instant_lead_booking' => array(
                'name' => 'instant_lead_booking',
                'description' => 'تسجيل حجز العميل واصتياد رقم الهاتف والاسم تلقائياً في الـ CRM وتفعيل رسالة الواتساب المباشرة',
            ),
            'calculate_direct_savings' => array(
                'name' => 'calculate_direct_savings',
                'description' => 'حساب نسبة وتكلفة العمولات الضائعة للمنصات الوسيطة وتوضيح الصافي المالي للعميل',
            )
        );
    }

    public static function execute_tool($tool_name, $args = array()) {
        switch ($tool_name) {
            case 'calculate_direct_savings':
                $revenue = floatval($args['monthly_revenue'] ?? 10000);
                $savings_min = $revenue * 0.15;
                $savings_max = $revenue * 0.30;
                return sprintf("📊 حساب التوفير المالي الصافي:\nعند تحقيق إيرادات شهرياً بقيمة $%s، يتم توفير من $%s إلى $%s سنوياً كان يتم دفعها كعمولات للمنصات الوسيطة!", number_format($revenue), number_format($savings_min * 12), number_format($savings_max * 12));

            case 'vector_rag_search':
                $query = sanitize_text_field($args['query'] ?? '');
                $chunks = KnowledgeBaseManager::search_similar_chunks($query, 3);
                return !empty($chunks) ? implode("\n\n", $chunks) : 'لم يتم العثور على مقاطع مطابقة.';

            case 'check_live_catalog':
                return KnowledgeBaseManager::get_live_booking_context() . "\n" . KnowledgeBaseManager::get_wp_wc_live_context();

            case 'instant_lead_booking':
                $name = sanitize_text_field($args['name'] ?? 'عميل جديد');
                $phone = sanitize_text_field($args['phone'] ?? '');
                $service = sanitize_text_field($args['service'] ?? 'جلسة استشارية');
                if (!empty($phone)) {
                    LeadManager::save_booking($name, $phone, $service, 'حجز تلقائي عبر محرك الأيجنت الذكي');
                    return "✅ تم تسجيل حجز العميل ($name - $phone) بنجاح وإرسال الرسالة التلقائية عبر الواتساب.";
                }
                return 'تعذر التسجيل: رقم الهاتف مطلوب.';

            default:
                return 'أداة غير معروفة.';
        }
    }
}
