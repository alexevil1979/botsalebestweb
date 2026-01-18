<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;
use Admin\Auth;

Config::load(__DIR__ . '/../.env');
Auth::requireAuth();

$statusFilter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($statusFilter) {
    $where[] = "d.status = ?";
    $params[] = $statusFilter;
}

$whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : '';

$total = Database::fetch("SELECT COUNT(*) as count FROM dialogs d {$whereClause}", $params)['count'] ?? 0;

$dialogs = Database::fetchAll(
    "SELECT d.*, u.telegram_id, u.username, u.first_name 
     FROM dialogs d 
     LEFT JOIN users u ON d.user_id = u.id 
     {$whereClause}
     ORDER BY d.updated_at DESC 
     LIMIT ? OFFSET ?",
    array_merge($params, [$perPage, $offset])
);

$totalPages = ceil($total / $perPage);

include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <h1>💬 Диалоги</h1>

    <div style="margin-bottom: 1.5rem;">
        <form method="GET" style="display: inline-block;">
            <select name="status" onchange="this.form.submit()">
                <option value="">Все статусы</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Активные</option>
                <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Завершённые</option>
                <option value="archived" <?php echo $statusFilter === 'archived' ? 'selected' : ''; ?>>Архивные</option>
            </select>
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Пользователь</th>
                <th>Текущий шаг</th>
                <th>Статус</th>
                <th>Создан</th>
                <th>Обновлён</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dialogs as $dialog): ?>
                <tr>
                    <td>#<?php echo $dialog['id']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($dialog['first_name'] ?? $dialog['username'] ?? 'N/A'); ?>
                        <?php if ($dialog['telegram_id']): ?>
                            <br><small>@<?php echo $dialog['telegram_id']; ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($dialog['current_step'] ?? 'N/A'); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $dialog['status']; ?>">
                            <?php echo htmlspecialchars($dialog['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($dialog['created_at'])); ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($dialog['updated_at'])); ?></td>
                    <td>
                        <a href="/admin/chat.php?id=<?php echo $dialog['id']; ?>" class="btn-small">Просмотр</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($dialogs)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem;">Нет диалогов</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?><?php echo $statusFilter ? '&status=' . urlencode($statusFilter) : ''; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
