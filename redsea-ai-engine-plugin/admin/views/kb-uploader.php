<?php
/**
 * Red Sea AI Suite - Knowledge Base & Dynamic Booking Feeder View (Tab 5)
 */
if (!defined('ABSPATH')) exit;
?>
<div class="rsd-card">
    <h3>📚 5. قاعدة المعرفة والسياق المباشر (RAG Knowledge Base & Dynamic Feeder)</h3>
    <div class="rsd-field-group">
        <label><input type="checkbox" name="rsd_kb_enabled" value="1" <?php checked(get_option('rsd_kb_enabled', '1'), '1'); ?> /> تفعيل نظام حقن المعرفة (RAG Mode)</label>
    </div>

    <!-- 🏨 🌴 DYNAMIC HOTEL, TRAVEL & WOOCOMMERCE INTEGRATION UI -->
    <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color:#fff; padding:22px; border-radius:16px; margin-top:20px; border:1px solid #38bdf8;">
        <h4 style="margin:0 0 12px 0; color:#38bdf8; font-size:1.1rem; display:flex; align-items:center; gap:8px;">
            🏨 🌴 التكامل الديناميكي مع إضافات الرحلات والفنادق والمتاجر (Dynamic Booking RAG Integration)
        </h4>
        <p style="color:#cbd5e1; font-size:0.88rem; margin-bottom:16px;">قراءة تلقائية مباشرة لكافة برامج الرحلات ورسومات الغرف الفندقية ومنتجات المتجر وتغذية الأيجنت بها لحظياً مع إنشاء روابط الدفع المباشرة.</p>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
            <div style="background:#0f172a; padding:14px; border-radius:10px; border:1px solid #334155;">
                <label style="color:#fff; font-size:0.9rem;"><input type="checkbox" name="rsd_feed_travel_enabled" value="1" <?php checked(get_option('rsd_feed_travel_enabled', '1'), '1'); ?> /> 🌴 <strong>سحب برامج الرحلات (WP Travel)</strong></label>
            </div>
            <div style="background:#0f172a; padding:14px; border-radius:10px; border:1px solid #334155;">
                <label style="color:#fff; font-size:0.9rem;"><input type="checkbox" name="rsd_feed_hotel_enabled" value="1" <?php checked(get_option('rsd_feed_hotel_enabled', '1'), '1'); ?> /> 🏨 <strong>سحب الغرف والأسعار (Hotel Booking)</strong></label>
            </div>
            <div style="background:#0f172a; padding:14px; border-radius:10px; border:1px solid #334155;">
                <label style="color:#fff; font-size:0.9rem;"><input type="checkbox" name="rsd_feed_wc_enabled" value="1" <?php checked(get_option('rsd_feed_wc_enabled', '1'), '1'); ?> /> 🛍️ <strong>سحب المتاجر (WooCommerce Products)</strong></label>
            </div>
        </div>
    </div>
</div>
