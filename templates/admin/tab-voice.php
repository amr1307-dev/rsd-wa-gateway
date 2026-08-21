<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tab 7: Voice AI Studio Partial
 * Displays speech language selection, voice rate and pitch controls, interactive speech synthesis preview tester, and settings form.
 */
?>

                        <div class="rsd-card">
                            <div class="rsd-card-header">
                                <h3 class="rsd-card-title">🎙️ استوديو الصوت التوليدي (Voice AI Studio)</h3>
                            </div>

                            <form method="POST">
                                <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
                                <input type="hidden" name="active_tab" value="voice">

                                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;">
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">لغة ونبرة الصوت</label>
                                        <select id="rsdVoiceLangSelect" name="rsd_voice_lang" class="rsd-select">
                                            <option value="ar-SA" <?php selected(get_option('rsd_voice_lang'), 'ar-SA'); ?>>العربية (لهجة مصرية/خليجية دافئة)</option>
                                            <option value="en-US" <?php selected(get_option('rsd_voice_lang'), 'en-US'); ?>>English (US Luxury Accent)</option>
                                        </select>
                                    </div>
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">سرعة القراءة (Rate)</label>
                                        <input type="text" id="rsdVoiceRateInput" name="rsd_voice_rate" class="rsd-input" value="<?php echo esc_attr(get_option('rsd_voice_rate', '1.0')); ?>">
                                    </div>
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">درجة الحدة (Pitch)</label>
                                        <input type="text" id="rsdVoicePitchInput" name="rsd_voice_pitch" class="rsd-input" value="<?php echo esc_attr(get_option('rsd_voice_pitch', '1.0')); ?>">
                                    </div>
                                </div>

                                <!-- AUDIO PREVIEW TESTER -->
                                <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:14px;padding:18px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;">
                                    <div>
                                        <strong style="color:#0F172A;font-size:0.95rem;display:block;margin-bottom:4px;">🔊 تجربة العينة الصوتية مباشرة</strong>
                                        <span style="font-size:0.84rem;color:#64748B;">استمع إلى نبرة الترحيب المباشرة للتأكد من ملاءمتها لعلامتك التجارية.</span>
                                    </div>
                                    <button type="button" onclick="rsdPlaySampleAudio()" class="rsd-btn rsd-btn-secondary" style="padding:8px 18px;">
                                        ▶️ استمع للعينة الصوتية
                                    </button>
                                </div>

                                <button type="submit" name="rsd_save_settings" class="rsd-btn">
                                    💾 حفظ إعدادات الصوت
                                </button>
                            </form>
                        </div>
                        <script>
                            function rsdPlaySampleAudio() {
                                if ('speechSynthesis' in window) {
                                    window.speechSynthesis.cancel();
                                    var text = "أهلاً بك في ريد سي ديجيتال، منظومة الحجز المباشر الذكية.";
                                    var lang = document.getElementById('rsdVoiceLangSelect').value;
                                    var rate = parseFloat(document.getElementById('rsdVoiceRateInput').value) || 1.0;
                                    var pitch = parseFloat(document.getElementById('rsdVoicePitchInput').value) || 1.0;
                                    var utter = new SpeechSynthesisUtterance(text);
                                    utter.lang = lang;
                                    utter.rate = rate;
                                    utter.pitch = pitch;
                                    window.speechSynthesis.speak(utter);
                                } else {
                                    alert('المتصفح لا يدعم التشغيل الصوتي المباشر.');
                                }
                            }
                        </script>
