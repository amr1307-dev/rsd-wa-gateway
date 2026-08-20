<?php
/**
 * WP Admin Settings Screen for Red Sea AI Widget
 */

if (!defined('ABSPATH')) exit;

class RSD_Admin_Page {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_admin_menu']);
        add_action('admin_init', [__CLASS__, 'handle_settings_save']);
    }

    public static function register_admin_menu() {
        add_menu_page(
            'Red Sea AI Widget',
            'AI Chat Widget',
            'manage_options',
            'redsea-ai-widget-settings',
            [__CLASS__, 'render_settings_page'],
            'dashicons-format-chat',
            7
        );
    }

    public static function handle_settings_save() {
        if (isset($_POST['rsd_save_widget_settings']) && check_admin_referer('rsd_widget_settings_nonce')) {
            update_option('rsd_widget_enabled', isset($_POST['rsd_widget_enabled']) ? '1' : '0');
            update_option('rsd_ai_provider', sanitize_text_field($_POST['rsd_ai_provider'] ?? 'opencode'));
            update_option('rsd_ai_model', sanitize_text_field($_POST['rsd_ai_model'] ?? 'deepseek-v4-flash-free'));
            update_option('rsd_opencode_api_key', sanitize_text_field($_POST['rsd_opencode_api_key'] ?? ''));
            update_option('rsd_gemini_api_key', sanitize_text_field($_POST['rsd_gemini_api_key'] ?? ''));
            update_option('rsd_openai_api_key', sanitize_text_field($_POST['rsd_openai_api_key'] ?? ''));
            update_option('rsd_opencode_endpoint', esc_url_raw($_POST['rsd_opencode_endpoint'] ?? 'https://opencode.ai/zen/v1/chat/completions'));
            
            update_option('rsd_brand_color', sanitize_hex_color($_POST['rsd_brand_color'] ?? '#0284c7'));
            update_option('rsd_accent_color', sanitize_hex_color($_POST['rsd_accent_color'] ?? '#10b981'));
            update_option('rsd_welcome_message', sanitize_text_field($_POST['rsd_welcome_message'] ?? 'أهلاً بك في Red Sea Digital ✨ كيف يمكنني مساعدتك اليوم؟'));
            update_option('rsd_quick_chips', sanitize_textarea_field($_POST['rsd_quick_chips'] ?? "طلب استشارة الباقة الملكية ($499)\nعرض تفاصيل باقة الفنادق\nحجز موعد تواصل عبر الواتساب"));
            update_option('rsd_whatsapp_phone', sanitize_text_field($_POST['rsd_whatsapp_phone'] ?? '01028803080'));

            if (isset($_POST['rsd_system_prompt'])) {
                update_option('rsd_system_prompt', wp_kses_post($_POST['rsd_system_prompt']));
            }

            wp_redirect(admin_url('admin.php?page=redsea-ai-widget-settings&updated=true'));
            exit;
        }
    }

    public static function render_settings_page() {
        global $wpdb;
        $tbl = $wpdb->prefix . 'rsd_bookings';
        $bookings = $wpdb->get_results("SELECT * FROM {$tbl} ORDER BY id DESC", ARRAY_A);

        $provider      = get_option('rsd_ai_provider', 'opencode');
        $model         = get_option('rsd_ai_model', 'deepseek-v4-flash-free');
        $opencode_key  = get_option('rsd_opencode_api_key', '');
        $gemini_key    = get_option('rsd_gemini_api_key', '');
        $openai_key    = get_option('rsd_openai_api_key', '');
        $opencode_end  = get_option('rsd_opencode_endpoint', 'https://opencode.ai/zen/v1/chat/completions');

        $brand_color   = get_option('rsd_brand_color', '#0284c7');
        $accent_color  = get_option('rsd_accent_color', '#10b981');
        $welcome_msg   = get_option('rsd_welcome_message', 'أهلاً بك في Red Sea Digital ✨ كيف يمكنني مساعدتك في استعادة أرباحك وتوفير عمولات المنصات اليوم؟');
        $quick_chips   = get_option('rsd_quick_chips', "طلب استشارة الباقة الملكية ($499)\nعرض تفاصيل باقة الفنادق\nحجز موعد تواصل عبر الواتساب");
        $wa_phone      = get_option('rsd_whatsapp_phone', '01028803080');

        $system_prompt = get_option('rsd_system_prompt', '');

        ?>
        <style>
            .rsd-widget-admin { font-family: Inter, system-ui, sans-serif; direction: rtl; text-align: right; color: #0f172a; margin-top: 20px; max-width: 1200px; }
            .rsd-widget-admin * { box-sizing: border-box; }
            .rsd-admin-card { background: #ffffff; padding: 28px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05); margin-bottom: 24px; }
            .rsd-admin-hdr { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 24px 32px; border-radius: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; }
            .rsd-admin-hdr h1 { color: #fff; margin: 0; font-size: 1.6rem; font-weight: 800; }
            .rsd-admin-hdr p { color: #94a3b8; margin: 4px 0 0 0; }
            .rsd-field { margin-bottom: 20px; }
            .rsd-field label { display: block; font-weight: 700; margin-bottom: 8px; color: #334155; }
            .rsd-field input[type="text"], .rsd-field input[type="password"], .rsd-field textarea { width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.95rem; }
            .rsd-btn-save { background: #0f172a; color: #ffffff; border: none; padding: 12px 32px; border-radius: 10px; font-weight: 800; font-size: 1rem; cursor: pointer; transition: all 0.2s ease; }
            .rsd-btn-save:hover { background: #0284c7; transform: translateY(-1px); }
        </style>

        <div class="wrap rsd-widget-admin">
            <div class="rsd-admin-hdr">
                <div>
                    <h1>👑 Red Sea AI Widget — Management Hub v4.0.0</h1>
                    <p>إعدادات وتخصيص ويدجت الذكاء الاصطناعي للمواجهات الأمامية</p>
                </div>
            </div>

            <?php if (isset($_GET['updated'])) : ?>
                <div class="notice notice-success is-dismissible" style="border-radius: 8px; font-weight: bold;"><p>✅ تم حفظ إعدادات الويدجت وتحديث الواجهة بنجاح!</p></div>
            <?php endif; ?>

            <form method="post" action="">
                <?php wp_nonce_field('rsd_widget_settings_nonce'); ?>

                <!-- DESIGN & PERSONA SETTINGS -->
                <div class="rsd-admin-card">
                    <h3 style="margin-top:0; color:#0f172a;">🎨 تخصيص التظهير والتصميم (UI & Branding)</h3>
                    
                    <div class="rsd-field">
                        <label><input type="checkbox" name="rsd_widget_enabled" value="1" <?php checked(get_option('rsd_widget_enabled', '1'), '1'); ?>> تفعيل ظهور الويدجت العائم في الواجهة الأمامية للموقع</label>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div class="rsd-field">
                            <label>لون الهوية الأساسي (Brand Color):</label>
                            <input type="color" name="rsd_brand_color" value="<?php echo esc_attr($brand_color); ?>" style="height: 42px; width: 100%; border-radius: 8px; cursor: pointer;">
                        </div>
                        <div class="rsd-field">
                            <label>لون التمييز (Accent Color):</label>
                            <input type="color" name="rsd_accent_color" value="<?php echo esc_attr($accent_color); ?>" style="height: 42px; width: 100%; border-radius: 8px; cursor: pointer;">
                        </div>
                        <div class="rsd-field">
                            <label>رقم الواتساب للتواصل المباشر:</label>
                            <input type="text" name="rsd_whatsapp_phone" value="<?php echo esc_attr($wa_phone); ?>" placeholder="01028803080">
                        </div>
                    </div>

                    <div class="rsd-field">
                        <label>رسالة الترحيب الأولى (Welcome Message):</label>
                        <input type="text" name="rsd_welcome_message" value="<?php echo esc_attr($welcome_msg); ?>">
                    </div>

                    <div class="rsd-field">
                        <label>خيارات الأزرار الفورية (Quick Choice Chips — خيار بكل سطر):</label>
                        <textarea name="rsd_quick_chips" rows="3"><?php echo esc_textarea($quick_chips); ?></textarea>
                    </div>
                </div>

                <!-- API KEYS & PROVIDER -->
                <div class="rsd-admin-card">
                    <h3 style="margin-top:0; color:#0f172a;">🔑 ربط المحركات والمفاتيح (AI Provider & API Keys)</h3>
                    
                    <div class="rsd-field">
                        <label>المحرك النشط (Active Provider):</label>
                        <select name="rsd_ai_provider" style="padding: 8px; border-radius: 8px; border: 1px solid #cbd5e1; width: 100%; max-width: 400px;">
                            <option value="opencode" <?php selected($provider, 'opencode'); ?>>OpenCode.ai Zen Workspace (🆓 Free Models)</option>
                            <option value="gemini" <?php selected($provider, 'gemini'); ?>>Google Gemini (1,500 Free Daily Calls)</option>
                            <option value="openai" <?php selected($provider, 'openai'); ?>>OpenAI ChatGPT Direct</option>
                        </select>
                    </div>

                    <div class="rsd-field">
                        <label>معرف النموذج (Model Identifier):</label>
                        <input type="text" name="rsd_ai_model" value="<?php echo esc_attr($model); ?>" style="max-width: 500px; font-family: monospace;">
                    </div>

                    <div class="rsd-field">
                        <label>OpenCode API Key:</label>
                        <input type="password" name="rsd_opencode_api_key" value="<?php echo esc_attr($opencode_key); ?>" style="font-family: monospace;">
                    </div>

                    <div class="rsd-field">
                        <label>Google Gemini API Key:</label>
                        <input type="password" name="rsd_gemini_api_key" value="<?php echo esc_attr($gemini_key); ?>" style="font-family: monospace;">
                    </div>

                    <div class="rsd-field">
                        <label>System Prompt (توجيهات شخصية الـ Agent):</label>
                        <textarea name="rsd_system_prompt" rows="8" style="font-family: monospace; font-size: 0.9rem; background: #f8fafc;"><?php echo esc_textarea($system_prompt); ?></textarea>
                    </div>
                </div>

                <!-- CRM BOOKINGS TABLE -->
                <div class="rsd-admin-card">
                    <h3 style="margin-top:0; color:#0f172a;">📋 الحجوزات المسجلة من الويدجت (Widget Captured Leads)</h3>
                    <table class="wp-list-table widefat fixed striped" style="border-radius: 8px; overflow: hidden;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>اسم العميل</th>
                                <th>رقم الهاتف</th>
                                <th>نوع النشاط</th>
                                <th>تفاصيل الحجز</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($bookings) : foreach ($bookings as $b) : ?>
                                <tr>
                                    <td><strong>#<?php echo esc_html($b['id']); ?></strong></td>
                                    <td><strong><?php echo esc_html($b['customer_name']); ?></strong></td>
                                    <td><code><?php echo esc_html($b['customer_phone']); ?></code></td>
                                    <td><?php echo esc_html($b['service_type']); ?></td>
                                    <td><?php echo esc_html($b['booking_details']); ?></td>
                                    <td><?php echo esc_html($b['created_at']); ?></td>
                                </tr>
                            <?php endforeach; else : ?>
                                <tr><td colspan="6">لا توجد حجوزات مسجلة بعد.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 20px;">
                    <input type="submit" name="rsd_save_widget_settings" class="rsd-btn-save" value="💾 حفظ الإعدادات والتحديث الفوري">
                </div>

            </form>
        </div>
        <?php
    }
}
