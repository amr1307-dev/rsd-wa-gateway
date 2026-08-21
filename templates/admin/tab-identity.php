<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tab 3: Business Identity & Info Partial
 * Displays company name, slogan, headquarters, direct booking URL, and master system prompt editor with live token counter.
 */
?>

                        <div class="rsd-card">
                            <div class="rsd-card-header">
                                <h3 class="rsd-card-title">🏛️ هوية المنشأة ومعلومات النشاط</h3>
                            </div>

                            <form method="POST">
                                <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
                                <input type="hidden" name="active_tab" value="company">

                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">اسم المنشأة / البراند الرسمي</label>
                                        <input type="text" name="rsd_company_name" class="rsd-input" value="<?php echo esc_attr(get_option('rsd_company_name', 'RED SEA DIGITAL')); ?>">
                                    </div>
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">الشعار الترويجي (Slogan)</label>
                                        <input type="text" name="rsd_company_slogan" class="rsd-input" value="<?php echo esc_attr(get_option('rsd_company_slogan', 'منظومة الحجز المباشر بالذكاء الاصطناعي')); ?>">
                                    </div>
                                </div>

                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">المقر الرئيسي / المدينة</label>
                                        <input type="text" name="rsd_company_hq" class="rsd-input" value="<?php echo esc_attr(get_option('rsd_company_hq', 'الغردقة، البحر الأحمر، مصر')); ?>">
                                    </div>
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">رابط صفحة الحجز المباشر</label>
                                        <input type="url" name="rsd_booking_url" class="rsd-input" value="<?php echo esc_attr(get_option('rsd_booking_url', 'https://redseadigital.pro/#booking')); ?>">
                                    </div>
                                </div>

                                <div class="rsd-form-group">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                        <label class="rsd-label" style="margin:0;">البرومبت الأساسي لشخصية المنشأة (System Persona Context)</label>
                                        <span id="rsdTokenCounter" style="font-size:0.78rem;color:#64748B;font-family:monospace;">~ 120 Tokens</span>
                                    </div>
                                    <textarea id="rsdSystemPromptInput" name="rsd_system_prompt" class="rsd-textarea" rows="6" style="background:#0F172A;color:#E2E8F0;font-family:'JetBrains Mono',monospace;font-size:0.86rem;line-height:1.6;" oninput="document.getElementById('rsdTokenCounter').innerText = '~ ' + Math.round(this.value.length / 4) + ' Tokens';"><?php echo esc_textarea(get_option('rsd_system_prompt', 'أنت المستشار التقني والمبيعات لمنظومة RED SEA DIGITAL...')); ?></textarea>
                                </div>

                                <button type="submit" name="rsd_save_settings" class="rsd-btn">
                                    💾 حفظ بيانات الهوية
                                </button>
                            </form>
                        </div>
