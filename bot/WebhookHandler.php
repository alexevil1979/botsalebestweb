<?php

namespace Bot;

use Core\Config;
use Core\Database;
use Core\Redis;
use Core\TelegramAPI;
use Core\User;
use Core\Dialog;
use Core\StateMachine;
use Core\LLM;
use Core\Service;
use Core\Lead;
use Core\Translator;

class WebhookHandler
{
    private TelegramAPI $telegram;
    private LLM $llm;

    public function __construct()
    {
        $this->telegram = new TelegramAPI();
        $this->llm = new LLM();
    }

    public function handle(): void
    {
        // Verify webhook secret
        $secret = Config::get('TELEGRAM_WEBHOOK_SECRET', '');
        if ($secret) {
            $headers = getallheaders();
            $headerSecret = $headers['X-Telegram-Bot-Api-Secret-Token'] ?? '';
            if ($headerSecret !== $secret) {
                http_response_code(403);
                echo "Forbidden";
                return;
            }
        }

        $input = file_get_contents('php://input');
        $update = json_decode($input, true);

        if (!$update) {
            http_response_code(400);
            echo "Invalid JSON";
            return;
        }

        // Anti-flood check
        if (isset($update['message']['from']['id'])) {
            $chatId = $update['message']['chat']['id'];
            $floodKey = "flood:{$chatId}";
            if (Redis::exists($floodKey)) {
                http_response_code(200);
                echo "OK";
                return;
            }
            Redis::set($floodKey, true, 2); // 2 seconds cooldown
        }

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        }

        http_response_code(200);
        echo "OK";
    }

    private function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $chatType = $message['chat']['type'] ?? 'private';
        $text = $message['text'] ?? '';
        $from = $message['from'];

        // Check for contact
        $contact = $message['contact'] ?? null;

        // Get or create user
        $user = User::getOrCreate($from);
        $userId = $user['id'];
        $userLang = $user['preferred_language'] ?? Translator::normalizeLang($from['language_code'] ?? 'ru');

        // Get or create dialog
        $dialog = Dialog::getOrCreate($userId, $chatId);
        $dialogId = $dialog['id'];
        
        // Handle /start command - reset to initial state
        if ($text === '/start') {
            $initialStep = StateMachine::getInitialState();
            Dialog::updateStep($dialogId, $initialStep);
            $currentStep = $initialStep;
            $stateKey = "dialog:{$dialogId}";
            $stateData = ['current_step' => $initialStep, 'language' => $userLang];
            $this->processStep($chatId, $userId, $dialogId, $initialStep, '', $stateData, $userLang);
            Redis::set($stateKey, $stateData, 86400);
            return;
        }
        
        $currentStep = $dialog['current_step'] ?? StateMachine::getInitialState();

        // Get dialog state from Redis
        $stateKey = "dialog:{$dialogId}";
        $stateData = Redis::get($stateKey) ?? [];
        $stateData['current_step'] = $currentStep;
        $stateData['language'] = $userLang;

        // Store contact if present
        if ($contact) {
            $stateData['contact'] = $contact;
            $text = ''; // Clear text when contact is provided
        }

        // Save incoming message
        $messageText = $contact ? Translator::get('contact_received', $userLang, ['phone' => $contact['phone_number'] ?? 'N/A']) : $text;
        Dialog::saveMessage(
            $dialogId,
            $chatId,
            $userId,
            'in',
            $messageText,
            $message['message_id'] ?? null
        );

        // Store chat type in state for later use
        $stateData['chat_type'] = $chatType;

        // Process message based on current step
        $this->processStep($chatId, $userId, $dialogId, $currentStep, $text, $stateData, $userLang, $chatType);

        // Update state in Redis
        Redis::set($stateKey, $stateData, 86400); // 24 hours
    }

    private function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $chatType = $callbackQuery['message']['chat']['type'] ?? 'private';
        $data = $callbackQuery['data'] ?? '';
        $from = $callbackQuery['from'];

        $user = User::getOrCreate($from);
        $userId = $user['id'];
        $userLang = $user['preferred_language'] ?? Translator::normalizeLang($from['language_code'] ?? 'ru');
        $dialog = Dialog::getOrCreate($userId, $chatId);
        $dialogId = $dialog['id'];
        $currentStep = $dialog['current_step'] ?? StateMachine::getInitialState();

        $stateKey = "dialog:{$dialogId}";
        $stateData = Redis::get($stateKey) ?? [];
        $stateData['current_step'] = $currentStep;
        $stateData['language'] = $userLang;
        $stateData['chat_type'] = $chatType;

        // Process callback
        if ($data === 'start_dialog') {
            // Начать новый диалог - переходим к следующему шагу после приветствия
            // Если уже в greeting, переходим к task_definition
            // Если в другом состоянии, сбрасываем к greeting и сразу переходим к task_definition
            if ($currentStep === 'greeting') {
                // Уже в приветствии, просто переходим к следующему шагу
                $nextStep = StateMachine::getNextState('greeting');
                Dialog::updateStep($dialogId, $nextStep);
                $stateData['current_step'] = $nextStep;
                $this->processStep($chatId, $userId, $dialogId, $nextStep, '', $stateData, $userLang, $chatType);
            } else {
                // В другом состоянии, сбрасываем и переходим к task_definition
                $nextStep = 'task_definition';
                Dialog::updateStep($dialogId, $nextStep);
                $stateData['current_step'] = $nextStep;
                // Очищаем данные диалога
                $stateData = ['current_step' => $nextStep, 'language' => $userLang, 'chat_type' => $chatType];
                // Переходим к следующему шагу без показа приветствия
                $this->processStep($chatId, $userId, $dialogId, $nextStep, '', $stateData, $userLang, $chatType);
            }
        } elseif (strpos($data, 'service_') === 0) {
            $serviceId = (int)str_replace('service_', '', $data);
            $stateData['selected_service_id'] = $serviceId;
            $this->processStep($chatId, $userId, $dialogId, $currentStep, '', $stateData, $userLang, $chatType);
        }

        // Answer callback query
        $this->telegram->answerCallbackQuery($callbackQuery['id']);

        Redis::set($stateKey, $stateData, 86400);
    }

    private function processStep(int $chatId, int $userId, int $dialogId, string $step, string $userText, array &$stateData, string $lang, string $chatType = 'private'): void
    {
        switch ($step) {
            case 'greeting':
                $this->handleGreeting($chatId, $userId, $dialogId, $stateData, $lang);
                break;
            case 'task_definition':
                $this->handleTaskDefinition($chatId, $userId, $dialogId, $userText, $stateData, $lang);
                break;
            case 'clarification':
                $this->handleClarification($chatId, $userId, $dialogId, $userText, $stateData, $lang);
                break;
            case 'service_selection':
                $this->handleServiceSelection($chatId, $userId, $dialogId, $userText, $stateData, $lang);
                break;
            case 'price_range':
                $this->handlePriceRange($chatId, $userId, $dialogId, $userText, $stateData, $lang, $chatType);
                break;
            case 'call_to_action':
                $this->handleCallToAction($chatId, $userId, $dialogId, $userText, $stateData, $lang, $chatType);
                break;
            case 'contact_collection':
                $this->handleContactCollection($chatId, $userId, $dialogId, $userText, $stateData, $lang);
                break;
        }
    }

    private function handleGreeting(int $chatId, int $userId, int $dialogId, array &$stateData, string $lang): void
    {
        $template = Translator::get('greeting', $lang);
        $text = $this->llm->improveText($template, [], $lang);

        // Добавляем inline keyboard с кнопкой "Старт"
        $startButtonText = Translator::get('button_start', $lang);
        $inlineKeyboard = [
            [
                [
                    'text' => $startButtonText,
                    'callback_data' => 'start_dialog'
                ]
            ]
        ];

        $this->telegram->sendMessageWithInlineKeyboard($chatId, $text, $inlineKeyboard);
        Dialog::saveMessage($dialogId, $chatId, $userId, 'out', $text);

        $nextStep = StateMachine::getNextState('greeting');
        Dialog::updateStep($dialogId, $nextStep);
        $stateData['current_step'] = $nextStep;
    }

    private function handleTaskDefinition(int $chatId, int $userId, int $dialogId, string $userText, array &$stateData, string $lang): void
    {
        if (empty($userText)) {
            // Показываем вопрос, если пользователь только перешел к этому шагу
            $template = Translator::get('task_definition_question', $lang);
            $text = $this->llm->improveText($template, [], $lang);
            $this->telegram->sendMessage($chatId, $text);
            Dialog::saveMessage($dialogId, $chatId, $userId, 'out', $text);
            return;
        }

        $stateData['task_description'] = $userText;

        $template = Translator::get('task_definition_response', $lang, ['task_description' => $userText]);
        $text = $this->llm->improveText($template, ['task_description' => $userText], $lang);

        $this->telegram->sendMessage($chatId, $text);
        Dialog::saveMessage($dialogId, $chatId, $userId, 'out', $text);

        $nextStep = StateMachine::getNextState('task_definition');
        Dialog::updateStep($dialogId, $nextStep);
        $stateData['current_step'] = $nextStep;
    }

    private function handleClarification(int $chatId, int $userId, int $dialogId, string $userText, array &$stateData, string $lang): void
    {
        if (empty($userText)) {
            return;
        }

        if (!isset($stateData['clarifications'])) {
            $stateData['clarifications'] = [];
        }
        $stateData['clarifications'][] = $userText;

        $services = Service::getAll();
        if (empty($services)) {
            $text = Translator::get('clarification_no_services', $lang);
            $this->telegram->sendMessage($chatId, $text);
            Dialog::saveMessage($dialogId, $chatId, $userId, 'out', $text);
            $nextStep = 'contact_collection';
            Dialog::updateStep($dialogId, $nextStep);
            $stateData['current_step'] = $nextStep;
        } else {
            $keyboard = [];
            foreach ($services as $service) {
                $keyboard[] = [['text' => $service['name'], 'callback_data' => 'service_' . $service['id']]];
            }

            $template = Translator::get('clarification_services', $lang);
            $text = $this->llm->improveText($template, [], $lang);

            $this->telegram->sendMessageWithKeyboard($chatId, $text, $keyboard);
            Dialog::saveMessage($dialogId, $chatId, $userId, 'out', $text);

            $nextStep = StateMachine::getNextState('clarification');
            Dialog::updateStep($dialogId, $nextStep);
            $stateData['current_step'] = $nextStep;
        }
    }

    private function handleServiceSelection(int $chatId, int $userId, int $dialogId, string $userText, array &$stateData, string $lang): void
    {
        $serviceId = $stateData['selected_service_id'] ?? null;

        if (!$serviceId) {
            // Try to find service by text
            $services = Service::getAll();
            foreach ($services as $service) {
                if (stripos($userText, $service['name']) !== false) {
                    $serviceId = $service['id'];
                    $stateData['selected_service_id'] = $serviceId;
                    break;
                }
            }
        }

        if ($serviceId) {
            $service = Service::getById($serviceId);
            if ($service) {
                $priceFrom = $service['price_from'] ? number_format($service['price_from'], 0, ',', ' ') : Translator::get('price_from', $lang);
                $priceTo = $service['price_to'] ? number_format($service['price_to'], 0, ',', ' ') : '';

                $template = Translator::get('service_selected', $lang, [
                    'service_name' => $service['name'],
                    'price_from' => $priceFrom,
                    'price_to' => $priceTo,
                    'description' => $service['description'] ?? '',
                ]);
                $text = $this->llm->improveText($template, [
                    'service_name' => $service['name'],
                    'price_from' => $priceFrom,
                    'price_to' => $priceTo,
                    'description' => $service['description'] ?? '',
                ], $lang);

                $this->telegram->sendMessage($chatId, $text);
                Dialog::saveMessage($dialogId, $chatId, $userId, 'out', $text);

                $nextStep = StateMachine::getNextState('service_selection');
                Dialog::updateStep($dialogId, $nextStep);
                $stateData['current_step'] = $nextStep;
                return;
            }
        }

        // Fallback
        $text = Translator::get('service_fallback', $lang);
        $this->telegram->sendMessage($chatId, $text);
        Dialog::saveMessage($dialogId, $chatId, $userId, 'out', $text);

        $nextStep = StateMachine::getNextState('service_selection');
        Dialog::updateStep($dialogId, $nextStep);
        $stateData['current_step'] = $nextStep;
    }

    private function handlePriceRange(int $chatId, int $userId, int $dialogId, string $userText, array &$stateData, string $lang, string $chatType = 'private'): void
    {
        // Extract numbers from text
        preg_match_all('/\d+/', $userText, $matches);
        $numbers = $matches[0] ?? [];
        if (!empty($numbers)) {
            $budget = (int)implode('', $numbers);
            $stateData['budget'] = $budget;
        }

        $template = Translator::get('price_range', $lang);
        $text = $this->llm->improveText($template, [], $lang);

        // Кнопка с request_contact работает только в приватных чатах
        $keyboard = [];
        if ($chatType === 'private') {
            $keyboard = [
                [[
                    'text' => Translator::get('button_phone', $lang),
                    'request_contact' => true
                ]],
                [[
                    'text' => Translator::get('button_email', $lang)
                ]],
            ];
        } else {
            // В группах/каналах только кнопка email
            $keyboard = [
                [[
                    'text' => Translator::get('button_email', $lang)
                ]],
            ];
            // Добавляем текст о том, что телефон можно написать вручную
            $text .= "\n\n" . Translator::get('contact_group_note', $lang);
        }

        if (!empty($keyboard)) {
            $this->telegram->sendMessageWithKeyboard($chatId, $text, $keyboard);
        } else {
            $this->telegram->sendMessage($chatId, $text);
        }
        Dialog::saveMessage($dialogId, $chatId, $userId, 'out', $text);

        $nextStep = StateMachine::getNextState('price_range');
        Dialog::updateStep($dialogId, $nextStep);
        $stateData['current_step'] = $nextStep;
    }

    private function handleCallToAction(int $chatId, int $userId, int $dialogId, string $userText, array &$stateData, string $lang, string $chatType = 'private'): void
    {
        $template = Translator::get('call_to_action', $lang);
        $text = $this->llm->improveText($template, [], $lang);

        // Кнопка с request_contact работает только в приватных чатах
        $keyboard = [];
        if ($chatType === 'private') {
            $keyboard = [
                [[
                    'text' => Translator::get('button_phone', $lang),
                    'request_contact' => true
                ]],
                [[
                    'text' => Translator::get('button_email', $lang)
                ]],
            ];
        } else {
            // В группах/каналах только кнопка email
            $keyboard = [
                [[
                    'text' => Translator::get('button_email', $lang)
                ]],
            ];
            // Добавляем текст о том, что телефон можно написать вручную
            $text .= "\n\n" . Translator::get('contact_group_note', $lang);
        }

        if (!empty($keyboard)) {
            $this->telegram->sendMessageWithKeyboard($chatId, $text, $keyboard);
        } else {
            $this->telegram->sendMessage($chatId, $text);
        }
        Dialog::saveMessage($dialogId, $chatId, $userId, 'out', $text);

        $nextStep = StateMachine::getNextState('call_to_action');
        Dialog::updateStep($dialogId, $nextStep);
        $stateData['current_step'] = $nextStep;
    }

    private function handleContactCollection(int $chatId, int $userId, int $dialogId, string $userText, array &$stateData, string $lang): void
    {
        $phone = null;
        $email = null;
        $name = null;

        // Check if it's a contact from state
        if (isset($stateData['contact'])) {
            $contact = $stateData['contact'];
            $phone = $contact['phone_number'] ?? null;
            $name = $contact['first_name'] ?? null;
        } elseif (!empty($userText)) {
            // Try to extract email or phone from text
            if (filter_var($userText, FILTER_VALIDATE_EMAIL)) {
                $email = $userText;
            } elseif (preg_match('/[\d\s\+\-\(\)]+/', $userText)) {
                $phone = preg_replace('/[^\d\+]/', '', $userText);
                if (strlen($phone) < 10) {
                    $phone = null; // Too short to be a phone
                }
            }
        }

        if (!$phone && !$email) {
            $text = Translator::get('contact_invalid', $lang);
            $this->telegram->sendMessage($chatId, $text);
            Dialog::saveMessage($dialogId, $chatId, $userId, 'out', $text);
            return;
        }

        // Create lead
        $leadData = [
            'user_id' => $userId,
            'dialog_id' => $dialogId,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'service_id' => $stateData['selected_service_id'] ?? null,
            'budget_from' => $stateData['budget'] ?? null,
            'budget_to' => $stateData['budget'] ?? null,
            'task_description' => $stateData['task_description'] ?? null,
        ];

        $leadId = Lead::create($leadData);

        $template = Translator::get('contact_success', $lang, ['lead_id' => $leadId]);
        $text = $this->llm->improveText($template, ['lead_id' => $leadId], $lang);

        $this->telegram->removeKeyboard($chatId, $text);
        Dialog::saveMessage($dialogId, $chatId, $userId, 'out', $text);

        // Complete dialog
        Dialog::complete($dialogId);
        $nextStep = StateMachine::getNextState('contact_collection');
        Dialog::updateStep($dialogId, $nextStep);

        // Log event
        Database::execute(
            "INSERT INTO events (user_id, dialog_id, event_type, event_data) VALUES (?, ?, 'lead_created', ?)",
            [$userId, $dialogId, json_encode(['lead_id' => $leadId])]
        );
    }
}
