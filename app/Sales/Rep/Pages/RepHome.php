<?php

namespace App\Sales\Rep\Pages;

use App\Sales\Rep\Resources\MyEnquiries\MyEnquiryResource;
use App\Sales\Support\Money;
use App\Sales\Support\SalesInsights;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/** The rep's first screen: what needs him today (late and due follow-ups, idle enquiries) and what is coming. Only his own enquiries. */
class RepHome extends Page
{
    protected static ?string $slug = 'home';

    protected static ?string $title = 'Home';

    protected static ?string $navigationLabel = 'Home';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.sales.rep.home';

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        return 'Hello, ' . explode(' ', (string) auth()->user()?->name)[0];
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return [
            'desk' => SalesInsights::repDesk((int) auth()->id()),
            'money' => fn (int $paise) => Money::short($paise),
            'edit' => fn ($enquiry) => MyEnquiryResource::getUrl('edit', ['record' => $enquiry]),
            'listUrl' => MyEnquiryResource::getUrl('index'),
            'tz' => config('sales.timezone'),
        ];
    }
}
