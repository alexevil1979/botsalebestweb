<?php

namespace Core;

class Translator
{
    private static array $translations = [];
    private static string $defaultLang = 'ru';

    public static function loadTranslations(): void
    {
        $langDir = __DIR__ . '/../translations';
        $languages = ['ru', 'en', 'th', 'zh'];

        foreach ($languages as $lang) {
            $file = "{$langDir}/{$lang}.php";
            if (file_exists($file)) {
                self::$translations[$lang] = require $file;
            }
        }
    }

    public static function get(string $key, string $lang = null, array $params = []): string
    {
        if ($lang === null) {
            $lang = self::$defaultLang;
        }

        // Normalize language code
        $lang = self::normalizeLang($lang);

        // Get translation
        $translation = self::$translations[$lang][$key] ?? 
                      self::$translations[self::$defaultLang][$key] ?? 
                      $key;

        // Replace parameters
        foreach ($params as $paramKey => $paramValue) {
            $translation = str_replace('{' . $paramKey . '}', $paramValue, $translation);
        }

        return $translation;
    }

    public static function normalizeLang(string $lang): string
    {
        // Map language codes
        $langMap = [
            'ru' => 'ru',
            'ru-RU' => 'ru',
            'en' => 'en',
            'en-US' => 'en',
            'en-GB' => 'en',
            'th' => 'th',
            'th-TH' => 'th',
            'zh' => 'zh',
            'zh-CN' => 'zh',
            'zh-TW' => 'zh',
            'zh-Hans' => 'zh',
            'zh-Hant' => 'zh',
        ];

        $lang = strtolower($lang);
        return $langMap[$lang] ?? self::$defaultLang;
    }

    public static function getSupportedLanguages(): array
    {
        return ['ru' => 'Русский', 'en' => 'English', 'th' => 'ไทย', 'zh' => '中文'];
    }

    public static function setDefaultLang(string $lang): void
    {
        self::$defaultLang = self::normalizeLang($lang);
    }
}
