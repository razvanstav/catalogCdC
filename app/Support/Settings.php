<?php

namespace App\Support;

class Settings
{
    public static function get(string $key, $default = null): ?string
    {
        $row = Database::queryOne("SELECT value FROM system_settings WHERE key = ?", [$key]);
        return $row !== null ? $row['value'] : $default;
    }

    public static function set(string $key, string $value): bool
    {
        $now = date('Y-m-d H:i:s');
        return Database::execute(
            "INSERT INTO system_settings (key, value, updated_at) VALUES (?, ?, ?)
             ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at",
            [$key, $value, $now]
        );
    }

    public static function isVacationMode(): bool
    {
        return self::get('vacation_mode', '0') === '1';
    }

    public static function toggleVacationMode(?bool $force = null, ?string $message = null): bool
    {
        $currentState = self::isVacationMode();
        $newState = $force !== null ? $force : !$currentState;
        self::set('vacation_mode', $newState ? '1' : '0');
        if ($message !== null) {
            self::set('vacation_message', $message);
        }
        return $newState;
    }

    public static function getVacationMessage(): string
    {
        return self::get('vacation_message', 'Suntem în vacanță! Toate cursurile și ședințele sunt în pauză.') ?: 'Suntem în vacanță! Toate cursurile și ședințele sunt în pauză.';
    }
}
