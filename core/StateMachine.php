<?php

namespace Core;

class StateMachine
{
    private const STATES = [
        'greeting' => 'Приветствие',
        'task_definition' => 'Определение задачи',
        'clarification' => 'Уточнение',
        'service_selection' => 'Подбор услуги',
        'price_range' => 'Цена / вилка',
        'call_to_action' => 'Call-to-action',
        'contact_collection' => 'Сбор контакта',
        'lead_completed' => 'Лид передан',
    ];

    private const NEXT_STATES = [
        'greeting' => 'task_definition',
        'task_definition' => 'clarification',
        'clarification' => 'service_selection',
        'service_selection' => 'price_range',
        'price_range' => 'call_to_action',
        'call_to_action' => 'contact_collection',
        'contact_collection' => 'lead_completed',
        'lead_completed' => null,
    ];

    public static function getInitialState(): string
    {
        return 'greeting';
    }

    public static function getNextState(string $currentState): ?string
    {
        return self::NEXT_STATES[$currentState] ?? null;
    }

    public static function isValidState(string $state): bool
    {
        return isset(self::STATES[$state]);
    }

    public static function getStateName(string $state): string
    {
        return self::STATES[$state] ?? $state;
    }

    public static function getAllStates(): array
    {
        return self::STATES;
    }
}
