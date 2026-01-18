<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Admin\Auth;
use Core\Service;

Config::load(__DIR__ . '/../.env');
Auth::requireAuth();

// Обработка POST запросов (должна быть ПЕРЕД получением action из GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
    $postAction = $_POST['action'] ?? '';
    $postServiceId = (int)($_POST['id'] ?? 0);
    
    if ($postAction === 'create' || $postAction === 'edit') {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price_from' => !empty($_POST['price_from']) ? (float)$_POST['price_from'] : null,
            'price_to' => !empty($_POST['price_to']) ? (float)$_POST['price_to'] : null,
            'hourly_rate' => !empty($_POST['hourly_rate']) ? (float)$_POST['hourly_rate'] : null,
            'is_hourly' => isset($_POST['is_hourly']) ? 1 : 0,
            'active' => isset($_POST['active']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'category' => !empty($_POST['category']) ? $_POST['category'] : null,
        ];

        if ($postAction === 'create') {
            Service::create($data);
        } else {
            Service::update($postServiceId, $data);
        }

        header('Location: /admin/services.php');
        exit;
    } elseif ($postAction === 'delete' && $postServiceId > 0) {
        Service::delete($postServiceId);
        header('Location: /admin/services.php');
        exit;
    } elseif ($postAction === 'delete_multiple') {
        $ids = $_POST['service_ids'] ?? [];
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

$action = $_GET['action'] ?? 'list';
$serviceId = (int)($_GET['id'] ?? 0);

// Получаем категории и услуги для дерева
$allServices = Service::getAll(false);
$categories = Service::getCategories(false);
$csrfToken = Auth::getCSRFToken();

// Группируем услуги по категориям
$servicesByCategory = [];
$servicesByParent = [];
foreach ($allServices as $service) {
    if ($service['parent_id'] === null && ($service['price_from'] !== null || $service['price_to'] !== null)) {
        // Услуга без родителя (но с ценой) - добавляем в общий список
        $servicesByCategory['_root'][] = $service;
    } elseif ($service['parent_id'] !== null) {
        // Услуга с родителем
        $servicesByParent[$service['parent_id']][] = $service;
    }
}

if ($action === 'edit' && $serviceId) {
    $service = Service::getById($serviceId);
    if (!$service) {
        header('Location: /admin/services.php');
        exit;
    }
}

// Для формы редактирования нужны категории
if ($action === 'create' || $action === 'edit') {
    $categories = Service::getCategories(false);
}

include __DIR__ . '/includes/header.php';
?>
<div class="container">
    <h1>⚙️ Услуги</h1>

    <?php if ($action === 'list'): ?>
        <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <a href="/admin/services.php?action=create" class="btn">+ Добавить услугу</a>
            <button type="button" class="btn btn-danger" id="deleteMultipleBtn" style="display: none;" onclick="deleteSelected()">Удалить выбранные</button>
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
        function toggleCategory(categoryId) {
            const content = document.getElementById('content-' + categoryId);
            const toggle = document.getElementById('toggle-' + categoryId);
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                toggle.textContent = '▼';
            } else {
                content.style.display = 'none';
                toggle.textContent = '▶';
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.service-checkbox');
            const deleteMultipleBtn = document.getElementById('deleteMultipleBtn');

            // Обновление кнопки удаления при изменении чекбоксов
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateDeleteButton);
            });

            function updateDeleteButton() {
                const selected = Array.from(checkboxes).filter(cb => cb.checked);
                if (selected.length > 0) {
                    deleteMultipleBtn.style.display = 'inline-block';
                } else {
                    deleteMultipleBtn.style.display = 'none';
                }
            }
        });

        function deleteSelected() {
            if (!confirm('Удалить выбранные услуги?')) {
                return;
            }
            
            const form = document.getElementById('servicesForm');
            const formData = new FormData(form);
            formData.append('action', 'delete_multiple');
            
            // Создаем скрытую форму для отправки
            const submitForm = document.createElement('form');
            submitForm.method = 'POST';
            submitForm.action = '/admin/services.php';
            
            // Копируем CSRF токен
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = formData.get('csrf_token');
            submitForm.appendChild(csrfInput);
            
            // Добавляем action
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_multiple';
            submitForm.appendChild(actionInput);
            
            // Добавляем выбранные ID
            const checkboxes = document.querySelectorAll('.service-checkbox:checked');
            checkboxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'service_ids[]';
                input.value = cb.value;
                submitForm.appendChild(input);
            });
            
            document.body.appendChild(submitForm);
            submitForm.submit();
        }
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
