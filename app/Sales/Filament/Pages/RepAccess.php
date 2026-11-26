<?php

namespace App\Sales\Filament\Pages;

use App\Sales\Models\SalesProfile;
use App\Sales\Rep\Support\RepDesk;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Sales team > Rep access: one table of every rep and every part of the web desk (/rep), each an on/off switch. The manager opens or closes
 * a part for one rep, all parts for one rep, or one part for every active rep. Nothing is open for a new rep. Every change is written to
 * the activity log (SalesProfile logs its changes) and takes effect at once, also on a desk page the rep has open (RepDesk checks every
 * request and every write). The phone app is not affected by any of this.
 */
class RepAccess extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Rep access';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'rep-access';

    protected static ?string $title = 'Rep access';

    protected string $view = 'filament.sales.rep-access';

    /** The same right as the Reps screen: whoever manages the reps decides what they may use. */
    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->canManage('sales-reps');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function toggle(int $profileId, string $area): void
    {
        $profile = $this->profile($profileId);
        abort_unless(array_key_exists($area, RepDesk::AREAS), 422);

        $areas = RepDesk::clean($profile->desk_areas);
        $on = ! in_array($area, $areas, true);
        $areas = $on ? [...$areas, $area] : array_values(array_diff($areas, [$area]));
        $this->save($profile, $areas);

        Notification::make()->title(RepDesk::AREAS[$area][0] . ($on ? ' opened for ' : ' closed for ') . $profile->user->name)->success()->send();
    }

    public function setAllFor(int $profileId, bool $on): void
    {
        $profile = $this->profile($profileId);
        $this->save($profile, $on ? RepDesk::keys() : []);

        Notification::make()->title(($on ? 'Everything opened for ' : 'Web desk closed for ') . $profile->user->name)->success()->send();
    }

    /** One part for every ACTIVE rep at once (a switched-off rep is left as it is). */
    public function setAreaForAll(string $area, bool $on): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless(array_key_exists($area, RepDesk::AREAS), 422);

        $count = 0;
        foreach (SalesProfile::query()->active()->with('user')->get() as $profile) {
            $areas = RepDesk::clean($profile->desk_areas);
            $next = $on ? RepDesk::clean([...$areas, $area]) : array_values(array_diff($areas, [$area]));
            if ($next !== $areas) {
                $this->save($profile, $next);
                $count++;
            }
        }

        Notification::make()->title(RepDesk::AREAS[$area][0] . ($on ? ' opened' : ' closed') . ' for ' . $count . ' ' . ($count === 1 ? 'rep' : 'reps'))->success()->send();
    }

    private function profile(int $id): SalesProfile
    {
        abort_unless(static::canAccess(), 403);

        return SalesProfile::query()->with('user')->findOrFail($id);
    }

    private function save(SalesProfile $profile, array $areas): void
    {
        $areas = RepDesk::clean($areas);
        $profile->update(['desk_areas' => $areas ?: null]);
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $reps = SalesProfile::query()->with('user')
            ->orderByRaw('sort_order IS NULL')->orderBy('sort_order')->orderBy('employee_code')
            ->get()
            ->map(fn (SalesProfile $p) => [
                'id' => $p->id,
                'name' => $p->user?->name ?? $p->employee_code,
                'code' => $p->employee_code,
                'active' => (bool) $p->is_active && (bool) $p->user?->is_active,
                'areas' => RepDesk::clean($p->desk_areas),
            ]);

        return [
            'reps' => $reps,
            'areas' => RepDesk::AREAS,
            'openCount' => fn (string $area) => $reps->filter(fn ($r) => $r['active'] && in_array($area, $r['areas'], true))->count(),
            'activeCount' => $reps->where('active', true)->count(),
            'repsUrl' => \App\Sales\Filament\Resources\SalesReps\SalesRepResource::getUrl('index'),
            'profileUrl' => fn (int $id) => \App\Sales\Filament\Resources\SalesReps\SalesRepResource::getUrl('view', ['record' => $id]),
        ];
    }
}
