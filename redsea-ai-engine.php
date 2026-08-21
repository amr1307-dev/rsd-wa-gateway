<?php

require_once plugin_dir_path(__FILE__) . 'includes/class-rsd-elementor-suite.php';

/**
 * Plugin Name: Red Sea AI Engine
 * Plugin URI: https://redseadigital.pro
 * Description: Unified AI Architecture, RAG Knowledge Base, Lead CRM, and Glassmorphic Frontend Chat Engine v5.1.0 Pro.
 * Version: 5.3.0 Pro
 * Author: Red Sea Digital (Amr Ahmed)
 */

// Load PSR-4 Composer Autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use RedSea\Core\OutputCleaner;
use RedSea\Agents\ToolManager;
use RedSea\Agents\QAAgent;
use RedSea\Agents\RAGAgent;
use RedSea\Agents\ConciergeAgent;
use RedSea\Agents\AgentFactory;
use RedSea\Orchestrator\ChiefOrchestrator;
use RedSea\Radar\LeadRadarEngine;
use RedSea\RAG\KnowledgeBaseManager;
use RedSea\Providers\LLMProviderManager;
use RedSea\CRM\LeadManager;
use RedSea\Gateway\WhatsAppGateway;
use RedSea\Database\SchemaManager;
use RedSea\Admin\AdminController;
use RedSea\Admin\AjaxHandler;
use RedSea\Frontend\FrontendManager;

if (!defined('ABSPATH')) exit;







register_activation_hook(__FILE__, ['\\RedSea\\Database\\SchemaManager', 'create_tables']);

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
        return \RedSea\Gateway\WhatsAppGateway::handle_inbound_webhook($request);
    }
    public function handle_whatsapp_webhook_legacy($request) {
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

            $raw_reply = LLMProviderManager::generate($clean_text, [], [
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
        // wp_head handled in FrontendManager::init()
        add_action('wp_head', [$this, 'inject_universal_master_header'], 2);
        // wp_footer master footer handled in FrontendManager::init()
        // Initialize Frontend Layout & Chat Presentation Layer
        FrontendManager::init();

        add_action('admin_enqueue_scripts', [$this, 'remove_admin_footer_text'], 999);

        // Register Enterprise AJAX Handlers
        AjaxHandler::init();

        // Register REST API Endpoints for 2-Way WhatsApp Webhook
        add_action('rest_api_init', [$this, 'register_rest_routes']);


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

        $res = LLMProviderManager::generate($message_text);
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
        include __DIR__ . '/templates/frontend/nuclear-css.php';
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


    public function render_crm_page() {
        global $wpdb;

        // Ensure Vector Store & Leads tables exist
        KnowledgeBaseManager::init_vector_store_table();
        SchemaManager::create_tables();

        // Handle POST submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('rsd_crm_settings_nonce');
            if (!current_user_can('manage_options')) wp_die('غير مصرح لك.');

            $tab = sanitize_text_field($_POST['active_tab'] ?? 'overview');

            // 1. WhatsApp Dual-Engine & Gateway Settings
            if (isset($_POST['rsd_save_settings']) || isset($_POST['rsd_whatsapp_api_url']) || isset($_POST['rsd_meta_phone_id'])) {
                if (isset($_POST['rsd_whatsapp_gateway_mode'])) {
                    update_option('rsd_whatsapp_gateway_mode', sanitize_text_field($_POST['rsd_whatsapp_gateway_mode']));
                }
                if (isset($_POST['rsd_meta_app_id'])) {
                    update_option('rsd_meta_app_id', sanitize_text_field($_POST['rsd_meta_app_id']));
                }
                if (isset($_POST['rsd_meta_phone_id'])) {
                    update_option('rsd_meta_phone_id', sanitize_text_field($_POST['rsd_meta_phone_id']));
                }
                if (isset($_POST['rsd_meta_waba_id'])) {
                    update_option('rsd_meta_waba_id', sanitize_text_field($_POST['rsd_meta_waba_id']));
                }
                if (isset($_POST['rsd_meta_access_token'])) {
                    update_option('rsd_meta_access_token', sanitize_text_field($_POST['rsd_meta_access_token']));
                }
                if (isset($_POST['rsd_meta_webhook_verify_token'])) {
                    update_option('rsd_meta_webhook_verify_token', sanitize_text_field($_POST['rsd_meta_webhook_verify_token']));
                }
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
                echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم حفظ إعدادات محرك بوابة الواتساب بنجاح! 💾✨</strong></p></div>';
            }

            if (isset($_POST['rsd_create_custom_agent'])) {
                $agent_name    = sanitize_text_field($_POST['rsd_new_agent_name'] ?? '');
                $agent_mission = sanitize_textarea_field($_POST['rsd_new_agent_mission'] ?? '');
                if (!empty($agent_name) && !empty($agent_mission)) {
                    $new_agent = AgentFactory::create_custom_agent($agent_name, $agent_mission);
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
                    KnowledgeBaseManager::save_file_content($file_name, $content);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #2563EB;"><p><strong>تم حفظ التعديلات على الملف [' . esc_html($file_name) . '] وإعادة فهرسته دلالياً بنجاح.</strong></p></div>';
                }
            }

            // 4. Delete RAG File
            if (isset($_POST['rsd_delete_file'])) {
                $file_name = sanitize_text_field($_POST['rsd_delete_file_name'] ?? '');
                if (!empty($file_name)) {
                    KnowledgeBaseManager::delete_file($file_name);
                    echo '<div class="notice notice-success is-dismissible" style="margin:20px 0;border-radius:10px;border-right:4px solid #EF4444;"><p><strong>تم حذف الملف ومسح مقاطعه من قاعدة المعرفة بنجاح.</strong></p></div>';
                }
            }

            // 5. Upload New RAG File
            if (isset($_FILES['rsd_upload_new_file']) && !empty($_FILES['rsd_upload_new_file']['name'])) {
                $uploaded = $_FILES['rsd_upload_new_file'];
                $ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['md', 'txt', 'json'])) {
                    $content = file_get_contents($uploaded['tmp_name']);
                    KnowledgeBaseManager::save_file_content($uploaded['name'], $content);
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
        $kb_files     = KnowledgeBaseManager::list_all_kb_files();
        $total_leads  = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rsd_bookings");
        $total_chunks = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rsd_vector_store");
        $recent_logs  = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}rsd_bookings ORDER BY id DESC LIMIT 50", ARRAY_A);
        $traces       = get_option('rsd_orchestration_logs', []);
        $all_agents   = AgentFactory::get_all_agents();
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

            
            /* Card Container */
            .rsd-crm-card {
                background: #FFFFFF !important;
                border: 1px solid #E2E8F0 !important;
                border-radius: 16px !important;
                padding: 20px 24px !important;
                box-sizing: border-box !important;
                width: 100% !important;
            }

            /* Table Wrapper */
            .rsd-crm-table-container {
                width: 100% !important;
                overflow-x: hidden !important; /* Strictly disable horizontal scrollbar */
                margin-top: 16px !important;
            }

            /* Responsive Table Structure */
            table.rsd-crm-table {
                width: 100% !important;
                border-collapse: collapse !important;
                table-layout: fixed !important; /* Enforces strict percentage column boundaries */
                direction: rtl !important;
            }

            table.rsd-crm-table th, 
            table.rsd-crm-table td {
                padding: 10px 6px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #F1F5F9 !important;
                font-size: 12px !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }

            /* Exact 6-Column Percentage Distribution */
            table.rsd-crm-table th:nth-child(1), table.rsd-crm-table td:nth-child(1) { width: 5% !important; text-align: center !important; } /* # ID */
            table.rsd-crm-table th:nth-child(2), table.rsd-crm-table td:nth-child(2) { width: 18% !important; text-align: right !important; font-weight: 600 !important; } /* Client Name */
            table.rsd-crm-table th:nth-child(3), table.rsd-crm-table td:nth-child(3) { width: 17% !important; text-align: center !important; } /* Phone Badge */
            table.rsd-crm-table th:nth-child(4), table.rsd-crm-table td:nth-child(4) { width: 18% !important; text-align: center !important; } /* Service Type */
            table.rsd-crm-table th:nth-child(5), table.rsd-crm-table td:nth-child(5) { width: 26% !important; text-align: right !important; color: #475569 !important; } /* Message */
            table.rsd-crm-table th:nth-child(6), table.rsd-crm-table td:nth-child(6) { width: 16% !important; text-align: left !important; color: #94A3B8 !important; font-size: 11px !important; direction: ltr !important; } /* Date */

            /* Badge Styling */
            .rsd-phone-badge {
                direction: ltr !important;
                display: inline-block !important;
                max-width: 100% !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
                font-family: monospace !important;
                font-size: 10.5px !important;
                background: #DCFCE7 !important;
                color: #15803D !important;
                padding: 3px 8px !important;
                border-radius: 12px !important;
                box-sizing: border-box !important;
            }

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
                        <?php include __DIR__ . '/templates/admin/tab-overview.php'; ?>

                    <!-- TAB 2: AGENTS FORGE -->
                    <?php elseif ($active_tab === 'agents'): ?>
                        <?php include __DIR__ . '/templates/admin/tab-agents.php'; ?>

                    <!-- TAB 3: BUSINESS IDENTITY -->
                    <?php elseif ($active_tab === 'company'): ?>
                        <?php include __DIR__ . '/templates/admin/tab-identity.php'; ?>

                    <!-- TAB 4: RAG KNOWLEDGE FILES -->
                    <?php elseif ($active_tab === 'rag'): ?>
                        <?php include __DIR__ . '/templates/admin/tab-knowledge.php'; ?>

                    <!-- TAB 5: SALES CONCIERGE -->
                    <?php elseif ($active_tab === 'concierge'): ?>
                        <?php include __DIR__ . '/templates/admin/tab-sales.php'; ?>

                    <!-- TAB 6: MODELS HUB -->
                    <?php elseif ($active_tab === 'models'): ?>
                        <?php include __DIR__ . '/templates/admin/tab-models.php'; ?>

                    <!-- TAB 7: VOICE STUDIO -->
                    <?php elseif ($active_tab === 'voice'): ?>
                        <?php include __DIR__ . '/templates/admin/tab-voice.php'; ?>

                    <!-- TAB 8: WHATSAPP BRIDGE & CRM -->
                    <?php elseif ($active_tab === 'crm'): ?>
                        <?php include __DIR__ . '/templates/admin/tab-crm.php'; ?>

                    <!-- TAB 9: AUTONOMOUS LEAD RADAR -->
                    <?php elseif ($active_tab === 'radar'): ?>
                        <?php include __DIR__ . '/templates/admin/tab-radar.php'; ?>

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
