<?php
namespace RedSea\Agents;

if (!defined('ABSPATH')) {
    exit;
}

use RedSea\RAG\KnowledgeBaseManager;

/**
 * RAGAgent - Grounded Context Extractor & Semantic Retrieval Agent
 */
class RAGAgent {
    public static function get_grounded_context($user_query) {
        $context_blocks = [];

        if (class_exists(KnowledgeBaseManager::class)) {
            // 1. Search Semantic Vector Store
            $similar_chunks = KnowledgeBaseManager::search_similar_chunks($user_query, 4);
            if (!empty($similar_chunks)) {
                $context_blocks[] = "=== KNOWLEDGE BASE GROUNDED CONTEXT ===\n" . implode("\n\n", $similar_chunks);
            }

            // 2. Extract Real-Time Business Catalog (Products / Tours / Hotels / Services)
            $live_catalog = KnowledgeBaseManager::get_live_business_catalog();
            if (!empty($live_catalog)) {
                $context_blocks[] = $live_catalog;
            }
        }

        return implode("\n\n", $context_blocks);
    }
}
