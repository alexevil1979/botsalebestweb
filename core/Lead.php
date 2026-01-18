<?php

namespace Core;

class Lead
{
    public static function create(array $data): int
    {
        Database::execute(
            "INSERT INTO leads (user_id, dialog_id, name, phone, email, service_id, budget_from, budget_to, task_description, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new')",
            [
                $data['user_id'],
                $data['dialog_id'],
                $data['name'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['service_id'] ?? null,
                $data['budget_from'] ?? null,
                $data['budget_to'] ?? null,
                $data['task_description'] ?? null,
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $leadId, array $data): void
    {
        $fields = [];
        $params = [];

        foreach (['name', 'phone', 'email', 'service_id', 'budget_from', 'budget_to', 'task_description', 'status', 'notes'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return;
        }

        $params[] = $leadId;
        $sql = "UPDATE leads SET " . implode(', ', $fields) . " WHERE id = ?";
        Database::execute($sql, $params);
    }

    public static function getById(int $leadId): ?array
    {
        return Database::fetch(
            "SELECT l.*, u.telegram_id, u.username, u.first_name, s.name as service_name 
             FROM leads l 
             LEFT JOIN users u ON l.user_id = u.id 
             LEFT JOIN services s ON l.service_id = s.id 
             WHERE l.id = ?",
            [$leadId]
        );
    }

    public static function getAll(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (isset($filters['status'])) {
            $where[] = "l.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['date_from'])) {
            $where[] = "l.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (isset($filters['date_to'])) {
            $where[] = "l.created_at <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : '';

        return Database::fetchAll(
            "SELECT l.*, u.telegram_id, u.username, u.first_name, s.name as service_name 
             FROM leads l 
             LEFT JOIN users u ON l.user_id = u.id 
             LEFT JOIN services s ON l.service_id = s.id 
             {$whereClause}
             ORDER BY l.created_at DESC",
            $params
        );
    }
}
