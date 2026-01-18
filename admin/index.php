<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;
use Admin\Auth;
use Core\Lead;
use Core\Dialog;
use Core\User;

Config::load(__DIR__ . '/../.env');
Auth::requireAuth();

// Statistics
$stats = [
    'total_users' => Database::fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
    'active_dialogs' => Database::fetch("SELECT COUNT(*) as count FROM dialogs WHERE status = 'active'")['count'] ?? 0,
    'total_leads' => Database::fetch("SELECT COUNT(*) as count FROM leads")['count'] ?? 0,
    'new_leads' => Database::fetch("SELECT COUNT(*) as count FROM leads WHERE status = 'new'")['count'] ?? 0,
];

// Recent leads
$recentLeads = Lead::getAll(['date_from' => date('Y-m-d', strtotime('-7 days'))]);
$recentLeads = array_slice($recentLeads, 0, 10);

// Recent dialogs
$recentDialogs = Database::fetchAll(
    "SELECT d.*, u.telegram_id, u.first_name, u.username 
     FROM dialogs d 
     LEFT JOIN users u ON d.user_id = u.id 
     ORDER BY d.updated_at DESC 
     LIMIT 10"
);

include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <h1>📊 Дашборд</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_users'] ?></div>
            <div class="stat-label">Пользователей</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['active_dialogs'] ?></div>
            <div class="stat-label">Активных диалогов</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_leads'] ?></div>
            <div class="stat-label">Всего лидов</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['new_leads'] ?></div>
            <div class="stat-label">Новых лидов</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-section">
            <h2>🆕 Последние лиды</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Услуга</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLeads as $lead): ?>
                        <tr>
                            <td>#<?= $lead['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($lead['first_name'] ?? $lead['username'] ?? 'N/A') ?>
                                <?php if ($lead['telegram_id']): ?>
                                    <br><small>@<?= $lead['telegram_id'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($lead['service_name'] ?? 'Не указано') ?></td>
                            <td>
                                <span class="status-badge status-<?= $lead['status'] ?>">
                                    <?= htmlspecialchars($lead['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($lead['created_at'])) ?></td>
                            <td>
                                <a href="/admin/lead.php?id=<?= $lead['id'] ?>" class="btn-small">Просмотр</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentLeads)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">Нет лидов</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top: 1rem;">
                <a href="/admin/leads.php" class="btn">Все лиды →</a>
            </div>
        </div>

        <div class="dashboard-section">
            <h2>💬 Последние диалоги</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Пользователь</th>
                        <th>Шаг</th>
                        <th>Статус</th>
                        <th>Обновлено</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentDialogs as $dialog): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($dialog['first_name'] ?? $dialog['username'] ?? 'N/A') ?>
                                <br><small>@<?= $dialog['telegram_id'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($dialog['current_step'] ?? 'N/A') ?></td>
                            <td>
                                <span class="status-badge status-<?= $dialog['status'] ?>">
                                    <?= htmlspecialchars($dialog['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($dialog['updated_at'])) ?></td>
                            <td>
                                <a href="/admin/chat.php?id=<?= $dialog['id'] ?>" class="btn-small">Чат</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentDialogs)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">Нет диалогов</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top: 1rem;">
                <a href="/admin/dialogs.php" class="btn">Все диалоги →</a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
