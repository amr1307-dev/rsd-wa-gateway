<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tab 5: Sales & Speed of Response Partial
 * Displays sales persona tone selector, OTA commission slider with live ROI preview calculation, and smart response cache toggle.
 */
?>

                        <div class="rsd-card">
                            <div class="rsd-card-header">
                                <h3 class="rsd-card-title">⚡ إعدادات وكيل المبيعات وسرعة الرد</h3>
                            </div>

                            <form method="POST">
                                <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
                                <input type="hidden" name="active_tab" value="concierge">

                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">نبرة الحوار المبيعي (Sales Persona Tone)</label>
                                        <select name="rsd_sales_tone" class="rsd-select">
                                            <option value="elite_closer" <?php selected(get_option('rsd_sales_tone'), 'elite_closer'); ?>>مستشار مبيعات خبير وهادئ (Quiet Luxury)</option>
                                            <option value="consultative" <?php selected(get_option('rsd_sales_tone'), 'consultative'); ?>>استشاري أرقام وعائد استثماري (ROI Focused)</option>
                                            <option value="friendly" <?php selected(get_option('rsd_sales_tone'), 'friendly'); ?>>مضياف وودود (Hospitality Concierge)</option>
                                        </select>
                                    </div>
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">نسبة عمولة الوسطاء الافتراضية للحاسبة (%)</label>
                                        <div style="display:flex;gap:14px;align-items:center;">
                                            <input type="range" min="5" max="30" step="1" id="rsdCommSlider" value="<?php echo esc_attr(get_option('rsd_concierge_commission_preset', '20')); ?>" oninput="document.getElementById('rsdCommInput').value = this.value; rsdUpdateRoiPreview(this.value);" style="flex:1;">
                                            <input type="number" id="rsdCommInput" name="rsd_concierge_commission_preset" class="rsd-input" style="width:90px;" value="<?php echo esc_attr(get_option('rsd_concierge_commission_preset', '20')); ?>" oninput="document.getElementById('rsdCommSlider').value = this.value; rsdUpdateRoiPreview(this.value);">
                                        </div>
                                    </div>
                                </div>

                                <!-- LIVE OTA SAVINGS PREVIEW -->
                                <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:14px;padding:18px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
                                    <div>
                                        <h4 style="margin:0 0 4px 0;color:#166534;font-weight:800;font-size:0.95rem;">💡 محاكاة التوفير المالي للعميل (OTA Savings Simulation)</h4>
                                        <p style="margin:0;color:#15803D;font-size:0.84rem;">لمبيعات غرف بقيمة 50,000$ شهرياً، سيوفر الفندق للعميل بفضل الحجز المباشر:</p>
                                    </div>
                                    <div id="rsdRoiPreviewVal" style="font-size:1.6rem;font-weight:900;color:#166534;font-family:monospace;">
                                        $10,000 / شهرياً
                                    </div>
                                </div>

                                <div class="rsd-form-group">
                                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:700;color:#0F172A;">
                                        <input type="checkbox" name="rsd_enable_response_cache" value="1" <?php checked(get_option('rsd_enable_response_cache', '1'), '1'); ?> style="width:18px;height:18px;">
                                        <span>تفعيل التخزين المؤقت الذكي للردود المتكررة لتوفير استهلاك التوكنات وتسريع الاستجابة لأقل من 500ms</span>
                                    </label>
                                </div>

                                <button type="submit" name="rsd_save_settings" class="rsd-btn">
                                    💾 حفظ إعدادات الوكيل
                                </button>
                            </form>
                        </div>
                        <script>
                            function rsdUpdateRoiPreview(comm) {
                                var val = Math.round(50000 * (comm / 100));
                                document.getElementById('rsdRoiPreviewVal').innerText = '$' + val.toLocaleString() + ' / شهرياً';
                            }
                        </script>
