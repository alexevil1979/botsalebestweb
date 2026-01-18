<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;
use Admin\Auth;

Config::load(__DIR__ . '/../.env');
Auth::requireAuth();

$query = $_GET['q'] ?? '';
$results = [];

if ($query && strlen($query) >= 3) {
    $searchTerm = '%' . $query . '%';
    $results = Database::fetchAll(
        "SELECT m.*, u.first_name, u.username, d.id as dialog_id
         FROM messages m
         LEFT JOIN users u ON m.user_id = u.id
         LEFT JOIN dialogs d ON m.dialog_id = d.id
         WHERE m.text LIKE ?
         ORDER BY m.created_at DESC
         LIMIT 50",
        [$searchTerm]
    );
}

include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <h1>🔍 Поиск по сообщениям</h1>

    <div class="search-form">
        <form method="GET">
            <div class="form-group">
                <label for="q">Поисковый запрос (минимум 3 символа)</label>
                <input type="text" id="q" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Введите текст для поиска..." required>
            </div>
            <button type="submit" class="btn">Искать</button>
        </form>
    </div>

    <?php if ($query): ?>
        <div class="search-results">
            <h2>Результаты поиска (<?php echo count($results); ?>)</h2>
            <?php if (empty($results)): ?>
                <p style="text-align: center; padding: 2rem; color: #999;">Ничего не найдено</p>
            <?php else: ?>
                <?php foreach ($results as $result): ?>
                    <div class="search-result-item">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <div>
                                <strong><?php echo htmlspecialchars($result['first_name'] ?? $result['username'] ?? 'N/A'); ?></strong>
                                <span style="color: #999; margin-left: 1rem;">
                                    <?php echo $result['direction'] === 'in' ? '← Входящее' : '→ Исходящее'; ?>
                                </span>
                            </div>
                            <div style="color: #999;">
                                <?php echo date('d.m.Y H:i:s', strtotime($result['created_at'])); ?>
                            </div>
                        </div>
                        <div style="background: #f8f9fa; padding: 0.75rem; border-radius: 4px; margin-bottom: 0.5rem;">
                            <?php echo nl2br(htmlspecialchars($result['text'])); ?>
                        </div>
                        <div>
                            <a href="/admin/chat.php?id=<?php echo $result['dialog_id']; ?>" class="btn-small">Открыть диалог</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
