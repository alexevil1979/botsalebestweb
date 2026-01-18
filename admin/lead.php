<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;
use Admin\Auth;
use Core\Lead;

Config::load(__DIR__ . '/../.env');
Auth::requireAuth();

$leadId = (int)($_GET['id'] ?? 0);

if (!$leadId) {
    header('Location: /admin/leads.php');
    exit;
}

$lead = Lead::getById($leadId);

if (!$lead) {
    header('Location: /admin/leads.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
    $status = $_POST['status'] ?? '';
    $notes = $_POST['notes'] ?? '';

    Lead::update($leadId, [
        'status' => $status,
        'notes' => $notes,
    ]);

    header('Location: /admin/lead.php?id=' . $leadId);
    exit;
}

$csrfToken = Auth::getCSRFToken();

include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <h1>📋 Лид #<?php echo $lead['id']; ?></h1>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div style="background: white; padding: 1.5rem; border-radius: 8px;">
            <h2>Информация о лиде</h2>
            <p><strong>Пользователь:</strong> <?php echo htmlspecialchars($lead['first_name'] ?? $lead['username'] ?? 'N/A'); ?></p>
            <p><strong>Telegram ID:</strong> <?php echo $lead['telegram_id']; ?></p>
            <p><strong>Телефон:</strong> <?php echo htmlspecialchars($lead['phone'] ?? 'Не указан'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($lead['email'] ?? 'Не указан'); ?></p>
            <p><strong>Услуга:</strong> <?php echo htmlspecialchars($lead['service_name'] ?? 'Не указана'); ?></p>
            <p><strong>Бюджет:</strong>
                <?php if ($lead['budget_from'] || $lead['budget_to']): ?>
                    <?php if ($lead['budget_from']): ?>
                        от <?php echo number_format($lead['budget_from'], 0, ',', ' '); ?> ₽
                    <?php endif; ?>
                    <?php if ($lead['budget_to']): ?>
                        до <?php echo number_format($lead['budget_to'], 0, ',', ' '); ?> ₽
                    <?php endif; ?>
                <?php else: ?>
                    Не указан
                <?php endif; ?>
            </p>
            <p><strong>Описание задачи:</strong></p>
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; margin-top: 0.5rem;">
                <?php echo nl2br(htmlspecialchars($lead['task_description'] ?? 'Не указано')); ?>
            </div>
            <p><strong>Дата создания:</strong> <?php echo date('d.m.Y H:i', strtotime($lead['created_at'])); ?></p>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 8px;">
            <h2>Управление</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="status">Статус</label>
                    <select name="status" id="status" required>
                        <option value="new" <?php echo $lead['status'] === 'new' ? 'selected' : ''; ?>>Новый</option>
                        <option value="contacted" <?php echo $lead['status'] === 'contacted' ? 'selected' : ''; ?>>Связались</option>
                        <option value="qualified" <?php echo $lead['status'] === 'qualified' ? 'selected' : ''; ?>>Квалифицирован</option>
                        <option value="converted" <?php echo $lead['status'] === 'converted' ? 'selected' : ''; ?>>Конвертирован</option>
                        <option value="lost" <?php echo $lead['status'] === 'lost' ? 'selected' : ''; ?>>Потерян</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Заметки</label>
                    <textarea name="notes" id="notes"><?php echo htmlspecialchars($lead['notes'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn">Сохранить</button>
            </form>
        </div>
    </div>

    <div style="margin-top: 1.5rem;">
        <a href="/admin/chat.php?id=<?php echo $lead['dialog_id']; ?>" class="btn">Просмотр диалога</a>
        <a href="/admin/leads.php" class="btn">← Назад к лидам</a>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
