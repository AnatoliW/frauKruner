<?php

namespace App\Support;

/**
 * Umrechnung der php.ini-Kurzschreibweisen ("2M", "1G") in Bytes.
 *
 * Wird von den Upload-Tests und von `php artisan uploads:diagnose` verwendet.
 */
class UploadLimits
{
    /**
     * @return int Bytes, oder -1 für "unbegrenzt"
     */
    public static function bytes(string|false|null $value): int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        if ($value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int) $value,
        };
    }

    public static function human(int|float $bytes): string
    {
        if ($bytes < 0) {
            return 'unbegrenzt';
        }

        foreach (['B', 'KB', 'MB', 'GB', 'TB'] as $unit) {
            if ($bytes < 1024 || $unit === 'TB') {
                return round($bytes, 1).' '.$unit;
            }
            $bytes /= 1024;
        }

        return $bytes.' B';
    }
}
