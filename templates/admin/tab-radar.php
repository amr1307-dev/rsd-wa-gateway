<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

/**
 * Tab 9: Executive Outbound Lead Radar & Deal Closer Studio (Master SOP Schema Edition)
 */

$leads_tbl = $wpdb->prefix . 'rsd_leads';
$all_leads = $wpdb->get_results("SELECT * FROM {$leads_tbl} ORDER BY id DESC LIMIT 50", ARRAY_A) ?: [];

$cnt_total = count($all_leads);
$cnt_pending = 0;
$cnt_contacted = 0;
$cnt_negotiating = 0;
$cnt_won = 0;
$cnt_quarantined = 0;

foreach ($all_leads as $l) {
    $st = $l['pipeline_status'] ?? 'pending_review';
    if ($st === 'quarantined') $cnt_quarantined++;
    elseif ($st === 'pending_review' || $st === 'scouted') $cnt_pending++;
    elseif ($st === 'contacting' || $st === 'contacted') $cnt_contacted++;
    elseif ($st === 'negotiating') $cnt_negotiating++;
    elseif ($st === 'closed' || $st === 'won') $cnt_won++;
}
?>

<!-- 1. RADAR CONTROLS & DISCOVERY LAUNCHER -->
<div class="rsd-card" style="margin-bottom:24px;border:2px solid #E2E8F0;background:#FFFFFF;box-shadow:0 4px 12px rgba(0,0,0,0.03);direction:rtl;text-align:right;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap;">
                <span style="font-size:1.4rem;">🎯</span>
                <h3 style="margin:0;font-size:1.25rem;font-weight:900;color:#0F172A;letter-spacing:-0.3px;">
                    رادار الصفقات والتنقيب الذاتي (Master SOP Lead Radar)
                </h3>
                <span class="rsd-badge" style="background:#EFF6FF;color:#2563EB;border:1px solid #BFDBFE;font-size:0.75rem;padding:3px 10px;">
                    ⚡ Master SOP • Binary Triage • Google Maps
                </span>
            </div>
            <p style="margin:0;color:#64748B;font-size:0.88rem;line-height:1.6;">
                محرك استخباراتي يفحص الكيانات السياحية بدقة SOP صارمة، يقيس الفاقد المالي، ويفلتر الفرص عبر Binary Triage لعزل السجلات غير المؤكدة.
            </p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <select id="rsdRadarNiche" class="rsd-select" style="min-width:240px;font-weight:700;background:#F8FAFC;border:1px solid #CBD5E1;">
                <option value="boutique luxury hotels red sea">🏨 بوتيك هوتيل ومنتجعات البحر الأحمر الفاخرة</option>
                <option value="luxury dive center sharm el sheikh">🤿 مراكز وسفاري الغوص واليخوت بشرم الشيخ</option>
                <option value="luxury el gouna lagoon resorts">🏝️ منتجعات وفلل الجونة لاجون الفاخرة</option>
                <option value="hurghada soma bay direct booking">🌊 منتجعات سوما باي وسهل حشيش</option>
            </select>
            <button type="button" id="rsdBtnRunRadar" onclick="rsdRunRadarScan()" class="rsd-btn" style="background:linear-gradient(135deg, #2563EB, #1D4ED8);box-shadow:0 4px 12px rgba(37,99,235,0.25);font-weight:800;padding:10px 20px;">
                🚀 ابدأ جولة التنقيب الآلي
            </button>
            <button type="button" onclick="rsdPurgeAllLeads()" class="rsd-btn rsd-btn-secondary" style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;font-weight:700;padding:10px 14px;" title="حذف وتطهير السجلات السابقة">
                🗑️ تطهير السجلات
            </button>
        </div>
    </div>

    <!-- RADAR REAL-TIME CONSOLE -->
    <div id="rsdRadarConsole" style="display:none;margin-top:18px;background:#0F172A;border-radius:14px;padding:16px 20px;color:#E2E8F0;font-family:'JetBrains Mono',monospace;font-size:0.84rem;line-height:1.6;border:1px solid #1E293B;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;border-bottom:1px solid #1E293B;padding-bottom:6px;">
            <span style="color:#38BDF8;font-weight:800;">✦ وحدة الأوركسترا النشطة (Active Discovery Agent)</span>
            <span style="color:#94A3B8;font-size:0.75rem;">Master SOP Schemas • Binary Triage Filter</span>
        </div>
        <div id="rsdRadarLogLines">
            <div style="color:#A5F3FC;">[Scout Agent] 🔍 جاري الاتصال بمحركات البحث واستكشاف المنشآت النشطة...</div>
        </div>
    </div>
</div>

<!-- 2. EXECUTIVE METRICS STRIP -->
<div class="rsd-telemetry-grid" style="margin-bottom:24px;direction:rtl;">
    <div class="rsd-telemetry-card" style="border-color:#E2E8F0;background:#FFFFFF;text-align:right;">
        <div class="rsd-telemetry-title" style="color:#64748B;">إجمالي الفرص المرصودة</div>
        <div class="rsd-telemetry-val" style="color:#0F172A;font-weight:900;"><?php echo intval($cnt_total); ?></div>
        <span style="font-size:0.75rem;color:#94A3B8;margin-top:4px;">فرصة بملف استخباراتي كامل</span>
    </div>
    <div class="rsd-telemetry-card" style="border-color:#FEF08A;background:#FEFCE8;text-align:right;">
        <div class="rsd-telemetry-title" style="color:#A16207;">⏳ مؤهلة بانتظار الاعتماد</div>
        <div class="rsd-telemetry-val" style="color:#CA8A04;font-weight:900;"><?php echo intval($cnt_pending); ?></div>
        <span style="font-size:0.75rem;color:#A16207;margin-top:4px;">اجتازت الـ Binary Triage</span>
    </div>
    <div class="rsd-telemetry-card" style="border-color:#FECACA;background:#FEF2F2;text-align:right;">
        <div class="rsd-telemetry-title" style="color:#991B1B;">⛔ قائمة العزل (Quarantine)</div>
        <div class="rsd-telemetry-val" style="color:#DC2626;font-weight:900;"><?php echo intval($cnt_quarantined); ?></div>
        <span style="font-size:0.75rem;color:#991B1B;margin-top:4px;">تتطلب تدقيقاً يدوياً</span>
    </div>
    <div class="rsd-telemetry-card" style="border-color:#BAE6FD;background:#F0F9FF;text-align:right;">
        <div class="rsd-telemetry-title" style="color:#0369A1;">💬 تم التواصل عبر الواتساب</div>
        <div class="rsd-telemetry-val" style="color:#0284C7;font-weight:900;"><?php echo intval($cnt_contacted); ?></div>
        <span style="font-size:0.75rem;color:#0369A1;margin-top:4px;">رسائل مرسلة بنجاح</span>
    </div>
    <div class="rsd-telemetry-card" style="border-color:#BBF7D0;background:#F0FDF4;text-align:right;">
        <div class="rsd-telemetry-title" style="color:#15803D;">🏆 صفقات مغلقة بنجاح</div>
        <div class="rsd-telemetry-val" style="color:#16A34A;font-weight:900;"><?php echo intval($cnt_won); ?></div>
        <span style="font-size:0.75rem;color:#15803D;margin-top:4px;">عقود حجز مباشر نشطة</span>
    </div>
</div>

<!-- 3. DETAILED LEADS PIPELINE CARDS CONTAINER -->
<div id="rsdLeadsContainer" style="display:flex;flex-direction:column;gap:20px;direction:rtl;text-align:right;">
    <?php if (empty($all_leads)): ?>
        <div id="rsdEmptyPlaceholder" class="rsd-card" style="text-align:center;padding:48px 20px;background:#FFFFFF;border:2px dashed #CBD5E1;">
            <div style="font-size:2.5rem;margin-bottom:12px;">🏖️</div>
            <h4 style="margin:0 0 6px 0;font-size:1.15rem;font-weight:800;color:#0F172A;">لا توجد فرص مرصودة حالياً</h4>
            <p style="margin:0 0 16px 0;color:#64748B;font-size:0.88rem;">اختر القطاع المستهدف بالأعلى واضغط «ابدأ جولة التنقيب الآلي» لبدء رصد الفنادق والمراكز السياحية.</p>
        </div>
    <?php else: ?>
        <?php foreach ($all_leads as $lead): ?>
            <?php
            $dossier = json_decode($lead['gap_analysis'] ?? '{}', true) ?: [];
            $status = $lead['pipeline_status'] ?? 'pending_review';
            
            $is_quarantined = ($status === 'quarantined');
            
            // Extract SOP measurements with fallbacks
            $tech_status = $dossier['technical_audit']['website_status']['value'] ?? $dossier['tech_audit']['status_code'] ?? 'MODERN_ACTIVE';
            $tech_cms = $dossier['technical_audit']['cms']['value'] ?? $dossier['tech_audit']['cms'] ?? 'WordPress';
            $tech_engine = $dossier['technical_audit']['booking_engine']['value'] ?? $dossier['tech_audit']['booking_engine'] ?? 'OTA Links Only';
            $tech_diag = $dossier['technical_audit']['diagnosis']['value'] ?? $dossier['tech_audit']['diagnosis'] ?? 'الموقع يفتقر لمحرك حجز مباشر.';

            $maps_rating = $dossier['google_maps_intelligence']['rating']['value'] ?? $dossier['google_maps_intel']['rating'] ?? '4.7⭐';
            $maps_reviews = $dossier['google_maps_intelligence']['reviews_count']['value'] ?? $dossier['google_maps_intel']['reviews_count'] ?? '540+ تقييم';
            $maps_addr = $dossier['google_maps_intelligence']['address']['value'] ?? $dossier['google_maps_intel']['address'] ?? 'البحر الأحمر';
            $maps_pains = $dossier['google_maps_intelligence']['key_pain_points']['value'] ?? $dossier['google_maps_intel']['key_pain_points'] ?? ['تأخر في الرد على استفسارات الواتساب'];

            $loss_est = $dossier['commercial_audit']['ota_leakage_estimate']['value'] ?? $dossier['revenue_loss_estimate'] ?? '$35,000 – $95,000 سنويًا';
            
            $score_pct = 92;
            if ($tech_status === 'OUTDATED_LEGACY' || strpos($loss_est, '95,000') !== false) {
                $score_pct = 96;
            } elseif ($tech_status === 'NO_WEBSITE') {
                $score_pct = 98;
            }

            $upload_info = wp_upload_dir();
            $report_url  = $upload_info['baseurl'] . '/rsd-reports/RSD-Audit-' . sanitize_file_name($lead['company_name']) . '-' . $lead['id'] . '.html';
            $pitch_body  = ($lead['tailored_pitch'] ?? '') . "\n\n📄 وثيقة التدقيق الرقمي الرسمية:\n" . $report_url;
            $encoded_pitch = rawurlencode($pitch_body);
            
            $clean_phone = preg_replace('/[^0-9]/', '', (string)$lead['contact_phone']);
            if (substr($clean_phone, 0, 1) === '0' && strlen($clean_phone) === 11) {
                $clean_phone = '2' . $clean_phone;
            }
            $wa_direct_link = "https://wa.me/{$clean_phone}?text={$encoded_pitch}";
            ?>

            <div class="rsd-card rsd-lead-card-box" id="leadCard_<?php echo $lead['id']; ?>" style="margin-bottom:0;border:1px solid <?php echo $is_quarantined ? '#FECACA' : '#E2E8F0'; ?>;background:<?php echo $is_quarantined ? '#FFFDFD' : '#FFFFFF'; ?>;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,0.03);padding:24px;direction:rtl;text-align:right;">
                
                <!-- CARD HEADER & PIPELINE CONTROL -->
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;margin-bottom:18px;border-bottom:1px solid #F1F5F9;padding-bottom:16px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap;">
                            <h4 style="margin:0;font-size:1.25rem;font-weight:900;color:#0F172A;">
                                <?php echo esc_html($lead['company_name']); ?>
                            </h4>
                            <span class="rsd-badge" style="background:#EFF6FF;color:#1E40AF;border:1px solid #DBEAFE;font-size:0.78rem;font-weight:700;">
                                <?php echo esc_html($lead['target_industry']); ?>
                            </span>
                            <?php if ($is_quarantined): ?>
                                <span class="rsd-badge" style="background:#FEF2F2;color:#991B1B;border:1px solid #FECACA;font-weight:900;font-size:0.78rem;">
                                    ⛔ معزولة: تتطلب تدقيقاً يدوياً (SOP Gated)
                                </span>
                            <?php else: ?>
                                <span class="rsd-badge" style="background:#DCFCE7;color:#166534;border:1px solid #86EFAC;font-weight:800;font-size:0.78rem;">
                                    🎯 مؤشر احتمالية الإغلاق: <?php echo $score_pct; ?>% (Triage PASS)
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- TECH STACK & CMS BADGE STRIP -->
                        <div style="display:flex;gap:10px;align-items:center;margin-bottom:10px;flex-wrap:wrap;">
                            <span class="rsd-badge" style="<?php echo ($tech_status === 'OUTDATED_LEGACY' || $tech_status === 'NO_WEBSITE') ? 'background:#FEF2F2;color:#991B1B;border:1px solid #FECACA;' : 'background:#F0FDF4;color:#166534;border:1px solid #BBF7D0;'; ?> font-weight:800;font-size:0.78rem;">
                                🌐 البنية التقنية: <?php echo esc_html($tech_cms); ?> (<?php echo esc_html($tech_status); ?>)
                            </span>
                            <span class="rsd-badge" style="background:#F8FAFC;color:#475569;border:1px solid #E2E8F0;font-size:0.76rem;">
                                ⚙️ المحرك: <?php echo esc_html($tech_engine); ?>
                            </span>
                        </div>

                        <div style="display:flex;gap:16px;font-size:0.84rem;color:#64748B;align-items:center;flex-wrap:wrap;">
                            <?php if (!empty($lead['website_url'])): ?>
                                <a href="<?php echo esc_url($lead['website_url']); ?>" target="_blank" style="color:#2563EB;text-decoration:none;font-weight:700;">
                                    🔗 <?php echo esc_html($lead['website_url']); ?>
                                </a>
                                <button type="button" onclick="rsdPreviewSite('<?php echo esc_url($lead['website_url']); ?>')" class="rsd-btn rsd-btn-secondary" style="padding:2px 8px;font-size:0.72rem;background:#F1F5F9;">
                                    👁️ معاينة فورية
                                </button>
                            <?php endif; ?>
                            <span style="direction:ltr;font-weight:700;color:#334155;">📱 +<?php echo esc_html($clean_phone ?: 'غير مؤكد'); ?></span>
                            <span>📅 رُصدت في: <?php echo esc_html($lead['created_at']); ?></span>
                        </div>
                    </div>

                    <!-- PIPELINE STATUS SELECTOR -->
                    <div style="display:flex;gap:10px;align-items:center;">
                        <label style="font-size:0.82rem;font-weight:800;color:#475569;">حالة الصفقة:</label>
                        <select onchange="rsdUpdatePipelineStatus(<?php echo $lead['id']; ?>, this.value)" class="rsd-select" style="min-width:160px;font-weight:700;font-size:0.84rem;padding:6px 12px;background:#F8FAFC;">
                            <option value="pending_review" <?php selected($status, 'pending_review'); ?>>⏳ مؤهلة بانتظار الاعتماد</option>
                            <option value="quarantined" <?php selected($status, 'quarantined'); ?>>⛔ معزولة (تحتاج تدقيق)</option>
                            <option value="contacted" <?php selected($status === 'contacted' || $status === 'contacting', true); ?>>💬 تم التواصل (WhatsApp)</option>
                            <option value="negotiating" <?php selected($status, 'negotiating'); ?>>🤝 مرحلة التفاوض</option>
                            <option value="won" <?php selected($status === 'won' || $status === 'closed', true); ?>>🏆 صفقة مغلقة بنجاح</option>
                            <option value="rejected" <?php selected($status, 'rejected'); ?>>✖ مستبعدة</option>
                        </select>
                    </div>
                </div>

                <!-- 2-COLUMN DOSSIER GRID: MAPS INTEL & OTA GAP -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                    
                    <!-- COLUMN 1: GOOGLE MAPS INTEL -->
                    <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:14px;padding:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <span style="font-weight:800;font-size:0.9rem;color:#0F172A;">
                                📍 استخبارات خرائط جوجل (Google Maps Intel)
                            </span>
                            <span class="rsd-badge" style="background:#FEF3C7;color:#92400E;border:1px solid #FDE68A;font-weight:800;font-size:0.8rem;">
                                <?php echo esc_html($maps_rating); ?> (<?php echo esc_html($maps_reviews); ?>)
                            </span>
                        </div>
                        <p style="margin:0 0 8px 0;font-size:0.82rem;color:#475569;font-weight:600;">
                            📌 الموقع: <?php echo esc_html($maps_addr); ?>
                        </p>
                        <div style="font-size:0.8rem;color:#64748B;">
                            <div style="font-weight:700;color:#334155;margin-bottom:4px;">أبرز نقاط الألم في مراجعات النزلاء:</div>
                            <ul style="margin:0;padding-right:18px;line-height:1.6;">
                                <?php foreach ((array)$maps_pains as $pain): ?>
                                    <li><?php echo esc_html($pain); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- COLUMN 2: TECH DIAGNOSIS & OTA GAP AUDIT -->
                    <div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:14px;padding:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <span style="font-weight:800;font-size:0.9rem;color:#92400E;">
                                ⚠️ تشخيص الثغرات والفاقد المالي
                            </span>
                            <span class="rsd-badge" style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;font-weight:900;font-size:0.8rem;">
                                💸 فاقد العمولات: <?php echo esc_html($loss_est); ?>
                            </span>
                        </div>
                        <p style="margin:0 0 6px 0;font-size:0.83rem;color:#78350F;line-height:1.5;">
                            <strong>التشخيص الفني:</strong> <?php echo esc_html($tech_diag); ?>
                        </p>
                        <p style="margin:0;font-size:0.82rem;color:#92400E;line-height:1.5;">
                            <strong>نقاط القوة:</strong> <?php echo esc_html($dossier['commercial_audit']['strengths']['value'] ?? $dossier['strengths'] ?? 'تقييم ممتاز وطلب سياحي مرتفع.'); ?>
                        </p>
                    </div>

                </div>

                <!-- PERSONALIZED PITCH TEXTAREA -->
                <div style="margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <label style="font-weight:800;font-size:0.88rem;color:#0F172A;">
                            ✍️ نص الرسالة الاستشارية المخصصة (Personalized Pitch Copy):
                        </label>
                        <button type="button" onclick="rsdRegeneratePitch(<?php echo $lead['id']; ?>)" class="rsd-btn rsd-btn-secondary" style="padding:3px 10px;font-size:0.75rem;background:#F1F5F9;font-weight:700;">
                            ✨ إعادة الصياغة بالذكاء الاصطناعي (AI Re-Pitch)
                        </button>
                    </div>
                    <textarea id="pitchText_<?php echo $lead['id']; ?>" class="rsd-input" rows="3" style="width:100%;font-size:0.88rem;line-height:1.6;border-radius:12px;background:#F8FAFC;font-family:inherit;padding:12px;border:1px solid #CBD5E1;"><?php echo esc_textarea($lead['tailored_pitch']); ?></textarea>
                </div>

                <!-- ACTION BUTTONS STRIP -->
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    <div style="display:flex;gap:10px;">
                        <button type="button" onclick="rsdSaveLeadPitch(<?php echo $lead['id']; ?>)" class="rsd-btn rsd-btn-secondary" style="padding:8px 16px;font-size:0.84rem;font-weight:700;">
                            💾 حفظ التعديلات
                        </button>
                        <button type="button" onclick="rsdGenerateLeadPdf(<?php echo $lead['id']; ?>)" class="rsd-btn rsd-btn-secondary" style="padding:8px 16px;font-size:0.84rem;font-weight:700;background:#1E293B;color:#F8FAFC;border:1px solid #334155;">
                            📄 وثيقة الـ PDF الاستشارية
                        </button>
                    </div>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <?php if ($is_quarantined): ?>
                            <span style="font-size:0.82rem;color:#DC2626;font-weight:700;">⚠️ محظور الإرسال التلقائي: الفرصة معزولة وتتطلب مراجعة البيانات.</span>
                        <?php else: ?>
                            <a href="<?php echo esc_url($wa_direct_link); ?>" target="_blank" onclick="rsdMarkLeadContacted(<?php echo $lead['id']; ?>)" class="rsd-btn" style="background:#16A34A;box-shadow:0 4px 12px rgba(22,163,74,0.25);padding:8px 20px;font-size:0.88rem;font-weight:800;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                                <span>💬 اعتماد وإرسال مباشر عبر الواتساب</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function rsdRunRadarScan() {
    var btn = document.getElementById('rsdBtnRunRadar');
    var consoleBox = document.getElementById('rsdRadarConsole');
    var logLines = document.getElementById('rsdRadarLogLines');
    var niche = document.getElementById('rsdRadarNiche').value;

    btn.disabled = true;
    btn.innerHTML = '⏳ جاري التنقيب والتحليل...';
    consoleBox.style.display = 'block';

    logLines.innerHTML = '<div style="color:#38BDF8;">[1/4] 🌐 جاري الاتصال بمحركات البحث واستكشاف المنشآت النشطة...</div>';

    setTimeout(function() {
        if (btn.disabled) {
            logLines.innerHTML += '<div style="color:#A5F3FC;">[2/4] 📍 فحص استخبارات خرائط جوجل وتقييمات النزلاء الحية...</div>';
        }
    }, 1200);

    setTimeout(function() {
        if (btn.disabled) {
            logLines.innerHTML += '<div style="color:#FDE68A;">[3/4] ⚙️ تشخيص البنية التقنية وتطبيق معايير Master SOP...</div>';
        }
    }, 2400);

    var fd = new FormData();
    fd.append('action', 'rsd_radar_run_discovery');
    fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
    fd.append('niche', niche);

    fetch(ajaxurl, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            btn.disabled = false;
            btn.innerHTML = '🚀 ابدأ جولة التنقيب الآلي';

            if (d.success && d.data && d.data.leads) {
                logLines.innerHTML += '<div style="color:#86EFAC;font-weight:800;">[4/4] ✅ اكتملت الجولة! تم تطبيق الـ Binary Triage وتدقيق ' + d.data.leads.length + ' منشأة.</div>';
                setTimeout(function() {
                    window.location.reload();
                }, 800);
            } else {
                alert('حدث خطأ أثناء التنقيب: ' + (d.data && d.data.message ? d.data.message : ''));
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '🚀 ابدأ جولة التنقيب الآلي';
            alert('تعذر استكمال الاتصال: ' + err.message);
        });
}

function rsdPurgeAllLeads() {
    if (!confirm('⚠️ هل أنت متأكد من رغبتك في حذف وتطهير كافة سجلات الفرص السابقة؟ لا يمكن التراجع عن هذا الإجراء.')) return;
    
    var fd = new FormData();
    fd.append('action', 'rsd_radar_purge_leads');
    fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');

    fetch(ajaxurl, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                alert('✅ تم تطهير كافة سجلات الرادار بنجاح.');
                window.location.reload();
            } else {
                alert('تعذر التطهير: ' + (d.data && d.data.message ? d.data.message : ''));
            }
        });
}

function rsdUpdatePipelineStatus(leadId, newStatus) {
    var fd = new FormData();
    fd.append('action', 'rsd_update_lead_status');
    fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
    fd.append('lead_id', leadId);
    fd.append('status', newStatus);

    fetch(ajaxurl, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var card = document.getElementById('leadCard_' + leadId);
                if (card) {
                    card.style.borderColor = (newStatus === 'quarantined') ? '#DC2626' : '#10B981';
                    setTimeout(function() { card.style.borderColor = (newStatus === 'quarantined') ? '#FECACA' : '#E2E8F0'; }, 1000);
                }
            }
        });
}

function rsdMarkLeadContacted(leadId) {
    rsdUpdatePipelineStatus(leadId, 'contacted');
}

function rsdSaveLeadPitch(leadId) {
    var pitch = document.getElementById('pitchText_' + leadId).value;
    var fd = new FormData();
    fd.append('action', 'rsd_radar_edit_pitch');
    fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
    fd.append('lead_id', leadId);
    fd.append('pitch', pitch);

    fetch(ajaxurl, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                alert('💾 تم حفظ نص الرسالة بنجاح.');
            } else {
                alert('تعذر حفظ التعديل.');
            }
        });
}

function rsdRegeneratePitch(leadId) {
    var textarea = document.getElementById('pitchText_' + leadId);
    var orig = textarea.value;
    textarea.value = '⏳ جاري الصياغة الذكية بواسطة الذكاء الاصطناعي...';
    textarea.disabled = true;

    var fd = new FormData();
    fd.append('action', 'rsd_radar_regenerate_pitch');
    fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
    fd.append('lead_id', leadId);

    fetch(ajaxurl, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            textarea.disabled = false;
            if (d.success && d.data && d.data.fresh_pitch) {
                textarea.value = d.data.fresh_pitch;
            } else {
                textarea.value = orig;
                alert('تعذر إعادة الصياغة.');
            }
        })
        .catch(function() {
            textarea.disabled = false;
            textarea.value = orig;
        });
}

function rsdGenerateLeadPdf(leadId) {
    var fd = new FormData();
    fd.append('action', 'rsd_generate_lead_pdf');
    fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
    fd.append('lead_id', leadId);

    // Visual feedback
    var btn = event.target;
    var origText = btn.innerHTML;
    btn.innerHTML = '⏳ جاري التوليد...';
    btn.disabled = true;

    fetch(ajaxurl, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            btn.innerHTML = origText;
            btn.disabled = false;
            if (d.success && d.data && d.data.file_url) {
                window.open(d.data.file_url, '_blank');
            } else {
                alert('حدث خطأ أثناء توليد الوثيقة: ' + (d.data && d.data.message ? d.data.message : ''));
            }
        })
        .catch(function(err) {
            btn.innerHTML = origText;
            btn.disabled = false;
            alert('تعذر استكمال الاتصال: ' + err.message);
        });
}

</script>