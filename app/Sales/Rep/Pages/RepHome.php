<?php

namespace App\Sales\Rep\Pages;

use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesTask;
use App\Sales\Models\SalesTodo;
use App\Sales\Rep\Resources\Meetings\MeetingResource;
use App\Sales\Rep\Resources\MyEnquiries\MyEnquiryResource;
use App\Sales\Rep\Resources\Tasks\TaskResource;
use App\Sales\Rep\Resources\Todos\TodoResource;
use App\Sales\Rep\Support\RepDesk;
use App\Sales\Support\Money;
use App\Sales\Support\SalesInsights;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * The rep's first screen: only the parts the manager opened for him. Leads: what needs him today and what is coming. Meetings: today's
 * visits. Tasks: the open ones. To-do: what is due. Every list holds only his own records.
 */
class RepHome extends Page
{
    protected static ?string $slug = 'home';

    protected static ?string $title = 'Home';

    protected static ?string $navigationLabel = 'Home';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.sales.rep.home';

    public static function canAccess(): bool
    {
        return RepDesk::areasOf(auth()->user()) !== [];
    }

    public function boot(): void
    {
        abort_unless(static::canAccess(), 403);
    }

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
        $user = auth()->user();
        $uid = (int) $user->id;
        $areas = RepDesk::areasOf($user);
        $has = fn (string $area) => in_array($area, $areas, true);
        $tz = config('sales.timezone');
        $today = now($tz)->startOfDay();

        return [
            'desk' => $has('leads') ? SalesInsights::repDesk($uid) : null,
            'money' => fn (int $paise) => Money::short($paise),
            'edit' => fn ($enquiry) => MyEnquiryResource::getUrl('edit', ['record' => $enquiry]),
            'listUrl' => $has('leads') ? MyEnquiryResource::getUrl('index') : null,
            'meetings' => $has('meetings')
                ? SalesMeeting::where('user_id', $uid)->whereIn('status', SalesMeeting::OPEN)
                    ->whereBetween('meeting_at', [$today->copy()->utc(), $today->copy()->endOfDay()->utc()])->orderBy('meeting_at')->limit(10)->get()
                : null,
            'meetingsUrl' => $has('meetings') ? MeetingResource::getUrl('index') : null,
            'tasks' => $has('tasks')
                ? SalesTask::where('assigned_to', $uid)->whereIn('status', SalesTask::OPEN)->orderByRaw('due_at IS NULL')->orderBy('due_at')->limit(5)->get()
                : null,
            'taskUrl' => fn ($task) => TaskResource::getUrl('view', ['record' => $task]),
            'todos' => $has('notes')
                ? SalesTodo::where('user_id', $uid)->where('done', false)->whereNotNull('due_date')->where('due_date', '<=', $today->toDateString())->orderBy('due_date')->limit(5)->get()
                : null,
            'todoUrl' => $has('notes') ? TodoResource::getUrl('index') : null,
            'tz' => $tz,
        ];
    }
}
