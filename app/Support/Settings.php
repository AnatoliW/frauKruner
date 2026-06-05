<?php

namespace App\Support;

use App\Models\Setting;

class Settings
{
    /**
     * In-request cache to avoid repeated DB hits for the same key.
     *
     * @var array<string, mixed>
     */
    protected static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }

        $value = Setting::query()->where('key', $key)->value('value');
        $resolved = $value ?? $default;

        static::$cache[$key] = $resolved;

        return $resolved;
    }

    public static function paypalClientId(): ?string
    {
        $value = static::get('site.client_id');

        return is_string($value) ? trim($value) : null;
    }

    public static function paypalSecretId(): ?string
    {
        $value = static::get('site.paypal_secret_id');

        return is_string($value) ? trim($value) : null;
    }

    public static function stripePublishKey(): ?string
    {
        $value = static::get('site.publish_key');

        return is_string($value) ? trim($value) : null;
    }

    public static function stripeSecretKey(): ?string
    {
        $value = static::get('site.secret_key');

        return is_string($value) ? trim($value) : null;
    }
}
