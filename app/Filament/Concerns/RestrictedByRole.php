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

    /**
     * Filament's actions (delete, bulk delete, reorder, replicate ...) ask this, not the can* methods above,
     * and without policies Filament would allow everything. Route every action through the can* methods,
     * so a resource that says canDelete() = false really cannot be deleted from anywhere.
     */
    public static function getAuthorizationResponse(string|\UnitEnum $action, ?Model $record = null): \Illuminate\Auth\Access\Response
    {
        $action = $action instanceof \BackedEnum ? $action->value : ($action instanceof \UnitEnum ? $action->name : $action);

        $allowed = match ($action) {
            'viewAny' => static::canViewAny(),
            'view' => $record ? static::canView($record) : static::canViewAny(),
            'create' => static::canCreate(),
            'update' => $record ? static::canEdit($record) : static::roleAllows(),
            'delete' => $record ? static::canDelete($record) : static::canDeleteAny(),
            'deleteAny' => static::canDeleteAny(),
            'reorder', 'replicate' => static::roleAllows(),
            default => false,   // forceDelete, restore ...: this site has no soft deletes
        };

        return $allowed ? \Illuminate\Auth\Access\Response::allow() : \Illuminate\Auth\Access\Response::deny();
    }
}
