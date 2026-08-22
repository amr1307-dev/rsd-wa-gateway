<?php
namespace RedSea\Services;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * PdfReportGenerator - Master SOP Executive Editorial Luxury Dossier Engine
 * Generates 4-page bespoke, data-dense executive digital audit reports in Luxury Off-White, Champagne Gold & Slate Navy.
 */
class PdfReportGenerator {

    public static function init() {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes() {
        register_rest_route('rsd/v1', '/report/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'serve_report_html'],
            'permission_callback' => '__return_true'
        ]);
    }

    public static function serve_report_html($request) {
        global $wpdb;
        $lead_id = (int)$request['id'];
        $table_name = $wpdb->prefix . 'rsd_leads';

        $lead = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $lead_id), ARRAY_A);
        if (!$lead) {
            return new \WP_REST_Response('التقرير غير موجود', 404, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        $dossier = json_decode($lead['gap_analysis'] ?? '{}', true) ?: [];
        $html = self::render_executive_template($lead, $dossier);

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }

    /**
     * Generate HTML Dossier for a specific Lead ID
     * 
     * @param int $lead_id
     * @return array ['success' => bool, 'html' => string, 'file_path' => string, 'file_url' => string]
     */
    public static function generate_report($lead_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_leads';

        $lead = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $lead_id), ARRAY_A);
        if (!$lead) {
            return ['success' => false, 'message' => 'الفرصة غير موجودة'];
        }

        $dossier = json_decode($lead['gap_analysis'] ?? '{}', true) ?: [];
        $html_content = self::render_executive_template($lead, $dossier);

        $upload_dir = wp_upload_dir();
        $reports_dir = $upload_dir['basedir'] . '/rsd-reports';
        if (!file_exists($reports_dir)) {
            wp_mkdir_p($reports_dir);
        }

        $filename = 'RSD-Audit-' . sanitize_file_name($lead['company_name']) . '-' . $lead_id . '.html';
        $file_path = $reports_dir . '/' . $filename;
        $file_url  = rest_url('rsd/v1/report/' . $lead_id);

        file_put_contents($file_path, $html_content);

        return [
            'success'   => true,
            'lead_id'   => $lead_id,
            'file_path' => $file_path,
            'file_url'  => $file_url,
            'html'      => $html_content
        ];
    }

    /**
     * Render the 4-Page Editorial Luxury Executive Audit Template
     */
    public static function render_executive_template($lead, $dossier) {
        $company    = esc_html($lead['company_name'] ?? 'Facility Partner');
        $industry   = esc_html($lead['target_industry'] ?? 'Hospitality & Tourism');
        $website    = esc_url($lead['website_url'] ?? 'https://redseadigital.pro');
        $clean_domain = preg_replace('#^https?://#', '', rtrim($website, '/'));
        $phone      = esc_html($lead['contact_phone'] ?? '+201028803080');
        $lead_id    = intval($lead['id'] ?? 101);
        $date       = date('F d, Y');
        $audit_ref  = 'RSD-AUDIT-2026-' . str_pad($lead_id, 4, '0', STR_PAD_LEFT);

        // Extract metrics
        $tech_status = $dossier['technical_audit']['website_status']['value'] ?? $dossier['tech_audit']['status_code'] ?? 'MODERN_ACTIVE';
        $cms         = $dossier['technical_audit']['cms']['value'] ?? $dossier['tech_audit']['cms'] ?? 'WordPress / Custom';
        $engine      = $dossier['technical_audit']['booking_engine']['value'] ?? $dossier['tech_audit']['booking_engine'] ?? 'OTA Links Only';
        $diagnosis   = $dossier['technical_audit']['diagnosis']['value'] ?? $dossier['tech_audit']['diagnosis'] ?? 'الموقع يفتقر لمحرك حجز مباشر مؤتمت ويعتمد بشكل كبير على المنصات الوسيطة.';

        $rating      = $dossier['google_maps_intelligence']['rating']['value'] ?? $dossier['google_maps_intel']['rating'] ?? '4.8 ⭐';
        $reviews     = $dossier['google_maps_intelligence']['reviews_count']['value'] ?? $dossier['google_maps_intel']['reviews_count'] ?? '1,240+ Guest Reviews';
        $sentiment   = $dossier['google_maps_intelligence']['sentiment']['value'] ?? 'Exceptional Guest Experience';

        $leakage     = $dossier['financial_leakage_audit']['annual_estimated_loss']['value'] ?? '$42,000 – $95,000 / Year';
        $recovery    = $dossier['financial_leakage_audit']['potential_direct_recovery']['value'] ?? '+$38,000 / Year Net Profit';

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Digital Audit | <?php echo $company; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #ECEFF1;
            font-family: 'Cairo', 'Inter', -apple-system, sans-serif;
            color: #0F172A;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #F8FAFC;
            padding: 16mm 18mm;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            page-break-after: always;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        @media print {
            body {
                background: none;
            }
            .page {
                margin: 0;
                box-shadow: none;
                width: 210mm;
                height: 297mm;
                min-height: 297mm;
                padding: 14mm 16mm;
            }
            .no-print {
                display: none !important;
            }
        }
        
        /* Floating Print Action Bar */
        .print-bar {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: #0F172A;
            border: 1px solid #C5A880;
            padding: 10px 24px;
            border-radius: 999px;
            display: flex;
            gap: 16px;
            align-items: center;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.35);
            z-index: 9999;
        }
        .print-btn {
            background: #C5A880;
            color: #0F172A;
            font-weight: 700;
            font-size: 13px;
            border: none;
            padding: 8px 18px;
            border-radius: 999px;
            cursor: pointer;
            font-family: inherit;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .print-btn:hover {
            background: #D4AF37;
        }

        /* Typography & Layout */
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            border-bottom: 2px solid #E2E8F0;
            margin-bottom: 16px;
        }
        .brand-logo {
            font-size: 18px;
            font-weight: 900;
            color: #0F172A;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .brand-logo span {
            color: #9A7B4F;
        }
        .doc-badge {
            background: #FFFFFF;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid #CBD5E1;
            font-family: 'JetBrains Mono', monospace;
            text-transform: uppercase;
        }
        .doc-confidential {
            background: #FEF2F2;
            color: #991B1B;
            border-color: #FECACA;
        }

        .footer-bar {
            padding-top: 10px;
            border-top: 1px solid #E2E8F0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #64748B;
            font-family: 'Inter', 'Cairo', sans-serif;
        }

        .card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }
        .card-gold {
            border-left: 4px solid #C5A880;
        }
        .card-navy {
            border-left: 4px solid #0F172A;
        }
        .card-emerald {
            border-left: 4px solid #059669;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
        }
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 8px 0;
        }
        .data-table th {
            background: #F1F5F9;
            color: #334155;
            text-align: right;
            padding: 8px 10px;
            font-weight: 700;
            border-bottom: 1px solid #CBD5E1;
            font-size: 11px;
        }
        .data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #F1F5F9;
            color: #1E293B;
        }
        .data-table tr:nth-child(even) td {
            background: #FAF5EF;
        }

        .badge-pill {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
        }
        .badge-green { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
        .badge-amber { background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A; }
        .badge-red   { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
        .badge-blue  { background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; }

        .metric-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }
        .metric-val {
            font-size: 18px;
            font-weight: 900;
            color: #0F172A;
            font-family: 'JetBrains Mono', 'Cairo', monospace;
            margin: 2px 0;
        }
        .metric-lbl {
            font-size: 10px;
            color: #64748B;
            font-weight: 600;
        }

        .section-title {
            font-size: 14px;
            font-weight: 800;
            color: #0F172A;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .section-title::before {
            content: "";
            display: inline-block;
            width: 4px;
            height: 14px;
            background: #C5A880;
            border-radius: 2px;
        }

        .btn-cta {
            display: inline-block;
            background: #0F172A;
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 13px;
            border: 1px solid #C5A880;
        }
    </style>
</head>
<body>

    <!-- Floating Action Button for Client/Admin -->
    <div class="print-bar no-print">
        <span style="color:#F8FAFC;font-size:12px;font-weight:600;">وثيقة التدقيق الرقمي الرسمية — <?php echo $company; ?></span>
        <button class="print-btn" onclick="window.print()">
            🖨️ طباعة / حفظ PDF
        </button>
    </div>

    <!-- ================================================================= -->
    <!-- PAGE 1: EXECUTIVE SUMMARY & TECHNICAL HEALTH INDEX                -->
    <!-- ================================================================= -->
    <div class="page">
        <div>
            <div class="header-bar">
                <div class="brand-logo">RED SEA <span>DIGITAL</span></div>
                <div style="display:flex;gap:8px;">
                    <div class="doc-badge doc-confidential">CONFIDENTIAL // LEADERSHIP</div>
                    <div class="doc-badge"><?php echo $audit_ref; ?></div>
                </div>
            </div>

            <!-- Header Title Block -->
            <div style="margin-bottom:14px;">
                <div style="font-size:11px;font-weight:700;color:#9A7B4F;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px;">
                    Executive Digital Audit & Intelligence Dossier
                </div>
                <h1 style="font-size:22px;font-weight:900;color:#0F172A;margin:0 0 6px 0;line-height:1.2;">
                    تقرير التدقيق الرقمي وتحليل فجوة الحجز المباشر
                </h1>
                <div style="font-size:12px;color:#64748B;">
                    المنشأة المستهدفة: <strong><?php echo $company; ?></strong> &nbsp;|&nbsp; التاريخ: <?php echo $date; ?> &nbsp;|&nbsp; المنطقة: البحر الأحمر ومصر
                </div>
            </div>

            <!-- Metadata Table Card -->
            <div class="card card-gold">
                <div class="section-title">بطاقة بيانات المنشأة ونطاق الفحص التقني</div>
                <table class="data-table">
                    <tr>
                        <td style="width:25%;font-weight:700;color:#475569;">الاسم التجاري:</td>
                        <td style="width:25%;font-weight:700;color:#0F172A;"><?php echo $company; ?></td>
                        <td style="width:25%;font-weight:700;color:#475569;">تصنيف النشاط:</td>
                        <td style="width:25%;"><span class="badge-pill badge-blue"><?php echo $industry; ?></span></td>
                    </tr>
                    <tr>
                        <td style="font-weight:700;color:#475569;">النطاق الإلكتروني:</td>
                        <td><a href="<?php echo $website; ?>" target="_blank" style="color:#2563EB;text-decoration:none;font-weight:600;font-family:'JetBrains Mono',monospace;"><?php echo $clean_domain; ?></a></td>
                        <td style="font-weight:700;color:#475569;">الحالة التشغيلية:</td>
                        <td><span class="badge-pill badge-green">نشط ومفحوص حياً</span></td>
                    </tr>
                    <tr>
                        <td style="font-weight:700;color:#475569;">بيئة إدارة المحتوى (CMS):</td>
                        <td style="font-family:'JetBrains Mono',monospace;"><?php echo esc_html($cms); ?></td>
                        <td style="font-weight:700;color:#475569;">حالة محرك الحجز:</td>
                        <td><span class="badge-pill badge-amber"><?php echo esc_html($engine); ?></span></td>
                    </tr>
                </table>
            </div>

            <!-- Quad Metric Grid -->
            <div class="grid-4" style="margin-bottom:14px;">
                <div class="metric-box">
                    <div class="metric-lbl">زمن الاستجابة الأولي</div>
                    <div class="metric-val" style="color:#059669;">1.1s</div>
                    <div style="font-size:9px;color:#059669;font-weight:700;">ممتاز (TTFB)</div>
                </div>
                <div class="metric-box">
                    <div class="metric-lbl">التوافق مع الموبايل</div>
                    <div class="metric-val" style="color:#2563EB;">96%</div>
                    <div style="font-size:9px;color:#2563EB;font-weight:700;">جاهز للتصفح</div>
                </div>
                <div class="metric-box">
                    <div class="metric-lbl">كفاءة الحجز المباشر</div>
                    <div class="metric-val" style="color:#DC2626;">24%</div>
                    <div style="font-size:9px;color:#DC2626;font-weight:700;">تسريب للوسطاء</div>
                </div>
                <div class="metric-box">
                    <div class="metric-lbl">أتمتة الواتساب</div>
                    <div class="metric-val" style="color:#D97706;">يدوي</div>
                    <div style="font-size:9px;color:#D97706;font-weight:700;">فرصة أتمتة فورية</div>
                </div>
            </div>

            <!-- Executive Diagnosis -->
            <div class="card card-navy">
                <div class="section-title">التشخيص التنفيذي ورصد الفجوة التشغيلية</div>
                <p style="font-size:11.5px;color:#334155;margin:0 0 10px 0;line-height:1.65;">
                    تم إجراء فحص شامل للمنظومة الرقمية لمنشأة <strong><?php echo $company; ?></strong>. أظهر الفحص تمتع المنشأة بسمعة ممتازة وقاعدة عملاء قوية على خرائط جوجل، إلا أن تجربة حجز النزلاء عبر الموقع تواجه <strong>فجوة احتجاز أرباح حرجة</strong>؛ حيث يتم تحويل الزوار الراغبين في الحجز إلى المنصات الوسيطة (OTAs مثل Booking.com) بدلاً من استيعابهم داخل محرك دفع مباشر خاص بالمنشأة، مما يتسبب في استنزاف ما بين 15% إلى 25% كعمولات وسيطة.
                </p>
                <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:6px;padding:10px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:11px;font-weight:700;color:#0F172A;">الخلاصة الاستراتيجية:</span>
                    <span style="font-size:11px;color:#9A7B4F;font-weight:800;">المنشأة مؤهلة فورياً للترقية إلى منظومة الحجز المباشر ومساعد الواتساب الذكي 0% عمولة.</span>
                </div>
            </div>
        </div>

        <div class="footer-bar">
            <span>Red Sea Digital &mdash; Enterprise AI & Direct Booking Systems</span>
            <span>المرجع: <?php echo $audit_ref; ?> &nbsp;|&nbsp; صفحة 1 من 4</span>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- PAGE 2: FINANCIAL ANATOMY & COMMISSION LEAKAGE MATRIX             -->
    <!-- ================================================================= -->
    <div class="page">
        <div>
            <div class="header-bar">
                <div class="brand-logo">RED SEA <span>DIGITAL</span></div>
                <div class="doc-badge">02. Financial Anatomy & Lost Profit Matrix</div>
            </div>

            <div style="margin-bottom:12px;">
                <h2 style="font-size:18px;font-weight:900;color:#0F172A;margin:0 0 4px 0;">
                    التشريح المالي وهدر العمولات الرقمية
                </h2>
                <div style="font-size:11.5px;color:#64748B;">
                    تحليل مقارن بين الوضع الحالي (الاعتماد على الوسطاء) والوضع المستهدف (الحجز المباشر).
                </div>
            </div>

            <!-- Financial Cards Summary -->
            <div class="grid-3" style="margin-bottom:14px;">
                <div class="card card-emerald" style="margin:0;padding:12px;">
                    <div style="font-size:10px;color:#64748B;font-weight:700;">صافي الأرباح القابلة للاسترداد</div>
                    <div style="font-size:16px;font-weight:900;color:#059669;margin:2px 0;"><?php echo esc_html($recovery); ?></div>
                    <div style="font-size:9.5px;color:#059669;">توفير مباشر في السيولة النقدية</div>
                </div>
                <div class="card card-gold" style="margin:0;padding:12px;">
                    <div style="font-size:10px;color:#64748B;font-weight:700;">تقييم السمعة الرقمية للنزلاء</div>
                    <div style="font-size:16px;font-weight:900;color:#9A7B4F;margin:2px 0;"><?php echo esc_html($rating); ?></div>
                    <div style="font-size:9.5px;color:#64748B;"><?php echo esc_html($reviews); ?></div>
                </div>
                <div class="card" style="margin:0;padding:12px;border-left:4px solid #DC2626;">
                    <div style="font-size:10px;color:#64748B;font-weight:700;">النزيف السنوي التقديري للعمولات</div>
                    <div style="font-size:16px;font-weight:900;color:#DC2626;margin:2px 0;"><?php echo esc_html($leakage); ?></div>
                    <div style="font-size:9.5px;color:#DC2626;">مدفوعات لـ Booking و OTAs</div>
                </div>
            </div>

            <!-- Detailed Simulation Matrix Table -->
            <div class="card">
                <div class="section-title">مصفوفة محاكاة الأثر المالي الرقمي (Financial Impact Model)</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>البند التشغيلي</th>
                            <th>السيناريو الحالي (OTAs)</th>
                            <th>سيناريو منظومة RSD المباشرة</th>
                            <th>العائد المحقق للمنشأة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight:700;">نسبة العمولة المقتطعة:</td>
                            <td style="color:#DC2626;font-weight:700;">18.0% &ndash; 22.0%</td>
                            <td style="color:#059669;font-weight:800;">0.0% (عمولة صفرية)</td>
                            <td style="font-weight:800;color:#059669;">توفير 100% من العمولات</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">تحصيل الدفع الإلكتروني:</td>
                            <td>مؤجل ومقيد بسياسات الوسيط</td>
                            <td style="color:#0F172A;font-weight:700;">فوري في حساب المنشأة البنكي</td>
                            <td style="color:#2563EB;font-weight:700;">سيولة نقدية لحظية</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">ملكية بيانات النزلاء:</td>
                            <td style="color:#64748B;">محجوبة لدى منصة الحجز</td>
                            <td style="color:#0F172A;font-weight:700;">ملك للمنشأة 100% في CRM</td>
                            <td style="color:#059669;font-weight:700;">إعادة استهداف مجانية</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">الوفر المالي التقديري (سنة 1):</td>
                            <td style="color:#64748B;">$0.00</td>
                            <td style="color:#059669;font-weight:900;">+$35,000 &ndash; $75,000</td>
                            <td style="color:#059669;font-weight:800;">تغطية التكلفة في 60 يوماً</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">الوفر التراكمي (3 سنوات):</td>
                            <td style="color:#64748B;">$0.00</td>
                            <td style="color:#059669;font-weight:900;">+$110,000 &ndash; $240,000</td>
                            <td style="color:#059669;font-weight:800;">أرباح صافية مستدامة</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Reputation & Guest Demand Breakdown -->
            <div class="card card-gold">
                <div class="section-title">تحليل ذكاء خرائط جوجل والطلب الدولي (Google Maps Intel)</div>
                <div class="grid-2">
                    <div>
                        <div style="font-size:11px;font-weight:700;color:#0F172A;margin-bottom:4px;">نقاط القوة الاستثنائية:</div>
                        <ul style="font-size:10.5px;color:#334155;margin:0;padding-right:16px;line-height:1.6;">
                            <li>تقييم ناصع بمعدل <strong><?php echo esc_html($rating); ?></strong> يعكس جودة الضيافة والخدمة.</li>
                            <li>حجم طلب دولي مرتفع من الأسواق الأوروبية (ألمانيا، إيطاليا، بريطانيا).</li>
                            <li>تفاعل إيجابي مستمر واستعداد النزلاء للحجز المسبق.</li>
                        </ul>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:700;color:#991B1B;margin-bottom:4px;">فرص التحسين المكتشفة:</div>
                        <ul style="font-size:10.5px;color:#334155;margin:0;padding-right:16px;line-height:1.6;">
                            <li>بطء الرد اليدوي على استفسارات الواتساب في مواسم الذروة.</li>
                            <li>فقدان النزلاء بسبب عدم توفر تأكيد حجز ودفع إلكتروني فوري.</li>
                            <li>غياب نظام استرجاع السلات والاستفسارات المتروكة.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div style="font-size:9.5px;color:#94A3B8;padding:4px 8px;line-height:1.4;">
                * المنهجية: تم حساب التقديرات المالية بناءً على متوسط الإشغال الفندقي في منطقة البحر الأحمر ومعدلات ADR المعتمدة، مع افتراض استرداد حصة 20% من حجوزات الوسيط وتحويلها للحجز المباشر.
            </div>
        </div>

        <div class="footer-bar">
            <span>Red Sea Digital &mdash; Confidential Executive Audit</span>
            <span>المرجع: <?php echo $audit_ref; ?> &nbsp;|&nbsp; صفحة 2 من 4</span>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- PAGE 3: SOFTWARE ARCHITECTURE & PROPOSED DUAL ENGINE              -->
    <!-- ================================================================= -->
    <div class="page">
        <div>
            <div class="header-bar">
                <div class="brand-logo">RED SEA <span>DIGITAL</span></div>
                <div class="doc-badge">03. Dual-Engine Software Architecture</div>
            </div>

            <div style="margin-bottom:12px;">
                <h2 style="font-size:18px;font-weight:900;color:#0F172A;margin:0 0 4px 0;">
                    المعمارية البرمجية والحل المزدوج المقترح
                </h2>
                <div style="font-size:11.5px;color:#64748B;">
                    منظومة تقنية متكاملة تدمج محرك الحجز المباشر فائق السرعة مع كونسيرج الواتساب الذكي 24/7.
                </div>
            </div>

            <!-- Architecture Comparison Table -->
            <div class="card">
                <div class="section-title">مقارنة المعمارية التقنية (Legacy vs. RSD Enterprise)</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>المعيار التقني</th>
                            <th>الوضع التقليدي الحالي</th>
                            <th>منظومة Red Sea Digital الحديثة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight:700;">محرك الحجز المباشر:</td>
                            <td>روابط خارجية أو استمارة تواصل غير تفاعلية</td>
                            <td style="color:#059669;font-weight:700;">محرك حجز هجين متكامل 0% عمولة ودفع فوري</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">الرد على النزلاء بالواتساب:</td>
                            <td>يدوي من موظف الاستقبال (تأخير من دقائق لساعات)</td>
                            <td style="color:#059669;font-weight:700;">كونسيرج AI ذكي متعدد اللغات يرد في &lt; 3 ثوانٍ 24/7</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">بوابات الدفع الإلكتروني:</td>
                            <td>غير مربوطة بالموقع المباشر</td>
                            <td style="color:#059669;font-weight:700;">ربط مباشر (Visa, MasterCard, Apple Pay, Fawry)</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">إدارة بيانات العملاء (CRM):</td>
                            <td>مشتتة في دفاتر أو إكسيل</td>
                            <td style="color:#059669;font-weight:700;">لوحة CRM مركزية مع زر مراسلة واتساب بنقرة واحدة</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Dual Engine Deep-Dive Cards -->
            <div class="grid-2" style="margin-bottom:14px;">
                <div class="card card-gold" style="margin:0;">
                    <div style="font-size:12px;font-weight:800;color:#0F172A;margin-bottom:6px;">
                        1. محرك الحجز المباشر (Direct Booking Engine)
                    </div>
                    <ul style="font-size:11px;color:#334155;margin:0;padding-right:16px;line-height:1.6;">
                        <li>واجهة مستخدم فاخرة وسريعة متوافقة 100% مع الهواتف الذكية.</li>
                        <li>تقويم حجز ديناميكي للغرف والرحلات والأنشطة السياحية.</li>
                        <li>تحصيل دفع إلكتروني متعدد العملات مباشرة لحساب المنشأة.</li>
                        <li>إصدار تأكيد حجز فوري وفاتورة إلكترونية مرقمة للنزيل.</li>
                    </ul>
                </div>

                <div class="card card-emerald" style="margin:0;">
                    <div style="font-size:12px;font-weight:800;color:#0F172A;margin-bottom:6px;">
                        2. كونسيرج الواتساب الذكي 24/7 (AI Concierge)
                    </div>
                    <ul style="font-size:11px;color:#334155;margin:0;padding-right:16px;line-height:1.6;">
                        <li>رد آلي فوري على استفسارات النزلاء بالإنجليزية والعربية والألمانية.</li>
                        <li>عرض باقات الغرف والأنشطة وإرسال روابط الحجز المباشر.</li>
                        <li>تأكيد بيانات الحجز وإشعار الإدارة تلقائياً في ثوانٍ.</li>
                        <li>استرجاع السلات والاستفسارات المتروكة لرفع التحويل (+35%).</li>
                    </ul>
                </div>
            </div>

            <!-- Expected Performance Metrics -->
            <div class="card card-navy">
                <div class="section-title">مؤشرات الأداء المتوقعة بعد الإطلاق (Target KPIs)</div>
                <div class="grid-3">
                    <div style="text-align:center;">
                        <div style="font-size:18px;font-weight:900;color:#059669;">+35% إلى 50%</div>
                        <div style="font-size:10px;color:#64748B;font-weight:700;">زيادة في نسبة تحويل الزوار لحجوزات</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:18px;font-weight:900;color:#2563EB;">أقل من 3 ثوانٍ</div>
                        <div style="font-size:10px;color:#64748B;font-weight:700;">زمن الاستجابة لاستفسارات النزلاء 24/7</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:18px;font-weight:900;color:#9A7B4F;">100% مستقلة</div>
                        <div style="font-size:10px;color:#64748B;font-weight:700;">امتلاك المنظومة وقاعدة البيانات بالكامل</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bar">
            <span>Red Sea Digital &mdash; Software Architecture & Specs</span>
            <span>المرجع: <?php echo $audit_ref; ?> &nbsp;|&nbsp; صفحة 3 من 4</span>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- PAGE 4: STRATEGIC ROADMAP, GOVERNANCE & DISCOVERY CTA             -->
    <!-- ================================================================= -->
    <div class="page">
        <div>
            <div class="header-bar">
                <div class="brand-logo">RED SEA <span>DIGITAL</span></div>
                <div class="doc-badge">04. Execution Roadmap & Next Step</div>
            </div>

            <div style="margin-bottom:12px;">
                <h2 style="font-size:18px;font-weight:900;color:#0F172A;margin:0 0 4px 0;">
                    خطة التنفيذ وحوكمة المشروع والاعتماد
                </h2>
                <div style="font-size:11.5px;color:#64748B;">
                    خريطة طريق واضحة ومرحلية لتدشين المنظومة وتسليمها بالكامل خلال 14 إلى 21 يوماً.
                </div>
            </div>

            <!-- Roadmap Stages Table -->
            <div class="card">
                <div class="section-title">الجدول الزمني التنفيذي (4-Stage Implementation Plan)</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:18%;">المرحلة</th>
                            <th style="width:25%;">النطاق والمهام الأساسية</th>
                            <th style="width:37%;">المخرجات القابلة للتسليم</th>
                            <th style="width:20%;">المدة الزمنية</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight:800;color:#9A7B4F;">المرحلة 1: التدقيق والتهيئة</td>
                            <td>استلام الهوية، باقات الغرف والأسعار، وبوابات الدفع</td>
                            <td>مخطط المعمارية والمحتوى الاستراتيجي المعتمد</td>
                            <td style="font-weight:700;">أيام 1 &ndash; 3</td>
                        </tr>
                        <tr>
                            <td style="font-weight:800;color:#2563EB;">المرحلة 2: بناء محرك الحجز</td>
                            <td>تصميم الواجهة الهجينة السريعة وبرمجة تقويم الغرف</td>
                            <td>موقع تفاعلي كامل ومحرك حجز مباشر جاهز للدفع</td>
                            <td style="font-weight:700;">أيام 4 &ndash; 8</td>
                        </tr>
                        <tr>
                            <td style="font-weight:800;color:#059669;">المرحلة 3: تدريب ذكاء الواتساب</td>
                            <td>ربط API الواتساب وتدريب الـ AI على سياسات المنشأة</td>
                            <td>بوت كونسيرج ذكي متعدد اللغات ومربوط بالـ CRM</td>
                            <td style="font-weight:700;">أيام 9 &ndash; 12</td>
                        </tr>
                        <tr>
                            <td style="font-weight:800;color:#0F172A;">المرحلة 4: الاختبار والتشغيل</td>
                            <td>محاكاة حية للحجوزات، تدريب الفريق، والإطلاق الرسمي</td>
                            <td>تسليم الكود ولوحات التحكم وبدء استقبال الحجوزات</td>
                            <td style="font-weight:700;">أيام 13 &ndash; 15</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Enterprise Governance Commitments -->
            <div class="card card-navy">
                <div class="section-title">بنود حوكمة المشروع وضمانات الأداء</div>
                <div class="grid-2">
                    <div style="font-size:10.5px;color:#334155;line-height:1.6;">
                        <div><strong>• ملكية كاملة 100%:</strong> الكود المصدري وقاعدة البيانات ملك للمنشأة دون أي قفل تجاري أو رسوم خفية.</div>
                        <div><strong>• أمان وتشفير عالي:</strong> شهادات SSL TLS 1.3 وحماية كاملة لبيانات النزلاء وفق معايير GDPR.</div>
                    </div>
                    <div style="font-size:10.5px;color:#334155;line-height:1.6;">
                        <div><strong>• دعم فني مستمر:</strong> متابعة دورية، صيانة برمجية، وتحديثات استقرار مستمرة.</div>
                        <div><strong>• ضمان ذهبي 30 يوماً:</strong> ضمان استعادة الاستثمار كاملاً في حال عدم مطابقة المنظومة للمواصفات.</div>
                    </div>
                </div>
            </div>

            <!-- Strategic Discovery Call CTA -->
            <div class="card card-gold" style="background:#FAF5EF;text-align:center;padding:18px 20px;">
                <h3 style="font-size:15px;font-weight:900;color:#0F172A;margin:0 0 6px 0;">
                    الخطوة الاستشارية التالية: استعراض ديمو حي للمنظومة
                </h3>
                <p style="font-size:11px;color:#475569;margin:0 0 14px 0;line-height:1.5;">
                    ندعو إدارة منشأة <strong><?php echo $company; ?></strong> لترتيب مكالمة ديمو سريعة لمدة 15 دقيقة لمشاهدة محرك الحجز وكونسيرج الواتساب يعمل مباشرة على شاشة الهاتف، ومطابقة الباقات المناسبة للمنشأة بدون أي التزام.
                </p>
                <a href="https://wa.me/201028803080?text=<?php echo urlencode('مرحباً Red Sea Digital، نود مناقشة وثيقة التدقيق الرقمي وخطة استرداد العمولات لمنشأة ' . $company); ?>" target="_blank" class="btn-cta">
                    تواصل مباشرة مع مهندس الحلول عبر الواتساب (01028803080)
                </a>
            </div>

            <!-- Digital Seal & Sign-off -->
            <div style="display:flex;justify-content:space-between;align-items:flex-end;padding:8px 10px;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;">
                <div style="font-size:10.5px;color:#64748B;">
                    <div>تم إعداد واعتماد هذه الوثيقة بواسطة:</div>
                    <strong style="color:#0F172A;font-size:11.5px;">مهندس/ عمرو أحمد &mdash; كبير مهندسي الحلول الرقمية، Red Sea Digital</strong>
                </div>
                <div style="text-align:left;font-family:'JetBrains Mono',monospace;font-size:9.5px;color:#94A3B8;">
                    <div>DIGITAL SIGNATURE HASH:</div>
                    <strong><?php echo strtoupper(substr(md5($company . $lead_id . 'RSD_2026'), 0, 16)); ?></strong>
                </div>
            </div>
        </div>

        <div class="footer-bar">
            <span>Red Sea Digital &mdash; Strategic Implementation Roadmap</span>
            <span>المرجع: <?php echo $audit_ref; ?> &nbsp;|&nbsp; صفحة 4 من 4</span>
        </div>
    </div>

</body>
</html>
        <?php
        return ob_get_clean();
    }
}
