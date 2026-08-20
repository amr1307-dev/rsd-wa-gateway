<?php
namespace RedSea\Agents;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\Core\OutputCleaner;

/**
 * QAAgent - Output Verification, Security Guardrails & Sanitization Agent
 */
class QAAgent {
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

        if (class_exists(OutputCleaner::class)) {
            $clean_response = OutputCleaner::clean($raw_response);
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
