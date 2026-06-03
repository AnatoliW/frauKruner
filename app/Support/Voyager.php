<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

class Voyager
{
    public static function image(?string $path): string
    {
        return media_url($path);
    }

    public static function translatable($value): bool
    {
        return false;
    }

    public static function model(string $name): object
    {
        return new class ($name) {
            public function __construct(public string $name)
            {
            }

            public function where(string $column, mixed $operator = null, mixed $value = null): self
            {
                return $this;
            }

            public function first(): null
            {
                return null;
            }
        };
    }

    public static function actions(): array
    {
        return [];
    }

    public static function modelClass(string $name): string
    {
        return match ($name) {
            'User' => User::class,
            'Role' => Role::class,
            'Permission' => Permission::class,
            default => Str::studly($name),
        };
    }
}