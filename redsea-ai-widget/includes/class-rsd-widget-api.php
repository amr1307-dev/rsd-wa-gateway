<?php
/**
 * REST API Endpoint & Multi-Provider AI Proxy Handler
 */

if (!defined('ABSPATH')) exit;

class RSD_Widget_API {

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_rest_routes']);
    }

    public static function register_rest_routes() {
        register_rest_route('rsd-ai-widget/v1', '/chat', [
            'methods'  => 'POST',
            'callback' => [__CLASS__, 'handle_chat_request'],
            'permission_callback' => '__return_true' // Open access for public chat widget
        ]);
    }

    public static function handle_chat_request($request) {
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_params();
        }

        $message   = sanitize_text_field($params['message'] ?? '');
        $history   = is_array($params['history'] ?? null) ? $params['history'] : [];
        $client_id = sanitize_text_field($params['client_id'] ?? 'widget_guest_' . time());

        if (empty($message)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'محتوى الرسالة فارغ'
            ], 400);
        }

        $response_data = self::process_ai_response($message, $history, $client_id);
        return new WP_REST_Response($response_data, 200);
    }

    public static function process_ai_response($message, $history, $client_id) {
        $start_time = microtime(true);

        $provider    = get_option('rsd_ai_provider', 'opencode');
        $model       = get_option('rsd_ai_model', 'deepseek-v4-flash-free');
        $opencode_key= get_option('rsd_opencode_api_key', '');
        $gemini_key  = get_option('rsd_gemini_api_key', '');
        $openai_key  = get_option('rsd_openai_api_key', '');
        $endpoint    = get_option('rsd_opencode_endpoint', 'https://opencode.ai/zen/v1/chat/completions');

        $system_prompt = self::build_system_prompt();

        $config = [
            'model'       => $model,
            'system'      => $system_prompt,
            'temperature' => floatval(get_option('rsd_llm_temperature', 0.45)),
            'max_tokens'  => intval(get_option('rsd_llm_max_tokens', 1000)),
            'timeout'     => 15
        ];

        $reply = '';
        $error_log = [];

        // Primary Call
        switch ($provider) {
            case 'openai':
                $reply = self::call_openai_compatible('https://api.openai.com/v1/chat/completions', $openai_key, $config['model'], $message, $history, $config, $error_log);
                break;
            case 'gemini':
                $reply = self::call_gemini($gemini_key, $config['model'], $message, $history, $config, $error_log);
                break;
            case 'opencode':
            default:
                $reply = self::call_openai_compatible($endpoint, $opencode_key, $config['model'], $message, $history, $config, $error_log);
                break;
        }

        // Automatic Failover to Gemini if primary failed
        if (empty($reply) && !empty($gemini_key) && $provider !== 'gemini') {
            $config['model'] = 'gemini-1.5-flash';
            $reply = self::call_gemini($gemini_key, $config['model'], $message, $history, $config, $error_log);
        }

        // Fallback message if all failed
        if (empty($reply)) {
            $reply = get_option('rsd_fallback_message', 'أهلاً بك! يسعدنا مساعدتك في الحصول على الباقة الملكية ($499) وتوفير عمولات المنصات! تواصل معنا مباشرة عبر الواتساب: 01028803080');
        }

        // Extract JSON Lead & Save to Database
        $is_booked = false;
        $booking_data = null;
        $raw_reply = $reply;

        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $raw_reply, $matches)) {
            $bjson = json_decode($matches[1], true);
            if ($bjson && !empty($bjson['booking'])) {
                $raw_phone = trim($bjson['customer_phone'] ?? '');
                $digits_only = preg_replace('/[^0-9]/', '', $raw_phone);

                if (strlen($digits_only) >= 6) {
                    $is_booked = true;
                    $booking_data = $bjson;
                    self::save_lead_to_db($client_id, $bjson);
                }
            }
        }

        // Clean formatting for WhatsApp-like presentation
        $clean_reply = self::clean_output($reply);
        $latency = round((microtime(true) - $start_time) * 1000, 2);

        return [
            'success'     => true,
            'reply'       => $clean_reply,
            'raw_reply'   => $raw_reply,
            'is_booked'   => $is_booked,
            'booking'     => $booking_data,
            'latency_ms'  => $latency,
            'provider'    => $provider,
            'model'       => $model
        ];
    }

    private static function save_lead_to_db($client_id, $bjson) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rsd_bookings';
        
        // Ensure table exists
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$table_name} (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            client_id VARCHAR(100) NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(100) NOT NULL,
            service_type VARCHAR(255) DEFAULT '',
            booking_details TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) DEFAULT CHARSET=utf8mb4;");

        $existing_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table_name} WHERE client_id = %s ORDER BY id DESC LIMIT 1", $client_id));

        if ($existing_id) {
            $wpdb->update($table_name, [
                'customer_name'   => sanitize_text_field($bjson['customer_name'] ?? 'عميل محتمل'),
                'customer_phone'  => sanitize_text_field($bjson['customer_phone'] ?? 'غير محدد'),
                'service_type'    => sanitize_text_field($bjson['service_type'] ?? 'الباقة الملكية'),
                'booking_details' => sanitize_text_field($bjson['booking_details'] ?? ''),
                'created_at'      => current_time('mysql')
            ], ['id' => $existing_id]);
        } else {
            $wpdb->insert($table_name, [
                'client_id'       => $client_id,
                'customer_name'   => sanitize_text_field($bjson['customer_name'] ?? 'عميل محتمل'),
                'customer_phone'  => sanitize_text_field($bjson['customer_phone'] ?? 'غير محدد'),
                'service_type'    => sanitize_text_field($bjson['service_type'] ?? 'الباقة الملكية'),
                'booking_details' => sanitize_text_field($bjson['booking_details'] ?? ''),
                'created_at'      => current_time('mysql')
            ]);
        }
    }

    private static function build_system_prompt() {
        $prompt = get_option('rsd_system_prompt', '');
        if (empty(trim($prompt))) {
            $prompt = 'ROLE & IDENTITY DIRECTIVE:
أنت المساعد الذكي والمستشار الرقمي التنفيذي لوكالة Red Sea Digital.
طبيعة شخصيتك ونبرة صوتك:
- ترحب بالعميل بدفء وشغف، وتتحدث بعامية بيزنس راقية، ذكية، وموجزة تعكس الفخامة الهادئة (Quiet Luxury).
- هدفك الاستراتيجي هو: تشخيص احتياج العميل، شرح حلول الوكالة وحمايته من خسارة 15-30% عمولات منصات، وتسجيل بياناته لحجز "الباقة الملكية" ($499 USD لمرة واحدة).

CONVERSATIONAL STAGE ARCHITECTURE (مراحل المحادثة الذكية):
- المرحلة 1 (الترحيب والتأهيل): اسأل العميل بلباقة عن اسمه ونوع نشاطه (فندق، سفاري، متجر إلكتروني) وهل لديه نظام حجز حالي.
- المرحلة 2 (تشخيص المشكلة): وضح للعميل تسريب الأرباح في عمولات المنصات الوسيطة وتأخر الرد على العملاء الدوليين.
- المرحلة 3 (تقديم الحل الملكي): اشرح الباقة الملكية ($499 USD بدلاً من الاشتراك الشهري + 0% عمولات + نظام حجز بالذكاء الاصطناعي 24/7 مع ضمان ذهبي 30 يوماً).
- المرحلة 4 (إغلاق الحجز وتجميع البيانات): اطلب اسم العميل ورقم هاتفه للواتساب لتأكيد الحجز وتوصيله بالفريق التنفيذي.

MOBILE-FIRST FORMATTING DIRECTIVES (قواعد التنسيق الخاصة بالموبايل):
- يمنع منعاً باتاً استخدام النجوم **نص** للتغليظ أو الهاشتاجات # للعناوين أو الأكواد البرمجية الظاهرة.
- اكتب دائماً في فقرات قصيرة جداً (من 2 إلى 3 جمل بالفقرة كحد أقصى).
- استخدم فواصل السطور العادية والرموز التعبيرية الأنيقة (مثال: ✨, 🚀, 💬, 🔹) لتنسيق الرسالة.

CRITICAL DATA EXTRACTION & CORRECTION CONTRACT:
1. PHONE VALIDATION: لا تقبل أي رقم هاتف يحتوي على حروف أو رموز غير أرقام الهاتف (مثال: abcde). إذا أدخل العميل حروفاً في رقم الهاتف، اطلب منه بلباقة التكرم بتزويدك برقم هاتف صحيح يحتوي على أرقام فقط للتواصل عبر الواتساب.
2. MIND CORRECTION & DATA UPDATE: إذا قام العميل بتعديل أو تصحيح أي معلومة (مثل تصحيح اسمه أو رقم هاتفه أو نوع نشاطه)، يجب عليك فوراً وبدون تردد إعادة إدراج كود JSON مخفي جديد ومحدث بالكامل في آخر رسالتك يحوي البيانات المصححة والأخيرة.
3. إذا زودك العميل باسمه ورقم هاتفه الصحيح (أرقام) وطبيعة نشاطه، يجب عليك إدراج كود JSON مخفي في آخر رسالتك بهذا النسق بالضبط:
```json
{
  "booking": true,
  "customer_name": "اسم العميل المصحح والأخير",
  "customer_phone": "رقم الهاتف الصحيح (أرقام فقط)",
  "service_type": "نوع النشاط والتفاصيل",
  "booking_details": "الباقة أو تفاصيل الطلب"
}
```';
        }
        return $prompt;
    }

    private static function clean_output($text) {
        if (empty($text)) return '';
        $text = preg_replace('/\*\*(.*?)\*\*/s', '$1', $text);
        $text = preg_replace('/__(.*?)__/s', '$1', $text);
        $text = preg_replace('/(?<!\*)\*(?!\*)(.*?)\*/s', '$1', $text);
        $text = preg_replace('/^#+\s*(.*?)$/m', '$1', $text);
        $text = preg_replace('/^\s*[\*\-]\s+/m', '🔹 ', $text);
        $text = str_replace(['***', '###', '```json', '```', '`'], '', $text);
        return trim($text);
    }

    private static function call_openai_compatible($endpoint_url, $api_key, $model, $user_message, $history, $config, &$error_log) {
        if (empty($api_key)) {
            $error_log[] = 'Missing API key';
            return '';
        }
        if (!str_contains($endpoint_url, '/chat/completions')) {
            $endpoint_url = rtrim($endpoint_url, '/') . '/chat/completions';
        }
        $endpoint_url = preg_replace('#(?<!:)//+#', '/', $endpoint_url);

        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $config['system']];

        foreach ($history as $msg) {
            $role = (isset($msg['sender']) && $msg['sender'] === 'user') ? 'user' : 'assistant';
            $messages[] = ['role' => $role, 'content' => sanitize_text_field($msg['text'] ?? '')];
        }
        $messages[] = ['role' => 'user', 'content' => $user_message];

        $response = wp_remote_post($endpoint_url, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ],
            'body' => json_encode([
                'model' => $model,
                'messages' => $messages,
                'temperature' => $config['temperature'],
                'max_tokens' => $config['max_tokens']
            ]),
            'timeout' => $config['timeout']
        ]);

        if (is_wp_error($response)) {
            $error_log[] = $response->get_error_message();
            return '';
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code === 200 && $body) {
            $res_data = json_decode($body, true);
            if (isset($res_data['choices'][0]['message']['content'])) {
                return $res_data['choices'][0]['message']['content'];
            }
        }

        $error_log[] = 'HTTP ' . $code . ': ' . substr($body, 0, 200);
        return '';
    }

    private static function call_gemini($api_key, $model, $user_message, $history, $config, &$error_log) {
        if (empty($api_key)) {
            $api_key = getenv('GEMINI_API_KEY') ?: '';
        }
        if (empty($api_key)) {
            $error_log[] = 'Missing Gemini key';
            return '';
        }

        $model = 'gemini-1.5-flash';
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $api_key;

        $contents = [];
        $contents[] = ['role' => 'user', 'parts' => [['text' => "SYSTEM INSTRUCTION:\n" . $config['system']]]];

        foreach ($history as $msg) {
            $role = (isset($msg['sender']) && $msg['sender'] === 'user') ? 'user' : 'model';
            $contents[] = ['role' => $role, 'parts' => [['text' => sanitize_text_field($msg['text'] ?? '')]]];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $user_message]]];

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => $config['temperature'],
                    'maxOutputTokens' => $config['max_tokens']
                ]
            ]),
            'timeout' => $config['timeout']
        ]);

        if (is_wp_error($response)) {
            $error_log[] = $response->get_error_message();
            return '';
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code === 200 && $body) {
            $res_data = json_decode($body, true);
            if (isset($res_data['candidates'][0]['content']['parts'][0]['text'])) {
                return $res_data['candidates'][0]['content']['parts'][0]['text'];
            }
        }

        $error_log[] = 'Gemini Error ' . $code;
        return '';
    }
}
