<?php

namespace Core;

class Service
{
    public static function getAll(bool $activeOnly = true): array
    {
        $where = $activeOnly ? "WHERE active = 1" : "";
        return Database::fetchAll(
            "SELECT * FROM services {$where} ORDER BY sort_order ASC, id ASC"
        );
    }

    public static function getByCategory(string $category, bool $activeOnly = true): array
    {
        $where = $activeOnly ? "AND active = 1" : "";
        return Database::fetchAll(
            "SELECT * FROM services WHERE category = ? {$where} ORDER BY sort_order ASC, id ASC",
            [$category]
        );
    }

    public static function getCategories(bool $activeOnly = true): array
    {
        $where = $activeOnly ? "AND active = 1" : "";
        return Database::fetchAll(
            "SELECT * FROM services WHERE parent_id IS NULL AND category IS NOT NULL {$where} ORDER BY sort_order ASC, id ASC"
        );
    }

    public static function getByParent(int $parentId, bool $activeOnly = true): array
    {
        $where = $activeOnly ? "AND active = 1" : "";
        return Database::fetchAll(
            "SELECT * FROM services WHERE parent_id = ? {$where} ORDER BY sort_order ASC, id ASC",
            [$parentId]
        );
    }

    public static function getById(int $id): ?array
    {
        return Database::fetch("SELECT * FROM services WHERE id = ?", [$id]);
    }

    public static function create(array $data): int
    {
        Database::execute(
            "INSERT INTO services (name, description, price_from, price_to, hourly_rate, is_hourly, active, sort_order, parent_id, category) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['name'],
                $data['description'] ?? null,
                $data['price_from'] ?? null,
                $data['price_to'] ?? null,
                $data['hourly_rate'] ?? null,
                $data['is_hourly'] ?? 0,
                $data['active'] ?? 1,
                $data['sort_order'] ?? 0,
                $data['parent_id'] ?? null,
                $data['category'] ?? null,
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $fields = [];
        $params = [];

        foreach (['name', 'description', 'price_from', 'price_to', 'hourly_rate', 'is_hourly', 'active', 'sort_order', 'parent_id', 'category'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return;
        }

        $params[] = $id;
        $sql = "UPDATE services SET " . implode(', ', $fields) . " WHERE id = ?";
        Database::execute($sql, $params);
    }

    public static function delete(int $id): void
    {
        Database::execute("DELETE FROM services WHERE id = ?", [$id]);
    }
}
