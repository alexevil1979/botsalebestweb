<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Admin\Auth;
use Core\Service;

Config::load(__DIR__ . '/../.env');
Auth::requireAuth();

$action = $_GET['action'] ?? 'list';
$serviceId = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
    if ($action === 'create' || $action === 'edit') {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price_from' => !empty($_POST['price_from']) ? (float)$_POST['price_from'] : null,
            'price_to' => !empty($_POST['price_to']) ? (float)$_POST['price_to'] : null,
            'active' => isset($_POST['active']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        if ($action === 'create') {
            Service::create($data);
        } else {
            Service::update($serviceId, $data);
        }

        header('Location: /admin/services.php');
        exit;
    } elseif ($action === 'delete' && $serviceId) {
        Service::delete($serviceId);
        header('Location: /admin/services.php');
        exit;
    } elseif ($action === 'delete_multiple') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids) && is_array($ids)) {
            foreach ($ids as $id) {
                $id = (int)$id;
                if ($id > 0) {
                    Service::delete($id);
                }
            }
        }
        header('Location: /admin/services.php');
        exit;
    }
}

$services = Service::getAll(false);
$csrfToken = Auth::getCSRFToken();

if ($action === 'edit' && $serviceId) {
    $service = Service::getById($serviceId);
    if (!$service) {
        header('Location: /admin/services.php');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <h1>⚙️ Услуги</h1>

    <?php if ($action === 'list'): ?>
        <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <a href="/admin/services.php?action=create" class="btn">+ Добавить услугу</a>
            <form id="deleteMultipleForm" method="POST" style="display: none; margin: 0;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="delete_multiple">
                <input type="hidden" name="ids" id="selectedIds" value="">
                <button type="submit" class="btn btn-danger" id="deleteMultipleBtn" onclick="return confirm('Удалить выбранные услуги?')">Удалить выбранные</button>
            </form>
        </div>

        <form id="servicesForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll" title="Выбрать все">
                        </th>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Описание</th>
                        <th>Цена</th>
                        <th>Активна</th>
                        <th>Порядок</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="service_ids[]" value="<?php echo $service['id']; ?>" class="service-checkbox">
                            </td>
                            <td><?php echo $service['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($service['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars(mb_substr($service['description'] ?? '', 0, 100)); ?><?php echo mb_strlen($service['description'] ?? '') > 100 ? '...' : ''; ?></td>
                            <td>
                                <?php if ($service['price_from'] || $service['price_to']): ?>
                                    <?php if ($service['price_from']): ?>
                                        от <?php echo number_format($service['price_from'], 0, ',', ' '); ?> ₽
                                    <?php endif; ?>
                                    <?php if ($service['price_to']): ?>
                                        до <?php echo number_format($service['price_to'], 0, ',', ' '); ?> ₽
                                    <?php endif; ?>
                                <?php else: ?>
                                    Не указана
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($service['active']): ?>
                                    <span class="status-badge status-active">Да</span>
                                <?php else: ?>
                                    <span class="status-badge status-lost">Нет</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $service['sort_order']; ?></td>
                            <td>
                                <a href="/admin/services.php?action=edit&id=<?php echo $service['id']; ?>" class="btn-small">Редактировать</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить услугу?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                                    <button type="submit" class="btn-small btn-danger">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">Нет услуг</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.service-checkbox');
            const deleteMultipleForm = document.getElementById('deleteMultipleForm');
            const deleteMultipleBtn = document.getElementById('deleteMultipleBtn');
            const selectedIdsInput = document.getElementById('selectedIds');

            // Выбрать все / снять выбор
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateDeleteButton();
            });

            // Обновление кнопки удаления при изменении чекбоксов
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateDeleteButton);
            });

            function updateDeleteButton() {
                const selected = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
                if (selected.length > 0) {
                    deleteMultipleForm.style.display = 'inline-block';
                    selectedIdsInput.value = JSON.stringify(selected);
                } else {
                    deleteMultipleForm.style.display = 'none';
                }
            }

            // Обновление "Выбрать все" при изменении отдельных чекбоксов
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    selectAll.checked = Array.from(checkboxes).every(cb => cb.checked);
                });
            });
        });
        </script>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
        <h2><?php echo $action === 'create' ? 'Добавить услугу' : 'Редактировать услугу'; ?></h2>

        <form method="POST" style="max-width: 600px;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

            <div class="form-group">
                <label for="name">Название *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($service['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="price_from">Цена от (₽)</label>
                <input type="number" id="price_from" name="price_from" value="<?php echo $service['price_from'] ?? ''; ?>" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label for="price_to">Цена до (₽)</label>
                <input type="number" id="price_to" name="price_to" value="<?php echo $service['price_to'] ?? ''; ?>" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="active" <?php echo ($service['active'] ?? 1) ? 'checked' : ''; ?>>
                    Активна
                </label>
            </div>

            <div class="form-group">
                <label for="sort_order">Порядок сортировки</label>
                <input type="number" id="sort_order" name="sort_order" value="<?php echo $service['sort_order'] ?? 0; ?>" min="0">
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn">Сохранить</button>
                <a href="/admin/services.php" class="btn btn-danger">Отмена</a>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
