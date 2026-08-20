<?php
/**
 * Red Sea AI Suite - Clean Settings Page View (Tab 4 & Tab 7)
 */
if (!defined('ABSPATH')) exit;
?>
<?php if ($active_tab === 'appearance') : ?>
    <!-- Card 1: UI Customization -->
    <div class="rsd-card">
        <h2>🎨 تخصيص واجهة الشات والمظهر</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="rsd_widget_enabled">تفعيل الشات بالموقع</label></th>
                <td><input type="checkbox" id="rsd_widget_enabled" name="rsd_widget_enabled" value="1" <?php checked('1', get_option('rsd_widget_enabled', '1')); ?>></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_brand_color">اللون الرئيسي (Brand Color)</label></th>
                <td><input type="color" id="rsd_brand_color" name="rsd_brand_color" value="<?php echo esc_attr(get_option('rsd_brand_color', '#0284c7')); ?>" style="height:38px; width:80px; cursor:pointer;"></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_accent_color">اللون التمييزي (Accent Color)</label></th>
                <td><input type="color" id="rsd_accent_color" name="rsd_accent_color" value="<?php echo esc_attr(get_option('rsd_accent_color', '#10b981')); ?>" style="height:38px; width:80px; cursor:pointer;"></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_welcome_message">رسالة الترحيب الأولى</label></th>
                <td><input type="text" id="rsd_welcome_message" name="rsd_welcome_message" value="<?php echo esc_attr(get_option('rsd_welcome_message', '')); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_quick_chips">خيارات الإجابة السريعة (خيار بكل سطر)</label></th>
                <td><textarea id="rsd_quick_chips" name="rsd_quick_chips" rows="4" class="large-text"><?php echo esc_textarea(get_option('rsd_quick_chips', '')); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_launcher_position">موقع الأداة (Position)</label></th>
                <td>
                    <select id="rsd_launcher_position" name="rsd_launcher_position">
                        <option value="bottom-right" <?php selected('bottom-right', get_option('rsd_launcher_position', 'bottom-right')); ?>>أسفل اليمين (Bottom Right)</option>
                        <option value="bottom-left" <?php selected('bottom-left', get_option('rsd_launcher_position', 'bottom-right')); ?>>أسفل اليسار (Bottom Left)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_whatsapp_phone">رقم الواتساب للتواصل المباشر</label></th>
                <td><input type="text" id="rsd_whatsapp_phone" name="rsd_whatsapp_phone" value="<?php echo esc_attr(get_option('rsd_whatsapp_phone', '201028803080')); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_launcher_glow">هالة الإشعاع المتحركة</label></th>
                <td><input type="checkbox" id="rsd_launcher_glow" name="rsd_launcher_glow" value="1" <?php checked('1', get_option('rsd_launcher_glow', '1')); ?>></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_mobile_keyboard_fix">معالجة كيبورد الجوال (visualViewport)</label></th>
                <td><input type="checkbox" id="rsd_mobile_keyboard_fix" name="rsd_mobile_keyboard_fix" value="1" <?php checked('1', get_option('rsd_mobile_keyboard_fix', '1')); ?>></td>
            </tr>
        </table>
    </div>

    <!-- Card 2: Chatwoot Omnichannel Integration -->
    <div class="rsd-card" style="margin-top: 24px;">
        <h2>💬 إعدادات دمج Chatwoot (Omnichannel Integration)</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="rsd_chatwoot_enabled">تفعيل دمج Chatwoot</label></th>
                <td><input type="checkbox" id="rsd_chatwoot_enabled" name="rsd_chatwoot_enabled" value="1" <?php checked('1', get_option('rsd_chatwoot_enabled', '0')); ?>></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_chatwoot_url">Chatwoot Base URL</label></th>
                <td><input type="url" id="rsd_chatwoot_url" name="rsd_chatwoot_url" value="<?php echo esc_url(get_option('rsd_chatwoot_url', 'https://app.chatwoot.com')); ?>" class="regular-text" placeholder="https://app.chatwoot.com"></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_chatwoot_account_id">Chatwoot Account ID</label></th>
                <td><input type="text" id="rsd_chatwoot_account_id" name="rsd_chatwoot_account_id" value="<?php echo esc_attr(get_option('rsd_chatwoot_account_id', '')); ?>" class="regular-text" placeholder="1"></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_chatwoot_inbox_token">Chatwoot Website Token / Inbox Identifier</label></th>
                <td><input type="text" id="rsd_chatwoot_inbox_token" name="rsd_chatwoot_inbox_token" value="<?php echo esc_attr(get_option('rsd_chatwoot_inbox_token', '')); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_chatwoot_access_token">Chatwoot User API Access Token</label></th>
                <td><input type="password" id="rsd_chatwoot_access_token" name="rsd_chatwoot_access_token" value="<?php echo esc_attr(get_option('rsd_chatwoot_access_token', '')); ?>" class="regular-text"></td>
            </tr>
        </table>
    </div>

    <!-- Card 3: WhatsApp Evolution API -->
    <div class="rsd-card" style="margin-top: 24px;">
        <h2>📱 إعدادات أتمتة الواتساب (Evolution API / Meta Webhook)</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="rsd_wa_autoresponder_enabled">تفعيل الرد التلقائي على الواتساب</label></th>
                <td><input type="checkbox" id="rsd_wa_autoresponder_enabled" name="rsd_wa_autoresponder_enabled" value="1" <?php checked('1', get_option('rsd_wa_autoresponder_enabled', '0')); ?>></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_wa_api_endpoint">WhatsApp API Endpoint URL</label></th>
                <td><input type="url" id="rsd_wa_api_endpoint" name="rsd_wa_api_endpoint" value="<?php echo esc_url(get_option('rsd_wa_api_endpoint', '')); ?>" class="regular-text" placeholder="https://api.yourserver.com/message/sendText"></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_wa_api_key">WhatsApp API Key / Bearer Token</label></th>
                <td><input type="password" id="rsd_wa_api_key" name="rsd_wa_api_key" value="<?php echo esc_attr(get_option('rsd_wa_api_key', '')); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_wa_instance_id">Instance Name / Phone ID</label></th>
                <td>
                    <input type="text" id="rsd_wa_instance_id" name="rsd_wa_instance_id" value="<?php echo esc_attr(get_option('rsd_wa_instance_id', 'rsd_instance_01')); ?>" class="regular-text">
                    <p class="description">رابط الـ Webhook الخاص بموقعك للاستقبال على الواتساب: <code><?php echo site_url('/wp-json/rsd-ai/v1/whatsapp-webhook'); ?></code></p>
                </td>
            </tr>
        </table>
    </div>
<?php endif; ?>

<?php if ($active_tab === 'voice') : ?>
    <div class="rsd-card">
        <h2>🎙️ إعدادات المساعد الصوتي التفاعلي (Voice Agent Suite)</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="rsd_voice_agent_enabled">تفعيل المساعد الصوتي بالموقع</label></th>
                <td><input type="checkbox" id="rsd_voice_agent_enabled" name="rsd_voice_agent_enabled" value="1" <?php checked('1', get_option('rsd_voice_agent_enabled', '1')); ?>></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_voice_lang">لغة الصوت الافتراضية</label></th>
                <td>
                    <select id="rsd_voice_lang" name="rsd_voice_lang">
                        <option value="ar-SA" <?php selected('ar-SA', get_option('rsd_voice_lang', 'ar-SA')); ?>>العربية (ar-SA)</option>
                        <option value="en-US" <?php selected('en-US', get_option('rsd_voice_lang', 'ar-SA')); ?>>English (en-US)</option>
                        <option value="de-DE" <?php selected('de-DE', get_option('rsd_voice_lang', 'ar-SA')); ?>>Deutsch (de-DE)</option>
                        <option value="fr-FR" <?php selected('fr-FR', get_option('rsd_voice_lang', 'ar-SA')); ?>>Français (fr-FR)</option>
                        <option value="ru-RU" <?php selected('ru-RU', get_option('rsd_voice_lang', 'ar-SA')); ?>>Русский (ru-RU)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_voice_rate">سرعة النطق (Speech Rate)</label></th>
                <td>
                    <input type="number" step="0.1" min="0.5" max="2.0" id="rsd_voice_rate" name="rsd_voice_rate" value="<?php echo esc_attr(get_option('rsd_voice_rate', '1.0')); ?>" class="small-text">
                    <span class="description">القيم المتاحة من 0.5 إلى 2.0 (الافتراضي: 1.0)</span>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_voice_pitch">نبرة الصوت (Speech Pitch)</label></th>
                <td>
                    <input type="number" step="0.1" min="0.5" max="1.5" id="rsd_voice_pitch" name="rsd_voice_pitch" value="<?php echo esc_attr(get_option('rsd_voice_pitch', '1.0')); ?>" class="small-text">
                    <span class="description">القيم المتاحة من 0.5 إلى 1.5 (الافتراضي: 1.0)</span>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_voice_autospeak">نطق إجابات الأيجنت تلقائياً صوتاً</label></th>
                <td><input type="checkbox" id="rsd_voice_autospeak" name="rsd_voice_autospeak" value="1" <?php checked('1', get_option('rsd_voice_autospeak', '1')); ?>></td>
            </tr>
            <tr>
                <th scope="row"><label for="rsd_voice_strict_cleaner">تطهير النص الصوتي من النجوم والأيموجي</label></th>
                <td><input type="checkbox" id="rsd_voice_strict_cleaner" name="rsd_voice_strict_cleaner" value="1" <?php checked('1', get_option('rsd_voice_strict_cleaner', '1')); ?>></td>
            </tr>
        </table>
    </div>
<?php endif; ?>
