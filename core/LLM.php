<?php

namespace Core;

class LLM
{
    private bool $enabled;
    private string $provider;

    public function __construct()
    {
        $this->enabled = Config::get('LLM_ENABLED', 'false') === 'true';
        $this->provider = Config::get('LLM_PROVIDER', 'yandex');
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function improveText(string $template, array $context = [], string $lang = 'ru'): string
    {
        if (!$this->enabled) {
            return $this->replacePlaceholders($template, $context);
        }

        try {
            if ($this->provider === 'yandex') {
                return $this->yandexGPT($template, $context, $lang);
            } elseif ($this->provider === 'gigachat') {
                return $this->gigaChat($template, $context, $lang);
            }
        } catch (\Exception $e) {
            error_log("LLM error: " . $e->getMessage());
        }

        return $this->replacePlaceholders($template, $context);
    }

    private function yandexGPT(string $template, array $context, string $lang = 'ru'): string
    {
        $apiKey = Config::get('YANDEX_API_KEY');
        $folderId = Config::get('YANDEX_FOLDER_ID');

        if (!$apiKey || !$folderId) {
            return $this->replacePlaceholders($template, $context);
        }

        $langPrompts = [
            'ru' => "Улучши следующий текст для Telegram-бота, который работает как менеджер по продажам веб-студии. Текст должен быть дружелюбным, профессиональным и вести к следующему шагу воронки продаж. Не упоминай, что ты ИИ. Сохрани смысл и структуру.",
            'en' => "Improve the following text for a Telegram bot that works as a web studio sales manager. The text should be friendly, professional and lead to the next step in the sales funnel. Don't mention that you're an AI. Preserve meaning and structure.",
            'th' => "ปรับปรุงข้อความต่อไปนี้สำหรับ Telegram bot ที่ทำงานเป็นผู้จัดการฝ่ายขายของสตูดิโอเว็บ ข้อความควรเป็นมิตร เป็นมืออาชีพ และนำไปสู่ขั้นตอนถัดไปในกรวยการขาย อย่าพูดว่าคุณเป็น AI รักษาความหมายและโครงสร้าง",
            'zh' => "改进以下 Telegram 机器人的文本，该机器人作为网络工作室销售经理工作。文本应该友好、专业，并引导到销售漏斗的下一步。不要提及你是 AI。保留含义和结构。",
        ];

        $systemPrompts = [
            'ru' => 'Ты профессиональный менеджер по продажам веб-студии. Пиши кратко, по делу, веди к действию.',
            'en' => 'You are a professional web studio sales manager. Write briefly, to the point, lead to action.',
            'th' => 'คุณเป็นผู้จัดการฝ่ายขายของสตูดิโอเว็บมืออาชีพ เขียนสั้นๆ ตรงประเด็น นำไปสู่การกระทำ',
            'zh' => '你是一名专业的网络工作室销售经理。写得简短、切题、引导行动。',
        ];

        $systemPrompt = $systemPrompts[$lang] ?? $systemPrompts['ru'];
        $userPrompt = $langPrompts[$lang] ?? $langPrompts['ru'];

        $prompt = "{$userPrompt}\n\n"
            . "Шаблон: {$template}\n\n"
            . "Контекст: " . json_encode($context, JSON_UNESCAPED_UNICODE);

        $data = [
            'modelUri' => "gpt://{$folderId}/yandexgpt/latest",
            'completionOptions' => [
                'stream' => false,
                'temperature' => 0.6,
                'maxTokens' => 500,
            ],
            'messages' => [
                [
                    'role' => 'system',
                    'text' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'text' => $prompt,
                ],
            ],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Api-Key {$apiKey}",
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if (isset($result['result']['alternatives'][0]['message']['text'])) {
                return $result['result']['alternatives'][0]['message']['text'];
            }
        }

        return $this->replacePlaceholders($template, $context);
    }

    private function gigaChat(string $template, array $context, string $lang = 'ru'): string
    {
        $clientId = Config::get('GIGACHAT_CLIENT_ID');
        $clientSecret = Config::get('GIGACHAT_CLIENT_SECRET');
        $scope = Config::get('GIGACHAT_SCOPE', 'https://gigachat.dev/v1');

        if (!$clientId || !$clientSecret) {
            return $this->replacePlaceholders($template, $context);
        }

        // Get access token
        $token = $this->getGigaChatToken($clientId, $clientSecret, $scope);
        if (!$token) {
            return $this->replacePlaceholders($template, $context);
        }

        $langPrompts = [
            'ru' => "Улучши следующий текст для Telegram-бота, который работает как менеджер по продажам веб-студии. Текст должен быть дружелюбным, профессиональным и вести к следующему шагу воронки продаж. Не упоминай, что ты ИИ. Сохрани смысл и структуру.",
            'en' => "Improve the following text for a Telegram bot that works as a web studio sales manager. The text should be friendly, professional and lead to the next step in the sales funnel. Don't mention that you're an AI. Preserve meaning and structure.",
            'th' => "ปรับปรุงข้อความต่อไปนี้สำหรับ Telegram bot ที่ทำงานเป็นผู้จัดการฝ่ายขายของสตูดิโอเว็บ ข้อความควรเป็นมิตร เป็นมืออาชีพ และนำไปสู่ขั้นตอนถัดไปในกรวยการขาย อย่าพูดว่าคุณเป็น AI รักษาความหมายและโครงสร้าง",
            'zh' => "改进以下 Telegram 机器人的文本，该机器人作为网络工作室销售经理工作。文本应该友好、专业，并引导到销售漏斗的下一步。不要提及你是 AI。保留含义和结构。",
        ];

        $systemPrompts = [
            'ru' => 'Ты профессиональный менеджер по продажам веб-студии. Пиши кратко, по делу, веди к действию.',
            'en' => 'You are a professional web studio sales manager. Write briefly, to the point, lead to action.',
            'th' => 'คุณเป็นผู้จัดการฝ่ายขายของสตูดิโอเว็บมืออาชีพ เขียนสั้นๆ ตรงประเด็น นำไปสู่การกระทำ',
            'zh' => '你是一名专业的网络工作室销售经理。写得简短、切题、引导行动。',
        ];

        $systemPrompt = $systemPrompts[$lang] ?? $systemPrompts['ru'];
        $userPrompt = $langPrompts[$lang] ?? $langPrompts['ru'];

        $prompt = "{$userPrompt}\n\n"
            . "Шаблон: {$template}\n\n"
            . "Контекст: " . json_encode($context, JSON_UNESCAPED_UNICODE);

        $data = [
            'model' => 'GigaChat',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.6,
            'max_tokens' => 500,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://gigachat.dev/v1/chat/completions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$token}",
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                return $result['choices'][0]['message']['content'];
            }
        }

        return $this->replacePlaceholders($template, $context);
    }

    private function getGigaChatToken(string $clientId, string $clientSecret, string $scope): ?string
    {
        $data = [
            'grant_type' => 'client_credentials',
            'scope' => $scope,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ngw.devices.sberbank.ru:9443/api/v2/oauth');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$clientId}:{$clientSecret}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            return $result['access_token'] ?? null;
        }

        return null;
    }

    private function replacePlaceholders(string $template, array $context): string
    {
        foreach ($context as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
}
