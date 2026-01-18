<?php

namespace Core;

class Service
{
    public static function getAll(bool $activeOnly = true): array
    {
        if ($activeOnly) {
            return Database::fetchAll(
                "SELECT * FROM services WHERE active = 1 ORDER BY sort_order ASC, id ASC"
            );
        }
        return Database::fetchAll("SELECT * FROM services ORDER BY sort_order ASC, id ASC");
    }

    public static function getById(int $id): ?array
    {
        return Database::fetch("SELECT * FROM services WHERE id = ?", [$id]);
    }

    public static function create(array $data): int
    {
        Database::execute(
            "INSERT INTO services (name, description, price_from, price_to, active, sort_order) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['name'],
                $data['description'] ?? null,
                $data['price_from'] ?? null,
                $data['price_to'] ?? null,
                $data['active'] ?? 1,
                $data['sort_order'] ?? 0,
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $fields = [];
        $params = [];

        foreach (['name', 'description', 'price_from', 'price_to', 'active', 'sort_order'] as $field) {
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
