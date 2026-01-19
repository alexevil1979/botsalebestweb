/**
 * BestWeb Telegram Bot Chat Widget Script
 */

(function() {
    'use strict';
    
    if (typeof bestwebTgBotChat === 'undefined') {
        return;
    }
    
    const widget = document.getElementById('bestweb-tg-bot-chat-widget');
    const button = document.getElementById('bestweb-tg-bot-chat-button');
    const window = document.getElementById('bestweb-tg-bot-chat-window');
    const closeBtn = document.getElementById('bestweb-tg-bot-chat-close');
    const messagesContainer = document.getElementById('bestweb-tg-bot-chat-messages');
    const input = document.getElementById('bestweb-tg-bot-chat-input');
    const sendBtn = document.getElementById('bestweb-tg-bot-chat-send');
    
    let sessionId = localStorage.getItem('bestweb_tg_bot_chat_session_id') || generateSessionId();
    let isOpen = false;
    let isTyping = false;
    
    // Сохраняем session ID
    localStorage.setItem('bestweb_tg_bot_chat_session_id', sessionId);
    
    // Открытие/закрытие чата
    button.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', closeChat);
    
    // Отправка сообщения
    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    function toggleChat() {
        if (isOpen) {
            closeChat();
        } else {
            openChat();
        }
    }
    
    function openChat() {
        isOpen = true;
        window.style.display = 'flex';
        button.style.display = 'none';
        input.focus();
        
        // Если сообщений нет, показываем приветствие
        if (messagesContainer.children.length === 0) {
            sendInitialMessage();
        }
    }
    
    function closeChat() {
        isOpen = false;
        window.style.display = 'none';
        button.style.display = 'flex';
    }
    
    function sendInitialMessage() {
        // Отправляем пустое сообщение для получения приветствия
        sendMessageToAPI('', true);
    }
    
    function sendMessage() {
        const message = input.value.trim();
        if (!message || isTyping) {
            return;
        }
        
        // Добавляем сообщение пользователя
        addMessage(message, 'user');
        input.value = '';
        
        // Отправляем на сервер
        sendMessageToAPI(message, false);
    }
    
    function sendMessageToAPI(message, isInitial = false) {
        isTyping = true;
        showTypingIndicator();
        sendBtn.disabled = true;
        
        fetch(bestwebTgBotChat.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                session_id: sessionId,
                message: message || (isInitial ? '/start' : ''),
                lang: bestwebTgBotChat.lang,
                name: 'Website User',
            }),
        })
        .then(response => response.json())
        .then(data => {
            hideTypingIndicator();
            isTyping = false;
            sendBtn.disabled = false;
            
            if (data.success && data.response) {
                addMessage(data.response, 'bot');
            } else if (data.error) {
                addMessage('Ошибка: ' + data.error, 'bot');
            }
        })
        .catch(error => {
            hideTypingIndicator();
            isTyping = false;
            sendBtn.disabled = false;
            console.error('Chat error:', error);
            addMessage('Произошла ошибка. Пожалуйста, попробуйте позже.', 'bot');
        });
    }
    
    function addMessage(text, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `bestweb-tg-bot-chat-message bestweb-tg-bot-chat-message-${type}`;
        
        const textDiv = document.createElement('div');
        textDiv.textContent = text;
        messageDiv.appendChild(textDiv);
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'bestweb-tg-bot-chat-message-time';
        timeDiv.textContent = new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
        messageDiv.appendChild(timeDiv);
        
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }
    
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'bestweb-tg-bot-chat-typing';
        typingDiv.id = 'bestweb-tg-bot-chat-typing';
        typingDiv.innerHTML = '<span></span><span></span><span></span>';
        messagesContainer.appendChild(typingDiv);
        scrollToBottom();
    }
    
    function hideTypingIndicator() {
        const typingDiv = document.getElementById('bestweb-tg-bot-chat-typing');
        if (typingDiv) {
            typingDiv.remove();
        }
    }
    
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    function generateSessionId() {
        return 'web_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
    }
})();
