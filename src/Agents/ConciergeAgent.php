<?php
namespace RedSea\Agents;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\Providers\LLMProviderManager;

/**
 * ConciergeAgent - Senior Direct Booking & Sales Conversion Agent
 */
class ConciergeAgent {
    public static function generate_response($user_message, $rag_context, $history = [], $custom_options = [], &$trace = []) {
        $start_time = microtime(true);

        $extra_context = "";
        if (!empty($rag_context)) {
            $extra_context = "\n\n<grounded_knowledge_base>\n" . $rag_context . "\n</grounded_knowledge_base>";
        }

        $system_prompt = LLMProviderManager::build_system_prompt('', 'concierge', $custom_options) . $extra_context;

        $provider = $custom_options['provider'] ?? get_option('rsd_ai_provider', 'gemini');
        $model    = $custom_options['model'] ?? get_option('rsd_ai_model', 'gemini-flash-latest');

        $gen_options = array_merge($custom_options, [
            'provider'      => $provider,
            'model'         => $model,
            'system_prompt' => $system_prompt
        ]);

        $response = LLMProviderManager::generate($user_message, $history, $gen_options);

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
