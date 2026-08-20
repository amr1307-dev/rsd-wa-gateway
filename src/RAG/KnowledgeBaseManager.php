<?php
namespace RedSea\RAG;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * KnowledgeBaseManager - Vector RAG, Document Storage & Semantic Search Engine
 */
class KnowledgeBaseManager {

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
