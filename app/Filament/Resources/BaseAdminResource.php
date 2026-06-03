<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

abstract class BaseAdminResource extends Resource
{
    protected static function getModelTableName(): ?string
    {
        $model = static::getModel();

        if (! $model || ! class_exists($model)) {
            return null;
        }

        $instance = app($model);

        if (! $instance instanceof Model) {
            return null;
        }

        return $instance->getTable();
    }

    protected static function getPermissionActionKey(string $action): ?string
    {
        $table = static::getModelTableName();

        if (! $table) {
            return null;
        }

        return sprintf('%s_%s', $action, $table);
    }

    protected static function canPerform(string $action): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        $permissionKey = static::getPermissionActionKey($action);

        if (! $permissionKey || ! method_exists($user, 'role')) {
            return false;
        }

        $role = $user->role()->first();

        if (! $role || ! method_exists($role, 'permissions')) {
            return false;
        }

        // Keep backward compatibility: allow role_id=1 when no permission rows exist yet.
        if ((int) ($user->role_id ?? 0) === 1 && ! $role->permissions()->exists()) {
            return true;
        }

        return $role->permissions()->where('key', $permissionKey)->exists();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return static::canPerform('browse');
    }

    public static function canView(Model $record): bool
    {
        return static::canPerform('read');
    }

    public static function canCreate(): bool
    {
        return static::canPerform('add');
    }

    public static function canEdit(Model $record): bool
    {
        return static::canPerform('edit');
    }

    public static function canDelete(Model $record): bool
    {
        return static::canPerform('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::canPerform('delete');
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::canPerform('delete');
    }

    public static function canForceDeleteAny(): bool
    {
        return static::canPerform('delete');
    }

    public static function canRestore(Model $record): bool
    {
        return static::canPerform('edit');
    }

    public static function canRestoreAny(): bool
    {
        return static::canPerform('edit');
    }

    public static function getNavigationGroup(): ?string
    {
        $table = static::getModelTableName();

        if (! $table) {
            return 'System';
        }

        $groups = [
            'Catalog' => [
                'categories', 'products', 'product_tags', 'product_brands', 'prodcats', 'prodcat_products',
                'tags', 'attributes', 'brands', 'additions', 'finishings', 'wearing_times', 'sliders',
            ],
            'Orders & Payments' => [
                'orders', 'order_products', 'orderimages', 'payments', 'payment_icons', 'shippings',
                'coupons', 'packages', 'boosts',
            ],
            'Users & Roles' => [
                'users', 'roles', 'permissions', 'profiles', 'addresses', 'verifications', 'favorites',
                'points', 'user_metas', 'notifications', 'ratings', 'reviews', 'methods',
            ],
            'Content' => [
                'pages', 'posts', 'postcats', 'faqs', 'contacts',
            ],
            'System' => [
                'settings', 'logs', 'images', 'metas',
            ],
        ];

        foreach ($groups as $group => $tables) {
            if (in_array($table, $tables, true)) {
                return $group;
            }
        }

        return 'System';
    }

    public static function getNavigationSort(): ?int
    {
        $table = static::getModelTableName();

        if (! $table) {
            return parent::getNavigationSort();
        }

        $order = [
            'users' => 10,
            'roles' => 11,
            'permissions' => 12,
            'profiles' => 13,
            'addresses' => 14,
            'verifications' => 15,
            'orders' => 20,
            'order_products' => 21,
            'orderimages' => 22,
            'payments' => 23,
            'shippings' => 24,
            'products' => 30,
            'categories' => 31,
            'tags' => 32,
            'pages' => 40,
            'posts' => 41,
            'postcats' => 42,
            'settings' => 90,
            'logs' => 91,
        ];

        return $order[$table] ?? (100 + crc32(Str::lower($table)) % 1000);
    }
}

