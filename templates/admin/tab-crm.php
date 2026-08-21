<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tab 8: WhatsApp Gateway Bridge & CRM Leads Table Partial
 * Displays 8-digit phone pairing & QR code mode switcher, gateway status badge, webhook URL configuration, and full 6-column balanced CRM table with CSV exporter and search filter.
 */
?>

                        <?php
                        $wa_phone     = get_option('rsd_whatsapp_phone', '201028803080');
                        $wa_instance  = get_option('rsd_whatsapp_instance', 'rsd_live');
                        $wa_api_url   = get_option('rsd_whatsapp_api_url', '');
                        $wa_api_key   = get_option('rsd_whatsapp_api_key', 'rsd_secret_token_2026');
                        $webhook_url  = get_rest_url(null, 'rsd/v1/whatsapp-webhook');
                        ?>

                        <div style="display:grid;grid-template-columns:1.1fr 1fr;gap:24px;margin-bottom:24px;">

                            <!-- GATEWAY STATUS & PAIRING CARD -->
                            <div class="rsd-card">
                                <div class="rsd-card-header">
                                    <h3 class="rsd-card-title">📱 اتصال بوابة الواتساب المباشرة</h3>
                                    <span id="rsdWaStatusBadge" class="rsd-badge" style="background:#F8FAFC;color:#64748B;border:1px solid #CBD5E1;">
                                        ⏳ جاري فحص الاتصال...
                                    </span>
                                </div>

                                <div style="display:flex;gap:8px;background:#F1F5F9;padding:4px;border-radius:12px;margin-bottom:18px;">
                                    <button type="button" id="tabBtnCode" onclick="rsdSwitchPairMode('code')" class="rsd-btn" style="flex:1;background:#FFFFFF;color:#0F172A;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-size:0.85rem;">
                                        🔢 ربط برقم الهاتف (كود 8 أرقام)
                                    </button>
                                    <button type="button" id="tabBtnQr" onclick="rsdSwitchPairMode('qr')" class="rsd-btn rsd-btn-secondary" style="flex:1;background:transparent;border:none;font-size:0.85rem;">
                                        📷 ربط عبر كاميرا الهاتف (QR)
                                    </button>
                                </div>

                                <div id="rsdPairModeCode" style="display:block;">
                                    <p style="font-size:0.85rem;color:#64748B;margin:0 0 12px 0;">أدخل رقم الهاتف لتوليد كود تأكيد مباشر لإدخاله في تطبيق واتساب:</p>
                                    <div style="display:flex;gap:8px;margin-bottom:12px;">
                                        <input type="text" id="rsdPairPhoneInput" class="rsd-input" value="<?php echo esc_attr($wa_phone); ?>" placeholder="مثال: 201028803080">
                                        <button type="button" onclick="rsdRequestPairingCode()" class="rsd-btn" style="white-space:nowrap;">
                                            ⚡ طلب كود التأكيد
                                        </button>
                                    </div>

                                    <div id="rsdPairingCodeDisplay" style="display:none;background:#F0FDF4;border:2px solid #86EFAC;border-radius:14px;padding:16px;text-align:center;margin-top:14px;">
                                        <div style="font-size:0.82rem;color:#166534;font-weight:700;margin-bottom:6px;">أدخل هذا الكود في هاتفك (الأجهزة المرتبطة ➔ ربط برقم الهاتف):</div>
                                        <div id="rsdPairingCodeVal" style="font-size:2rem;font-weight:900;letter-spacing:6px;color:#15803D;font-family:monospace;">----</div>
                                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('rsdPairingCodeVal').innerText);alert('تم نسخ الكود!');" class="rsd-btn rsd-btn-secondary" style="margin-top:10px;padding:4px 12px;font-size:0.78rem;">
                                            📋 نسخ الكود
                                        </button>
                                    </div>
                                </div>

                                <div id="rsdPairModeQr" style="display:none;text-align:center;">
                                    <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:14px;padding:16px;display:inline-block;margin-bottom:12px;">
                                        <img id="rsdQrCodeImg" src="" alt="QR Code" style="display:none;width:180px;height:180px;margin:0 auto;">
                                        <div id="rsdQrPlaceholder" style="color:#64748B;padding:40px 20px;font-size:0.85rem;">اضغط على الزر أدناه لتوليد رمز الـ QR</div>
                                    </div>
                                    <br>
                                    <button type="button" onclick="rsdRefreshQrCode()" class="rsd-btn rsd-btn-secondary">🔄 تحديث كود QR</button>
                                </div>

                                <div style="display:flex;gap:10px;margin-top:20px;border-top:1px solid #F1F5F9;padding-top:16px;">
                                    <button type="button" onclick="rsdCheckWaStatus()" class="rsd-btn rsd-btn-secondary" style="flex:1;">🔄 فحص الحالة</button>
                                    <button type="button" onclick="rsdDisconnectWa()" class="rsd-btn-danger" style="flex:1;padding:8px 14px;border-radius:10px;cursor:pointer;font-weight:700;font-size:0.85rem;">🔴 فك الارتباط</button>
                                </div>
                            </div>

                            <!-- WEBHOOK & GATEWAY SETTINGS CARD -->
                            <div class="rsd-card">
                                <div class="rsd-card-header">
                                    <h3 class="rsd-card-title">⚙️ إعدادات البوابة والـ Webhook</h3>
                                </div>

                                <form method="POST">
                                    <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
                                    <input type="hidden" name="active_tab" value="crm">

                                    <div class="rsd-form-group">
                                        <label class="rsd-label">🔗 رابط الـ Webhook المخصص للاستقبال</label>
                                        <div style="display:flex;gap:8px;">
                                            <input type="text" id="rsdWebhookUrlInput" readonly class="rsd-input" value="<?php echo esc_attr($webhook_url); ?>" style="background:#F8FAFC;font-family:monospace;direction:ltr;">
                                            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('rsdWebhookUrlInput').value);alert('تم نسخ الرابط!');" class="rsd-btn rsd-btn-secondary" style="white-space:nowrap;">📋 نسخ</button>
                                        </div>
                                    </div>

                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                                        <div class="rsd-form-group">
                                            <label class="rsd-label">رقم الهاتف</label>
                                            <input type="text" name="rsd_whatsapp_phone" class="rsd-input" value="<?php echo esc_attr($wa_phone); ?>">
                                        </div>
                                        <div class="rsd-form-group">
                                            <label class="rsd-label">اسم الجلسة (Instance)</label>
                                            <input type="text" name="rsd_whatsapp_instance" class="rsd-input" value="<?php echo esc_attr($wa_instance); ?>">
                                        </div>
                                    </div>

                                    <div class="rsd-form-group">
                                        <label class="rsd-label">رابط خادم البوابة / السوكيت</label>
                                        <input type="text" name="rsd_whatsapp_api_url" class="rsd-input" value="<?php echo esc_attr($wa_api_url); ?>" placeholder="https://api.your-gateway.com">
                                    </div>

                                    <div class="rsd-form-group">
                                        <label class="rsd-label">مفتاح الأمان (API Key)</label>
                                        <input type="password" name="rsd_whatsapp_api_key" class="rsd-input" value="<?php echo esc_attr($wa_api_key); ?>">
                                    </div>

                                    <button type="submit" name="rsd_save_settings" class="rsd-btn">💾 حفظ الإعدادات</button>
                                </form>
                            </div>

                        </div>

                                                                                                <!-- LEADS CRM TABLE -->
                        <div class="rsd-card rsd-crm-card">
                            <div class="rsd-card-header" style="margin-bottom:0;padding-bottom:12px;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <h3 class="rsd-card-title">👥 سجل جهات الاتصال والحجوزات المسجلة</h3>
                                    <span class="rsd-badge rsd-badge-success"><?php echo intval($total_leads); ?> عميل مسجل</span>
                                </div>
                                <div style="display:flex;gap:10px;">
                                    <input type="text" id="rsdCrmSearch" class="rsd-input" placeholder="🔍 بحث في السجلات..." onkeyup="rsdFilterCrmTable()" style="width:220px;padding:6px 12px;font-size:0.84rem;">
                                    <button type="button" onclick="rsdExportCrmCsv()" class="rsd-btn rsd-btn-secondary" style="padding:6px 14px;font-size:0.82rem;">📥 تصدير CSV</button>
                                </div>
                            </div>

                            <div class="rsd-crm-table-container">
                                <table class="rsd-table rsd-crm-table" id="rsdCrmTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>اسم العميل</th>
                                            <th>رقم الواتساب</th>
                                            <th>نوع الخدمة</th>
                                            <th>تفاصيل المحادثة</th>
                                            <th class="date-col">التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recent_logs)): ?>
                                            <?php foreach ($recent_logs as $log): ?>
                                                <?php
                                                $c_name = $log['customer_name'] ?? ($log['name'] ?? 'عميل واتساب');
                                                $c_phone = $log['customer_phone'] ?? ($log['phone'] ?? '');
                                                $c_service = $log['service_type'] ?? ($log['service'] ?? 'استفسار مباشر');
                                                $c_details = $log['booking_details'] ?? ($log['details'] ?? '-');
                                                ?>
                                                <tr>
                                                    <td>#<?php echo esc_html($log['id']); ?></td>
                                                    <td style="font-weight:700;color:#0F172A;"><?php echo esc_html($c_name); ?></td>
                                                    <td>
                                                        <?php if (!empty($c_phone)): ?>
                                                            <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $c_phone)); ?>" target="_blank" class="rsd-phone-badge">
                                                                💬 +<?php echo esc_html($c_phone); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="rsd-badge">غير مسجل</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><span class="rsd-badge rsd-badge-info"><?php echo esc_html($c_service); ?></span></td>
                                                    <td><?php echo esc_html($c_details); ?></td>
                                                    <td class="date-col"><?php echo esc_html($log['created_at']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <script>
                            function rsdFilterCrmTable() {
                                var q = document.getElementById('rsdCrmSearch').value.toLowerCase();
                                var rows = document.querySelectorAll('#rsdCrmTable tbody tr');
                                rows.forEach(function(r) {
                                    r.style.display = r.innerText.toLowerCase().includes(q) ? '' : 'none';
                                });
                            }

                            function rsdExportCrmCsv() {
                                var rows = document.querySelectorAll('#rsdCrmTable tr');
                                var csv = [];
                                rows.forEach(function(r) {
                                    var cols = r.querySelectorAll('th, td');
                                    var rowData = [];
                                    cols.forEach(function(c) { rowData.push('"' + c.innerText.replace(/"/g, '""') + '"'); });
                                    csv.push(rowData.join(','));
                                });
                                var blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
                                var link = document.createElement('a');
                                link.href = URL.createObjectURL(blob);
                                link.download = 'rsd_crm_leads.csv';
                                link.click();
                            }
                        </script>
