<?php
/**
 * Обработчик веб-чата (без Telegram API)
 * Использует ту же логику, что и WebhookHandler, но возвращает ответы вместо отправки в Telegram
 */

namespace Bot;

use Core\Database;
use Core\Redis;
use Core\StateMachine;
use Core\LLM;
use Core\Service;
use Core\Lead;
use Core\Translator;

class WebChatHandler
{
    private int $chatId;
    private int $userId;
    private int $dialogId;
    private LLM $llm;

    public function __construct(int $chatId, int $userId, int $dialogId)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->dialogId = $dialogId;
        $this->llm = new LLM();
    }

    /**
     * Обработать сообщение и вернуть ответ
     */
    public function processMessage(string $currentStep, string $userText, array &$stateData, string $lang): array
    {
        // Обрабатываем шаг
        $this->processStep($currentStep, $userText, $stateData, $lang);
        
        // Получаем последнее сообщение бота
        $lastMessage = Database::fetch(
            "SELECT message_text FROM messages 
             WHERE dialog_id = ? AND direction = 'out' 
             ORDER BY created_at DESC LIMIT 1",
            [$this->dialogId]
        );
        
        $responseText = $lastMessage['message_text'] ?? Translator::get('greeting', $lang);
        
        // Получаем клавиатуру если есть
        $keyboard = $this->getKeyboardForStep($stateData['current_step'] ?? $currentStep, $lang);
        
        return [
            'text' => $responseText,
            'keyboard' => $keyboard,
        ];
    }

    private function processStep(string $step, string $userText, array &$stateData, string $lang): void
    {
        switch ($step) {
            case 'greeting':
                $this->handleGreeting($stateData, $lang);
                break;
            case 'task_definition':
                $this->handleTaskDefinition($userText, $stateData, $lang);
                break;
            case 'clarification':
                $this->handleClarification($userText, $stateData, $lang);
                break;
            case 'service_selection':
                $this->handleServiceSelection($userText, $stateData, $lang);
                break;
            case 'price_range':
                $this->handlePriceRange($userText, $stateData, $lang);
                break;
            case 'call_to_action':
                $this->handleCallToAction($userText, $stateData, $lang);
                break;
            case 'contact_collection':
                $this->handleContactCollection($userText, $stateData, $lang);
                break;
        }
    }

    private function handleGreeting(array &$stateData, string $lang): void
    {
        $template = Translator::get('greeting', $lang);
        $text = $this->llm->improveText($template, [], $lang);
        
        $this->saveResponse($text);
        
        $nextStep = StateMachine::getNextState('greeting');
        \Core\Dialog::updateStep($this->dialogId, $nextStep);
        $stateData['current_step'] = $nextStep;
    }

    private function handleTaskDefinition(string $userText, array &$stateData, string $lang): void
    {
        if (empty($userText)) {
            $template = Translator::get('task_definition_question', $lang);
            $text = $this->llm->improveText($template, [], $lang);
            $this->saveResponse($text);
            return;
        }
        
        $stateData['task_description'] = $userText;
        
        $template = Translator::get('task_definition_response', $lang, [
            'task_description' => $userText,
        ]);
        $text = $this->llm->improveText($template, ['task_description' => $userText], $lang);
        $this->saveResponse($text);
        
        $nextStep = StateMachine::getNextState('task_definition');
        \Core\Dialog::updateStep($this->dialogId, $nextStep);
        $stateData['current_step'] = $nextStep;
    }

    private function handleClarification(string $userText, array &$stateData, string $lang): void
    {
        // Получаем категории услуг
        $categories = Service::getCategories(true);
        
        if (empty($categories)) {
            $template = Translator::get('clarification_no_services', $lang);
            $text = $this->llm->improveText($template, [], $lang);
            $this->saveResponse($text);
            return;
        }
        
        // Проверяем, выбрана ли категория
        $selectedCategory = null;
        foreach ($categories as $category) {
            if (stripos($userText, $category['name']) !== false) {
                $selectedCategory = $category;
                break;
            }
        }
        
        if ($selectedCategory) {
            $stateData['selected_category_id'] = $selectedCategory['id'];
            $services = Service::getByCategory($selectedCategory['category'], true);
            
            if (empty($services)) {
                $template = Translator::get('category_no_services', $lang);
                $text = $this->llm->improveText($template, [], $lang);
            } else {
                $serviceList = [];
                foreach ($services as $service) {
                    $price = $service['is_hourly'] 
                        ? ($service['hourly_rate'] ? '$' . $service['hourly_rate'] . '/час' : '')
                        : ($service['price_from'] ? number_format($service['price_from'], 0, ',', ' ') . ' - ' . number_format($service['price_to'], 0, ',', ' ') . ' ₽' : '');
                    $serviceList[] = "• {$service['name']}" . ($price ? " ({$price})" : '');
                }
                
                $template = Translator::get('category_services', $lang);
                $text = $template . "\n\n" . implode("\n", $serviceList);
            }
            
            $this->saveResponse($text);
            $nextStep = StateMachine::getNextState('clarification');
            \Core\Dialog::updateStep($this->dialogId, $nextStep);
            $stateData['current_step'] = $nextStep;
        } else {
            // Показываем категории
            $template = Translator::get('clarification_services', $lang);
            $categoryList = [];
            foreach ($categories as $category) {
                $categoryList[] = "• {$category['name']}";
            }
            $text = $template . "\n\n" . implode("\n", $categoryList);
            $this->saveResponse($text);
        }
    }

    private function handleServiceSelection(string $userText, array &$stateData, string $lang): void
    {
        $serviceId = $stateData['selected_service_id'] ?? null;
        
        if (!$serviceId) {
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
                $priceFrom = $service['price_from'] ? number_format($service['price_from'], 0, ',', ' ') : '';
                $priceTo = $service['price_to'] ? number_format($service['price_to'], 0, ',', ' ') : '';
                $price = $service['is_hourly'] 
                    ? ($service['hourly_rate'] ? '$' . $service['hourly_rate'] . '/час' : '')
                    : ($priceFrom ? ($priceTo ? "{$priceFrom} - {$priceTo} ₽" : "от {$priceFrom} ₽") : '');
                
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
                
                $this->saveResponse($text);
                
                $nextStep = StateMachine::getNextState('service_selection');
                \Core\Dialog::updateStep($this->dialogId, $nextStep);
                $stateData['current_step'] = $nextStep;
            }
        } else {
            $template = Translator::get('service_fallback', $lang);
            $text = $this->llm->improveText($template, [], $lang);
            $this->saveResponse($text);
        }
    }

    private function handlePriceRange(string $userText, array &$stateData, string $lang): void
    {
        // Извлекаем бюджет из текста
        preg_match_all('/\d+/', $userText, $matches);
        if (!empty($matches[0])) {
            $budget = (int)implode('', $matches[0]);
            $stateData['budget'] = $budget;
        }
        
        $template = Translator::get('price_range', $lang);
        $text = $this->llm->improveText($template, [], $lang);
        $this->saveResponse($text);
        
        $nextStep = StateMachine::getNextState('price_range');
        \Core\Dialog::updateStep($this->dialogId, $nextStep);
        $stateData['current_step'] = $nextStep;
    }

    private function handleCallToAction(string $userText, array &$stateData, string $lang): void
    {
        $template = Translator::get('call_to_action', $lang);
        $text = $this->llm->improveText($template, [], $lang);
        $this->saveResponse($text);
        
        $nextStep = StateMachine::getNextState('call_to_action');
        \Core\Dialog::updateStep($this->dialogId, $nextStep);
        $stateData['current_step'] = $nextStep;
    }

    private function handleContactCollection(string $userText, array &$stateData, string $lang): void
    {
        // Извлекаем контакты из текста
        $phone = null;
        $email = null;
        
        // Поиск телефона
        if (preg_match('/(\+?\d{1,3}[\s\-]?)?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}/', $userText, $phoneMatches)) {
            $phone = preg_replace('/[\s\-\(\)]/', '', $phoneMatches[0]);
        }
        
        // Поиск email
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $userText, $emailMatches)) {
            $email = $emailMatches[0];
        }
        
        if (!$phone && !$email) {
            $template = Translator::get('contact_invalid', $lang);
            $text = $this->llm->improveText($template, [], $lang);
            $this->saveResponse($text);
            return;
        }
        
        // Создаем лид
        $leadData = [
            'user_id' => $this->userId,
            'dialog_id' => $this->dialogId,
            'name' => $stateData['user_name'] ?? 'Website User',
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
        $this->saveResponse($text);
        
        \Core\Dialog::complete($this->dialogId);
        $nextStep = StateMachine::getNextState('contact_collection');
        \Core\Dialog::updateStep($this->dialogId, $nextStep);
        $stateData['current_step'] = $nextStep;
    }

    private function saveResponse(string $text): void
    {
        \Core\Dialog::saveMessage($this->dialogId, $this->chatId, $this->userId, 'out', $text);
    }

    private function getKeyboardForStep(string $step, string $lang): ?array
    {
        // Для веб-чата упрощаем клавиатуру
        // Можно вернуть массив кнопок для отображения в интерфейсе
        return null;
    }
}
