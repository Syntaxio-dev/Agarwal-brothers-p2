<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\ContactMessage;
use App\Models\Enquiry;
use App\Models\Insight;
use App\Models\JobApplication;
use App\Models\Product;
use Filament\Widgets\Widget;

/** "Needs your attention" list: only shows what the signed-in role can act on. */
class QuickUpdates extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.quick-updates';

    public static function canView(): bool
    {
        return auth()->check();
    }

    /** @return array<int, array{area:string,label:string,hint:string,count:int,url:string,tone:string}> */
    public function getItems(): array
    {
        $user = auth()->user();

        $all = [
            ['enquiries', 'New product enquiries', 'Customers waiting for a reply', fn () => Enquiry::where('status', 'new')->count(),
                route('filament.admin.resources.enquiries.index', ['filters' => ['status' => ['value' => 'new']]]), 'warn'],
            ['contact-messages', 'New contact messages', 'Messages from the Contact Us form', fn () => ContactMessage::where('status', 'new')->count(),
                route('filament.admin.resources.contact-messages.index', ['filters' => ['status' => ['value' => 'new']]]), 'warn'],
            ['job-applications', 'New job applications', 'Candidates who applied and are not reviewed yet', fn () => JobApplication::where('status', 'new')->count(),
                route('filament.admin.resources.job-applications.index', ['filters' => ['status' => ['value' => 'new']]]), 'warn'],
            ['insights', 'Webinars in the next 7 days', 'Check the link, date and registration page', fn () => $this->upcomingWebinars(),
                route('filament.admin.resources.insights.index', ['filters' => ['type' => ['value' => 'webinar']]]), 'info'],
            ['products', 'Products without an image', 'Add a photo so they look good on the site', fn () => Product::where('is_active', true)->whereNull('image')->count(),
                route('filament.admin.resources.products.index'), 'todo'],
            ['brands', 'Brands without a country', 'These are missing from the world map', fn () => Brand::where('is_active', true)->whereNull('country_id')->count(),
                route('filament.admin.resources.brands.index'), 'todo'],
        ];

        $items = [];

        foreach ($all as [$area, $label, $hint, $counter, $url, $tone]) {
            if (! $user->canManage($area)) {
                continue;
            }

            $items[] = [
                'area' => $area,
                'label' => $label,
                'hint' => $hint,
                'count' => (int) $counter(),
                'url' => $url,
                'tone' => $tone,
            ];
        }

        // Things to do first, then the rest.
        usort($items, fn ($a, $b) => [$a['count'] > 0 ? 0 : 1, $a['tone'] === 'warn' ? 0 : 1] <=> [$b['count'] > 0 ? 0 : 1, $b['tone'] === 'warn' ? 0 : 1]);

        return $items;
    }

    private function upcomingWebinars(): int
    {
        $now = now(Insight::TZ);

        return Insight::where('type', 'webinar')
            ->where('is_active', true)
            ->get()
            ->filter(fn (Insight $w) => $w->isUpcoming() && $w->start_ist->lte($now->copy()->addDays(7)->endOfDay()))
            ->count();
    }
}
