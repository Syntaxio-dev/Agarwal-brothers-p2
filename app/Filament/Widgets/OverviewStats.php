<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Enquiry;
use App\Models\Insight;
use App\Models\Product;
use App\Support\DashboardCache;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        // The numbers cover the catalogue and content, so they are for admins and editors.
        return (bool) auth()->user()?->canManage('brands');
    }

    protected function getStats(): array
    {
        $new = DashboardCache::remember('enquiries', 'new', fn () => Enquiry::where('status', 'new')->count());

        return [
            Stat::make('New enquiries', $new)
                ->description($new ? 'Waiting for a response' : 'All caught up')
                ->color($new ? 'warning' : 'success')
                ->extraAttributes(['class' => $new ? 'border-s-4 !border-s-danger-500' : 'border-s-4 !border-s-success-500'])
                ->url(route('filament.admin.resources.enquiries.index')),
            Stat::make('Active products', DashboardCache::remember('products', 'active', fn () => Product::where('is_active', true)->count()))
                ->description(DashboardCache::remember('products', 'top-picks', fn () => Product::where('is_top_pick', true)->count()) . ' marked as top picks')
                ->color('info')
                ->extraAttributes(['class' => 'border-s-4 !border-s-info-500']),
            Stat::make('Brands', DashboardCache::remember('brands', 'active', fn () => Brand::where('is_active', true)->count()))
                ->description(DashboardCache::remember('brands', 'no-country-all', fn () => Brand::whereNull('country_id')->count()) . ' without a country (hidden on map)')
                ->color('primary')
                ->extraAttributes(['class' => 'border-s-4 !border-s-primary-500']),
            Stat::make('Published insights', DashboardCache::remember('insights', 'published', fn () => Insight::where('is_active', true)->count()))
                ->description(DashboardCache::remember('insights', 'featured', fn () => Insight::where('is_featured', true)->count()) . ' featured on homepage')
                ->color('gray')
                ->extraAttributes(['class' => 'border-s-4 !border-s-gray-400']),
        ];
    }
}
