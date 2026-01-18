<?php

namespace Core;

class User
{
    public static function getOrCreate(array $telegramUser): array
    {
        $telegramId = $telegramUser['id'];
        $user = Database::fetch(
            "SELECT * FROM users WHERE telegram_id = ?",
            [$telegramId]
        );

        if (!$user) {
            $lang = \Core\Translator::normalizeLang($telegramUser['language_code'] ?? 'ru');
            Database::execute(
                "INSERT INTO users (telegram_id, username, first_name, last_name, language_code, preferred_language) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $telegramId,
                    $telegramUser['username'] ?? null,
                    $telegramUser['first_name'] ?? null,
                    $telegramUser['last_name'] ?? null,
                    $telegramUser['language_code'] ?? null,
                    $lang,
                ]
            );
            $userId = Database::lastInsertId();
            $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        } else {
            // Update user info and language
            $lang = \Core\Translator::normalizeLang($telegramUser['language_code'] ?? $user['preferred_language'] ?? 'ru');
            Database::execute(
                "UPDATE users SET username = ?, first_name = ?, last_name = ?, language_code = ?, preferred_language = ? 
                 WHERE id = ?",
                [
                    $telegramUser['username'] ?? null,
                    $telegramUser['first_name'] ?? null,
                    $telegramUser['last_name'] ?? null,
                    $telegramUser['language_code'] ?? null,
                    $lang,
                    $user['id'],
                ]
            );
            $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$user['id']]);
        }

        return $user;
    }

    public static function getById(int $userId): ?array
    {
        return Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    }

    public static function getByTelegramId(int $telegramId): ?array
    {
        return Database::fetch("SELECT * FROM users WHERE telegram_id = ?", [$telegramId]);
    }
}
