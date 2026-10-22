<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Support\DashboardData;
use Filament\Widgets\Widget;

/** How complete the live catalogue is, and which live products nobody has touched for a long time. */
class CatalogueHealth extends Widget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    protected string $view = 'filament.widgets.catalogue-health';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->canManage('products');
    }

    public function getColumnSpan(): int|string|array
    {
        return TopProducts::canView() ? $this->columnSpan : 'full';
    }

    public function getHealth(): array
    {
        return DashboardData::catalogueHealth();
    }

    public function getStale(): array
    {
        return DashboardData::stale(5);
    }

    public function getMonths(): int
    {
        return (int) config('dashboard.stale_months');
    }

    public function editUrl(int $id): string
    {
        return ProductResource::getUrl('edit', ['record' => $id]);
    }

    public function listUrl(): string
    {
        return ProductResource::getUrl('index', ['filters' => ['is_active' => ['value' => '1']]]);
    }
}
