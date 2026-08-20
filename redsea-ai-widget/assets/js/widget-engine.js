/**
 * Red Sea AI Chatbot Widget — Vanilla JS Engine (v4.0.0)
 * Visual Viewport Mobile Keyboard Handling, Interactive Choice Chips, & REST Sync
 */

document.addEventListener('DOMContentLoaded', function() {
    
    const config = window.rsdWidgetConfig || {};
    const root = document.getElementById('rsd-widget-root');
    if (!root) return;

    // DOM Elements
    const launcher = document.getElementById('rsd-widget-launcher');
    const container = document.getElementById('rsd-widget-container');
    const closeBtn = document.getElementById('rsd-widget-close');
    const messagesBox = document.getElementById('rsd-messages-box');
    const typingIndicator = document.getElementById('rsd-typing-indicator');
    const chipsContainer = document.getElementById('rsd-chips-container');
    const inputField = document.getElementById('rsd-input-field');
    const sendBtn = document.getElementById('rsd-send-btn');
    const unreadBadge = document.getElementById('rsd-unread-badge');
    const iconChat = launcher.querySelector('.rsd-icon-chat');
    const iconClose = launcher.querySelector('.rsd-icon-close');

    // State Variables
    let isOpen = false;
    let chatHistory = [];
    let clientId = localStorage.getItem('rsd_widget_client_id');
    if (!clientId) {
        clientId = 'guest_' + Math.random().toString(36).substring(2, 11) + '_' + Date.now();
        localStorage.setItem('rsd_widget_client_id', clientId);
    }

    // Apply Brand Color Palette dynamically
    if (config.brandColor) {
        root.style.setProperty('--rsd-primary', config.brandColor);
    }
    if (config.accentColor) {
        root.style.setProperty('--rsd-accent', config.accentColor);
    }

    /* ==========================================================================
       FLAWLESS MOBILE KEYBOARD HANDLING VIA VISUAL VIEWPORT API
       ========================================================================== */
    function setupVisualViewport() {
        if (!window.visualViewport) return;

        function onVisualViewportResize() {
            if (!isOpen) return;

            // Only apply dynamic resize on mobile devices (screens <= 480px)
            if (window.innerWidth <= 480) {
                const viewportHeight = window.visualViewport.height;
                container.style.height = `${viewportHeight}px`;
                container.style.bottom = `${window.innerHeight - window.visualViewport.height - window.visualViewport.offsetTop}px`;
                
                // Keep input field and last message anchored in view
                messagesBox.scrollTop = messagesBox.scrollHeight;
            }
        }

        window.visualViewport.addEventListener('resize', onVisualViewportResize);
        window.visualViewport.addEventListener('scroll', onVisualViewportResize);
    }

    setupVisualViewport();

    // Anchor Input on Focus
    inputField.addEventListener('focus', function() {
        setTimeout(() => {
            if (window.innerWidth <= 480) {
                inputField.scrollIntoView({ behavior: 'smooth', block: 'end' });
            }
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }, 150);
    });

    /* ==========================================================================
       WIDGET TOGGLE & UNREAD BADGE
       ========================================================================== */
    function toggleWidget() {
        isOpen = !isOpen;
        if (isOpen) {
            container.classList.remove('rsd-hidden');
            iconChat.style.display = 'none';
            iconClose.style.display = 'block';
            unreadBadge.style.display = 'none';

            if (chatHistory.length === 0) {
                renderWelcomeMessage();
            }
            messagesBox.scrollTop = messagesBox.scrollHeight;
            inputField.focus();
        } else {
            container.classList.add('rsd-hidden');
            iconChat.style.display = 'block';
            iconClose.style.display = 'none';
            container.style.height = '';
            container.style.bottom = '';
        }
    }

    launcher.addEventListener('click', toggleWidget);
    closeBtn.addEventListener('click', toggleWidget);

    /* ==========================================================================
       WELCOME MESSAGE & CHOICE CHIPS RENDERING
       ========================================================================== */
    function renderWelcomeMessage() {
        const welcomeText = config.welcomeMessage || 'أهلاً بك في Red Sea Digital ✨ كيف يمكنني مساعدتك اليوم؟';
        appendMessage('model', welcomeText);
        renderQuickChips(config.quickChips || []);
    }

    function renderQuickChips(chips) {
        chipsContainer.innerHTML = '';
        if (!chips || chips.length === 0) return;

        chips.forEach(chipText => {
            const chipBtn = document.createElement('button');
            chipBtn.type = 'button';
            chipBtn.className = 'rsd-chip-btn';
            chipBtn.textContent = chipText;
            chipBtn.addEventListener('click', function() {
                handleUserSend(chipText);
            });
            chipsContainer.appendChild(chipBtn);
        });
    }

    /* ==========================================================================
       MESSAGE HANDLING & REST API FETCH
       ========================================================================== */
    function appendMessage(sender, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `rsd-msg ${sender === 'user' ? 'rsd-msg-user' : 'rsd-msg-ai'}`;
        
        // Convert line breaks to HTML breaks
        msgDiv.innerHTML = text.replace(/\n/g, '<br>');

        const timeSpan = document.createElement('span');
        timeSpan.className = 'rsd-msg-time';
        const now = new Date();
        timeSpan.textContent = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        msgDiv.appendChild(timeSpan);

        messagesBox.appendChild(msgDiv);
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    async function handleUserSend(overrideText = null) {
        const text = (overrideText || inputField.value).trim();
        if (!text) return;

        inputField.value = '';
        inputField.style.height = 'auto';

        // Add user message to UI & history
        appendMessage('user', text);
        chatHistory.push({ sender: 'user', text: text });

        // Show typing indicator
        typingIndicator.style.display = 'block';
        messagesBox.scrollTop = messagesBox.scrollHeight;

        try {
            const response = await fetch(config.restUrl || '/wp-json/rsd-ai-widget/v1/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.nonce || ''
                },
                body: JSON.stringify({
                    message: text,
                    history: chatHistory,
                    client_id: clientId
                })
            });

            const data = await response.json();
            typingIndicator.style.display = 'none';

            const replyText = data.reply || (data.data && data.data.reply) || '';
            const isBooked = data.is_booked || (data.data && data.data.is_booked) || false;

            if (replyText) {
                appendMessage('model', replyText);
                chatHistory.push({ sender: 'model', text: replyText });

                if (isBooked) {
                    // Lead captured successfully!
                    chipsContainer.innerHTML = `
                        <a href="https://wa.me/${config.whatsappNumber}?text=أهلاً بك، أود متابعة حجز الباقة الملكية" target="_blank" class="rsd-chip-btn" style="background:#25D366; color:#fff; font-weight:bold;">
                            💬 تواصل مباشر عبر الواتساب
                        </a>
                    `;
                }
            } else {
                appendMessage('model', '⚠️ عذراً، حدث خطأ مؤقت في الاتصال. يمكنك التواصل المباشر معنا عبر الواتساب: ' + (config.whatsappNumber || '01028803080'));
            }

        } catch (err) {
            typingIndicator.style.display = 'none';
            appendMessage('model', '⚠️ عذراً، تعذر الاتصال بالسيرفر. يرجى المراسلة على الواتساب: ' + (config.whatsappNumber || '01028803080'));
        }
    }

    // Event Listeners for Send
    sendBtn.addEventListener('click', () => handleUserSend());

    inputField.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleUserSend();
        }
    });

    // Auto-grow textarea
    inputField.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});
