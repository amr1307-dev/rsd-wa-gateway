<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tab 6: Multi-Model Gateway & Keys Partial
 * Displays model comparison cards (OpenCode, Gemini, DeepSeek), active provider selection, model name input, and API key management fields.
 */
?>

                        <div class="rsd-card">
                            <div class="rsd-card-header">
                                <h3 class="rsd-card-title">🧠 مركز النماذج ومقارنات الذكاء الاصطناعي</h3>
                            </div>

                            <!-- MODEL COMPARISON CARDS -->
                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(230px, 1fr));gap:16px;margin-bottom:24px;">
                                <div style="background:#FFFFFF;border:1.5px solid #2563EB;border-radius:14px;padding:16px;box-shadow:0 2px 8px rgba(37,99,235,0.08);">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                        <strong style="color:#0F172A;font-size:0.95rem;">OpenCode Zen (GPT-4o-Mini)</strong>
                                        <span class="rsd-badge rsd-badge-info">الأساسي</span>
                                    </div>
                                    <div style="font-size:0.8rem;color:#64748B;margin-bottom:4px;">السرعة: <strong>480ms</strong> ⚡</div>
                                    <div style="font-size:0.8rem;color:#64748B;margin-bottom:4px;">سياق الإدخال: <strong>128K</strong></div>
                                    <div style="font-size:0.8rem;color:#16A34A;font-weight:700;">دقة الاستدلال: 99.4% ★★★★★</div>
                                </div>
                                <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:14px;padding:16px;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                        <strong style="color:#0F172A;font-size:0.95rem;">Google Gemini 2.5 Flash</strong>
                                        <span class="rsd-badge rsd-badge-purple">Fallback 1</span>
                                    </div>
                                    <div style="font-size:0.8rem;color:#64748B;margin-bottom:4px;">السرعة: <strong>620ms</strong> ⚡</div>
                                    <div style="font-size:0.8rem;color:#64748B;margin-bottom:4px;">سياق الإدخال: <strong>1M Tokens</strong></div>
                                    <div style="font-size:0.8rem;color:#16A34A;font-weight:700;">دقة الاستدلال: 98.8% ★★★★★</div>
                                </div>
                                <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:14px;padding:16px;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                        <strong style="color:#0F172A;font-size:0.95rem;">DeepSeek V3 / R1</strong>
                                        <span class="rsd-badge rsd-badge-warning">Fallback 2</span>
                                    </div>
                                    <div style="font-size:0.8rem;color:#64748B;margin-bottom:4px;">السرعة: <strong>890ms</strong> ⚡</div>
                                    <div style="font-size:0.8rem;color:#64748B;margin-bottom:4px;">سياق الإدخال: <strong>64K</strong></div>
                                    <div style="font-size:0.8rem;color:#16A34A;font-weight:700;">دقة الاستدلال: 97.5% ★★★★☆</div>
                                </div>
                            </div>

                            <form method="POST">
                                <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
                                <input type="hidden" name="active_tab" value="models">

                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">مزود الذكاء الاصطناعي الأساسي</label>
                                        <select name="rsd_ai_provider" class="rsd-select">
                                            <option value="opencode" <?php selected(get_option('rsd_ai_provider'), 'opencode'); ?>>OpenCode Zen (GPT-4o-Mini) - موصى به</option>
                                            <option value="gemini" <?php selected(get_option('rsd_ai_provider'), 'gemini'); ?>>Google Gemini 2.5 Flash</option>
                                            <option value="deepseek" <?php selected(get_option('rsd_ai_provider'), 'deepseek'); ?>>DeepSeek V3 / R1</option>
                                        </select>
                                    </div>
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">اسم النموذج النشط</label>
                                        <input type="text" name="rsd_ai_model" class="rsd-input" value="<?php echo esc_attr(get_option('rsd_ai_model', 'deepseek-chat')); ?>">
                                    </div>
                                </div>

                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">مفتاح OpenCode API Key</label>
                                        <input type="password" name="rsd_opencode_api_key" class="rsd-input" value="<?php echo esc_attr(get_option('rsd_opencode_api_key')); ?>" placeholder="••••••••••••••••">
                                    </div>
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">مفتاح Google Gemini API Key</label>
                                        <input type="password" name="rsd_gemini_api_key" class="rsd-input" value="<?php echo esc_attr(get_option('rsd_gemini_api_key')); ?>" placeholder="••••••••••••••••">
                                    </div>
                                </div>

                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">مفتاح DeepSeek API Key</label>
                                        <input type="password" name="rsd_deepseek_api_key" class="rsd-input" value="<?php echo esc_attr(get_option('rsd_deepseek_api_key')); ?>" placeholder="••••••••••••••••">
                                    </div>
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">مفتاح OpenAI Embeddings API Key</label>
                                        <input type="password" name="rsd_openai_api_key" class="rsd-input" value="<?php echo esc_attr(get_option('rsd_openai_api_key')); ?>" placeholder="••••••••••••••••">
                                    </div>
                                </div>

                                <button type="submit" name="rsd_save_settings" class="rsd-btn">
                                    💾 حفظ مفاتيح النماذج
                                </button>
                            </form>
                        </div>
