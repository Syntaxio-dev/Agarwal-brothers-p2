<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Role based access for a Filament resource. The resource declares
 * `protected static string $accessKey = 'products';` and the user's role
 * (config/roles.php) decides whether the resource is visible and editable.
 */
trait RestrictedByRole
{
    protected static function roleAllows(): bool
    {
        return (bool) auth()->user()?->canManage(static::$accessKey);
    }

    public static function canViewAny(): bool
    {
        return static::roleAllows();
    }

    public static function canView(Model $record): bool
    {
        return static::roleAllows();
    }

    public static function canCreate(): bool
    {
        return static::roleAllows();
    }

    public static function canEdit(Model $record): bool
    {
        return static::roleAllows();
    }

    public static function canDelete(Model $record): bool
    {
        return static::roleAllows();
    }

    public static function canDeleteAny(): bool
    {
        return static::roleAllows();
    }
}
