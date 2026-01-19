/**
 * BestWeb Telegram Bot Widget Script
 */

(function() {
    'use strict';
    
    // Проверяем, что объект настроек доступен
    if (typeof bestwebTgBot === 'undefined') {
        return;
    }
    
    // Добавляем обработчик клика для аналитики (опционально)
    const widget = document.getElementById('bestweb-tg-bot-widget');
    if (widget) {
        const button = widget.querySelector('.bestweb-tg-bot-button');
        if (button) {
            button.addEventListener('click', function(e) {
                // Можно добавить отправку события в Google Analytics или другую аналитику
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'click', {
                        'event_category': 'Telegram Bot',
                        'event_label': 'Widget Button Click',
                        'value': 1
                    });
                }
                
                // Или для других систем аналитики
                if (typeof dataLayer !== 'undefined') {
                    dataLayer.push({
                        'event': 'telegram_bot_click',
                        'bot_username': bestwebTgBot.username
                    });
                }
            });
        }
    }
    
    // Показываем виджет с небольшой задержкой для плавного появления
    if (widget) {
        widget.style.opacity = '0';
        setTimeout(function() {
            widget.style.transition = 'opacity 0.3s ease';
            widget.style.opacity = '1';
        }, 500);
    }
})();
