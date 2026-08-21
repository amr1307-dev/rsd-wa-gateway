<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

/**
 * Tab 9: Autonomous Outbound Lead Radar Partial
 * Displays niche scan trigger, active radar console logger, pipeline metrics strip (total, pending, contacting, closed), and lead audit cards with gap analysis dossier & human-in-the-loop WhatsApp approval buttons.
 */
?>

                        <?php
                        $leads_tbl = $wpdb->prefix . 'rsd_leads';
                        $all_leads = $wpdb->get_results("SELECT * FROM {$leads_tbl} ORDER BY id DESC LIMIT 50", ARRAY_A);
                        $cnt_total = count($all_leads);
                        $cnt_pending = 0; $cnt_contacting = 0; $cnt_closed = 0;
                        foreach ($all_leads as $l) {
                            if ($l['pipeline_status'] === 'pending_review') $cnt_pending++;
                            elseif ($l['pipeline_status'] === 'contacting') $cnt_contacting++;
                            elseif ($l['pipeline_status'] === 'closed') $cnt_closed++;
                        }
                        ?>

                        <!-- RADAR CONTROLS -->
                        <div class="rsd-card">
                            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
                                <div>
                                    <h3 style="margin:0 0 4px 0;font-size:1.15rem;font-weight:800;color:#0F172A;">
                                        🎯 رادار العملاء وصائد الصفقات الآلي
                                    </h3>
                                    <p style="margin:0;color:#64748B;font-size:0.86rem;">
                                        منظومة وكلاء ذكاء اصطناعي تقوم بالتنقيب وتحليل فجوة العمولات (OTA Gap) وصياغة رسائل استشارية بهوية م. عمرو أحمد مع بوابة اعتماد بشرية.
                                    </p>
                                </div>
                                <div style="display:flex;gap:10px;align-items:center;">
                                    <select id="rsdRadarNiche" class="rsd-select" style="min-width:220px;">
                                        <option value="resorts_redsea">🏨 منتجعات وبوتيك هوتيل البحر الأحمر</option>
                                        <option value="diving_sharm">🤿 مراكز وسفاري الغوص واليخوت</option>
                                        <option value="luxury_travel">✈️ شركات السياحة والرحلات الفاخرة</option>
                                        <option value="medical_clinics">🏥 مراكز السياحة العلاجية والعيادات</option>
                                    </select>
                                    <button type="button" id="rsdBtnRunRadar" onclick="rsdRunRadarScan()" class="rsd-btn">
                                        🤖 ابدأ جولة التنقيب الآلي الآن
                                    </button>
                                </div>
                            </div>

                            <div id="rsdRadarConsole" style="display:none;margin-top:18px;background:#0F172A;border-radius:12px;padding:14px 16px;color:#E2E8F0;font-family:'JetBrains Mono',monospace;font-size:0.84rem;line-height:1.6;">
                                <div style="color:#38BDF8;font-weight:700;margin-bottom:6px;">✦ وحدة الأوركسترا النشطة: جاري استكشاف وتحليل الفرص...</div>
                                <div id="rsdRadarLogLines"><div>[Scout Agent] 🔍 جاري البحث في أدلة الأعمال ومحركات الخرائط...</div></div>
                            </div>
                        </div>

                        <!-- METRICS STRIP -->
                        <div class="rsd-telemetry-grid">
                            <div class="rsd-telemetry-card">
                                <div class="rsd-telemetry-title">إجمالي الفرص المرصودة</div>
                                <div class="rsd-telemetry-val"><?php echo intval($cnt_total); ?></div>
                            </div>
                            <div class="rsd-telemetry-card" style="border-color:#FEF08A;background:#FEFCE8;">
                                <div class="rsd-telemetry-title" style="color:#A16207;">⏳ بانتظار الاعتماد البشري</div>
                                <div class="rsd-telemetry-val" style="color:#CA8A04;"><?php echo intval($cnt_pending); ?></div>
                            </div>
                            <div class="rsd-telemetry-card" style="border-color:#BAE6FD;background:#F0F9FF;">
                                <div class="rsd-telemetry-title" style="color:#0369A1;">💬 تم التواصل عبر الواتساب</div>
                                <div class="rsd-telemetry-val" style="color:#0284C7;"><?php echo intval($cnt_contacting); ?></div>
                            </div>
                            <div class="rsd-telemetry-card" style="border-color:#BBF7D0;background:#F0FDF4;">
                                <div class="rsd-telemetry-title" style="color:#15803D;">🏆 صفقات ناجحة ومغلقة</div>
                                <div class="rsd-telemetry-val" style="color:#16A34A;"><?php echo intval($cnt_closed); ?></div>
                            </div>
                        </div>

                        <!-- LEADS CARDS -->
                        <div style="display:flex;flex-direction:column;gap:18px;">
                            <?php foreach ($all_leads as $lead): ?>
                                <?php
                                $dossier = json_decode($lead['gap_analysis'] ?? '{}', true) ?: [];
                                $status = $lead['pipeline_status'] ?? 'pending_review';
                                ?>
                                <div class="rsd-card" id="leadCard_<?php echo $lead['id']; ?>" style="margin-bottom:0;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
                                        <div>
                                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                                <h4 style="margin:0;font-size:1.15rem;font-weight:800;color:#0F172A;"><?php echo esc_html($lead['company_name']); ?></h4>
                                                <span class="rsd-badge rsd-badge-info"><?php echo esc_html($lead['target_industry']); ?></span>
                                            </div>
                                            <div style="display:flex;gap:16px;font-size:0.84rem;color:#64748B;align-items:center;">
                                                <?php if (!empty($lead['website_url'])): ?>
                                                    <a href="<?php echo esc_url($lead['website_url']); ?>" target="_blank" style="color:#2563EB;text-decoration:none;font-weight:600;">🌐 <?php echo esc_html($lead['website_url']); ?></a>
                                                    <button type="button" onclick="rsdPreviewSite('<?php echo esc_url($lead['website_url']); ?>')" class="rsd-btn rsd-btn-secondary" style="padding:2px 8px;font-size:0.72rem;">👁️ معاينة</button>
                                                <?php endif; ?>
                                                <span style="direction:ltr;">📱 +<?php echo esc_html($lead['contact_phone']); ?></span>
                                                <span>📅 <?php echo esc_html($lead['created_at']); ?></span>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($status === 'pending_review'): ?>
                                                <span class="rsd-badge rsd-badge-warning">⏳ بانتظار الاعتماد</span>
                                            <?php elseif ($status === 'contacting'): ?>
                                                <span class="rsd-badge rsd-badge-info">💬 تم الإرسال وجاري المتابعة</span>
                                            <?php elseif ($status === 'rejected'): ?>
                                                <span class="rsd-badge rsd-badge-danger">🗑️ مستبعد</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div style="display:grid;grid-template-columns:1fr 1.2fr;gap:18px;margin-bottom:14px;">
                                        <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:14px;">
                                            <strong style="display:block;font-size:0.85rem;color:#0F172A;margin-bottom:8px;">📊 تقرير تدقيق الفجوات والفاقد المالي:</strong>
                                            <div style="font-size:0.82rem;margin-bottom:6px;"><span style="color:#059669;font-weight:700;">✦ نقاط القوة:</span> <?php echo esc_html($dossier['strengths'] ?? 'حضور رقمي نشط'); ?></div>
                                            <div style="font-size:0.82rem;margin-bottom:8px;"><span style="color:#DC2626;font-weight:700;">✦ الفجوات:</span> <?php echo esc_html($dossier['critical_gaps'] ?? 'غياب محرك الحجز المباشر'); ?></div>
                                            <div style="background:#FEF2F2;border:1px solid #FECACA;padding:6px 10px;border-radius:8px;font-size:0.82rem;color:#991B1B;font-weight:700;">
                                                💸 تقدير الفاقد لعمولات OTA: <?php echo esc_html($dossier['revenue_loss_estimate'] ?? '20,000$ سنوياً'); ?>
                                            </div>
                                        </div>

                                        <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:14px;">
                                            <strong style="display:block;font-size:0.85rem;color:#166534;margin-bottom:8px;">✉️ رسالة العرض المخصصة (بهوية م. عمرو أحمد):</strong>
                                            <textarea id="pitchText_<?php echo $lead['id']; ?>" class="rsd-textarea" rows="4" style="background:#FFFFFF;border-color:#86EFAC;font-size:0.85rem;"><?php echo esc_textarea($lead['tailored_pitch']); ?></textarea>
                                        </div>
                                    </div>

                                    <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #F1F5F9;padding-top:12px;">
                                        <button type="button" onclick="rsdSaveLeadPitch(<?php echo $lead['id']; ?>)" class="rsd-btn rsd-btn-secondary" style="padding:6px 14px;font-size:0.8rem;">💾 حفظ التعديل</button>
                                        <button type="button" onclick="rsdRejectLead(<?php echo $lead['id']; ?>)" class="rsd-btn-danger" style="padding:6px 14px;border-radius:8px;font-size:0.8rem;cursor:pointer;">🗑️ استبعاد</button>
                                        <?php if ($status === 'pending_review'): ?>
                                            <button type="button" onclick="rsdApproveAndSend(<?php echo $lead['id']; ?>)" class="rsd-btn" style="background:#059669;padding:6px 18px;font-size:0.82rem;">🚀 اعتماد وإرسال عبر الواتساب</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
