<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;
use Admin\Auth;
use Core\Dialog;

Config::load(__DIR__ . '/../.env');
Auth::requireAuth();

$dialogId = (int)($_GET['id'] ?? 0);

if (!$dialogId) {
    header('Location: /admin/dialogs.php');
    exit;
}

$dialog = Database::fetch(
    "SELECT d.*, u.telegram_id, u.username, u.first_name 
     FROM dialogs d 
     LEFT JOIN users u ON d.user_id = u.id 
     WHERE d.id = ?",
    [$dialogId]
);

if (!$dialog) {
    header('Location: /admin/dialogs.php');
    exit;
}

$messages = Dialog::getMessages($dialogId);

include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <h1>💬 Диалог #<?php echo $dialog['id']; ?></h1>

    <div style="background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <p><strong>Пользователь:</strong> <?php echo htmlspecialchars($dialog['first_name'] ?? $dialog['username'] ?? 'N/A'); ?></p>
        <p><strong>Telegram ID:</strong> <?php echo $dialog['telegram_id']; ?></p>
        <p><strong>Текущий шаг:</strong> <?php echo htmlspecialchars($dialog['current_step'] ?? 'N/A'); ?></p>
        <p><strong>Статус:</strong> 
            <span class="status-badge status-<?php echo $dialog['status']; ?>">
                <?php echo htmlspecialchars($dialog['status']); ?>
            </span>
        </p>
        <p><strong>Создан:</strong> <?php echo date('d.m.Y H:i', strtotime($dialog['created_at'])); ?></p>
        <p><strong>Обновлён:</strong> <?php echo date('d.m.Y H:i', strtotime($dialog['updated_at'])); ?></p>
    </div>

    <div class="chat-container">
        <?php if (empty($messages)): ?>
            <p style="text-align: center; padding: 2rem; color: #999;">Нет сообщений</p>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="chat-message <?php echo $message['direction']; ?>">
                    <div><?php echo nl2br(htmlspecialchars($message['text'])); ?></div>
                    <div class="chat-message-time">
                        <?php echo date('d.m.Y H:i:s', strtotime($message['created_at'])); ?>
                        <?php if ($message['direction'] === 'in'): ?>
                            ← Входящее
                        <?php else: ?>
                            → Исходящее
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div style="margin-top: 1.5rem;">
        <a href="/admin/dialogs.php" class="btn">← Назад к диалогам</a>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
