<?php

namespace Core;

class Dialog
{
    public static function getOrCreate(int $userId, int $chatId): array
    {
        $dialog = Database::fetch(
            "SELECT * FROM dialogs WHERE user_id = ? AND telegram_chat_id = ? AND status = 'active' 
             ORDER BY created_at DESC LIMIT 1",
            [$userId, $chatId]
        );

        if (!$dialog) {
            $initialState = StateMachine::getInitialState();
            Database::execute(
                "INSERT INTO dialogs (user_id, telegram_chat_id, current_step, status) 
                 VALUES (?, ?, ?, 'active')",
                [$userId, $chatId, $initialState]
            );
            $dialogId = Database::lastInsertId();
            $dialog = Database::fetch("SELECT * FROM dialogs WHERE id = ?", [$dialogId]);
        }

        return $dialog;
    }

    public static function updateStep(int $dialogId, string $step): void
    {
        Database::execute(
            "UPDATE dialogs SET current_step = ?, updated_at = NOW() WHERE id = ?",
            [$step, $dialogId]
        );
    }

    public static function complete(int $dialogId): void
    {
        Database::execute(
            "UPDATE dialogs SET status = 'completed', updated_at = NOW() WHERE id = ?",
            [$dialogId]
        );
    }

    public static function saveMessage(int $dialogId, int $chatId, int $userId, string $direction, string $text, ?int $messageId = null): void
    {
        Database::execute(
            "INSERT INTO messages (dialog_id, chat_id, user_id, direction, text, message_id) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$dialogId, $chatId, $userId, $direction, $text, $messageId]
        );
    }

    public static function getMessages(int $dialogId): array
    {
        return Database::fetchAll(
            "SELECT * FROM messages WHERE dialog_id = ? ORDER BY created_at ASC",
            [$dialogId]
        );
    }
}
