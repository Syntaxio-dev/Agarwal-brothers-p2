<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Enquiry;
use App\Models\Insight;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        // The numbers cover the catalogue and content, so they are for admins and editors.
        return (bool) auth()->user()?->canManage('brands');
    }

    protected function getStats(): array
    {
        $new = Enquiry::where('status', 'new')->count();

        return [
            Stat::make('New enquiries', $new)
                ->description($new ? 'Waiting for a response' : 'All caught up')
                ->color($new ? 'warning' : 'success')
                ->url(route('filament.admin.resources.enquiries.index')),
            Stat::make('Active products', Product::where('is_active', true)->count())
                ->description(Product::where('is_top_pick', true)->count() . ' marked as top picks')
                ->color('info'),
            Stat::make('Brands', Brand::where('is_active', true)->count())
                ->description(Brand::whereNull('country_id')->count() . ' without a country (hidden on map)')
                ->color('primary'),
            Stat::make('Published insights', Insight::where('is_active', true)->count())
                ->description(Insight::where('is_featured', true)->count() . ' featured on homepage')
                ->color('gray'),
        ];
    }
}
