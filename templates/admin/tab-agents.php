<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tab 2: Agents Forge / Multi-Agent Hierarchy Partial
 * Displays custom agent creator, agent card grid, core/custom badges, and assigned tool tags.
 */
?>

                        <div class="rsd-card">
                            <div class="rsd-card-header">
                                <h3 class="rsd-card-title">🤖 مصنع الوكلاء الذكية (Multi-Agent Forge)</h3>
                            </div>

                            <form method="POST" style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:16px;padding:22px;margin-bottom:24px;">
                                <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
                                <input type="hidden" name="active_tab" value="agents">
                                
                                <h4 style="margin:0 0 14px 0;color:#0F172A;font-size:1rem;font-weight:800;">➕ إنشاء وتدريب وكيل مخصص جديد بالذكاء الاصطناعي</h4>
                                
                                <div style="display:grid;grid-template-columns:1fr 2fr auto;gap:14px;align-items:flex-end;">
                                    <div>
                                        <label class="rsd-label">اسم الوكيل المخصص</label>
                                        <input type="text" name="rsd_new_agent_name" class="rsd-input" placeholder="مثال: وكيل ترقية الغرف الفاخرة" required>
                                    </div>
                                    <div>
                                        <label class="rsd-label">مهمة الوكيل وأهدافه الاستشارية</label>
                                        <input type="text" name="rsd_new_agent_mission" class="rsd-input" placeholder="مثال: إقناع العملاء بالترقية للأجنحة الملكية وتقديم عروض رحلات اليخوت" required>
                                    </div>
                                    <div>
                                        <button type="submit" name="rsd_create_custom_agent" class="rsd-btn" style="white-space:nowrap;padding:11px 22px;">
                                            🚀 إنشاء وتوليد البرومبت
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- AGENTS CARDS GRID -->
                            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(310px, 1fr));gap:18px;">
                                <?php foreach ($all_agents as $a_id => $agent): ?>
                                    <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;">
                                        <div>
                                            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                                                <div>
                                                    <h4 style="margin:0 0 4px 0;font-size:1.05rem;font-weight:800;color:#0F172A;"><?php echo esc_html($agent['name']); ?></h4>
                                                    <span class="rsd-badge <?php echo !empty($agent['is_core']) ? 'rsd-badge-purple' : 'rsd-badge-info'; ?>">
                                                        <?php echo !empty($agent['is_core']) ? '🌟 وكيل نظام أساسي' : '✨ وكيل مخصص'; ?>
                                                    </span>
                                                </div>
                                                <?php if (empty($agent['is_core'])): ?>
                                                    <form method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الوكيل؟');">
                                                        <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
                                                        <input type="hidden" name="active_tab" value="agents">
                                                        <input type="hidden" name="rsd_delete_agent_id" value="<?php echo esc_attr($a_id); ?>">
                                                        <button type="submit" name="rsd_delete_custom_agent" class="rsd-btn-danger" style="padding:4px 10px;border-radius:8px;cursor:pointer;font-size:0.78rem;">🗑️ حذف</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                            <p style="font-size:0.86rem;color:#64748B;line-height:1.5;margin:0 0 16px 0;"><?php echo esc_html($agent['mission']); ?></p>
                                        </div>
                                        <div style="border-top:1px solid #F1F5F9;padding-top:12px;">
                                            <div style="font-size:0.78rem;font-weight:700;color:#475569;margin-bottom:6px;">الأدوات المفعلة للوكيل:</div>
                                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                                <?php foreach (($agent['tools'] ?? ['rag_search']) as $tool): ?>
                                                    <span class="rsd-badge rsd-badge-success" style="font-size:0.75rem;">⚙️ <?php echo esc_html($tool); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
