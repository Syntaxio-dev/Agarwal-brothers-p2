<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\HelpGuide;
use App\Filament\Pages\ImportProducts;
use App\Filament\Resources\Brands\BrandResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\JobApplications\JobApplicationResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Slides\SlideResource;
use App\Models\Brand;
use App\Models\ContactMessage;
use App\Models\Enquiry;
use App\Models\Insight;
use App\Models\JobApplication;
use App\Models\Product;
use App\Models\Slide;
use Filament\Widgets\Widget;

/**
 * Dashboard "Quick updates": shortcuts to add things, and cards that count what needs attention.
 * Every card opens the matching list already filtered. Only what the signed-in role can act on is shown.
 */
class QuickUpdates extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.quick-updates';

    public static function canView(): bool
    {
        return auth()->check();
    }

    /** Small "do something now" buttons. @return array<int, array{label:string,url:string}> */
    public function getLinks(): array
    {
        $links = [];

        foreach ([
            ['Add product', ProductResource::class, 'create'],
            ['Import products', null, null],
            ['Add brand', BrandResource::class, 'create'],
            ['New blog / news', InsightResource::class, 'create'],
            ['Add slide', SlideResource::class, 'create'],
        ] as [$label, $resource, $page]) {
            if ($resource === null) {
                if (ImportProducts::canAccess()) {
                    $links[] = ['label' => $label, 'url' => ImportProducts::getUrl()];
                }

                continue;
            }

            if ($resource::canCreate()) {
                $links[] = ['label' => $label, 'url' => $resource::getUrl($page)];
            }
        }

        $links[] = ['label' => 'How-to guide', 'url' => HelpGuide::getUrl()];

        return $links;
    }

    /**
     * Cards, grouped. tone: warn (customers waiting), info (coming up), todo (tidy up).
     *
     * @return array<string, array<int, array{area:string,label:string,hint:string,count:int,url:string,tone:string}>>
     */
    public function getGroups(): array
    {
        $user = auth()->user();
        $activeOnly = ['is_active' => ['value' => '1']];

        $all = [
            ['Needs a reply', 'enquiries', 'New product enquiries', 'Customers waiting for a reply', 'warn',
                fn () => Enquiry::where('status', 'new')->count(),
                fn () => EnquiryResource::getUrl('index', ['filters' => ['status' => ['value' => 'new']]])],
            ['Needs a reply', 'enquiries', 'Enquiries assigned to you', 'Open enquiries waiting on you', 'warn',
                fn () => Enquiry::open()->where('assigned_to', $user->id)->count(),
                fn () => EnquiryResource::getUrl('index', ['filters' => ['mine' => ['isActive' => true], 'open' => ['isActive' => true]]])],
            ['Needs a reply', 'enquiries', 'Unassigned enquiries', 'Nobody looks after these yet', 'todo',
                fn () => Enquiry::open()->whereNull('assigned_to')->count(),
                fn () => EnquiryResource::getUrl('index', ['filters' => ['unassigned' => ['isActive' => true], 'open' => ['isActive' => true]]])],
            ['Needs a reply', 'contact-messages', 'New contact messages', 'Messages from the Contact Us form', 'warn',
                fn () => ContactMessage::where('status', 'new')->count(),
                fn () => ContactMessageResource::getUrl('index', ['filters' => ['status' => ['value' => 'new']]])],
            ['Needs a reply', 'job-applications', 'New job applications', 'Candidates who are not reviewed yet', 'warn',
                fn () => JobApplication::where('status', 'new')->count(),
                fn () => JobApplicationResource::getUrl('index', ['filters' => ['status' => ['value' => 'new']]])],

            ['Coming up', 'insights', 'Webinars in the next 7 days', 'Check the link, date and registration page', 'info',
                fn () => $this->upcomingWebinars(),
                fn () => InsightResource::getUrl('index', ['filters' => ['type' => ['value' => 'webinar']]])],

            ['Tidy up', 'products', 'Products without a photo', 'They look empty on the site', 'todo',
                fn () => Product::where('is_active', true)->withoutImage()->count(),
                fn () => ProductResource::getUrl('index', ['filters' => ['no_image' => ['isActive' => true]] + $activeOnly])],
            ['Tidy up', 'products', 'Products without specifications', 'Needed for the compare page', 'todo',
                fn () => Product::where('is_active', true)->withoutSpecs()->count(),
                fn () => ProductResource::getUrl('index', ['filters' => ['no_specs' => ['isActive' => true]] + $activeOnly])],
            ['Tidy up', 'products', 'Products without a Google title', 'Add one to show up better in search', 'todo',
                fn () => Product::where('is_active', true)->withoutSeo()->count(),
                fn () => ProductResource::getUrl('index', ['filters' => ['no_seo' => ['isActive' => true]] + $activeOnly])],
            ['Tidy up', 'products', 'Draft products', 'Hidden until you switch them to Active', 'todo',
                fn () => Product::where('is_active', false)->count(),
                fn () => ProductResource::getUrl('index', ['filters' => ['is_active' => ['value' => '0']]])],
            ['Tidy up', 'brands', 'Brands without a country', 'They are missing from the world map', 'todo',
                fn () => Brand::where('is_active', true)->withoutCountry()->count(),
                fn () => BrandResource::getUrl('index', ['filters' => ['no_country' => ['isActive' => true]] + $activeOnly])],
            ['Tidy up', 'brands', 'Brands without a logo', 'Their cards show only the name', 'todo',
                fn () => Brand::where('is_active', true)->withoutLogo()->count(),
                fn () => BrandResource::getUrl('index', ['filters' => ['no_logo' => ['isActive' => true]] + $activeOnly])],
            ['Tidy up', 'slides', 'Slides without a description', 'Add alt text for screen readers and Google', 'todo',
                fn () => Slide::withoutAlt()->count(),
                fn () => SlideResource::getUrl('index', ['filters' => ['no_alt' => ['isActive' => true]]])],
        ];

        $groups = [];

        foreach ($all as [$group, $area, $label, $hint, $tone, $counter, $url]) {
            if (! $user->canManage($area)) {
                continue;
            }

            $groups[$group][] = ['area' => $area, 'label' => $label, 'hint' => $hint, 'count' => (int) $counter(), 'url' => $url(), 'tone' => $tone];
        }

        // Cards that need action first inside every group.
        foreach ($groups as &$cards) {
            usort($cards, fn ($a, $b) => ($a['count'] > 0 ? 0 : 1) <=> ($b['count'] > 0 ? 0 : 1));
        }

        return $groups;
    }

    /** Flat list (used by tests and the "n things need attention" line). */
    public function getItems(): array
    {
        return collect($this->getGroups())->flatten(1)->values()->all();
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
