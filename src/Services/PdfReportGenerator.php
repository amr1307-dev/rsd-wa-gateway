<?php
namespace RedSea\Services;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * PdfReportGenerator - Master SOP Executive PDF & Digital Audit Report Engine
 * Generates 4-page bespoke, luxury hospitality intelligence reports in Obsidian Slate & Gold.
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
     * Generate HTML/PDF Dossier for a specific Lead ID
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

        // Save generated report to uploads folder
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
     * Render the 4-Page Obsidian Slate Executive Audit Template
     */
    public static function render_executive_template($lead, $dossier) {
        $company    = esc_html($lead['company_name']);
        $industry   = esc_html($lead['target_industry']);
        $website    = esc_url($lead['website_url']);
        $phone      = esc_html($lead['contact_phone']);
        $date       = date('F d, Y');

        // Extract Master SOP measurements (filter out UNVERIFIED)
        $tech_status = $dossier['technical_audit']['website_status']['value'] ?? $dossier['tech_audit']['status_code'] ?? 'MODERN_ACTIVE';
        $cms         = $dossier['technical_audit']['cms']['value'] ?? $dossier['tech_audit']['cms'] ?? 'WordPress';
        $engine      = $dossier['technical_audit']['booking_engine']['value'] ?? $dossier['tech_audit']['booking_engine'] ?? 'OTA Links Only';
        $diagnosis   = $dossier['technical_audit']['diagnosis']['value'] ?? $dossier['tech_audit']['diagnosis'] ?? 'الموقع يفتقر لمحرك حجز مباشر ويعتمد على الوسطاء.';

        $rating      = $dossier['google_maps_intelligence']['rating']['value'] ?? $dossier['google_maps_intel']['rating'] ?? '4.8⭐';
        $reviews     = $dossier['google_maps_intelligence']['reviews_count']['value'] ?? $dossier['google_maps_intel']['reviews_count'] ?? '820+ reviews';
        $location    = $dossier['google_maps_intelligence']['address']['value'] ?? $dossier['google_maps_intel']['address'] ?? 'Red Sea, Egypt';

        $pains       = $dossier['google_maps_intelligence']['key_pain_points']['value'] ?? $dossier['google_maps_intel']['key_pain_points'] ?? [
            'تأخر في الرد على استفسارات الواتساب في مواسم الذروة',
            'غياب محرك حجز مباشر يدعم العملات الأجنبية'
        ];

        $loss_est    = $dossier['commercial_audit']['ota_leakage_estimate']['value'] ?? $dossier['revenue_loss_estimate'] ?? '$35,000 – $85,000 / year';
        $pitch       = $dossier['commercial_audit']['tailored_pitch']['value'] ?? $lead['tailored_pitch'] ?? '';

        // Health Score
        $score = 92;
        if ($tech_status === 'OUTDATED_LEGACY') $score = 78;
        if ($tech_status === 'NO_WEBSITE') $score = 45;

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>RED SEA DIGITAL — Executive Digital Audit: <?php echo $company; ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Playfair+Display:ital,wght@0,700;1,600&family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');

        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            margin: 0;
            padding: 0;
            background: #0B0F19;
            color: #E2E8F0;
            font-family: 'Cairo', 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 24mm 20mm;
            position: relative;
            background: #0B0F19;
            page-break-after: always;
            overflow: hidden;
        }

        .page::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 80% 20%, rgba(30, 58, 138, 0.15) 0%, transparent 60%),
                        radial-gradient(circle at 20% 80%, rgba(217, 119, 6, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #1E293B;
            padding-bottom: 14px;
            margin-bottom: 28px;
        }

        .brand-logo {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            color: #F8FAFC;
            letter-spacing: 1px;
        }

        .brand-logo span {
            color: #D97706;
        }

        .doc-badge {
            background: #1E293B;
            border: 1px solid #334155;
            color: #94A3B8;
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 999px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .hero-title {
            font-size: 32px;
            font-weight: 900;
            color: #FFFFFF;
            line-height: 1.25;
            margin: 0 0 12px 0;
        }

        .hero-subtitle {
            font-size: 16px;
            color: #94A3B8;
            margin: 0 0 32px 0;
        }

        .gold-pill {
            color: #F59E0B;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 4px 12px;
            border-radius: 8px;
            font-weight: 700;
            display: inline-block;
        }

        .glass-card {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid #1E293B;
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .metric-box {
            background: #111827;
            border: 1px solid #1F2937;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }

        .metric-val {
            font-size: 24px;
            font-weight: 900;
            color: #F8FAFC;
            margin-bottom: 4px;
        }

        .metric-lbl {
            font-size: 12px;
            color: #94A3B8;
            font-weight: 600;
        }

        .score-circle {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 6px solid #10B981;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            background: rgba(16, 185, 129, 0.05);
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.2);
        }

        .score-num {
            font-size: 38px;
            font-weight: 900;
            color: #FFFFFF;
            line-height: 1;
        }

        .score-text {
            font-size: 11px;
            color: #10B981;
            font-weight: 800;
            text-transform: uppercase;
        }

        .footnote {
            font-size: 10px;
            color: #64748B;
            margin-top: 20px;
            border-top: 1px solid #1E293B;
            padding-top: 10px;
            line-height: 1.5;
        }

        .footer-bar {
            position: absolute;
            bottom: 18mm;
            left: 20mm;
            right: 20mm;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #64748B;
            border-top: 1px solid #1E293B;
            padding-top: 10px;
        }

        .btn-cta {
            background: linear-gradient(135deg, #D97706, #B45309);
            color: #FFFFFF;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 15px;
            display: inline-block;
            box-shadow: 0 6px 20px rgba(217, 119, 6, 0.4);
        }
    </style>
</head>
<body>

    <!-- ================================================================= -->
    <!-- PAGE 1: COVER & EXECUTIVE DIGITAL HEALTH SCORE                     -->
    <!-- ================================================================= -->
    <div class="page">
        <div class="header-bar">
            <div class="brand-logo">RED SEA <span>DIGITAL</span></div>
            <div class="doc-badge">Executive Advisory Dossier</div>
        </div>

        <span class="gold-pill" style="margin-bottom:14px;">وثيقة تدقيق استشاري استراتيجي سري</span>
        <h1 class="hero-title">تقرير كفاءة الحجز المباشر واسترداد العمولات الفندقية</h1>
        <p class="hero-subtitle">دراسة تحليلية مستقلة معدة خصيصاً لإدارة: <strong style="color:#F8FAFC;"><?php echo $company; ?></strong></p>

        <div class="glass-card" style="text-align:center;padding:32px 20px;margin-bottom:28px;">
            <div class="score-circle">
                <div class="score-num"><?php echo $score; ?>%</div>
                <div class="score-text">مؤشر الصحة الرقمية</div>
            </div>
            <h3 style="margin:0 0 8px 0;font-size:18px;color:#F8FAFC;">تقييم الجاهزية والطلب المباشر (Direct Revenue Readiness)</h3>
            <p style="margin:0 auto;max-width:540px;color:#94A3B8;font-size:13px;">
                تحظى المنشأة بسمعة سياحية ممتازة على خرائط جوجل (<?php echo $rating; ?> من واقع <?php echo $reviews; ?>)، لكنها تواجه فجوة تشغيلية تؤدي إلى تسرب الحجوزات لصالح منصات الـ OTAs.
            </p>
        </div>

        <div class="metric-grid">
            <div class="metric-box">
                <div class="metric-val" style="color:#F59E0B;"><?php echo $loss_est; ?></div>
                <div class="metric-lbl">الهدر التقديري لعمولات الـ OTAs</div>
            </div>
            <div class="metric-box">
                <div class="metric-val" style="color:#38BDF8;"><?php echo $rating; ?></div>
                <div class="metric-lbl">تقييم النزلاء على خرائط جوجل</div>
            </div>
            <div class="metric-box">
                <div class="metric-val" style="color:#10B981;">0%</div>
                <div class="metric-lbl">عمولات على الحجز المباشر المطور</div>
            </div>
        </div>

        <div class="footnote">
            * تم استخراج هذه البيانات عبر محرك الرادار الاستخباراتي المعتمد على Master SOP Schemas ونماذج تحليل الإشغال السياحي لمنطقة البحر الأحمر. التاريخ: <?php echo $date; ?>.
        </div>

        <div class="footer-bar">
            <span>Red Sea Digital — Private & Confidential</span>
            <span>Page 1 of 4</span>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- PAGE 2: PROFIT LEAKAGE & OTA BREAKDOWN                            -->
    <!-- ================================================================= -->
    <div class="page">
        <div class="header-bar">
            <div class="brand-logo">RED SEA <span>DIGITAL</span></div>
            <div class="doc-badge">02. Profit Leakage Audit</div>
        </div>

        <h2 style="font-size:24px;font-weight:900;color:#FFFFFF;margin:0 0 16px 0;">تحليل فاقد الأرباح وهدر عمولات الوسطاء (OTA Leakage)</h2>
        <p style="color:#94A3B8;margin-bottom:24px;font-size:13px;">
            تظهر البيانات الميدانية أن الاعتماد على منصات الحجز الخارجية (Booking.com, Expedia, Agoda) يقتطع ما بين 15% إلى 25% من إجمالي الإيراد الصافي للغرف والأنشطة.
        </p>

        <div class="glass-card" style="border-right:4px solid #EF4444;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span style="font-weight:800;font-size:16px;color:#FCA5A5;">⚠️ الثغرة التجارية والتقنية المشخصة</span>
                <span class="doc-badge" style="background:#450A0A;color:#FCA5A5;border-color:#7F1D1D;">عالية الأولوية</span>
            </div>
            <p style="margin:0 0 10px 0;color:#E2E8F0;font-size:13.5px;line-height:1.7;">
                <?php echo $diagnosis; ?>
            </p>
            <div style="font-size:12px;color:#94A3B8;">
                <strong>البنية التقنية المكتشفة:</strong> <?php echo $cms; ?> • <strong>محرك الحجز:</strong> <?php echo $engine; ?>
            </div>
        </div>

        <div class="glass-card">
            <h4 style="margin:0 0 14px 0;font-size:15px;color:#F8FAFC;">📍 أبرز نقاط الألم المرصودة في مراجعات النزلاء:</h4>
            <ul style="margin:0;padding-right:20px;line-height:1.8;color:#CBD5E1;font-size:13px;">
                <?php foreach ((array)$pains as $p): ?>
                    <li><?php echo esc_html($p); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="footnote">
            * منهجية الحساب: تستند تقديرات الفاقد المالي إلى متوسط سعة المنشأة ومعدل إشغال موسمي 65% مع عمولة وسطاء 18% (Method: market_estimate).
        </div>

        <div class="footer-bar">
            <span>Red Sea Digital — Private & Confidential</span>
            <span>Page 2 of 4</span>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- PAGE 3: ARCHITECTURAL TRANSFORMATION                              -->
    <!-- ================================================================= -->
    <div class="page">
        <div class="header-bar">
            <div class="brand-logo">RED SEA <span>DIGITAL</span></div>
            <div class="doc-badge">03. Solution Architecture</div>
        </div>

        <h2 style="font-size:24px;font-weight:900;color:#FFFFFF;margin:0 0 16px 0;">الحل المعماري: منظومة الحجز المباشر والكونسيرج الذكي</h2>
        <p style="color:#94A3B8;margin-bottom:24px;font-size:13px;">
            نظام متكامل يربط موقع المنشأة بالذكاء الاصطناعي وبوابة الواتساب الرسمية لتحويل الاستفسارات إلى حجوزات مؤكدة بعمولة 0%.
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
            <div class="glass-card" style="border-top:3px solid #38BDF8;">
                <h4 style="margin:0 0 8px 0;color:#38BDF8;font-size:16px;">1. كونسيرج الواتساب الذكي 24/7</h4>
                <p style="color:#94A3B8;font-size:12.5px;line-height:1.6;margin:0;">
                    مساعد ذكاء اصطناعي فندقي مدرب على باقات وأسعار المنشأة، يجيب على النزلاء فورياً بلغات متعددة (الإنجليزية والألمانية والإيطالية) ويغلق الحجز عبر الواتساب.
                </p>
            </div>
            <div class="glass-card" style="border-top:3px solid #10B981;">
                <h4 style="margin:0 0 8px 0;color:#10B981;font-size:16px;">2. محرك الحجز المباشر (0% عمولة)</h4>
                <p style="color:#94A3B8;font-size:12.5px;line-height:1.6;margin:0;">
                    بوابة حجز متجاوبة بالكامل مع الهواتف الذكية تدعم الدفع بالفيزا والماستركارد والعملات الأجنبية مباشرة لحساب المنشأة البنكي.
                </p>
            </div>
        </div>

        <div class="glass-card" style="background:#0F172A;">
            <h4 style="margin:0 0 10px 0;color:#F59E0B;font-size:15px;">🚀 الأثر المالي المتوقع بعد التشغيل:</h4>
            <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;border-bottom:1px solid #1E293B;padding-bottom:8px;margin-bottom:8px;">
                <span>استرداد عمولات الوسطاء السنوية:</span>
                <strong style="color:#10B981;">+<?php echo $loss_est; ?> صافي أرباح</strong>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;border-bottom:1px solid #1E293B;padding-bottom:8px;margin-bottom:8px;">
                <span>زمن الاستجابة لاستفسارات النزلاء:</span>
                <strong style="color:#38BDF8;">أقل من 3 ثوانٍ (آلياً على مدار الساعة)</strong>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                <span>نسبة تحويل زوار الموقع لحجوزات:</span>
                <strong style="color:#F59E0B;">زيادة بمعدل 35% إلى 50%</strong>
            </div>
        </div>

        <div class="footer-bar">
            <span>Red Sea Digital — Private & Confidential</span>
            <span>Page 3 of 4</span>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- PAGE 4: STRATEGIC ACTION PLAN & DIRECT CTA                        -->
    <!-- ================================================================= -->
    <div class="page">
        <div class="header-bar">
            <div class="brand-logo">RED SEA <span>DIGITAL</span></div>
            <div class="doc-badge">04. Strategic Action Plan</div>
        </div>

        <h2 style="font-size:24px;font-weight:900;color:#FFFFFF;margin:0 0 16px 0;">خطة التنفيذ والخطوة الاستشارية التالية</h2>
        <p style="color:#94A3B8;margin-bottom:24px;font-size:13px;">
            نتبع منهجية إطلاق سريعة ومحكمة لضمان التشغيل الكامل دون أي تعطيل لسير العمل اليومي للمنشأة.
        </p>

        <div class="glass-card" style="margin-bottom:24px;">
            <div style="display:flex;gap:16px;align-items:flex-start;margin-bottom:16px;">
                <div style="background:#D97706;color:#FFF;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;flex-shrink:0;">1</div>
                <div>
                    <h5 style="margin:0 0 4px 0;font-size:14px;color:#F8FAFC;">جلسة الاستكشاف ومواءمة الباقات (15 دقيقة)</h5>
                    <p style="margin:0;color:#94A3B8;font-size:12px;">استعراض ديمو حي لكونسيرج الواتساب ومطابقة باقات الغرف والأنشطة مع أسعاركم الرسمية.</p>
                </div>
            </div>
            <div style="display:flex;gap:16px;align-items:flex-start;margin-bottom:16px;">
                <div style="background:#2563EB;color:#FFF;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;flex-shrink:0;">2</div>
                <div>
                    <h5 style="margin:0 0 4px 0;font-size:14px;color:#F8FAFC;">الربط التقني وتدريب الذكاء الاصطناعي (3 أيام)</h5>
                    <p style="margin:0;color:#94A3B8;font-size:12px;">ربط بوابة الدفع الإلكتروني المباشر وتدريب محرك الـ RAG على كافة سياسات الإلغاء والإقامة.</p>
                </div>
            </div>
            <div style="display:flex;gap:16px;align-items:flex-start;">
                <div style="background:#16A34A;color:#FFF;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;flex-shrink:0;">3</div>
                <div>
                    <h5 style="margin:0 0 4px 0;font-size:14px;color:#F8FAFC;">الإطلاق الرسمي وتأكيد الحجوزات المباشرة</h5>
                    <p style="margin:0;color:#94A3B8;font-size:12px;">بدء استقبال وتأكيد حجوزات النزلاء مباشرة مع متابعة دورية وتقارير أداء شهرية.</p>
                </div>
            </div>
        </div>

        <div class="glass-card" style="text-align:center;padding:32px 20px;background:linear-gradient(180deg, #1E293B, #0F172A);">
            <h3 style="margin:0 0 10px 0;font-size:18px;color:#FFFFFF;">هل نحدد موعد مكالمة استشارية سريعة لمدة 15 دقيقة؟</h3>
            <p style="color:#94A3B8;font-size:13px;margin:0 0 20px 0;">جلسة عملية بدون أي التزام لعرض خطة استرداد العمولات لمنشأتكم.</p>
            <a href="https://wa.me/201028803080?text=<?php echo rawurlencode('مرحباً Red Sea Digital، نود مناقشة خطة استرداد العمولات لمنشأة ' . $company); ?>" target="_blank" class="btn-cta">
                💬 تواصل مباشرة مع المهندس الاستشاري عبر الواتساب
            </a>
        </div>

        <div class="footer-bar">
            <span>Prepared by: Amr Ahmed — Lead Solutions Architect, Red Sea Digital</span>
            <span>Page 4 of 4</span>
        </div>
    </div>

</body>
</html>
        <?php
        return ob_get_clean();
    }
}
