<?php

namespace App\Sales\Actions;

use App\Models\User;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesProfile;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * THE one place that decides which reps see a client (and so all the people at it): the main rep (`assigned_to`) and the
 * "also sees" reps (`sales_client_access`). The rep who added the client always sees it and is never stored as a viewer.
 * The client screen, "Give to a rep", "Open for reps" and "Close for reps" all end here.
 *
 * Phones hear about it on their next pull: the client and its people get a new phone time, and the pull's `scope` fingerprint
 * changes for every rep whose list changed (SyncService::scopeOf), which makes that phone fetch the list again and drop what
 * is no longer open to it.
 */
class SetClientAccess
{
    /**
     * @param  array<int,int>  $repIds    every rep who should see the client (the added-by rep need not be in it)
     * @param  ?int            $mainRepId the rep who works the client, or null ("Nobody yet"); joins $repIds when missing
     * @return array{old: list<string>, new: list<string>, changed: bool}
     */
    public function handle(SalesClient $client, array $repIds, ?int $mainRepId, User $manager): array
    {
        abort_unless($manager->canManage('sales-clients'), 403);
        $this->repIds = null;

        $repIds = array_values(array_unique(array_map('intval', $repIds)));
        if ($mainRepId !== null) {
            $repIds[] = $mainRepId;
        }
        $repIds = array_values(array_unique($repIds));

        $known = SalesProfile::active()->whereIn('user_id', $repIds)->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        if (array_diff($repIds, $known) !== []) {
            throw new InvalidArgumentException('Only active reps can be given a client.');
        }

        return DB::transaction(function () use ($client, $repIds, $mainRepId, $manager) {
            $client = SalesClient::query()->lockForUpdate()->findOrFail($client->getKey());
            $before = $this->seeing($client);

            $viewers = array_values(array_diff($repIds, [$mainRepId, $client->user_id]));
            $client->viewers()->sync($viewers);
            \App\Sales\Support\SalesInsights::forgetClientSets();
            $client->assigned_to = $mainRepId;   // the model's saving hook gives the client a new phone time when this changed

            $after = $this->seeing($client->setRelation('viewers', $client->viewers()->get()));
            $changed = $before['ids'] !== $after['ids'] || $before['main'] !== $mainRepId;

            if ($changed) {
                $this->touch($client);
                \App\Sales\Support\SalesInsights::forgetClientSets();
                ActivityLogger::record('updated', $client, ['access' => ['old' => $before['names'], 'new' => $after['names']]], $manager);
            }

            return ['old' => $before['names'], 'new' => $after['names'], 'changed' => $changed];
        });
    }

    /** "Open for reps": add these reps to the ones who already see the client. */
    public function open(SalesClient $client, array $repIds, User $manager): array
    {
        $client->refresh();
        $now = $this->currentRepIds($client);

        return $this->handle($client, array_merge($now, $repIds), $client->assigned_to ? (int) $client->assigned_to : null, $manager);
    }

    /** "Close for reps": take these reps off (the rep who added the client stays; a closed main rep is replaced as usual). */
    public function close(SalesClient $client, array $repIds, User $manager): array
    {
        $client->refresh();
        $remaining = array_values(array_diff($this->currentRepIds($client), array_map('intval', $repIds)));
        $main = $client->assigned_to && in_array((int) $client->assigned_to, $remaining, true) ? (int) $client->assigned_to : null;

        return $this->handle($client, $remaining, $main ?? ($remaining[0] ?? null), $manager);
    }

    /** "Give to a rep": this rep becomes the main rep and sees the client; the others who were opened for keep seeing it. */
    public function give(SalesClient $client, int $repId, User $manager): array
    {
        $client->refresh();

        return $this->handle($client, array_merge($this->currentRepIds($client), [$repId]), $repId, $manager);
    }

    /** Ids of the reps who see the client apart from the one who added it: the main rep and the viewers. */
    public function currentRepIds(SalesClient $client): array
    {
        return collect([$client->assigned_to])->merge($client->viewers()->pluck('users.id'))
            ->filter()->map(fn ($id) => (int) $id)->diff([$client->user_id])->unique()->values()->all();
    }

    // ---- the pure rules of the screen (used by the Livewire page and tested without a browser) -------------------------------
    // `$selected` = the ticked reps (not counting the rep who added the client: that one always sees it), `$locked` = that rep.

    /**
     * Ticking or un-ticking a rep. One rep mode: ticking replaces the whole list and that rep becomes main. Several: the main rep stays;
     * an un-ticked main rep is replaced by the first remaining one (or nobody), and the first ticked rep is main when there was none.
     *
     * @param  array<int,int>  $selected
     * @return array{selected: list<int>, main: ?int}
     */
    public static function toggle(string $mode, array $selected, ?int $main, int $repId, ?int $locked = null): array
    {
        $selected = self::ids($selected);

        if (in_array($repId, $selected, true)) {
            $selected = array_values(array_diff($selected, [$repId]));
        } elseif ($mode === 'one') {
            $selected = [$repId];
            $main = $repId;
        } else {
            $selected[] = $repId;
        }

        return ['selected' => $selected, 'main' => self::mainFor($selected, $main, $locked)];
    }

    /** Starring a rep who is not ticked ticks them (in One rep mode that replaces the list). */
    public static function makeMain(string $mode, array $selected, int $repId, ?int $locked = null): array
    {
        $selected = self::ids($selected);

        if ($repId === $locked) {
            return ['selected' => $mode === 'one' ? [] : $selected, 'main' => $repId];
        }
        if (! in_array($repId, $selected, true)) {
            $selected = $mode === 'one' ? [$repId] : array_merge($selected, [$repId]);
        }

        return ['selected' => $selected, 'main' => $repId];
    }

    /** Several -> One with more than one ticked keeps the main rep only (or the first ticked rep when there is no main). */
    public static function switchMode(string $mode, array $selected, ?int $main, ?int $locked = null): array
    {
        $selected = self::ids($selected);

        if ($mode === 'one' && count($selected) > 1) {
            $selected = [$main !== null && in_array($main, $selected, true) ? $main : $selected[0]];
        }

        return ['selected' => $selected, 'main' => self::mainFor($selected, $main, $locked)];
    }

    /** "One rep" is the default when at most one rep is ticked. */
    public static function modeFor(array $selected): string
    {
        return count(self::ids($selected)) > 1 ? 'several' : 'one';
    }

    /** Every rep who will see the client with this choice, the rep who added it included. */
    public static function seers(array $selected, ?int $main, ?int $locked): array
    {
        return array_values(array_unique(array_filter(array_merge(self::ids($selected), [$main, $locked]))));
    }

    /** The main rep must be a rep in the list (ticked, or the rep who added the client); otherwise the first ticked rep, or nobody. */
    private static function mainFor(array $selected, ?int $main, ?int $locked): ?int
    {
        if ($main !== null && (in_array($main, $selected, true) || $main === $locked)) {
            return $main;
        }

        return $selected[0] ?? null;
    }

    private static function ids(array $ids): array
    {
        return array_values(array_unique(array_map('intval', $ids)));
    }

    // ---- inside -------------------------------------------------------------------------------------------------------------

    private function activeRepIds(): \Illuminate\Support\Collection
    {
        return $this->repIds ??= SalesProfile::active()->pluck('user_id')->map(fn ($id) => (int) $id);
    }

    private ?\Illuminate\Support\Collection $repIds = null;

    /** @return array{ids: list<int>, main: ?int, names: list<string>} */
    private function seeing(SalesClient $client): array
    {
        $viewerIds = $client->relationLoaded('viewers') ? $client->viewers->pluck('id') : $client->viewers()->pluck('users.id');
        $ids = collect([$client->user_id, $client->assigned_to])->merge($viewerIds)->filter()->map(fn ($id) => (int) $id)->unique()->sort()->values();

        // only reps count (a client the office added has a manager as "added by", who is not a rep and sees everything anyway)
        $ids = $ids->filter(fn ($id) => $this->activeRepIds()->contains($id))->values();
        $names = User::query()->whereIn('id', $ids)->orderBy('name')->pluck('name')->all();

        return ['ids' => $ids->all(), 'main' => $client->assigned_to ? (int) $client->assigned_to : null, 'names' => $names];
    }

    /**
     * Gives the client and its people a new phone time, so the next pull carries them. (A change of `assigned_to` alone is already
     * stamped by the model; a change of the viewers alone is not, because it only touches sales_client_access.)
     */
    private function touch(SalesClient $client): void
    {
        $stamp = now()->utc()->format('Y-m-d H:i:s.v');

        $client->forceFill(['client_updated_at' => $stamp]);
        $client->save();

        $client->contacts()->withTrashed()->update(['client_updated_at' => $stamp, 'updated_at' => now()]);
    }
}
