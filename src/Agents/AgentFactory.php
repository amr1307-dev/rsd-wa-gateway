<?php
namespace RedSea\Agents;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\Providers\LLMProviderManager;

/**
 * AgentFactory - Dynamic Multi-Agent Factory
 */
class AgentFactory {
    public static function create_custom_agent($agent_name, $agent_mission, $assigned_tools = ['rag_search', 'sales_calculator']) {
        return LLMProviderManager::create_custom_agent($agent_name, $agent_mission, $assigned_tools);
    }

    public static function get_all_agents() {
        return LLMProviderManager::get_all_agents();
    }
}
