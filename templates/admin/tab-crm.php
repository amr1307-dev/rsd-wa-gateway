<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tab 8: Dual-Engine WhatsApp Gateway Bridge & CRM Leads Table Partial
 * Features Official Meta Cloud API integration, Local QR/Socket Bridge with legal disclaimer,
 * live gateway status checker, webhook URL configuration, and full 6-column CRM leads table.
 */

use RedSea\Gateway\WhatsAppGateway;

$gateway_mode = get_option('rsd_whatsapp_gateway_mode', 'official_cloud');

// Meta Cloud API Options
$meta_app_id       = get_option('rsd_meta_app_id', '');
$meta_phone_id     = get_option('rsd_meta_phone_id', get_option('rsd_whatsapp_phone_number_id', ''));
$meta_waba_id      = get_option('rsd_meta_waba_id', '');
$meta_access_token = get_option('rsd_meta_access_token', get_option('rsd_whatsapp_cloud_token', ''));
$meta_verify_token = get_option('rsd_meta_webhook_verify_token', 'rsd_meta_secure_token_2026');

// Local Bridge Options
$wa_phone     = get_option('rsd_whatsapp_phone', '201028803080');
$wa_instance  = get_option('rsd_whatsapp_instance', 'rsd_live');
$wa_api_url   = get_option('rsd_whatsapp_api_url', '');
$wa_api_key   = get_option('rsd_whatsapp_api_key', 'rsd_secret_token_2026');

$webhook_url  = get_rest_url(null, 'rsd/v1/whatsapp-webhook');
?>

<!-- DUAL-ENGINE SELECTION BAR -->
<div class="rsd-card" style="margin-bottom:24px;border:2px solid #E2E8F0;background:#FFFFFF;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;margin-bottom:16px;">
        <div>
            <h3 style="margin:0 0 4px 0;font-size:1.15rem;font-weight:800;color:#0F172A;">
                ⚡ محرك بوابة الواتساب المزدوج (Dual-Engine WhatsApp Gateway)
            </h3>
            <p style="margin:0;color:#64748B;font-size:0.86rem;">
                اختر البنية التحتية المناسبة لقناة الواتساب الخاصة بمنشأتك: الربط السحابي المعتمد أو السوكيت المباشر.
            </p>
        </div>
        <div style="display:flex;gap:10px;">
            <span class="rsd-badge" style="<?php echo $gateway_mode === 'official_cloud' ? 'background:#DCFCE7;color:#166534;border:1px solid #86EFAC;' : 'background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;'; ?>">
                <?php echo $gateway_mode === 'official_cloud' ? '🟢 النمط النشط: Meta Cloud API السحابي المعتمد' : '🟡 النمط النشط: Local Socket / QR Bridge'; ?>
            </span>
        </div>
    </div>

    <!-- ENGINE SELECTOR RADIO TABS -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div onclick="rsdSelectGatewayEngine('official_cloud')" id="cardEngineMeta" style="cursor:pointer;border:2px solid <?php echo $gateway_mode === 'official_cloud' ? '#2563EB' : '#E2E8F0'; ?>;background:<?php echo $gateway_mode === 'official_cloud' ? '#EFF6FF' : '#F8FAFC'; ?>;border-radius:14px;padding:16px;transition:all 0.2s ease;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-weight:800;font-size:0.95rem;color:#0F172A;">🌐 الربط السحابي المعتمد (Official Meta Cloud API)</span>
                <span class="rsd-badge" style="background:#2563EB;color:#FFFFFF;font-size:0.75rem;padding:2px 8px;">موصى به للأعمال والفنادق</span>
            </div>
            <p style="margin:0;font-size:0.82rem;color:#64748B;line-height:1.5;">
                اتصال رسمي ومباشر عبر سحابة Meta Graph API. استقرار تجاري دائم بنسبة 100% مع ضمان عدم انقطاع الخدمة أو قيود الأرقام.
            </p>
        </div>

        <div onclick="rsdSelectGatewayEngine('local_bridge')" id="cardEngineLocal" style="cursor:pointer;border:2px solid <?php echo $gateway_mode === 'local_bridge' ? '#2563EB' : '#E2E8F0'; ?>;background:<?php echo $gateway_mode === 'local_bridge' ? '#EFF6FF' : '#F8FAFC'; ?>;border-radius:14px;padding:16px;transition:all 0.2s ease;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-weight:800;font-size:0.95rem;color:#0F172A;">⚡ الربط المباشر السريع (Local QR/Socket Bridge)</span>
                <span class="rsd-badge" style="background:#F59E0B;color:#FFFFFF;font-size:0.75rem;padding:2px 8px;">تشغيل فوري تجريبي</span>
            </div>
            <p style="margin:0;font-size:0.82rem;color:#64748B;line-height:1.5;">
                اقتران سريع برقم الهاتف أو مسح رمز الـ QR عبر خادم Socket/Baileys دون الحاجة لحساب أعمال موثق.
            </p>
        </div>
    </div>
</div>

<form method="POST">
    <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
    <input type="hidden" name="active_tab" value="crm">
    <input type="hidden" name="rsd_whatsapp_gateway_mode" id="rsdGatewayModeInput" value="<?php echo esc_attr($gateway_mode); ?>">

    <!-- SECTION 1: META CLOUD API SETTINGS (OFFICIAL ENGINE) -->
    <div id="rsdEngineMetaContainer" style="display:<?php echo $gateway_mode === 'official_cloud' ? 'block' : 'none'; ?>;margin-bottom:24px;">
        <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:24px;">
            <!-- CREDENTIALS CARD -->
            <div class="rsd-card" style="margin-bottom:0;">
                <div class="rsd-card-header">
                    <h3 class="rsd-card-title">🔐 بيانات الاعتماد السحابية (Meta Developers)</h3>
                    <span class="rsd-badge rsd-badge-success">Official Graph API v19.0</span>
                </div>

                <div class="rsd-form-group">
                    <label class="rsd-label">معرف التطبيق (Meta App ID)</label>
                    <input type="text" name="rsd_meta_app_id" class="rsd-input" value="<?php echo esc_attr($meta_app_id); ?>" placeholder="مثال: 108472918392817">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="rsd-form-group">
                        <label class="rsd-label">معرف رقم الهاتف (Phone Number ID) <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="rsd_meta_phone_id" class="rsd-input" value="<?php echo esc_attr($meta_phone_id); ?>" placeholder="مثال: 10482910482910">
                    </div>
                    <div class="rsd-form-group">
                        <label class="rsd-label">معرف حساب الأعمال (WABA ID)</label>
                        <input type="text" name="rsd_meta_waba_id" class="rsd-input" value="<?php echo esc_attr($meta_waba_id); ?>" placeholder="مثال: 29104829104829">
                    </div>
                </div>

                <div class="rsd-form-group">
                    <label class="rsd-label">رمز الوصول الدائم (System User Access Token) <span style="color:#DC2626;">*</span></label>
                    <input type="password" name="rsd_meta_access_token" class="rsd-input" value="<?php echo esc_attr($meta_access_token); ?>" placeholder="EAAG...">
                </div>

                <div class="rsd-form-group">
                    <label class="rsd-label">رمز التحقق السري للـ Webhook (Verify Token)</label>
                    <div style="display:flex;gap:8px;">
                        <input type="text" name="rsd_meta_webhook_verify_token" id="rsdMetaVerifyTokenInput" class="rsd-input" value="<?php echo esc_attr($meta_verify_token); ?>" style="font-family:monospace;">
                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('rsdMetaVerifyTokenInput').value);alert('تم نسخ الـ Verify Token!');" class="rsd-btn rsd-btn-secondary" style="white-space:nowrap;">📋 نسخ</button>
                    </div>
                </div>
            </div>

            <!-- WEBHOOK GUIDE CARD -->
            <div class="rsd-card" style="margin-bottom:0;background:#F8FAFC;border:1px solid #E2E8F0;">
                <div class="rsd-card-header">
                    <h3 class="rsd-card-title">🔗 إعدادات الـ Webhook في Meta</h3>
                </div>

                <div class="rsd-form-group">
                    <label class="rsd-label">رابط استدعاء الـ Callback URL لنسخه في Meta Portal:</label>
                    <div style="display:flex;gap:8px;">
                        <input type="text" id="rsdMetaWebhookUrl" readonly class="rsd-input" value="<?php echo esc_attr($webhook_url); ?>" style="background:#FFFFFF;font-family:monospace;direction:ltr;font-size:0.82rem;">
                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('rsdMetaWebhookUrl').value);alert('تم نسخ رابط الـ Webhook!');" class="rsd-btn rsd-btn-secondary" style="white-space:nowrap;">📋 نسخ</button>
                    </div>
                </div>

                <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:12px;padding:14px;font-size:0.84rem;color:#1E40AF;line-height:1.6;">
                    <strong>📌 خطوات تفعيل الـ Webhook السحابي:</strong>
                    <ol style="margin:8px 0 0 16px;padding:0;">
                        <li>افتح تطبيقك في <strong>Meta for Developers</strong> وانتقل إلى WhatsApp ➔ Configuration.</li>
                        <li>الصق <strong>Callback URL</strong> و <strong>Verify Token</strong> واضغط Verify and Save.</li>
                        <li>اشترك في حقل <strong>messages</strong> لاستقبال رسائل العملاء فورياً عبر الأوركسترا.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: LOCAL SOCKET / QR BRIDGE SETTINGS (DIRECT ENGINE) -->
    <div id="rsdEngineLocalContainer" style="display:<?php echo $gateway_mode === 'local_bridge' ? 'block' : 'none'; ?>;margin-bottom:24px;">
        
        <!-- OPERATIONAL DISCLAIMER ALERT -->
        <div style="background:#FFFBEB;border:1px solid #FCD34D;border-radius:14px;padding:16px;margin-bottom:20px;display:flex;align-items:flex-start;gap:14px;">
            <span style="font-size:1.6rem;line-height:1;">⚠️</span>
            <div>
                <strong style="color:#92400E;font-size:0.92rem;display:block;margin-bottom:4px;">تنبيه تشغيلي وإخلاء مسؤولية قانوني:</strong>
                <p style="margin:0;color:#B45309;font-size:0.84rem;line-height:1.5;">
                    يعتمد هذا النمط على جلسة اتصال محاكاة غير رسمية (Web Socket Simulation). المنشأة مسؤولة بالكامل عن استقرار الاتصال أو احتمالية قيود الأرقام من قبل مزود الخدمة. للاستقرار التجاري المستدام للمنشآت الكبرى والفنادق، نوصي دائماً بالاعتماد على <strong>الربط السحابي المعتمد (Meta Cloud API)</strong>.
                </p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1.1fr 1fr;gap:24px;">
            <!-- PAIRING CARD -->
            <div class="rsd-card" style="margin-bottom:0;">
                <div class="rsd-card-header">
                    <h3 class="rsd-card-title">📱 اتصال بوابة السوكيت المباشرة</h3>
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

            <!-- SOCKET SETTINGS CARD -->
            <div class="rsd-card" style="margin-bottom:0;">
                <div class="rsd-card-header">
                    <h3 class="rsd-card-title">⚙️ إعدادات خادم السوكيت</h3>
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
                    <label class="rsd-label">رابط خادم السوكيت / البوابة</label>
                    <input type="text" name="rsd_whatsapp_api_url" class="rsd-input" value="<?php echo esc_attr($wa_api_url); ?>" placeholder="https://api.your-gateway.com">
                </div>

                <div class="rsd-form-group">
                    <label class="rsd-label">مفتاح الأمان (API Key)</label>
                    <input type="password" name="rsd_whatsapp_api_key" class="rsd-input" value="<?php echo esc_attr($wa_api_key); ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- GLOBAL SAVE BUTTON -->
    <div style="margin-bottom:24px;display:flex;justify-content:flex-end;">
        <button type="submit" name="rsd_save_settings" class="rsd-btn" style="padding:10px 24px;font-size:0.92rem;">💾 حفظ كافة إعدادات البوابة</button>
    </div>
</form>

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
    function rsdSelectGatewayEngine(engine) {
        document.getElementById('rsdGatewayModeInput').value = engine;
        
        var cardMeta = document.getElementById('cardEngineMeta');
        var cardLocal = document.getElementById('cardEngineLocal');
        var contMeta = document.getElementById('rsdEngineMetaContainer');
        var contLocal = document.getElementById('rsdEngineLocalContainer');

        if (engine === 'official_cloud') {
            cardMeta.style.borderColor = '#2563EB';
            cardMeta.style.background = '#EFF6FF';
            cardLocal.style.borderColor = '#E2E8F0';
            cardLocal.style.background = '#F8FAFC';
            contMeta.style.display = 'block';
            contLocal.style.display = 'none';
        } else {
            cardLocal.style.borderColor = '#2563EB';
            cardLocal.style.background = '#EFF6FF';
            cardMeta.style.borderColor = '#E2E8F0';
            cardMeta.style.background = '#F8FAFC';
            contLocal.style.display = 'block';
            contMeta.style.display = 'none';
        }
    }

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
