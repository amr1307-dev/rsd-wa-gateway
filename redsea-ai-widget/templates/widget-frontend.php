<?php
/**
 * Frontend Floating UI HTML Structure for Red Sea AI Widget
 * Tightly scoped under .rsd-widget-root
 */

if (!defined('ABSPATH')) exit;
?>

<div id="rsd-widget-root" class="rsd-widget-root">

    <!-- FLOATING LAUNCHER BUTTON -->
    <button type="button" id="rsd-widget-launcher" class="rsd-widget-launcher" aria-label="Open AI Assistant Chat">
        <span class="rsd-launcher-icon rsd-icon-chat">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
        </span>
        <span class="rsd-launcher-icon rsd-icon-close" style="display:none;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </span>
        <span id="rsd-unread-badge" class="rsd-unread-badge" style="display:none;">1</span>
    </button>

    <!-- FLOATING CHAT CONTAINER WINDOW -->
    <div id="rsd-widget-container" class="rsd-widget-container rsd-hidden" role="dialog" aria-modal="true" aria-labelledby="rsd-widget-title">
        
        <!-- GLASSMORPHIC HEADER -->
        <div class="rsd-widget-header">
            <div class="rsd-header-info">
                <div class="rsd-avatar-box">
                    <span class="rsd-avatar-status"></span>
                    <span class="rsd-avatar-emoji">👑</span>
                </div>
                <div>
                    <h3 id="rsd-widget-title" class="rsd-header-title">Red Sea AI Assistant</h3>
                    <p class="rsd-header-subtitle">مستشار رقمي تنفيذي • متصل الآن</p>
                </div>
            </div>
            <button type="button" id="rsd-widget-close" class="rsd-header-close" aria-label="Close Chat">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- MESSAGES SCROLL AREA -->
        <div id="rsd-messages-box" class="rsd-messages-box">
            <!-- Messages rendered dynamically by JS -->
        </div>

        <!-- TYPING INDICATOR -->
        <div id="rsd-typing-indicator" class="rsd-typing-indicator" style="display:none;">
            <div class="rsd-typing-bubble">
                <span class="rsd-dot"></span>
                <span class="rsd-dot"></span>
                <span class="rsd-dot"></span>
            </div>
        </div>

        <!-- QUICK CHOICE CHIPS CONTAINER -->
        <div id="rsd-chips-container" class="rsd-chips-container">
            <!-- Choice Pills inserted dynamically by JS -->
        </div>

        <!-- PINNED INPUT CONTROLS BAR -->
        <div class="rsd-input-bar">
            <div class="rsd-input-wrapper">
                <textarea 
                    id="rsd-input-field" 
                    class="rsd-input-field" 
                    placeholder="اكتب استفسارك أو طلبك هنا..." 
                    rows="1" 
                    aria-label="Write a message"
                ></textarea>
                <button type="button" id="rsd-send-btn" class="rsd-send-btn" aria-label="Send message">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
            <div class="rsd-footer-brand">
                <span>Powered by Red Sea Digital AI Engine v4.0</span>
            </div>
        </div>

    </div>

</div>
