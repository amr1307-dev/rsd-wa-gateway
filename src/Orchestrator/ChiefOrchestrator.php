<?php
namespace RedSea\Orchestrator;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\Agents\ToolManager;
use RedSea\Agents\QAAgent;
use RedSea\Agents\RAGAgent;
use RedSea\Agents\ConciergeAgent;
use RedSea\RAG\KnowledgeBaseManager;

/**
 * ChiefOrchestrator - Multi-Agent Supervisor & Intent Routing Engine
 */
class ChiefOrchestrator {
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

        $rag_context = RAGAgent::get_grounded_context($user_message);
        $trace['rag_agent'] = [
            'status'        => !empty($rag_context) ? 'grounded' : 'no_chunks',
            'chunks_found'  => !empty($rag_context) ? 1 : 0
        ];

        $raw_response = ConciergeAgent::generate_response($user_message, $rag_context, $history, $custom_options, $trace);
        $final_response = QAAgent::audit_and_sanitize($raw_response, $trace);

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
