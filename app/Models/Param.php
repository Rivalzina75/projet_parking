<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Param extends Model
{
    // Chaque paramètre applicatif est stocké sur une ligne (name, value).
    // Ce format évite d'ajouter une migration SQL à chaque nouveau paramètre.
    public const DEFAULT_RESERVATION_HOURS = 'default_reservation_hours';

    public const DOUBLE_CONSENT_ENABLED = 'double_consent_enabled';

    protected $fillable = [
        'name',
        'value',
    ];

    public static function getIntValue(string $name, int $default): int
    {
        $value = self::query()->where('name', $name)->value('value');
        if ($value === null) {
            return $default;
        } else {
            return (int) $value;
        }
    }

    public static function getBoolValue(string $name, bool $default): bool
    {
        $value = self::query()->where('name', $name)->value('value');

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function setValue(string $name, string|int|bool $value): void
    {
        $normalized = match (true) {
            is_bool($value) => $value ? '1' : '0',
            default => (string) $value,
        };

        self::query()->updateOrCreate(
            ['name' => $name],
            ['value' => $normalized]
        );
    }
}
