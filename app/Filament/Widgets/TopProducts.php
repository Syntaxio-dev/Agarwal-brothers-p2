<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Support\DashboardData;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

/** The five products customers asked about most in the chosen range. */
class TopProducts extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    protected string $view = 'filament.widgets.top-products';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->canManage('enquiries');
    }

    public function getColumnSpan(): int|string|array
    {
        return CatalogueHealth::canView() ? $this->columnSpan : 'full';
    }

    public function getDays(): int
    {
        return DashboardData::days($this->pageFilters);
    }

    public function getRows()
    {
        return DashboardData::topProducts($this->getDays(), 5);
    }

    /** Link to the product in the admin, when this person may open products. */
    public function url(?int $id): ?string
    {
        return $id && auth()->user()?->canManage('products') ? ProductResource::getUrl('edit', ['record' => $id]) : null;
    }
}
