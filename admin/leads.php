<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Admin\Auth;
use Core\Lead;

Config::load(__DIR__ . '/../.env');
Auth::requireAuth();

$statusFilter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$filters = [];
if ($statusFilter) {
    $filters['status'] = $statusFilter;
}

$allLeads = Lead::getAll($filters);
$total = count($allLeads);
$leads = array_slice($allLeads, $offset, $perPage);
$totalPages = ceil($total / $perPage);

include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <h1>📋 Лиды</h1>

    <div style="margin-bottom: 1.5rem;">
        <form method="GET" style="display: inline-block;">
            <select name="status" onchange="this.form.submit()">
                <option value="">Все статусы</option>
                <option value="new" <?php echo $statusFilter === 'new' ? 'selected' : ''; ?>>Новые</option>
                <option value="contacted" <?php echo $statusFilter === 'contacted' ? 'selected' : ''; ?>>Связались</option>
                <option value="qualified" <?php echo $statusFilter === 'qualified' ? 'selected' : ''; ?>>Квалифицированы</option>
                <option value="converted" <?php echo $statusFilter === 'converted' ? 'selected' : ''; ?>>Конвертированы</option>
                <option value="lost" <?php echo $statusFilter === 'lost' ? 'selected' : ''; ?>>Потеряны</option>
            </select>
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Пользователь</th>
                <th>Контакт</th>
                <th>Услуга</th>
                <th>Бюджет</th>
                <th>Статус</th>
                <th>Дата</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leads as $lead): ?>
                <tr>
                    <td>#<?php echo $lead['id']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($lead['first_name'] ?? $lead['username'] ?? 'N/A'); ?>
                        <?php if ($lead['telegram_id']): ?>
                            <br><small>@<?php echo $lead['telegram_id']; ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($lead['phone']): ?>
                            📱 <?php echo htmlspecialchars($lead['phone']); ?><br>
                        <?php endif; ?>
                        <?php if ($lead['email']): ?>
                            ✉️ <?php echo htmlspecialchars($lead['email']); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($lead['service_name'] ?? 'Не указано'); ?></td>
                    <td>
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
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo $lead['status']; ?>">
                            <?php echo htmlspecialchars($lead['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($lead['created_at'])); ?></td>
                    <td>
                        <a href="/admin/lead.php?id=<?php echo $lead['id']; ?>" class="btn-small">Просмотр</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($leads)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem;">Нет лидов</td>
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
