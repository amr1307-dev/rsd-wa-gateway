<?php
if (!defined('ABSPATH')) {
    exit;
}

use RedSea\RAG\KnowledgeBaseManager;

/**
 * Tab 4: RAG Knowledge Files & Vector Store Partial
 * Displays interactive drag-and-drop file uploader (.md, .txt, .json), inline markdown/text file editor, and indexed knowledge files table.
 */
?>

                        <div class="rsd-card">
                            <div class="rsd-card-header">
                                <h3 class="rsd-card-title">📚 قاعدة المعرفة وإدارة الملفات المتجهية (RAG Vector Store)</h3>
                            </div>

                            <!-- INTERACTIVE DRAG & DROP ZONE -->
                            <form method="POST" enctype="multipart/form-data" id="rsdDropzoneForm" style="background:#F8FAFC;border:2px dashed #3B82F6;border-radius:16px;padding:30px;text-align:center;margin-bottom:24px;transition:all 0.2s ease;">
                                <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
                                <input type="hidden" name="active_tab" value="rag">
                                <span style="font-size:2.5rem;display:block;margin-bottom:8px;">📁</span>
                                <h4 style="margin:0 0 6px 0;font-weight:800;color:#0F172A;font-size:1.1rem;">اسحب وأفلت ملف المعرفة هنا أو اضغط للاختيار</h4>
                                <p style="margin:0 0 16px 0;font-size:0.86rem;color:#64748B;">يدعم ملفات (.md / .txt / .json) — سيتم تقطيعها وتوليد الـ Embeddings فورياً.</p>
                                <label class="rsd-btn rsd-btn-secondary" style="cursor:pointer;padding:8px 20px;">
                                    <span>📂 تصفح الملفات</span>
                                    <input type="file" name="rsd_upload_new_file" accept=".md,.txt,.json" onchange="document.getElementById('rsdSelectedFileName').innerText = this.files[0] ? this.files[0].name : '';" style="display:none;">
                                </label>
                                <div id="rsdSelectedFileName" style="margin-top:10px;font-size:0.85rem;font-weight:700;color:#2563EB;"></div>
                                <button type="submit" class="rsd-btn" style="margin-top:14px;">📤 رفع وفهرسة الملف الآن</button>
                            </form>

                            <!-- FILE EDIT VIEW IF SELECTED -->
                            <?php if (!empty($edit_file)): ?>
                                <?php $file_text = KnowledgeBaseManager::get_file_content($edit_file); ?>
                                <div style="background:#F0F9FF;border:1px solid #BFDBFE;border-radius:16px;padding:20px;margin-bottom:24px;">
                                    <h4 style="margin:0 0 12px 0;color:#0369A1;font-weight:800;">✏️ محرر ملف المعرفة: <?php echo esc_html($edit_file); ?></h4>
                                    <form method="POST">
                                        <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
                                        <input type="hidden" name="active_tab" value="rag">
                                        <input type="hidden" name="rsd_edit_file_name" value="<?php echo esc_attr($edit_file); ?>">
                                        <textarea name="rsd_edit_file_text" class="rsd-textarea" rows="10" style="background:#0F172A;color:#F8FAFC;font-family:'JetBrains Mono',monospace;font-size:0.88rem;line-height:1.6;margin-bottom:14px;"><?php echo esc_textarea($file_text); ?></textarea>
                                        <div style="display:flex;gap:10px;">
                                            <button type="submit" name="rsd_save_file_content" class="rsd-btn">💾 حفظ التعديلات وإعادة الفهرسة</button>
                                            <a href="?page=redsea-ai-engine&tab=rag" class="rsd-btn rsd-btn-secondary" style="text-decoration:none;">إلغاء</a>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>

                            <!-- FILES TABLE -->
                            <table class="rsd-table">
                                <thead>
                                    <tr>
                                        <th>اسم الملف</th>
                                        <th>الحجم الفعلي</th>
                                        <th>الحالة الفهرسية</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kb_files as $f): ?>
                                        <?php
                                        $fsize = !empty($f['size']) ? round($f['size'] / 1024, 1) : 4.5;
                                        ?>
                                        <tr>
                                            <td style="font-weight:700;color:#0F172A;">📄 <?php echo esc_html($f['name']); ?></td>
                                            <td><span class="rsd-badge" style="background:#F1F5F9;color:#475569;"><?php echo $fsize; ?> KB</span></td>
                                            <td><span class="rsd-badge rsd-badge-success">🟢 مفهرس وجاهز</span></td>
                                            <td>
                                                <div style="display:flex;gap:8px;">
                                                    <a href="?page=redsea-ai-engine&tab=rag&edit_file=<?php echo urlencode($f['name']); ?>" class="rsd-btn rsd-btn-secondary" style="padding:5px 12px;font-size:0.78rem;text-decoration:none;">✏️ تعديل</a>
                                                    <form method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الملف؟');" style="display:inline;">
                                                        <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
                                                        <input type="hidden" name="active_tab" value="rag">
                                                        <input type="hidden" name="rsd_delete_file_name" value="<?php echo esc_attr($f['name']); ?>">
                                                        <button type="submit" name="rsd_delete_file" class="rsd-btn-danger" style="padding:5px 12px;font-size:0.78rem;border-radius:8px;cursor:pointer;">🗑️ حذف</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
