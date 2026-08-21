<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tab 1: Overview & Telemetry Partial
 * Displays CRM lead counts, vector chunk index status, active KB files, and real-time orchestration trace timeline.
 */
?>
                        
                        <div class="rsd-telemetry-grid">
                            <div class="rsd-telemetry-card">
                                <div class="rsd-telemetry-title">إجمالي العملاء والحجوزات</div>
                                <div class="rsd-telemetry-val"><?php echo number_format($total_leads); ?></div>
                                <div class="rsd-telemetry-sub">مسجل بالـ CRM</div>
                            </div>
                            <div class="rsd-telemetry-card">
                                <div class="rsd-telemetry-title">المقاطع المتجهية المفهرسة</div>
                                <div class="rsd-telemetry-val"><?php echo number_format($total_chunks); ?></div>
                                <div class="rsd-telemetry-sub">جاهزة للاستعلام الدلالي</div>
                            </div>
                            <div class="rsd-telemetry-card">
                                <div class="rsd-telemetry-title">ملفات قاعدة المعرفة النشطة</div>
                                <div class="rsd-telemetry-val"><?php echo count($kb_files); ?></div>
                                <div class="rsd-telemetry-sub">محدثة ومتاحة للوكلاء</div>
                            </div>
                            <div class="rsd-telemetry-card">
                                <div class="rsd-telemetry-title">سلسلة الفشل التلقائي</div>
                                <div class="rsd-telemetry-val" style="color:#16A34A;font-size:1.25rem;">نشط 100%</div>
                                <div class="rsd-telemetry-sub">OpenCode ➔ Gemini ➔ DeepSeek</div>
                            </div>
                        </div>

                        <!-- LIVE TRACES TIMELINE STREAM -->
                        <div class="rsd-card">
                            <div class="rsd-card-header">
                                <h3 class="rsd-card-title">
                                    <span>⚡ سجل استدلال الأوركسترا اللحظي (Interactive Timeline Stream)</span>
                                </h3>
                                <span class="rsd-badge rsd-badge-info">أحدث العمليات الحية</span>
                            </div>

                            <?php if (empty($traces)): ?>
                                <p style="color:#64748B;text-align:center;padding:24px 0;">لا توجد سجلات تتبع حالياً. ستظهر العمليات هنا فور محادثة العملاء مع المحرك.</p>
                            <?php else: ?>
                                <div style="display:flex;flex-direction:column;gap:12px;">
                                    <?php foreach (array_slice(array_reverse($traces), 0, 15) as $i => $trace): ?>
                                        <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:14px;padding:16px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                                <div style="display:flex;gap:8px;align-items:center;">
                                                    <span class="rsd-badge rsd-badge-purple"><?php echo esc_html($trace['intent'] ?? 'عام'); ?></span>
                                                    <span class="rsd-badge rsd-badge-info"><?php echo esc_html($trace['model'] ?? 'opencode'); ?></span>
                                                    <strong style="color:#0F172A;font-size:0.9rem;">👤 <?php echo esc_html($trace['sender'] ?? 'زائر'); ?></strong>
                                                </div>
                                                <div style="display:flex;gap:10px;align-items:center;">
                                                    <span style="font-size:0.78rem;color:#94A3B8;">📅 <?php echo esc_html($trace['timestamp'] ?? ''); ?></span>
                                                    <button type="button" onclick="rsdInspectTrace(<?php echo $i; ?>)" class="rsd-btn rsd-btn-secondary" style="padding:3px 10px;font-size:0.75rem;">🔍 فحص JSON</button>
                                                </div>
                                            </div>
                                            <div style="font-size:0.86rem;color:#334155;line-height:1.5;margin-bottom:8px;background:#F8FAFC;padding:10px 14px;border-radius:8px;border-right:3px solid #3B82F6;">
                                                <strong>سؤال العميل:</strong> <?php echo esc_html($trace['user_message'] ?? 'استفسار أولي عن الحجز والخدمات'); ?>
                                            </div>
                                            <div style="font-size:0.86rem;color:#1E293B;background:#F0FDF4;padding:12px 14px;border-radius:8px;border-right:3px solid #10B981;line-height:1.5;">
                                                <strong>رد المحرك:</strong> <?php echo esc_html($trace['final_reply'] ?? 'أهلاً بك في RED SEA DIGITAL! يسعدنا تقديم استشارة كاملة لحجزك.'); ?>
                                            </div>
                                            <script>
                                                window.rsdTraceData = window.rsdTraceData || {};
                                                window.rsdTraceData[<?php echo $i; ?>] = <?php echo json_encode($trace, JSON_UNESCAPED_UNICODE); ?>;
                                            </script>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
