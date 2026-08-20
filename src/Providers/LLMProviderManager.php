<?php
namespace RedSea\Providers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * LLMProviderManager - Multi-Model Gateway & AI Resilience Router
 * Interfaces OpenCode, Gemini, DeepSeek, OpenAI, and custom provider fallbacks.
 */
class LLMProviderManager {

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
