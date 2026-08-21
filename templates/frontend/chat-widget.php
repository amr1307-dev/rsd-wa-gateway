<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend Chat Widget & Glassmorphic Modal Window Partial
 * Responsive multi-lingual AI concierge interface with Web Speech API voice input and direct booking handoff.
 */
?>
<style id="rsd-light-luxury-ai-css">
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cairo:wght@400;500;600;700;800&display=swap');

            /* Floating Launcher Button */
            .rsd-chat-launcher {
                position: fixed !important;
                bottom: 26px !important;
                right: 26px !important;
                z-index: 9999999 !important;
                width: 62px !important;
                height: 62px !important;
                border-radius: 50% !important;
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                border: 2px solid #FFFFFF !important;
                color: #FFFFFF !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                box-shadow: 0 12px 32px rgba(37, 99, 235, 0.35), 0 4px 12px rgba(0, 0, 0, 0.1) !important;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }
            .rsd-chat-launcher:hover {
                transform: scale(1.08) translateY(-3px) !important;
                box-shadow: 0 18px 40px rgba(37, 99, 235, 0.45) !important;
            }
            .rsd-chat-launcher svg {
                width: 28px !important;
                height: 28px !important;
                fill: #FFFFFF !important;
            }
            .rsd-chat-launcher-badge {
                position: absolute !important;
                top: 2px !important;
                right: 2px !important;
                width: 14px !important;
                height: 14px !important;
                background: #10B981 !important;
                border: 2.5px solid #FFFFFF !important;
                border-radius: 50% !important;
            }

            /* Light Luxury Glassmorphic Modal Window */
            #rsdModalWindow {
                position: fixed !important;
                bottom: 98px !important;
                right: 26px !important;
                width: 400px !important;
                height: 650px !important;
                max-height: calc(100vh - 120px) !important;
                z-index: 99999999 !important;
                background: #FFFFFF !important;
                border: 1px solid rgba(0, 0, 0, 0.08) !important;
                border-radius: 26px !important;
                box-shadow: 0 24px 60px -10px rgba(0, 0, 0, 0.18), 0 0 1px rgba(0, 0, 0, 0.1) !important;
                display: none;
                flex-direction: column !important;
                overflow: hidden !important;
                direction: <?php echo $chat_dir; ?> !important;
                box-sizing: border-box !important;
                font-family: <?php echo $is_ar ? "'Cairo', sans-serif" : "'Plus Jakarta Sans', sans-serif"; ?> !important;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }
            #rsdModalWindow.active {
                display: flex !important;
                animation: rsdSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
            }
            @keyframes rsdSlideUp {
                from { opacity: 0; transform: translateY(20px) scale(0.97); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }

            /* Clean Header */
            .rsd-light-header {
                background: #FFFFFF !important;
                padding: 16px 20px !important;
                border-bottom: 1px solid #F1F5F9 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02) !important;
            }
            .rsd-header-info {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }
            .rsd-header-avatar {
                width: 42px !important;
                height: 42px !important;
                border-radius: 50% !important;
                background: linear-gradient(135deg, #2563EB, #38BDF8) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                color: #FFFFFF !important;
                font-weight: 800 !important;
                font-size: 1rem !important;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25) !important;
            }
            .rsd-header-text h4 {
                margin: 0 !important;
                font-size: 1rem !important;
                font-weight: 800 !important;
                color: #0F172A !important;
                line-height: 1.2 !important;
            }
            .rsd-header-status {
                display: flex !important;
                align-items: center !important;
                gap: 6px !important;
                font-size: 0.76rem !important;
                color: #10B981 !important;
                font-weight: 700 !important;
                margin-top: 3px !important;
            }
            .rsd-status-dot {
                width: 7px !important;
                height: 7px !important;
                background: #10B981 !important;
                border-radius: 50% !important;
                box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2) !important;
            }
            .rsd-header-close {
                width: 32px !important;
                height: 32px !important;
                border-radius: 50% !important;
                background: #F1F5F9 !important;
                border: none !important;
                color: #64748B !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                font-size: 1.1rem !important;
                transition: all 0.2s ease !important;
            }
            .rsd-header-close:hover {
                background: #E2E8F0 !important;
                color: #0F172A !important;
            }

            /* Chat Messages Body */
            .rsd-chat-body {
                flex: 1 !important;
                background: #F8FAFC !important;
                padding: 20px !important;
                overflow-y: auto !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 14px !important;
            }
            .rsd-chat-bubble {
                max-width: 85% !important;
                padding: 13px 18px !important;
                border-radius: 18px !important;
                font-size: 0.92rem !important;
                line-height: 1.6 !important;
                word-break: break-word !important;
            }
            .rsd-chat-ai {
                align-self: flex-start !important;
                background: #FFFFFF !important;
                color: #1E293B !important;
                border: 1px solid #E2E8F0 !important;
                border-bottom-right-radius: 4px !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03) !important;
            }
            .rsd-chat-user {
                align-self: flex-end !important;
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                color: #FFFFFF !important;
                border-bottom-left-radius: 4px !important;
                box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25) !important;
            }

            /* Suggestion Chips */
            .rsd-chips-container {
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 8px !important;
                margin-top: 10px !important;
            }
            .rsd-chip-btn {
                background: #F1F5F9 !important;
                border: 1px solid #E2E8F0 !important;
                color: #334155 !important;
                padding: 7px 14px !important;
                border-radius: 9999px !important;
                font-size: 0.78rem !important;
                font-weight: 700 !important;
                cursor: pointer !important;
                transition: all 0.2s ease !important;
                font-family: inherit !important;
            }
            .rsd-chip-btn:hover {
                background: #2563EB !important;
                color: #FFFFFF !important;
                border-color: #2563EB !important;
                transform: translateY(-1px) !important;
            }

            /* Input Footer */
            .rsd-chat-footer {
                background: #FFFFFF !important;
                border-top: 1px solid #F1F5F9 !important;
                padding: 14px 18px !important;
                display: flex !important;
                align-items: center !important;
                gap: 10px !important;
            }
            .rsd-input-wrapper {
                flex: 1 !important;
                background: #F1F5F9 !important;
                border: 1.5px solid #E2E8F0 !important;
                border-radius: 24px !important;
                padding: 6px 16px !important;
                display: flex !important;
                align-items: center !important;
                transition: border-color 0.2s ease !important;
            }
            .rsd-input-wrapper:focus-within {
                border-color: #2563EB !important;
                background: #FFFFFF !important;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
            }
            .rsd-chat-field {
                width: 100% !important;
                background: transparent !important;
                border: none !important;
                outline: none !important;
                font-size: 0.92rem !important;
                font-family: inherit !important;
                color: #0F172A !important;
                resize: none !important;
                line-height: 1.4 !important;
                max-height: 80px !important;
                padding: 4px 0 !important;
            }
            .rsd-btn-send {
                width: 44px !important;
                height: 44px !important;
                min-width: 44px !important;
                min-height: 44px !important;
                border-radius: 50% !important;
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                color: #FFFFFF !important;
                border: none !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                flex-shrink: 0 !important;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
                padding: 0 !important;
            }
            .rsd-btn-send:hover {
                background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%) !important;
                transform: scale(1.08) !important;
                box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4) !important;
            }
            .rsd-btn-send svg {
                width: 22px !important;
                height: 22px !important;
                min-width: 22px !important;
                min-height: 22px !important;
                display: block !important;
                fill: #FFFFFF !important;
            }
            .rsd-btn-voice {
                width: 44px !important;
                height: 44px !important;
                min-width: 44px !important;
                min-height: 44px !important;
                border-radius: 50% !important;
                background: #F8FAFC !important;
                border: 1.5px solid #E2E8F0 !important;
                color: #475569 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                flex-shrink: 0 !important;
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
                padding: 0 !important;
            }
            .rsd-btn-voice:hover {
                background: #EFF6FF !important;
                border-color: #2563EB !important;
                color: #2563EB !important;
                transform: scale(1.06) !important;
            }
            .rsd-btn-voice svg {
                width: 22px !important;
                height: 22px !important;
                min-width: 22px !important;
                min-height: 22px !important;
                display: block !important;
                fill: currentColor !important;
            }

            @media (max-width: 480px) {
                #rsdModalWindow {
                    width: 100% !important;
                    height: 100% !important;
                    max-height: 100% !important;
                    bottom: 0 !important;
                    right: 0 !important;
                    border-radius: 0 !important;
                }
            }
        </style>

        <!-- Floating Launcher Button -->
        <div class="rsd-chat-launcher" onclick="window.toggleRsdChatWidget(event)" title="<?php echo $is_ar ? 'تحدث مع المستشار الذكي واحجز موعدك' : 'Chat with AI Concierge & Book Call'; ?>">
            <div class="rsd-chat-launcher-badge"></div>
            <svg viewBox="0 0 24 24" width="28" height="28" fill="#FFFFFF">
                <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
            </svg>
        </div>

        <!-- Light Luxury Chat Modal Window -->
        <div id="rsdModalWindow">
            
            <!-- Header -->
            <div class="rsd-light-header">
                <div class="rsd-header-info">
                    <div class="rsd-header-avatar">AI</div>
                    <div class="rsd-header-text">
                        <h4><?php echo $is_ar ? 'المستشار الذكي — RED SEA' : 'RED SEA AI Concierge'; ?></h4>
                        <div class="rsd-header-status">
                            <div class="rsd-status-dot"></div>
                            <span><?php echo $is_ar ? 'متصل الآن لخدمتك 24/7' : 'Active & Ready 24/7'; ?></span>
                        </div>
                    </div>
                </div>
                <button class="rsd-header-close" onclick="var el=document.getElementById(&apos;rsd-booking-calendar&apos;);if(el){el.scrollIntoView({behavior:&apos;smooth&apos;});}else{window.toggleRsdChatWidget(event);}">✕</button>
            </div>

            <!-- Messages Stream -->
            <div class="rsd-chat-body" id="rsdChatMessages">
                <div class="rsd-chat-bubble rsd-chat-ai">
                    <?php echo $is_ar ? 'أهلاً بك! أنا مستشارك الذكي في RED SEA DIGITAL. كيف يمكنني مساعدتك في تطوير نشاطك ومضاعفة مبيعاتك المباشرة اليوم؟' : 'Welcome to RED SEA DIGITAL! I am your AI Strategic Consultant. How may I assist your business or property today?'; ?>
                    
                    <div class="rsd-chips-container">
                        <button class="rsd-chip-btn" onclick="rsdSendQuickPrompt('<?php echo $is_ar ? 'أريد حجز موعد استشارة مجانية' : 'Book a free consultation'; ?>')"><?php echo $is_ar ? 'حجز موعد استشارة' : 'Book Consultation'; ?></button>
                        <button class="rsd-chip-btn" onclick="rsdSendQuickPrompt('<?php echo $is_ar ? 'كيف يساعدني الذكاء الاصطناعي في زيادة المبيعات؟' : 'How does AI increase direct sales?'; ?>')"><?php echo $is_ar ? 'مضاعفة المبيعات المباشرة' : 'Direct Revenue Growth'; ?></button>
                        <button class="rsd-chip-btn" onclick="rsdSendQuickPrompt('<?php echo $is_ar ? 'ما هي خدماتكم لقطاع السياحة والفنادق والغوص؟' : 'Services for Hospitality & Diving'; ?>')"><?php echo $is_ar ? 'الفنادق والغوص والسياحة' : 'Hospitality & Diving'; ?></button>
                    </div>
                </div>
            </div>

            <!-- Input Footer -->
            <div class="rsd-chat-footer">
                <div class="rsd-input-wrapper">
                    <textarea id="rsdChatInput" class="rsd-chat-field" rows="1" placeholder="<?php echo $is_ar ? 'اكتب استفسارك هنا...' : 'Type your message...'; ?>" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();rsdSendMsg();}"></textarea>
                </div>
                <button id="rsdMicBtn" class="rsd-btn-voice" onclick="rsdStartVoiceInput()" title="<?php echo $is_ar ? 'تحدث صوتياً' : 'Voice Input'; ?>" type="button">
                    <svg width="22" height="22" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                </button>
                <button id="rsdSendBtn" class="rsd-btn-send" onclick="rsdSendMsg()" title="<?php echo $is_ar ? 'إرسال الرسالة' : 'Send Message'; ?>" type="button">
                    <svg width="22" height="22" viewBox="0 0 24 24" style="transform: rotate(<?php echo $is_ar ? '180deg' : '0deg'; ?>);"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </button>
            </div>

        </div>

        <script>
        window.toggleRsdChatWidget = function(e) {
            if (e) e.preventDefault();
            var modal = document.getElementById("rsdModalWindow");
            if (!modal) return;
            modal.classList.toggle("active");
            if (modal.classList.contains("active")) {
                var input = document.getElementById("rsdChatInput");
                if (input) input.focus();
            }
        };

        function rsdSendQuickPrompt(text) {
            var input = document.getElementById("rsdChatInput");
            if (input) {
                input.value = text;
                rsdSendMsg();
            }
        }

        window.rsdChatHistory = [];

        function rsdSendMsg() {
            var input = document.getElementById("rsdChatInput");
            var text = input.value.trim();
            if (!text) return;
            input.value = "";

            var body = document.getElementById("rsdChatMessages");
            var userBubble = document.createElement("div");
            userBubble.className = "rsd-chat-bubble rsd-chat-user";
            userBubble.innerText = text;
            body.appendChild(userBubble);
            body.scrollTop = body.scrollHeight;

            var aiBubble = document.createElement("div");
            aiBubble.className = "rsd-chat-bubble rsd-chat-ai";
            aiBubble.innerText = "جاري الرد...";
            body.appendChild(aiBubble);
            body.scrollTop = body.scrollHeight;

            // Keep conversation history up to 10 turns
            if (!window.rsdChatHistory) { window.rsdChatHistory = []; }
            window.rsdChatHistory.push({ role: "user", content: text });
            if (window.rsdChatHistory.length > 10) {
                window.rsdChatHistory = window.rsdChatHistory.slice(-10);
            }

            var formData = new FormData();
            formData.append("action", "rsd_chat");
            formData.append("message", text);
            formData.append("history", JSON.stringify(window.rsdChatHistory.slice(0, -1)));

            fetch("/wp-admin/admin-ajax.php", {
                method: "POST",
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var replyHtml = data.reply || "<?php echo $is_ar ? 'تم استلام استفسارك بنجاح.' : 'Your request was received successfully.'; ?>";
                aiBubble.innerHTML = replyHtml;
                // Strip html tags for history memory
                var tmp = document.createElement("div");
                tmp.innerHTML = replyHtml;
                var cleanReply = tmp.textContent || tmp.innerText || "";
                window.rsdChatHistory.push({ role: "model", content: cleanReply });
                body.scrollTop = body.scrollHeight;
            })
            .catch(function(err) {
                aiBubble.innerText = "<?php echo $is_ar ? 'يسعدنا تواصلك المباشر معنا عبر الواتساب على 01028803080 لخدمتك فوراً.' : 'Please connect with us directly on WhatsApp.'; ?>";
            });
        }

        var rsdSpeechRec = null;
        function rsdStartVoiceInput() {
            var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                alert("<?php echo $is_ar ? 'متصفحك لا يدعم التعرف الصوتي المباشر.' : 'Speech recognition not supported in this browser.'; ?>");
                return;
            }
            if (!rsdSpeechRec) {
                rsdSpeechRec = new SpeechRecognition();
                rsdSpeechRec.lang = "<?php echo $is_ar ? 'ar-SA' : 'en-US'; ?>";
                rsdSpeechRec.onresult = function(event) {
                    var spoken = event.results[0][0].transcript;
                    rsdSendQuickPrompt(spoken);
                };
            }
            rsdSpeechRec.start();
        }
        
                    </script>
