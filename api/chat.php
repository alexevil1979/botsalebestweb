<?php
/**
 * API endpoint для встроенного чата на сайте
 * Принимает сообщения от пользователя и отправляет их в Telegram бота
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;
use Core\Redis;
use Core\TelegramAPI;
use Core\User;
use Core\Dialog;
use Core\StateMachine;
use Core\LLM;
use Core\Translator;

try {
    Config::load(__DIR__ . '/../.env');
    Translator::loadTranslations();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($method === 'POST') {
        // Отправка сообщения
        handleSendMessage($input);
    } elseif ($method === 'GET') {
        // Получение новых сообщений
        handleGetMessages($input);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Обработка отправки сообщения
 */
function handleSendMessage(array $data): void
{
    $sessionId = $data['session_id'] ?? generateSessionId();
    $message = trim($data['message'] ?? '');
    $userName = $data['name'] ?? 'Website User';
    $userEmail = $data['email'] ?? '';
    $userPhone = $data['phone'] ?? '';
    $lang = $data['lang'] ?? 'ru';
    
    if (empty($message)) {
        http_response_code(400);
        echo json_encode(['error' => 'Message is required']);
        return;
    }
    
    // Создаем или получаем виртуального пользователя для веб-чата
    $virtualUserId = getOrCreateWebUser($sessionId, $userName, $userEmail, $userPhone, $lang);
    
    // Создаем или получаем диалог
    // Используем отрицательный chat_id для веб-чата (чтобы не конфликтовать с Telegram)
    $webChatId = -1000000000 - abs(crc32($sessionId)); // Уникальный отрицательный ID
    $dialog = Dialog::getOrCreate($virtualUserId, $webChatId);
    $dialogId = $dialog['id'];
    
    // Сохраняем входящее сообщение
    Dialog::saveMessage($dialogId, $webChatId, $virtualUserId, 'in', $message);
    
    // Получаем текущий шаг диалога
    $currentStep = $dialog['current_step'] ?? StateMachine::getInitialState();
    
    // Получаем состояние из Redis
    $stateKey = "dialog:{$dialogId}";
    $stateData = Redis::get($stateKey) ?? [];
    $stateData['current_step'] = $currentStep;
    $stateData['language'] = $lang;
    $stateData['session_id'] = $sessionId;
    $stateData['is_web_chat'] = true; // Флаг для веб-чата
    
    // Обрабатываем сообщение через веб-чат обработчик
    $webChatHandler = new \Bot\WebChatHandler($webChatId, $virtualUserId, $dialogId);
    $response = $webChatHandler->processMessage($currentStep, $message, $stateData, $lang);
    
    // Сохраняем состояние
    Redis::set($stateKey, $stateData, 86400);
    
    // Возвращаем ответ
    echo json_encode([
        'success' => true,
        'session_id' => $sessionId,
        'response' => $response['text'],
        'keyboard' => $response['keyboard'] ?? null,
        'step' => $stateData['current_step'],
    ]);
}

/**
 * Обработка получения новых сообщений (polling)
 */
function handleGetMessages(array $data): void
{
    $sessionId = $data['session_id'] ?? '';
    $lastMessageId = (int)($data['last_message_id'] ?? 0);
    
    if (empty($sessionId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Session ID is required']);
        return;
    }
    
    // Получаем виртуального пользователя
    $user = Database::fetch(
        "SELECT id FROM users WHERE telegram_id = ? AND username = ?",
        [-1000000000, 'web_chat_' . $sessionId]
    );
    
    if (!$user) {
        echo json_encode(['messages' => []]);
        return;
    }
    
    $webChatId = -1000000000 - abs(crc32($sessionId));
    $dialog = Database::fetch(
        "SELECT id FROM dialogs WHERE user_id = ? AND telegram_chat_id = ?",
        [$user['id'], $webChatId]
    );
    
    if (!$dialog) {
        echo json_encode(['messages' => []]);
        return;
    }
    
    // Получаем новые сообщения
    $messages = Database::fetchAll(
        "SELECT id, direction, message_text, created_at 
         FROM messages 
         WHERE dialog_id = ? AND id > ? AND direction = 'out'
         ORDER BY created_at ASC
         LIMIT 50",
        [$dialog['id'], $lastMessageId]
    );
    
    echo json_encode([
        'messages' => $messages,
        'last_message_id' => !empty($messages) ? end($messages)['id'] : $lastMessageId,
    ]);
}

/**
 * Создать или получить виртуального пользователя для веб-чата
 */
function getOrCreateWebUser(string $sessionId, string $name, string $email, string $phone, string $lang): int
{
    // Ищем существующего пользователя
    $user = Database::fetch(
        "SELECT id FROM users WHERE telegram_id = ? AND username = ?",
        [-1000000000, 'web_chat_' . $sessionId]
    );
    
    if ($user) {
        // Обновляем информацию
        Database::execute(
            "UPDATE users SET first_name = ?, preferred_language = ? WHERE id = ?",
            [$name, $lang, $user['id']]
        );
        return $user['id'];
    }
    
    // Создаем нового пользователя
    Database::execute(
        "INSERT INTO users (telegram_id, username, first_name, preferred_language, created_at) 
         VALUES (?, ?, ?, ?, NOW())",
        [-1000000000, 'web_chat_' . $sessionId, $name, $lang]
    );
    
    return (int)Database::lastInsertId();
}

/**
 * Обработать сообщение от веб-чата
 */
function processWebChatMessage(
    \Bot\WebhookHandler $handler,
    int $webChatId,
    int $userId,
    int $dialogId,
    string $currentStep,
    string $message,
    array &$stateData,
    string $lang
): array {
    // Создаем обработчик веб-чата
    $webChatHandler = new \Bot\WebChatHandler($webChatId, $userId, $dialogId);
    
    // Обрабатываем сообщение
    $response = $webChatHandler->processMessage($currentStep, $message, $stateData, $lang);
    
    return $response;
}

/**
 * Генерация уникального session ID
 */
function generateSessionId(): string
{
    return 'web_' . bin2hex(random_bytes(16));
}
