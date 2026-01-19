<?php

namespace Core;

class TelegramAPI
{
    private string $token;
    private string $apiUrl;

    public function __construct()
    {
        $this->token = Config::get('TELEGRAM_BOT_TOKEN');
        if (!$this->token) {
            throw new \RuntimeException("TELEGRAM_BOT_TOKEN not configured");
        }
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}/";
    }

    public function sendMessage(int|string $chatId, string $text, array $options = []): ?array
    {
        $data = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        return $this->request('sendMessage', $data);
    }

    public function sendMessageWithKeyboard(int $chatId, string $text, array $keyboard, bool $resize = true, bool $oneTime = false): ?array
    {
        $replyMarkup = [
            'keyboard' => $keyboard,
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $oneTime,
        ];

        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode($replyMarkup),
        ]);
    }

    public function removeKeyboard(int $chatId, string $text): ?array
    {
        $replyMarkup = [
            'remove_keyboard' => true,
        ];

        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode($replyMarkup),
        ]);
    }

    public function editMessage(int $chatId, int $messageId, string $text, array $options = []): ?array
    {
        $data = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        return $this->request('editMessageText', $data);
    }

    public function deleteMessage(int $chatId, int $messageId): ?array
    {
        return $this->request('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    public function sendMessageWithInlineKeyboard(int $chatId, string $text, array $inlineKeyboard): ?array
    {
        $replyMarkup = [
            'inline_keyboard' => $inlineKeyboard,
        ];

        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode($replyMarkup),
        ]);
    }

    public function answerCallbackQuery(string $callbackQueryId, array $options = []): ?array
    {
        $data = array_merge([
            'callback_query_id' => $callbackQueryId,
        ], $options);

        return $this->request('answerCallbackQuery', $data);
    }

    public function setWebhook(string $url, string $secret = ''): ?array
    {
        $data = ['url' => $url];
        if ($secret) {
            $data['secret_token'] = $secret;
        }
        return $this->request('setWebhook', $data);
    }

    public function deleteWebhook(): ?array
    {
        return $this->request('deleteWebhook');
    }

    public function getWebhookInfo(): ?array
    {
        return $this->request('getWebhookInfo');
    }

    /**
     * Получить информацию о чате по username или chat_id
     */
    public function getChat(string|int $chatId): ?array
    {
        $result = $this->request('getChat', ['chat_id' => $chatId]);
        return $result;
    }

    /**
     * Отправить сообщение менеджеру
     * Поддерживает как chat_id (число), так и username (строка с @)
     */
    public function sendMessageToManager(string|int $managerChatId, string $text, array $options = []): ?array
    {
        return $this->sendMessage($managerChatId, $text, $options);
    }

    private function request(string $method, array $data = []): ?array
    {
        $url = $this->apiUrl . $method;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Telegram API error: {$error}");
            return null;
        }

        if ($httpCode !== 200) {
            error_log("Telegram API HTTP {$httpCode}: {$response}");
            return null;
        }

        if (empty($response)) {
            error_log("Telegram API error: Empty response");
            return null;
        }

        $result = json_decode($response, true);
        
        // json_decode returns null on failure, false is not possible for associative array
        if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log("Telegram API JSON decode error: " . json_last_error_msg() . " | Response: {$response}");
            return null;
        }

        if (!is_array($result) || !isset($result['ok']) || !$result['ok']) {
            $errorMsg = isset($result['description']) ? $result['description'] : 'Unknown error';
            error_log("Telegram API error: {$errorMsg} | Response: {$response}");
            return null;
        }

        // Проверяем, что result существует и является массивом
        if (!isset($result['result'])) {
            return null;
        }

        // Если result не массив, возвращаем null (может быть false, true, string и т.д.)
        if (!is_array($result['result'])) {
            return null;
        }

        return $result['result'];
    }
}
