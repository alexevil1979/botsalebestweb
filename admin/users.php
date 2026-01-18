<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;
use Admin\Auth;

Config::load(__DIR__ . '/../.env');
Auth::requireAuth();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$total = Database::fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;

$users = Database::fetchAll(
    "SELECT u.*, 
            (SELECT COUNT(*) FROM dialogs WHERE user_id = u.id) as dialogs_count,
            (SELECT COUNT(*) FROM leads WHERE user_id = u.id) as leads_count
     FROM users u 
     ORDER BY u.created_at DESC 
     LIMIT ? OFFSET ?",
    [$perPage, $offset]
);

$totalPages = ceil($total / $perPage);

include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <h1>👥 Пользователи</h1>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Telegram ID</th>
                <th>Имя</th>
                <th>Username</th>
                <th>Язык</th>
                <th>Диалогов</th>
                <th>Лидов</th>
                <th>Регистрация</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= $user['telegram_id'] ?></td>
                    <td><?= htmlspecialchars($user['first_name'] ?? 'N/A') ?></td>
                    <td>@<?= htmlspecialchars($user['username'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($user['language_code'] ?? 'N/A') ?></td>
                    <td><?= $user['dialogs_count'] ?></td>
                    <td><?= $user['leads_count'] ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem;">Нет пользователей</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
