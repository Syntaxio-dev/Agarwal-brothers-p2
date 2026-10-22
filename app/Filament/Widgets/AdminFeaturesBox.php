<?php

namespace App\Filament\Widgets;

use App\Support\AdminFeatures;
use Filament\Widgets\Widget;

/** Dashboard info box: what this admin panel can do, limited to the signed-in person's role. */
class AdminFeaturesBox extends Widget
{
    protected static ?int $sort = 13;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.admin-features';

    public static function canView(): bool
    {
        return auth()->check();
    }

    public function getGroups(): array
    {
        return AdminFeatures::forUser(auth()->user());
    }

    public function getEveryone(): array
    {
        return AdminFeatures::everyone();
    }

    public function getRoleText(): string
    {
        $role = config('roles.' . (auth()->user()?->role ?? ''), []);

        return trim(($role['label'] ?? '') . ($role['description'] ?? '' ? ': ' . $role['description'] : ''));
    }
}
