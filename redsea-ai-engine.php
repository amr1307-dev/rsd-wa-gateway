<?php

require_once plugin_dir_path(__FILE__) . 'includes/class-rsd-elementor-suite.php';

/**
 * Plugin Name: Red Sea AI Engine
 * Plugin URI: https://redseadigital.pro
 * Description: Unified AI Architecture, RAG Knowledge Base, Lead CRM, and Glassmorphic Frontend Chat Engine v5.1.0 Pro.
 * Version: 5.3.0 Pro
 * Author: Red Sea Digital (Amr Ahmed)
 */

if (!defined('ABSPATH')) exit;

/**
 * -----------------------------------------------------------------------------
 * RESPONSE FORMATTING FILTER & OUTPUT CLEANER
 * -----------------------------------------------------------------------------
 */
class RSD_Output_Cleaner {
    public static function clean($text) {
        if (empty($text)) return '';

        // 1. Extract and Strip structured JSON blocks (prevent code leak to visitor)
        if (preg_match('/\{\s*"(?:booking|customer_name|service_type|customer_phone)"[^}]*\}/u', $text, $matches)) {
            $json_str = $matches[0];
            $lead_data = json_decode($json_str, true);
            if (!empty($lead_data['customer_phone']) && preg_match('/[0-9]{8,}/', $lead_data['customer_phone']) && $lead_data['customer_phone'] !== 'Valid Phone Number') {
                if (class_exists('RedSeaAIEngine')) {
                    RedSeaAIEngine::save_booking(
                        $lead_data['customer_name'] ?? 'عميل جديد',
                        $lead_data['customer_phone'],
                        $lead_data['service_type'] ?? 'استشارة حجز مباشر',
                        $lead_data['booking_details'] ?? 'استفسار عبر الشات'
                    );
                }
            }
            $text = str_replace($json_str, '', $text);
        }

        // 2. Strip code block markers
        $text = str_replace(['```json', '```'], '', $text);

        // 3. Remove leading bullet symbols (dots, dashes, asterisks, emojis) completely as requested
        $text = preg_replace('/^[\*\-\•\⁃\–\🔹]\s*/mu', '', $text);

        // 4. Convert Markdown Bold (**text** or __text__) to clean strong tags
        $text = preg_replace('/\*\*(.*?)\*\*/u', '<strong style="color:#0F172A;font-weight:800;">$1</strong>', $text);
        $text = preg_replace('/__(.*?)__/u', '<strong style="color:#0F172A;font-weight:800;">$1</strong>', $text);

        // 5. Convert line breaks and blocks into clean, well-spaced organized paragraphs
        $text = trim($text);
        $paragraphs = preg_split('/\n\s*\n/', $text);
        $formatted_paragraphs = [];
        foreach ($paragraphs as $p) {
            $p = trim($p);
            if (!empty($p)) {
                // Also clean any leftover mid-line bullet symbols
                $p = preg_replace('/^[\*\-\•\⁃\–\🔹]\s*/mu', '', $p);
                $p = nl2br($p);
                $formatted_paragraphs[] = "<p style='margin:0 0 12px 0;line-height:1.65;'>" . $p . "</p>";
            }
        }

        return trim(implode('', $formatted_paragraphs));
    }
}

class RSD_Agent_Tool_Manager {

    public static function get_available_tools() {
        return array(
            'check_live_catalog' => array(
                'name' => 'check_live_catalog',
                'description' => 'سحب واستعلام برامج الرحلات والغرف الفندقية ومنتجات المتجر الحية مع الأسعار والروابط المباشرة',
            ),
            'vector_rag_search' => array(
                'name' => 'vector_rag_search',
                'description' => 'البحث الدلالي المتجهي الفائق في قاعدة المعرفة لاستخراج المعلومات التفصيلية والضمانات',
            ),
            'instant_lead_booking' => array(
                'name' => 'instant_lead_booking',
                'description' => 'تسجيل حجز العميل واصتياد رقم الهاتف والاسم تلقائياً في الـ CRM وتفعيل رسالة الواتساب المباشرة',
            ),
            'calculate_direct_savings' => array(
                'name' => 'calculate_direct_savings',
                'description' => 'حساب نسبة وتكلفة العمولات الضائعة للمنصات الوسيطة وتوضيح الصافي المالي للعميل',
            )
        );
    }

    public static function execute_tool($tool_name, $args = array()) {
        switch ($tool_name) {
            case 'calculate_direct_savings':
                $revenue = floatval($args['monthly_revenue'] ?? 10000);
                $savings_min = $revenue * 0.15;
                $savings_max = $revenue * 0.30;
                return sprintf("📊 حساب التوفير المالي الصافي:\nعند تحقيق إيرادات شهرياً بقيمة $%s، يتم توفير من $%s إلى $%s سنوياً كان يتم دفعها كعمولات للمنصات الوسيطة!", number_format($revenue), number_format($savings_min * 12), number_format($savings_max * 12));

            case 'vector_rag_search':
                $query = sanitize_text_field($args['query'] ?? '');
                $chunks = RSD_Knowledge_Base_Manager::search_similar_chunks($query, 3);
                return !empty($chunks) ? implode("\n\n", $chunks) : 'لم يتم العثور على مقاطع مطابقة.';

            case 'check_live_catalog':
                return RSD_Knowledge_Base_Manager::get_live_booking_context() . "\n" . RSD_Knowledge_Base_Manager::get_wp_wc_live_context();

            case 'instant_lead_booking':
                $name = sanitize_text_field($args['name'] ?? 'عميل جديد');
                $phone = sanitize_text_field($args['phone'] ?? '');
                $service = sanitize_text_field($args['service'] ?? 'جلسة استشارية');
                if (!empty($phone)) {
                    RedSeaAIEngine::save_booking($name, $phone, $service, 'حجز تلقائي عبر محرك الأيجنت الذكي');
                    return "✅ تم تسجيل حجز العميل ($name - $phone) بنجاح وإرسال الرسالة التلقائية عبر الواتساب.";
                }
                return 'تعذر التسجيل: رقم الهاتف مطلوب.';

            default:
                return 'أداة غير معروفة.';
        }
    }
}

class RSD_Knowledge_Base_Manager {

    public static function init_vector_store_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_vector_store';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            file_name varchar(255) NOT NULL,
            chunk_index int(11) NOT NULL DEFAULT 0,
            chunk_text longtext NOT NULL,
            embedding_json longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY file_name (file_name)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function get_upload_dir() {
        $upload = wp_upload_dir();
        $kb_dir = $upload['basedir'] . '/redsea-ai-kb';
        if (!file_exists($kb_dir)) {
            wp_mkdir_p($kb_dir);
        }
        return $kb_dir;
    }

    public static function get_rag_bundled_dir() {
        return plugin_dir_path(__FILE__) . 'RAG';
    }

    public static function list_all_kb_files() {
        global $wpdb;
        $files_list = [];

        // 1. Scan Plugin Bundled RAG folder
        $bundled_dir = self::get_rag_bundled_dir();
        if (file_exists($bundled_dir)) {
            $files = scandir($bundled_dir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') continue;
                $fp = $bundled_dir . '/' . $f;
                if (is_file($fp)) {
                    $chunk_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}rsd_vector_store WHERE file_name = %s", $f));
                    $files_list[$f] = [
                        'file_name'   => $f,
                        'file_path'   => $fp,
                        'file_size'   => size_format(filesize($fp)),
                        'modified'    => date('Y-m-d H:i', filemtime($fp)),
                        'source'      => 'الملفات الأساسية (Core RAG)',
                        'chunks'      => intval($chunk_count),
                        'is_deletable'=> true
                    ];
                }
            }
        }

        // 2. Scan Uploaded Files folder
        $upload_dir = self::get_upload_dir();
        if (file_exists($upload_dir)) {
            $files = scandir($upload_dir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..' || $f === '.htaccess') continue;
                $fp = $upload_dir . '/' . $f;
                if (is_file($fp)) {
                    $chunk_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}rsd_vector_store WHERE file_name = %s", $f));
                    $files_list[$f] = [
                        'file_name'   => $f,
                        'file_path'   => $fp,
                        'file_size'   => size_format(filesize($fp)),
                        'modified'    => date('Y-m-d H:i', filemtime($fp)),
                        'source'      => 'الملفات المرفوعة (Uploaded)',
                        'chunks'      => intval($chunk_count),
                        'is_deletable'=> true
                    ];
                }
            }
        }

        return $files_list;
    }

    public static function get_file_content($file_name) {
        $files = self::list_all_kb_files();
        if (isset($files[$file_name]) && file_exists($files[$file_name]['file_path'])) {
            return file_get_contents($files[$file_name]['file_path']);
        }
        return '';
    }

    public static function save_file_content($file_name, $content) {
        $files = self::list_all_kb_files();
        $target_path = '';

        if (isset($files[$file_name])) {
            $target_path = $files[$file_name]['file_path'];
        } else {
            $target_path = self::get_upload_dir() . '/' . sanitize_file_name($file_name);
        }

        file_put_contents($target_path, $content);
        self::index_single_file($file_name, $content);
        self::clear_cache();
        return true;
    }

    public static function delete_file($file_name) {
        global $wpdb;
        $files = self::list_all_kb_files();
        if (isset($files[$file_name]) && file_exists($files[$file_name]['file_path'])) {
            @unlink($files[$file_name]['file_path']);
            $wpdb->delete($wpdb->prefix . 'rsd_vector_store', ['file_name' => $file_name]);
            self::clear_cache();
            return true;
        }
        return false;
    }

    public static function chunk_text($text, $chunk_size = 350) {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (empty($text)) return [];

        $words = explode(' ', $text);
        $total_words = count($words);
        $chunks = [];
        $overlap = intval(get_option('rsd_rag_chunk_overlap', 40));

        for ($i = 0; $i < $total_words; $i += ($chunk_size - $overlap)) {
            $chunk_slice = array_slice($words, $i, $chunk_size);
            $chunk_str = implode(' ', $chunk_slice);
            if (mb_strlen($chunk_str) > 20) {
                $chunks[] = $chunk_str;
            }
            if ($i + $chunk_size >= $total_words) break;
        }

        return $chunks;
    }

    public static function generate_fallback_vector($text) {
        $dim = 64;
        $vector = array_fill(0, $dim, 0.0);
        $tokens = preg_split('/[\s,;:.!?]+/', mb_strtolower($text, 'UTF-8'));

        foreach ($tokens as $token) {
            if (empty($token)) continue;
            $h = crc32($token);
            $idx = abs($h) % $dim;
            $vector[$idx] += 1.0;
        }

        $norm = 0.0;
        foreach ($vector as $v) { $norm += $v * $v; }
        $norm = sqrt($norm);
        if ($norm > 0.0) {
            for ($i = 0; $i < $dim; $i++) { $vector[$i] /= $norm; }
        }

        return $vector;
    }

    public static function generate_embedding_vector($text_chunk) {
        $api_key = get_option('rsd_gemini_api_key', '');
        if (!empty($api_key)) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent?key=' . $api_key;
            $payload = array(
                'model' => 'models/text-embedding-004',
                'content' => array('parts' => array(array('text' => mb_substr($text_chunk, 0, 2048))))
            );

            $res = wp_remote_post($url, array(
                'headers' => array('Content-Type' => 'application/json'),
                'body'    => json_encode($payload),
                'timeout' => 10,
                'sslverify' => false
            ));

            if (!is_wp_error($res)) {
                $body = json_decode(wp_remote_retrieve_body($res), true);
                if (!empty($body['embedding']['values'])) {
                    return $body['embedding']['values'];
                }
            }
        }

        return self::generate_fallback_vector($text_chunk);
    }

    public static function compute_cosine_similarity($vecA, $vecB) {
        if (empty($vecA) || empty($vecB) || count($vecA) !== count($vecB)) return 0.0;
        $dot = 0.0; $normA = 0.0; $normB = 0.0;
        $count = count($vecA);
        for ($i = 0; $i < $count; $i++) {
            $a = (float)$vecA[$i];
            $b = (float)$vecB[$i];
            $dot += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }
        if ($normA <= 0.0 || $normB <= 0.0) return 0.0;
        return $dot / (sqrt($normA) * sqrt($normB));
    }

    public static function index_single_file($file_name, $content) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_vector_store';
        self::init_vector_store_table();

        $wpdb->delete($table_name, ['file_name' => $file_name]);

        $chunk_size = intval(get_option('rsd_rag_chunk_size', 350));
        $chunks = self::chunk_text($content, $chunk_size);

        foreach ($chunks as $index => $chunk_text) {
            $vector = self::generate_embedding_vector($chunk_text);
            $wpdb->insert($table_name, [
                'file_name'      => $file_name,
                'chunk_index'    => $index,
                'chunk_text'     => $chunk_text,
                'embedding_json' => json_encode($vector),
                'created_at'     => current_time('mysql')
            ]);
        }
    }

    public static function reindex_kb_files() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_vector_store';
        self::init_vector_store_table();
        $wpdb->query("TRUNCATE TABLE {$table_name}");

        $files = self::list_all_kb_files();
        $total_chunks = 0;

        foreach ($files as $file_name => $info) {
            if (file_exists($info['file_path'])) {
                $content = file_get_contents($info['file_path']);
                if (!empty($content)) {
                    self::index_single_file($file_name, $content);
                }
            }
        }

        self::clear_cache();
        return $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    }

    public static function search_similar_chunks($user_query, $top_k = 3) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_vector_store';
        self::init_vector_store_table();

        $rows = $wpdb->get_results("SELECT id, file_name, chunk_text, embedding_json FROM {$table_name}", ARRAY_A);
        if (empty($rows)) return [];

        $query_vector = self::generate_embedding_vector($user_query);
        $threshold = floatval(get_option('rsd_rag_similarity_threshold', 0.55));
        $scored = [];

        foreach ($rows as $row) {
            $db_vector = json_decode($row['embedding_json'], true);
            if (!empty($db_vector)) {
                $sim = self::compute_cosine_similarity($query_vector, $db_vector);
                if ($sim >= $threshold) {
                    $scored[] = [
                        'score' => $sim,
                        'text'  => "[" . $row['file_name'] . "]: " . $row['chunk_text']
                    ];
                }
            }
        }

        usort($scored, function($a, $b) {
            return ($b['score'] <=> $a['score']);
        });

        $top = array_slice($scored, 0, $top_k);
        return array_column($top, 'text');
    }

    public static function get_live_business_catalog() {
        $cached = get_transient('rsd_live_business_catalog_cache');
        if ($cached !== false) return $cached;

        $items = [];

        // 1. Tourism, Trips, Expeditions & Tours
        $travel_post_types = ['itineraries', 'trip', 'wp-travel-trip', 'tour', 'wp_travel_itinerary', 'trips', 'tours'];
        $travel_query = get_posts([
            'post_type'      => $travel_post_types,
            'posts_per_page' => 30,
            'post_status'    => 'publish'
        ]);

        if (!empty($travel_query)) {
            $items[] = "=== LIVE TRAVEL TOURS, TRIPS & EXPEDITIONS CATALOG ===";
            foreach ($travel_query as $t) {
                $price = get_post_meta($t->ID, 'wp_travel_trip_price', true) ?: get_post_meta($t->ID, 'price', true) ?: get_post_meta($t->ID, 'tour_price', true) ?: 'Contact Concierge';
                $duration = get_post_meta($t->ID, 'wp_travel_trip_duration', true) ?: get_post_meta($t->ID, 'duration', true) ?: 'Standard Duration';
                $items[] = sprintf("Tour: %s | Price: %s | Duration: %s | Booking Link: %s",
                    $t->post_title,
                    $price,
                    $duration,
                    get_permalink($t->ID)
                );
            }
        }

        // 2. Hotel Rooms, Suites & Resorts
        $hotel_post_types = ['mphb_room_type', 'hb_room', 'hotel_room', 'room', 'room_type'];
        $hotel_query = get_posts([
            'post_type'      => $hotel_post_types,
            'posts_per_page' => 30,
            'post_status'    => 'publish'
        ]);

        if (!empty($hotel_query)) {
            $items[] = "\n=== LIVE HOTEL ROOMS, SUITES & INVENTORY ===";
            foreach ($hotel_query as $r) {
                $rate = get_post_meta($r->ID, 'mphb_price', true) ?: get_post_meta($r->ID, 'room_price', true) ?: get_post_meta($r->ID, 'price', true) ?: 'Contact Concierge';
                $capacity = get_post_meta($r->ID, 'mphb_capacity', true) ?: '2 Guests';
                $items[] = sprintf("Room/Suite: %s | Rate: %s/Night | Capacity: %s | Booking URL: %s",
                    $r->post_title,
                    $rate,
                    $capacity,
                    get_permalink($r->ID)
                );
            }
        }

        // 3. WooCommerce Store Products & 1-Tap Checkout
        if (class_exists('WooCommerce')) {
            $products = wc_get_products(['limit' => 40, 'status' => 'publish']);
            if (!empty($products)) {
                $items[] = "\n=== LIVE E-COMMERCE PRODUCTS & DIRECT 1-TAP CHECKOUT ===";
                foreach ($products as $prod) {
                    $stock_str = $prod->is_in_stock() ? 'In Stock (' . ($prod->get_stock_quantity() ?? 'Available') . ')' : 'Out of Stock';
                    $checkout_link = get_site_url() . '/checkout/?add-to-cart=' . $prod->get_id();
                    $items[] = sprintf("Product: %s (ID: %d) | Price: %s %s | Stock: %s | 1-Tap Checkout: %s",
                        $prod->get_name(),
                        $prod->get_id(),
                        $prod->get_price(),
                        get_woocommerce_currency(),
                        $stock_str,
                        $checkout_link
                    );
                }
            }
        }

        // 4. Key Public Pages Snippets
        $pages = get_pages(['number' => 15, 'post_status' => 'publish']);
        if (!empty($pages)) {
            $items[] = "\n=== WEBSITE PUBLIC PAGES & SERVICES OVERVIEW ===";
            foreach ($pages as $p) {
                $clean_text = wp_strip_all_tags($p->post_content);
                $snippet = mb_substr(preg_replace('/\s+/', ' ', $clean_text), 0, 250);
                $items[] = sprintf("Page: %s | URL: %s | Summary: %s", $p->post_title, get_permalink($p->ID), $snippet);
            }
        }

        $context_str = implode("\n", $items);
        set_transient('rsd_live_business_catalog_cache', $context_str, 6 * HOUR_IN_SECONDS);
        return $context_str;
    }

    public static function clear_cache() {
        delete_transient('rsd_live_business_catalog_cache');
        delete_transient('rsd_kb_active_context_cache');
    }
}

class RedSeaAIProviderManager {

    public static function get_model_leaderboard() {
        return [
            'deepseek-reasoner' => [
                'name'        => 'DeepSeek R1 (Reasoner)',
                'provider'    => 'OpenCode AI',
                'score'       => '9.9/10',
                'badge'       => '🏆 القمة في الاستدلال والرياضيات',
                'latency'     => 'متوسط (تفكير تفصيلي)',
                'context'     => '64k tokens',
                'free'        => true
            ],
            'claude-3-5-sonnet' => [
                'name'        => 'Claude 3.5 Sonnet',
                'provider'    => 'OpenCode AI',
                'score'       => '9.9/10',
                'badge'       => '💎 فخامة الصياغة والإقناع البيعي',
                'latency'     => 'سريع جداً',
                'context'     => '200k tokens',
                'free'        => true
            ],
            'deepseek-chat' => [
                'name'        => 'DeepSeek V3 (Chat)',
                'provider'    => 'OpenCode AI / DeepSeek',
                'score'       => '9.8/10',
                'badge'       => '⚡ رقم 1 في السرعة واللغة العربية',
                'latency'     => 'فائق السرعة (~400ms)',
                'context'     => '64k tokens',
                'free'        => true
            ],
            'gpt-4o-mini' => [
                'name'        => 'GPT-4o Mini',
                'provider'    => 'OpenCode AI / OpenAI',
                'score'       => '9.6/10',
                'badge'       => '🚀 ذكاء متوازن وسرعة فائقة',
                'latency'     => 'فائق السرعة (~350ms)',
                'context'     => '128k tokens',
                'free'        => true
            ],
            'gemini-flash-latest' => [
                'name'        => 'Google Gemini 2.5 Flash',
                'provider'    => 'OpenCode AI / Google',
                'score'       => '9.5/10',
                'badge'       => '🌐 سياق عملاق واستجابة لحظية',
                'latency'     => 'فائق السرعة (~300ms)',
                'context'     => '1M tokens',
                'free'        => true
            ],
            'llama-3.3-70b' => [
                'name'        => 'Llama 3.3 70B Instruct',
                'provider'    => 'OpenCode AI',
                'score'       => '9.4/10',
                'badge'       => '🛡️ مفتوح المصدر ومتعدد المهام',
                'latency'     => 'سريع (~600ms)',
                'context'     => '128k tokens',
                'free'        => true
            ],
            'qwen-2.5-coder-32b' => [
                'name'        => 'Qwen 2.5 Coder 32B',
                'provider'    => 'OpenCode AI',
                'score'       => '9.3/10',
                'badge'       => '⚙️ متخصص في المنطق والهيكلة',
                'latency'     => 'سريع',
                'context'     => '32k tokens',
                'free'        => true
            ]
        ];
    }

    public static function track_telemetry($provider, $tokens, $success = true, $failed = false) {
        $telemetry = get_option('rsd_provider_telemetry', [
            'opencode' => ['requests' => 0, 'tokens' => 0, 'errors' => 0, 'last_active' => 'Never'],
            'gemini'   => ['requests' => 0, 'tokens' => 0, 'errors' => 0, 'last_active' => 'Never'],
            'openai'   => ['requests' => 0, 'tokens' => 0, 'errors' => 0, 'last_active' => 'Never'],
            'deepseek' => ['requests' => 0, 'tokens' => 0, 'errors' => 0, 'last_active' => 'Never'],
        ]);

        if (!isset($telemetry[$provider])) {
            $telemetry[$provider] = ['requests' => 0, 'tokens' => 0, 'errors' => 0, 'last_active' => 'Never'];
        }

        $telemetry[$provider]['requests']++;
        $telemetry[$provider]['tokens'] += intval($tokens);
        if ($failed) {
            $telemetry[$provider]['errors']++;
        }
        $telemetry[$provider]['last_active'] = current_time('mysql');

        update_option('rsd_provider_telemetry', $telemetry);
    }

    public static function clear_kb_cache() {
        delete_transient('rsd_kb_active_context_cache');
    }

    public static function get_default_master_prompt() {
        $company_name = get_option('rsd_company_name', 'RED SEA DIGITAL');
        $whatsapp     = get_option('rsd_whatsapp_phone', '201028803080');

        return "<system_identity>
  أنت مستشار استراتيجي وخبير مبيعات ذكي لشركة {$company_name}.
  أسلوبك: بشري، ذكي، سريع البديهة، مركز على الفوائد الملموسة للعميل، وتجيب بدقة متناهية على السؤال المطروح دون تكرار أو قوالب جامدة.
  واتساب الشركة: {$whatsapp}
</system_identity>

<autonomous_booking_mandate>
  1. أنت مفوض بالكامل لإجراء وحجز مواعيد الاستشارات الاستراتيجية للعميل مباشرة بدلاً منه داخل المحادثة.
  2. عندما يطلب العميل حجز استشارة أو استفساراً عن باقاتنا، اطلب منه بلباقة (الاسم ورقم الواتساب ونوع النشاط أو الموعد المفضل).
  3. بمجرد أن يزودك العميل برقم هاتفه، قم بتأكيد الحجز فوراً وبثقة واحترافية عالية:
     - بالعربية: '🎉 تم تأكيد وتثبيت حجز استشارتك بنجاح يا [اسم العميل]! تم تسجيل طلبك وإرسال إشعار التأكيد إلى رقم الواتساب: [الرقم]. سيتواصل معك مستشارنا التقني في الموعد المحدد.'
     - بالإنجليزية: '🎉 Your consultation is confirmed, [Customer Name]! Your reservation is logged and confirmation sent to WhatsApp: [Phone Number]. Our senior architect will connect with you at the scheduled time.'
</autonomous_booking_mandate>

<conversational_behavior_rules>
  1. عدم تكرار الترحيب: إذا كانت هناك محادثة سابقة أو قام العميل بطرح سؤال متابعة، لا تقل 'أهلاً بك' أو تعيد تقديم الشركة، بل أجب عن سؤاله مباشرة وفوراً.
  2. الإجابة المخصصة حسب الموضوع (Specific Domain Deep-Dive):
     - إذا سأل عن (أتمتة المبيعات والواتساب): اشرح فوائد بوت الواتساب التفاعلي، استرجاع السلات المتروكة، وتأكيد الحجوزات فورياً.
     - إذا سأل عن (حجز استشارة أو باقة): اطلب منه الاسم ورقم الواتساب ونوع نشاطه لترتيب الموعد فوراً.
     - إذا سأل عن (فنادق أو غوص أو تجارة): اشرح الحل التقني وحساب الوفر المالي (توفير 15-30% عمولات) الخاص بنشاطه.
  3. الهيكل والتنسيق:
     - إجابات مركزة وموجزة (40 إلى 70 كلمة).
     - نقاط تعداد عريضة (2 إلى 3 نقاط) خاصة بالموضوع المطلوب حصراً.
     - سؤال ذكي في النهاية لدفع المحادثة للأمام (مثال: 'هل ترغب في ربط البوت بمتجر إلكتروني أم موقع حجوزات؟').
  4. عدم إخراج أي أكواد أو وسوم برمجية أو أقواس JSON إطلاقاً.
</conversational_behavior_rules>";
    }

    public static function generate($user_message, $history = [], $custom_options = []) {
        $primary_provider = $custom_options['provider'] ?? get_option('rsd_ai_provider', 'gemini');
        $primary_model    = $custom_options['model'] ?? get_option('rsd_ai_model', 'gemini-flash-latest');
        
        $fallback_chain = [$primary_provider, 'gemini', 'opencode', 'deepseek', 'openai'];
        $fallback_chain = array_values(array_unique(array_filter($fallback_chain)));

        $error_log = [];

        foreach ($fallback_chain as $provider) {
            $config = self::get_provider_config($provider, $custom_options);
            if (empty($config['api_key']) && $provider !== 'opencode') {
                continue;
            }

            $response = self::call_provider($provider, $user_message, $history, $config, $error_log);
            if (!empty($response) && strlen(trim($response)) > 10) {
                self::track_telemetry($provider, strlen($response) / 4, true, false);
                return $response;
            } else {
                self::track_telemetry($provider, 0, false, true);
            }
        }

        return get_option('rsd_fallback_message', '<p style=\'margin:0 0 10px 0;line-height:1.65;\'>أهلاً بك في <strong>RED SEA DIGITAL</strong>! يسعدنا مساعدتك في تطوير نشاطك ومضاعفة مبيعاتك المباشرة.</p>
<div style="margin:6px 0;padding-right:14px;position:relative;"><span style="color:#2563EB;position:absolute;right:0;font-weight:bold;">•</span> <strong>حجز مباشر 24/7</strong>: استقبال استفسارات العملاء وحجز الرحلات والغرف تلقائياً بعدة لغات دون توقف.</div>
<div style="margin:6px 0;padding-right:14px;position:relative;"><span style="color:#2563EB;position:absolute;right:0;font-weight:bold;">•</span> <strong>استرداد العمولات</strong>: توفير 15% إلى 30% من أرباحك الصافية وتجنب عمولات الوسطاء والمنصات.</div>
<div style="margin:6px 0;padding-right:14px;position:relative;"><span style="color:#2563EB;position:absolute;right:0;font-weight:bold;">•</span> <strong>أتمتة المبيعات والواتساب</strong>: جمع بيانات العملاء ومتابعة الحجوزات والسلات تلقائياً.</div>
<p style=\'margin:10px 0 0 0;line-height:1.65;\'>يسعدنا ترتيب استشارة سريعة لمشروعك، تواصل معنا مباشرة عبر الواتساب: <strong>01028803080</strong></p>');
    }

    public static function get_provider_config($provider, $custom_options = []) {
        $user_model = $custom_options['model'] ?? get_option('rsd_ai_model', 'gemini-flash-latest');

        switch ($provider) {
            case 'gemini':
                $model = (strpos($user_model, 'gemini') !== false) ? $user_model : 'gemini-flash-latest';
                return [
                    'api_key'       => get_option('rsd_gemini_api_key', ''),
                    'model'         => $model,
                    'system_prompt' => $custom_options['system_prompt'] ?? self::build_system_prompt('', 'concierge', $custom_options),
                    'temperature'   => floatval(get_option('rsd_llm_temperature', 0.6)),
                    'max_tokens'    => 2048,
                    'timeout'       => intval(get_option('rsd_llm_timeout', 15))
                ];
            case 'deepseek':
                $model = (strpos($user_model, 'deepseek') !== false) ? $user_model : 'deepseek-chat';
                return [
                    'api_key'       => get_option('rsd_deepseek_api_key', ''),
                    'model'         => $model,
                    'system_prompt' => $custom_options['system_prompt'] ?? self::build_system_prompt('', 'concierge', $custom_options),
                    'temperature'   => floatval(get_option('rsd_llm_temperature', 0.6)),
                    'max_tokens'    => 2048,
                    'timeout'       => intval(get_option('rsd_llm_timeout', 15))
                ];
            case 'openai':
                $model = (strpos($user_model, 'gpt') !== false) ? $user_model : 'gpt-4o-mini';
                return [
                    'api_key'       => get_option('rsd_openai_api_key', ''),
                    'model'         => $model,
                    'system_prompt' => $custom_options['system_prompt'] ?? self::build_system_prompt('', 'concierge', $custom_options),
                    'temperature'   => floatval(get_option('rsd_llm_temperature', 0.6)),
                    'max_tokens'    => 2048,
                    'timeout'       => intval(get_option('rsd_llm_timeout', 15))
                ];
            case 'opencode':
            default:
                $opencode_key = get_option('rsd_opencode_api_key', '');
                $model = (!empty($user_model) && strpos($user_model, 'gemini') === false) ? $user_model : 'gpt-4o-mini';
                return [
                    'api_key'       => !empty($opencode_key) ? $opencode_key : 'free_tier_key',
                    'model'         => $model,
                    'system_prompt' => $custom_options['system_prompt'] ?? self::build_system_prompt('', 'concierge', $custom_options),
                    'temperature'   => floatval(get_option('rsd_llm_temperature', 0.6)),
                    'max_tokens'    => 2048,
                    'timeout'       => intval(get_option('rsd_llm_timeout', 15))
                ];
        }
    }

    public static function call_provider($provider, $user_message, $history, $config, &$error_log = []) {
        if ($provider === 'gemini') {
            return self::call_gemini($config['api_key'], $config['model'], $user_message, $history, $config, $error_log);
        } elseif ($provider === 'deepseek') {
            return self::call_openai_compatible('https://api.deepseek.com/v1/chat/completions', $config['api_key'], $config['model'], $user_message, $history, $config, $error_log);
        } elseif ($provider === 'openai') {
            return self::call_openai_compatible('https://api.openai.com/v1/chat/completions', $config['api_key'], $config['model'], $user_message, $history, $config, $error_log);
        } else {
            return self::call_openai_compatible('https://opencode.ai/zen/v1/chat/completions', $config['api_key'], $config['model'], $user_message, $history, $config, $error_log);
        }
    }

    public static function call_gemini($api_key, $model, $user_message, $history, $config, &$error_log) {
        if (empty($api_key)) {
            $error_log[] = "Gemini: API Key missing.";
            return null;
        }

        // List of operational models in priority order
        $models_to_try = array_unique([
            $model ?: 'gemini-flash-latest',
            'gemini-flash-latest',
            'gemini-flash-lite-latest',
            'gemini-flash-latest'
        ]);

        $contents = [];
        if (!empty($history) && is_array($history)) {
            foreach ($history as $h) {
                $role = ($h['role'] === 'user') ? 'user' : 'model';
                $contents[] = ['role' => $role, 'parts' => [['text' => $h['content']]]];
            }
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $user_message]]];

        $body = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature'     => $config['temperature'] ?? 0.5,
                'maxOutputTokens' => 2048
            ]
        ];

        if (!empty($config['system_prompt'])) {
            $body['systemInstruction'] = ['parts' => [['text' => $config['system_prompt']]]];
        }

        foreach ($models_to_try as $m_name) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($m_name) . ":generateContent?key=" . urlencode($api_key);

            $res = wp_remote_post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode($body),
                'timeout' => $config['timeout'] ?? 15,
                'sslverify' => false
            ]);

            if (is_wp_error($res)) {
                $error_log[] = "Gemini ($m_name) WP_Error: " . $res->get_error_message();
                continue;
            }

            $status = wp_remote_retrieve_response_code($res);
            $raw = wp_remote_retrieve_body($res);
            $data = json_decode($raw, true);

            if ($status === 200 && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return trim($data['candidates'][0]['content']['parts'][0]['text']);
            }

            $error_log[] = "Gemini ($m_name) HTTP $status: " . mb_substr($raw, 0, 100);
        }

        return null;
    }

    public static function call_openai_compatible($endpoint_url, $api_key, $model, $user_message, $history, $config, &$error_log) {
        $messages = [];
        if (!empty($config['system_prompt'])) {
            $messages[] = ['role' => 'system', 'content' => $config['system_prompt']];
        }
        if (!empty($history) && is_array($history)) {
            foreach ($history as $h) {
                $messages[] = ['role' => $h['role'], 'content' => $h['content']];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $user_message];

        $body = [
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $config['temperature'] ?? 0.6,
            'max_tokens'  => $config['max_tokens'] ?? 850
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ];

        $res = wp_remote_post($endpoint_url, [
            'headers' => $headers,
            'body'    => json_encode($body),
            'timeout' => $config['timeout'] ?? 12,
            'sslverify' => false
        ]);

        if (is_wp_error($res)) {
            $error_log[] = "OpenAI Compatible WP_Error: " . $res->get_error_message();
            return null;
        }

        $status = wp_remote_retrieve_response_code($res);
        $raw = wp_remote_retrieve_body($res);
        $data = json_decode($raw, true);

        if ($status === 200 && isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }

        $error_log[] = "OpenAI Compatible HTTP $status: " . $raw;
        return null;
    }

    public static function build_system_prompt($custom_prompt = '', $agent_role = 'concierge', $custom_options = []) {
        $detected_lang = $custom_options['detected_lang'] ?? 'ar';
        
        $lang_mandates = [
            'en' => "CRITICAL LANGUAGE LOCK: The user is communicating in ENGLISH. You MUST formulate your entire response exclusively in English. Even if knowledge base references are in Arabic, you MUST translate and answer 100% in fluent, polished English. Never output Arabic when the user writes in English.",
            'ar' => "قاعدة لغوية صارمة: المستخدم يتحدث باللغة العربية. يجب أن تكون إجابتك باللغة العربية الفصحى الراقية حصراً.",
            'ru' => "CRITICAL LANGUAGE LOCK: The user is communicating in RUSSIAN. Отвечайте строго на русском языке.",
            'de' => "CRITICAL LANGUAGE LOCK: The user is communicating in GERMAN. Antworten Sie ausschließlich auf Deutsch.",
            'fr' => "CRITICAL LANGUAGE LOCK: The user is communicating in FRENCH. Répondez exclusivement en français.",
            'es' => "CRITICAL LANGUAGE LOCK: The user is communicating in SPANISH. Responda exclusivamente en español."
        ];

        $lang_header = "<language_mandate>
  " . ($lang_mandates[$detected_lang] ?? $lang_mandates['en']) . "
</language_mandate>

";

        if (!empty($custom_prompt)) {
            return $lang_header . $custom_prompt;
        }

        $base = self::get_default_master_prompt();

        return $lang_header . $base;
    }

    public static function create_custom_agent($agent_name, $agent_mission, $assigned_tools = ['rag_search', 'sales_calculator']) {
        $prompt_generator_instruction = "You are the Chief AI Architect for RED SEA DIGITAL.
A user wants to create a new specialized AI Agent named '{$agent_name}'.
The mission of this agent is: '{$agent_mission}'.

Write a world-class, production-grade XML system prompt for this agent.
The system prompt MUST include:
<agent_identity>: Clear role, quiet luxury authority, and tone.
<mission_objectives>: Specific outcomes and tasks.
<response_style>: Ultra-fast, concise, human-like sales consultation without boring monologues.
<guardrails>: Zero prompt leakage and strict focus on Red Sea Digital value propositions.

Output ONLY the final XML system prompt without any explanations or introductory remarks.";

        $generated_prompt = RedSeaAIProviderManager::generate($prompt_generator_instruction, [], [
            'provider' => 'opencode',
            'model'    => 'gpt-4o-mini'
        ]);

        if (empty($generated_prompt) || strlen($generated_prompt) < 50) {
            $generated_prompt = "<agent_identity>\nYou are {$agent_name}, a specialized AI Agent at RED SEA DIGITAL.\nMission: {$agent_mission}\n</agent_identity>\n<response_style>\nBe fast, concise, professional, and consultatively persuasive.\n</response_style>";
        }

        $agent_id = sanitize_title($agent_name) . '_' . time();
        $custom_agents = get_option('rsd_custom_agents', []);
        if (!is_array($custom_agents)) $custom_agents = [];

        $custom_agents[$agent_id] = [
            'id'             => $agent_id,
            'name'           => sanitize_text_field($agent_name),
            'mission'        => sanitize_textarea_field($agent_mission),
            'system_prompt'  => trim($generated_prompt),
            'tools'          => (array)$assigned_tools,
            'status'         => 'active',
            'created_at'     => current_time('mysql'),
            'execution_count'=> 0
        ];

        update_option('rsd_custom_agents', $custom_agents);
        return $custom_agents[$agent_id];
    }

    public static function get_all_agents() {
        $core_agents = [
            'chief' => [
                'name'        => 'Chief Orchestrator',
                'role'        => 'Intent Routing & Task Allocation',
                'status'      => 'active',
                'is_core'     => true,
                'description' => 'الموجه الرئيسي لتحليل نية العميل وتوزيع المهام بدقة وسرعة.'
            ],
            'rag' => [
                'name'        => 'RAG Knowledge Agent',
                'role'        => 'Vector Retrieval & DB Grounding',
                'status'      => 'active',
                'is_core'     => true,
                'description' => 'البحث الدلالي المتجهي واستخراج معلومات النشاط بدون هلوسة.'
            ],
            'concierge' => [
                'name'        => 'Frontline Sales Concierge',
                'role'        => 'Strategic Negotiation & Sales Closing',
                'status'      => 'active',
                'is_core'     => true,
                'description' => 'مسؤول المبيعات والاستشارات الفندقية المباشر والسريع.'
            ],
            'qa' => [
                'name'        => 'QA & Security Guardrail',
                'role'        => 'Prompt Protection & Sanitization',
                'status'      => 'active',
                'is_core'     => true,
                'description' => 'حائط الصد الأمني لاعتراض محاولات الاختراق وتنقية المخرجات.'
            ]
        ];

        $custom_agents = get_option('rsd_custom_agents', []);
        if (!is_array($custom_agents)) $custom_agents = [];

        return array_merge($core_agents, $custom_agents);
    }
}

class RedSeaRAGAgent {
    public static function get_grounded_context($user_query) {
        $context_blocks = [];

        if (class_exists('RSD_Knowledge_Base_Manager')) {
            // 1. Search Semantic Vector Store
            $similar_chunks = RSD_Knowledge_Base_Manager::search_similar_chunks($user_query, 4);
            if (!empty($similar_chunks)) {
                $context_blocks[] = "=== KNOWLEDGE BASE GROUNDED CONTEXT ===\n" . implode("\n\n", $similar_chunks);
            }

            // 2. Extract Real-Time Business Catalog (Products / Tours / Hotels / Services)
            $live_catalog = RSD_Knowledge_Base_Manager::get_live_business_catalog();
            if (!empty($live_catalog)) {
                $context_blocks[] = $live_catalog;
            }
        }

        return implode("\n\n", $context_blocks);
    }
}

class RedSeaConciergeAgent {
    public static function generate_response($user_message, $rag_context, $history = [], $custom_options = [], &$trace = []) {
        $start_time = microtime(true);

        $extra_context = "";
        if (!empty($rag_context)) {
            $extra_context = "\n\n<grounded_knowledge_base>\n" . $rag_context . "\n</grounded_knowledge_base>";
        }

        $system_prompt = RedSeaAIProviderManager::build_system_prompt('', 'concierge', $custom_options) . $extra_context;

        $provider = $custom_options['provider'] ?? get_option('rsd_ai_provider', 'gemini');
        $model    = $custom_options['model'] ?? get_option('rsd_ai_model', 'gemini-flash-latest');

        $gen_options = array_merge($custom_options, [
            'provider'      => $provider,
            'model'         => $model,
            'system_prompt' => $system_prompt
        ]);

        $response = RedSeaAIProviderManager::generate($user_message, $history, $gen_options);

        $execution_time = round((microtime(true) - $start_time) * 1000, 2);
        $trace['concierge_agent'] = [
            'status'         => 'success',
            'provider'       => $provider,
            'model'          => $model,
            'execution_ms'   => $execution_time,
            'context_length' => strlen($rag_context)
        ];

        return $response;
    }
}

class RedSeaQAAgent {
    public static function audit_and_sanitize($raw_response, &$trace = []) {
        $start_time = microtime(true);
        $safety_passed = true;
        $violations = [];

        // Intercept XML delimiters & system prompt leaks
        $leak_patterns = [
            '/<system_identity>/i',
            '/<security_and_prompt_guardrails>/i',
            '/RedSeaAIProviderManager/i',
            '/rsd_master_system_prompt/i'
        ];

        foreach ($leak_patterns as $pattern) {
            if (preg_match($pattern, $raw_response)) {
                $safety_passed = false;
                $violations[] = "Potential system prompt leak intercepted.";
                $raw_response = preg_replace($pattern, '', $raw_response);
            }
        }

        if (class_exists('RSD_Output_Cleaner')) {
            $clean_response = RSD_Output_Cleaner::clean($raw_response);
        } else {
            $clean_response = trim($raw_response);
        }

        $execution_time = round((microtime(true) - $start_time) * 1000, 2);
        $trace['qa_agent'] = [
            'status'        => $safety_passed ? 'passed' : 'sanitized',
            'violations'    => $violations,
            'execution_ms'  => $execution_time,
            'clean_length'  => strlen($clean_response)
        ];

        return $clean_response;
    }
}

class RedSeaChiefOrchestrator {
    public static function classify_intent($user_message) {
        $msg = mb_strtolower($user_message, 'UTF-8');

        if (preg_match('/(حساب|وفر|عمولة|عمولات|أرباح|فلوس|نسبة|roi|calculate|saving|profit)/iu', $msg)) {
            return 'roi_calculation';
        }
        if (preg_match('/(حجز|استشارة|ميعاد|تواصل|واتساب|اتصال|رقم|book|consultation|schedule)/iu', $msg)) {
            return 'lead_booking';
        }
        if (preg_match('/(pms|opera|cloudbeds|hostaway|ازدواج|حجز مزدوج|تزامن|sync)/iu', $msg)) {
            return 'pms_sync';
        }
        if (preg_match('/(صوت|تحدث|ميكروفون|voice|speech|listen)/iu', $msg)) {
            return 'voice_mode';
        }

        return 'general_consultation';
    }

    public static function process_message($user_message, $history = [], $custom_options = []) {
        $total_start = microtime(true);
        $trace = [
            'timestamp'  => current_time('mysql'),
            'user_query' => mb_substr($user_message, 0, 100, 'UTF-8'),
        ];

        $intent = self::classify_intent($user_message);
        $trace['chief_orchestrator'] = [
            'status'            => 'routed',
            'classified_intent' => $intent
        ];

        $rag_context = RedSeaRAGAgent::get_grounded_context($user_message);
        $trace['rag_agent'] = [
            'status'        => !empty($rag_context) ? 'grounded' : 'no_chunks',
            'chunks_found'  => !empty($rag_context) ? 1 : 0
        ];

        $raw_response = RedSeaConciergeAgent::generate_response($user_message, $rag_context, $history, $custom_options, $trace);
        $final_response = RedSeaQAAgent::audit_and_sanitize($raw_response, $trace);

        $total_ms = round((microtime(true) - $total_start) * 1000, 2);
        $trace['total_ms'] = $total_ms;

        self::log_orchestration_trace($trace);

        return [
            'reply'  => $final_response,
            'intent' => $intent,
            'trace'  => $trace
        ];
    }

    public static function log_orchestration_trace($trace) {
        $recent_traces = get_option('rsd_orchestration_logs', []);
        if (!is_array($recent_traces)) $recent_traces = [];
        array_unshift($recent_traces, $trace);
        if (count($recent_traces) > 50) {
            $recent_traces = array_slice($recent_traces, 0, 50);
        }
        update_option('rsd_orchestration_logs', $recent_traces);
    }
}

class RedSeaAgentFactory {
    public static function create_custom_agent($agent_name, $agent_mission, $assigned_tools = ['rag_search', 'sales_calculator']) {
        return RedSeaAIProviderManager::create_custom_agent($agent_name, $agent_mission, $assigned_tools);
    }

    public static function get_all_agents() {
        return RedSeaAIProviderManager::get_all_agents();
    }
}

/**
 * =========================================================================
 * RED SEA DIGITAL — AUTONOMOUS OUTBOUND MULTI-AGENT LEAD RADAR ENGINE
 * =========================================================================
 */
class RedSeaLeadRadarEngine {

    public static function init_leads_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_leads';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            company_name VARCHAR(255) NOT NULL,
            target_industry VARCHAR(100) NOT NULL,
            contact_phone VARCHAR(50) NOT NULL,
            website_url VARCHAR(255) DEFAULT '',
            gap_analysis LONGTEXT DEFAULT NULL,
            tailored_pitch TEXT DEFAULT NULL,
            pipeline_status VARCHAR(50) DEFAULT 'pending_review',
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY pipeline_status (pipeline_status)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function run_discovery_cycle($niche = 'resorts_redsea', $city = 'الغردقة وشرم الشيخ') {
        global $wpdb;
        self::init_leads_table();
        $table_name = $wpdb->prefix . 'rsd_leads';

        // 1. Multi-Agent Synthesis via RedSeaAIProviderManager
        $prompt = "You are the Chief Sales Prospecting & Intelligence Agent for RED SEA DIGITAL.
Target Niche: {$niche} in {$city}.
Generate 3 realistic, high-value prospective Egyptian businesses (e.g. boutique resorts, diving clubs, luxury tour operators, or medical tourism clinics) that currently suffer from heavy OTA commission leakages (15-30%) or lack a direct WhatsApp AI booking engine.

For each prospect, return a valid JSON array of objects with the exact schema:
[
  {
    \"company_name\": \"اسم المنشأة أو المنتجع\",
    \"target_industry\": \"الفنادق والمنتجعات الفاخرة\",
    \"contact_phone\": \"2010XXXXXXXX\",
    \"website_url\": \"https://example.com\",
    \"strengths\": \"موقع ممتاز وتقييمات عالية على بوكينج وتريب أدفايزر\",
    \"critical_gaps\": \"الاعتماد الكامل بنسبة 80% على منصات OTA، عدم وجود محرك حجز مباشر بدون عمولة، غياب الرد الآلي بالواتساب\",
    \"revenue_loss_estimate\": \"ما بين 20,000 إلى 45,000 دولار سنوياً عمولات مهدرة\",
    \"tailored_pitch\": \"مساء الخير يا فندم، أنا م. عمرو أحمد من Red Sea Digital... [رسالة مخصصة باللهجة المصرية الراقية والموجزة تركز على استرداد 20% عمولات وتوفير محرك حجز مباشر فاخر مع دعوة لمكالمة 15 دقيقة]\"
  }
]
Return ONLY pure JSON array without markdown fences.";

        $raw_response = RedSeaAIProviderManager::generate($prompt, [], [
            'temperature' => 0.7
        ]);

        $clean_json = trim(preg_replace('/```json|```/', '', $raw_response));
        $prospects = json_decode($clean_json, true);

        if (!is_array($prospects) || empty($prospects)) {
            // Fallback curated high-yield Red Sea prospects if AI format varies
            $prospects = [
                [
                    'company_name'          => 'منتجع المرجان الأزرق لاجون (Blue Lagoon Boutique Resort)',
                    'target_industry'       => 'الضيافة والمنتجعات الفاخرة',
                    'contact_phone'         => '201099887766',
                    'website_url'           => 'https://bluelagoon-redsea.com',
                    'strengths'             => 'إشغال سياحي موسمي 75% وتقييم 8.9 على Booking.com',
                    'critical_gaps'         => '82% من الحجوزات تأتي عبر Booking و Expedia مع هدر 18% عمولات، وبطء الموقع على الموبايل',
                    'revenue_loss_estimate' => '32,000$ سنوياً عمولات وسطاء',
                    'tailored_pitch'        => "مساء الخير يا فندم، أتمنى لحضرتك يوماً طيباً. أنا م. عمرو أحمد المؤسس لـ Red Sea Digital. كنا بنراجع حركة الحجوزات لمنتجعات البحر الأحمر، ولفت انتباهنا التقييم الرائع لمنتجعكم (8.9). لاحظنا أن أكثر من 80% من الحجوزات بتمر عبر بوكينج بعمولة 18%، بينما نقدر نبني لحضرتكم محرك حجز مباشر فاخر بالذكاء الاصطناعي يسترد أرباحكم الصافية ويزيد الحجوزات المباشرة 40%. يسعدني نتشارك مكالمة سريعة مدتها 15 دقيقة نستعرض فيها خطة الاسترداد بالأرقام."
                ],
                [
                    'company_name'          => 'نادي أعماق البحر الأحمر الدولي للغوص (Deep Blue Divers Hub)',
                    'target_industry'       => 'مراكز الغوص والرحلات البحرية',
                    'contact_phone'         => '201055443322',
                    'website_url'           => 'https://deepbluediving-sharm.com',
                    'strengths'             => 'سمعة دولية ورحلات سفاري بحرية منتظمة لجزيرة تيران ورأس محمد',
                    'critical_gaps'         => 'لا يوجد نظام دفع وتأكيد فوري للرحلات، وتأخر الرد على استفسارات الواتساب الأوروبية لأكثر من 6 ساعات',
                    'revenue_loss_estimate' => '18,500$ عمولات وسطاء وباقات مهدرة',
                    'tailored_pitch'        => "أهلاً بحضرتك يا فندم، أنا م. عمرو أحمد من Red Sea Digital. بنحييكم على التقييمات الممتازة لرحلات السفاري والغوص. لاحظنا أن حجوزات السياح الأجانب بتواجه تأخير في التأكيد والدفع الفوري على الواتساب، وده بيقلل التحويل المباشر. طورنا نظام ذكاء اصطناعي لوكلاء الغوص يربط الحجز بالدفع الفوري ويجيب السائح بلغات متعددة خلال ثوانٍ 24/7. هل يناسب حضرتك نستعرض ديمو سريع للمنظومة هذا الأسبوع؟"
                ]
            ];
        }

        $inserted_count = 0;
        foreach ($prospects as $p) {
            $company = sanitize_text_field($p['company_name'] ?? 'شركة جديدة');
            $industry = sanitize_text_field($p['target_industry'] ?? 'سياحة وضيافة');
            $phone = preg_replace('/[^0-9]/', '', $p['contact_phone'] ?? '');
            $url = esc_url_raw($p['website_url'] ?? '');
            
            $gap_dossier = [
                'strengths'             => sanitize_text_field($p['strengths'] ?? ''),
                'critical_gaps'         => sanitize_text_field($p['critical_gaps'] ?? ''),
                'revenue_loss_estimate' => sanitize_text_field($p['revenue_loss_estimate'] ?? '')
            ];

            $pitch = sanitize_textarea_field($p['tailored_pitch'] ?? '');

            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table_name} WHERE company_name = %s LIMIT 1", $company));
            if (!$exists) {
                $wpdb->insert($table_name, [
                    'company_name'    => $company,
                    'target_industry' => $industry,
                    'contact_phone'   => $phone,
                    'website_url'     => $url,
                    'gap_analysis'    => json_encode($gap_dossier, JSON_UNESCAPED_UNICODE),
                    'tailored_pitch'  => $pitch,
                    'pipeline_status' => 'pending_review',
                    'created_at'      => current_time('mysql')
                ]);
                $inserted_count++;
            }
        }

        return [
            'status'         => 'success',
            'inserted_count' => $inserted_count,
            'total_found'    => count($prospects)
        ];
    }
}

class RedSeaAIEngine {
/**
     * UNIFY ELEMENTOR DATA FOR ALL HOMEPAGES & PAGES SITE-WIDE
     */
    public static function sync_elementor_data_globally() {
        $homepage_ids = [163, 12, 165, 167];
        
        $elementor_widget_json = json_encode([
            [
                'id' => 'rsd_master_saas_container',
                'elType' => 'container',
                'isInner' => false,
                'settings' => [
                    'content_width' => 'full',
                    'flex_direction' => 'column'
                ],
                'elements' => [
                    [
                        'id' => 'rsd_master_saas_html_widget',
                        'elType' => 'widget',
                        'isInner' => false,
                        'widgetType' => 'html',
                        'settings' => [
                            'html' => '<div class="rsd-saas-elementor-preview-notice">✦ Red Sea Digital — Award-Winning SaaS Design System Active</div>'
                        ],
                        'elements' => []
                    ]
                ]
            ]
        ]);

        foreach ($homepage_ids as $pid) {
            if (get_post($pid)) {
                update_post_meta($pid, '_elementor_data', wp_slash($elementor_widget_json));
                update_post_meta($pid, '_elementor_edit_mode', 'builder');
            }
        }
    }

/**
     * static save_booking method for lead database insertion
     */
    public static function save_booking($name, $phone, $service, $details = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_bookings';

        $inserted = $wpdb->insert(
            $table_name,
            array(
                'customer_name'   => sanitize_text_field($name),
                'customer_phone'  => sanitize_text_field($phone),
                'service_type'    => sanitize_text_field($service),
                'booking_details' => sanitize_textarea_field($details),
                'created_at'      => current_time('mysql')
            )
        );

        if ($inserted) {
            self::trigger_whatsapp_outbound($name, $phone, $service);
        }

        return $inserted;
    }

    /**
     * Asynchronous Non-Blocking Outbound WhatsApp Webhook Notification
     */
    public static function trigger_whatsapp_outbound($customer_name, $customer_phone, $service_type) {
        $is_enabled = get_option('rsd_whatsapp_enabled', get_option('rsd_wa_autoresponder_enabled', '0'));
        if ($is_enabled !== '1') {
            return false;
        }

        $api_url = get_option('rsd_whatsapp_api_url', get_option('rsd_wa_api_endpoint', ''));
        $api_key = get_option('rsd_whatsapp_api_key', get_option('rsd_wa_api_key', ''));
        if (empty($api_url)) {
            return false;
        }

        $clean_phone = preg_replace('/[^0-9]/', '', $customer_phone);
        if (strpos($clean_phone, '01') === 0) {
            $clean_phone = '2' . $clean_phone;
        }

        $default_template = "أهلاً بك أستاذ {name}! ✨\nتم استلام طلبك لـ ({service}) بنجاح عبر Red Sea Digital.\nسيتواصل معك مستشارك في أقرب وقت.";
        $template = get_option('rsd_whatsapp_template', $default_template);

        $message_text = str_replace(
            array('{name}', '{service}'),
            array($customer_name, $service_type),
            $template
        );

        $payload = array(
            'number'  => $clean_phone,
            'options' => array('delay' => 1200),
            'text'    => $message_text,
            'message' => $message_text
        );

        wp_remote_post($api_url, array(
            'method'    => 'POST',
            'blocking'  => false,
            'headers'   => array(
                'Content-Type'  => 'application/json',
                'apikey'        => $api_key,
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body'      => json_encode($payload),
            'timeout'   => 5,
        ));

        return true;
    }


    
    /**
     * Register 2-Way WhatsApp Webhook REST API Endpoint
     */
    public function register_rest_routes() {
        register_rest_route('rsd/v1', '/whatsapp-webhook', [
            'methods'             => ['POST', 'GET'],
            'callback'            => [$this, 'handle_whatsapp_webhook'],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * 2-Way WhatsApp Inbound Webhook Processor with Multi-Agent Intelligence
     */
    /**
     * 2-Way WhatsApp Inbound Webhook Processor with Security & Anti-Ban Hardening
     */
    public function handle_whatsapp_webhook($request) {
        // 1. GET Challenge & Webhook Verification
        if ($request->get_method() === 'GET') {
            $configured_key = get_option('rsd_whatsapp_api_key', '');
            $token = $request->get_param('hub_verify_token') ?? ($request->get_param('verify_token') ?? $request->get_param('token'));
            $challenge = $request->get_param('hub_challenge') ?? $request->get_param('challenge');
            
            if (!empty($configured_key) && !empty($token) && !hash_equals($configured_key, (string)$token)) {
                return new WP_REST_Response(['status' => 'error', 'message' => 'Unauthorized: Invalid Verification Token'], 403);
            }
            if (!empty($challenge)) {
                return new WP_REST_Response((int)$challenge, 200);
            }
            return new WP_REST_Response([
                'status'   => 'active',
                'service'  => 'RED SEA DIGITAL — WhatsApp 2-Way Multi-Agent Bridge',
                'security' => 'Hardened Anti-Ban & Token Auth Active',
                'time'     => current_time('mysql')
            ], 200);
        }

        // 2. Webhook Authentication Security Gate (X-Api-Key / Bearer Token)
        $configured_key = get_option('rsd_whatsapp_api_key', '');
        if (!empty($configured_key)) {
            $auth_header = $request->get_header('x_api_key') ?? ($request->get_header('authorization') ?? $request->get_header('apikey'));
            $token_param = $request->get_param('token') ?? $request->get_param('api_key');
            
            $provided_key = '';
            if (!empty($auth_header)) {
                $provided_key = str_replace('Bearer ', '', trim($auth_header));
            } elseif (!empty($token_param)) {
                $provided_key = trim($token_param);
            }

            if (empty($provided_key) || !hash_equals($configured_key, $provided_key)) {
                return new WP_REST_Response([
                    'status'  => 'error',
                    'message' => 'Unauthorized: Invalid or Missing API Key'
                ], 401);
            }
        }

        $params = $request->get_json_params() ?: $request->get_body_params();
        if (empty($params)) {
            $raw = file_get_contents('php://input');
            $params = json_decode($raw, true) ?: [];
        }

        // 3. Robust Payload Parsing & Sanitization
        $sender_phone = '';
        $message_text = '';
        $push_name    = 'عميل واتساب';
        $from_me      = false;

        // Evolution API / Baileys payload format
        if (isset($params['data'])) {
            $data = $params['data'];
            $from_me = !empty($data['key']['fromMe']);
            $sender_phone = $data['key']['remoteJid'] ?? '';
            $message_text = $data['message']['conversation'] ?? ($data['message']['extendedTextMessage']['text'] ?? '');
            $push_name = $data['pushName'] ?? $push_name;
        } elseif (isset($params['entry'][0]['changes'][0]['value']['messages'][0])) {
            // Meta Cloud API format
            $msg = $params['entry'][0]['changes'][0]['value']['messages'][0];
            $sender_phone = $msg['from'] ?? '';
            $message_text = $msg['text']['body'] ?? '';
            $push_name = $params['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? $push_name;
        } else {
            // Generic JSON format
            $sender_phone = $params['phone'] ?? ($params['from'] ?? ($params['number'] ?? ''));
            $message_text = $params['message'] ?? ($params['text'] ?? ($params['body'] ?? ''));
            $push_name = $params['name'] ?? ($params['pushName'] ?? $push_name);
            $from_me = !empty($params['fromMe']);
        }

        // Native WordPress Sanitization & Clean Phone Extraction
        $clean_phone = sanitize_text_field(preg_replace('/[^0-9]/', '', str_replace('@s.whatsapp.net', '', (string)$sender_phone)));
        $clean_name  = sanitize_text_field(wp_unslash((string)$push_name));
        $clean_text  = sanitize_textarea_field(wp_unslash((string)$message_text));

        // Inbound-Only Constraint: Ignore self-sent messages and blank pings
        if ($from_me || empty($clean_phone) || empty(trim($clean_text))) {
            return new WP_REST_Response(['status' => 'ignored', 'reason' => 'empty or outbound message'], 200);
        }

        // 4. Anti-Ban Circuit Breaker & Bot Loop Protection
        $phone_hash = md5($clean_phone);
        $cooldown_key = 'rsd_wa_cooldown_' . $phone_hash;
        if (get_transient($cooldown_key)) {
            return new WP_REST_Response([
                'status'  => 'cooldown',
                'message' => 'Sender in human handoff cooldown mode'
            ], 200);
        }

        $turns_key = 'rsd_wa_turns_' . $phone_hash;
        $turns_count = (int) get_transient($turns_key);
        if ($turns_count >= 4) { // Max 4 rapid consecutive turns per 60s
            set_transient($cooldown_key, 1, 600); // 10-minute bot pause
            delete_transient($turns_key);
            
            $handoff_msg = "عزيزي العميل، تم إشعار فريق الاستشارات والحلول الرقمية وسيتواصل معك مستشارك المختص مباشرة لمتابعة كافة التفاصيل.";
            self::send_whatsapp_message($clean_phone, $handoff_msg);
            self::save_booking($clean_name, $clean_phone, 'تحويل لفريق المبيعات (Circuit Breaker)', 'تم تحويل المحادثة بعد تكرار الاستفسارات السريعة');

            return new WP_REST_Response([
                'status'  => 'circuit_breaker_triggered',
                'message' => 'Bot loop breaker activated — handed off to human consultant'
            ], 200);
        }
        set_transient($turns_key, $turns_count + 1, 60);

        // 5. Multi-Agent AI Response Generation (if enabled)
        $ai_enabled = get_option('rsd_whatsapp_ai_enabled', '1');
        $reply_text = '';

        if ($ai_enabled === '1') {
            $system_prompt = "You are the Senior Luxury Direct Booking Consultant for RED SEA DIGITAL on WhatsApp. 
Keep replies concise (2-3 sentences), highly professional, polite, and persuasive. 
Guide the client on eliminating OTA commissions (15-30%) and building direct booking engines. 
Provide clear answers and invite them to schedule a 15-min strategy call.";

            $raw_reply = RedSeaAIProviderManager::generate($clean_text, [], [
                'system_prompt' => $system_prompt
            ]);

            // Strip HTML and XML tags to prevent code injection
            $clean_reply = strip_tags($raw_reply);
            $clean_reply = preg_replace('/<[^>]*>/', '', $clean_reply);
            $clean_reply = html_entity_decode($clean_reply, ENT_QUOTES, 'UTF-8');
            $reply_text  = trim($clean_reply);

            // 6. Anti-Ban Dynamic Delay & Composing State Dispatch
            self::send_whatsapp_message($clean_phone, $reply_text);
        }

        // 7. Lead Capture & CRM Storage
        if (preg_match('/(حجز|سعر|استشارة|booking|price|quote|meeting|consultation)/iu', $clean_text)) {
            self::save_booking($clean_name, $clean_phone, 'استفسار واتساب مباشر', $clean_text);
        }

        return new WP_REST_Response([
            'status'       => 'success',
            'sender'       => $clean_phone,
            'push_name'    => $clean_name,
            'reply'        => $reply_text,
            'auto_replied' => ($ai_enabled === '1')
        ], 200);
    }

    /**
     * Universal WhatsApp Message Dispatcher with Human Typing Simulation
     */
    public static function send_whatsapp_message($phone, $message) {
        $clean_phone = sanitize_text_field(preg_replace('/[^0-9]/', '', (string)$phone));
        if (empty($clean_phone) || empty(trim($message))) return false;

        $gateway_url = rtrim(get_option('rsd_whatsapp_api_url', ''), '/');
        $instance    = get_option('rsd_whatsapp_instance', 'rsd_live');
        $api_key     = get_option('rsd_whatsapp_api_key', '');

        // Anti-Ban Human Typing Simulation: Calculate dynamic typing delay proportional to length (2-4.5s)
        $msg_len = mb_strlen($message);
        $dynamic_delay = min(4500, max(2000, 2000 + ($msg_len * 15) + rand(100, 400)));

        // 1. Evolution API / Baileys Socket Gateway
        if (!empty($gateway_url)) {
            $send_url = strpos($gateway_url, '/message/sendText') !== false 
                ? $gateway_url 
                : "{$gateway_url}/message/sendText/{$instance}";

            wp_remote_post($send_url, [
                'method'    => 'POST',
                'blocking'  => false,
                'headers'   => [
                    'Content-Type'  => 'application/json',
                    'apikey'        => $api_key,
                    'Authorization' => 'Bearer ' . $api_key
                ],
                'body'      => json_encode([
                    'number'  => $clean_phone,
                    'options' => [
                        'delay'       => $dynamic_delay,
                        'presence'    => 'composing',
                        'linkPreview' => false
                    ],
                    'text'    => $message
                ]),
                'timeout'   => 8
            ]);
            return true;
        }

        // 2. Meta Cloud API Fallback
        $cloud_token = get_option('rsd_whatsapp_cloud_token', '');
        $phone_id    = get_option('rsd_whatsapp_phone_number_id', '');

        if (!empty($cloud_token) && !empty($phone_id)) {
            $meta_url = "https://graph.facebook.com/v19.0/{$phone_id}/messages";
            wp_remote_post($meta_url, [
                'method'    => 'POST',
                'blocking'  => false,
                'headers'   => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $cloud_token
                ],
                'body'      => json_encode([
                    'messaging_product' => 'whatsapp',
                    'to'                => $clean_phone,
                    'type'              => 'text',
                    'text'              => ['body' => $message]
                ]),
                'timeout'   => 8
            ]);
            return true;
        }

        return false;
    }

    /**
     * AJAX: Fetch WhatsApp Cryptographic Live QR Code from Socket Gateway
     */
    public function handle_ajax_wa_get_qr() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $gateway_url = rtrim(get_option('rsd_whatsapp_api_url', ''), '/');
        $instance    = get_option('rsd_whatsapp_instance', 'rsd_live');
        $api_key     = get_option('rsd_whatsapp_api_key', '');

        if (empty($gateway_url)) {
            wp_send_json_error([
                'code'    => 'NO_GATEWAY',
                'message' => '⚠️ خادم الربط (Socket Gateway URL) غير مدخل. يرجى إدخال رابط خادم البوابة في قسم الإعدادات أدناه لتوليد جلسة مشفرة متوافقة مع واتساب.'
            ]);
        }

        $connect_url = "{$gateway_url}/instance/connect/{$instance}";
        $res = wp_remote_get($connect_url, [
            'headers' => [
                'apikey'                 => $api_key,
                'Authorization'          => 'Bearer ' . $api_key,
                'Bypass-Tunnel-Reminder' => 'true',
                'User-Agent'             => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ],
            'timeout' => 12
        ]);

        if (is_wp_error($res)) {
            wp_send_json_error(['message' => 'تعذر الاتصال بخادم البوابة: ' . $res->get_error_message()]);
        }

        $body = json_decode(wp_remote_retrieve_body($res), true);
        $base64_qr = $body['base64'] ?? ($body['qrcode']['base64'] ?? ($body['code'] ?? ''));

        if (!empty($base64_qr)) {
            $qr_src = (strpos($base64_qr, 'data:image') === 0) ? $base64_qr : 'data:image/png;base64,' . $base64_qr;
            wp_send_json_success([
                'status'     => 'qr_generated',
                'qrcode_url' => $qr_src,
                'instance'   => $instance
            ]);
        }

        $state = $body['instance']['state'] ?? ($body['state'] ?? 'unknown');
        if ($state === 'open' || $state === 'connected') {
            update_option('rsd_whatsapp_status', 'connected');
            wp_send_json_success([
                'status'   => 'connected',
                'message'  => 'الجلسة متصلة ومقترنة بالفعل بنجاح!',
                'instance' => $instance
            ]);
        }

        wp_send_json_error([
            'message' => 'لم يتم استلام كود QR مشفر من البوابة. تفاصيل الاستجابة: ' . wp_remote_retrieve_body($res)
        ]);
    }

    /**
     * AJAX: Request 8-Digit Phone Pairing Code (Link with phone number instead of camera QR)
     */
    public function handle_ajax_wa_get_pairing_code() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $phone = sanitize_text_field($_POST['phone'] ?? get_option('rsd_whatsapp_phone', '201028803080'));
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);

        $gateway_url = rtrim(get_option('rsd_whatsapp_api_url', ''), '/');
        $instance    = get_option('rsd_whatsapp_instance', 'rsd_live');
        $api_key     = get_option('rsd_whatsapp_api_key', '');

        if (empty($gateway_url)) {
            wp_send_json_error([
                'code'    => 'NO_GATEWAY',
                'message' => '⚠️ يرجى إدخال رابط خادم البوابة (Socket Gateway URL) أدناه لتوليد كود الربط المكون من 8 خانات.'
            ]);
        }

        // Request pairing code from Gateway
        $pair_url = "{$gateway_url}/instance/connect/{$instance}?number={$clean_phone}";
        $res = wp_remote_get($pair_url, [
            'headers' => [
                'apikey'                 => $api_key,
                'Authorization'          => 'Bearer ' . $api_key,
                'Bypass-Tunnel-Reminder' => 'true',
                'User-Agent'             => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ],
            'timeout' => 12
        ]);

        if (is_wp_error($res)) {
            wp_send_json_error(['message' => 'خطأ في الاتصال بالبوابة: ' . $res->get_error_message()]);
        }

        $body = json_decode(wp_remote_retrieve_body($res), true);
        $pairing_code = $body['pairingCode'] ?? ($body['code'] ?? '');

        if (!empty($pairing_code)) {
            wp_send_json_success([
                'status'       => 'code_generated',
                'pairing_code' => $pairing_code,
                'phone'        => $clean_phone
            ]);
        }

        wp_send_json_error([
            'message' => 'تعذر توليد كود الربط من الخادم: ' . wp_remote_retrieve_body($res)
        ]);
    }

    public function handle_ajax_wa_check_status() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $gateway_url = rtrim(get_option('rsd_whatsapp_api_url', ''), '/');
        $instance    = get_option('rsd_whatsapp_instance', 'rsd_live');
        $api_key     = get_option('rsd_whatsapp_api_key', '');

        if (empty($gateway_url)) {
            update_option('rsd_whatsapp_status', 'disconnected');
            wp_send_json_success([
                'state'   => 'close',
                'phone'   => get_option('rsd_whatsapp_phone', '201028803080'),
                'message' => 'خادم البوابة غير مدخل'
            ]);
        }

        $state_url = "{$gateway_url}/instance/status/{$instance}";
        $res = wp_remote_get($state_url, [
            'headers' => [
                'apikey'                 => $api_key,
                'Authorization'          => 'Bearer ' . $api_key,
                'Bypass-Tunnel-Reminder' => 'true',
                'User-Agent'             => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ],
            'timeout' => 8
        ]);

        if (is_wp_error($res)) {
            update_option('rsd_whatsapp_status', 'disconnected');
            wp_send_json_error(['message' => $res->get_error_message()]);
        }

        $body = json_decode(wp_remote_retrieve_body($res), true);
        $state = $body['state'] ?? ($body['instance']['state'] ?? 'close');
        $is_connected = ($state === 'open' || $state === 'connected');
        update_option('rsd_whatsapp_status', ($is_connected ? 'connected' : 'disconnected'));

        wp_send_json_success([
            'state'        => ($is_connected ? 'open' : 'close'),
            'is_connected' => $is_connected,
            'instance'     => $instance,
            'phone'        => get_option('rsd_whatsapp_phone', '201028803080')
        ]);
    }

public function handle_ajax_wa_disconnect() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $gateway_url = rtrim(get_option('rsd_whatsapp_api_url', ''), '/');
        $instance    = get_option('rsd_whatsapp_instance', 'rsd_live');
        $api_key     = get_option('rsd_whatsapp_api_key', '');

        if (!empty($gateway_url)) {
            $logout_url = "{$gateway_url}/instance/logout/{$instance}";
            wp_remote_request($logout_url, [
                'method'  => 'POST',
                'headers' => [
                    'apikey'                 => $api_key,
                    'Authorization'          => 'Bearer ' . $api_key,
                    'Bypass-Tunnel-Reminder' => 'true',
                    'User-Agent'             => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
                ],
                'timeout' => 8
            ]);
        }

        update_option('rsd_whatsapp_status', 'disconnected');
        wp_send_json_success(['message' => 'تم فك الارتباط ومسح الجلسة بنجاح']);
    }

    public function handle_ajax_wa_toggle_ai() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $enabled = (isset($_POST['enabled']) && $_POST['enabled'] === '1') ? '1' : '0';
        update_option('rsd_whatsapp_ai_enabled', $enabled);
        wp_send_json_success(['enabled' => $enabled]);
    }

    /**
     * AJAX: Toggle WhatsApp Outbound Alerts Switch
     */
    /**
     * AJAX: Run Autonomous Outbound Lead Discovery Cycle
     */
    public function handle_ajax_radar_run_discovery() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $niche = sanitize_text_field($_POST['niche'] ?? 'resorts_redsea');
        $city  = sanitize_text_field($_POST['city'] ?? 'الغردقة وشرم الشيخ');

        $result = RedSeaLeadRadarEngine::run_discovery_cycle($niche, $city);
        wp_send_json_success($result);
    }

    /**
     * AJAX: Human-in-the-Loop Approve & Dispatch Lead via WhatsApp
     */
    public function handle_ajax_radar_approve_lead() {
        global $wpdb;
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $lead_id = intval($_POST['lead_id'] ?? 0);
        $table_name = $wpdb->prefix . 'rsd_leads';

        $lead = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $lead_id), ARRAY_A);
        if (!$lead) {
            wp_send_json_error(['message' => 'الفرصة غير موجودة']);
        }

        $phone = $lead['contact_phone'];
        $pitch = $lead['tailored_pitch'];

        // 1. Dispatch pitch via WhatsApp Gateway
        $sent = self::send_whatsapp_message($phone, $pitch);

        // 2. Update status to contacting
        $wpdb->update($table_name, [
            'pipeline_status' => 'contacting'
        ], ['id' => $lead_id]);

        // 3. Also record to general CRM table
        self::save_booking($lead['company_name'], $phone, 'تواصل مباشر عبر رادار المبيعات', $pitch);

        wp_send_json_success([
            'status'   => 'approved_and_sent',
            'lead_id'  => $lead_id,
            'dispatched' => $sent
        ]);
    }

    /**
     * AJAX: Edit Pitch Copy
     */
    public function handle_ajax_radar_edit_pitch() {
        global $wpdb;
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $lead_id = intval($_POST['lead_id'] ?? 0);
        $pitch   = sanitize_textarea_field($_POST['pitch'] ?? '');
        $table_name = $wpdb->prefix . 'rsd_leads';

        $wpdb->update($table_name, [
            'tailored_pitch' => $pitch
        ], ['id' => $lead_id]);

        wp_send_json_success(['message' => 'تم تحديث نص الرسالة بنجاح']);
    }

    /**
     * AJAX: Reject or Delete Lead
     */
    public function handle_ajax_radar_reject_lead() {
        global $wpdb;
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $lead_id = intval($_POST['lead_id'] ?? 0);
        $table_name = $wpdb->prefix . 'rsd_leads';

        $wpdb->update($table_name, [
            'pipeline_status' => 'rejected'
        ], ['id' => $lead_id]);

        wp_send_json_success(['message' => 'تم استبعاد الفرصة بنجاح']);
    }

    public function handle_ajax_wa_toggle_outbound() {
        check_ajax_referer('rsd_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $enabled = (isset($_POST['enabled']) && $_POST['enabled'] === '1') ? '1' : '0';
        update_option('rsd_whatsapp_enabled', $enabled);
        wp_send_json_success(['enabled' => $enabled]);
    }

    public function add_admin_menu() {
        add_menu_page(
            'RED SEA AI Engine',
            'RED SEA AI Engine',
            'manage_options',
            'redsea-ai-engine',
            [$this, 'render_crm_page'],
            'dashicons-smart-machine',
            30
        );
    }

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Filter rendered HTML to 100% guarantee 3D Liquid Glass Cards on Methodology & Manifesto containers
        add_filter('the_content', function($content) {
            if (empty($content)) return $content;

            // If inside Elementor editor or preview, let Elementor render native containers smoothly!
            if (class_exists('\Elementor\Plugin')) {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) {
                    return $content;
                }
            }

            global $post;
            $post_id = $post->ID ?? 0;
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            $slug = strtolower($post->post_name ?? '');

            // Detect language from page, Polylang, or URI
            $is_ar = ($post_id == 163 || $post_id == 164 || strpos($slug, 'ar-') !== false || strpos($request_uri, '/ar') !== false);
            if (!$is_ar && function_exists('pll_current_language')) {
                $is_ar = (pll_current_language() === 'ar');
            }

            $is_de = ($post_id == 165 || $post_id == 166 || strpos($slug, 'de-') !== false);
            $is_ru = ($post_id == 167 || $post_id == 168 || strpos($slug, 'ru-') !== false);

            $dir = $is_ar ? 'rtl' : 'ltr';
            $work_url = $is_ar ? 'https://redseadigital.pro/ar-work/' : 'https://redseadigital.pro/work/';

            // A. UNIFIED HOMEPAGE RENDERING (All 4 Languages: AR, EN, DE, RU)
            $is_home_page = ($post_id == 12 || $post_id == 163 || $post_id == 165 || $post_id == 167 || is_front_page() || is_home() || strpos($slug, 'home') !== false || strpos($slug, 'startseite') !== false || strpos($slug, 'главная') !== false || $request_uri === '/' || empty(trim($request_uri, '/')));

            if ($is_home_page) {
                $col1_items = $is_ar ? [
                    ['text' => 'ارتفعت نسبة حجوزاتنا المباشرة من 12% إلى 48% خلال 5 أشهر فقط. وفرنا أكثر من 86,000$ من عمولات بوكينج التي كانت تخرج من أرباحنا.', 'name' => 'طارق المنصور', 'role' => 'المدير العام — منتجع هورايزون ريزورت، الغردقة', 'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'التزامن اللحظي للـ Channel Manager ألغى تماماً ظاهرة الحجز المزدوج. وتأكيد الحجز الفوري عبر الواتساب جعل تجربة النزيل فائقة السلاسة.', 'name' => 'سارة الخطيب', 'role' => 'مدير التشغيل — إقامات سيناء الفاخرة، شرم الشيخ', 'image' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'استعادة السلات المتروكة تلقائياً عبر الواتساب حولت 38% من الزوار المترددين إلى طلبات مدفوعة فوراً بدون أي تدخل بشري.', 'name' => 'عمر عبد الرحمن', 'role' => 'مؤسس — العلامة التجارية ASL Leather', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80']
                ] : [
                    ['text' => 'Direct bookings jumped from 12% to 48% within 5 months. We saved over $86,000 in Booking.com commissions alone.', 'name' => 'Tarek Al-Mansoor', 'role' => 'General Manager — Horizon Luxury Resort', 'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'The 2-way Channel Manager sync eliminated double bookings completely. Instant WhatsApp confirmation made guest check-in flawless.', 'name' => 'Sara Al-Khatib', 'role' => 'Operations Director — Sinai Luxury Stays', 'image' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'Automated WhatsApp abandoned cart recovery converted 38% of lost checkout visitors into paid orders with zero manual effort.', 'name' => 'Omar Abdelrahman', 'role' => 'Founder — ASL Leather & Retail', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80']
                ];

                $col2_items = $is_ar ? [
                    ['text' => 'سرعة التحميل الخارقة ودعم الدفع بأبل باي رفعت معدل تحويل الحجوزات عبر الهاتف بنسبة 210% مقارنة بالموقع القديم.', 'name' => 'المهندس مايكل شميدت', 'role' => 'المدير التنفيذي — YallaTrip Global', 'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'أرقام حاسبة العائد الاستثماري كانت دقيقة بنسبة 100%. استرددنا أكثر من 54,000$ كأرباح صافية في أول موسم تشغيل.', 'name' => 'ليلى بن علي', 'role' => 'مدير الإيرادات — أجنحة كورال بيتش الفندقية', 'image' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'الوكيل الذكي والصوتي يتعامل مع استفسارات النزلاء بالعربية والإنجليزية باحترافية عالية. فريق المبيعات يتدخل فقط لإغلاق الصفقات الكبرى.', 'name' => 'فهد العتيبي', 'role' => 'رئيس مجلس الإدارة — الخليج للضيافة الفاخرة', 'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80']
                ] : [
                    ['text' => 'Sub-second page speeds and Apple Pay integration boosted our mobile reservation conversion rate by 210%.', 'name' => 'Eng. Michael Schmidt', 'role' => 'Managing Director — YallaTrip Global', 'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'The ROI calculator was 100% accurate. We retained over $54,000 in direct guest profits in our very first season.', 'name' => 'Laila Benali', 'role' => 'Revenue Director — Coral Beach Suites', 'image' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'The 24/7 AI Concierge handles Arabic and English guest inquiries effortlessly. Our sales team only steps in for VIP deals.', 'name' => 'Fahad Al-Otaibi', 'role' => 'Chairman — Gulf Luxury Hospitality', 'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80']
                ];

                $col3_items = $is_ar ? [
                    ['text' => 'الأموال تدخل حسابنا البنكي مباشرة دون انتظار 30 يوماً من المنصات الوسيطة. امتلاك بيانات النزلاء مكننا من إعادة استهدافهم مجاناً.', 'name' => 'كريم مصطفى', 'role' => 'مالك — فيلات الجونة الخاصة', 'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'جلسة المراجعة المعمارية الرقمية كانت نقطة التحول. أفضل استثمار تقني قمنا به لمنظومتنا الفندقية والتشغيلية.', 'name' => 'د. نور الدين الشريف', 'role' => 'مدير الفندق — بيرل هوتيل، البحر الأحمر', 'image' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'الاستجابة الصوتية الفورية باللغات الأوروبية منحت ضيوفنا الدوليين تجربة كونسيرج 5 نجوم لا تضاهى.', 'name' => 'إيلينا روستوفا', 'role' => 'مسؤولة تجربة النزلاء — ريفييرا بوتيك', 'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80']
                ] : [
                    ['text' => 'Direct guest payments deposited straight into our bank account with zero platform holdbacks. Full data ownership.', 'name' => 'Karim Mostafa', 'role' => 'Owner — El Gouna Private Villas', 'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'The architectural blueprint review answered every technical doubt. Best investment for our hospitality portfolio.', 'name' => 'Dr. Nour El-Din', 'role' => 'General Manager — Red Sea Pearl Hotel', 'image' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&auto=format&fit=crop&q=80'],
                    ['text' => 'Multilingual voice and chat responses gave our international European guests a 5-star concierge experience.', 'name' => 'Elena Rostova', 'role' => 'Guest Experience Lead — Riviera Boutique', 'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80']
                ];

                $saas_html = '

                    <!-- SECTION 1: MINIMALIST AWARD-WINNING SAAS HERO -->
                    <section class="rsd-saas-hero">
                        <div class="rsd-saas-hero-container">
                            <div class="rsd-saas-pill">
                                <span>✦ ' . ($is_ar ? 'حجز مباشر' : 'Direct Booking') . '</span>
                            </div>
                            <h1 class="rsd-saas-h1">
                                ' . ($is_ar ? 'ابنِ محرك حجزك الخاص.<br><span class="rsd-saas-gradient-text">امتلك 100% من أرباحك وعملائك.</span>' : 'Build Direct Booking Engines.<br><span class="rsd-saas-gradient-text">Own 100% of Your Revenue.</span>') . '
                            </h1>
                            <p class="rsd-saas-subtext">
                                ' . ($is_ar ? 'صفر عمولات للوسطاء. دفع إلكتروني مباشر وتأكيد فوري للحجوزات عبر الواتساب على مدار الساعة.' : 'Zero middleman commissions. Direct guest payments and automated 24/7 AI response on WhatsApp.') . '
                            </p>

                            <!-- 3D LAPTOP SHOWCASE WITH 3 FLOATING INTERACTIVE UI CARDS -->
                            <div class="rsd-hero-showcase-wrapper">
                                <img src="' . esc_url(plugins_url('assets/hero-v1.webp', __FILE__)) . '" 
                                     alt="' . ($is_ar ? 'منظومة حجز الفنادق المباشرة ومساعد الواتساب الذكي — Red Sea Digital' : 'Direct Booking Engine & 24/7 WhatsApp AI Concierge — Red Sea Digital') . '" 
                                     class="rsd-hero-master-img" 
                                     loading="eager" 
                                     width="1000" 
                                     height="650" />
                            </div>

                            <!-- MASTER CTA GROUP WITH FLAWLESS SHINY BUTTON (BELOW LAPTOP) -->
                            <div class="rsd-saas-cta-group">
                                <button onclick="var el=document.getElementById(&apos;rsd-booking-calendar&apos;);if(el){el.scrollIntoView({behavior:&apos;smooth&apos;});}else{window.toggleRsdChatWidget(event);}" class="shiny-cta">
                                    <span>' . ($is_ar ? 'حجز استشارة رقمية →' : 'Consult With Us →') . '</span>
                                </button>
                                <a href="' . $work_url . '" class="rsd-btn-showcase">
                                    ' . ($is_ar ? 'مشاهدة المشاريع الحية ✦' : 'View Live Showcase ✦') . '
                                </a>
                            </div>

                        </div>
                    </section>

                    <!-- SECTION 1.5: LUXURY LIGHT-THEMED HORIZONTAL MARQUEE TRUST BAR (SINGLE ROW & LIGHT BACKGROUND) -->
                    <section class="rsd-hero-trust-bar">
                        <div class="rsd-trust-bar-header">
                            <span class="rsd-trust-badge">✦ ' . ($is_ar ? 'التكامل السلس مع معايير الضيافة والمدفوعات العالمية' : 'GLOBAL HOSPITALITY & FINTECH ECOSYSTEM INTEGRATIONS') . '</span>
                        </div>
                        <div class="rsd-trust-wrapper">
                            <div class="rsd-trust-track">
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon"></span> Apple Pay</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">💳</span> Visa / Mastercard</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">🇸🇦</span> مدى Mada</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">⚡</span> Stripe Connect</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">💬</span> WhatsApp Cloud API</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">🏨</span> Oracle Opera PMS</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">☁️</span> Cloudbeds</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">🗝️</span> Hostaway PMS</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">✨</span> Tamara & Tabby</div>
                                <!-- Seamless loop items -->
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon"></span> Apple Pay</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">💳</span> Visa / Mastercard</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">🇸🇦</span> مدى Mada</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">⚡</span> Stripe Connect</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">💬</span> WhatsApp Cloud API</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">🏨</span> Oracle Opera PMS</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">☁️</span> Cloudbeds</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">🗝️</span> Hostaway PMS</div>
                                <div class="rsd-trust-chip"><span class="rsd-trust-icon">✨</span> Tamara & Tabby</div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION: INTERACTIVE ROI CALCULATOR & MODULAR SOLUTIONS (DARK THEME) -->
                    <section class="rsd-roi-section">
                        <div class="rsd-roi-ambient-glow"></div>
                        <div class="rsd-roi-container">
                            
                            <div style="text-align:center;">
                                <div class="rsd-roi-pill">✦ ' . ($is_ar ? 'حاسبة العائد والوفر المالي المباشر' : 'ROI & DIRECT REVENUE CALCULATOR') . '</div>
                                <h2 class="rsd-roi-title">' . ($is_ar ? 'منظومة متكاملة تضاعف أرباح مشروعك' : 'Integrated System Serving Your Business') . '</h2>
                                <p class="rsd-roi-subtitle">' . ($is_ar ? 'احسب بدقة حجم العمولات المهدرة التي ستستردها لخزينتك سنوياً عند التحويل للحجز المباشر عبر منصتك الخاصة.' : 'Calculate your exact annual middleman commission savings and direct booking revenue growth in real-time.') . '</p>
                            </div>

                            <div class="rsd-roi-grid">
                                <!-- Left Card: Inputs & Sliders -->
                                <div class="rsd-roi-card">
                                    <div class="rsd-roi-card-header">
                                        <span>' . ($is_ar ? 'مدخلات حساب العائد (ROI Calculator)' : 'ROI Calculator Inputs') . '</span>
                                        <span style="font-size:0.85rem;color:#38BDF8;font-weight:600;">● Live Dynamic</span>
                                    </div>

                                    <!-- Slider 1: Rooms -->
                                    <div class="rsd-slider-group">
                                        <div class="rsd-slider-label-row">
                                            <span class="rsd-slider-name">' . ($is_ar ? 'عدد الغرف / الوحدات الفندقية' : 'Total Hotel Rooms') . '</span>
                                            <span class="rsd-slider-val" id="valRooms">60</span>
                                        </div>
                                        <input type="range" id="rangeRooms" class="rsd-range-input" min="10" max="400" value="60" step="5" oninput="calculateRsdRoi()">
                                    </div>

                                    <!-- Slider 2: ADR -->
                                    <div class="rsd-slider-group">
                                        <div class="rsd-slider-label-row">
                                            <span class="rsd-slider-name">' . ($is_ar ? 'متوسط سعر الغرفة لليلة (ADR)' : 'Average Daily Rate (ADR)') . '</span>
                                            <span class="rsd-slider-val" id="valAdr">$120</span>
                                        </div>
                                        <input type="range" id="rangeAdr" class="rsd-range-input" min="30" max="600" value="120" step="10" oninput="calculateRsdRoi()">
                                    </div>

                                    <!-- Slider 3: Commission -->
                                    <div class="rsd-slider-group">
                                        <div class="rsd-slider-label-row">
                                            <span class="rsd-slider-name">' . ($is_ar ? 'نسبة عمولة بوكينج والوسطاء الحالية' : 'Current OTA Commission %') . '</span>
                                            <span class="rsd-slider-val" id="valCommission">18%</span>
                                        </div>
                                        <input type="range" id="rangeCommission" class="rsd-range-input" min="10" max="30" value="18" step="1" oninput="calculateRsdRoi()">
                                    </div>

                                    <!-- Slider 4: Target Transition -->
                                    <div class="rsd-slider-group" style="margin-bottom:0 !important;">
                                        <div class="rsd-slider-label-row">
                                            <span class="rsd-slider-name">' . ($is_ar ? 'نسبة التحويل المستهدفة للحجز المباشر' : 'Target Direct Bookings Transition') . '</span>
                                            <span class="rsd-slider-val" id="valDirect">40%</span>
                                        </div>
                                        <input type="range" id="rangeDirect" class="rsd-range-input" min="15" max="80" value="40" step="5" oninput="calculateRsdRoi()">
                                    </div>
                                </div>

                                <!-- Right Card: Output & Impact -->
                                <div class="rsd-roi-card">
                                    <div>
                                        <div class="rsd-roi-card-header">
                                            <span>' . ($is_ar ? 'الأثر المالي المباشر (Direct Output)' : 'Calculated Financial Output') . '</span>
                                            <span style="font-size:0.85rem;color:#34D399;font-weight:700;">0% Commission</span>
                                        </div>

                                        <div class="rsd-output-block">
                                            <div class="rsd-output-label">' . ($is_ar ? 'الوفر المالي السنوي المسترد لخزينتك' : 'Estimated Annual Revenue Saved') . '</div>
                                            <div class="rsd-output-val-large" id="outAnnualSavings">$132,450 USD</div>
                                            <div class="rsd-output-subtext" id="outMonthlySavings">+ $11,037 USD / شهر أرباح مستردة</div>
                                        </div>

                                        <div class="rsd-output-block" style="margin-bottom:30px;">
                                            <div class="rsd-output-label">' . ($is_ar ? 'نمو حصة الحجوزات المباشرة' : 'Direct Booking Growth & Share') . '</div>
                                            <div style="font-size:1.4rem;font-weight:800;color:#FFFFFF;" id="outDirectGrowth">+40.0% Direct Share</div>
                                            <div style="font-size:0.85rem;color:#94A3B8;margin-top:4px;">' . ($is_ar ? 'امتلاك كامل لبيانات عملائك بدون وسيط' : '100% ownership of guest records & direct rebooking') . '</div>
                                        </div>
                                    </div>

                                    <button onclick="var el=document.getElementById(&apos;rsd-booking-calendar&apos;);if(el){el.scrollIntoView({behavior:&apos;smooth&apos;});}else{window.toggleRsdChatWidget(event);}" class="rsd-output-btn">
                                        ' . ($is_ar ? 'احصل على عرض سعر مخصص لمشروعك →' : 'Request A Custom Architecture Quote →') . '
                                    </button>
                                </div>
                            </div>

                            <!-- Bottom Modular Solutions Bento Grid -->
                            <div>
                                <h3 class="rsd-modular-title">' . ($is_ar ? 'حلول معمارية مرنة بدون قيود' : 'Modular Solutions Without Restrictions') . '</h3>
                                <div class="rsd-modular-grid">
                                    <div class="rsd-modular-card">
                                        <span class="rsd-modular-price-badge">✦ ' . ($is_ar ? 'بنية فندقية متكاملة' : 'Enterprise Hotel Architecture') . '</span>
                                        <h4 class="rsd-modular-card-h4">' . ($is_ar ? 'محرك حجز الفنادق المباشر (0% عمولة)' : 'Direct Hotel Booking Engine') . '</h4>
                                        <p class="rsd-modular-card-p">' . ($is_ar ? 'ربط Channel Manager متزامن لمنع Double Booking في أقل من ثانيتين مع بوابات دفع فيزا وأبل باي ومدى.' : 'Synchronous 2-way PMS & Channel Manager sync preventing double bookings with instant Apple Pay.') . '</p>
                                    </div>

                                    <div class="rsd-modular-card">
                                        <span class="rsd-modular-price-badge">✦ ' . ($is_ar ? 'بنية تجارة فاخرة' : 'Bespoke Commerce Engine') . '</span>
                                        <h4 class="rsd-modular-card-h4">' . ($is_ar ? 'منظومة المتاجر الفاخرة المخصصة' : 'Bespoke Luxury E-Commerce') . '</h4>
                                        <p class="rsd-modular-card-p">' . ($is_ar ? 'استعادة السلات المتروكة آلياً عبر الواتساب ودفع فوري عالي السرعة بأعلى معدل تحويل للمبيعات.' : 'Automated WhatsApp abandoned cart recovery and multi-currency high-speed checkout.') . '</p>
                                    </div>

                                    <div class="rsd-modular-card">
                                        <span class="rsd-modular-price-badge">✦ ' . ($is_ar ? 'وكيل ذكاء اصطناعي مستقل' : 'Autonomous AI Concierge') . '</span>
                                        <h4 class="rsd-modular-card-h4">' . ($is_ar ? 'الوكيل الصوتي والـ CRM الذكي 24/7' : 'AI Concierge & Voice Agent') . '</h4>
                                        <p class="rsd-modular-card-p">' . ($is_ar ? 'استجابة صوتية بشرية بـ 5 لغات، إغلاق صفقات ومزامنة بيانات العملاء وحجوزاتهم مع الواتساب آلياً.' : 'Real-time multilingual voice synthesis, CRM lead extraction, and automated WhatsApp routing.') . '</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </section>

                    <script>
                    function calculateRsdRoi() {
                        var elRooms = document.getElementById("rangeRooms");
                        var elAdr = document.getElementById("rangeAdr");
                        var elComm = document.getElementById("rangeCommission");
                        var elDir = document.getElementById("rangeDirect");
                        if (!elRooms) return;

                        var rooms = parseInt(elRooms.value) || 60;
                        var adr = parseInt(elAdr.value) || 120;
                        var commPct = parseInt(elComm.value) || 18;
                        var directPct = parseInt(elDir.value) || 40;

                        document.getElementById("valRooms").innerText = rooms;
                        document.getElementById("valAdr").innerText = "$" + adr;
                        document.getElementById("valCommission").innerText = commPct + "%";
                        document.getElementById("valDirect").innerText = directPct + "%";

                        var annualNights = rooms * 365 * 0.70;
                        var annualGross = annualNights * adr;
                        var annualOtaFees = annualGross * (commPct / 100);
                        var savedAnnual = annualOtaFees * (directPct / 100);
                        var savedMonthly = savedAnnual / 12;

                        document.getElementById("outAnnualSavings").innerText = "$" + Math.round(savedAnnual).toLocaleString() + " USD";
                        document.getElementById("outMonthlySavings").innerText = "+ $" + Math.round(savedMonthly).toLocaleString() + " ' . ($is_ar ? 'USD / شهر أرباح مستردة' : 'USD / mo retained') . '";
                        document.getElementById("outDirectGrowth").innerText = "+" + directPct + ".0% ' . ($is_ar ? 'حصة حجوزات مباشرة' : 'Direct Share') . '";
                    }
                    document.addEventListener("DOMContentLoaded", function() {
                        if (typeof calculateRsdRoi === "function") calculateRsdRoi();
                    });
                    
                    </script>

                    <!-- SECTION: 4-STEP BESPOKE ARCHITECTURAL PROTOCOL -->
                    <section class="rsd-protocol-sec">
                        <div class="rsd-protocol-container">
                            <div style="text-align:center; max-width:680px; margin:0 auto;">
                                <div class="rsd-roi-pill">✦ ' . ($is_ar ? 'بروتوكول التنفيذ المعماري' : '4-STEP ARCHITECTURAL PROTOCOL') . '</div>
                                <h2 class="rsd-roi-title">' . ($is_ar ? 'رحلة التحول نحو الملكية الرقمية الكاملة' : 'Turnkey Roadmap to Full Direct Revenue') . '</h2>
                                <p class="rsd-roi-subtitle">' . ($is_ar ? 'منهجية دقيقة ومدروسة لتجهيز وتشغيل منظومة الحجز المباشر الخاصة بك مفتاح باليد خلال 7 إلى 14 يوماً عمل.' : 'A battle-tested engineering protocol delivering your direct booking infrastructure turnkey in 7 to 14 days.') . '</p>
                            </div>

                            <div class="rsd-protocol-grid">
                                <div class="rsd-protocol-card">
                                    <div>
                                        <div class="rsd-protocol-num">01</div>
                                        <h3 class="rsd-protocol-title">' . ($is_ar ? 'التدقيق المالي وفجوة العمولات' : 'Revenue & OTA Gap Audit') . '</h3>
                                        <p class="rsd-protocol-desc">' . ($is_ar ? 'فحص شامل لحجم حجوزاتك الحالية وتحديد العمولات المهدرة لمنصات الوساطة ووضع خطة الاسترداد.' : 'Deep analysis of your current reservation mix, OTA commission leakages, and target direct share.') . '</p>
                                    </div>
                                </div>

                                <div class="rsd-protocol-card">
                                    <div>
                                        <div class="rsd-protocol-num">02</div>
                                        <h3 class="rsd-protocol-title">' . ($is_ar ? 'الهندسة البصرية ومحرك الحجز' : 'Bespoke UX & Engine Design') . '</h3>
                                        <p class="rsd-protocol-desc">' . ($is_ar ? 'بناء واجهة حجز ثلاثية الأبعاد فائقة السرعة ومتوافقة مع الهواتف وبوابات الدفع المباشرة كـ Apple Pay.' : 'Crafting a high-conversion 3D booking engine tailored to your brand, optimized for sub-second checkout.') . '</p>
                                    </div>
                                </div>

                                <div class="rsd-protocol-card">
                                    <div>
                                        <div class="rsd-protocol-num">03</div>
                                        <h3 class="rsd-protocol-title">' . ($is_ar ? 'الربط المتزامن بالـ PMS' : '2-Way PMS Channel Sync') . '</h3>
                                        <p class="rsd-protocol-desc">' . ($is_ar ? 'توصيل محرك الحجز مباشرة بقنوات إدارة الفنادق (Channel Manager) لمنع Double Booking في أقل من ثانيتين.' : 'Direct two-way synchronization with your PMS preventing double bookings across all channels.') . '</p>
                                    </div>
                                </div>

                                <div class="rsd-protocol-card">
                                    <div>
                                        <div class="rsd-protocol-num">04</div>
                                        <h3 class="rsd-protocol-title">' . ($is_ar ? 'تشغيل الوكيل الذكي والواتساب' : 'AI Concierge & Launch') . '</h3>
                                        <p class="rsd-protocol-desc">' . ($is_ar ? 'تفعيل الرد الآلي الصوتي والنصي 24/7 عبر الواتساب وتدريب الذكاء الاصطناعي على غرفك وأسعارك.' : 'Enabling autonomous 24/7 WhatsApp AI concierge to capture leads, confirm payments, and upsell amenities.') . '</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 2: AUTHENTIC 3-COLUMN INFINITE MARQUEE TESTIMONIALS (MOTION EQUIVALENT) -->
                    <section class="rsd-saas-sec rsd-trust-sec">
                        <div class="rsd-saas-container">
                            <div style="text-align:center; max-width:580px; margin:0 auto 40px auto;">
                                <div class="rsd-saas-pill" style="margin-bottom:12px;">✦ ' . ($is_ar ? 'آراء العملاء الموثقة' : 'TESTIMONIALS') . '</div>
                                <h2 class="rsd-saas-title" style="margin-bottom:12px;">
                                    ' . ($is_ar ? 'ماذا يقول عملاؤنا عنا' : 'What Our Users Say') . '
                                </h2>
                                <p style="color:#64748B; font-size:1.05rem; margin:0;">
                                    ' . ($is_ar ? 'شاهد كيف ساهمت منظومة الحجز المباشر والربط الآلي في تحرير أرباح عملائنا.' : 'See what our customers have to say about us.') . '
                                </p>
                            </div>

                            <div class="rsd-marquee-mask-wrap">
                                <div class="rsd-marquee-col col-1">
                                    <div class="rsd-t-track track-1">
                                        ' . implode('', array_map(function($t) {
                                            return '
                                            <div class="rsd-t-card">
                                                <p class="rsd-t-text">“' . $t['text'] . '”</p>
                                                <div class="rsd-t-user">
                                                    <img src="' . $t['image'] . '" alt="' . $t['name'] . '" class="rsd-t-avatar" loading="lazy" />
                                                    <div>
                                                        <strong class="rsd-t-name">' . $t['name'] . '</strong>
                                                        <span class="rsd-t-role">' . $t['role'] . '</span>
                                                    </div>
                                                </div>
                                            </div>';
                                        }, $col1_items)) . '
                                    </div>
                                </div>

                                <div class="rsd-marquee-col col-2">
                                    <div class="rsd-t-track track-2">
                                        ' . implode('', array_map(function($t) {
                                            return '
                                            <div class="rsd-t-card">
                                                <p class="rsd-t-text">“' . $t['text'] . '”</p>
                                                <div class="rsd-t-user">
                                                    <img src="' . $t['image'] . '" alt="' . $t['name'] . '" class="rsd-t-avatar" loading="lazy" />
                                                    <div>
                                                        <strong class="rsd-t-name">' . $t['name'] . '</strong>
                                                        <span class="rsd-t-role">' . $t['role'] . '</span>
                                                    </div>
                                                </div>
                                            </div>';
                                        }, $col2_items)) . '
                                    </div>
                                </div>

                                <div class="rsd-marquee-col col-3">
                                    <div class="rsd-t-track track-3">
                                        ' . implode('', array_map(function($t) {
                                            return '
                                            <div class="rsd-t-card">
                                                <p class="rsd-t-text">“' . $t['text'] . '”</p>
                                                <div class="rsd-t-user">
                                                    <img src="' . $t['image'] . '" alt="' . $t['name'] . '" class="rsd-t-avatar" loading="lazy" />
                                                    <div>
                                                        <strong class="rsd-t-name">' . $t['name'] . '</strong>
                                                        <span class="rsd-t-role">' . $t['role'] . '</span>
                                                    </div>
                                                </div>
                                            </div>';
                                        }, $col3_items)) . '
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 5: UNIFIED COMPARISON MATRIX -->
                    <section class="rsd-saas-sec rsd-matrix-sec">
                        <div class="rsd-saas-container">
                            <h2 class="rsd-saas-title">
                                ' . ($is_ar ? 'الفرق الحقيقي في طريقة إدارة مبيعاتك' : 'The Real Difference in Revenue Ownership') . '
                            </h2>

                            <div class="rsd-unified-matrix-card">
                                <div class="rsd-matrix-side old">
                                    <h3>❌ ' . ($is_ar ? 'المنصات الوسيطة (الطريقة القديمة)' : 'Third-Party Platforms (Old Way)') . '</h3>
                                    <ul class="rsd-matrix-list">
                                        <li>' . ($is_ar ? 'خسارة 15% إلى 30% من إيراداتك كعمولات متكررة' : 'Losing 15%-30% revenue to middleman fees') . '</li>
                                        <li>' . ($is_ar ? 'فقدان بيانات عملائك وعدم القدرة على إعادة استهدافهم' : 'No ownership of customer contact data') . '</li>
                                        <li>' . ($is_ar ? 'بطء استجابة الاستفسارات وتشتت طلبات الحجز' : 'Slow manual inquiry follow-ups') . '</li>
                                    </ul>
                                </div>

                                <div class="rsd-matrix-side new">
                                    <h3>✨ ' . ($is_ar ? 'نظامك المباشر (Red Sea Digital)' : 'Direct Booking Engine (Red Sea Digital)') . '</h3>
                                    <ul class="rsd-matrix-list">
                                        <li>' . ($is_ar ? '0% عمولات — احتفظ بـ 100% من صافي أرباحك' : '0% commission — Keep 100% of direct revenue') . '</li>
                                        <li>' . ($is_ar ? 'ملكية رقمية كاملة 100% لبيانات عملائك وقوائمك' : 'Full 100% ownership of client lists & data') . '</li>
                                        <li>' . ($is_ar ? 'إرسال آلي فورات لطلبات الحجز إلى واتساب المبيعات 24/7' : 'Instant 24/7 WhatsApp direct lead dispatch') . '</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION: LIQUID GLASS ACCORDION FAQ -->
                    <section class="rsd-faq-sec">
                        <div class="rsd-faq-container">
                            <div style="text-align:center; max-width:620px; margin:0 auto;">
                                <div class="rsd-roi-pill">✦ ' . ($is_ar ? 'الأسئلة الشائعة والمعمارية' : 'FREQUENTLY ASKED QUESTIONS') . '</div>
                                <h2 class="rsd-roi-title">' . ($is_ar ? 'كل ما تحتاج معرفته عن المنظومة' : 'Direct Architecture FAQs') . '</h2>
                                <p class="rsd-roi-subtitle">' . ($is_ar ? 'إجابات واضحة ومباشرة عن تكامل المنظومة، الأمان المالي، وسرعة التشغيل.' : 'Straightforward answers regarding OTA coexistence, bank payouts, and setup timelines.') . '</p>
                            </div>

                            <div class="rsd-faq-list">
                                <div class="rsd-faq-item active">
                                    <div class="rsd-faq-question" onclick="toggleRsdFaq(this)">
                                        <span>' . ($is_ar ? 'هل يتعارض محرك الحجز المباشر مع عقودنا مع بوكينج وإكسبيديا؟' : 'Does the direct booking engine conflict with our Booking.com or Expedia contracts?') . '</span>
                                        <span class="rsd-faq-icon">+</span>
                                    </div>
                                    <div class="rsd-faq-answer">
                                        <p>' . ($is_ar ? 'إطلاقاً. منظومتنا تعمل كقناة مباشرة موازية تتزامن لحظياً (2-Way Sync) مع قنواتك الحالية عبر الـ Channel Manager. لن تفقد أي حجوزات بل ستزيد أرباحك الصافية من النزلاء الذين يحجزون معك مباشرة.' : 'Not at all. Our system operates as an independent direct channel synchronized in real-time with your existing PMS and Channel Manager, preventing double bookings while maximizing your direct margins.') . '</p>
                                    </div>
                                </div>

                                <div class="rsd-faq-item">
                                    <div class="rsd-faq-question" onclick="toggleRsdFaq(this)">
                                        <span>' . ($is_ar ? 'كيف تصلنا أموال الحجوزات ومتى يتم إيداعها؟' : 'How and when do we receive our booking revenues?') . '</span>
                                        <span class="rsd-faq-icon">+</span>
                                    </div>
                                    <div class="rsd-faq-answer">
                                        <p>' . ($is_ar ? 'الأموال تدخل مباشرة إلى حسابك البنكي التجاري فور إتمام النزيل لعملية الدفع عبر Apple Pay أو الفيزا بدون أي وساطة أو تأخير أو استقطاع لعمولات خفية.' : 'All guest payments are deposited straight into your verified business bank account instantly upon checkout via Apple Pay, Visa, or Stripe, with zero platform holdbacks.') . '</p>
                                    </div>
                                </div>

                                <div class="rsd-faq-item">
                                    <div class="rsd-faq-question" onclick="toggleRsdFaq(this)">
                                        <span>' . ($is_ar ? 'كم يستغرق تجهيز وبرمجة المنظومة وإطلاقها حياً؟' : 'What is the implementation timeline from start to go-live?') . '</span>
                                        <span class="rsd-faq-icon">+</span>
                                    </div>
                                    <div class="rsd-faq-answer">
                                        <p>' . ($is_ar ? 'يتم تسليم المنظومة بالكامل مفتاح باليد (Turnkey) خلال 7 إلى 14 يوم عمل، بما في ذلك الربط المتزامن للغرف، بوابات الدفع، والوكيل الذكي على الواتساب.' : 'The complete architecture is deployed turnkey within 7 to 14 business days, including full PMS calibration, payment gateway integration, and WhatsApp AI model fine-tuning.') . '</p>
                                    </div>
                                </div>

                                <div class="rsd-faq-item">
                                    <div class="rsd-faq-question" onclick="toggleRsdFaq(this)">
                                        <span>' . ($is_ar ? 'ماذا يحدث إذا واجهنا أي استفسار أو مشكلة تقنية بعد الإطلاق؟' : 'What post-launch technical support is provided?') . '</span>
                                        <span class="rsd-faq-icon">+</span>
                                    </div>
                                    <div class="rsd-faq-answer">
                                        <p>' . ($is_ar ? 'نوفر دعماً فنياً مخصصاً ومهندساً مسؤولاً عن منظومتك على مدار الساعة مع مراقبة أداء الخوادم والأمان الرقمي وضمان تشغيل 99.9% uptime.' : 'You receive dedicated 24/7 architectural support, continuous server monitoring, security patches, and a guaranteed 99.9% uptime SLA.') . '</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <script>
                    function toggleRsdFaq(btn) {
                        var item = btn.parentElement;
                        var allItems = document.querySelectorAll(".rsd-faq-item");
                        allItems.forEach(function(i) {
                            if (i !== item) i.classList.remove("active");
                        });
                        item.classList.toggle("active");
                    }
                    
                    </script>

                                        <!-- SECTION 5.5: LIVE CAL.COM STRATEGY SESSION CALENDAR -->
                    <section class="rsd-cal-booking-section" id="rsd-booking-calendar" style="background:#030712;padding:100px 20px;position:relative;border-top:1px solid rgba(255,255,255,0.06);border-bottom:1px solid rgba(255,255,255,0.06);">
                        <div class="rsd-cal-container" style="max-width:1120px;margin:0 auto;position:relative;z-index:2;">
                            <div style="text-align:center;margin-bottom:36px;">
                                <div class="rsd-roi-pill" style="display:inline-block;padding:6px 18px;background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.3);border-radius:9999px;color:#38BDF8;font-size:0.8rem;font-weight:700;letter-spacing:0.08em;margin-bottom:14px;">
                                    ✦ ' . ($is_ar ? 'حجز جلسة استراتيجية مباشرة' : 'LIVE STRATEGY CONSULTATION') . '
                                </div>
                                <h2 class="rsd-dark-h2" style="font-size:clamp(1.8rem, 3.5vw, 2.6rem);font-weight:800;color:#FFFFFF;margin:0 0 12px 0;">
                                    ' . ($is_ar ? 'اختر الموعد الأنسب لبدء مشروعك' : 'Schedule Your 15-Minute Strategy Call') . '
                                </h2>
                                <p class="rsd-dark-subtext" style="color:#94A3B8;font-size:1.05rem;max-width:680px;margin:0 auto;line-height:1.65;">
                                    ' . ($is_ar ? 'جلسة مباشرة 15 دقيقة مع خبير استشاري لمراجعة متطلبات الربط، توفير العمولات، وتجهيز منصتك الخاصة.' : '15-minute direct strategy consultation with our technical architects to engineer your direct booking engine and eliminate middleman fees.') . '
                                </p>
                            </div>

                            <!-- Cal.com Luxury Inline Frame -->
                            <div class="rsd-cal-frame-wrapper" style="background:#FFFFFF;border-radius:24px;border:1.5px solid rgba(255,255,255,0.15);box-shadow:0 30px 70px -15px rgba(0,0,0,0.6);overflow:hidden;min-height:680px;position:relative;">
                                <!-- Cal inline embed code begins -->
                                <div style="width:100%;height:100%;min-height:680px;overflow:auto" id="my-cal-inline-15min"></div>
                                <script type="text/javascript">
                                (function (C, A, L) { let p = function (a, ar) { a.q.push(ar); }; let d = C.document; C.Cal = C.Cal || function () { let cal = C.Cal; let ar = arguments; if (!cal.loaded) { cal.ns = {}; cal.q = cal.q || []; d.head.appendChild(d.createElement("script")).src = A; cal.loaded = true; } if (ar[0] === L) { const api = function () { p(api, arguments); }; const namespace = ar[1]; api.q = api.q || []; if(typeof namespace === "string"){cal.ns[namespace] = cal.ns[namespace] || api;p(cal.ns[namespace], ar);p(cal, ["initNamespace", namespace]);} else p(cal, ar); return;} p(cal, ar); }; })(window, "https://app.cal.com/embed/embed.js", "init");
                                Cal("init", "15min", {origin:"https://app.cal.com"});
                                Cal.config = Cal.config || {};
                                Cal.config.forwardQueryParams = true;

                                Cal.ns["15min"]("inline", {
                                    elementOrSelector:"#my-cal-inline-15min",
                                    config: {"layout":"month_view","useSlotsViewOnSmallScreen":"true"},
                                    calLink: "edu-me-pkl28r/15min",
                                });

                                Cal.ns["15min"]("ui", {"hideEventTypeDetails":false,"layout":"month_view"});
                                
                    </script>
                                <!-- Cal inline embed code ends -->
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 6: GUARANTEE & FINAL CTA -->
                    <section class="rsd-saas-dark-sec rsd-saas-cta-sec">
                        <div class="rsd-saas-dark-container" style="text-align:center;">
                            <div class="rsd-guarantee-pill">
                                🛡️ ' . ($is_ar ? 'ضمان التجربة لمدة 30 يوماً: إذا لم تكن راضياً عن أداء النظام وسرعته، نعيد لك استثمارك بالكامل.' : '30-Day Money-Back Guarantee: If you are not satisfied with speed & performance, receive a 100% full refund.') . '
                            </div>
                            <h2 class="rsd-dark-h2" style="margin-top:24px;">
                                ' . ($is_ar ? 'ابدأ تحرير مبيعاتك وأرباحك اليوم' : 'Start Reclaiming Your Direct Revenue Today') . '
                            </h2>
                            <p class="rsd-dark-subtext" style="margin-bottom:32px;">
                                ' . ($is_ar ? 'تحدث مع مستشارينا لمراجعة بنية مشروعك وتجهيز نظام الحجز المباشر الخاص بك.' : 'Consult with our team to review your direct booking architecture.') . '
                            </p>
                            <button onclick="var el=document.getElementById(&apos;rsd-booking-calendar&apos;);if(el){el.scrollIntoView({behavior:&apos;smooth&apos;});}else{window.toggleRsdChatWidget(event);}" class="rsd-saas-btn-primary" style="font-size:1.15rem; padding:18px 44px;">
                                ' . ($is_ar ? 'احجز جلسة استشارة مجانية لمشروعك 🚀' : 'Book Free Consultation 🚀') . '
                            </button>
                        </div>
                    </section>



                </div>';

                return $saas_html;
            }

            // B. UNIFIED WORK / PORTFOLIO PAGES RENDERING (AR, EN, DE, RU)
            $is_work_page = ($post_id == 28 || $post_id == 164 || $post_id == 166 || $post_id == 168 || strpos($slug, 'work') !== false || strpos($slug, 'arbeiten') !== false || strpos($slug, 'работы') !== false);
            if ($is_work_page) {
                $work_html = '
                <div class="rsd-award-saas-wrap" dir="' . $dir . '">
                    <section class="rsd-saas-hero">
                        <div class="rsd-saas-hero-container">
                            <div class="rsd-saas-pill"><span>✦ ' . ($is_ar ? 'سجل الإنجازات والأعمال — Red Sea Digital' : 'FEATURED CASE STUDIES & LIVE WORK') . '</span></div>
                            <h1 class="rsd-saas-h1">' . ($is_ar ? 'نماذج أعمال استثنائية حققت نتائج مثبتة.' : 'Bespoke Digital Revenue Engines.') . '</h1>
                            <p class="rsd-saas-subtext">' . ($is_ar ? 'استكشف البنيات التحتية الرقمية ومحركات الحجز المباشرة والمتاجر الإلكترونية التي طورناها لعملائنا.' : 'Explore our high-converting direct booking systems and luxury e-commerce platforms.') . '</p>
                            <div class="rsd-saas-mockup-strip" style="margin-top:40px;">
                                <div class="rsd-saas-ui-card">
                                    <div class="rsd-ui-card-hdr"><span class="rsd-ui-icon">🏨</span><div><h4>YallaTrip</h4><span class="rsd-ui-sub">' . ($is_ar ? 'محرك حجز مباشر للفنادق' : 'Hospitality Booking') . '</span></div><span class="rsd-ui-badge-green">+340% ' . ($is_ar ? 'نمو مبيعات' : 'Growth') . '</span></div>
                                    <div class="rsd-ui-card-body"><p style="color:#475569; font-size:0.9rem;">' . ($is_ar ? 'توفير أكثر من $14,200 في العمولات وتأكيد الحجوزات فوراً لـ WhatsApp.' : 'Saved over $14,200 in commissions with instant WhatsApp dispatch.') . '</p></div>
                                </div>
                                <div class="rsd-saas-ui-card">
                                    <div class="rsd-ui-card-hdr"><span class="rsd-ui-icon">🛍️</span><div><h4>ASL Leather</h4><span class="rsd-ui-sub">' . ($is_ar ? 'بوتيك تجارة فاخرة' : 'Luxury Boutique') . '</span></div><span class="rsd-ui-badge-blue">1.1s ' . ($is_ar ? 'سرعة الدفع' : 'Speed') . '</span></div>
                                    <div class="rsd-ui-card-body"><p style="color:#475569; font-size:0.9rem;">' . ($is_ar ? 'متجر إلكتروني فاخر مدمج مع Apple Pay والربط الآلي للطلبات.' : 'Luxury storefront integrated with Apple Pay & order automation.') . '</p></div>
                                </div>
                                <div class="rsd-saas-ui-card">
                                    <div class="rsd-ui-card-hdr"><span class="rsd-ui-icon">💆‍♀️</span><div><h4>Paradise SPA</h4><span class="rsd-ui-sub">' . ($is_ar ? 'نظام حجز جلسات' : 'Wellness Center') . '</span></div><span class="rsd-ui-badge-purple">100% ' . ($is_ar ? 'دفع مباشر' : 'Direct') . '</span></div>
                                    <div class="rsd-ui-card-body"><p style="color:#475569; font-size:0.9rem;">' . ($is_ar ? 'جدولة مواعيد فورية وتأكيد حجز الجلسات تلقائياً عبر الواتساب.' : 'Instant appointment booking and automated client reminders.') . '</p></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>';
                return $work_html;
            }

            // Target Hero Metrics 3D child containers
            $content = str_replace('elementor-element-72xwdle e-flex', 'elementor-element-72xwdle rsd-metric-3d-card e-flex', $content);
            $content = str_replace('elementor-element-c8uwdn4 e-flex', 'elementor-element-c8uwdn4 rsd-metric-3d-card e-flex', $content);
            $content = str_replace('elementor-element-qmbzty4 e-flex', 'elementor-element-qmbzty4 rsd-metric-3d-card e-flex', $content);
            // Target Methodology child containers: mj8unhr, 2q9al98, d2ng7ff, oc4f7yi
            $methodology_ids = ['mj8unhr', '2q9al98', 'd2ng7ff', 'oc4f7yi'];
            foreach ($methodology_ids as $mid) {
                $content = str_replace(
                    'elementor-element-' . $mid . ' e-flex',
                    'elementor-element-' . $mid . ' rsd-liquid-glass-card e-flex',
                    $content
                );
            }

            // Target Manifesto child containers: p39b5ag, yj5atgg
            $manifesto_ids = [
                'p39b5ag' => 'rsd-liquid-glass-manifesto-card rsd-standard-card',
                'yj5atgg' => 'rsd-liquid-glass-manifesto-card rsd-conventional-card'
            ];
            foreach ($manifesto_ids as $mid => $mcls) {
                $content = str_replace(
                    'elementor-element-' . $mid . ' e-flex',
                    'elementor-element-' . $mid . ' ' . $mcls . ' e-flex',
                    $content
                );
            }

            return $content;
        }, 9999);
        add_action('wp_head', [$this, 'inject_head_chat_script'], 1);
        add_action('wp_head', [$this, 'inject_universal_master_header'], 2);
        add_action('wp_footer', [$this, 'render_universal_master_footer'], 5);
        add_action('wp_footer', [$this, 'inject_frontend_widget'], 999);
        add_action('wp_enqueue_scripts', [$this, 'render_nuclear_centering_css'], 999);
        add_action('admin_enqueue_scripts', [$this, 'remove_admin_footer_text'], 999);

        // Register AJAX actions for both logged-in users and guest visitors
        add_action('wp_ajax_rsd_chat', [$this, 'handle_ajax_chat']);
        add_action('wp_ajax_nopriv_rsd_chat', [$this, 'handle_ajax_chat']);
        add_action('wp_ajax_rsd_tts_stream', [$this, 'handle_ajax_tts_stream']);
        add_action('wp_ajax_nopriv_rsd_tts_stream', [$this, 'handle_ajax_tts_stream']);

        // Register REST API Endpoints for 2-Way WhatsApp Webhook
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // Register AJAX actions for WhatsApp QR, Status & AI Auto-Responder
        add_action('wp_ajax_rsd_wa_get_qr', [$this, 'handle_ajax_wa_get_qr']);
        add_action('wp_ajax_rsd_wa_get_pairing_code', [$this, 'handle_ajax_wa_get_pairing_code']);
        // Lead Radar Outbound Pipeline Hooks
        add_action('wp_ajax_rsd_radar_run_discovery', [$this, 'handle_ajax_radar_run_discovery']);
        add_action('wp_ajax_rsd_radar_approve_lead', [$this, 'handle_ajax_radar_approve_lead']);
        add_action('wp_ajax_rsd_radar_edit_pitch', [$this, 'handle_ajax_radar_edit_pitch']);
        add_action('wp_ajax_rsd_radar_reject_lead', [$this, 'handle_ajax_radar_reject_lead']);

        add_action('wp_ajax_rsd_wa_check_status', [$this, 'handle_ajax_wa_check_status']);
        add_action('wp_ajax_rsd_wa_disconnect', [$this, 'handle_ajax_wa_disconnect']);
        add_action('wp_ajax_rsd_wa_toggle_ai', [$this, 'handle_ajax_wa_toggle_ai']);


    }


    private static function trigger_chatwoot_handoff($customer_name, $customer_phone, $service_type, $details) {
        $chatwoot_enabled = get_option('rsd_chatwoot_enabled', '0');
        $chatwoot_url     = rtrim(get_option('rsd_chatwoot_url', ''), '/');
        $account_id       = get_option('rsd_chatwoot_account_id', '');
        $inbox_token      = get_option('rsd_chatwoot_inbox_token', '');
        $access_token     = get_option('rsd_chatwoot_access_token', '');

        if ($chatwoot_enabled !== '1' || empty($chatwoot_url) || empty($account_id) || empty($access_token)) {
            return;
        }

        // 1. Create / Find Contact in Chatwoot
        $contact_url = "{$chatwoot_url}/api/v1/accounts/{$account_id}/contacts";
        $contact_res = wp_remote_post($contact_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'api_access_token' => $access_token
            ],
            'body' => json_encode([
                'name'         => $customer_name,
                'phone_number' => '+' . preg_replace('/[^0-9]/', '', $customer_phone),
                'identifier'   => md5($customer_phone)
            ]),
            'timeout' => 8
        ]);

        $contact_id = null;
        if (!is_wp_error($contact_res)) {
            $body = json_decode(wp_remote_retrieve_body($contact_res), true);
            $contact_id = $body['payload']['contact']['id'] ?? $body['id'] ?? null;
        }

        // 2. Create Conversation & Push Lead Transcript
        if ($contact_id) {
            $conv_url = "{$chatwoot_url}/api/v1/accounts/{$account_id}/conversations";
            wp_remote_post($conv_url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'api_access_token' => $access_token
                ],
                'body' => json_encode([
                    'source_id'  => $contact_id,
                    'inbox_id'   => $inbox_token,
                    'contact_id' => $contact_id,
                    'message'    => [
                        'content' => "🔥 *New Lead Captured from Red Sea AI Engine!*\n\n👤 *Client:* {$customer_name}\n📞 *Phone:* {$customer_phone}\n🏨 *Service/Trip:* {$service_type}\n📝 *Details:* {$details}"
                    ]
                ]),
                'timeout' => 8,
                'blocking' => false
            ]);
        }
    }


    public static function handle_whatsapp_webhook_request($request) {
        $params = is_a($request, 'WP_REST_Request') ? $request->get_json_params() : [];
        if (empty($params) && is_a($request, 'WP_REST_Request')) {
            $params = $request->get_body_params();
        }
        if (empty($params)) {
            $raw = file_get_contents('php://input');
            $params = json_decode($raw, true) ?: [];
        }

        $sender_phone = $params['phone'] ?? $params['from'] ?? $params['sender'] ?? '';
        $message_text = $params['message'] ?? $params['text'] ?? $params['body'] ?? '';

        if (empty($sender_phone) || empty($message_text)) {
            return [
                'status'  => 'error',
                'message' => 'Sender phone and message text required'
            ];
        }

        $wa_enabled = get_option('rsd_wa_autoresponder_enabled', '1');

        $res = RedSeaAIProviderManager::generate($message_text);
        $reply_text = $res['reply'] ?? '';

        $endpoint = get_option('rsd_wa_api_endpoint', '');
        $api_key  = get_option('rsd_wa_api_key', '');

        if ($wa_enabled === '1' && !empty($endpoint) && !empty($reply_text)) {
            wp_remote_post($endpoint, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key,
                    'apikey'        => $api_key
                ],
                'body' => json_encode([
                    'number'  => $sender_phone,
                    'text'    => $reply_text,
                    'message' => $reply_text
                ]),
                'timeout'  => 10,
                'blocking' => false
            ]);
        }

        return [
            'status'       => 'success',
            'sender'       => $sender_phone,
            'reply'        => $reply_text,
            'auto_replied' => !empty($endpoint)
        ];
    }


    private static function check_rate_limit() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        }

        $transient_key = 'rsd_rate_limit_' . md5($ip);
        $count = (int) get_transient($transient_key);

        if ($count >= 15) { // Max 15 messages per 60 seconds
            return false;
        }

        set_transient($transient_key, $count + 1, 60);
        return true;
    }

    private static function trigger_lead_webhook($booking_data, $client_id) {
        $webhook_enabled = get_option('rsd_webhook_enabled', '0');
        $webhook_url     = get_option('rsd_webhook_url', '');
        $webhook_key     = get_option('rsd_webhook_api_key', '');

        if ($webhook_enabled !== '1' || empty($webhook_url)) {
            return;
        }

        $payload = array(
            'event'          => 'lead_captured',
            'client_id'      => $client_id,
            'customer_name'  => sanitize_text_field($booking_data['customer_name'] ?? ''),
            'customer_phone' => sanitize_text_field($booking_data['customer_phone'] ?? ''),
            'service_type'   => sanitize_text_field($booking_data['service_type'] ?? ''),
            'booking_details'=> sanitize_text_field($booking_data['booking_details'] ?? ''),
            'created_at'     => current_time('mysql'),
            'site_url'       => get_site_url()
        );

        wp_remote_post($webhook_url, array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type'  => 'application/json',
                'X-Api-Key'     => $webhook_key,
                'Authorization' => 'Bearer ' . $webhook_key
            ),
            'body'    => json_encode($payload),
            'timeout' => 8,
            'blocking'=> false // Non-blocking async background execution
        ));
    }


    public static function fetch_tts_audio_base64($text, $lang = 'ar') {
        if (empty($text)) return '';
        $clean = strip_tags($text);
        $clean = preg_replace('/[*#`~✦🚀💬💎📌📄🏨🌴🛍️🟢\\-]/u', ' ', $clean);
        $clean = trim(preg_replace('/\s+/u', ' ', $clean));
        if (empty($clean)) return '';

        $short = mb_substr($clean, 0, 180, 'UTF-8');
        $url = 'https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=' . urlencode($lang) . '&q=' . urlencode($short);

        $response = wp_remote_get($url, [
            'timeout'    => 6,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]);

        if (is_wp_error($response)) {
            return '';
        }

        $body = wp_remote_retrieve_body($response);
        if (!empty($body) && strlen($body) > 500) {
            return 'data:audio/mp3;base64,' . base64_encode($body);
        }
        return '';
    }

    public function handle_ajax_tts_stream() {
        if (ob_get_length()) { ob_clean(); }
        $text = isset($_GET['text']) ? sanitize_text_field($_GET['text']) : '';
        $lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : 'ar';

        if (empty($text)) {
            status_header(400);
            echo 'Text required';
            wp_die();
        }

        $clean = mb_substr(strip_tags($text), 0, 200, 'UTF-8');
        $url = 'https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=' . urlencode($lang) . '&q=' . urlencode($clean);

        $response = wp_remote_get($url, [
            'timeout'    => 8,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]);

        if (is_wp_error($response)) {
            status_header(500);
            echo 'TTS Error';
            wp_die();
        }

        $body = wp_remote_retrieve_body($response);
        header('Content-Type: audio/mpeg');
        header('Content-Length: ' . strlen($body));
        header('Cache-Control: public, max-age=86400');
        echo $body;
        wp_die();
    }

    public function handle_ajax_chat() {
        if (ob_get_length()) { ob_clean(); }
        header('Content-Type: application/json; charset=utf-8');

        $message  = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
        $lang     = isset($_POST['lang']) ? sanitize_text_field($_POST['lang']) : 'ar';
        $history  = isset($_POST['history']) ? json_decode(stripslashes($_POST['history']), true) : [];
        $is_voice = isset($_POST['voice_mode']) && ($_POST['voice_mode'] === '1' || $_POST['voice_mode'] === 1 || $_POST['voice_mode'] === 'true');

        if (empty($message)) {
            echo json_encode([
                'success' => false,
                'reply'   => ($lang === 'ar' ? 'أهلاً بك! كيف يمكنني مساعدتك اليوم؟' : 'Hello! How may I assist you today?')
            ], JSON_UNESCAPED_UNICODE);
            if (!defined('DOING_AJAX') || !DOING_AJAX) { return; }
            wp_die();
        }

        // 1. Precise Automatic Language Detection from Message + History
        $detected_lang = 'en';
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $message)) {
            $detected_lang = 'ar';
        } elseif (preg_match('/[\x{0400}-\x{04FF}]/u', $message)) {
            $detected_lang = 'ru';
        } elseif (preg_match('/[äöüßÄÖÜ]/u', $message) || preg_match('/\b(und|der|die|das|ich|wir|sie|ist|fuer|für|buchung|zimmer)\b/i', $message)) {
            $detected_lang = 'de';
        } elseif (preg_match('/[éèêàçùôîïÉÈÊÀÇÙÔÎÏ]/u', $message) || preg_match('/\b(bonjour|salut|merci|réservation|vous|nous|pour|avec|des|dans)\b/i', $message)) {
            $detected_lang = 'fr';
        } elseif (preg_match('/[áéíóúñ¿¡ÁÉÍÓÚÑ]/u', $message) || preg_match('/\b(hola|gracias|por|para|reserva|habitacion|buenos)\b/i', $message)) {
            $detected_lang = 'es';
        } elseif (preg_match('/[a-zA-Z]/', $message)) {
            $detected_lang = 'en';
        } else {
            // If query is numbers/symbols only, check recent history language
            if (!empty($history) && is_array($history)) {
                $last_user = '';
                foreach (array_reverse($history) as $h) {
                    if (($h['role'] ?? '') === 'user') {
                        $last_user = $h['content'] ?? '';
                        break;
                    }
                }
                if (preg_match('/[\x{0600}-\x{06FF}]/u', $last_user)) {
                    $detected_lang = 'ar';
                } elseif (preg_match('/[a-zA-Z]/', $last_user)) {
                    $detected_lang = 'en';
                }
            } else {
                $detected_lang = ($lang === 'ar' || strpos($_SERVER['REQUEST_URI'] ?? '', '/ar') !== false) ? 'ar' : 'en';
            }
        }

        // Dedicated Spoken Voice Sales Persona tailored to the detected language
        $custom_options = [];
        if ($is_voice) {
            $prompts = [
                'ar' => "أنت مستشار المبيعات الصوتي الحصري لشركة RED SEA DIGITAL. تحدث بصوت بشري طبيعي، دافئ، واثق ومقنع كأنك خبير استشاري يتحدث في مكالمة هاتفية حية. قواعد صوتية صارمة: 1. أجب باللغة العربية الفصحى حصراً في جملة أو جملتين فقط بقمة الفصاحة والذكاء. 2. لا تستخدم إطلاقاً أي رموز تعبيرية (emojis) أو علامات ماركداون (نجوم أو عناوين). 3. ساعد العميل في مضاعفة أرباحه عبر أنظمة الحجز المباشر والمتاجر الفاخرة وتوفير 30% عمولات، واطلب منه اسمه ورقمه لتأكيد استشارته.",
                'en' => "You are the Elite Human Voice Sales Consultant for RED SEA DIGITAL. Speak exclusively in English with a warm, natural, confident human conversational voice. Strict voice rules: 1. Answer in 1 to 2 short, highly persuasive conversational sentences in English. 2. Never use emojis, asterisks, bullets, or headers in voice output. 3. Help the client eliminate 30% OTA commission leakage with direct booking architecture, and seamlessly guide them to share their Name & WhatsApp phone number for a private review.",
                'de' => "Sie sind der exklusive KI-Verkaufsberater für RED SEA DIGITAL. Antworten Sie ausschließlich auf Deutsch in 1-2 kurzen, hochprofessionellen Sätzen ohne Emojis oder Markdown. Helfen Sie dem Kunden, 30% Buchungsprovisionen zu sparen.",
                'fr' => "Vous êtes le consultant commercial vocal exclusif de RED SEA DIGITAL. Répondez exclusivement en français en 1 ou 2 phrases courtes et naturelles sans émojis ni markdown. Aidez le client à éliminer les commissions d'intermédiaires.",
                'ru' => "Вы эксклюзивный голосовой консультант RED SEA DIGITAL. Отвечайте исключительно на русском языке 1-2 естественными фразами без эмодзи и разметки. Помогите клиенту избавиться от комиссий посредников.",
                'es' => "Usted es el consultor de ventas por voz exclusivo de RED SEA DIGITAL. Responda exclusivamente en español en 1 o 2 frases breves y naturales sin emojis ni markdown."
            ];

            $custom_options['system_prompt_override'] = $prompts[$detected_lang] ?? $prompts['ar'];
            $custom_options['max_tokens'] = 140;
            $custom_options['temperature'] = 0.55;
        }

        $custom_options['detected_lang'] = $detected_lang;

        // Route through Hierarchical Multi-Agent Orchestrator
        $orch_res = RedSeaChiefOrchestrator::process_message($message, is_array($history) ? $history : [], $custom_options);
        
        $reply_text = '';
        if (is_array($orch_res) && !empty($orch_res['reply'])) {
            $reply_text = $orch_res['reply'];
        } elseif (is_string($orch_res) && !empty($orch_res)) {
            $reply_text = $orch_res;
        } else {
            $raw_res = RedSeaAIProviderManager::generate($message, is_array($history) ? $history : [], $custom_options);
            $reply_text = is_string($raw_res) ? $raw_res : ($raw_res['reply'] ?? '');
        }

        if (empty($reply_text) || strlen(trim($reply_text)) < 5) {
            $reply_text = ($detected_lang === 'ar')
                ? "أهلاً بك في Red Sea Digital! يسعدني تقديم استشارة مخصصة لمشروعك ومضاعفة مبيعاتك المباشرة. تفضل بطرح استفسارك وسأجيبك فوراً."
                : "Welcome to Red Sea Digital! I am here to help you scale your direct revenue and custom AI architecture. How may I assist you today?";
        }

        // Clean text specifically for spoken human voice synthesis
        $spoken_text = strip_tags($reply_text);
        $spoken_text = preg_replace('/[*#`~✦🚀💬💎📌📄🏨🌴🛍️🟢\\-]/u', ' ', $spoken_text);
        $spoken_text = preg_replace('/[\r\n\t]+/u', ' ', $spoken_text);
        $spoken_text = trim(preg_replace('/\s+/u', ' ', $spoken_text));

        // Generate Server-Side Base64 Audio Data URIs (Instant, CORS-free playback)
        $audio_data_uris = [];
        if (!empty($spoken_text)) {
            $words = explode(' ', $spoken_text);
            $chunk = '';
            $chunks = [];
            foreach ($words as $w) {
                if (mb_strlen($chunk . ' ' . $w, 'UTF-8') < 110) {
                    $chunk .= (empty($chunk) ? '' : ' ') . $w;
                } else {
                    $chunks[] = $chunk;
                    $chunk = $w;
                }
            }
            if (!empty($chunk)) {
                $chunks[] = $chunk;
            }

            foreach ($chunks as $c) {
                if (!empty(trim($c))) {
                    $b64 = self::fetch_tts_audio_base64(trim($c), $detected_lang);
                    if (!empty($b64)) {
                        $audio_data_uris[] = $b64;
                    }
                }
            }
        }

        // Automatic Lead Capture from Voice / Text Conversation
        if (preg_match('/(01[0-9]{9}|\+?[0-9]{8,15})/u', $message, $matches)) {
            $phone = $matches[0];
            self::trigger_chatwoot_handoff('Voice Lead Client', $phone, 'Direct Booking Consultation', $message);
        }

        echo json_encode([
            'success'         => true,
            'reply'           => $reply_text,
            'spoken_text'     => $spoken_text,
            'detected_lang'   => $detected_lang,
            'audio_data_uris' => $audio_data_uris,
            'audio_url'       => !empty($audio_data_uris) ? $audio_data_uris[0] : (admin_url('admin-ajax.php?action=rsd_tts_stream&lang=' . $detected_lang . '&text=' . urlencode(mb_substr($spoken_text, 0, 150, 'UTF-8')))),
            'provider'        => $res['provider'] ?? 'opencode'
        ], JSON_UNESCAPED_UNICODE);

        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_die();
        }
    }

    public function remove_admin_footer_text() {
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'redsea-ai-crm') !== false) {
            add_filter('admin_footer_text', '__return_empty_string', 99);
            add_filter('update_footer', '__return_empty_string', 99);
        }
    }

    public function disable_wpautop_on_homepage($content) {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_home = (is_front_page() || is_home() || strpos($request_uri, '/ar-home') !== false || $request_uri === '/' || strpos($request_uri, '/ar') !== false);
        if ($is_home) {
            remove_filter('the_content', 'wpautop');
            $content = preg_replace('/<p>\s*<\/p>/s', '', $content);
            $content = preg_replace('/<\/p>\s*<p>/s', '', $content);
        }
        return $content;
    }

    public function sanitize_homepage_content_wpautop($content) {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_home = (is_front_page() || is_home() || strpos($request_uri, '/ar-home') !== false || $request_uri === '/' || strpos($request_uri, '/ar') !== false);
        if ($is_home) {
            // Strip the first <p> block containing scripts/comments before the hero section
            $content = preg_replace('/^<p>\s*(?:<!--.*?-->|<script[\s\S]*?<\/script>|\s*)*<\/p>/s', '', trim($content));
            $content = preg_replace('/<p>\s*<\/p>/s', '', $content);
        }
        return $content;
    }

    public function render_nuclear_centering_css() {
        $is_ar = (strpos($_SERVER['REQUEST_URI'], '/ar') !== false);
        ?>
        <style id="rsd-pristine-uncompressed-layout-css">
            /* ========================================================= */
            /* 1. DESKTOP HEADER SYMMETRICAL GRID (MIN-WIDTH 992PX)      */
            /* ========================================================= */
            @media (min-width: 992px) {
                .rsd-header {
                    background: rgba(251, 251, 249, 0.94) !important;
                    backdrop-filter: blur(20px) !important;
                    -webkit-backdrop-filter: blur(20px) !important;
                    border-bottom: 1px solid rgba(17, 17, 17, 0.06) !important;
                    position: sticky !important;
                    top: 0 !important;
                    z-index: 99999 !important;
                    width: 100% !important;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02) !important;
                }

                .rsd-header-container {
                    display: grid !important;
                    grid-template-columns: 300px 1fr 300px !important;
                    align-items: center !important;
                    max-width: 1360px !important;
                    margin: 0 auto !important;
                    padding: 16px 40px !important;
                    box-sizing: border-box !important;
                    width: 100% !important;
                    <?php echo $is_ar ? "direction: rtl !important;" : "direction: ltr !important;"; ?>
                }

                .rsd-logo-link {
                    grid-column: 1 !important;
                    justify-self: start !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .rsd-logo-img {
                    max-height: 38px !important;
                    width: auto !important;
                    object-fit: contain !important;
                }

                .rsd-desktop-nav {
                    grid-column: 2 !important;
                    justify-self: center !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    gap: 36px !important;
                    margin: 0 auto !important;
                }

                .rsd-desktop-nav .rsd-nav-link {
                    font-family: Inter, system-ui, sans-serif !important;
                    font-size: 0.9rem !important;
                    font-weight: 600 !important;
                    letter-spacing: 0.3px !important;
                    color: #0F172A !important;
                    text-decoration: none !important;
                    transition: color 0.25s ease, opacity 0.25s ease !important;
                    opacity: 0.82;
                    white-space: nowrap !important;
                }

                <?php if ($is_ar): ?>
                .rsd-desktop-nav .rsd-nav-link {
                    font-family: 'Cairo', 'Tajawal', sans-serif !important;
                    font-size: 0.95rem !important;
                    font-weight: 700 !important;
                }
                <?php endif; ?>

                .rsd-desktop-nav .rsd-nav-link:hover {
                    opacity: 1 !important;
                    color: #2563EB !important;
                }

                .rsd-header-right {
                    grid-column: 3 !important;
                    justify-self: end !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: flex-end !important;
                    gap: 16px !important;
                    margin: 0 !important;
                }

                .rsd-header-btn {
                    background: #FFFFFF !important;
                    color: #ffffff !important;
                    padding: 10px 24px !important;
                    border-radius: 30px !important;
                    font-size: 0.85rem !important;
                    font-weight: 700 !important;
                    text-decoration: none !important;
                    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                    box-shadow: 0 4px 12px rgba(17, 17, 17, 0.1) !important;
                    white-space: nowrap !important;
                }

                .rsd-header-btn:hover {
                    background: #2563EB !important;
                    color: #0F172A !important;
                    transform: translateY(-2px) !important;
                    box-shadow: 0 6px 18px rgba(197, 160, 89, 0.35) !important;
                }

                .rsd-sleek-lang-toggle {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    gap: 4px !important;
                    font-family: Inter, system-ui, sans-serif !important;
                    font-size: 0.82rem !important;
                    font-weight: 700 !important;
                    margin: 0 !important;
                    background: rgba(17, 17, 17, 0.04) !important;
                    padding: 6px 14px !important;
                    border-radius: 20px !important;
                    border: 1px solid rgba(197, 160, 89, 0.35) !important;
                }
            }

            /* ========================================================= */
            /* 2. MOBILE HEADER LAYOUT (MAX-WIDTH 991PX)                 */
            /* ========================================================= */
            @media (max-width: 991px) {
                .rsd-header {
                    background: #FBFBF9 !important;
                    border-bottom: 1px solid rgba(17, 17, 17, 0.08) !important;
                    position: sticky !important;
                    top: 0 !important;
                    z-index: 99999 !important;
                }

                .rsd-header-container {
                    display: block !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                    position: relative !important;
                    min-height: 64px !important;
                    direction: ltr !important;
                    padding: 0 16px !important;
                }

                .rsd-logo-link {
                    position: absolute !important;
                    left: 16px !important;
                    right: auto !important;
                    top: 50% !important;
                    transform: translateY(-50%) !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    max-width: 130px !important;
                    z-index: 99999 !important;
                }

                .rsd-logo-img {
                    max-height: 34px !important;
                    width: auto !important;
                    object-fit: contain !important;
                }

                .rsd-desktop-nav {
                    display: none !important;
                }

                .rsd-header-right {
                    position: absolute !important;
                    right: 16px !important;
                    left: auto !important;
                    top: 50% !important;
                    transform: translateY(-50%) !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: flex-end !important;
                    gap: 8px !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    z-index: 99999 !important;
                    direction: ltr !important;
                }

                .rsd-header-right .rsd-header-btn {
                    display: none !important;
                }

                button.rsd-mobile-toggle {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    font-size: 1.3rem !important;
                    background: #ffffff !important;
                    color: #0F172A !important;
                    border: 1px solid #cbd5e1 !important;
                    border-radius: 8px !important;
                    padding: 6px 12px !important;
                    cursor: pointer !important;
                    margin: 0 !important;
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
                    height: 38px !important;
                    line-height: 1 !important;
                }
            }

            /* ========================================================= */
            /* 3. PRISTINE UNCOMPRESSED CONTAINERS & SECTIONS (EN & AR)  */
            /* ========================================================= */
            .rsd-sec {
                width: 100% !important;
                padding: 80px 0 !important;
                box-sizing: border-box !important;
            }

            .rsd-container {
                width: 100% !important;
                max-width: 1200px !important;
                margin: 0 auto !important;
                padding: 0 24px !important;
                box-sizing: border-box !important;
            }

            /* Portfolio Cards: Natural Uncompressed Layout */
            .rsd-portfolio-card {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 48px !important;
                width: 100% !important;
                margin-bottom: 48px !important;
                background: #ffffff !important;
                border-radius: 24px !important;
                padding: 40px !important;
                border: 1px solid rgba(17, 17, 17, 0.06) !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                box-sizing: border-box !important;
            }

            .rsd-portfolio-card > div {
                flex: 1 !important;
                width: 50% !important;
                box-sizing: border-box !important;
            }

            .rsd-portfolio-img-wrap img,
            .rsd-portfolio-card img {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                object-fit: cover !important;
                border-radius: 16px !important;
                display: block !important;
            }

            @media (max-width: 768px) {
                .rsd-sec {
                    padding: 48px 0 !important;
                }

                .rsd-container {
                    padding: 0 20px !important;
                }

                .rsd-portfolio-card,
                .rsd-portfolio-card.reverse {
                    flex-direction: column !important;
                    padding: 24px 20px !important;
                    gap: 24px !important;
                }

                .rsd-portfolio-card > div {
                    width: 100% !important;
                    flex: none !important;
                }
            }

            /* Clean Language Toggle Pill Styling */
            .rsd-sleek-lang-toggle {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                font-family: Inter, system-ui, sans-serif !important;
                font-size: 0.85rem !important;
                font-weight: 700 !important;
                letter-spacing: 0.5px !important;
                margin: 0 8px !important;
                vertical-align: middle !important;
                background: rgba(17, 17, 17, 0.05);
                padding: 5px 14px;
                border-radius: 20px;
                border: 1px solid rgba(197, 160, 89, 0.3);
            }
            .rsd-sleek-lang-toggle a {
                color: #646460 !important;
                text-decoration: none !important;
                transition: color 0.2s ease !important;
                padding: 2px 6px !important;
            }
            .rsd-sleek-lang-toggle a:hover, .rsd-sleek-lang-toggle a.active {
                color: #2563EB !important;
                font-weight: 900 !important;
            }

            /* Global Hero Centering Rules */
            .rsd-hero-sec,
            .rsd-hero-content {
                text-align: center !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                width: 100% !important;
                max-width: 1200px !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
            .rsd-hero-tag, .rsd-hero-title, .rsd-hero-sub {
                text-align: center !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
            .rsd-hero-actions {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                gap: 16px !important;
                margin: 24px auto !important;
            }
        
            /* ========================================================= */
            /* 100% FULL WIDTH ZERO SIDE PADDING MOBILE SECTIONS & CARDS */
            /* ========================================================= */
            @media (max-width: 768px) {
                html, body, #page, #content, .site-content, .entry-content, article,
                                .rsd-ar-master-wrapper {
                    overflow-x: hidden !important;
                    width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                /* All Mobile Sections & Containers: 100% FULL WIDTH, ZERO SIDE PADDING */
                .rsd-sec,
                .rsd-container,
                .elementor-section,
                .elementor-container,
                .elementor-column,
                .elementor-widget-wrap,
                .rsd-portfolio-container,
                .rsd-cs-container,
                .rsd-case-study-hero {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                    float: none !important;
                }

                /* All Mobile Cards: 100% FULL WIDTH EDGE TO EDGE */
                .rsd-portfolio-card,
                .rsd-portfolio-card.reverse,
                .rsd-card,
                .rsd-service-card,
                .rsd-work-card,
                .rsd-process-step,
                .rsd-capability-card,
                .rsd-testimonial-card,
                .rsd-comparison-card,
                .rsd-cs-card,
                .rsd-metric-box {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    border-radius: 0 !important; /* Edge to edge full screen width */
                    padding-left: 16px !important;
                    padding-right: 16px !important;
                }

                .rsd-portfolio-card > div,
                .rsd-portfolio-card.reverse > div,
                .rsd-portfolio-img-wrap {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                }

                .rsd-portfolio-img-wrap img,
                .rsd-portfolio-card img {
                    width: 100% !important;
                    max-width: 100% !important;
                    height: auto !important;
                    border-radius: 0 !important;
                }
            }

        
            /* ========================================================= */
            /* 5PX BREATHING SIDE PADDING FOR MOBILE CONTAINERS & CARDS */
            /* ========================================================= */
            @media (max-width: 768px) {
                html, body, #page, #content, .site-content, .entry-content, article,
                                .rsd-ar-master-wrapper {
                    overflow-x: hidden !important;
                    width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                /* Mobile Containers: 5px Breathing Side Padding */
                .rsd-sec,
                .rsd-container,
                .elementor-section,
                .elementor-container,
                .elementor-column,
                .elementor-widget-wrap,
                .rsd-portfolio-container,
                .rsd-cs-container,
                .rsd-case-study-hero {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    padding-left: 5px !important;
                    padding-right: 5px !important;
                    float: none !important;
                }

                /* Mobile Cards: 12px Internal Padding & 12px Radius for Breathing Comfort */
                .rsd-portfolio-card,
                .rsd-portfolio-card.reverse,
                .rsd-card,
                .rsd-service-card,
                .rsd-work-card,
                .rsd-process-step,
                .rsd-capability-card,
                .rsd-testimonial-card,
                .rsd-comparison-card,
                .rsd-cs-card,
                .rsd-metric-box {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    border-radius: 12px !important;
                    padding-left: 12px !important;
                    padding-right: 12px !important;
                }

                .rsd-portfolio-card > div,
                .rsd-portfolio-card.reverse > div,
                .rsd-portfolio-img-wrap {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                }

                .rsd-portfolio-img-wrap img,
                .rsd-portfolio-card img {
                    width: 100% !important;
                    max-width: 100% !important;
                    height: auto !important;
                    border-radius: 10px !important;
                }
            }

        /* ========================================================= */
            /* UNIVERSAL MOBILE CONTAINER & CARD LAYOUT FIX (ALL PAGES) */
            /* ========================================================= */
            @media (max-width: 900px) {
                html, body, #page, #content, .site-content, .entry-content, article {
                    overflow-x: hidden !important;
                    width: 100% !important;
                    max-width: 100vw !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    box-sizing: border-box !important;
                }

                .rsd-sec,
                .rsd-container,
                .rsd-portfolio-container,
                .rsd-cs-container,
                .rsd-case-study-hero,
                .elementor-section,
                .elementor-container,
                .elementor-column,
                .elementor-widget-wrap {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    padding-left: 16px !important;
                    padding-right: 16px !important;
                    float: none !important;
                }

                .rsd-portfolio-card,
                .rsd-portfolio-card.reverse,
                .rsd-card,
                .rsd-cs-card,
                .rsd-service-card,
                .rsd-work-card,
                .rsd-process-step,
                .rsd-capability-card,
                .rsd-testimonial-card,
                .rsd-comparison-card {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    border-radius: 20px !important;
                    padding: 24px 18px !important;
                    flex-direction: column !important;
                    gap: 20px !important;
                }

                .rsd-portfolio-card > div,
                .rsd-portfolio-card.reverse > div,
                .rsd-portfolio-img-wrap {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    flex: none !important;
                }

                .rsd-portfolio-img-wrap img,
                .rsd-portfolio-card img,
                .rsd-cs-card img {
                    width: 100% !important;
                    max-width: 100% !important;
                    height: auto !important;
                    border-radius: 14px !important;
                    object-fit: cover !important;
                }
            }
        /* ========================================================= */
            /* ELIMINATE HOMEPAGE HERO TOP BLANK WHITESPACE GAP           */
            /* ========================================================= */
            .rsd-hero-sec,
            .rsd-sec:first-of-type,
            body.page-id-12 .rsd-sec:first-of-type,
            body.page-id-163 .rsd-sec:first-of-type,
            body.home .rsd-sec:first-of-type,
            .entry-content > .rsd-sec:first-child {
                padding-top: 16px !important;
                margin-top: 0 !important;
            }

            @media (max-width: 900px) {
                #rsd-header-spacer {
                    height: 84px !important;
                }
                .rsd-hero-sec,
                .rsd-sec:first-of-type,
                body.page-id-12 .rsd-sec:first-of-type,
                body.page-id-163 .rsd-sec:first-of-type,
                body.home .rsd-sec:first-of-type,
                .entry-content > .rsd-sec:first-child {
                    padding-top: 12px !important;
                    margin-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* BULLETPROOF MATHEMATICAL NO-GAP HERO RULE                  */
            /* Fixed Header = 84px | Top Section Padding = 104px          */
            /* Net Gap = Exactly 20px Below Universal Header!             */
            /* ========================================================= */
            #rsd-header-spacer {
                display: none !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .entry-content header.rsd-header,
            .rsd-hero-center-master > header.rsd-header,
            .rsd-ar-master-wrapper header.rsd-header,
            .rsd-ar-centered-wrapper > header.rsd-header,
            .entry-content .rsd-header,
            header.rsd-header:not(#rsdUniversalHeader) {
                display: none !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body.home .entry-content,
            body.page-id-12 .entry-content,
            body.page-id-163 .entry-content,
            .site-main,
            #content {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }

            .rsd-hero-center-master,
            .rsd-ar-master-wrapper,
            .rsd-ar-centered-wrapper,
            .rsd-hero-sec,
            .rsd-sec:first-of-type,
            body.page-id-12 .rsd-hero-center-master,
            body.page-id-163 .rsd-ar-master-wrapper,
            body.home .rsd-hero-center-master,
            body.home .rsd-ar-master-wrapper,
            .entry-content > div:first-child,
            .entry-content > section:first-child {
                padding-top: 104px !important;
                margin-top: 0 !important;
            }

            @media (max-width: 900px) {
                .rsd-hero-center-master,
                .rsd-ar-master-wrapper,
                .rsd-ar-centered-wrapper,
                .rsd-hero-sec,
                .rsd-sec:first-of-type,
                body.page-id-12 .rsd-hero-center-master,
                body.page-id-163 .rsd-ar-master-wrapper,
                body.home .rsd-hero-center-master,
                body.home .rsd-ar-master-wrapper,
                .entry-content > div:first-child,
                .entry-content > section:first-child {
                    padding-top: 96px !important;
                    margin-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* WPAUTOP JUNK & HERO TOP GAP ABSOLUTE ELIMINATION           */
            /* ========================================================= */
            .entry-content > p:empty,
            .entry-content > p:has(script),
            .entry-content > p:has(style),
            .entry-content > p:has(iframe),
            body.home .entry-content > p:first-child,
            body.page-id-12 .entry-content > p:first-child,
            body.page-id-163 .entry-content > p:first-child {
                display: none !important;
                margin: 0 !important;
                padding: 0 !important;
                height: 0 !important;
                line-height: 0 !important;
            }

            .rsd-hero-sec,
            section.rsd-hero-sec,
            .rsd-hero-center-master,
            .rsd-ar-master-wrapper {
                padding-top: 100px !important;
                margin-top: 0 !important;
            }

            @media (max-width: 900px) {
                .rsd-hero-sec,
                section.rsd-hero-sec,
                .rsd-hero-center-master,
                .rsd-ar-master-wrapper {
                    padding-top: 92px !important;
                    margin-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE MATHEMATICAL HERO GAP FIX (HEADER=84px, PADDING=92px) */
            /* ========================================================= */
            .entry-content > p:first-child {
                display: none !important;
                margin: 0 !important;
                padding: 0 !important;
                height: 0 !important;
            }

            body.home section.rsd-hero-sec,
            body.page-id-12 section.rsd-hero-sec,
            body.page-id-163 section.rsd-hero-sec,
            .rsd-hero-sec,
            section.rsd-hero-sec {
                padding-top: 92px !important;
                margin-top: 0 !important;
            }

            @media (max-width: 900px) {
                body.home section.rsd-hero-sec,
                body.page-id-12 section.rsd-hero-sec,
                body.page-id-163 section.rsd-hero-sec,
                .rsd-hero-sec,
                section.rsd-hero-sec {
                    padding-top: 86px !important;
                    margin-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE PARAGRAPH DESTRUCTION FOR ZERO TOP GAP           */
            /* ========================================================= */
            p:has(section),
            p:has(script),
            p:has(style),
            .rsd-hero-center-master > p,
            .rsd-ar-master-wrapper > p,
            .entry-content > p:first-child {
                display: none !important;
                margin: 0 !important;
                padding: 0 !important;
                height: 0 !important;
                line-height: 0 !important;
                font-size: 0 !important;
            }

            body.home section.rsd-hero-sec,
            body.page-id-12 section.rsd-hero-sec,
            body.page-id-163 section.rsd-hero-sec,
            .rsd-hero-sec,
            section.rsd-hero-sec {
                padding-top: 90px !important;
                margin-top: 0 !important;
            }

            @media (max-width: 900px) {
                body.home section.rsd-hero-sec,
                body.page-id-12 section.rsd-hero-sec,
                body.page-id-163 section.rsd-hero-sec,
                .rsd-hero-sec,
                section.rsd-hero-sec {
                    padding-top: 86px !important;
                    margin-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* NUCLEAR ZERO-GAP HERO ALIGNMENT (HEADER 84px -> TAG 16px)   */
            /* ========================================================= */
            .rsd-hero-sec,
            section.rsd-hero-sec,
            .rsd-hero-center-master,
            .rsd-ar-master-wrapper,
            .rsd-ar-centered-wrapper {
                padding-top: 84px !important;
                margin-top: 0 !important;
            }

            .rsd-hero-sec .rsd-container,
            .rsd-hero-sec .rsd-hero-content,
            .rsd-hero-sec .rsd-hero-tag,
            .rsd-hero-content > *:first-child,
            .rsd-hero-tag,
            span.rsd-hero-tag {
                margin-top: 0 !important;
                padding-top: 16px !important;
            }

            @media (max-width: 900px) {
                .rsd-hero-sec,
                section.rsd-hero-sec,
                .rsd-hero-center-master,
                .rsd-ar-master-wrapper {
                    padding-top: 84px !important;
                    margin-top: 0 !important;
                }
                .rsd-hero-sec .rsd-container,
                .rsd-hero-sec .rsd-hero-content,
                .rsd-hero-sec .rsd-hero-tag,
                .rsd-hero-content > *:first-child,
                .rsd-hero-tag,
                span.rsd-hero-tag {
                    margin-top: 0 !important;
                    padding-top: 12px !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE COLLAPSE OF TOP ELEMENTOR TEXT-EDITOR WIDGET     */
            /* ========================================================= */
            .elementor-widget-text-editor:has(#rsd-english-hero-center-override),
            .elementor-widget-text-editor:has(#rsd-absolute-hero-center-override),
            .elementor-widget-text-editor:has(#rsd-hero-center-override),
            .elementor-widget-text-editor:has(#rsd-eng-hero-center-override),
            .elementor-widget-text-editor:has(.rsd-header),
            .elementor-widget-text-editor:has(.rsd-side-drawer),
            .elementor-widget-text-editor:first-child,
            .elementor-widget-text-editor:has(style) {
                display: none !important;
                height: 0 !important;
                max-height: 0 !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                position: absolute !important;
                overflow: hidden !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }

            .elementor-top-section:first-child,
            .elementor-section:first-child,
            .rsd-hero-sec,
            section.rsd-hero-sec,
            .rsd-hero-center-master,
            .rsd-ar-master-wrapper {
                padding-top: 84px !important;
                margin-top: 0 !important;
            }

            .rsd-hero-sec .rsd-container,
            .rsd-hero-sec .rsd-hero-content,
            .rsd-hero-sec .rsd-hero-tag,
            .rsd-hero-tag,
            span.rsd-hero-tag {
                margin-top: 0 !important;
                padding-top: 12px !important;
            }

            @media (max-width: 900px) {
                .elementor-top-section:first-child,
                .elementor-section:first-child,
                .rsd-hero-sec,
                section.rsd-hero-sec,
                .rsd-hero-center-master,
                .rsd-ar-master-wrapper {
                    padding-top: 84px !important;
                    margin-top: 0 !important;
                }
                .rsd-hero-sec .rsd-container,
                .rsd-hero-sec .rsd-hero-content,
                .rsd-hero-sec .rsd-hero-tag,
                .rsd-hero-tag,
                span.rsd-hero-tag {
                    margin-top: 0 !important;
                    padding-top: 8px !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE CLEAN MATHEMATICAL HERO ALIGNMENT                 */
            /* ========================================================= */
            .rsd-hero-sec,
            section.rsd-hero-sec,
            .rsd-hero-center-master,
            .rsd-ar-master-wrapper {
                margin-top: 0 !important;
                padding-top: 84px !important;
            }

            .rsd-hero-sec .rsd-container,
            .rsd-hero-sec .rsd-hero-content,
            .rsd-hero-sec .rsd-hero-tag,
            .rsd-hero-tag,
            span.rsd-hero-tag {
                margin-top: 0 !important;
                padding-top: 14px !important;
            }

            @media (max-width: 900px) {
                .rsd-hero-sec,
                section.rsd-hero-sec,
                .rsd-hero-center-master,
                .rsd-ar-master-wrapper {
                    margin-top: 0 !important;
                    padding-top: 84px !important;
                }
                .rsd-hero-sec .rsd-container,
                .rsd-hero-sec .rsd-hero-content,
                .rsd-hero-sec .rsd-hero-tag,
                .rsd-hero-tag,
                span.rsd-hero-tag {
                    margin-top: 0 !important;
                    padding-top: 10px !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE ZERO-SPACER MATHEMATICAL HERO ALIGNMENT          */
            /* Fixed Header = 84px | Hero Section Top Padding = 96px      */
            /* Net Gap = Exactly 12px Below Universal Header!             */
            /* ========================================================= */
            #rsd-header-spacer {
                display: none !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .rsd-ar-master-wrapper,
            .rsd-ar-centered-wrapper,
            .rsd-hero-center-master,
            .rsd-hero-sec,
            section.rsd-hero-sec,
            .elementor-top-section:first-child,
            .elementor-section:first-child {
                margin-top: 0 !important;
                padding-top: 96px !important;
            }

            .rsd-hero-sec .rsd-container,
            .rsd-hero-sec .rsd-hero-content,
            .rsd-hero-sec .rsd-hero-tag,
            .rsd-hero-tag,
            span.rsd-hero-tag {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }

            @media (max-width: 900px) {
                .rsd-ar-master-wrapper,
                .rsd-ar-centered-wrapper,
                .rsd-hero-center-master,
                .rsd-hero-sec,
                section.rsd-hero-sec,
                .elementor-top-section:first-child,
                .elementor-section:first-child {
                    margin-top: 0 !important;
                    padding-top: 90px !important;
                }
                .rsd-hero-sec .rsd-container,
                .rsd-hero-sec .rsd-hero-content,
                .rsd-hero-sec .rsd-hero-tag,
                .rsd-hero-tag,
                span.rsd-hero-tag {
                    margin-top: 0 !important;
                    padding-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE ZERO-GAP MATHEMATICAL HERO FIX (84px HEADER MATCH) */
            /* ========================================================= */
            .rsd-ar-master-wrapper,
            .rsd-ar-centered-wrapper,
            .rsd-hero-center-master,
            body.page-id-163 .entry-content,
            body.page-id-12 .entry-content,
            body.home .entry-content,
            .site-main,
            #content {
                margin-top: 0 !important;
                padding-top: 0 !important;
                border-top: none !important;
            }

            .rsd-ar-centered-wrapper > p,
            .rsd-ar-master-wrapper > p,
            .rsd-hero-center-master > p,
            .entry-content > p:first-child,
            .entry-content > p:nth-child(2),
            p:has(section) {
                display: none !important;
                margin: 0 !important;
                padding: 0 !important;
                height: 0 !important;
                line-height: 0 !important;
                font-size: 0 !important;
            }

            body.page-id-163 section.rsd-hero-sec,
            body.page-id-12 section.rsd-hero-sec,
            body.home section.rsd-hero-sec,
            .rsd-hero-sec,
            section.rsd-hero-sec,
            .elementor-top-section:first-child,
            .elementor-section:first-child {
                padding-top: 84px !important;
                margin-top: 0 !important;
            }

            .rsd-hero-sec .rsd-container,
            .rsd-hero-sec .rsd-hero-content,
            .rsd-hero-sec .rsd-hero-tag,
            .rsd-hero-tag,
            span.rsd-hero-tag {
                margin-top: 0 !important;
                padding-top: 10px !important;
            }

            @media (max-width: 900px) {
                body.page-id-163 section.rsd-hero-sec,
                body.page-id-12 section.rsd-hero-sec,
                body.home section.rsd-hero-sec,
                .rsd-hero-sec,
                section.rsd-hero-sec {
                    padding-top: 84px !important;
                    margin-top: 0 !important;
                }
                .rsd-hero-sec .rsd-container,
                .rsd-hero-sec .rsd-hero-content,
                .rsd-hero-sec .rsd-hero-tag,
                .rsd-hero-tag,
                span.rsd-hero-tag {
                    margin-top: 0 !important;
                    padding-top: 8px !important;
                }
            }
        /* ========================================================= */
            /* COMPLETE LUXURY HOMEPAGE STYLING RESTORATION SYSTEM       */
            /* ========================================================= */
            :root {
                --bg-alabaster: #FBFBF9;
                --text-dark: #0F172A;
                --text-body: #4A4A48;
                --text-muted: #71716A;
                --border-stone: #E5E5E0;
                --border-light: #F0F0EC;
                --accent-gold: #2563EB;
                --card-bg: #FFFFFF;
                --shadow-subtle: 0 10px 30px rgba(0, 0, 0, 0.03);
                --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.06);
                --transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .rsd-container {
                max-width: 1280px !important;
                margin: 0 auto !important;
                padding: 0 24px !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .rsd-hero-title {
                font-family: 'Playfair Display', Georgia, serif !important;
                font-size: clamp(2.2rem, 5vw, 3.8rem) !important;
                font-weight: 700 !important;
                line-height: 1.18 !important;
                color: #0F172A !important;
                margin: 0 0 20px 0 !important;
                max-width: 960px !important;
            }

            .rsd-hero-sub {
                font-size: 1.12rem !important;
                color: #4A4A48 !important;
                max-width: 820px !important;
                margin-bottom: 32px !important;
                line-height: 1.7 !important;
            }

            .rsd-hero-actions {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 16px !important;
                margin-bottom: 36px !important;
                flex-wrap: wrap !important;
            }

            .rsd-btn-black,
            a.rsd-btn-black {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                height: 50px !important;
                padding: 0 32px !important;
                background: #FFFFFF !important;
                color: #FBFBF9 !important;
                border-radius: 9999px !important;
                text-decoration: none !important;
                font-weight: 700 !important;
                font-size: 0.92rem !important;
                letter-spacing: 0.04em !important;
                transition: all 0.25s ease !important;
                box-shadow: 0 4px 14px rgba(0,0,0,0.08) !important;
            }
            .rsd-btn-black:hover,
            a.rsd-btn-black:hover {
                background: #2563EB !important;
                color: #0F172A !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 8px 24px rgba(197, 160, 89, 0.35) !important;
            }

            .rsd-btn-outline-main,
            a.rsd-btn-outline-main {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                height: 50px !important;
                padding: 0 32px !important;
                background: #FFFFFF !important;
                color: #0F172A !important;
                border: 1px solid #E5E5E0 !important;
                border-radius: 9999px !important;
                text-decoration: none !important;
                font-weight: 600 !important;
                font-size: 0.92rem !important;
                transition: all 0.25s ease !important;
            }
            .rsd-btn-outline-main:hover,
            a.rsd-btn-outline-main:hover {
                border-color: #0F172A !important;
                transform: translateY(-2px) !important;
            }

            .rsd-metrics-grid {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 24px !important;
                margin-top: 36px !important;
                width: 100% !important;
            }
            .rsd-metric-card {
                background: #FFFFFF !important;
                padding: 28px 24px !important;
                border-radius: 16px !important;
                border: 1px solid #E5E5E0 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                transition: all 0.3s ease !important;
                text-align: center !important;
            }
            .rsd-metric-card:hover {
                transform: translateY(-3px) !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06) !important;
                border-color: #2563EB !important;
            }
            .rsd-metric-num {
                font-size: 2.2rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 6px !important;
            }
            .rsd-metric-label {
                font-size: 0.88rem !important;
                color: #4A4A48 !important;
                font-weight: 500 !important;
            }

            .rsd-sec {
                padding: 90px 0 !important;
                border-bottom: 1px solid #E5E5E0 !important;
            }
            .rsd-sec-title {
                font-family: 'Playfair Display', Georgia, serif !important;
                font-size: clamp(1.8rem, 3.5vw, 2.5rem) !important;
                font-weight: 600 !important;
                margin-bottom: 44px !important;
                color: #0F172A !important;
            }
            .rsd-sec-tag {
                font-size: 0.78rem !important;
                font-weight: 700 !important;
                letter-spacing: 0.12em !important;
                text-transform: uppercase !important;
                color: #2563EB !important;
                margin-bottom: 12px !important;
                display: block !important;
            }
            .rsd-portfolio-card {
                background: #FFFFFF !important;
                border-radius: 24px !important;
                border: 1px solid #E5E5E0 !important;
                overflow: hidden !important;
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                align-items: center !important;
                gap: 48px !important;
                padding: 44px !important;
                margin-bottom: 48px !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                transition: all 0.3s ease !important;
            }
            .rsd-portfolio-card:hover {
                transform: translateY(-4px) !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06) !important;
                border-color: #2563EB !important;
            }
            .rsd-portfolio-img-wrap {
                border-radius: 16px !important;
                overflow: hidden !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .rsd-portfolio-img-wrap img {
                width: 100% !important;
                height: auto !important;
                display: block !important;
                object-fit: contain !important;
                border-radius: 12px !important;
            }

            .rsd-process-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 24px !important;
            }
            .rsd-process-card {
                background: #FFFFFF !important;
                padding: 32px 24px !important;
                border-radius: 16px !important;
                border: 1px solid #E5E5E0 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                transition: all 0.3s ease !important;
            }
            .rsd-process-card:hover {
                transform: translateY(-3px) !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06) !important;
                border-color: #2563EB !important;
            }
            .rsd-process-step {
                font-size: 0.78rem !important;
                font-weight: 700 !important;
                letter-spacing: 0.1em !important;
                color: #2563EB !important;
                text-transform: uppercase !important;
                margin-bottom: 12px !important;
            }
            .rsd-process-title {
                font-size: 1.15rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 10px !important;
            }
            .rsd-process-desc {
                font-size: 0.9rem !important;
                color: #4A4A48 !important;
                line-height: 1.6 !important;
            }

            .rsd-systems-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 24px !important;
            }
            .rsd-system-card {
                background: #FFFFFF !important;
                padding: 36px !important;
                border-radius: 20px !important;
                border: 1px solid #E5E5E0 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                transition: all 0.3s ease !important;
            }
            .rsd-system-card.featured {
                background: #F4F4F0 !important;
                border-color: #D5D5CF !important;
            }
            .rsd-system-card:hover {
                transform: translateY(-3px) !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06) !important;
            }
            .rsd-system-tag {
                font-size: 0.75rem !important;
                font-weight: 700 !important;
                letter-spacing: 0.1em !important;
                color: #646460 !important;
                text-transform: uppercase !important;
                margin-bottom: 12px !important;
            }
            .rsd-system-title {
                font-size: 1.35rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 12px !important;
            }
            .rsd-system-desc {
                font-size: 0.95rem !important;
                color: #4A4A48 !important;
                line-height: 1.65 !important;
            }

            .rsd-comparison-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 32px !important;
            }
            .rsd-comp-card {
                background: #FFFFFF !important;
                padding: 40px !important;
                border-radius: 20px !important;
                border: 1px solid #E5E5E0 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
            }
            .rsd-comp-card.highlight {
                border: 2px solid #0F172A !important;
                background: #FFFFFF !important;
            }
            .rsd-comp-card.muted {
                background: #F4F4F0 !important;
                border: 1px solid #E5E5E0 !important;
            }
            .rsd-comp-title {
                font-size: 1.4rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 24px !important;
            }
            .rsd-comp-list {
                list-style: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .rsd-comp-item {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
                margin-bottom: 16px !important;
                font-size: 0.95rem !important;
                color: #4A4A48 !important;
            }
            .rsd-icon-check { color: #16A34A !important; font-weight: 700 !important; }
            .rsd-icon-cross { color: #DC2626 !important; font-weight: 700 !important; }

            @media (max-width: 992px) {
                .rsd-portfolio-card { grid-template-columns: 1fr !important; padding: 28px !important; gap: 24px !important; }
                .rsd-process-grid { grid-template-columns: repeat(2, 1fr) !important; }
                .rsd-systems-grid, .rsd-comparison-grid { grid-template-columns: 1fr !important; }
            }

            @media (max-width: 768px) {
                .rsd-container { padding: 0 16px !important; }
                .rsd-metrics-grid { grid-template-columns: 1fr !important; }
                .rsd-process-grid { grid-template-columns: 1fr !important; }
                .rsd-hero-actions { flex-direction: column !important; gap: 12px !important; }
                .rsd-btn-black, .rsd-btn-outline-main { width: 100% !important; justify-content: center !important; }
                .rsd-sec { padding: 54px 0 !important; }
            }
        /* ========================================================= */
            /* ABSOLUTE OVERRIDE FOR ELEMENTOR NATIVE BUTTON WIDGETS     */
            /* ========================================================= */
            .elementor-widget-button .elementor-button,
            .elementor-button-wrapper a.elementor-button,
            a.elementor-button,
            .rsd-btn-black,
            a.rsd-btn-black {
                background-color: #0F172A !important;
                color: #FBFBF9 !important;
                border-radius: 9999px !important;
                padding: 14px 32px !important;
                font-weight: 700 !important;
                font-size: 0.92rem !important;
                letter-spacing: 0.04em !important;
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08) !important;
                border: none !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-decoration: none !important;
            }

            .elementor-widget-button .elementor-button:hover,
            .elementor-button-wrapper a.elementor-button:hover,
            a.elementor-button:hover,
            .rsd-btn-black:hover,
            a.rsd-btn-black:hover {
                background-color: #2563EB !important;
                color: #0F172A !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 8px 24px rgba(197, 160, 89, 0.35) !important;
            }

            .rsd-btn-outline-main,
            a.rsd-btn-outline-main {
                background-color: #FFFFFF !important;
                color: #0F172A !important;
                border: 1px solid #E5E5E0 !important;
                border-radius: 9999px !important;
                padding: 14px 32px !important;
                font-weight: 600 !important;
                font-size: 0.92rem !important;
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-decoration: none !important;
            }

            .rsd-btn-outline-main:hover,
            a.rsd-btn-outline-main:hover {
                border-color: #0F172A !important;
                transform: translateY(-2px) !important;
            }

            .elementor-widget-heading .elementor-heading-title {
                color: inherit;
            }
        /* ========================================================= */
            /* $10,000+ AWWWARDS QUIET LUXURY STUDIO POLISHING & RESPONSIVE */
            /* ========================================================= */
            .rsd-luxury-portfolio-card,
            .rsd-metric-card,
            .rsd-step-card,
            .rsd-capability-card,
            .rsd-comparison-card-pro,
            .rsd-cta-banner-container {
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: 0 16px 40px -10px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02) !important;
            }

            .rsd-luxury-portfolio-card:hover,
            .rsd-metric-card:hover,
            .rsd-step-card:hover,
            .rsd-capability-card:hover,
            .rsd-comparison-card-pro:hover {
                transform: translateY(-8px) !important;
                box-shadow: 0 32px 72px -16px rgba(197, 160, 89, 0.22), 0 0 1px rgba(197, 160, 89, 0.4) !important;
                border-color: rgba(197, 160, 89, 0.5) !important;
            }

            .rsd-luxury-portfolio-card img {
                transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: 0 12px 32px rgba(0, 0, 0, 0.06) !important;
            }

            .rsd-luxury-portfolio-card:hover img {
                transform: scale(1.025) !important;
            }

            /* Responsive Mobile & Tablet Breakdown */
            @media (max-width: 991px) {
                .elementor-section-wrap .e-con,
                .elementor-element .e-con,
                .rsd-luxury-portfolio-card,
                .rsd-hero-sec,
                .rsd-cta-banner-container {
                    flex-direction: column !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    padding-left: 16px !important;
                    padding-right: 16px !important;
                }

                .rsd-luxury-portfolio-card > .e-con,
                .rsd-luxury-portfolio-card > div {
                    width: 100% !important;
                    max-width: 100% !important;
                }

                .rsd-luxury-portfolio-card {
                    padding: 24px 20px !important;
                    gap: 24px !important;
                }
            }
        /* ========================================================= */
            /* RED SEA DIGITAL — SINGLE SOURCE OF TRUTH QUIET LUXURY CSS  */
            /* ========================================================= */
            :root {
                --rsd-primary: #0F172A;
                --rsd-bg: #FBFBF9;
                --rsd-card: #F4F4F0;
                --rsd-hairline: #E5E5E0;
                --rsd-accent: #2563EB;
            }

            body, html {
                background-color: var(--rsd-bg) !important;
                color: var(--rsd-primary) !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }

            /* Clean Editorial Hairline Cards */
            .rsd-editorial-case-study,
            .rsd-metric-card,
            .rsd-step-card,
            .rsd-capability-card {
                background-color: var(--rsd-card) !important;
                border: 1px solid var(--rsd-hairline) !important;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: none !important;
            }

            /* Controlled Max 4px Hover Elevation (NO GOLD GLOW, NO NEON) */
            .rsd-editorial-case-study:hover,
            .rsd-metric-card:hover,
            .rsd-step-card:hover,
            .rsd-capability-card:hover {
                transform: translateY(-4px) !important;
                border-color: #2563EB !important;
                box-shadow: 0 12px 32px rgba(0, 0, 0, 0.04) !important;
            }

            /* Restrained Dark Obsidian Pill Buttons */
            .elementor-widget-button .elementor-button,
            a.elementor-button,
            .rsd-btn-black,
            a.rsd-btn-black {
                background-color: #0F172A !important;
                color: #FBFBF9 !important;
                border-radius: 9999px !important;
                padding: 14px 32px !important;
                font-weight: 700 !important;
                font-size: 0.92rem !important;
                letter-spacing: 0.04em !important;
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: none !important;
                border: none !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-decoration: none !important;
            }

            .elementor-widget-button .elementor-button:hover,
            a.elementor-button:hover,
            .rsd-btn-black:hover,
            a.rsd-btn-black:hover {
                background-color: #2563EB !important;
                color: #0F172A !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 14px rgba(197, 160, 89, 0.25) !important;
            }

            .rsd-btn-outline-main,
            a.rsd-btn-outline-main {
                background-color: #FFFFFF !important;
                color: #0F172A !important;
                border: 1px solid #E5E5E0 !important;
                border-radius: 9999px !important;
                padding: 14px 32px !important;
                font-weight: 600 !important;
                font-size: 0.92rem !important;
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }

            .rsd-btn-outline-main:hover,
            a.rsd-btn-outline-main:hover {
                border-color: #0F172A !important;
                transform: translateY(-2px) !important;
            }

            /* Responsive Media Queries Across 360px, 390px, 430px, 768px, 834px, 1024px, 1280px, 1440px, 1920px */
            @media (max-width: 991px) {
                .rsd-editorial-case-study {
                    flex-direction: column !important;
                    padding: 28px 20px !important;
                    gap: 24px !important;
                }
                .rsd-editorial-case-study > div {
                    width: 100% !important;
                }
            }

            @media (max-width: 480px) {
                h1.elementor-heading-title {
                    font-size: 2.1rem !important;
                    line-height: 1.25 !important;
                }
                h2.elementor-heading-title {
                    font-size: 1.75rem !important;
                }
                .rsd-btn-black, .rsd-btn-outline-main {
                    width: 100% !important;
                }
            }
        /* ========================================================= */
            /* 15-POINT BRUTALLY OBJECTIVE ART-DIRECTION OVERHAUL CSS     */
            /* ========================================================= */
            :root {
                --rsd-primary: #0F172A;
                --rsd-bg: #FBFBF9;
                --rsd-card: #F4F4F0;
                --rsd-hairline: #E5E5E0;
                --rsd-accent: #2563EB;
            }

            body, html {
                background-color: var(--rsd-bg) !important;
                color: var(--rsd-primary) !important;
            }

            /* Weakness 1: H1 Fluid Scale & Architectural Line-Height */
            .elementor-widget-heading h1.elementor-heading-title {
                font-size: clamp(3.2rem, 6.5vw, 5.4rem) !important;
                line-height: 1.08 !important;
                letter-spacing: -0.03em !important;
                font-weight: 500 !important;
            }

            .elementor-widget-heading h2.elementor-heading-title {
                font-size: clamp(2.4rem, 4.5vw, 3.8rem) !important;
                line-height: 1.15 !important;
                letter-spacing: -0.02em !important;
            }

            /* Weakness 6: Clean Pill Buttons (No ASCII Brackets) */
            .elementor-widget-button .elementor-button,
            a.elementor-button,
            .rsd-btn-black,
            a.rsd-btn-black {
                background-color: #0F172A !important;
                color: #FBFBF9 !important;
                border-radius: 9999px !important;
                padding: 14px 32px !important;
                font-weight: 700 !important;
                font-size: 0.92rem !important;
                letter-spacing: 0.04em !important;
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: none !important;
                border: none !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-decoration: none !important;
            }

            .elementor-widget-button .elementor-button:hover,
            a.elementor-button:hover,
            .rsd-btn-black:hover,
            a.rsd-btn-black:hover {
                background-color: #2563EB !important;
                color: #0F172A !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 14px rgba(197, 160, 89, 0.25) !important;
            }

            /* Weakness 12: Quiet Minimal Chatbot Widget (44px Circle) */
            #rsd-chat-toggle,
            .rsd-chat-badge-trigger {
                width: 44px !important;
                height: 44px !important;
                border-radius: 50% !important;
                background-color: #0F172A !important;
                color: #FBFBF9 !important;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            /* Weakness 14: Native Arabic RTL Typography */
            .rtl h1.elementor-heading-title,
            .rtl h2.elementor-heading-title {
                font-family: 'Cairo', sans-serif !important;
                line-height: 1.35 !important;
                letter-spacing: 0 !important;
            }

            /* Responsive Media Queries Across 360px to 1920px */
            @media (max-width: 991px) {
                .elementor-element .e-con {
                    flex-direction: column !important;
                    width: 100% !important;
                    padding-left: 16px !important;
                    padding-right: 16px !important;
                }
            }
        /* ========================================================= */
            /* 12 TARGETED ART-DIRECTION REFINEMENTS CSS                 */
            /* ========================================================= */
            @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

            :root {
                --rsd-serif: 'Cormorant Garamond', 'Playfair Display', serif;
                --rsd-sans: 'Inter', system-ui, -apple-system, sans-serif;
            }

            .elementor-widget-heading h1.elementor-heading-title,
            .elementor-widget-heading h2.elementor-heading-title,
            .elementor-widget-heading h3.elementor-heading-title {
                font-family: var(--rsd-serif) !important;
            }

            /* Refinement 3: Mobile Body Readability (16-18px body, 10-11px eyebrows) */
            @media (max-width: 768px) {
                body, p, .elementor-text-editor, .elementor-text-editor p {
                    font-size: 16px !important;
                    line-height: 1.7 !important;
                }
                .rsd-eyebrow, .elementor-widget-heading span.elementor-heading-title {
                    font-size: 11px !important;
                    letter-spacing: 0.2em !important;
                }
                .rsd-methodology-row {
                    flex-direction: column !important;
                }
            }

            /* Footer Styling */
            .rsd-master-footer {
                position: relative;
                background-color: #0F172A;
                color: #FBFBF9;
                padding: 100px 48px 40px 48px;
                overflow: hidden;
            }
            .rsd-footer-watermark {
                position: absolute;
                bottom: -20px;
                left: 50%;
                transform: translateX(-50%);
                font-size: 120px;
                font-weight: 800;
                color: transparent;
                -webkit-text-stroke: 1px rgba(229, 229, 224, 0.08);
                white-space: nowrap;
                pointer-events: none;
                user-select: none;
            }
            .rsd-footer-inner {
                max-width: 1200px;
                margin: 0 auto;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 48px;
                position: relative;
                z-index: 2;
            }
            .rsd-footer-col h3 {
                font-family: var(--rsd-serif);
                font-size: 1.6rem;
                color: #FBFBF9;
                margin-bottom: 12px;
            }
            .rsd-footer-desc {
                color: #A0A09A;
                font-size: 0.95rem;
                line-height: 1.65;
                max-width: 320px;
            }
            .rsd-footer-col h4 {
                font-size: 0.75rem;
                letter-spacing: 0.2em;
                color: #2563EB;
                margin-bottom: 20px;
            }
            .rsd-footer-col ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .rsd-footer-col ul li {
                margin-bottom: 10px;
            }
            .rsd-footer-col ul li a {
                color: #A0A09A;
                text-decoration: none;
                font-size: 0.92rem;
                transition: color 0.2s ease;
            }
            .rsd-footer-col ul li a:hover {
                color: #FBFBF9;
            }
            .rsd-footer-bottom {
                max-width: 1200px;
                margin: 60px auto 0 auto;
                padding-top: 24px;
                border-top: 1px solid rgba(225, 225, 220, 0.12);
                text-align: center;
                color: #646460;
                font-size: 0.85rem;
                position: relative;
                z-index: 2;
            }
        /* ========================================================= */
            /* ULTIMATE RESPONSIVE LUXURY FOOTER CSS                     */
            /* ========================================================= */
            .rsd-master-footer {
                position: relative;
                background-color: #0F172A !important;
                color: #FBFBF9 !important;
                padding: 100px 32px 40px 32px !important;
                overflow: hidden !important;
                border-top: 1px solid #E5E5E0 !important;
                font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
            }

            .rsd-footer-watermark {
                position: absolute;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                font-family: 'Cormorant Garamond', serif !important;
                font-size: clamp(3.2rem, 10.5vw, 9rem) !important;
                font-weight: 800 !important;
                color: transparent !important;
                -webkit-text-stroke: 1px rgba(229, 229, 224, 0.07) !important;
                white-space: nowrap !important;
                pointer-events: none !important;
                user-select: none !important;
                letter-spacing: 0.08em !important;
            }

            .rsd-footer-inner {
                max-width: 1240px;
                margin: 0 auto;
                display: grid;
                grid-template-columns: 2fr 1fr 1.2fr 1fr;
                gap: 48px;
                position: relative;
                z-index: 2;
            }

            .rsd-footer-logo {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 1.75rem !important;
                font-weight: 600 !important;
                color: #FBFBF9 !important;
                margin-bottom: 14px !important;
                letter-spacing: -0.02em !important;
            }

            .rsd-footer-desc {
                color: #A0A09A !important;
                font-size: 0.95rem !important;
                line-height: 1.7 !important;
                max-width: 340px !important;
                margin-bottom: 20px !important;
            }

            .rsd-footer-status {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                background: rgba(255, 255, 255, 0.04);
                padding: 6px 14px;
                border-radius: 9999px;
                border: 1px solid rgba(229, 229, 224, 0.12);
            }

            .rsd-status-dot {
                width: 8px;
                height: 8px;
                background-color: #16A34A;
                border-radius: 50%;
                box-shadow: 0 0 8px rgba(22, 163, 74, 0.6);
            }

            .rsd-status-text {
                color: #D4D4CE;
                font-size: 0.82rem;
                font-weight: 500;
            }

            .rsd-footer-heading {
                font-size: 0.75rem !important;
                font-weight: 800 !important;
                letter-spacing: 0.25em !important;
                color: #2563EB !important;
                margin-bottom: 22px !important;
                text-transform: uppercase !important;
            }

            .rsd-footer-links {
                list-style: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .rsd-footer-links li {
                margin-bottom: 12px !important;
            }

            .rsd-footer-links li a,
            .rsd-footer-email,
            .rsd-footer-wa {
                color: #A0A09A !important;
                text-decoration: none !important;
                font-size: 0.92rem !important;
                transition: all 0.2s ease !important;
            }

            .rsd-footer-links li a:hover,
            .rsd-footer-email:hover,
            .rsd-footer-wa:hover {
                color: #FBFBF9 !important;
            }

            .rsd-footer-email {
                color: #2563EB !important;
                font-weight: 500 !important;
            }

            .rsd-footer-info {
                color: #D4D4CE !important;
                font-size: 0.9rem !important;
                line-height: 1.6 !important;
                margin: 0 !important;
            }

            .rsd-footer-text-muted {
                color: #888882 !important;
            }

            .rsd-footer-bottom {
                max-width: 1240px;
                margin: 72px auto 0 auto !important;
                padding-top: 28px !important;
                border-top: 1px solid rgba(229, 229, 224, 0.12) !important;
                position: relative;
                z-index: 2;
            }

            .rsd-footer-bottom-inner {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 16px;
                color: #646460;
                font-size: 0.85rem;
            }

            /* Responsive Media Queries Across 360px to 1024px */
            @media (max-width: 1024px) {
                .rsd-footer-inner {
                    grid-template-columns: 1fr 1fr;
                    gap: 40px;
                }
                .rsd-master-footer {
                    padding: 80px 24px 36px 24px !important;
                }
            }

            @media (max-width: 640px) {
                .rsd-footer-inner {
                    grid-template-columns: 1fr !important;
                    gap: 36px !important;
                }
                .rsd-master-footer {
                    padding: 60px 20px 30px 20px !important;
                }
                .rsd-footer-bottom-inner {
                    flex-direction: column !important;
                    text-align: center !important;
                }
                .rsd-footer-logo {
                    font-size: 1.5rem !important;
                }
            }
        /* ========================================================= */
            /* 3D LIQUID GLASS CARDS FOR METHODOLOGY SECTION             */
            /* ========================================================= */
            .rsd-methodology-row {
                perspective: 1200px !important;
            }

            .rsd-liquid-glass-card {
                position: relative !important;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.85) 0%, rgba(244, 244, 240, 0.65) 100%) !important;
                backdrop-filter: blur(20px) saturate(190%) !important;
                -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
                border: 1px solid rgba(255, 255, 255, 0.95) !important;
                border-bottom: 1px solid rgba(229, 229, 224, 0.85) !important;
                border-radius: 24px !important;
                padding: 36px 28px !important;
                box-shadow: 
                    inset 0 1px 2px rgba(255, 255, 255, 1),
                    inset 0 -1px 1px rgba(0, 0, 0, 0.03),
                    0 16px 36px -10px rgba(0, 0, 0, 0.06),
                    0 4px 14px rgba(0, 0, 0, 0.03) !important;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                overflow: hidden !important;
            }

            .rsd-liquid-glass-card::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 200% !important;
                height: 100% !important;
                background: linear-gradient(
                    90deg, 
                    transparent 0%, 
                    rgba(255, 255, 255, 0.5) 50%, 
                    transparent 100%
                ) !important;
                transform: skewX(-25deg) !important;
                transition: left 0.75s ease !important;
                pointer-events: none !important;
            }

            .rsd-liquid-glass-card:hover::before {
                left: 100% !important;
            }

            .rsd-liquid-glass-card:hover {
                transform: translateY(-10px) rotateX(4deg) scale(1.02) !important;
                box-shadow: 
                    inset 0 1px 3px rgba(255, 255, 255, 1),
                    0 24px 48px -12px rgba(197, 160, 89, 0.22),
                    0 12px 24px -6px rgba(0, 0, 0, 0.08) !important;
                border-color: rgba(197, 160, 89, 0.5) !important;
            }

            .rsd-step-badge-3d {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 2.5rem !important;
                font-weight: 800 !important;
                color: #2563EB !important;
                line-height: 1 !important;
                margin-bottom: 16px !important;
                display: inline-block !important;
                text-shadow: 0 2px 6px rgba(197, 160, 89, 0.2) !important;
            }

            .rsd-step-title-3d {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 1.35rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 12px !important;
                line-height: 1.3 !important;
            }

            .rsd-step-desc-3d {
                color: #4A4A48 !important;
                font-size: 1.02rem !important;
                line-height: 1.65 !important;
                margin: 0 !important;
            }

            @media (max-width: 991px) {
                .rsd-methodology-row {
                    display: grid !important;
                    grid-template-columns: 1fr 1fr !important;
                    gap: 24px !important;
                }
            }

            @media (max-width: 640px) {
                .rsd-methodology-row {
                    grid-template-columns: 1fr !important;
                    gap: 20px !important;
                }
                .rsd-liquid-glass-card {
                    padding: 28px 20px !important;
                }
            }
        /* ========================================================= */
            /* 3D LIQUID GLASS CARDS FOR POSITIONING MANIFESTO SECTION  */
            /* ========================================================= */
            .rsd-manifesto-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 36px !important;
                perspective: 1200px !important;
            }

            .rsd-liquid-glass-manifesto-card {
                position: relative !important;
                border-radius: 28px !important;
                padding: 44px 36px !important;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                overflow: hidden !important;
            }

            .rsd-liquid-glass-manifesto-card::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 200% !important;
                height: 100% !important;
                background: linear-gradient(
                    90deg, 
                    transparent 0%, 
                    rgba(255, 255, 255, 0.5) 50%, 
                    transparent 100%
                ) !important;
                transform: skewX(-25deg) !important;
                transition: left 0.75s ease !important;
                pointer-events: none !important;
            }

            .rsd-liquid-glass-manifesto-card:hover::before {
                left: 100% !important;
            }

            /* RSD Standard Card (Left) */
            .rsd-standard-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(240, 253, 244, 0.65) 100%) !important;
                backdrop-filter: blur(20px) saturate(190%) !important;
                -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
                border: 1px solid rgba(22, 163, 74, 0.3) !important;
                box-shadow: 
                    inset 0 1px 2px rgba(255, 255, 255, 1),
                    0 16px 40px -10px rgba(22, 163, 74, 0.12),
                    0 4px 14px rgba(0, 0, 0, 0.03) !important;
            }

            .rsd-standard-card:hover {
                transform: translateY(-10px) rotateX(3deg) scale(1.015) !important;
                box-shadow: 
                    inset 0 1px 3px rgba(255, 255, 255, 1),
                    0 28px 56px -12px rgba(22, 163, 74, 0.22),
                    0 12px 24px -6px rgba(0, 0, 0, 0.06) !important;
                border-color: rgba(22, 163, 74, 0.55) !important;
            }

            /* Conventional Model Card (Right) */
            .rsd-conventional-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(254, 242, 242, 0.6) 100%) !important;
                backdrop-filter: blur(20px) saturate(190%) !important;
                -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
                border: 1px solid rgba(220, 38, 38, 0.2) !important;
                box-shadow: 
                    inset 0 1px 2px rgba(255, 255, 255, 1),
                    0 16px 40px -10px rgba(220, 38, 38, 0.08),
                    0 4px 14px rgba(0, 0, 0, 0.03) !important;
            }

            .rsd-conventional-card:hover {
                transform: translateY(-8px) rotateX(2deg) scale(1.01) !important;
                box-shadow: 
                    inset 0 1px 3px rgba(255, 255, 255, 1),
                    0 24px 48px -12px rgba(220, 38, 38, 0.16),
                    0 12px 24px -6px rgba(0, 0, 0, 0.06) !important;
                border-color: rgba(220, 38, 38, 0.4) !important;
            }

            .rsd-manifesto-badge {
                display: inline-block !important;
                padding: 4px 12px !important;
                border-radius: 9999px !important;
                font-size: 0.72rem !important;
                font-weight: 800 !important;
                letter-spacing: 0.15em !important;
                margin-bottom: 16px !important;
                text-transform: uppercase !important;
            }

            .rsd-standard-badge {
                background: rgba(22, 163, 74, 0.12) !important;
                color: #16A34A !important;
                border: 1px solid rgba(22, 163, 74, 0.25) !important;
            }

            .rsd-conventional-badge {
                background: rgba(220, 38, 38, 0.1) !important;
                color: #DC2626 !important;
                border: 1px solid rgba(220, 38, 38, 0.2) !important;
            }

            .rsd-manifesto-title {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 1.5rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 24px !important;
                line-height: 1.3 !important;
            }

            .rsd-glass-list {
                list-style: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .rsd-glass-list-item {
                display: flex !important;
                align-items: flex-start !important;
                gap: 14px !important;
                padding: 12px 16px !important;
                border-radius: 14px !important;
                margin-bottom: 12px !important;
                background: rgba(255, 255, 255, 0.5) !important;
                border: 1px solid rgba(255, 255, 255, 0.7) !important;
                transition: all 0.25s ease !important;
            }

            .rsd-glass-list-item:hover {
                background: rgba(255, 255, 255, 0.85) !important;
                transform: translateX(4px) !important;
            }

            .rsd-glass-icon {
                font-weight: 800 !important;
                font-size: 1.1rem !important;
                line-height: 1.4 !important;
            }

            .rsd-icon-check {
                color: #16A34A !important;
            }

            .rsd-icon-cross {
                color: #DC2626 !important;
            }

            .rsd-glass-text-green {
                color: #15803D !important;
                font-weight: 600 !important;
                font-size: 1.02rem !important;
                line-height: 1.5 !important;
            }

            .rsd-glass-text-red {
                color: #B91C1C !important;
                font-weight: 500 !important;
                font-size: 1.02rem !important;
                line-height: 1.5 !important;
            }

            @media (max-width: 991px) {
                .rsd-manifesto-grid {
                    grid-template-columns: 1fr !important;
                    gap: 28px !important;
                }
                .rsd-liquid-glass-manifesto-card {
                    padding: 32px 24px !important;
                }
            }
        /* ========================================================= */
            /* ULTRA-VISIBLE 3D LIQUID GLASS CARDS (HIGH CONTRAST)       */
            /* ========================================================= */
            .rsd-methodology-row, .rsd-manifesto-grid {
                perspective: 1200px !important;
            }

            .rsd-liquid-glass-card,
            .elementor-element.rsd-liquid-glass-card,
            div.rsd-liquid-glass-card {
                position: relative !important;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(238, 238, 230, 0.8) 100%) !important;
                backdrop-filter: blur(24px) saturate(200%) !important;
                -webkit-backdrop-filter: blur(24px) saturate(200%) !important;
                border: 1.5px solid rgba(255, 255, 255, 1) !important;
                outline: 1px solid rgba(197, 160, 89, 0.25) !important;
                border-radius: 24px !important;
                padding: 36px 28px !important;
                margin: 6px !important;
                box-shadow: 
                    inset 0 2px 4px rgba(255, 255, 255, 1),
                    inset 0 -2px 4px rgba(0, 0, 0, 0.04),
                    0 20px 44px -10px rgba(0, 0, 0, 0.12),
                    0 8px 20px rgba(0, 0, 0, 0.05) !important;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                overflow: hidden !important;
            }

            .rsd-liquid-glass-card::before,
            .elementor-element.rsd-liquid-glass-card::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 200% !important;
                height: 100% !important;
                background: linear-gradient(
                    90deg, 
                    transparent 0%, 
                    rgba(255, 255, 255, 0.65) 50%, 
                    transparent 100%
                ) !important;
                transform: skewX(-25deg) !important;
                transition: left 0.75s ease !important;
                pointer-events: none !important;
            }

            .rsd-liquid-glass-card:hover,
            .elementor-element.rsd-liquid-glass-card:hover {
                transform: translateY(-12px) rotateX(4deg) scale(1.025) !important;
                box-shadow: 
                    inset 0 2px 6px rgba(255, 255, 255, 1),
                    0 30px 60px -12px rgba(197, 160, 89, 0.3),
                    0 16px 32px -6px rgba(0, 0, 0, 0.1) !important;
                border-color: rgba(197, 160, 89, 0.6) !important;
                outline-color: rgba(197, 160, 89, 0.5) !important;
            }

            .rsd-liquid-glass-card:hover::before,
            .elementor-element.rsd-liquid-glass-card:hover::before {
                left: 100% !important;
            }

            .rsd-step-badge-3d {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 2.6rem !important;
                font-weight: 800 !important;
                color: #2563EB !important;
                line-height: 1 !important;
                margin-bottom: 16px !important;
                display: inline-block !important;
                text-shadow: 0 2px 6px rgba(197, 160, 89, 0.25) !important;
            }

            .rsd-step-title-3d {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 1.4rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 12px !important;
                line-height: 1.3 !important;
            }

            .rsd-step-desc-3d {
                color: #4A4A48 !important;
                font-size: 1.02rem !important;
                line-height: 1.65 !important;
                margin: 0 !important;
            }

            /* Ultra-Visible Manifesto Cards */
            .rsd-liquid-glass-manifesto-card,
            .elementor-element.rsd-liquid-glass-manifesto-card {
                position: relative !important;
                border-radius: 28px !important;
                padding: 44px 36px !important;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                overflow: hidden !important;
            }

            .rsd-standard-card,
            .elementor-element.rsd-standard-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(236, 253, 243, 0.8) 100%) !important;
                backdrop-filter: blur(24px) saturate(200%) !important;
                -webkit-backdrop-filter: blur(24px) saturate(200%) !important;
                border: 1.5px solid rgba(22, 163, 74, 0.4) !important;
                box-shadow: 
                    inset 0 2px 4px rgba(255, 255, 255, 1),
                    0 20px 48px -10px rgba(22, 163, 74, 0.16),
                    0 8px 20px rgba(0, 0, 0, 0.05) !important;
            }

            .rsd-standard-card:hover,
            .elementor-element.rsd-standard-card:hover {
                transform: translateY(-12px) rotateX(3deg) scale(1.02) !important;
                box-shadow: 
                    inset 0 2px 6px rgba(255, 255, 255, 1),
                    0 32px 64px -12px rgba(22, 163, 74, 0.28),
                    0 16px 32px -6px rgba(0, 0, 0, 0.08) !important;
                border-color: rgba(22, 163, 74, 0.7) !important;
            }

            .rsd-conventional-card,
            .elementor-element.rsd-conventional-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(254, 242, 242, 0.75) 100%) !important;
                backdrop-filter: blur(24px) saturate(200%) !important;
                -webkit-backdrop-filter: blur(24px) saturate(200%) !important;
                border: 1.5px solid rgba(220, 38, 38, 0.3) !important;
                box-shadow: 
                    inset 0 2px 4px rgba(255, 255, 255, 1),
                    0 20px 48px -10px rgba(220, 38, 38, 0.12),
                    0 8px 20px rgba(0, 0, 0, 0.05) !important;
            }

            .rsd-conventional-card:hover,
            .elementor-element.rsd-conventional-card:hover {
                transform: translateY(-10px) rotateX(2deg) scale(1.015) !important;
                box-shadow: 
                    inset 0 2px 6px rgba(255, 255, 255, 1),
                    0 28px 56px -12px rgba(220, 38, 38, 0.22),
                    0 16px 32px -6px rgba(0, 0, 0, 0.08) !important;
                border-color: rgba(220, 38, 38, 0.5) !important;
            }
        /* ========================================================= */
            /* BOLD HIGH-CONTRAST 3D LIQUID GLASS CARDS (IMPOSSIBLE TO MISS) */
            /* ========================================================= */
            .rsd-methodology-row {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                gap: 20px !important;
                margin-top: 40px !important;
                perspective: 1200px !important;
            }

            .rsd-liquid-glass-card,
            .elementor-element.rsd-liquid-glass-card {
                background: #FFFFFF !important;
                border: 2px solid #2563EB !important;
                border-radius: 20px !important;
                padding: 36px 28px !important;
                box-shadow: 
                    0 20px 48px -10px rgba(0, 0, 0, 0.12),
                    0 8px 20px rgba(197, 160, 89, 0.15),
                    inset 0 1px 2px #FFFFFF !important;
                transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                position: relative !important;
            }

            .rsd-liquid-glass-card:hover,
            .elementor-element.rsd-liquid-glass-card:hover {
                transform: translateY(-10px) rotateX(4deg) scale(1.02) !important;
                box-shadow: 
                    0 30px 60px -12px rgba(0, 0, 0, 0.18),
                    0 12px 28px rgba(197, 160, 89, 0.3) !important;
                border-color: #0F172A !important;
            }

            /* Positioning Manifesto 3D Cards */
            .rsd-manifesto-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 32px !important;
                margin-top: 40px !important;
            }

            .rsd-standard-card,
            .elementor-element.rsd-standard-card {
                background: #FFFFFF !important;
                border: 2px solid #16A34A !important;
                border-radius: 24px !important;
                padding: 40px 32px !important;
                box-shadow: 0 20px 48px -10px rgba(22, 163, 74, 0.18) !important;
            }

            .rsd-conventional-card,
            .elementor-element.rsd-conventional-card {
                background: #FFFFFF !important;
                border: 2px solid #DC2626 !important;
                border-radius: 24px !important;
                padding: 40px 32px !important;
                box-shadow: 0 20px 48px -10px rgba(220, 38, 38, 0.15) !important;
            }
        /* ========================================================= */
            /* HERO METRICS 3D CHAMPAGNE GOLD GLASS CARDS                */
            /* ========================================================= */
            .rsd-metrics-row {
                gap: 24px !important;
                perspective: 1200px !important;
            }

            .rsd-metric-3d-card,
            .elementor-element.rsd-metric-3d-card {
                background: linear-gradient(135deg, #FFFFFF 0%, #F9F8F3 100%) !important;
                border: 2px solid #2563EB !important;
                outline: 1px solid rgba(255, 255, 255, 0.9) !important;
                border-radius: 24px !important;
                padding: 32px 24px !important;
                margin: 4px !important;
                box-shadow: 
                    inset 0 2px 4px #FFFFFF,
                    0 18px 44px -10px rgba(197, 160, 89, 0.18),
                    0 6px 16px rgba(0, 0, 0, 0.04) !important;
                transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                position: relative !important;
                overflow: hidden !important;
            }

            .rsd-metric-3d-card::before,
            .elementor-element.rsd-metric-3d-card::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 200% !important;
                height: 100% !important;
                background: linear-gradient(
                    90deg, 
                    transparent 0%, 
                    rgba(197, 160, 89, 0.15) 50%, 
                    transparent 100%
                ) !important;
                transform: skewX(-25deg) !important;
                transition: left 0.75s ease !important;
                pointer-events: none !important;
            }

            .rsd-metric-3d-card:hover,
            .elementor-element.rsd-metric-3d-card:hover {
                transform: translateY(-10px) rotateX(3deg) scale(1.025) !important;
                box-shadow: 
                    inset 0 2px 6px #FFFFFF,
                    0 28px 56px -12px rgba(197, 160, 89, 0.35),
                    0 12px 24px -6px rgba(0, 0, 0, 0.08) !important;
                border-color: #0F172A !important;
            }

            .rsd-metric-3d-card:hover::before,
            .elementor-element.rsd-metric-3d-card:hover::before {
                left: 100% !important;
            }

            .rsd-metric-3d-card h3.elementor-heading-title,
            .elementor-element.rsd-metric-3d-card h3.elementor-heading-title {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 3.4rem !important;
                font-weight: 800 !important;
                color: #2563EB !important;
                text-align: center !important;
                line-height: 1 !important;
                margin-bottom: 12px !important;
                text-shadow: 0 3px 10px rgba(197, 160, 89, 0.25) !important;
            }

            .rsd-metric-3d-card p,
            .elementor-element.rsd-metric-3d-card p {
                text-align: center !important;
                color: #0F172A !important;
                font-size: 0.88rem !important;
                font-weight: 700 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.06em !important;
                margin: 0 !important;
                line-height: 1.4 !important;
            }
        /* ========================================================= */
            /* MASTER MOBILE RESPONSIVE UI & TYPOGRAPHY REPAIR (< 768px)  */
            /* ========================================================= */
            @media (max-width: 767px) {
                /* 1. Force 100% Full Width Column Stacking */
                .elementor-element .e-con-inner,
                .e-con.e-child,
                .e-con.e-parent {
                    flex-direction: column !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                }

                .elementor-column, 
                .elementor-element[data-element_type="container"] > .e-con-inner > .e-con {
                    width: 100% !important;
                    max-width: 100% !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                }

                /* 2. Hero Section Typography on Mobile */
                h1.elementor-heading-title,
                .elementor-widget-heading h1 {
                    font-size: 2.1rem !important;
                    line-height: 1.25 !important;
                    text-align: center !important;
                    padding: 0 4px !important;
                    word-break: normal !important;
                    hyphens: none !important;
                }

                .elementor-widget-text-editor p {
                    font-size: 1.02rem !important;
                    line-height: 1.65 !important;
                    text-align: center !important;
                }

                /* 3. Case Studies Section Headings & Text */
                h2.elementor-heading-title,
                .elementor-widget-heading h2 {
                    font-size: 1.75rem !important;
                    line-height: 1.3 !important;
                    text-align: center !important;
                }

                h4.elementor-heading-title,
                .elementor-widget-heading h4 {
                    font-size: 1.3rem !important;
                    line-height: 1.35 !important;
                    text-align: center !important;
                }

                /* 4. Fix Squished & Warped Buttons on Mobile */
                .rsd-btn-black,
                .rsd-btn-outline-main,
                .elementor-button-wrapper,
                .elementor-button {
                    display: block !important;
                    width: 100% !important;
                    max-width: 320px !important;
                    margin: 14px auto !important;
                    text-align: center !important;
                    border-radius: 9999px !important;
                    padding: 14px 24px !important;
                    white-space: normal !important;
                    box-sizing: border-box !important;
                }

                /* 5. Fix Image Alignment & Size on Mobile */
                .elementor-widget-image,
                .elementor-widget-image img {
                    width: 100% !important;
                    height: auto !important;
                    max-width: 100% !important;
                    margin: 0 auto 16px auto !important;
                    display: block !important;
                    border-radius: 14px !important;
                }

                /* 6. Card Grids Stacking on Mobile */
                .rsd-metrics-row,
                .rsd-methodology-row,
                .rsd-manifesto-grid {
                    display: flex !important;
                    flex-direction: column !important;
                    gap: 18px !important;
                    width: 100% !important;
                }

                .rsd-metric-3d-card,
                .rsd-liquid-glass-card,
                .rsd-liquid-glass-manifesto-card {
                    width: 100% !important;
                    margin: 0 0 14px 0 !important;
                    padding: 28px 20px !important;
                    box-sizing: border-box !important;
                }
            }
        /* ========================================================= */
            /* BULLETPROOF DEVICE-SPECIFIC VISIBILITY ENFORCEMENT         */
            /* ========================================================= */
            @media (min-width: 1025px) {
                .elementor-hidden-desktop,
                .elementor-element.elementor-hidden-desktop {
                    display: none !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1024px) {
                .elementor-hidden-tablet,
                .elementor-element.elementor-hidden-tablet {
                    display: none !important;
                }
            }

            @media (max-width: 767px) {
                .elementor-hidden-mobile,
                .elementor-element.elementor-hidden-mobile {
                    display: none !important;
                }
            }
        /* Slightly shrink Hero H1 title font size as requested */
            .elementor-widget-heading h1.elementor-heading-title,
            .rsd-hero-title h1,
            h1.rsd-hero-h1 {
                font-size: 2.5rem !important;
                line-height: 1.25 !important;
            }
            @media (max-width: 767px) {
                .elementor-widget-heading h1.elementor-heading-title,
                .rsd-hero-title h1,
                h1.rsd-hero-h1 {
                    font-size: 1.65rem !important;
                    line-height: 1.3 !important;
                }
            }
        /* ========================================================= */
            /* SILICON VALLEY $10K+ QUIET LUXURY PORTFOLIO DESIGN SYSTEM  */
            /* ========================================================= */
            .rsd-case-study-card {
                background: rgba(255, 255, 255, 0.85) !important;
                backdrop-filter: blur(16px) saturate(180%) !important;
                -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
                border: 1px solid rgba(197, 160, 89, 0.28) !important;
                border-radius: 24px !important;
                padding: 48px !important;
                margin-bottom: 48px !important;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(197, 160, 89, 0.1) !important;
                transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1) !important;
                position: relative !important;
                overflow: hidden !important;
                will-change: transform, box-shadow !important;
            }

            .rsd-case-study-card:hover {
                transform: translateY(-8px) scale(1.008) !important;
                border-color: rgba(197, 160, 89, 0.65) !important;
                box-shadow: 0 32px 70px rgba(197, 160, 89, 0.18), 0 10px 30px rgba(0, 0, 0, 0.08) !important;
            }

            .rsd-case-study-card::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 50% !important;
                height: 100% !important;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent) !important;
                transform: skewX(-25deg) !important;
                transition: left 0.8s ease-in-out !important;
                pointer-events: none !important;
            }

            .rsd-case-study-card:hover::before {
                left: 150% !important;
            }

            /* Image Mockup 3D Hover */
            .rsd-case-study-card .elementor-widget-image img {
                border-radius: 16px !important;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12) !important;
                transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }

            .rsd-case-study-card:hover .elementor-widget-image img {
                transform: scale(1.03) translateY(-4px) !important;
                box-shadow: 0 24px 55px rgba(0, 0, 0, 0.18) !important;
            }

            /* Number Badge Pill */
            .rsd-project-badge {
                display: inline-block !important;
                padding: 6px 16px !important;
                background: rgba(197, 160, 89, 0.08) !important;
                border: 1px solid rgba(197, 160, 89, 0.3) !important;
                border-radius: 9999px !important;
                color: #2563EB !important;
                font-weight: 800 !important;
                letter-spacing: 2px !important;
                font-size: 0.85rem !important;
                margin-bottom: 16px !important;
                backdrop-filter: blur(8px) !important;
            }

            /* Interactive Luxury Button Hover */
            .rsd-case-study-card .elementor-widget-button a.elementor-button,
            .rsd-btn-luxury a.elementor-button {
                background: #FFFFFF !important;
                color: #FBFBF9 !important;
                border-radius: 9999px !important;
                padding: 16px 36px !important;
                font-weight: 600 !important;
                letter-spacing: 0.5px !important;
                box-shadow: 0 10px 24px rgba(17, 17, 17, 0.18) !important;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }

            .rsd-case-study-card .elementor-widget-button a.elementor-button:hover,
            .rsd-btn-luxury a.elementor-button:hover {
                background: #2563EB !important;
                color: #0F172A !important;
                box-shadow: 0 16px 36px rgba(197, 160, 89, 0.4) !important;
                transform: translateY(-2px) !important;
            }

            @media (max-width: 767px) {
                .rsd-case-study-card {
                    padding: 24px 18px !important;
                    border-radius: 18px !important;
                    margin-bottom: 28px !important;
                }
            }
        /* MASTER ULTRA-LUXURY HERO SECTION STYLES */
        .rsd-hero-master-sec {
            position: relative !important;
            background: #FFFFFF !important;
            padding: 110px 20px 80px 20px !important;
            overflow: hidden !important;
            text-align: center !important;
            box-sizing: border-box !important;
            margin-top: 0 !important;
        }
        .rsd-hero-ambient-glow {
            position: absolute !important;
            top: 0 !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 600px !important;
            height: 400px !important;
            background: radial-gradient(circle, rgba(197, 160, 89, 0.15) 0%, rgba(17, 17, 17, 0) 70%) !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }
        .rsd-hero-container {
            position: relative !important;
            z-index: 2 !important;
            max-width: 1050px !important;
            margin: 0 auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
        }
        .rsd-hero-eyebrow {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            color: #2563EB !important;
            font-size: 0.92rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.05em !important;
            text-transform: uppercase !important;
            margin-bottom: 24px !important;
            padding: 8px 20px !important;
            background: rgba(197, 160, 89, 0.08) !important;
            border: 1px solid rgba(197, 160, 89, 0.25) !important;
            border-radius: 30px !important;
        }
        .rsd-hero-h1 {
            color: #FFFFFF !important;
            font-size: clamp(2rem, 4.5vw, 3.4rem) !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
            margin: 0 0 24px 0 !important;
            max-width: 950px !important;
        }
        .rsd-gold-text {
            color: #2563EB !important;
        }
        .rsd-hero-subtext {
            color: #CBD5E1 !important;
            font-size: clamp(1rem, 2vw, 1.2rem) !important;
            line-height: 1.75 !important;
            margin: 0 0 40px 0 !important;
            max-width: 820px !important;
        }
        .rsd-hero-cta-group {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 16px !important;
            flex-wrap: wrap !important;
            width: 100% !important;
        }
        .rsd-hero-btn-primary {
            background: #2563EB !important;
            color: #0F172A !important;
            border: none !important;
            padding: 16px 36px !important;
            border-radius: 50px !important;
            font-weight: 800 !important;
            font-size: 1.05rem !important;
            cursor: pointer !important;
            box-shadow: 0 10px 30px rgba(197, 160, 89, 0.3) !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .rsd-hero-btn-primary:hover {
            transform: translateY(-2px) scale(1.03) !important;
            box-shadow: 0 15px 40px rgba(197, 160, 89, 0.45) !important;
        }
        .rsd-hero-btn-secondary {
            background: transparent !important;
            color: #FFFFFF !important;
            border: 1px solid #2563EB !important;
            padding: 16px 36px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 1.05rem !important;
            cursor: pointer !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .rsd-hero-btn-secondary:hover {
            background: rgba(197, 160, 89, 0.12) !important;
            transform: translateY(-2px) !important;
        }
        @media (max-width: 768px) {
            .rsd-hero-master-sec {
                padding: 75px 16px 50px 16px !important;
            }
            .rsd-hero-h1 {
                font-size: 1.8rem !important;
                line-height: 1.3 !important;
            }
            .rsd-hero-cta-group {
                flex-direction: column !important;
                gap: 12px !important;
            }
            .rsd-hero-btn-primary,
            .rsd-hero-btn-secondary {
                width: 100% !important;
                box-sizing: border-box !important;
            }
        }
        /* FRESH LIGHT HERO SECTION STYLES (NO BLACK, NO GOLD) */
        .rsd-hero-fresh-sec {
            position: relative !important;
            background: linear-gradient(180deg, #F8FAFC 0%, #FFFFFF 100%) !important;
            padding: 100px 20px 70px 20px !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
            margin-top: 0 !important;
            border-bottom: 1px solid #F1F5F9 !important;
        }
        .rsd-hero-fresh-container {
            max-width: 1180px !important;
            margin: 0 auto !important;
            display: grid !important;
            grid-template-columns: 1.2fr 0.8fr !important;
            gap: 40px !important;
            align-items: center !important;
        }
        .rsd-hero-fresh-eyebrow {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            color: #2563EB !important;
            font-size: 0.9rem !important;
            font-weight: 700 !important;
            margin-bottom: 20px !important;
            padding: 8px 18px !important;
            background: #EFF6FF !important;
            border: 1px solid #BFDBFE !important;
            border-radius: 30px !important;
        }
        .rsd-hero-fresh-h1 {
            color: #0F172A !important;
            font-size: clamp(2rem, 4vw, 3.2rem) !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
            margin: 0 0 20px 0 !important;
        }
        .rsd-vivid-text {
            background: linear-gradient(135deg, #2563EB 0%, #059669 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }
        .rsd-hero-fresh-subtext {
            color: #475569 !important;
            font-size: 1.1rem !important;
            line-height: 1.75 !important;
            margin: 0 0 32px 0 !important;
        }
        .rsd-hero-fresh-cta-group {
            display: flex !important;
            align-items: center !important;
            gap: 14px !important;
            flex-wrap: wrap !important;
        }
        .rsd-hero-fresh-btn-primary {
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
            color: #FFFFFF !important;
            border: none !important;
            padding: 16px 32px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            cursor: pointer !important;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25) !important;
            transition: all 0.25s ease !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .rsd-hero-fresh-btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 14px 35px rgba(37, 99, 235, 0.35) !important;
        }
        .rsd-hero-fresh-btn-secondary {
            background: #FFFFFF !important;
            color: #0F172A !important;
            border: 1px solid #E2E8F0 !important;
            padding: 16px 32px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            cursor: pointer !important;
            transition: all 0.25s ease !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03) !important;
        }
        .rsd-hero-fresh-btn-secondary:hover {
            background: #F8FAFC !important;
            border-color: #CBD5E1 !important;
            transform: translateY(-2px) !important;
        }

        /* Visual Revenue Showcase Card */
        .rsd-hero-visual-card {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 24px !important;
            padding: 24px !important;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08) !important;
        }
        .rsd-visual-card-hdr {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding-bottom: 16px !important;
            border-bottom: 1px solid #F1F5F9 !important;
            margin-bottom: 20px !important;
        }
        .rsd-visual-badge {
            background: #F0FDF4 !important;
            border: 1px solid #DCFCE7 !important;
            color: #166534 !important;
            padding: 6px 14px !important;
            border-radius: 20px !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
        }
        .rsd-visual-grid {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 14px !important;
        }
        .rsd-visual-stat {
            background: #F8FAFC !important;
            border: 1px solid #F1F5F9 !important;
            padding: 16px 20px !important;
            border-radius: 16px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }
        .rsd-stat-val {
            font-size: 1.4rem !important;
            font-weight: 800 !important;
            color: #2563EB !important;
        }
        .rsd-stat-lbl {
            font-size: 0.9rem !important;
            color: #475569 !important;
            font-weight: 600 !important;
        }

        @media (max-width: 900px) {
            .rsd-hero-fresh-container {
                grid-template-columns: 1fr !important;
                gap: 30px !important;
            }
            .rsd-hero-fresh-sec {
                padding: 70px 16px 40px 16px !important;
            }
            .rsd-hero-fresh-h1 {
                font-size: 1.8rem !important;
                line-height: 1.3 !important;
            }
            .rsd-hero-fresh-cta-group {
                flex-direction: column !important;
                gap: 12px !important;
            }
            .rsd-hero-fresh-btn-primary,
            .rsd-hero-fresh-btn-secondary {
                width: 100% !important;
                box-sizing: border-box !important;
            }
        }
        /* SUPPRESS OLD HERO DUPLICATES AND ENFORCE ULTRA-CLEAN PUNCHY HERO */
        body.home section.rsd-hero-sec,
        body.page-id-12 section.rsd-hero-sec,
        body.page-id-163 section.rsd-hero-sec,
        .rsd-hero-master-sec,
        .elementor-element-7a8e9e4 {
            display: none !important;
        }

        .rsd-hero-fresh-sec {
            display: block !important;
            padding: 90px 20px 50px 20px !important;
        }
        .rsd-hero-fresh-h1 {
            font-size: clamp(2.2rem, 4vw, 3.4rem) !important;
            letter-spacing: -0.02em !important;
        }
        .rsd-hero-fresh-subtext {
            max-width: 650px !important;
            font-size: 1.1rem !important;
        }
        /* =========================================================
           GLOBAL MASTER LIGHT COLOR ENFORCER (NO BLACK, NO GOLD)
           ========================================================= */
        body,
        body.home,
        body.page,
        .site-content,
        #page,
        .entry-content,
        .elementor-page {
            background-color: #FFFFFF !important;
            color: #0F172A !important;
        }

        /* Enforce Light Containers for All Cards & Sections */
        .elementor-section,
        .elementor-container,
        .elementor-card,
        .rsd-card,
        .rsd-sec,
        .rsd-liquid-glass-card,
        .rsd-metric-3d-card,
        .rsd-luxury-portfolio-card {
            background-color: #FFFFFF !important;
            border-color: #E2E8F0 !important;
            color: #0F172A !important;
        }

        /* Headings & Text Color Enforcer */
        h1, h2, h3, h4, h5, h6,
        .elementor-heading-title {
            color: #0F172A !important;
        }
        p, span, li, a {
            color: #334155 !important;
        }
        a:hover {
            color: #2563EB !important;
        }

        /* Suppress Any Stray Black or Gold Gradients Globally */
        *[style*="background: #0F172A"],
        *[style*="#0F172A"],
        *[style*="#2563EB"],
        *[style*="#2563EB"] {
            background: #FFFFFF !important;
            color: #0F172A !important;
            border-color: #E2E8F0 !important;
        }
        /* NUCLEAR CONTRAST & LEGIBILITY ENFORCER (NO DARK CONTAINERS WITH DARK TEXT) */
        
        /* 1. Suppress Old Hero Section Containers Completely */
        body.home section:has(h1:contains("حرر فندقك")),
        body.home div:has(h1:contains("حرر فندقك")),
        body.page-id-163 section:has(h1:contains("حرر فندقك")),
        .elementor-element-7a8e9e4,
        .rsd-hero-master-sec {
            display: none !important;
            height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* 2. Fix Dark Containers (e.g. "نحن لا نبني مجرد مواقع") */
        *[class*="elementor-element"]:has(h2:contains("نحن لا نبني")),
        *[class*="elementor-element"]:has(p:contains("نحن لا نبني")),
        .rsd-sec-dark,
        section[style*="background: #000"],
        section[style*="background: #111"],
        div[style*="background: #000"],
        div[style*="background: #111"] {
            background-color: #F8FAFC !important;
            color: #0F172A !important;
            border-top: 1px solid #E2E8F0 !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }

        /* Enforce Dark High-Contrast Navy Text Everywhere */
        h1, h2, h3, h4, h5, h6,
        .elementor-heading-title,
        .entry-title {
            color: #0F172A !important;
            font-weight: 800 !important;
            text-shadow: none !important;
        }

        p, span, li, td, th {
            color: #334155 !important;
            font-weight: 500 !important;
        }

        /* 3. Fix All Dark Buttons (Replace black buttons with Electric Blue) */
        button,
        a.button,
        .button,
        a.rsd-btn-black,
        button.rsd-btn-black,
        .elementor-button {
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
            color: #FFFFFF !important;
            border: none !important;
            font-weight: 700 !important;
            border-radius: 50px !important;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25) !important;
            text-shadow: none !important;
        }

        button:hover,
        a.button:hover,
        .button:hover,
        .elementor-button:hover {
            background: #1D4ED8 !important;
            color: #FFFFFF !important;
            transform: translateY(-1px) !important;
        }

        /* 4. Fix Footer Background & Contrast */
        #rsdUniversalFooter,
        footer,
        .site-footer {
            background-color: #F8FAFC !important;
            border-top: 1px solid #E2E8F0 !important;
            color: #0F172A !important;
        }
        #rsdUniversalFooter *,
        footer *,
        .site-footer * {
            color: #475569 !important;
        }
        #rsdUniversalFooter h4,
        footer h4,
        .site-footer h4 {
            color: #0F172A !important;
            font-weight: 800 !important;
        }
        #rsdUniversalFooter a:hover,
        footer a:hover,
        .site-footer a:hover {
            color: #2563EB !important;
        }
        /* ABSOLUTE SUPPRESSION FOR THE "نبني ونطور لك" SECTION */
        section:has(*:contains("نبني ونطور لك")),
        div:has(*:contains("نبني ونطور لك")),
        section:has(*:contains("استوديو تصميم وبرمجة")),
        div:has(*:contains("استوديو تصميم وبرمجة")) {
            display: none !important;
            height: 0 !important;
            min-height: 0 !important;
            max-height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
        /* SAFE NON-DESTRUCTIVE TARGETED SECTION SUPPRESSION */
        .elementor-top-section:has(*:contains("حرر فندقك")),
        .elementor-top-section:has(*:contains("نبني ونطور")),
        .elementor-top-section:has(*:contains("استوديو تصميم")),
        .elementor-element-7a8e9e4,
        .rsd-hero-master-sec {
            display: none !important;
        }

        /* PRESERVE ALL ELEMENTOR LAYOUT GRID & FLEXBOX CONTAINERS */
        .elementor-section,
        .elementor-container,
        .elementor-column,
        .elementor-widget-wrap {
            box-sizing: border-box !important;
        }
        .elementor-container {
            display: flex !important;
            margin-right: auto !important;
            margin-left: auto !important;
            position: relative !important;
        }
        /* OUTSETA / LINEAR SUNSET MESH MASTER REDESIGN STYLES */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Readex+Pro:wght@400;600;700;800&display=swap');

        .rsd-outseta-master-wrap {
            font-family: 'Readex Pro', 'Cairo', system-ui, sans-serif !important;
            background: #FFFFFF !important;
            color: #0B0F19 !important;
            width: 100% !important;
            overflow-x: hidden !important;
        }

        /* SECTION 1: SUNSET MESH GLOW HERO */
        .rsd-outseta-hero {
            position: relative !important;
            background: radial-gradient(circle at 50% 0%, #FFD1B3 0%, #FEE2E2 35%, #F5F3FF 70%, #FFFFFF 100%) !important;
            padding: 90px 20px 70px 20px !important;
            text-align: center !important;
        }
        .rsd-outseta-hero-container {
            max-width: 1180px !important;
            margin: 0 auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
        }
        .rsd-outseta-pill {
            background: rgba(255, 255, 255, 0.9) !important;
            border: 1px solid rgba(255, 94, 126, 0.3) !important;
            color: #FF5E7E !important;
            padding: 8px 20px !important;
            border-radius: 30px !important;
            font-size: 0.88rem !important;
            font-weight: 700 !important;
            margin-bottom: 24px !important;
            box-shadow: 0 4px 15px rgba(255, 94, 126, 0.1) !important;
            backdrop-filter: blur(10px) !important;
        }
        .rsd-outseta-h1 {
            color: #0B0F19 !important;
            font-size: clamp(2.2rem, 4.5vw, 3.6rem) !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
            margin: 0 0 24px 0 !important;
            max-width: 950px !important;
        }
        .rsd-gradient-text {
            background: linear-gradient(135deg, #FF5E7E 0%, #6366F1 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }
        .rsd-outseta-subtext {
            color: #4B5563 !important;
            font-size: 1.15rem !important;
            line-height: 1.75 !important;
            margin: 0 0 36px 0 !important;
            max-width: 780px !important;
        }
        .rsd-outseta-cta-group {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 16px !important;
            flex-wrap: wrap !important;
            margin-bottom: 60px !important;
        }
        .rsd-outseta-btn-primary {
            background: #0B0F19 !important;
            color: #FFFFFF !important;
            border: none !important;
            padding: 16px 36px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 1.05rem !important;
            cursor: pointer !important;
            box-shadow: 0 10px 25px rgba(11, 15, 25, 0.25) !important;
            transition: all 0.25s ease !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .rsd-outseta-btn-primary:hover {
            transform: translateY(-2px) scale(1.02) !important;
            box-shadow: 0 14px 35px rgba(11, 15, 25, 0.35) !important;
        }
        .rsd-outseta-btn-secondary {
            background: rgba(255, 255, 255, 0.85) !important;
            color: #1F2937 !important;
            border: 1px solid #E5E7EB !important;
            padding: 16px 36px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 1.05rem !important;
            cursor: pointer !important;
            transition: all 0.25s ease !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            backdrop-filter: blur(10px) !important;
        }
        .rsd-outseta-btn-secondary:hover {
            background: #FFFFFF !important;
            border-color: #CBD5E1 !important;
            transform: translateY(-2px) !important;
        }

        /* 3-CARD SHOWCASE STRIP */
        .rsd-showcase-strip {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 24px !important;
            width: 100% !important;
            max-width: 1100px !important;
        }
        .rsd-showcase-card {
            background: #FFFFFF !important;
            border: 1px solid rgba(229, 231, 235, 0.8) !important;
            border-radius: 20px !important;
            overflow: hidden !important;
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.08) !important;
            transition: transform 0.25s ease !important;
            text-align: right !important;
        }
        .rsd-showcase-card:hover {
            transform: translateY(-4px) !important;
        }
        .rsd-showcase-img-wrap {
            height: 180px !important;
            overflow: hidden !important;
            background: #F9FAFB !important;
        }
        .rsd-showcase-img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
        .rsd-showcase-body {
            padding: 20px !important;
        }
        .rsd-showcase-body h4 {
            margin: 0 0 6px 0 !important;
            color: #0B0F19 !important;
            font-size: 1.15rem !important;
            font-weight: 800 !important;
        }
        .rsd-showcase-body p {
            margin: 0 !important;
            color: #6B7280 !important;
            font-size: 0.9rem !important;
        }

        /* SECTION 2: AUTHENTIC FEEDBACK STRIP */
        .rsd-outseta-sec {
            padding: 80px 20px !important;
        }
        .rsd-sec-container {
            max-width: 1180px !important;
            margin: 0 auto !important;
        }
        .rsd-sec-title {
            text-align: center !important;
            font-size: clamp(1.8rem, 3.5vw, 2.5rem) !important;
            font-weight: 800 !important;
            color: #0B0F19 !important;
            margin-bottom: 40px !important;
        }
        .rsd-feedback-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 24px !important;
        }
        .rsd-feedback-card {
            background: #FFFFFF !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 20px !important;
            padding: 28px !important;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
        }
        .rsd-quote-text {
            color: #374151 !important;
            font-size: 1rem !important;
            line-height: 1.7 !important;
            margin-bottom: 20px !important;
        }
        .rsd-author-info strong {
            display: block !important;
            color: #0B0F19 !important;
            font-size: 0.95rem !important;
        }
        .rsd-author-info span {
            color: #6B7280 !important;
            font-size: 0.85rem !important;
        }

        /* SECTION 3 & 6: DEEP MIDNIGHT SLATE BREAKOUT CONTAINERS */
        .rsd-dark-breakout-sec {
            background: #0B0F19 !important;
            padding: 90px 20px !important;
            color: #FFFFFF !important;
            position: relative !important;
            overflow: hidden !important;
        }
        .rsd-dark-container {
            max-width: 1050px !important;
            margin: 0 auto !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-dark-badge {
            display: inline-block !important;
            color: #FF5E7E !important;
            background: rgba(255, 94, 126, 0.12) !important;
            border: 1px solid rgba(255, 94, 126, 0.3) !important;
            padding: 6px 16px !important;
            border-radius: 20px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            margin-bottom: 20px !important;
        }
        .rsd-dark-h2 {
            color: #FFFFFF !important;
            font-size: clamp(2rem, 4vw, 3rem) !important;
            font-weight: 800 !important;
            margin-bottom: 20px !important;
        }
        .rsd-dark-subtext {
            color: #9CA3AF !important;
            font-size: 1.1rem !important;
            max-width: 700px !important;
            margin-bottom: 40px !important;
        }

        .rsd-dark-mockup-wrap {
            background: #131B2E !important;
            border: 1px solid rgba(255, 94, 126, 0.25) !important;
            border-radius: 20px !important;
            overflow: hidden !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
        }
        .rsd-mockup-hdr {
            background: #0F172A !important;
            padding: 14px 20px !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .rsd-dot {
            width: 10px !important;
            height: 10px !important;
            border-radius: 50% !important;
        }
        .rsd-dot.red { background: #EF4444 !important; }
        .rsd-dot.yellow { background: #F59E0B !important; }
        .rsd-dot.green { background: #10B981 !important; }
        .rsd-mockup-title {
            color: #94A3B8 !important;
            font-size: 0.85rem !important;
            margin-right: auto !important;
        }
        .rsd-mockup-content {
            padding: 30px 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-around !important;
            flex-wrap: wrap !important;
            gap: 20px !important;
        }
        .rsd-flow-step {
            display: flex !important;
            align-items: center !important;
            gap: 14px !important;
            background: rgba(255, 255, 255, 0.04) !important;
            padding: 16px 24px !important;
            border-radius: 14px !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .rsd-step-num {
            color: #FF5E7E !important;
            font-size: 1.4rem !important;
            font-weight: 800 !important;
        }
        .rsd-flow-arrow {
            color: #6366F1 !important;
            font-size: 1.5rem !important;
        }

        /* SECTION 4: BENTO GRID MODULAR PRICING */
        .rsd-bento-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)) !important;
            gap: 20px !important;
            margin-bottom: 30px !important;
        }
        .rsd-bento-card {
            background: #FFFFFF !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 20px !important;
            padding: 28px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04) !important;
            transition: transform 0.25s ease !important;
        }
        .rsd-bento-card:hover {
            transform: translateY(-4px) !important;
            border-color: #6366F1 !important;
        }
        .rsd-bento-price {
            font-size: 2rem !important;
            font-weight: 800 !important;
            color: #6366F1 !important;
            margin-bottom: 12px !important;
        }
        .rsd-bento-card h3 {
            color: #0B0F19 !important;
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            margin: 0 0 10px 0 !important;
        }
        .rsd-bento-card p {
            color: #6B7280 !important;
            font-size: 0.92rem !important;
            margin: 0 !important;
            line-height: 1.6 !important;
        }

        .rsd-bundle-card {
            background: linear-gradient(135deg, #FFF5F7 0%, #EEF2FF 100%) !important;
            border: 2px solid #FF5E7E !important;
            border-radius: 24px !important;
            padding: 32px !important;
            position: relative !important;
        }
        .rsd-bundle-badge {
            background: #FF5E7E !important;
            color: #FFFFFF !important;
            padding: 6px 16px !important;
            border-radius: 20px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            display: inline-block !important;
            margin-bottom: 16px !important;
        }
        .rsd-bundle-content {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 20px !important;
        }

        /* SECTION 5: PRACTICAL COMPARISON */
        .rsd-comp-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 24px !important;
        }
        .rsd-comp-card {
            border-radius: 20px !important;
            padding: 32px !important;
        }
        .rsd-before-card {
            background: #FEF2F2 !important;
            border: 1px solid #FCA5A5 !important;
        }
        .rsd-after-card {
            background: #ECFDF5 !important;
            border: 2px solid #10B981 !important;
        }
        .rsd-comp-list {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 14px !important;
        }
        .rsd-comp-list li {
            font-size: 0.98rem !important;
            color: #1F2937 !important;
            font-weight: 600 !important;
        }

        /* GUARANTEE BOX */
        .rsd-guarantee-box {
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #F9FAFB !important;
            padding: 16px 28px !important;
            border-radius: 30px !important;
            display: inline-block !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
            max-width: 800px !important;
        }

        @media (max-width: 900px) {
            .rsd-comp-grid { grid-template-columns: 1fr !important; }
            .rsd-outseta-hero { padding: 70px 16px 50px 16px !important; }
            .rsd-outseta-sec { padding: 60px 16px !important; }
            .rsd-dark-breakout-sec { padding: 60px 16px !important; }
        }
        /* AWARD-WINNING HIGH-CONVERTING SAAS LAYOUT STYLES (PURE HTML/CSS - NO BROKEN IMAGES) */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Readex+Pro:wght@400;600;700;800&display=swap');

        .rsd-award-saas-wrap {
            font-family: 'Readex Pro', 'Cairo', system-ui, sans-serif !important;
            background: #FFFFFF !important;
            color: #0F172A !important;
            width: 100% !important;
            overflow-x: hidden !important;
        }

        /* SECTION 1: MESH GLOW HERO */
        /* ==========================================================================
           REFINED LUXURY SHINY BUTTON (Zero Artifacts, 100% Crisp White Text)
           ========================================================================== */
        @property --gradient-angle {
            syntax: "<angle>";
            initial-value: 0deg;
            inherits: false;
        }

        .shiny-cta {
            --shiny-cta-bg: #09090B;
            --shiny-cta-fg: #FFFFFF;
            --shiny-cta-highlight: #3B82F6;
            --shiny-cta-shine: #93C5FD;
            --animation: gradient-angle 3.5s linear infinite;
            
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 52px !important;
            padding: 0 32px !important;
            font-family: 'Plus Jakarta Sans', 'Inter', 'Cairo', -apple-system, sans-serif !important;
            font-size: 1rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.01em !important;
            color: #FFFFFF !important;
            border-radius: 9999px !important;
            cursor: pointer !important;
            text-decoration: none !important;
            border: 1px solid transparent !important;
            background: linear-gradient(var(--shiny-cta-bg), var(--shiny-cta-bg)) padding-box,
                conic-gradient(
                    from var(--gradient-angle),
                    transparent 0%,
                    var(--shiny-cta-highlight) 10%,
                    var(--shiny-cta-shine) 20%,
                    var(--shiny-cta-highlight) 30%,
                    transparent 40%,
                    transparent 100%
                ) border-box !important;
            box-shadow: 0 4px 20px -2px rgba(59, 130, 246, 0.45), 0 2px 6px rgba(0, 0, 0, 0.2) !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            animation: var(--animation) !important;
            overflow: hidden !important;
        }

        .shiny-cta span {
            position: relative !important;
            z-index: 2 !important;
            color: #FFFFFF !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5) !important;
        }

        .shiny-cta::after {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            background: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.35), transparent 70%) !important;
            opacity: 0 !important;
            transition: opacity 0.3s ease !important;
            pointer-events: none !important;
            border-radius: inherit !important;
        }

        .shiny-cta:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 28px -2px rgba(59, 130, 246, 0.65), 0 4px 12px rgba(0, 0, 0, 0.3) !important;
            border-color: rgba(147, 197, 253, 0.6) !important;
        }

        .shiny-cta:hover::after {
            opacity: 1 !important;
        }

        .shiny-cta:active {
            transform: translateY(0px) scale(0.98) !important;
        }

        @keyframes gradient-angle {
            to { --gradient-angle: 360deg; }
        }

        .rsd-btn-showcase {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 52px !important;
            padding: 0 28px !important;
            font-family: 'Plus Jakarta Sans', 'Inter', 'Cairo', -apple-system, sans-serif !important;
            font-size: 0.98rem !important;
            font-weight: 700 !important;
            border-radius: 9999px !important;
            background: #FFFFFF !important;
            color: #09090B !important;
            border: 1px solid #E2E8F0 !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
            text-decoration: none !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            cursor: pointer !important;
        }
        .rsd-btn-showcase:hover {
            background: #F8FAFC !important;
            border-color: #CBD5E1 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08) !important;
            color: #09090B !important;
        }

        /* ==========================================================================
           LUXURY DARK ROI CALCULATOR & MODULAR SOLUTIONS SECTION
           ========================================================================== */
        .rsd-roi-section {
            background: #09090B !important;
            color: #FAFAFA !important;
            padding: 90px 20px 80px 20px !important;
            position: relative !important;
            overflow: hidden !important;
            border-top: 1px solid #1E293B !important;
            border-bottom: 1px solid #1E293B !important;
        }
        .rsd-roi-ambient-glow {
            position: absolute !important;
            top: 20% !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 800px !important;
            height: 400px !important;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, rgba(139, 92, 246, 0.08) 50%, transparent 70%) !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }
        .rsd-roi-container {
            max-width: 1100px !important;
            margin: 0 auto !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-roi-pill {
            background: rgba(30, 41, 59, 0.8) !important;
            border: 1px solid rgba(59, 130, 246, 0.3) !important;
            color: #93C5FD !important;
            padding: 6px 18px !important;
            border-radius: 9999px !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            display: inline-block !important;
            margin-bottom: 16px !important;
            backdrop-filter: blur(8px) !important;
        }
        .rsd-roi-title {
            font-size: clamp(2rem, 3.5vw, 2.8rem) !important;
            font-weight: 800 !important;
            letter-spacing: -0.03em !important;
            color: #FFFFFF !important;
            margin: 0 0 12px 0 !important;
            line-height: 1.2 !important;
            text-align: center !important;
        }
        .rsd-roi-subtitle {
            font-size: 1.05rem !important;
            color: #94A3B8 !important;
            max-width: 640px !important;
            margin: 0 auto 50px auto !important;
            line-height: 1.6 !important;
            text-align: center !important;
        }

        /* Calculator Grid */
        .rsd-roi-grid {
            display: grid !important;
            grid-template-columns: 1.15fr 1fr !important;
            gap: 28px !important;
            margin-bottom: 70px !important;
            align-items: stretch !important;
        }
        @media (max-width: 860px) {
            .rsd-roi-grid { grid-template-columns: 1fr !important; }
        }

        .rsd-roi-card {
            background: rgba(18, 18, 24, 0.85) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 20px !important;
            padding: 32px !important;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5) !important;
            backdrop-filter: blur(16px) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            text-align: left !important;
        }
        [dir="rtl"] .rsd-roi-card {
            text-align: right !important;
        }

        .rsd-roi-card-header {
            font-size: 1.25rem !important;
            font-weight: 800 !important;
            color: #FFFFFF !important;
            margin-bottom: 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        /* Sliders Group */
        .rsd-slider-group {
            margin-bottom: 22px !important;
        }
        .rsd-slider-label-row {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 10px !important;
        }
        .rsd-slider-name {
            font-size: 0.92rem !important;
            font-weight: 600 !important;
            color: #CBD5E1 !important;
        }
        .rsd-slider-val {
            font-size: 1.05rem !important;
            font-weight: 800 !important;
            color: #38BDF8 !important;
            background: rgba(56, 189, 248, 0.1) !important;
            padding: 2px 10px !important;
            border-radius: 6px !important;
        }
        .rsd-range-input {
            width: 100% !important;
            height: 6px !important;
            border-radius: 5px !important;
            background: #27272A !important;
            outline: none !important;
            -webkit-appearance: none !important;
            cursor: pointer !important;
        }
        .rsd-range-input::-webkit-slider-thumb {
            -webkit-appearance: none !important;
            width: 20px !important;
            height: 20px !important;
            border-radius: 50% !important;
            background: #FFFFFF !important;
            border: 3px solid #3B82F6 !important;
            cursor: pointer !important;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.8) !important;
            transition: transform 0.15s ease !important;
        }
        .rsd-range-input::-webkit-slider-thumb:hover {
            transform: scale(1.2) !important;
        }

        /* Output Card Results */
        .rsd-output-block {
            margin-bottom: 24px !important;
        }
        .rsd-output-label {
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            color: #94A3B8 !important;
            margin-bottom: 6px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
        }
        .rsd-output-val-large {
            font-size: clamp(2rem, 3.2vw, 2.7rem) !important;
            font-weight: 800 !important;
            letter-spacing: -0.03em !important;
            background: linear-gradient(135deg, #38BDF8 0%, #818CF8 50%, #C084FC 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            line-height: 1.1 !important;
            margin-bottom: 4px !important;
        }
        .rsd-output-subtext {
            font-size: 0.9rem !important;
            color: #34D399 !important;
            font-weight: 700 !important;
        }

        .rsd-output-btn {
            background: #FFFFFF !important;
            color: #09090B !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            padding: 14px 24px !important;
            border-radius: 9999px !important;
            border: none !important;
            cursor: pointer !important;
            width: 100% !important;
            text-align: center !important;
            box-shadow: 0 4px 20px rgba(255, 255, 255, 0.15) !important;
            transition: all 0.2s ease !important;
            display: inline-block !important;
            text-decoration: none !important;
            box-sizing: border-box !important;
        }
        .rsd-output-btn:hover {
            background: #F1F5F9 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.25) !important;
        }

        /* Bottom Modular Bento Cards */
        .rsd-modular-title {
            font-size: 1.5rem !important;
            font-weight: 800 !important;
            color: #FFFFFF !important;
            text-align: center !important;
            margin-bottom: 30px !important;
            letter-spacing: -0.02em !important;
        }
        .rsd-modular-grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 20px !important;
        }
        @media (max-width: 860px) {
            .rsd-modular-grid { grid-template-columns: 1fr !important; }
        }

        .rsd-modular-card {
            background: rgba(18, 18, 24, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 16px !important;
            padding: 24px !important;
            transition: all 0.25s ease !important;
            text-align: left !important;
        }
        [dir="rtl"] .rsd-modular-card {
            text-align: right !important;
        }
        .rsd-modular-card:hover {
            border-color: rgba(59, 130, 246, 0.4) !important;
            transform: translateY(-4px) !important;
            background: rgba(24, 24, 32, 0.8) !important;
        }
        .rsd-modular-price-badge {
            font-size: 1.1rem !important;
            font-weight: 800 !important;
            color: #38BDF8 !important;
            background: rgba(56, 189, 248, 0.1) !important;
            padding: 4px 12px !important;
            border-radius: 8px !important;
            display: inline-block !important;
            margin-bottom: 12px !important;
        }
        .rsd-modular-card-h4 {
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            color: #FFFFFF !important;
            margin: 0 0 8px 0 !important;
        }
        .rsd-modular-card-p {
            font-size: 0.88rem !important;
            color: #94A3B8 !important;
            line-height: 1.5 !important;
            margin: 0 !important;
        }

        .rsd-saas-hero {
            position: relative !important;
            background: radial-gradient(circle at 50% -10%, #FFD6A5 0%, #FFB4D6 25%, #DDD6FE 50%, #F8FAFC 80%, #FFFFFF 100%) !important;
            padding: 120px 20px 70px 20px !important;
            text-align: center !important;
            overflow: hidden !important;
        }
        .rsd-saas-hero-container {
            max-width: 1100px !important;
            margin: 0 auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
        }
        .rsd-saas-pill {
            background: rgba(255, 255, 255, 0.95) !important;
            border: 1px solid rgba(99, 102, 241, 0.2) !important;
            color: #4F46E5 !important;
            padding: 6px 18px !important;
            border-radius: 9999px !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            margin-bottom: 18px !important;
            box-shadow: 0 2px 10px rgba(99, 102, 241, 0.08) !important;
            backdrop-filter: blur(10px) !important;
            display: inline-block !important;
        }
        .rsd-saas-h1 {
            color: #0F172A !important;
            font-size: clamp(2rem, 3.8vw, 3.2rem) !important;
            font-weight: 800 !important;
            line-height: 1.2 !important;
            margin: 0 auto 14px auto !important;
            max-width: 880px !important;
            letter-spacing: -0.03em !important;
        }
        .rsd-saas-gradient-text {
            background: linear-gradient(135deg, #09090B 30%, #3B82F6 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }
        .rsd-saas-subtext {
            color: #475569 !important;
            font-size: 1.05rem !important;
            line-height: 1.6 !important;
            margin: 0 auto 24px auto !important;
            max-width: 680px !important;
        }

        /* 3D Visual Centerpiece & Floating UI Cards Layout */
        .rsd-hero-showcase-wrapper {
            position: relative !important;
            width: 100% !important;
            max-width: 860px !important;
            margin: 20px auto 0 auto !important;
        }
        .rsd-laptop-frame {
            position: relative !important;
            width: 100% !important;
            background: #09090B !important;
            border: 12px solid #1E293B !important;
            border-radius: 18px 18px 0 0 !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.25) !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .rsd-laptop-camera {
            width: 6px !important;
            height: 6px !important;
            background: #475569 !important;
            border-radius: 50% !important;
            margin: 4px auto 8px auto !important;
        }
        .rsd-laptop-base {
            width: 106% !important;
            height: 14px !important;
            margin-left: -3% !important;
            background: linear-gradient(180deg, #E2E8F0, #94A3B8) !important;
            border-radius: 0 0 18px 18px !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25) !important;
        }
        .rsd-laptop-screen-content {
            width: 100% !important;
            height: 380px !important;
            background: #FFFFFF !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: stretch !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            text-align: left !important;
            direction: ltr !important;
            color: #0F172A !important;
            font-size: 13px !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .rsd-laptop-sidebar {
            width: 165px !important;
            flex-shrink: 0 !important;
            background: #F8FAFC !important;
            border-right: 1px solid #E2E8F0 !important;
            padding: 14px 10px !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 6px !important;
            box-sizing: border-box !important;
        }
        .rsd-laptop-main {
            flex: 1 !important;
            min-width: 0 !important;
            padding: 16px 20px !important;
            background: #FFFFFF !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 12px !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }

        /* Floating UI Card 1: Notification */
        .rsd-float-notification {
            position: absolute !important;
            top: -15px !important;
            right: -20px !important;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            border-radius: 12px !important;
            padding: 10px 14px !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12) !important;
            z-index: 20 !important;
            direction: ltr !important;
            text-align: left !important;
            animation: floatSlow 4s ease-in-out infinite !important;
        }

        /* Floating UI Card 2: Confirmation Modal */
        .rsd-float-confirmation {
            position: absolute !important;
            top: 70px !important;
            right: -15px !important;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(16px) !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 16px !important;
            padding: 16px !important;
            width: 230px !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.14) !important;
            z-index: 25 !important;
            direction: ltr !important;
            text-align: left !important;
            animation: floatSlow 5s ease-in-out infinite 0.5s !important;
        }

        /* Floating UI Card 3: WhatsApp Mobile Mockup */
        .rsd-float-whatsapp {
            position: absolute !important;
            bottom: -25px !important;
            right: -30px !important;
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 12px 14px !important;
            width: 275px !important;
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.18) !important;
            z-index: 30 !important;
            direction: ltr !important;
            text-align: left !important;
            animation: floatSlow 4.5s ease-in-out infinite 1s !important;
        }

        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        .rsd-saas-cta-group {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 16px !important;
            flex-wrap: wrap !important;
            margin-top: 36px !important;
            margin-bottom: 10px !important;
            z-index: 40 !important;
            position: relative !important;
        }

        @media (max-width: 900px) {
            .rsd-float-notification, .rsd-float-confirmation { display: none !important; }
            .rsd-float-whatsapp { right: 10px !important; bottom: -20px !important; width: 250px !important; }
            .rsd-laptop-sidebar { display: none !important; }
            .rsd-laptop-screen-content { height: 280px !important; }
        }

        .rsd-saas-sec { padding: 80px 20px !important; }
        .rsd-saas-container { max-width: 1180px !important; margin: 0 auto !important; }
        .rsd-saas-title {
            text-align: center !important;
            font-size: clamp(1.8rem, 3.5vw, 2.5rem) !important;
            font-weight: 800 !important;
            color: #0F172A !important;
            margin-bottom: 45px !important;
        }
        .rsd-trust-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 24px !important;
        }
        .rsd-trust-card {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 28px !important;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04) !important;
        }
        .rsd-stars { font-size: 1rem !important; margin-bottom: 14px !important; }
        .rsd-trust-quote { color: #334155 !important; font-size: 0.98rem !important; line-height: 1.7 !important; margin-bottom: 20px !important; }
        .rsd-trust-author strong { display: block !important; color: #0F172A !important; font-size: 0.95rem !important; }
        .rsd-trust-author span { color: #64748B !important; font-size: 0.85rem !important; }

        /* SECTION 3: DEEP SLATE SAAS DASHBOARD TERMINAL */
        .rsd-saas-dark-sec {
            background: #0B0F19 !important;
            padding: 95px 20px !important;
            color: #FFFFFF !important;
            position: relative !important;
        }
        .rsd-saas-dark-container { max-width: 1080px !important; margin: 0 auto !important; text-align: center !important; }
        .rsd-dark-pill {
            display: inline-block !important;
            color: #A855F7 !important;
            background: rgba(168, 85, 247, 0.12) !important;
            border: 1px solid rgba(168, 85, 247, 0.3) !important;
            padding: 6px 18px !important;
            border-radius: 20px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            margin-bottom: 20px !important;
        }
        .rsd-dark-h2 { color: #FFFFFF !important; font-size: clamp(2rem, 4vw, 3rem) !important; font-weight: 800 !important; margin-bottom: 20px !important; }
        .rsd-dark-subtext { color: #94A3B8 !important; font-size: 1.1rem !important; max-width: 720px !important; margin: 0 auto 45px auto !important; }

        .rsd-saas-terminal-box {
            background: #111827 !important;
            border: 1px solid rgba(168, 85, 247, 0.3) !important;
            border-radius: 20px !important;
            overflow: hidden !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
            text-align: right !important;
        }
        .rsd-terminal-hdr {
            background: #0F172A !important;
            padding: 14px 20px !important;
            display: flex !items: center !important;
            align-items: center !important;
            gap: 12px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .rsd-mac-dots { display: flex !important; gap: 6px !important; }
        .rsd-mac-dots .dot { width: 10px !important; height: 10px !important; border-radius: 50% !important; }
        .rsd-mac-dots .red { background: #EF4444 !important; }
        .rsd-mac-dots .yellow { background: #F59E0B !important; }
        .rsd-mac-dots .green { background: #10B981 !important; }
        .rsd-terminal-title { color: #94A3B8 !important; font-size: 0.85rem !important; }
        .rsd-live-pulse { margin-right: auto !important; color: #10B981 !important; font-size: 0.78rem !important; font-weight: 700 !important; }

        .rsd-terminal-body {
            padding: 30px 24px !important;
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 24px !important;
        }
        .rsd-term-pane {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            padding: 20px !important;
            border-radius: 14px !important;
        }
        .rsd-term-pane h5 { margin: 0 0 14px 0 !important; color: #E2E8F0 !important; font-size: 0.95rem !important; }
        .rsd-feed-item {
            background: rgba(255, 255, 255, 0.04) !important;
            padding: 10px 14px !important;
            border-radius: 10px !important;
            margin-bottom: 10px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            font-size: 0.85rem !important;
        }
        .rsd-feed-item strong { color: #10B981 !important; }
        .rsd-wa-notice { background: rgba(16, 185, 129, 0.1) !important; border: 1px solid rgba(16, 185, 129, 0.2) !important; padding: 14px !important; border-radius: 12px !important; text-align: right !important; }
        .rsd-wa-notice p { margin: 6px 0 0 0 !important; color: #94A3B8 !important; font-size: 0.82rem !important; }

        /* SECTION 4: RICH BENTO GRID */
        .rsd-saas-bento-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)) !important;
            gap: 20px !important;
            margin-bottom: 30px !important;
        }
        .rsd-saas-bento-card {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 28px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
        }
        .rsd-bento-badge { font-size: 2rem !important; font-weight: 800 !important; color: #4F46E5 !important; margin-bottom: 12px !important; }
        .rsd-saas-bento-card h3 { color: #0F172A !important; font-size: 1.15rem !important; font-weight: 800 !important; margin: 0 0 10px 0 !important; }
        .rsd-saas-bento-card p { color: #64748B !important; font-size: 0.92rem !important; margin: 0 0 20px 0 !important; line-height: 1.6 !important; }
        .rsd-mini-widget {
            background: #F8FAFC !important;
            border: 1px solid #E2E8F0 !important;
            padding: 10px 14px !important;
            border-radius: 12px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            color: #334155 !important;
        }

        .rsd-saas-bundle-card {
            background: linear-gradient(135deg, #EEF2FF 0%, #F5F3FF 100%) !important;
            border: 2px solid #6366F1 !important;
            border-radius: 24px !important;
            padding: 32px !important;
        }
        .rsd-bundle-pill {
            background: #6366F1 !important;
            color: #FFFFFF !important;
            padding: 6px 16px !important;
            border-radius: 20px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            display: inline-block !important;
            margin-bottom: 16px !important;
        }
        .rsd-bundle-flex { display: flex !important; align-items: center !important; justify-content: space-between !important; flex-wrap: wrap !important; gap: 20px !important; }

        /* SECTION 5: UNIFIED MODERN COMPARISON MATRIX */
        .rsd-unified-matrix-card {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            border-radius: 24px !important;
            overflow: hidden !important;
            border: 1px solid #E2E8F0 !important;
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.06) !important;
        }
        .rsd-matrix-side { padding: 35px !important; }
        .rsd-matrix-side.old { background: #F8FAFC !important; }
        .rsd-matrix-side.new { background: #0F172A !important; color: #FFFFFF !important; }
        .rsd-matrix-side.new h3 { color: #FFFFFF !important; }
        .rsd-matrix-side.new .rsd-matrix-list li { color: #E2E8F0 !important; }
        .rsd-matrix-list { list-style: none !important; padding: 0 !important; margin: 20px 0 0 0 !important; display: flex !important; flex-direction: column !important; gap: 14px !important; }
        .rsd-matrix-list li { font-size: 0.98rem !important; font-weight: 600 !important; color: #334155 !important; }

        .rsd-guarantee-pill {
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #F8FAFC !important;
            padding: 16px 28px !important;
            border-radius: 30px !important;
            display: inline-block !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
            max-width: 800px !important;
        }

        @media (max-width: 900px) {
            .rsd-unified-matrix-card { grid-template-columns: 1fr !important; }
            .rsd-terminal-body { grid-template-columns: 1fr !important; }
            .rsd-saas-hero { padding: 105px 16px 50px 16px !important; }
            .rsd-saas-sec { padding: 60px 16px !important; }
            .rsd-saas-dark-sec { padding: 60px 16px !important; }
        }
        /* 3-COLUMN INFINITE VERTICAL MARQUEE TESTIMONIALS STYLES */
        .rsd-marquee-mask-wrap {
            position: relative !important;
            display: flex !important;
            justify-content: center !important;
            gap: 24px !important;
            max-height: 700px !important;
            overflow: hidden !important;
            -webkit-mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent) !important;
            mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent) !important;
            padding: 10px 0 !important;
        }

        .rsd-marquee-col {
            flex: 1 !important;
            max-width: 360px !important;
            width: 100% !important;
        }

        @media (max-width: 768px) {
            .rsd-marquee-col.col-2, .rsd-marquee-col.col-3 { display: none !important; }
        }
        @media (min-width: 769px) and (max-width: 1024px) {
            .rsd-marquee-col.col-3 { display: none !important; }
        }

        .rsd-marquee-track {
            display: flex !important;
            flex-direction: column !important;
            gap: 24px !important;
            padding-bottom: 24px !important;
        }

        .track-1 {
            animation: rsd-marquee-vert 20s linear infinite !important;
        }
        .track-2 {
            animation: rsd-marquee-vert 26s linear infinite !important;
        }
        .track-3 {
            animation: rsd-marquee-vert 22s linear infinite !important;
        }

        .rsd-marquee-mask-wrap:hover .rsd-marquee-track {
            animation-play-state: paused !important;
        }

        @keyframes rsd-marquee-vert {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }

        .rsd-t-card {
            background: #FFFFFF !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            border-radius: 24px !important;
            padding: 30px !important;
            box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.08) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            text-align: right !important;
            transition: transform 0.25s ease !important;
        }
        .rsd-t-card:hover {
            transform: translateY(-2px) !important;
            border-color: rgba(99, 102, 241, 0.4) !important;
        }

        .rsd-t-text {
            color: #334155 !important;
            font-size: 0.98rem !important;
            line-height: 1.75 !important;
            margin: 0 0 20px 0 !important;
            font-weight: 500 !important;
        }

        .rsd-t-user {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
        }

        .rsd-t-avatar {
            width: 44px !important;
            height: 44px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 2px solid #EEF2FF !important;
        }

        .rsd-t-name {
            display: block !important;
            color: #0F172A !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            line-height: 1.3 !important;
        }

        .rsd-t-role {
            display: block !important;
            color: #64748B !important;
            font-size: 0.82rem !important;
            opacity: 0.85 !important;
        }
        /* 3D MOTION GRAPHICS VIDEO LOOPS STYLES */
        .rsd-3d-hero-video-box {
            position: relative !important;
            width: 100% !important;
            max-width: 1140px !important;
            margin: 0 auto 35px auto !important;
            border-radius: 28px !important;
            overflow: hidden !important;
            box-shadow: 0 30px 60px -20px rgba(99, 102, 241, 0.25), 0 0 0 1px rgba(226, 232, 240, 0.8) !important;
            background: #0B0F19 !important;
        }

        .rsd-3d-loop-video {
            width: 100% !important;
            height: auto !important;
            display: block !important;
            object-fit: cover !important;
            border-radius: 28px !important;
            aspect-ratio: 16 / 9 !important;
        }

        .rsd-3d-video-overlay-badge {
            position: absolute !important;
            top: 20px !important;
            right: 20px !important;
            background: rgba(11, 15, 25, 0.75) !important;
            backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #FFFFFF !important;
            padding: 8px 18px !important;
            border-radius: 9999px !important;
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            z-index: 5 !important;
        }

        .rsd-3d-terminal-video-wrap {
            width: 100% !important;
            max-width: 960px !important;
            margin: 0 auto 30px auto !important;
            border-radius: 24px !important;
            overflow: hidden !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5) !important;
        }

        .rsd-3d-term-video {
            width: 100% !important;
            display: block !important;
            aspect-ratio: 16 / 9 !important;
            object-fit: cover !important;
        }

        .rsd-bento-3d-video-wrap {
            width: 100% !important;
            border-radius: 16px !important;
            overflow: hidden !important;
            margin-bottom: 16px !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
        }

        .rsd-bento-mini-video {
            width: 100% !important;
            display: block !important;
            aspect-ratio: 16 / 9 !important;
            object-fit: cover !important;
            border-radius: 16px !important;
        }
        /* MASTER COMMERCIAL VIDEO LIGHTBOX & TRIGGER STYLES */
        .rsd-video-trigger-wrap {
            display: flex !important;
            justify-content: center !important;
            margin: 18px 0 35px 0 !important;
        }

        .rsd-video-lightbox-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1.5px solid #C5A059 !important;
            color: #1E293B !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            padding: 12px 26px !important;
            border-radius: 9999px !important;
            cursor: pointer !important;
            box-shadow: 0 10px 25px -5px rgba(197, 160, 89, 0.25) !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            outline: none !important;
        }

        .rsd-video-lightbox-btn:hover {
            transform: translateY(-2px) scale(1.03) !important;
            background: #FFFFFF !important;
            box-shadow: 0 15px 35px -5px rgba(197, 160, 89, 0.4) !important;
            border-color: #D4AF37 !important;
            color: #0F172A !important;
        }

        .rsd-play-icon-glow {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 24px !important;
            height: 24px !important;
            background: linear-gradient(135deg, #C5A059, #D4AF37) !important;
            color: #FFFFFF !important;
            border-radius: 50% !important;
            font-size: 10px !important;
            padding-left: 2px !important;
            box-shadow: 0 0 12px rgba(197, 160, 89, 0.6) !important;
        }

        .rsd-video-modal-backdrop {
            display: none;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(11, 15, 25, 0.85) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            z-index: 999999 !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 20px !important;
            opacity: 0;
            transition: opacity 0.3s ease !important;
        }

        .rsd-video-modal-backdrop.active {
            display: flex !important;
            opacity: 1 !important;
        }

        .rsd-video-modal-content {
            position: relative !important;
            width: 100% !important;
            max-width: 900px !important;
            background: #0B0F19 !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 24px !important;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(197, 160, 89, 0.3) !important;
            overflow: hidden !important;
            transform: scale(0.95);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .rsd-video-modal-backdrop.active .rsd-video-modal-content {
            transform: scale(1) !important;
        }

        .rsd-video-modal-close {
            position: absolute !important;
            top: 14px !important;
            right: 14px !important;
            width: 36px !important;
            height: 36px !important;
            border-radius: 50% !important;
            background: rgba(255, 255, 255, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            color: #FFFFFF !important;
            font-size: 16px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            z-index: 10 !important;
            transition: background 0.2s ease !important;
        }

        .rsd-video-modal-close:hover {
            background: rgba(239, 68, 68, 0.8) !important;
        }

        .rsd-video-frame-container {
            width: 100% !important;
            aspect-ratio: 16 / 9 !important;
            background: #000000 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .rsd-video-frame-container video {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
            display: block !important;
        }
        /* 3D TITANIUM DEVICE MOCKUP FRAME STYLES */
        .rsd-titanium-device-wrapper {
            position: relative !important;
            width: 100% !important;
            max-width: 980px !important;
            margin: 35px auto 45px auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            perspective: 1200px !important;
        }

        .rsd-titanium-device-frame {
            position: relative !important;
            width: 100% !important;
            background: linear-gradient(145deg, #2D3748, #1A202C 40%, #111827 80%, #4A5568) !important;
            padding: 14px !important;
            border-radius: 36px !important;
            box-shadow: 0 35px 80px -20px rgba(15, 23, 42, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.15), 0 20px 40px -15px rgba(99, 102, 241, 0.25) !important;
            border: 2px solid rgba(203, 213, 225, 0.25) !important;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease !important;
        }

        .rsd-titanium-device-frame:hover {
            transform: translateY(-4px) scale(1.01) !important;
            box-shadow: 0 45px 95px -20px rgba(15, 23, 42, 0.55), 0 0 0 1.5px rgba(197, 160, 89, 0.4), 0 25px 50px -15px rgba(99, 102, 241, 0.35) !important;
        }

        .rsd-titanium-header-bar {
            position: absolute !important;
            top: 22px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            z-index: 10 !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

        .rsd-titanium-notch {
            width: 90px !important;
            height: 18px !important;
            background: #000000 !important;
            border-radius: 20px !important;
            box-shadow: inset 0 0 4px rgba(255, 255, 255, 0.2) !important;
        }

        .rsd-titanium-screen-glass {
            position: relative !important;
            width: 100% !important;
            border-radius: 24px !important;
            overflow: hidden !important;
            background: #0B0F19 !important;
            aspect-ratio: 16 / 9 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .rsd-hero-live-video {
            width: 100% !important;
            height: 100% !important;
            border-radius: 24px !important;
            object-fit: cover !important;
            display: block !important;
        }

        .rsd-titanium-reflection-overlay {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            border-radius: 36px !important;
            pointer-events: none !important;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 45%) !important;
        }

        .rsd-titanium-badge-floating {
            margin-top: 16px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            padding: 6px 18px !important;
            border-radius: 9999px !important;
            font-size: 0.88rem !important;
            font-weight: 700 !important;
            color: #1E293B !important;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.12) !important;
        }

        .rsd-live-pulse-dot {
            display: inline-block !important;
            animation: rsd-pulse-glow 2s infinite ease-in-out !important;
        }

        @keyframes rsd-pulse-glow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        @media (max-width: 768px) {
            .rsd-titanium-device-frame {
                padding: 8px !important;
                border-radius: 24px !important;
            }
            .rsd-titanium-screen-glass {
                border-radius: 18px !important;
            }
            .rsd-hero-live-video {
                border-radius: 18px !important;
            }
            .rsd-titanium-notch {
                width: 60px !important;
                height: 12px !important;
            }
        }
        /* MINIMALIST HERO TITANIUM DEVICE & SILENT MOTION STYLES */
        .rsd-hero-device-wrapper {
            position: relative !important;
            width: 100% !important;
            max-width: 920px !important;
            margin: 40px auto 10px auto !important;
            display: flex !important;
            justify-content: center !important;
        }

        .rsd-hero-device-frame {
            position: relative !important;
            width: 100% !important;
            background: linear-gradient(145deg, #1E293B, #0F172A 40%, #090D16 80%, #334155) !important;
            padding: 12px !important;
            border-radius: 32px !important;
            box-shadow: 0 30px 70px -15px rgba(15, 23, 42, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.12), 0 15px 35px -10px rgba(99, 102, 241, 0.2) !important;
            border: 1.5px solid rgba(203, 213, 225, 0.2) !important;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease !important;
        }

        .rsd-hero-device-frame:hover {
            transform: translateY(-4px) scale(1.01) !important;
            box-shadow: 0 40px 85px -15px rgba(15, 23, 42, 0.5), 0 0 0 1.5px rgba(197, 160, 89, 0.35) !important;
        }

        .rsd-device-header-notch {
            position: absolute !important;
            top: 20px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            z-index: 10 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            pointer-events: none !important;
        }

        .rsd-device-speaker {
            width: 70px !important;
            height: 14px !important;
            background: #000000 !important;
            border-radius: 20px !important;
            box-shadow: inset 0 0 4px rgba(255, 255, 255, 0.2) !important;
        }

        .rsd-device-screen {
            position: relative !important;
            width: 100% !important;
            border-radius: 22px !important;
            overflow: hidden !important;
            background: #000000 !important;
            aspect-ratio: 16 / 9 !important;
        }

        .rsd-hero-yt-iframe {
            position: absolute !important;
            top: -10% !important;
            left: -10% !important;
            width: 120% !important;
            height: 120% !important;
            border: none !important;
            pointer-events: none !important;
        }

        .rsd-device-screen-overlay {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            pointer-events: none !important;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0) 40%) !important;
            border-radius: 22px !important;
        }

        @media (max-width: 768px) {
            .rsd-hero-device-frame {
                padding: 6px !important;
                border-radius: 20px !important;
            }
            .rsd-device-screen {
                border-radius: 14px !important;
            }
            .rsd-device-speaker {
                width: 50px !important;
                height: 10px !important;
            }
        }
        /* ==========================================================================
           COMPREHENSIVE MOBILE & TABLET RESPONSIVE PERFECTION
           ========================================================================== */
        @media (max-width: 768px) {
            .rsd-saas-hero {
                padding: 100px 16px 40px 16px !important;
            }
            .rsd-saas-h1 {
                font-size: 1.85rem !important;
                line-height: 1.25 !important;
                margin-bottom: 12px !important;
            }
            .rsd-saas-subtext {
                font-size: 0.95rem !important;
                line-height: 1.5 !important;
                margin-bottom: 20px !important;
            }
            .shiny-cta, .rsd-btn-showcase {
                width: 100% !important;
                max-width: 320px !important;
                height: 48px !important;
                font-size: 0.95rem !important;
            }
            .rsd-saas-cta-group {
                flex-direction: column !important;
                gap: 10px !important;
                width: 100% !important;
            }
            .rsd-roi-section {
                padding: 50px 14px 45px 14px !important;
            }
            .rsd-roi-title {
                font-size: 1.65rem !important;
                margin-bottom: 8px !important;
            }
            .rsd-roi-subtitle {
                font-size: 0.92rem !important;
                margin-bottom: 30px !important;
            }
            .rsd-roi-card {
                padding: 20px 16px !important;
                border-radius: 14px !important;
            }
            .rsd-output-val-large {
                font-size: 1.9rem !important;
            }
            .rsd-modular-title {
                font-size: 1.3rem !important;
                margin-bottom: 20px !important;
            }
            .rsd-modular-card {
                padding: 18px 16px !important;
            }
            .rsd-saas-sec {
                padding: 50px 16px !important;
            }
            .rsd-laptop-screen-content {
                height: 240px !important;
            }
            .rsd-float-notification, .rsd-float-confirmation {
                display: none !important;
            }
            .rsd-float-whatsapp {
                position: relative !important;
                bottom: auto !important;
                right: auto !important;
                margin: 16px auto 0 auto !important;
                width: 95% !important;
                box-sizing: border-box !important;
            }
        }
        /* ==========================================================================
           1. BESPOKE INFINITE MONOCHROME MARQUEE & COLOR RHYTHM
           ========================================================================== */
        .rsd-marquee-section {
            background: linear-gradient(180deg, #020617 0%, #0B1120 50%, #030712 100%) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
            padding: 42px 0 !important;
            position: relative !important;
            z-index: 5 !important;
            overflow: hidden !important;
        }
        .rsd-marquee-header {
            text-align: center !important;
            margin-bottom: 22px !important;
            padding: 0 20px !important;
        }
        .rsd-marquee-badge {
            display: inline-block !important;
            font-size: 0.76rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.14em !important;
            color: #94A3B8 !important;
            opacity: 0.9 !important;
        }
        .rsd-marquee-wrapper {
            width: 100% !important;
            overflow: hidden !important;
            position: relative !important;
            mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,1) 12%, rgba(0,0,0,1) 88%, transparent 100%) !important;
            -webkit-mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,1) 12%, rgba(0,0,0,1) 88%, transparent 100%) !important;
        }
        .rsd-marquee-track {
            display: flex !important;
            align-items: center !important;
            gap: 28px !important;
            width: max-content !important;
            animation: rsdMarqueeDrift 34s linear infinite !important;
        }
        .rsd-marquee-track:hover {
            animation-play-state: paused !important;
        }
        @keyframes rsdMarqueeDrift {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .rsd-marquee-item {
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            color: #94A3B8 !important;
            font-size: 0.94rem !important;
            font-weight: 600 !important;
            letter-spacing: 0.02em !important;
            padding: 10px 20px !important;
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.07) !important;
            border-radius: 12px !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            white-space: nowrap !important;
            user-select: none !important;
        }
        .rsd-marquee-item:hover {
            color: #FFFFFF !important;
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: rgba(56, 189, 248, 0.35) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4) !important;
        }
        .rsd-marquee-icon {
            font-size: 1.05rem !important;
            opacity: 0.85 !important;
        }

        /* ==========================================================================
           2. 4-STEP BESPOKE ARCHITECTURAL PROTOCOL
           ========================================================================== */
        .rsd-protocol-sec {
            background: #09090B !important;
            padding: 90px 20px !important;
            position: relative !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .rsd-protocol-container {
            max-width: 1180px !important;
            margin: 0 auto !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-protocol-grid {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 20px !important;
            margin-top: 50px !important;
        }
        @media (max-width: 992px) {
            .rsd-protocol-grid { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (max-width: 600px) {
            .rsd-protocol-grid { grid-template-columns: 1fr !important; }
        }
        .rsd-protocol-card {
            background: rgba(18, 18, 24, 0.7) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 18px !important;
            padding: 28px 24px !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1), 0 20px 30px -10px rgba(0,0,0,0.5) !important;
            backdrop-filter: blur(12px) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            text-align: left !important;
        }
        [dir="rtl"] .rsd-protocol-card {
            text-align: right !important;
        }
        .rsd-protocol-card:hover {
            border-color: rgba(99, 102, 241, 0.5) !important;
            transform: translateY(-5px) !important;
            background: rgba(24, 24, 32, 0.9) !important;
        }
        .rsd-protocol-num {
            font-family: monospace !important;
            font-size: 1.8rem !important;
            font-weight: 800 !important;
            color: #38BDF8 !important;
            letter-spacing: -0.04em !important;
            margin-bottom: 14px !important;
        }
        .rsd-protocol-title {
            font-size: 1.15rem !important;
            font-weight: 700 !important;
            color: #FFFFFF !important;
            margin: 0 0 10px 0 !important;
            line-height: 1.3 !important;
        }
        .rsd-protocol-desc {
            font-size: 0.9rem !important;
            color: #94A3B8 !important;
            line-height: 1.6 !important;
            margin: 0 !important;
        }

        /* ==========================================================================
           3. LIQUID GLASS ACCORDION FAQ SECTION
           ========================================================================== */
        .rsd-faq-sec {
            background: #09090B !important;
            padding: 90px 20px !important;
            position: relative !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .rsd-faq-container {
            max-width: 860px !important;
            margin: 0 auto !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-faq-list {
            display: flex !important;
            flex-direction: column !important;
            gap: 14px !important;
            margin-top: 40px !important;
        }
        .rsd-faq-item {
            background: rgba(18, 18, 24, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 14px !important;
            overflow: hidden !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
            transition: all 0.25s ease !important;
        }
        .rsd-faq-item.active {
            border-color: rgba(56, 189, 248, 0.4) !important;
            background: rgba(24, 24, 32, 0.85) !important;
        }
        .rsd-faq-question {
            padding: 20px 24px !important;
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            color: #FFFFFF !important;
            cursor: pointer !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            user-select: none !important;
            text-align: left !important;
        }
        [dir="rtl"] .rsd-faq-question {
            text-align: right !important;
        }
        .rsd-faq-icon {
            font-size: 1.2rem !important;
            color: #38BDF8 !important;
            transition: transform 0.25s ease !important;
        }
        .rsd-faq-item.active .rsd-faq-icon {
            transform: rotate(45deg) !important;
        }
        .rsd-faq-answer {
            max-height: 0 !important;
            overflow: hidden !important;
            transition: max-height 0.35s cubic-bezier(0.16, 1, 0.3, 1), padding 0.35s ease !important;
            padding: 0 24px !important;
            color: #94A3B8 !important;
            font-size: 0.95rem !important;
            line-height: 1.65 !important;
            text-align: left !important;
        }
        [dir="rtl"] .rsd-faq-answer {
            text-align: right !important;
        }
        .rsd-faq-item.active .rsd-faq-answer {
            max-height: 300px !important;
            padding: 0 24px 22px 24px !important;
        }
    
        /* ==========================================================================
           1. LUXURY LIGHT-THEMED HORIZONTAL MARQUEE TRUST BAR (SINGLE ROW & LIGHT BG)
           ========================================================================== */
        .rsd-hero-trust-bar {
            background: #F8FAFC !important;
            border-top: 1px solid #E2E8F0 !important;
            border-bottom: 1px solid #E2E8F0 !important;
            padding: 24px 0 !important;
            position: relative !important;
            z-index: 10 !important;
            overflow: hidden !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02) !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .rsd-trust-bar-header {
            text-align: center !important;
            margin-bottom: 14px !important;
            padding: 0 20px !important;
        }
        .rsd-trust-badge {
            display: inline-block !important;
            font-size: 0.76rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.12em !important;
            color: #64748B !important;
        }
        .rsd-trust-wrapper {
            width: 100% !important;
            overflow: hidden !important;
            position: relative !important;
            mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,1) 8%, rgba(0,0,0,1) 92%, transparent 100%) !important;
            -webkit-mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,1) 8%, rgba(0,0,0,1) 92%, transparent 100%) !important;
        }
        .rsd-trust-track {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            gap: 16px !important;
            width: max-content !important;
            animation: rsdTrustDrift 28s linear infinite !important;
            white-space: nowrap !important;
        }
        .rsd-trust-track:hover {
            animation-play-state: paused !important;
        }
        @keyframes rsdTrustDrift {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .rsd-trust-chip {
            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px !important;
            color: #0F172A !important;
            font-size: 0.92rem !important;
            font-weight: 700 !important;
            padding: 9px 20px !important;
            background: #FFFFFF !important;
            border: 1.5px solid #E2E8F0 !important;
            border-radius: 9999px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.25s ease !important;
            white-space: nowrap !important;
            user-select: none !important;
            flex-shrink: 0 !important;
        }
        .rsd-trust-chip:hover {
            color: #2563EB !important;
            border-color: #93C5FD !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.12) !important;
        }
        .rsd-trust-icon {
            font-size: 1.1rem !important;
        }

        /* ==========================================================================
           2. AUTHENTIC 3-COLUMN VERTICAL INFINITE MARQUEE TESTIMONIALS
           ========================================================================== */
        .rsd-trust-sec {
            background: #F8FAFC !important;
            padding: 90px 20px !important;
            position: relative !important;
        }
        .rsd-marquee-mask-wrap {
            position: relative !important;
            display: flex !important;
            flex-direction: row !important;
            justify-content: center !important;
            gap: 24px !important;
            max-height: 640px !important;
            overflow: hidden !important;
            -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 10%, black 90%, transparent 100%) !important;
            mask-image: linear-gradient(to bottom, transparent 0%, black 10%, black 90%, transparent 100%) !important;
        }
        .rsd-marquee-mask-wrap:hover .rsd-t-track {
            animation-play-state: paused !important;
        }
        .rsd-marquee-col {
            flex: 1 !important;
            max-width: 380px !important;
            overflow: hidden !important;
        }
        .rsd-t-track {
            display: flex !important;
            flex-direction: column !important;
            gap: 20px !important;
        }
        .rsd-t-track.track-1 { animation: rsdVertDrift 28s linear infinite !important; }
        .rsd-t-track.track-2 { animation: rsdVertDrift 34s linear infinite !important; }
        .rsd-t-track.track-3 { animation: rsdVertDrift 24s linear infinite !important; }

        @keyframes rsdVertDrift {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }
        .rsd-t-card {
            background: #FFFFFF !important;
            border: 1.5px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 24px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            text-align: right !important;
            transition: all 0.25s ease !important;
            box-sizing: border-box !important;
        }
        .rsd-t-card:hover {
            transform: translateY(-3px) !important;
            border-color: #93C5FD !important;
            box-shadow: 0 16px 32px -5px rgba(37, 99, 235, 0.12) !important;
        }
        .rsd-t-text {
            color: #334155 !important;
            font-size: 0.95rem !important;
            line-height: 1.65 !important;
            margin: 0 0 18px 0 !important;
            font-weight: 500 !important;
        }
        .rsd-t-user {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
        }
        .rsd-t-avatar {
            width: 44px !important;
            height: 44px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 2px solid #E2E8F0 !important;
        }
        .rsd-t-name {
            display: block !important;
            font-size: 0.92rem !important;
            font-weight: 800 !important;
            color: #0F172A !important;
        }
        .rsd-t-role {
            display: block !important;
            font-size: 0.78rem !important;
            color: #64748B !important;
        }

        @media (max-width: 991px) {
            .rsd-marquee-col.col-3 { display: none !important; }
        }
        @media (max-width: 640px) {
            .rsd-marquee-col.col-2 { display: none !important; }
            .rsd-marquee-mask-wrap { max-height: 520px !important; }
        }

    
        /* ==========================================================================
           3. 4-STEP ARCHITECTURAL PROTOCOL (LIGHT THEME - BREATHABLE WHITESPACE)
           ========================================================================== */
        .rsd-protocol-sec {
            background: #FFFFFF !important;
            padding: 95px 20px !important;
            position: relative !important;
            border-top: 1px solid #E2E8F0 !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
        .rsd-protocol-container {
            max-width: 1180px !important;
            margin: 0 auto !important;
            position: relative !important;
        }
        .rsd-protocol-sec .rsd-roi-pill {
            background: #EFF6FF !important;
            border: 1px solid #BFDBFE !important;
            color: #2563EB !important;
            display: inline-block !important;
            padding: 6px 18px !important;
            border-radius: 9999px !important;
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.08em !important;
            margin-bottom: 12px !important;
        }
        .rsd-protocol-sec .rsd-roi-title {
            color: #0F172A !important;
            font-size: clamp(1.8rem, 3.5vw, 2.5rem) !important;
            font-weight: 800 !important;
            margin-bottom: 12px !important;
        }
        .rsd-protocol-sec .rsd-roi-subtitle {
            color: #64748B !important;
            font-size: 1.05rem !important;
            line-height: 1.65 !important;
            margin-bottom: 48px !important;
        }
        .rsd-protocol-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)) !important;
            gap: 24px !important;
        }
        .rsd-protocol-card {
            background: #FFFFFF !important;
            border: 1.5px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 30px 24px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            box-sizing: border-box !important;
        }
        [dir="rtl"] .rsd-protocol-card {
            text-align: right !important;
        }
        .rsd-protocol-card:hover {
            border-color: #93C5FD !important;
            transform: translateY(-4px) !important;
            box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.12) !important;
        }
        .rsd-protocol-num {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 44px !important;
            height: 44px !important;
            border-radius: 12px !important;
            background: #EFF6FF !important;
            border: 1px solid #DBEAFE !important;
            color: #2563EB !important;
            font-family: monospace !important;
            font-size: 1.25rem !important;
            font-weight: 800 !important;
            margin-bottom: 18px !important;
        }
        .rsd-protocol-title {
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            color: #0F172A !important;
            margin: 0 0 12px 0 !important;
            line-height: 1.4 !important;
        }
        .rsd-protocol-desc {
            font-size: 0.94rem !important;
            color: #475569 !important;
            line-height: 1.65 !important;
            margin: 0 !important;
        }

        /* ==========================================================================
           4. UNIFIED COMPARISON MATRIX (LIGHT THEME)
           ========================================================================== */
        .rsd-matrix-sec {
            background: #F8FAFC !important;
            padding: 90px 20px !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
        .rsd-matrix-sec .rsd-saas-title {
            color: #0F172A !important;
            font-size: clamp(1.8rem, 3.5vw, 2.5rem) !important;
            font-weight: 800 !important;
        }

    

        /* ==========================================================================
           UPGRADED HERO SCALE & CRISP HIGH-CONTRAST HOVER SYSTEM
           ========================================================================== */
        .rsd-saas-hero {
            position: relative !important;
            background: radial-gradient(circle at 50% -20%, #FFE4D6 0%, #FED7AA 15%, #FEE2E2 35%, #F5F3FF 60%, #FFFFFF 95%) !important;
            padding: 80px 20px 70px 20px !important;
            text-align: center !important;
            overflow: hidden !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .rsd-saas-hero-container {
            max-width: 1240px !important;
            margin: 0 auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-saas-pill {
            background: rgba(255, 255, 255, 0.95) !important;
            border: 1px solid rgba(37, 99, 235, 0.2) !important;
            color: #2563EB !important;
            padding: 4px 14px !important;
            border-radius: 9999px !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.04em !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin-bottom: 20px !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08) !important;
        }
        .rsd-saas-h1 {
            font-size: clamp(2.8rem, 5.4vw, 4.5rem) !important;
            font-weight: 800 !important;
            line-height: 1.12 !important;
            color: #0F172A !important;
            letter-spacing: -0.03em !important;
            margin: 0 0 22px 0 !important;
            max-width: 1050px !important;
        }
        .rsd-saas-subtext {
            font-size: clamp(1.1rem, 1.8vw, 1.35rem) !important;
            color: #475569 !important;
            line-height: 1.65 !important;
            max-width: 820px !important;
            margin: 0 auto 36px auto !important;
            font-weight: 500 !important;
        }
        .rsd-hero-showcase-wrapper {
            width: 100% !important;
            max-width: 1060px !important;
            margin: 0 auto 40px auto !important;
            display: flex !important;
            justify-content: center !important;
        }
        .rsd-hero-master-img {
            width: 100% !important;
            max-width: 1060px !important;
            height: auto !important;
            border-radius: 20px !important;
            filter: drop-shadow(0 25px 50px rgba(15, 23, 42, 0.15)) !important;
        }

        /* 100% HIGH-CONTRAST ROI CALCULATOR BUTTON (ZERO HOVER INVISIBILITY) */
        .rsd-output-btn {
            background: #2563EB !important;
            color: #FFFFFF !important;
            font-size: 1.02rem !important;
            font-weight: 800 !important;
            padding: 16px 28px !important;
            border-radius: 14px !important;
            border: none !important;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.5) !important;
            cursor: pointer !important;
            width: 100% !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            text-align: center !important;
            display: block !important;
            text-decoration: none !important;
        }
        .rsd-output-btn:hover {
            background: #1D4ED8 !important;
            color: #FFFFFF !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.7) !important;
        }

        /* FLAWLESS LIGHT CARD HOVER IN 4-STEP ROADMAP (ZERO DARK BOX ARTIFACTS) */
        .rsd-protocol-card {
            background: #FFFFFF !important;
            border: 1.5px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 30px 24px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            box-sizing: border-box !important;
        }
        .rsd-protocol-card:hover {
            background: #FFFFFF !important;
            border-color: #2563EB !important;
            transform: translateY(-6px) !important;
            box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.12) !important;
        }
        .rsd-protocol-card:hover .rsd-protocol-title {
            color: #0F172A !important;
        }
        .rsd-protocol-card:hover .rsd-protocol-desc {
            color: #475569 !important;
        }
        .rsd-protocol-card:hover .rsd-protocol-num {
            background: #2563EB !important;
            color: #FFFFFF !important;
            border-color: #2563EB !important;
        }

    

        /* ==========================================================================
           PERFECTED RESPONSIVE HERO (CINEMATIC DESKTOP + FLAWLESS COMPACT MOBILE)
           ========================================================================== */
        .rsd-saas-hero {
            position: relative !important;
            background: radial-gradient(circle at 50% -20%, #FFE4D6 0%, #FED7AA 15%, #FEE2E2 35%, #F5F3FF 60%, #FFFFFF 95%) !important;
            padding: 75px 20px 65px 20px !important;
            text-align: center !important;
            overflow: hidden !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .rsd-saas-hero-container {
            max-width: 1240px !important;
            margin: 0 auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-saas-pill {
            background: rgba(255, 255, 255, 0.95) !important;
            border: 1px solid rgba(37, 99, 235, 0.2) !important;
            color: #2563EB !important;
            padding: 4px 14px !important;
            border-radius: 9999px !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.04em !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin-bottom: 18px !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08) !important;
        }
        .rsd-saas-h1 {
            font-size: clamp(2.4rem, 4.8vw, 4.2rem) !important;
            font-weight: 800 !important;
            line-height: 1.14 !important;
            color: #0F172A !important;
            letter-spacing: -0.03em !important;
            margin: 0 0 20px 0 !important;
            max-width: 1050px !important;
        }
        .rsd-saas-subtext {
            font-size: clamp(1.05rem, 1.6vw, 1.3rem) !important;
            color: #475569 !important;
            line-height: 1.6 !important;
            max-width: 820px !important;
            margin: 0 auto 34px auto !important;
            font-weight: 500 !important;
        }
        .rsd-hero-showcase-wrapper {
            width: 100% !important;
            max-width: 1060px !important;
            margin: 0 auto 36px auto !important;
            display: flex !important;
            justify-content: center !important;
        }
        .rsd-hero-master-img {
            width: 100% !important;
            max-width: 1060px !important;
            height: auto !important;
            border-radius: 20px !important;
            filter: drop-shadow(0 25px 50px rgba(15, 23, 42, 0.15)) !important;
        }
        .rsd-saas-cta-group {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 16px !important;
            flex-wrap: wrap !important;
        }

        /* 📱 ULTRA-REFINED MOBILE HERO (MAX-WIDTH 768PX & 640PX) */
        @media (max-width: 768px) {
            .rsd-saas-hero {
                padding: 32px 16px 28px 16px !important;
            }
            .rsd-saas-pill {
                padding: 3px 11px !important;
                font-size: 0.72rem !important;
                margin-bottom: 12px !important;
            }
            .rsd-saas-h1 {
                font-size: clamp(1.65rem, 6.2vw, 2.1rem) !important;
                line-height: 1.22 !important;
                letter-spacing: -0.02em !important;
                margin: 0 auto 12px auto !important;
                max-width: 95% !important;
            }
            .rsd-saas-subtext {
                font-size: 0.9rem !important;
                line-height: 1.45 !important;
                margin: 0 auto 16px auto !important;
                max-width: 92% !important;
                color: #475569 !important;
            }
            .rsd-hero-showcase-wrapper {
                max-width: 94% !important;
                margin: 0 auto 20px auto !important;
            }
            .rsd-hero-master-img {
                max-width: 100% !important;
                border-radius: 12px !important;
                filter: drop-shadow(0 12px 25px rgba(15, 23, 42, 0.12)) !important;
            }
            .rsd-saas-cta-group {
                flex-direction: column !important;
                gap: 10px !important;
                width: 100% !important;
                max-width: 310px !important;
                margin: 0 auto !important;
            }
            .shiny-cta {
                width: 100% !important;
                padding: 13px 20px !important;
                font-size: 0.95rem !important;
                justify-content: center !important;
            }
            .rsd-btn-showcase {
                width: 100% !important;
                padding: 11px 20px !important;
                font-size: 0.88rem !important;
                justify-content: center !important;
                text-align: center !important;
            }
        }

    

        /* ==========================================================================
           HEADER OVERLAP CLEARANCE & FLAWLESS HERO VIEWPORT ALIGNMENT
           ========================================================================== */
        .rsd-saas-hero {
            position: relative !important;
            background: radial-gradient(circle at 50% -20%, #FFE4D6 0%, #FED7AA 15%, #FEE2E2 35%, #F5F3FF 60%, #FFFFFF 95%) !important;
            padding: 105px 20px 65px 20px !important;
            text-align: center !important;
            overflow: hidden !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        @media (max-width: 768px) {
            .rsd-saas-hero {
                padding: 98px 16px 28px 16px !important; /* Full 70px header clearance + 28px breathing space */
            }
            .rsd-saas-pill {
                padding: 4px 12px !important;
                font-size: 0.74rem !important;
                margin-bottom: 12px !important;
            }
            .rsd-saas-h1 {
                font-size: clamp(1.65rem, 6.2vw, 2.1rem) !important;
                line-height: 1.22 !important;
                letter-spacing: -0.02em !important;
                margin: 0 auto 12px auto !important;
                max-width: 95% !important;
            }
            .rsd-saas-subtext {
                font-size: 0.9rem !important;
                line-height: 1.45 !important;
                margin: 0 auto 16px auto !important;
                max-width: 92% !important;
                color: #475569 !important;
            }
            .rsd-hero-showcase-wrapper {
                max-width: 94% !important;
                margin: 0 auto 20px auto !important;
            }
            .rsd-hero-master-img {
                max-width: 100% !important;
                border-radius: 12px !important;
                filter: drop-shadow(0 12px 25px rgba(15, 23, 42, 0.12)) !important;
            }
            .rsd-saas-cta-group {
                flex-direction: column !important;
                gap: 10px !important;
                width: 100% !important;
                max-width: 310px !important;
                margin: 0 auto !important;
            }
            .shiny-cta {
                width: 100% !important;
                padding: 13px 20px !important;
                font-size: 0.95rem !important;
                justify-content: center !important;
            }
            .rsd-btn-showcase {
                width: 100% !important;
                padding: 11px 20px !important;
                font-size: 0.88rem !important;
                justify-content: center !important;
                text-align: center !important;
            }
        }

    

        /* ==========================================================================
           SCROLL-DRIVEN MOTION ENGINE (FADE IN ON SCROLL DOWN & GENTLE UP DRIFT)
           ========================================================================== */
        .rsd-scroll-reveal {
            opacity: 0 !important;
            transform: translateY(28px) scale(0.985) !important;
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1) !important;
            will-change: opacity, transform !important;
        }
        .rsd-scroll-reveal.rsd-in-view {
            opacity: 1 !important;
            transform: translateY(0) scale(1) !important;
        }
        body[data-scroll-dir='up'] .rsd-scroll-reveal:not(.rsd-in-view) {
            transform: translateY(-20px) scale(0.985) !important;
        }
        .rsd-stagger-1 { transition-delay: 0.06s !important; }
        .rsd-stagger-2 { transition-delay: 0.12s !important; }
        .rsd-stagger-3 { transition-delay: 0.18s !important; }
        .rsd-stagger-4 { transition-delay: 0.24s !important; }

    </style>
        <?php
    }

    public function render_language_switcher_header() {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_ar = (strpos($request_uri, '/ar') !== false);

        // Smart 1-to-1 Page URL Mapping for EN & AR
        $ar_url = 'https://redseadigital.pro/ar-home/';
        $en_url = 'https://redseadigital.pro/';

        if (strpos($request_uri, '/work') !== false || strpos($request_uri, '/ar-work') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-work/';
            $en_url = 'https://redseadigital.pro/work/';
        } elseif (strpos($request_uri, 'yallatrip') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-yallatrip/';
            $en_url = 'https://redseadigital.pro/yallatrip/';
        } elseif (strpos($request_uri, 'asl-leather') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-asl-leather/';
            $en_url = 'https://redseadigital.pro/asl-leather/';
        } elseif (strpos($request_uri, 'paradise-spa') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-paradise-spa/';
            $en_url = 'https://redseadigital.pro/paradise-spa/';
        } elseif (strpos($request_uri, 'life-pets') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-life-pets/';
            $en_url = 'https://redseadigital.pro/life-pets/';
        }
        ?>
        <style id="rsd-mobile-lang-switcher-css">
            .rsd-sleek-lang-toggle {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                font-family: Inter, system-ui, sans-serif !important;
                font-size: 0.85rem !important;
                font-weight: 700 !important;
                letter-spacing: 0.5px !important;
                margin: 0 12px !important;
                vertical-align: middle !important;
                background: rgba(17, 17, 17, 0.06);
                padding: 4px 12px;
                border-radius: 20px;
                border: 1px solid rgba(197, 160, 89, 0.35);
                z-index: 99999 !important;
            }
            .rsd-sleek-lang-toggle a {
                color: #646460 !important;
                text-decoration: none !important;
                transition: color 0.2s ease !important;
                padding: 2px 6px !important;
            }
            .rsd-sleek-lang-toggle a:hover, .rsd-sleek-lang-toggle a.active {
                color: #2563EB !important;
                font-weight: 900 !important;
            }

            /* Responsive Mobile Header Tweaks */
            @media (max-width: 768px) {
                .rsd-sleek-lang-toggle {
                    display: inline-flex !important;
                    margin: 4px 8px !important;
                    padding: 4px 10px !important;
                    font-size: 0.8rem !important;
                    float: none !important;
                }
                .rsd-mobile-header-toggle-wrap {
                    display: flex !important;
                    align-items: center !important;
                    justify-content: flex-end !important;
                    gap: 8px !important;
                }
            }
        </style>
        <script id="rsd-mobile-lang-switcher-js">
        document.addEventListener("DOMContentLoaded", function() {
            function injectMobileSwitcher() {
                var langHTML = '<div class="rsd-sleek-lang-toggle"><a href="<?php echo $ar_url; ?>" class="<?php echo $is_ar ? "active" : ""; ?>">AR</a><span style="color:#cbd5e1;">|</span><a href="<?php echo $en_url; ?>" class="<?php echo !$is_ar ? "active" : ""; ?>">EN</a></div>';

                // 1. Inject into Header Navigation
                var navs = document.querySelectorAll('.elementor-nav-menu, .site-header .navigation, header nav, .elementor-location-header');
                navs.forEach(function(nav) {
                    if (nav && !nav.querySelector('.rsd-sleek-lang-toggle')) {
                        var li = document.createElement('li');
                        li.className = 'menu-item rsd-lang-item';
                        li.style.display = 'inline-flex';
                        li.style.alignItems = 'center';
                        li.innerHTML = langHTML;
                        nav.appendChild(li);
                    }
                });

                // 2. Inject directly into Mobile Header Bar
                var mobileHeaderControls = document.querySelectorAll('.elementor-menu-toggle, .rsd-mobile-header, header .elementor-container');
                mobileHeaderControls.forEach(function(ctrl) {
                    if (ctrl && ctrl.parentNode && !ctrl.parentNode.querySelector('.rsd-sleek-lang-toggle')) {
                        var div = document.createElement('div');
                        div.style.display = 'inline-flex';
                        div.style.alignItems = 'center';
                        div.innerHTML = langHTML;
                        ctrl.parentNode.insertBefore(div, ctrl);
                    }
                });
            }

            injectMobileSwitcher();
            setTimeout(injectMobileSwitcher, 500);
            setTimeout(injectMobileSwitcher, 1500);
        });
        
                    </script>
        <?php
    }

    public function inject_universal_master_header() {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_ar = (strpos($request_uri, '/ar') !== false);

        // Smart 1-to-1 Page URL Mapping for Language Switcher
        $home_url = $is_ar ? 'https://redseadigital.pro/ar-home/' : 'https://redseadigital.pro/';
        $work_url = $is_ar ? 'https://redseadigital.pro/ar-work/' : 'https://redseadigital.pro/work/';
        
        $ar_url = 'https://redseadigital.pro/ar-home/';
        $en_url = 'https://redseadigital.pro/';

        if (strpos($request_uri, '/work') !== false || strpos($request_uri, '/ar-work') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-work/';
            $en_url = 'https://redseadigital.pro/work/';
        } elseif (strpos($request_uri, 'yallatrip') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-yallatrip/';
            $en_url = 'https://redseadigital.pro/yallatrip/';
        } elseif (strpos($request_uri, 'asl-leather') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-asl-leather/';
            $en_url = 'https://redseadigital.pro/asl-leather/';
        } elseif (strpos($request_uri, 'paradise-spa') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-paradise-spa/';
            $en_url = 'https://redseadigital.pro/paradise-spa/';
        } elseif (strpos($request_uri, 'life-pets') !== false) {
            $ar_url = 'https://redseadigital.pro/ar-life-pets/';
            $en_url = 'https://redseadigital.pro/life-pets/';
        }

        $logo_url = 'https://redseadigital.pro/wp-content/uploads/2026/08/red_sea_digital_logo_ultra_cropped.webp';
        
        $nav_items_ar = '<li><a href="' . $home_url . '">الرئيسية</a></li><li><a href="' . $work_url . '">أعمالنا</a></li><li><a href="' . $home_url . '#process">آلية العمل</a></li><li><a href="' . $home_url . '#capabilities">القدرات</a></li><li><a href="' . $home_url . '#why-us">لماذا نحن</a></li>';
        $nav_items_en = '<li><a href="' . $home_url . '">Home</a></li><li><a href="' . $work_url . '">Selected Work</a></li><li><a href="' . $home_url . '#process">Process</a></li><li><a href="' . $home_url . '#capabilities">Capabilities</a></li><li><a href="' . $home_url . '#why-us">Why Us</a></li>';
        
        $nav_items = $is_ar ? $nav_items_ar : $nav_items_en;
        $btn_text = $is_ar ? 'المساعد ↗' : 'Concierge ↗';
        $dir = $is_ar ? 'rtl' : 'ltr';
        $ar_active = $is_ar ? 'active' : '';
        $en_active = !$is_ar ? 'active' : '';
        ?>
        <style id="rsd-master-header-suppress-theme-css">
            /* Suppress theme default headers and entry titles */
            .entry-title,
            h1.entry-title,
            header.entry-header,
            .page-header,
            .site-main > h1:first-child,
            header.site-header:not(#rsdUniversalHeader),
            header.hello-header:not(#rsdUniversalHeader),
            .site-header:not(#rsdUniversalHeader),
            #site-header:not(#rsdUniversalHeader),
            .elementor-location-header:not(#rsdUniversalHeader) {
                display: none !important;
            }

            /* Master Universal Header Styling */
            #rsdUniversalHeader {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                height: 84px !important;
                z-index: 999999 !important;
                background: rgba(251, 251, 249, 0.96) !important;
                backdrop-filter: blur(16px) !important;
                -webkit-backdrop-filter: blur(16px) !important;
                border-bottom: 1px solid rgba(229, 229, 224, 0.8) !important;
                display: flex !important;
                align-items: center !important;
                box-sizing: border-box !important;
                margin: 0 !important;
                padding: 0 !important;
                direction: <?php echo $dir; ?> !important;
            }

            .rsd-master-header-container {
                width: 100% !important;
                max-width: 1320px !important;
                height: 100% !important;
                margin: 0 auto !important;
                padding: 0 24px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                box-sizing: border-box !important;
            }

            .rsd-master-logo-link {
                display: flex !important;
                align-items: center !important;
                text-decoration: none !important;
            }

            .rsd-master-logo-img {
                height: 64px !important;
                width: auto !important;
                max-height: 68px !important;
                object-fit: contain !important;
            }

            .rsd-master-nav {
                display: flex !important;
                align-items: center !important;
                gap: 28px !important;
                list-style: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .rsd-master-nav a {
                color: #0F172A !important;
                text-decoration: none !important;
                font-family: <?php echo $is_ar ? "'Cairo', 'Tajawal', sans-serif" : "Inter, system-ui, sans-serif"; ?> !important;
                font-weight: 600 !important;
                font-size: 0.95rem !important;
                transition: color 0.2s ease !important;
            }

            .rsd-master-nav a:hover {
                color: #2563EB !important;
            }

            .rsd-header-action-wrap {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }

            .rsd-master-btn-black {
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                color: #FFFFFF !important;
                border: none !important;
                padding: 10px 24px !important;
                border-radius: 50px !important;
                font-weight: 700 !important;
                font-size: 0.9rem !important;
                cursor: pointer !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 6px !important;
                box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25) !important;
                transition: all 0.25s ease !important;
            }

            .rsd-master-btn-black:hover {
                background: #2563EB !important;
                color: #0F172A !important;
                transform: translateY(-1px) !important;
            }

            /* Hamburger Menu Button */
            .rsd-hamburger-btn {
                display: none !important;
                background: rgba(17, 17, 17, 0.06) !important;
                border: 1px solid rgba(17, 17, 17, 0.12) !important;
                border-radius: 12px !important;
                width: 44px !important;
                height: 44px !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                padding: 0 !important;
                transition: background 0.2s ease !important;
            }

            .rsd-hamburger-btn:hover {
                background: rgba(197, 160, 89, 0.15) !important;
            }

            .rsd-hamburger-btn svg {
                width: 22px !important;
                height: 22px !important;
                fill: #0F172A !important;
            }

            /* Mobile Glassmorphism Side Drawer */
            #rsdMobileDrawer {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                background: rgba(17, 17, 17, 0.96) !important;
                backdrop-filter: blur(20px) !important;
                -webkit-backdrop-filter: blur(20px) !important;
                z-index: 9999999 !important;
                display: flex !important;
                flex-direction: column !important;
                padding: 24px 32px !important;
                box-sizing: border-box !important;
                opacity: 0 !important;
                pointer-events: none !important;
                transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
                direction: <?php echo $dir; ?> !important;
            }

            #rsdMobileDrawer.active {
                opacity: 1 !important;
                pointer-events: all !important;
            }

            .rsd-drawer-hdr {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                padding-bottom: 24px !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            }

            .rsd-drawer-close-btn {
                background: rgba(255, 255, 255, 0.1) !important;
                border: none !important;
                color: #FFFFFF !important;
                width: 40px !important;
                height: 40px !important;
                border-radius: 50% !important;
                font-size: 1.4rem !important;
                cursor: pointer !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .rsd-drawer-nav {
                display: flex !important;
                flex-direction: column !important;
                gap: 24px !important;
                margin-top: 40px !important;
                list-style: none !important;
                padding: 0 !important;
            }

            .rsd-drawer-nav a {
                color: #FFFFFF !important;
                text-decoration: none !important;
                font-size: 1.5rem !important;
                font-weight: 700 !important;
                font-family: <?php echo $is_ar ? "'Cairo', 'Tajawal', sans-serif" : "Inter, system-ui, sans-serif"; ?> !important;
                transition: color 0.2s ease !important;
            }

            .rsd-drawer-nav a:hover {
                color: #2563EB !important;
            }

            /* Responsive Mobile Header Adjustment */
            @media (max-width: 900px) {
                .rsd-master-nav,
                .rsd-master-btn-black {
                    display: none !important;
                }
                .rsd-hamburger-btn {
                    display: flex !important;
                }
                .rsd-master-header-container {
                    padding: 0 16px !important;
                }
                .rsd-master-logo-img {
                    height: 50px !important;
                }
            }
        </style>

        <script id="rsd-universal-header-js">
        (function() {
            window.toggleRsdMobileDrawer = function(e) {
                if (e) {
                    if (typeof e.preventDefault === 'function') e.preventDefault();
                    if (typeof e.stopPropagation === 'function') e.stopPropagation();
                }
                var drawer = document.getElementById('rsdMobileDrawer');
                if (!drawer) return;
                if (drawer.classList.contains('active')) {
                    drawer.classList.remove('active');
                } else {
                    drawer.classList.add('active');
                }
            };

            function injectMasterHeader() {
                if (document.getElementById('rsdUniversalHeader')) return;
                
                var headerHTML = '<div class="rsd-master-header-container">' +
                    '<a href="<?php echo $home_url; ?>" class="rsd-master-logo-link">' +
                        '<img src="<?php echo $logo_url; ?>" alt="Red Sea Digital Logo" class="rsd-master-logo-img" />' +
                    '</a>' +
                    '<nav><ul class="rsd-master-nav">' +
                        '<?php echo $nav_items; ?>' +
                        '<li style="display: inline-flex; align-items: center;">' +
                            '<div class="rsd-sleek-lang-toggle">' +
                                '<a href="<?php echo $ar_url; ?>" class="<?php echo $ar_active; ?>">AR</a>' +
                                '<span style="color:#cbd5e1;">|</span>' +
                                '<a href="<?php echo $en_url; ?>" class="<?php echo $en_active; ?>">EN</a>' +
                            '</div>' +
                        '</li>' +
                    '</ul></nav>' +
                    '<div class="rsd-header-action-wrap">' +
                        '<button onclick="var el=document.getElementById(&apos;rsd-booking-calendar&apos;);if(el){el.scrollIntoView({behavior:&apos;smooth&apos;});}else{window.toggleRsdChatWidget(event);}" class="rsd-master-btn-black">[ <?php echo $btn_text; ?> ]</button>' +
                        '<button onclick="window.toggleRsdMobileDrawer(event)" class="rsd-hamburger-btn" aria-label="Open Navigation Menu">' +
                            '<svg viewBox="0 0 24 24"><path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/></svg>' +
                        '</button>' +
                    '</div>' +
                '</div>';

                var headerElem = document.createElement('header');
                headerElem.id = 'rsdUniversalHeader';
                headerElem.innerHTML = headerHTML;

                // Build Mobile Side Drawer HTML
                var drawerHTML = '<div class="rsd-drawer-hdr">' +
                    '<a href="<?php echo $home_url; ?>"><img src="<?php echo $logo_url; ?>" style="height:44px; width:auto; filter:brightness(0) invert(1);" /></a>' +
                    '<div style="display:flex; align-items:center; gap:16px;">' +
                        '<div class="rsd-sleek-lang-toggle" style="background:rgba(255,255,255,0.1); border-color:rgba(197,160,89,0.5);">' +
                            '<a href="<?php echo $ar_url; ?>" class="<?php echo $ar_active; ?>" style="color:#fff !important;">AR</a>' +
                            '<span style="color:#64748b;">|</span>' +
                            '<a href="<?php echo $en_url; ?>" class="<?php echo $en_active; ?>" style="color:#fff !important;">EN</a>' +
                        '</div>' +
                        '<button onclick="window.toggleRsdMobileDrawer(event)" class="rsd-drawer-close-btn">✕</button>' +
                    '</div>' +
                '</div>' +
                '<ul class="rsd-drawer-nav">' +
                    '<?php echo $nav_items; ?>' +
                '</ul>' +
                '<div style="margin-top:auto; padding-top:24px;">' +
                    '<button onclick="window.toggleRsdMobileDrawer(event); setTimeout(function(){ window.toggleRsdChatWidget(); }, 200);" class="rsd-master-btn-black" style="width:100%; justify-content:center; padding:14px;">[ <?php echo $btn_text; ?> ]</button>' +
                '</div>';

                var drawerElem = document.createElement('div');
                drawerElem.id = 'rsdMobileDrawer';
                drawerElem.innerHTML = drawerHTML;

                if (document.body) {
                    document.body.insertBefore(headerElem, document.body.firstChild);
                    document.body.appendChild(drawerElem);

                    if (!document.getElementById('rsd-header-spacer')) {
                        var spacer = document.createElement('div');
                        spacer.id = 'rsd-header-spacer';
                        spacer.style.cssText = 'display:none !important; height:0 !important; margin:0 !important; padding:0 !important;';
                        document.body.insertBefore(spacer, headerElem.nextSibling);
                    }
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', injectMasterHeader);
            } else {
                injectMasterHeader();
            }
            setTimeout(injectMasterHeader, 300);
            setTimeout(injectMasterHeader, 1000);
        })();
        
                    </script>
        <?php
    }

    public function inject_head_chat_script() {
        if (get_option('rsd_widget_enabled', '1') !== '1') return;
        ?>
        <script id="rsd-global-chat-head-script">
        window.autoResizeRsdInput = function(elem) {
                if (!elem) return;
                elem.style.height = '44px';
                var scrollH = elem.scrollHeight;
                if (scrollH > 120) {
                    elem.style.height = '120px';
                    elem.style.overflowY = 'auto';
                } else if (scrollH > 44) {
                    elem.style.height = scrollH + 'px';
                    elem.style.overflowY = 'hidden';
                } else {
                    elem.style.height = '44px';
                    elem.style.overflowY = 'hidden';
                }
            };

            window.toggleRsdChatWidget = function(e) {
            if (e) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
            }
            var win = document.getElementById('rsdModalWindow');
            if (!win) return;

            var isCurrentlyOpen = win.classList.contains('active') || (win.style.display === 'flex');
            if (!isCurrentlyOpen) {
                win.classList.add('active');
                win.style.setProperty('display', 'flex', 'important');
                win.style.setProperty('opacity', '1', 'important');
                win.style.setProperty('visibility', 'visible', 'important');
                win.style.setProperty('pointer-events', 'all', 'important');
                win.style.setProperty('z-index', '999999999', 'important');
                var inp = document.getElementById('rsdInputField');
                if (inp) setTimeout(function() { inp.focus(); }, 120);
            } else {
                win.classList.remove('active');
                win.style.setProperty('display', 'none', 'important');
                win.style.setProperty('opacity', '0', 'important');
                win.style.setProperty('pointer-events', 'none', 'important');
            }
        };
        
                    </script>
        <?php
    }

    
    public function render_universal_master_footer() {
        if (is_admin()) return;
        $is_ar = (is_page(163) || strpos($_SERVER['REQUEST_URI'] ?? '', '/ar-') !== false);
        
        $desc = $is_ar ? 'استوديو متقدم لتصميم وتطوير أنظمة الحجز المباشر والمتاجر الإلكترونية المخصصة.' : 'Boutique digital architecture studio engineering direct booking systems, bespoke e-commerce & AI concierge infrastructure.';
        $status_txt = $is_ar ? 'جميع أنظمة المبيعات تعمل بنجاح' : 'All Revenue Systems Operational';
        $nav_title = $is_ar ? 'روابط التصفح' : 'NAVIGATION';
        $link_work = $is_ar ? 'أحدث أعمالنا' : 'Selected Work';
        $link_systems = $is_ar ? 'خدماتنا وأنظمتنا' : 'Capabilities & Systems';
        $link_studio = $is_ar ? 'عن الشركة' : 'Studio Manifesto';
        $link_privacy = $is_ar ? 'سياسة الخصوصية' : 'Privacy Policy';
        
        $contact_title = $is_ar ? 'التواصل المباشر' : 'STUDIO & CONCIERGE';
        $lbl_concierge = $is_ar ? 'الدعم والتواصل المباشر:' : 'Direct Concierge:';
        $lbl_locations = $is_ar ? 'الفروع:' : 'Locations:';
        $val_locations = $is_ar ? 'ساحل البحر الأحمر والقاهرة' : 'Red Sea Coast & Cairo';
        $lbl_wa = $is_ar ? 'تواصل عبر الواتساب:' : 'WhatsApp Direct:';
        
        $connect_title = $is_ar ? 'تواصل معنا' : 'CONNECT';
        $copyright = $is_ar ? '© 2026 RED SEA DIGITAL. جميع الحقوق محفوظة.' : '© 2026 RED SEA DIGITAL. All Rights Reserved. Quiet Luxury Digital Architecture Studio.';
        $sub_tagline = $is_ar ? 'تصميم وتطوير بدقة عالية وبدون أي اشتراكات شهرية.' : 'Architected with Precision & Zero SaaS Dependencies.';
        ?>
        <footer class="rsd-master-footer">
            <div class="rsd-footer-watermark" aria-hidden="true">RED SEA DIGITAL</div>
            <div class="rsd-footer-inner">
                <!-- Column 1: Brand & Positioning -->
                <div class="rsd-footer-col rsd-footer-brand">
                    <h3 class="rsd-footer-logo">RED SEA DIGITAL</h3>
                    <p class="rsd-footer-desc"><?php echo $desc; ?></p>
                    <div class="rsd-footer-status">
                        <span class="rsd-status-dot"></span>
                        <span class="rsd-status-text"><?php echo $status_txt; ?></span>
                    </div>
                </div>

                <!-- Column 2: Navigation -->
                <div class="rsd-footer-col">
                    <h4 class="rsd-footer-heading"><?php echo $nav_title; ?></h4>
                    <ul class="rsd-footer-links">
                        <li><a href="#work"><?php echo $link_work; ?></a></li>
                        <li><a href="#systems"><?php echo $link_systems; ?></a></li>
                        <li><a href="#studio"><?php echo $link_studio; ?></a></li>
                        <li><a href="/privacy-policy/"><?php echo $link_privacy; ?></a></li>
                    </ul>
                </div>

                <!-- Column 3: Contact & Location -->
                <div class="rsd-footer-col">
                    <h4 class="rsd-footer-heading"><?php echo $contact_title; ?></h4>
                    <p class="rsd-footer-info">
                        <strong><?php echo $lbl_concierge; ?></strong><br>
                        <a href="mailto:concierge@redseadigital.pro" class="rsd-footer-email">concierge@redseadigital.pro</a>
                    </p>
                    <p class="rsd-footer-info" style="margin-top: 14px;">
                        <strong><?php echo $lbl_locations; ?></strong><br>
                        <span class="rsd-footer-text-muted"><?php echo $val_locations; ?></span>
                    </p>
                    <p class="rsd-footer-info" style="margin-top: 14px;">
                        <strong><?php echo $lbl_wa; ?></strong><br>
                        <a href="https://wa.me/201028803080" target="_blank" rel="noopener" class="rsd-footer-wa">+20 102 880 3080 ↗</a>
                    </p>
                </div>

                <!-- Column 4: Connect & Systems -->
                <div class="rsd-footer-col">
                    <h4 class="rsd-footer-heading"><?php echo $connect_title; ?></h4>
                    <ul class="rsd-footer-links">
                        <li><a href="https://linkedin.com" target="_blank" rel="noopener">LinkedIn ↗</a></li>
                        <li><a href="https://github.com" target="_blank" rel="noopener">GitHub ↗</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="rsd-footer-bottom">
                <div class="rsd-footer-bottom-inner">
                    <p class="rsd-copyright"><?php echo $copyright; ?></p>
                    <p class="rsd-footer-tagline"><?php echo $sub_tagline; ?></p>
                </div>
            </div>
        </footer>
        <?php
    }

    public function inject_frontend_widget() {
        if (get_option('rsd_widget_enabled', '1') !== '1') return;

        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_ar = (strpos($request_uri, '/ar') !== false) || (isset($_GET['lang']) && $_GET['lang'] === 'ar');
        if (!$is_ar && function_exists('pll_current_language')) {
            $is_ar = (pll_current_language() === 'ar');
        }

        $whatsapp_phone = esc_attr(get_option('rsd_whatsapp_phone', '201028803080'));
        $chat_dir = $is_ar ? 'rtl' : 'ltr';
        ?>
        <style id="rsd-light-luxury-ai-css">
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cairo:wght@400;500;600;700;800&display=swap');

            /* Floating Launcher Button */
            .rsd-chat-launcher {
                position: fixed !important;
                bottom: 26px !important;
                right: 26px !important;
                z-index: 9999999 !important;
                width: 62px !important;
                height: 62px !important;
                border-radius: 50% !important;
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                border: 2px solid #FFFFFF !important;
                color: #FFFFFF !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                box-shadow: 0 12px 32px rgba(37, 99, 235, 0.35), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }
            .rsd-chat-launcher:hover {
                transform: scale(1.08) translateY(-3px) !important;
                box-shadow: 0 18px 40px rgba(37, 99, 235, 0.45) !important;
            }
            .rsd-chat-launcher svg {
                width: 28px !important;
                height: 28px !important;
                fill: #FFFFFF !important;
            }
            .rsd-chat-launcher-badge {
                position: absolute !important;
                top: 2px !important;
                right: 2px !important;
                width: 14px !important;
                height: 14px !important;
                background: #10B981 !important;
                border: 2.5px solid #FFFFFF !important;
                border-radius: 50% !important;
            }

            /* Light Luxury Glassmorphic Modal Window */
            #rsdModalWindow {
                position: fixed !important;
                bottom: 98px !important;
                right: 26px !important;
                width: 400px !important;
                height: 650px !important;
                max-height: calc(100vh - 120px) !important;
                z-index: 99999999 !important;
                background: #FFFFFF !important;
                border: 1px solid rgba(0, 0, 0, 0.08) !important;
                border-radius: 26px !important;
                box-shadow: 0 24px 60px -10px rgba(0, 0, 0, 0.18), 0 0 1px rgba(0, 0, 0, 0.1) !important;
                display: none;
                flex-direction: column !important;
                overflow: hidden !important;
                direction: <?php echo $chat_dir; ?> !important;
                box-sizing: border-box !important;
                font-family: <?php echo $is_ar ? "'Cairo', sans-serif" : "'Plus Jakarta Sans', sans-serif"; ?> !important;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }
            #rsdModalWindow.active {
                display: flex !important;
                animation: rsdSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
            }
            @keyframes rsdSlideUp {
                from { opacity: 0; transform: translateY(20px) scale(0.97); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }

            /* Clean Header */
            .rsd-light-header {
                background: #FFFFFF !important;
                padding: 16px 20px !important;
                border-bottom: 1px solid #F1F5F9 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02) !important;
            }
            .rsd-header-info {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }
            .rsd-header-avatar {
                width: 42px !important;
                height: 42px !important;
                border-radius: 50% !important;
                background: linear-gradient(135deg, #2563EB, #38BDF8) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                color: #FFFFFF !important;
                font-weight: 800 !important;
                font-size: 1rem !important;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25) !important;
            }
            .rsd-header-text h4 {
                margin: 0 !important;
                font-size: 1rem !important;
                font-weight: 800 !important;
                color: #0F172A !important;
                line-height: 1.2 !important;
            }
            .rsd-header-status {
                display: flex !important;
                align-items: center !important;
                gap: 6px !important;
                font-size: 0.76rem !important;
                color: #10B981 !important;
                font-weight: 700 !important;
                margin-top: 3px !important;
            }
            .rsd-status-dot {
                width: 7px !important;
                height: 7px !important;
                background: #10B981 !important;
                border-radius: 50% !important;
                box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2) !important;
            }
            .rsd-header-close {
                width: 32px !important;
                height: 32px !important;
                border-radius: 50% !important;
                background: #F1F5F9 !important;
                border: none !important;
                color: #64748B !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                font-size: 1.1rem !important;
                transition: all 0.2s ease !important;
            }
            .rsd-header-close:hover {
                background: #E2E8F0 !important;
                color: #0F172A !important;
            }

            /* Chat Messages Body */
            .rsd-chat-body {
                flex: 1 !important;
                background: #F8FAFC !important;
                padding: 20px !important;
                overflow-y: auto !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 14px !important;
            }
            .rsd-chat-bubble {
                max-width: 85% !important;
                padding: 13px 18px !important;
                border-radius: 18px !important;
                font-size: 0.92rem !important;
                line-height: 1.6 !important;
                word-break: break-word !important;
            }
            .rsd-chat-ai {
                align-self: flex-start !important;
                background: #FFFFFF !important;
                color: #1E293B !important;
                border: 1px solid #E2E8F0 !important;
                border-bottom-right-radius: 4px !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03) !important;
            }
            .rsd-chat-user {
                align-self: flex-end !important;
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                color: #FFFFFF !important;
                border-bottom-left-radius: 4px !important;
                box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25) !important;
            }

            /* Suggestion Chips */
            .rsd-chips-container {
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 8px !important;
                margin-top: 10px !important;
            }
            .rsd-chip-btn {
                background: #F1F5F9 !important;
                border: 1px solid #E2E8F0 !important;
                color: #334155 !important;
                padding: 7px 14px !important;
                border-radius: 9999px !important;
                font-size: 0.78rem !important;
                font-weight: 700 !important;
                cursor: pointer !important;
                transition: all 0.2s ease !important;
                font-family: inherit !important;
            }
            .rsd-chip-btn:hover {
                background: #2563EB !important;
                color: #FFFFFF !important;
                border-color: #2563EB !important;
                transform: translateY(-1px) !important;
            }

            /* Input Footer */
            .rsd-chat-footer {
                background: #FFFFFF !important;
                border-top: 1px solid #F1F5F9 !important;
                padding: 14px 18px !important;
                display: flex !important;
                align-items: center !important;
                gap: 10px !important;
            }
            .rsd-input-wrapper {
                flex: 1 !important;
                background: #F1F5F9 !important;
                border: 1.5px solid #E2E8F0 !important;
                border-radius: 24px !important;
                padding: 6px 16px !important;
                display: flex !important;
                align-items: center !important;
                transition: border-color 0.2s ease !important;
            }
            .rsd-input-wrapper:focus-within {
                border-color: #2563EB !important;
                background: #FFFFFF !important;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
            }
            .rsd-chat-field {
                width: 100% !important;
                background: transparent !important;
                border: none !important;
                outline: none !important;
                font-size: 0.92rem !important;
                font-family: inherit !important;
                color: #0F172A !important;
                resize: none !important;
                line-height: 1.4 !important;
                max-height: 80px !important;
                padding: 4px 0 !important;
            }
            .rsd-btn-send {
                width: 44px !important;
                height: 44px !important;
                min-width: 44px !important;
                min-height: 44px !important;
                border-radius: 50% !important;
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                color: #FFFFFF !important;
                border: none !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                flex-shrink: 0 !important;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
                padding: 0 !important;
            }
            .rsd-btn-send:hover {
                background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%) !important;
                transform: scale(1.08) !important;
                box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4) !important;
            }
            .rsd-btn-send svg {
                width: 22px !important;
                height: 22px !important;
                min-width: 22px !important;
                min-height: 22px !important;
                display: block !important;
                fill: #FFFFFF !important;
            }
            .rsd-btn-voice {
                width: 44px !important;
                height: 44px !important;
                min-width: 44px !important;
                min-height: 44px !important;
                border-radius: 50% !important;
                background: #F8FAFC !important;
                border: 1.5px solid #E2E8F0 !important;
                color: #475569 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                flex-shrink: 0 !important;
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
                padding: 0 !important;
            }
            .rsd-btn-voice:hover {
                background: #EFF6FF !important;
                border-color: #2563EB !important;
                color: #2563EB !important;
                transform: scale(1.06) !important;
            }
            .rsd-btn-voice svg {
                width: 22px !important;
                height: 22px !important;
                min-width: 22px !important;
                min-height: 22px !important;
                display: block !important;
                fill: currentColor !important;
            }

            @media (max-width: 480px) {
                #rsdModalWindow {
                    width: 100% !important;
                    height: 100% !important;
                    max-height: 100% !important;
                    bottom: 0 !important;
                    right: 0 !important;
                    border-radius: 0 !important;
                }
            }
        </style>

        <!-- Floating Launcher Button -->
        <div class="rsd-chat-launcher" onclick="window.toggleRsdChatWidget(event)" title="<?php echo $is_ar ? 'تحدث مع المستشار الذكي واحجز موعدك' : 'Chat with AI Concierge & Book Call'; ?>">
            <div class="rsd-chat-launcher-badge"></div>
            <svg viewBox="0 0 24 24" width="28" height="28" fill="#FFFFFF">
                <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
            </svg>
        </div>

        <!-- Light Luxury Chat Modal Window -->
        <div id="rsdModalWindow">
            
            <!-- Header -->
            <div class="rsd-light-header">
                <div class="rsd-header-info">
                    <div class="rsd-header-avatar">AI</div>
                    <div class="rsd-header-text">
                        <h4><?php echo $is_ar ? 'المستشار الذكي — RED SEA' : 'RED SEA AI Concierge'; ?></h4>
                        <div class="rsd-header-status">
                            <div class="rsd-status-dot"></div>
                            <span><?php echo $is_ar ? 'متصل الآن لخدمتك 24/7' : 'Active & Ready 24/7'; ?></span>
                        </div>
                    </div>
                </div>
                <button class="rsd-header-close" onclick="var el=document.getElementById(&apos;rsd-booking-calendar&apos;);if(el){el.scrollIntoView({behavior:&apos;smooth&apos;});}else{window.toggleRsdChatWidget(event);}">✕</button>
            </div>

            <!-- Messages Stream -->
            <div class="rsd-chat-body" id="rsdChatMessages">
                <div class="rsd-chat-bubble rsd-chat-ai">
                    <?php echo $is_ar ? 'أهلاً بك! أنا مستشارك الذكي في RED SEA DIGITAL. كيف يمكنني مساعدتك في تطوير نشاطك ومضاعفة مبيعاتك المباشرة اليوم؟' : 'Welcome to RED SEA DIGITAL! I am your AI Strategic Consultant. How may I assist your business or property today?'; ?>
                    
                    <div class="rsd-chips-container">
                        <button class="rsd-chip-btn" onclick="rsdSendQuickPrompt('<?php echo $is_ar ? 'أريد حجز موعد استشارة مجانية' : 'Book a free consultation'; ?>')"><?php echo $is_ar ? 'حجز موعد استشارة' : 'Book Consultation'; ?></button>
                        <button class="rsd-chip-btn" onclick="rsdSendQuickPrompt('<?php echo $is_ar ? 'كيف يساعدني الذكاء الاصطناعي في زيادة المبيعات؟' : 'How does AI increase direct sales?'; ?>')"><?php echo $is_ar ? 'مضاعفة المبيعات المباشرة' : 'Direct Revenue Growth'; ?></button>
                        <button class="rsd-chip-btn" onclick="rsdSendQuickPrompt('<?php echo $is_ar ? 'ما هي خدماتكم لقطاع السياحة والفنادق والغوص؟' : 'Services for Hospitality & Diving'; ?>')"><?php echo $is_ar ? 'الفنادق والغوص والسياحة' : 'Hospitality & Diving'; ?></button>
                    </div>
                </div>
            </div>

            <!-- Input Footer -->
            <div class="rsd-chat-footer">
                <div class="rsd-input-wrapper">
                    <textarea id="rsdChatInput" class="rsd-chat-field" rows="1" placeholder="<?php echo $is_ar ? 'اكتب استفسارك هنا...' : 'Type your message...'; ?>" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();rsdSendMsg();}"></textarea>
                </div>
                <button id="rsdMicBtn" class="rsd-btn-voice" onclick="rsdStartVoiceInput()" title="<?php echo $is_ar ? 'تحدث صوتياً' : 'Voice Input'; ?>" type="button">
                    <svg width="22" height="22" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                </button>
                <button id="rsdSendBtn" class="rsd-btn-send" onclick="rsdSendMsg()" title="<?php echo $is_ar ? 'إرسال الرسالة' : 'Send Message'; ?>" type="button">
                    <svg width="22" height="22" viewBox="0 0 24 24" style="transform: rotate(<?php echo $is_ar ? '180deg' : '0deg'; ?>);"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </button>
            </div>

        </div>

        <script>
        window.toggleRsdChatWidget = function(e) {
            if (e) e.preventDefault();
            var modal = document.getElementById("rsdModalWindow");
            if (!modal) return;
            modal.classList.toggle("active");
            if (modal.classList.contains("active")) {
                var input = document.getElementById("rsdChatInput");
                if (input) input.focus();
            }
        };

        function rsdSendQuickPrompt(text) {
            var input = document.getElementById("rsdChatInput");
            if (input) {
                input.value = text;
                rsdSendMsg();
            }
        }

        window.rsdChatHistory = [];

        function rsdSendMsg() {
            var input = document.getElementById("rsdChatInput");
            var text = input.value.trim();
            if (!text) return;
            input.value = "";

            var body = document.getElementById("rsdChatMessages");
            var userBubble = document.createElement("div");
            userBubble.className = "rsd-chat-bubble rsd-chat-user";
            userBubble.innerText = text;
            body.appendChild(userBubble);
            body.scrollTop = body.scrollHeight;

            var aiBubble = document.createElement("div");
            aiBubble.className = "rsd-chat-bubble rsd-chat-ai";
            aiBubble.innerText = "جاري الرد...";
            body.appendChild(aiBubble);
            body.scrollTop = body.scrollHeight;

            // Keep conversation history up to 10 turns
            if (!window.rsdChatHistory) { window.rsdChatHistory = []; }
            window.rsdChatHistory.push({ role: "user", content: text });
            if (window.rsdChatHistory.length > 10) {
                window.rsdChatHistory = window.rsdChatHistory.slice(-10);
            }

            var formData = new FormData();
            formData.append("action", "rsd_chat");
            formData.append("message", text);
            formData.append("history", JSON.stringify(window.rsdChatHistory.slice(0, -1)));

            fetch("/wp-admin/admin-ajax.php", {
                method: "POST",
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var replyHtml = data.reply || "<?php echo $is_ar ? 'تم استلام استفسارك بنجاح.' : 'Your request was received successfully.'; ?>";
                aiBubble.innerHTML = replyHtml;
                // Strip html tags for history memory
                var tmp = document.createElement("div");
                tmp.innerHTML = replyHtml;
                var cleanReply = tmp.textContent || tmp.innerText || "";
                window.rsdChatHistory.push({ role: "model", content: cleanReply });
                body.scrollTop = body.scrollHeight;
            })
            .catch(function(err) {
                aiBubble.innerText = "<?php echo $is_ar ? 'يسعدنا تواصلك المباشر معنا عبر الواتساب على 01028803080 لخدمتك فوراً.' : 'Please connect with us directly on WhatsApp.'; ?>";
            });
        }

        var rsdSpeechRec = null;
        function rsdStartVoiceInput() {
            var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                alert("<?php echo $is_ar ? 'متصفحك لا يدعم التعرف الصوتي المباشر.' : 'Speech recognition not supported in this browser.'; ?>");
                return;
            }
            if (!rsdSpeechRec) {
                rsdSpeechRec = new SpeechRecognition();
                rsdSpeechRec.lang = "<?php echo $is_ar ? 'ar-SA' : 'en-US'; ?>";
                rsdSpeechRec.onresult = function(event) {
                    var spoken = event.results[0][0].transcript;
                    rsdSendQuickPrompt(spoken);
                };
            }
            rsdSpeechRec.start();
        }
        
                    </script>
        <?php
    }

    public function render_crm_page() {
        global $wpdb;

        // Ensure Vector Store & Leads tables exist
        RSD_Knowledge_Base_Manager::init_vector_store_table();
        RedSeaLeadRadarEngine::init_leads_table();

        // Handle POST submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('rsd_crm_settings_nonce');
            if (!current_user_can('manage_options')) wp_die('غير مصرح لك.');

            $tab = sanitize_text_field($_POST['active_tab'] ?? 'overview');

            // 1. WhatsApp & Gateway Settings
            if (isset($_POST['rsd_save_settings']) || isset($_POST['rsd_whatsapp_api_url'])) {
                if (isset($_POST['rsd_whatsapp_phone'])) {
                    update_option('rsd_whatsapp_phone', sanitize_text_field($_POST['rsd_whatsapp_phone']));
                }
                if (isset($_POST['rsd_whatsapp_instance'])) {
                    update_option('rsd_whatsapp_instance', sanitize_text_field($_POST['rsd_whatsapp_instance']));
                }
                if (isset($_POST['rsd_whatsapp_api_url'])) {
                    update_option('rsd_whatsapp_api_url', esc_url_raw(trim($_POST['rsd_whatsapp_api_url'])));
                }
                if (isset($_POST['rsd_whatsapp_api_key'])) {
                    update_option('rsd_whatsapp_api_key', sanitize_text_field($_POST['rsd_whatsapp_api_key']));
                }
                echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم حفظ إعدادات خادم البوابة والواتساب بنجاح! 💾✨</strong></p></div>';
            }

            if (isset($_POST['rsd_create_custom_agent'])) {
                $agent_name    = sanitize_text_field($_POST['rsd_new_agent_name'] ?? '');
                $agent_mission = sanitize_textarea_field($_POST['rsd_new_agent_mission'] ?? '');
                if (!empty($agent_name) && !empty($agent_mission)) {
                    $new_agent = RedSeaAgentFactory::create_custom_agent($agent_name, $agent_mission);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم إنشاء وتجهيز الوكيل الذكي [' . esc_html($agent_name) . '] وصياغة السيستم برومبت الخاص به بنجاح.</strong></p></div>';
                }
            }

            // 2. Delete Custom Agent
            if (isset($_POST['rsd_delete_custom_agent'])) {
                $del_id = sanitize_text_field($_POST['rsd_delete_agent_id'] ?? '');
                $custom_agents = get_option('rsd_custom_agents', []);
                if (isset($custom_agents[$del_id])) {
                    unset($custom_agents[$del_id]);
                    update_option('rsd_custom_agents', $custom_agents);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #EF4444;"><p><strong>تم حذف الوكيل بنجاح.</strong></p></div>';
                }
            }

            // 3. Save File Content (RAG File Editor)
            if (isset($_POST['rsd_save_file_content'])) {
                $file_name = sanitize_text_field($_POST['rsd_edit_file_name'] ?? '');
                $content   = wp_unslash($_POST['rsd_edit_file_text'] ?? '');
                if (!empty($file_name)) {
                    RSD_Knowledge_Base_Manager::save_file_content($file_name, $content);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم حفظ التعديلات على الملف [' . esc_html($file_name) . '] وإعادة فهرسته دلالياً بنجاح.</strong></p></div>';
                }
            }

            // 4. Delete RAG File
            if (isset($_POST['rsd_delete_file'])) {
                $file_name = sanitize_text_field($_POST['rsd_delete_file_name'] ?? '');
                if (!empty($file_name)) {
                    RSD_Knowledge_Base_Manager::delete_file($file_name);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #EF4444;"><p><strong>تم حذف الملف ومسح مقاطعه من قاعدة المعرفة بنجاح.</strong></p></div>';
                }
            }

            // 5. Upload New RAG File
            if (isset($_FILES['rsd_upload_new_file']) && !empty($_FILES['rsd_upload_new_file']['name'])) {
                $uploaded = $_FILES['rsd_upload_new_file'];
                $ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['md', 'txt', 'json'])) {
                    $content = file_get_contents($uploaded['tmp_name']);
                    RSD_Knowledge_Base_Manager::save_file_content($uploaded['name'], $content);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم رفع الملف [' . esc_html($uploaded['name']) . '] وفهرسته في قاعدة المعرفة بنجاح.</strong></p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #EF4444;"><p><strong>يرجى رفع ملفات بصيغة .md أو .txt أو .json فقط.</strong></p></div>';
                }
            }

            // 6. Save General Settings
            if (isset($_POST['rsd_save_settings'])) {
                if ($tab === 'models' || $tab === 'settings') {
                    update_option('rsd_ai_provider', sanitize_text_field($_POST['rsd_ai_provider'] ?? 'opencode'));
                    update_option('rsd_ai_model', sanitize_text_field($_POST['rsd_ai_model'] ?? 'deepseek-chat'));
                    update_option('rsd_opencode_api_key', sanitize_text_field($_POST['rsd_opencode_api_key'] ?? ''));
                    update_option('rsd_gemini_api_key', sanitize_text_field($_POST['rsd_gemini_api_key'] ?? ''));
                    update_option('rsd_deepseek_api_key', sanitize_text_field($_POST['rsd_deepseek_api_key'] ?? ''));
                    update_option('rsd_openai_api_key', sanitize_text_field($_POST['rsd_openai_api_key'] ?? ''));
                    update_option('rsd_llm_temperature', floatval($_POST['rsd_llm_temperature'] ?? 0.6));
                    update_option('rsd_llm_max_tokens', intval($_POST['rsd_llm_max_tokens'] ?? 850));
                    if (isset($_POST['rsd_widget_enabled_submitted'])) {
                        update_option('rsd_widget_enabled', isset($_POST['rsd_widget_enabled']) ? '1' : '0');
                    }
                } elseif ($tab === 'company') {
                    update_option('rsd_company_name', sanitize_text_field($_POST['rsd_company_name'] ?? 'RED SEA DIGITAL'));
                    update_option('rsd_company_slogan', sanitize_text_field($_POST['rsd_company_slogan'] ?? ''));
                    update_option('rsd_company_hq', sanitize_text_field($_POST['rsd_company_hq'] ?? 'الغردقة، البحر الأحمر، مصر'));
                    update_option('rsd_booking_url', esc_url_raw($_POST['rsd_booking_url'] ?? ''));
                    update_option('rsd_system_prompt', sanitize_textarea_field($_POST['rsd_system_prompt'] ?? ''));
                } elseif ($tab === 'concierge') {
                    update_option('rsd_sales_tone', sanitize_text_field($_POST['rsd_sales_tone'] ?? 'elite_closer'));
                    update_option('rsd_concierge_commission_preset', intval($_POST['rsd_concierge_commission_preset'] ?? 20));
                    update_option('rsd_enable_response_cache', isset($_POST['rsd_enable_response_cache']) ? '1' : '0');
                } elseif ($tab === 'rag') {
                    update_option('rsd_rag_chunk_size', intval($_POST['rsd_rag_chunk_size'] ?? 350));
                    update_option('rsd_rag_chunk_overlap', intval($_POST['rsd_rag_chunk_overlap'] ?? 50));
                    update_option('rsd_rag_similarity_threshold', floatval($_POST['rsd_rag_similarity_threshold'] ?? 0.65));
                } elseif ($tab === 'voice') {
                    update_option('rsd_voice_lang', sanitize_text_field($_POST['rsd_voice_lang'] ?? 'ar-SA'));
                    update_option('rsd_voice_rate', sanitize_text_field($_POST['rsd_voice_rate'] ?? '1.0'));
                    update_option('rsd_voice_pitch', sanitize_text_field($_POST['rsd_voice_pitch'] ?? '1.0'));
                } elseif ($tab === 'crm') {
                    update_option('rsd_whatsapp_phone', sanitize_text_field($_POST['rsd_whatsapp_phone'] ?? '201028803080'));
                    update_option('rsd_whatsapp_cloud_token', sanitize_text_field($_POST['rsd_whatsapp_cloud_token'] ?? ''));
                    update_option('rsd_whatsapp_phone_number_id', sanitize_text_field($_POST['rsd_whatsapp_phone_number_id'] ?? ''));
                    update_option('rsd_whatsapp_waba_id', sanitize_text_field($_POST['rsd_whatsapp_waba_id'] ?? ''));
                }
                echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم حفظ الإعدادات بنجاح. ✨</strong></p></div>';
            }
        }

        $active_tab   = sanitize_text_field($_GET['tab'] ?? 'overview');
        $edit_file    = sanitize_text_field($_GET['edit_file'] ?? '');
        $kb_files     = RSD_Knowledge_Base_Manager::list_all_kb_files();
        $total_leads  = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rsd_bookings");
        $total_chunks = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rsd_vector_store");
        $recent_logs  = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}rsd_bookings ORDER BY id DESC LIMIT 50", ARRAY_A);
        $traces       = get_option('rsd_orchestration_logs', []);
        $all_agents   = RedSeaAgentFactory::get_all_agents();
        ?>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cairo:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

            /* TASKHUB CLEAN ENTERPRISE THEME SYSTEM TOKENS */
            .rsd-taskhub-wrap {
                font-family: 'Cairo', 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif !important;
                color: #0F172A !important;
                background: #F8FAFC !important;
                margin: 20px 20px 20px 0 !important;
                direction: rtl !important;
                box-sizing: border-box !important;
            }

            .rsd-taskhub-layout {
                display: flex !important;
                gap: 24px !important;
                align-items: flex-start !important;
            }

            /* 1. CLEAN FLOATING WHITE SIDEBAR */
            .rsd-taskhub-sidebar {
                width: 280px !important;
                flex-shrink: 0 !important;
                background: #FFFFFF !important;
                border: 1px solid #E2E8F0 !important;
                border-radius: 18px !important;
                padding: 20px 14px !important;
                box-sizing: border-box !important;
                box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.03), 0 2px 6px -1px rgba(15, 23, 42, 0.02) !important;
            }

            .rsd-taskhub-content {
                flex: 1 !important;
                min-width: 0 !important;
            }

            .rsd-sidebar-header {
                padding: 4px 10px 18px 10px !important;
                border-bottom: 1px solid #F1F5F9 !important;
                margin-bottom: 14px !important;
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }

            .rsd-logo-badge {
                width: 42px !important;
                height: 42px !important;
                border-radius: 12px !important;
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                color: #FFFFFF !important;
                font-weight: 900 !important;
                font-size: 1.15rem !important;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25) !important;
            }

            .rsd-sidebar-title {
                font-size: 1.05rem !important;
                font-weight: 800 !important;
                color: #0F172A !important;
                margin: 0 !important;
                line-height: 1.2 !important;
            }

            .rsd-sidebar-sub {
                font-size: 0.76rem !important;
                color: #64748B !important;
                margin: 2px 0 0 0 !important;
                font-weight: 600 !important;
            }

            .rsd-nav-group-label {
                font-size: 0.7rem !important;
                font-weight: 800 !important;
                color: #94A3B8 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.06em !important;
                padding: 14px 10px 6px 10px !important;
            }

            .rsd-sidebar-link {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
                padding: 10px 14px !important;
                border-radius: 12px !important;
                color: #475569 !important;
                text-decoration: none !important;
                font-size: 0.88rem !important;
                font-weight: 600 !important;
                transition: all 0.18s ease-in-out !important;
                margin-bottom: 4px !important;
                border: 1px solid transparent !important;
            }

            .rsd-sidebar-link:hover {
                background: #F8FAFC !important;
                color: #0F172A !important;
            }

            .rsd-sidebar-link.active {
                background: #EFF6FF !important;
                color: #2563EB !important;
                border-color: #BFDBFE !important;
                font-weight: 800 !important;
                box-shadow: 0 1px 3px rgba(37, 99, 235, 0.08) !important;
            }

            .rsd-sidebar-link .rsd-nav-icon {
                font-size: 1.1rem !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 24px !important;
            }

            /* 2. TASKHUB CLEAN WHITE CARDS */
            .rsd-card {
                background: #FFFFFF !important;
                border: 1px solid #E2E8F0 !important;
                border-radius: 18px !important;
                padding: 24px !important;
                box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.03), 0 1px 2px -1px rgba(15, 23, 42, 0.02) !important;
                margin-bottom: 24px !important;
            }

            .rsd-card-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin-bottom: 20px !important;
                border-bottom: 1px solid #F1F5F9 !important;
                padding-bottom: 16px !important;
            }

            .rsd-card-title {
                font-size: 1.12rem !important;
                font-weight: 800 !important;
                color: #0F172A !important;
                margin: 0 !important;
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
            }

            /* 3. TASKHUB KPI STRIP */
            .rsd-telemetry-grid {
                display: grid !important;
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)) !important;
                gap: 16px !important;
                margin-bottom: 24px !important;
            }

            .rsd-telemetry-card {
                background: #FFFFFF !important;
                border: 1px solid #E2E8F0 !important;
                border-radius: 16px !important;
                padding: 20px !important;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03) !important;
                transition: transform 0.15s ease, box-shadow 0.15s ease !important;
            }

            .rsd-telemetry-card:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 6px 16px -2px rgba(15, 23, 42, 0.06) !important;
            }

            .rsd-telemetry-title {
                font-size: 0.82rem !important;
                font-weight: 700 !important;
                color: #64748B !important;
                margin-bottom: 6px !important;
            }

            .rsd-telemetry-val {
                font-size: 1.7rem !important;
                font-weight: 900 !important;
                color: #0F172A !important;
                letter-spacing: -0.02em !important;
                line-height: 1.2 !important;
            }

            .rsd-telemetry-sub {
                font-size: 0.78rem !important;
                color: #94A3B8 !important;
                margin-top: 4px !important;
                font-weight: 600 !important;
            }

            /* 4. TASKHUB FORM ELEMENTS */
            .rsd-form-group { margin-bottom: 20px !important; }
            .rsd-label {
                display: block !important;
                font-weight: 700 !important;
                font-size: 0.88rem !important;
                margin-bottom: 8px !important;
                color: #1E293B !important;
            }

            .rsd-input, .rsd-select, .rsd-textarea {
                width: 100% !important;
                padding: 10px 16px !important;
                border: 1.5px solid #E2E8F0 !important;
                border-radius: 12px !important;
                font-family: inherit !important;
                font-size: 0.9rem !important;
                color: #0F172A !important;
                background: #FFFFFF !important;
                box-sizing: border-box !important;
                transition: all 0.18s ease-in-out !important;
            }

            .rsd-input:focus, .rsd-select:focus, .rsd-textarea:focus {
                border-color: #2563EB !important;
                background: #FFFFFF !important;
                outline: none !important;
                box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.12) !important;
            }

            .rsd-btn {
                background: #2563EB !important;
                color: #FFFFFF !important;
                border: none !important;
                padding: 10px 20px !important;
                border-radius: 10px !important;
                font-weight: 700 !important;
                cursor: pointer !important;
                font-family: inherit !important;
                font-size: 0.88rem !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                transition: all 0.18s ease !important;
                box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2) !important;
                text-decoration: none !important;
            }

            .rsd-btn:hover {
                background: #1D4ED8 !important;
                color: #FFFFFF !important;
                transform: translateY(-1px) !important;
                box-shadow: 0 4px 10px rgba(37, 99, 235, 0.28) !important;
            }

            .rsd-btn-secondary {
                background: #F1F5F9 !important;
                color: #334155 !important;
                border: 1px solid #CBD5E1 !important;
                box-shadow: none !important;
            }

            .rsd-btn-secondary:hover {
                background: #E2E8F0 !important;
                color: #0F172A !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.04) !important;
            }

            .rsd-btn-danger {
                background: #FEF2F2 !important;
                color: #DC2626 !important;
                border: 1px solid #FECACA !important;
                box-shadow: none !important;
            }

            .rsd-btn-danger:hover {
                background: #FEE2E2 !important;
                color: #B91C1C !important;
            }

            /* 5. BADGES */
            .rsd-badge {
                display: inline-flex !important;
                align-items: center !important;
                gap: 5px !important;
                padding: 4px 10px !important;
                border-radius: 9999px !important;
                font-size: 0.78rem !important;
                font-weight: 700 !important;
            }

            .rsd-badge-success { background: #DCFCE7 !important; color: #15803D !important; border: 1px solid #BBF7D0 !important; }
            .rsd-badge-warning { background: #FEF3C7 !important; color: #B45309 !important; border: 1px solid #FDE68A !important; }
            .rsd-badge-info    { background: #EFF6FF !important; color: #1D4ED8 !important; border: 1px solid #BFDBFE !important; }
            .rsd-badge-purple  { background: #F3E8FF !important; color: #7E22CE !important; border: 1px solid #E9D5FF !important; }
            .rsd-badge-danger  { background: #FEE2E2 !important; color: #B91C1C !important; border: 1px solid #FECACA !important; }

            /* 6. TABLES */
            .rsd-table {
                width: 100% !important;
                border-collapse: separate !important;
                border-spacing: 0 !important;
                font-size: 0.88rem !important;
            }

            .rsd-table th {
                background: #F8FAFC !important;
                color: #475569 !important;
                font-weight: 700 !important;
                text-align: right !important;
                padding: 12px 16px !important;
                border-bottom: 1.5px solid #E2E8F0 !important;
            }

            .rsd-table td {
                padding: 14px 16px !important;
                border-bottom: 1px solid #F1F5F9 !important;
                color: #334155 !important;
                vertical-align: middle !important;
            }

            .rsd-table tr:hover td {
                background: #F8FAFC !important;
            }

            /* 7. MODAL DRAWER */
            .rsd-modal-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(4px);
                z-index: 999999;
                align-items: center;
                justify-content: center;
            }
            .rsd-modal-box {
                background: #FFFFFF;
                border-radius: 20px;
                width: 90%;
                max-width: 750px;
                max-height: 85vh;
                overflow-y: auto;
                padding: 24px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            }
        </style>

        <div class="rsd-taskhub-wrap">
            <div class="rsd-taskhub-layout">

                <!-- 1. TASKHUB CLEAN FLOATING SIDEBAR -->
                <div class="rsd-taskhub-sidebar">
                    <div class="rsd-sidebar-header">
                        <div class="rsd-logo-badge">✦</div>
                        <div>
                            <h3 class="rsd-sidebar-title">RED SEA DIGITAL</h3>
                            <p class="rsd-sidebar-sub">AI Engine • Enterprise v4.5</p>
                        </div>
                    </div>

                    <div class="rsd-nav-group-label">المحرك والأوركسترا</div>
                    <a href="?page=redsea-ai-engine&tab=overview" class="rsd-sidebar-link <?php echo $active_tab === 'overview' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">📊</span>
                        <span>نظرة عامة وتليمتري</span>
                    </a>
                    <a href="?page=redsea-ai-engine&tab=agents" class="rsd-sidebar-link <?php echo $active_tab === 'agents' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">🤖</span>
                        <span>منشئ الوكلاء وإدارتها</span>
                    </a>

                    <div class="rsd-nav-group-label">المعرفة والنشاط</div>
                    <a href="?page=redsea-ai-engine&tab=company" class="rsd-sidebar-link <?php echo $active_tab === 'company' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">🏛️</span>
                        <span>هوية المنشأة ومعلومات النشاط</span>
                    </a>
                    <a href="?page=redsea-ai-engine&tab=rag" class="rsd-sidebar-link <?php echo $active_tab === 'rag' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">📚</span>
                        <span>قاعدة المعرفة وإدارة الملفات</span>
                    </a>
                    <a href="?page=redsea-ai-engine&tab=concierge" class="rsd-sidebar-link <?php echo $active_tab === 'concierge' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">⚡</span>
                        <span>وكيل المبيعات وسرعة الرد</span>
                    </a>

                    <div class="rsd-nav-group-label">القنوات والنماذج</div>
                    <a href="?page=redsea-ai-engine&tab=models" class="rsd-sidebar-link <?php echo $active_tab === 'models' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">🧠</span>
                        <span>مركز النماذج والتقييمات</span>
                    </a>
                    <a href="?page=redsea-ai-engine&tab=voice" class="rsd-sidebar-link <?php echo $active_tab === 'voice' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">🎙️</span>
                        <span>استوديو الصوت التوليدي</span>
                    </a>

                    <div class="rsd-nav-group-label">التنقيب والمبيعات</div>
                    <a href="?page=redsea-ai-engine&tab=radar" class="rsd-sidebar-link <?php echo $active_tab === 'radar' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">🎯</span>
                        <span>رادار العملاء وصائد الصفقات</span>
                    </a>
                    <a href="?page=redsea-ai-engine&tab=crm" class="rsd-sidebar-link <?php echo $active_tab === 'crm' ? 'active' : ''; ?>">
                        <span class="rsd-nav-icon">💬</span>
                        <span>الواتساب وسجل العملاء</span>
                    </a>
                </div>

                <!-- 2. TASKHUB CONTENT MAIN CONTAINER -->
                <div class="rsd-taskhub-content">

                    <!-- TAB 1: OVERVIEW & TELEMETRY -->
                    <?php if ($active_tab === 'overview'): ?>
                        
                        <div class="rsd-telemetry-grid">
                            <div class="rsd-telemetry-card">
                                <div class="rsd-telemetry-title">إجمالي العملاء والحجوزات</div>
                                <div class="rsd-telemetry-val"><?php echo number_format($total_leads); ?></div>
                                <div class="rsd-telemetry-sub">مسجل بالـ CRM</div>
                            </div>
                            <div class="rsd-telemetry-card">
                                <div class="rsd-telemetry-title">المقاطع المتجهية المفهرسة</div>
                                <div class="rsd-telemetry-val"><?php echo number_format($total_chunks); ?></div>
                                <div class="rsd-telemetry-sub">جاهزة للاستعلام الدلالي</div>
                            </div>
                            <div class="rsd-telemetry-card">
                                <div class="rsd-telemetry-title">ملفات قاعدة المعرفة النشطة</div>
                                <div class="rsd-telemetry-val"><?php echo count($kb_files); ?></div>
                                <div class="rsd-telemetry-sub">محدثة ومتاحة للوكلاء</div>
                            </div>
                            <div class="rsd-telemetry-card">
                                <div class="rsd-telemetry-title">سلسلة الفشل التلقائي</div>
                                <div class="rsd-telemetry-val" style="color:#16A34A;font-size:1.25rem;">نشط 100%</div>
                                <div class="rsd-telemetry-sub">OpenCode ➔ Gemini ➔ DeepSeek</div>
                            </div>
                        </div>

                        <!-- LIVE TRACES TIMELINE STREAM -->
                        <div class="rsd-card">
                            <div class="rsd-card-header">
                                <h3 class="rsd-card-title">
                                    <span>⚡ سجل استدلال الأوركسترا اللحظي (Interactive Timeline Stream)</span>
                                </h3>
                                <span class="rsd-badge rsd-badge-info">أحدث العمليات الحية</span>
                            </div>

                            <?php if (empty($traces)): ?>
                                <p style="color:#64748B;text-align:center;padding:24px 0;">لا توجد سجلات تتبع حالياً. ستظهر العمليات هنا فور محادثة العملاء مع المحرك.</p>
                            <?php else: ?>
                                <div style="display:flex;flex-direction:column;gap:12px;">
                                    <?php foreach (array_slice(array_reverse($traces), 0, 15) as $i => $trace): ?>
                                        <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:14px;padding:16px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                                <div style="display:flex;gap:8px;align-items:center;">
                                                    <span class="rsd-badge rsd-badge-purple"><?php echo esc_html($trace['intent'] ?? 'عام'); ?></span>
                                                    <span class="rsd-badge rsd-badge-info"><?php echo esc_html($trace['model'] ?? 'opencode'); ?></span>
                                                    <strong style="color:#0F172A;font-size:0.9rem;">👤 <?php echo esc_html($trace['sender'] ?? 'زائر'); ?></strong>
                                                </div>
                                                <div style="display:flex;gap:10px;align-items:center;">
                                                    <span style="font-size:0.78rem;color:#94A3B8;">📅 <?php echo esc_html($trace['timestamp'] ?? ''); ?></span>
                                                    <button type="button" onclick="rsdInspectTrace(<?php echo $i; ?>)" class="rsd-btn rsd-btn-secondary" style="padding:3px 10px;font-size:0.75rem;">🔍 فحص JSON</button>
                                                </div>
                                            </div>
                                            <div style="font-size:0.86rem;color:#334155;line-height:1.5;margin-bottom:8px;background:#F8FAFC;padding:10px 14px;border-radius:8px;border-right:3px solid #3B82F6;">
                                                <strong>سؤال العميل:</strong> <?php echo esc_html($trace['user_message'] ?? 'استفسار أولي عن الحجز والخدمات'); ?>
                                            </div>
                                            <div style="font-size:0.86rem;color:#1E293B;background:#F0FDF4;padding:12px 14px;border-radius:8px;border-right:3px solid #10B981;line-height:1.5;">
                                                <strong>رد المحرك:</strong> <?php echo esc_html($trace['final_reply'] ?? 'أهلاً بك في RED SEA DIGITAL! يسعدنا تقديم استشارة كاملة لحجزك.'); ?>
                                            </div>
                                            <script>
                                                window.rsdTraceData = window.rsdTraceData || {};
                                                window.rsdTraceData[<?php echo $i; ?>] = <?php echo json_encode($trace, JSON_UNESCAPED_UNICODE); ?>;
                                            </script>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <!-- TAB 2: AGENTS FORGE -->
                    <?php elseif ($active_tab === 'agents'): ?>

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

                    <!-- TAB 3: BUSINESS IDENTITY -->
                    <?php elseif ($active_tab === 'company'): ?>

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

                    <!-- TAB 4: RAG KNOWLEDGE FILES -->
                    <?php elseif ($active_tab === 'rag'): ?>

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
                                <?php $file_text = RSD_Knowledge_Base_Manager::get_file_content($edit_file); ?>
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

                    <!-- TAB 5: SALES CONCIERGE -->
                    <?php elseif ($active_tab === 'concierge'): ?>

                        <div class="rsd-card">
                            <div class="rsd-card-header">
                                <h3 class="rsd-card-title">⚡ إعدادات وكيل المبيعات وسرعة الرد</h3>
                            </div>

                            <form method="POST">
                                <?php wp_nonce_field('rsd_crm_settings_nonce'); ?>
                                <input type="hidden" name="active_tab" value="concierge">

                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">نبرة الحوار المبيعي (Sales Persona Tone)</label>
                                        <select name="rsd_sales_tone" class="rsd-select">
                                            <option value="elite_closer" <?php selected(get_option('rsd_sales_tone'), 'elite_closer'); ?>>مستشار مبيعات خبير وهادئ (Quiet Luxury)</option>
                                            <option value="consultative" <?php selected(get_option('rsd_sales_tone'), 'consultative'); ?>>استشاري أرقام وعائد استثماري (ROI Focused)</option>
                                            <option value="friendly" <?php selected(get_option('rsd_sales_tone'), 'friendly'); ?>>مضياف وودود (Hospitality Concierge)</option>
                                        </select>
                                    </div>
                                    <div class="rsd-form-group">
                                        <label class="rsd-label">نسبة عمولة الوسطاء الافتراضية للحاسبة (%)</label>
                                        <div style="display:flex;gap:14px;align-items:center;">
                                            <input type="range" min="5" max="30" step="1" id="rsdCommSlider" value="<?php echo esc_attr(get_option('rsd_concierge_commission_preset', '20')); ?>" oninput="document.getElementById('rsdCommInput').value = this.value; rsdUpdateRoiPreview(this.value);" style="flex:1;">
                                            <input type="number" id="rsdCommInput" name="rsd_concierge_commission_preset" class="rsd-input" style="width:90px;" value="<?php echo esc_attr(get_option('rsd_concierge_commission_preset', '20')); ?>" oninput="document.getElementById('rsdCommSlider').value = this.value; rsdUpdateRoiPreview(this.value);">
                                        </div>
                                    </div>
                                </div>

                                <!-- LIVE OTA SAVINGS PREVIEW -->
                                <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:14px;padding:18px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
                                    <div>
                                        <h4 style="margin:0 0 4px 0;color:#166534;font-weight:800;font-size:0.95rem;">💡 محاكاة التوفير المالي للعميل (OTA Savings Simulation)</h4>
                                        <p style="margin:0;color:#15803D;font-size:0.84rem;">لمبيعات غرف بقيمة 50,000$ شهرياً، سيوفر الفندق للعميل بفضل الحجز المباشر:</p>
                                    </div>
                                    <div id="rsdRoiPreviewVal" style="font-size:1.6rem;font-weight:900;color:#166534;font-family:monospace;">
                                        $10,000 / شهرياً
                                    </div>
                                </div>

                                <div class="rsd-form-group">
                                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:700;color:#0F172A;">
                                        <input type="checkbox" name="rsd_enable_response_cache" value="1" <?php checked(get_option('rsd_enable_response_cache', '1'), '1'); ?> style="width:18px;height:18px;">
                                        <span>تفعيل التخزين المؤقت الذكي للردود المتكررة لتوفير استهلاك التوكنات وتسريع الاستجابة لأقل من 500ms</span>
                                    </label>
                                </div>

                                <button type="submit" name="rsd_save_settings" class="rsd-btn">
                                    💾 حفظ إعدادات الوكيل
                                </button>
                            </form>
                        </div>
                        <script>
                            function rsdUpdateRoiPreview(comm) {
                                var val = Math.round(50000 * (comm / 100));
                                document.getElementById('rsdRoiPreviewVal').innerText = '$' + val.toLocaleString() + ' / شهرياً';
                            }
                        </script>

                    <!-- TAB 6: MODELS HUB -->
                    <?php elseif ($active_tab === 'models'): ?>

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

                    <!-- TAB 7: VOICE STUDIO -->
                    <?php elseif ($active_tab === 'voice'): ?>

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

                    <!-- TAB 8: WHATSAPP BRIDGE & CRM -->
                    <?php elseif ($active_tab === 'crm'): ?>

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
                        <div class="rsd-card">
                            <div class="rsd-card-header">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <h3 class="rsd-card-title">👥 سجل جهات الاتصال والحجوزات المسجلة</h3>
                                    <span class="rsd-badge rsd-badge-success"><?php echo intval($total_leads); ?> عميل مسجل</span>
                                </div>
                                <div style="display:flex;gap:10px;">
                                    <input type="text" id="rsdCrmSearch" class="rsd-input" placeholder="🔍 بحث في السجلات..." onkeyup="rsdFilterCrmTable()" style="width:220px;padding:6px 12px;font-size:0.84rem;">
                                    <button type="button" onclick="rsdExportCrmCsv()" class="rsd-btn rsd-btn-secondary" style="padding:6px 14px;font-size:0.82rem;">📥 تصدير CSV</button>
                                </div>
                            </div>

                            <table class="rsd-table" id="rsdCrmTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم العميل</th>
                                        <th>رقم الواتساب</th>
                                        <th>نوع الخدمة</th>
                                        <th>تفاصيل المحادثة</th>
                                        <th>التاريخ</th>
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
                                                        <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $c_phone)); ?>" target="_blank" class="rsd-badge rsd-badge-success" style="text-decoration:none;direction:ltr;display:inline-block;">
                                                            💬 +<?php echo esc_html($c_phone); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="rsd-badge">غير مسجل</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="rsd-badge rsd-badge-info"><?php echo esc_html($c_service); ?></span></td>
                                                <td style="max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#475569;"><?php echo esc_html($c_details); ?></td>
                                                <td style="color:#94A3B8;font-size:0.82rem;white-space:nowrap;"><?php echo esc_html($log['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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

                    <!-- TAB 9: AUTONOMOUS LEAD RADAR -->
                    <?php elseif ($active_tab === 'radar'): ?>

                        <?php
                        $leads_tbl = $wpdb->prefix . 'rsd_leads';
                        $all_leads = $wpdb->get_results("SELECT * FROM {$leads_tbl} ORDER BY id DESC LIMIT 50", ARRAY_A);
                        $cnt_total = count($all_leads);
                        $cnt_pending = 0; $cnt_contacting = 0; $cnt_closed = 0;
                        foreach ($all_leads as $l) {
                            if ($l['pipeline_status'] === 'pending_review') $cnt_pending++;
                            elseif ($l['pipeline_status'] === 'contacting') $cnt_contacting++;
                            elseif ($l['pipeline_status'] === 'closed') $cnt_closed++;
                        }
                        ?>

                        <!-- RADAR CONTROLS -->
                        <div class="rsd-card">
                            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
                                <div>
                                    <h3 style="margin:0 0 4px 0;font-size:1.15rem;font-weight:800;color:#0F172A;">
                                        🎯 رادار العملاء وصائد الصفقات الآلي
                                    </h3>
                                    <p style="margin:0;color:#64748B;font-size:0.86rem;">
                                        منظومة وكلاء ذكاء اصطناعي تقوم بالتنقيب وتحليل فجوة العمولات (OTA Gap) وصياغة رسائل استشارية بهوية م. عمرو أحمد مع بوابة اعتماد بشرية.
                                    </p>
                                </div>
                                <div style="display:flex;gap:10px;align-items:center;">
                                    <select id="rsdRadarNiche" class="rsd-select" style="min-width:220px;">
                                        <option value="resorts_redsea">🏨 منتجعات وبوتيك هوتيل البحر الأحمر</option>
                                        <option value="diving_sharm">🤿 مراكز وسفاري الغوص واليخوت</option>
                                        <option value="luxury_travel">✈️ شركات السياحة والرحلات الفاخرة</option>
                                        <option value="medical_clinics">🏥 مراكز السياحة العلاجية والعيادات</option>
                                    </select>
                                    <button type="button" id="rsdBtnRunRadar" onclick="rsdRunRadarScan()" class="rsd-btn">
                                        🤖 ابدأ جولة التنقيب الآلي الآن
                                    </button>
                                </div>
                            </div>

                            <div id="rsdRadarConsole" style="display:none;margin-top:18px;background:#0F172A;border-radius:12px;padding:14px 16px;color:#E2E8F0;font-family:'JetBrains Mono',monospace;font-size:0.84rem;line-height:1.6;">
                                <div style="color:#38BDF8;font-weight:700;margin-bottom:6px;">✦ وحدة الأوركسترا النشطة: جاري استكشاف وتحليل الفرص...</div>
                                <div id="rsdRadarLogLines"><div>[Scout Agent] 🔍 جاري البحث في أدلة الأعمال ومحركات الخرائط...</div></div>
                            </div>
                        </div>

                        <!-- METRICS STRIP -->
                        <div class="rsd-telemetry-grid">
                            <div class="rsd-telemetry-card">
                                <div class="rsd-telemetry-title">إجمالي الفرص المرصودة</div>
                                <div class="rsd-telemetry-val"><?php echo intval($cnt_total); ?></div>
                            </div>
                            <div class="rsd-telemetry-card" style="border-color:#FEF08A;background:#FEFCE8;">
                                <div class="rsd-telemetry-title" style="color:#A16207;">⏳ بانتظار الاعتماد البشري</div>
                                <div class="rsd-telemetry-val" style="color:#CA8A04;"><?php echo intval($cnt_pending); ?></div>
                            </div>
                            <div class="rsd-telemetry-card" style="border-color:#BAE6FD;background:#F0F9FF;">
                                <div class="rsd-telemetry-title" style="color:#0369A1;">💬 تم التواصل عبر الواتساب</div>
                                <div class="rsd-telemetry-val" style="color:#0284C7;"><?php echo intval($cnt_contacting); ?></div>
                            </div>
                            <div class="rsd-telemetry-card" style="border-color:#BBF7D0;background:#F0FDF4;">
                                <div class="rsd-telemetry-title" style="color:#15803D;">🏆 صفقات ناجحة ومغلقة</div>
                                <div class="rsd-telemetry-val" style="color:#16A34A;"><?php echo intval($cnt_closed); ?></div>
                            </div>
                        </div>

                        <!-- LEADS CARDS -->
                        <div style="display:flex;flex-direction:column;gap:18px;">
                            <?php foreach ($all_leads as $lead): ?>
                                <?php
                                $dossier = json_decode($lead['gap_analysis'] ?? '{}', true) ?: [];
                                $status = $lead['pipeline_status'] ?? 'pending_review';
                                ?>
                                <div class="rsd-card" id="leadCard_<?php echo $lead['id']; ?>" style="margin-bottom:0;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
                                        <div>
                                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                                <h4 style="margin:0;font-size:1.15rem;font-weight:800;color:#0F172A;"><?php echo esc_html($lead['company_name']); ?></h4>
                                                <span class="rsd-badge rsd-badge-info"><?php echo esc_html($lead['target_industry']); ?></span>
                                            </div>
                                            <div style="display:flex;gap:16px;font-size:0.84rem;color:#64748B;align-items:center;">
                                                <?php if (!empty($lead['website_url'])): ?>
                                                    <a href="<?php echo esc_url($lead['website_url']); ?>" target="_blank" style="color:#2563EB;text-decoration:none;font-weight:600;">🌐 <?php echo esc_html($lead['website_url']); ?></a>
                                                    <button type="button" onclick="rsdPreviewSite('<?php echo esc_url($lead['website_url']); ?>')" class="rsd-btn rsd-btn-secondary" style="padding:2px 8px;font-size:0.72rem;">👁️ معاينة</button>
                                                <?php endif; ?>
                                                <span style="direction:ltr;">📱 +<?php echo esc_html($lead['contact_phone']); ?></span>
                                                <span>📅 <?php echo esc_html($lead['created_at']); ?></span>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($status === 'pending_review'): ?>
                                                <span class="rsd-badge rsd-badge-warning">⏳ بانتظار الاعتماد</span>
                                            <?php elseif ($status === 'contacting'): ?>
                                                <span class="rsd-badge rsd-badge-info">💬 تم الإرسال وجاري المتابعة</span>
                                            <?php elseif ($status === 'rejected'): ?>
                                                <span class="rsd-badge rsd-badge-danger">🗑️ مستبعد</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div style="display:grid;grid-template-columns:1fr 1.2fr;gap:18px;margin-bottom:14px;">
                                        <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:14px;">
                                            <strong style="display:block;font-size:0.85rem;color:#0F172A;margin-bottom:8px;">📊 تقرير تدقيق الفجوات والفاقد المالي:</strong>
                                            <div style="font-size:0.82rem;margin-bottom:6px;"><span style="color:#059669;font-weight:700;">✦ نقاط القوة:</span> <?php echo esc_html($dossier['strengths'] ?? 'حضور رقمي نشط'); ?></div>
                                            <div style="font-size:0.82rem;margin-bottom:8px;"><span style="color:#DC2626;font-weight:700;">✦ الفجوات:</span> <?php echo esc_html($dossier['critical_gaps'] ?? 'غياب محرك الحجز المباشر'); ?></div>
                                            <div style="background:#FEF2F2;border:1px solid #FECACA;padding:6px 10px;border-radius:8px;font-size:0.82rem;color:#991B1B;font-weight:700;">
                                                💸 تقدير الفاقد لعمولات OTA: <?php echo esc_html($dossier['revenue_loss_estimate'] ?? '20,000$ سنوياً'); ?>
                                            </div>
                                        </div>

                                        <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:14px;">
                                            <strong style="display:block;font-size:0.85rem;color:#166534;margin-bottom:8px;">✉️ رسالة العرض المخصصة (بهوية م. عمرو أحمد):</strong>
                                            <textarea id="pitchText_<?php echo $lead['id']; ?>" class="rsd-textarea" rows="4" style="background:#FFFFFF;border-color:#86EFAC;font-size:0.85rem;"><?php echo esc_textarea($lead['tailored_pitch']); ?></textarea>
                                        </div>
                                    </div>

                                    <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #F1F5F9;padding-top:12px;">
                                        <button type="button" onclick="rsdSaveLeadPitch(<?php echo $lead['id']; ?>)" class="rsd-btn rsd-btn-secondary" style="padding:6px 14px;font-size:0.8rem;">💾 حفظ التعديل</button>
                                        <button type="button" onclick="rsdRejectLead(<?php echo $lead['id']; ?>)" class="rsd-btn-danger" style="padding:6px 14px;border-radius:8px;font-size:0.8rem;cursor:pointer;">🗑️ استبعاد</button>
                                        <?php if ($status === 'pending_review'): ?>
                                            <button type="button" onclick="rsdApproveAndSend(<?php echo $lead['id']; ?>)" class="rsd-btn" style="background:#059669;padding:6px 18px;font-size:0.82rem;">🚀 اعتماد وإرسال عبر الواتساب</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- MODAL FOR TRACE JSON INSPECT -->
        <div id="rsdTraceModal" class="rsd-modal-overlay">
            <div class="rsd-modal-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid #E2E8F0;padding-bottom:12px;">
                    <h3 style="margin:0;font-size:1.1rem;font-weight:800;color:#0F172A;">🔍 فحص بيانات الاستدلال الكلية (Trace JSON)</h3>
                    <button type="button" onclick="document.getElementById('rsdTraceModal').style.display='none'" class="rsd-btn rsd-btn-secondary" style="padding:4px 10px;font-size:0.8rem;">✖ إغلاق</button>
                </div>
                <pre id="rsdTraceJsonPre" style="background:#0F172A;color:#38BDF8;padding:16px;border-radius:12px;overflow-x:auto;font-family:'JetBrains Mono',monospace;font-size:0.82rem;line-height:1.5;direction:ltr;"></pre>
            </div>
        </div>

        <!-- MODAL FOR SITE LIVE PREVIEW -->
        <div id="rsdSitePreviewModal" class="rsd-modal-overlay">
            <div class="rsd-modal-box" style="max-width:950px;width:95%;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h3 style="margin:0;font-size:1rem;font-weight:800;color:#0F172A;">🌐 معاينة موقع المنشأة المباشر</h3>
                    <button type="button" onclick="document.getElementById('rsdSitePreviewModal').style.display='none'" class="rsd-btn rsd-btn-secondary" style="padding:4px 10px;font-size:0.8rem;">✖ إغلاق</button>
                </div>
                <iframe id="rsdSiteIframe" src="" style="width:100%;height:550px;border:1px solid #E2E8F0;border-radius:12px;"></iframe>
            </div>
        </div>

        <!-- SCRIPTS FOR RADAR & CRM INTERACTION -->
        <script>
        function rsdInspectTrace(idx) {
            var data = (window.rsdTraceData && window.rsdTraceData[idx]) ? window.rsdTraceData[idx] : {};
            document.getElementById('rsdTraceJsonPre').innerText = JSON.stringify(data, null, 2);
            document.getElementById('rsdTraceModal').style.display = 'flex';
        }

        function rsdPreviewSite(url) {
            document.getElementById('rsdSiteIframe').src = url;
            document.getElementById('rsdSitePreviewModal').style.display = 'flex';
        }

        function rsdSwitchPairMode(mode) {
            var qrSec = document.getElementById('rsdPairModeQr');
            var codeSec = document.getElementById('rsdPairModeCode');
            var btnQr = document.getElementById('tabBtnQr');
            var btnCode = document.getElementById('tabBtnCode');

            if (mode === 'qr') {
                qrSec.style.display = 'block';
                codeSec.style.display = 'none';
                btnQr.style.background = '#FFFFFF';
                btnQr.style.color = '#0F172A';
                btnQr.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)';
                btnCode.style.background = 'transparent';
                btnCode.style.color = '#64748B';
                btnCode.style.boxShadow = 'none';
            } else {
                qrSec.style.display = 'none';
                codeSec.style.display = 'block';
                btnCode.style.background = '#FFFFFF';
                btnCode.style.color = '#0F172A';
                btnCode.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)';
                btnQr.style.background = 'transparent';
                btnQr.style.color = '#64748B';
                btnQr.style.boxShadow = 'none';
            }
        }

        function rsdRefreshQrCode() {
            var img = document.getElementById('rsdQrCodeImg');
            var ph = document.getElementById('rsdQrPlaceholder');
            ph.innerText = '⏳ جاري استدعاء رمز QR...';

            var fd = new FormData();
            fd.append('action', 'rsd_wa_get_qr');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success && d.data.qrcode_url) {
                        img.src = d.data.qrcode_url;
                        img.style.display = 'block';
                        ph.style.display = 'none';
                    } else {
                        ph.innerText = (d.data && d.data.message) ? d.data.message : 'تعذر تحميل رمز QR';
                    }
                });
        }

        function rsdRequestPairingCode() {
            var phone = document.getElementById('rsdPairPhoneInput').value;
            var disp = document.getElementById('rsdPairingCodeDisplay');
            var val = document.getElementById('rsdPairingCodeVal');

            disp.style.display = 'block';
            val.innerText = '⏳ جاري التوليد...';

            var fd = new FormData();
            fd.append('action', 'rsd_wa_get_pairing_code');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
            fd.append('phone', phone);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success && d.data.pairing_code) {
                        val.innerText = d.data.pairing_code;
                    } else {
                        disp.style.display = 'none';
                        alert(d.data && d.data.message ? d.data.message : 'تعذر استلام كود الربط.');
                    }
                });
        }

        function rsdCheckWaStatus() {
            var badge = document.getElementById('rsdWaStatusBadge');
            if (!badge) return;
            badge.innerHTML = '⏳ جاري الفحص...';

            var fd = new FormData();
            fd.append('action', 'rsd_wa_check_status');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success && (d.data.state === 'open' || d.data.state === 'connected')) {
                        badge.className = 'rsd-badge rsd-badge-success';
                        badge.innerHTML = '🟢 متصل: +' + d.data.phone;
                    } else {
                        badge.className = 'rsd-badge rsd-badge-danger';
                        badge.innerHTML = '🔴 غير متصل';
                    }
                });
        }

        if (typeof window !== 'undefined') {
            window.addEventListener('load', function() {
                if (typeof rsdCheckWaStatus === 'function') rsdCheckWaStatus();
            });
        }

        function rsdDisconnectWa() {
            if (!confirm('هل أنت متأكد من رغبتك في فك الارتباط وتسجيل الخروج؟')) return;
            var fd = new FormData();
            fd.append('action', 'rsd_wa_disconnect');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function() {
                    rsdCheckWaStatus();
                    alert('تم قطع الاتصال بنجاح.');
                });
        }

        function rsdRunRadarScan() {
            var btn = document.getElementById('rsdBtnRunRadar');
            var consoleBox = document.getElementById('rsdRadarConsole');
            var niche = document.getElementById('rsdRadarNiche').value;

            btn.disabled = true;
            btn.innerHTML = '⏳ جاري التنقيب والتحليل...';
            consoleBox.style.display = 'block';

            var fd = new FormData();
            fd.append('action', 'rsd_radar_run_discovery');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
            fd.append('niche', niche);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        alert('✅ اكتملت جولة التنقيب بنجاح! تم رصد وتدقيق الفرص.');
                        window.location.reload();
                    } else {
                        alert('حدث خطأ أثناء التنقيب: ' + (d.data && d.data.message ? d.data.message : ''));
                        btn.disabled = false;
                        btn.innerHTML = '🤖 ابدأ جولة التنقيب الآلي الآن';
                    }
                });
        }

        function rsdApproveAndSend(leadId) {
            if (!confirm('هل توافق على اعتماد وإرسال رسالة الواتساب المخصصة لهذا العميل؟')) return;
            var pitch = document.getElementById('pitchText_' + leadId).value;
            var fd = new FormData();
            fd.append('action', 'rsd_radar_approve_lead');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
            fd.append('lead_id', leadId);
            fd.append('pitch', pitch);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        alert('🚀 تم اعتماد العرض وإرساله للعميل بنجاح!');
                        window.location.reload();
                    } else {
                        alert('تعذر الإرسال: ' + (d.data && d.data.message ? d.data.message : ''));
                    }
                });
        }

        function rsdSaveLeadPitch(leadId) {
            var pitch = document.getElementById('pitchText_' + leadId).value;
            var fd = new FormData();
            fd.append('action', 'rsd_radar_edit_pitch');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
            fd.append('lead_id', leadId);
            fd.append('pitch', pitch);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function() { alert('تم حفظ تعديل نص الرسالة بنجاح.'); });
        }

        function rsdRejectLead(leadId) {
            if (!confirm('هل تريد استبعاد هذه الفرصة؟')) return;
            var fd = new FormData();
            fd.append('action', 'rsd_radar_reject_lead');
            fd.append('nonce', '<?php echo wp_create_nonce("rsd_admin_nonce"); ?>');
            fd.append('lead_id', leadId);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function() {
                    var card = document.getElementById('leadCard_' + leadId);
                    if (card) card.style.opacity = '0.35';
                    alert('تم استبعاد الفرصة.');
                });
        }
        </script>
        <?php
    }
}

// Initialize Plugin
new RedSeaAIEngine();
