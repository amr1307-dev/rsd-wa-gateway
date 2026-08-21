<?php
namespace RedSea\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * SchemaManager - Enterprise Database Schema & Migration Manager
 * Manages database tables creation, updates, indexing, and integrity for RED SEA DIGITAL.
 */
class SchemaManager {

    /**
     * Get full table name with WordPress database prefix
     * 
     * @param string $table_suffix
     * @return string
     */
    public static function get_table_name($table_suffix) {
        global $wpdb;
        return $wpdb->prefix . 'rsd_' . ltrim($table_suffix, '_');
    }

    /**
     * Create or update all custom database tables using dbDelta
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // 1. Leads Table (Outbound Lead Radar & Prospecting Dossiers)
        $tbl_leads = self::get_table_name('leads');
        $sql_leads = "CREATE TABLE {$tbl_leads} (
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
            KEY pipeline_status (pipeline_status),
            KEY created_at (created_at)
        ) {$charset_collate};";

        // 2. Bookings Table (Customer Inquiries, WhatsApp & Direct Booking Pipeline)
        $tbl_bookings = self::get_table_name('bookings');
        $sql_bookings = "CREATE TABLE {$tbl_bookings} (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            customer_name VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(50) NOT NULL,
            service_type VARCHAR(100) DEFAULT 'استفسار مباشر',
            booking_details LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY customer_phone (customer_phone),
            KEY created_at (created_at)
        ) {$charset_collate};";

        // 3. Vector Store Table (RAG Knowledge Base & Semantic Search Embeddings)
        $tbl_vectors = self::get_table_name('vector_store');
        $sql_vectors = "CREATE TABLE {$tbl_vectors} (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            file_name VARCHAR(255) NOT NULL,
            chunk_index INT(11) NOT NULL DEFAULT 0,
            chunk_text LONGTEXT NOT NULL,
            embedding_json LONGTEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY file_name (file_name)
        ) {$charset_collate};";

        // 4. Telemetry & Inference Logs Table (AI Orchestration Traces & Audit Logs)
        $tbl_telemetry = self::get_table_name('telemetry_logs');
        $sql_telemetry = "CREATE TABLE {$tbl_telemetry} (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            request_id VARCHAR(64) NOT NULL,
            channel VARCHAR(50) NOT NULL DEFAULT 'web_chat',
            prompt_preview TEXT DEFAULT NULL,
            response_preview TEXT DEFAULT NULL,
            tokens_used INT(11) DEFAULT 0,
            execution_time FLOAT DEFAULT 0,
            status VARCHAR(50) DEFAULT 'success',
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY request_id (request_id),
            KEY channel (channel),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta($sql_leads);
        dbDelta($sql_bookings);
        dbDelta($sql_vectors);
        dbDelta($sql_telemetry);

        update_option('rsd_db_schema_version', '5.3.0');
    }

    /**
     * Check if all required tables exist
     * 
     * @return bool
     */
    public static function verify_tables_exist() {
        global $wpdb;
        $tables = ['leads', 'bookings', 'vector_store', 'telemetry_logs'];
        foreach ($tables as $t) {
            $name = self::get_table_name($t);
            if ($wpdb->get_var("SHOW TABLES LIKE '{$name}'") !== $name) {
                return false;
            }
        }
        return true;
    }
}
