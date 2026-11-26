<?php

namespace App\Sales\Rep\Support;

use App\Models\User;
use App\Sales\Models\SalesProspect;
use App\Sales\Models\SalesProspectNote;
use App\Sales\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Prospects from the web desk. The pool is the team's (every rep reads it); only the rep who added a prospect changes or deletes it
 * (SyncService answers NOT_ALLOWED otherwise); any rep logs a call. Everything goes through DeskSync, so the phone's rules apply.
 */
final class DeskProspects
{
    public const AREA = 'prospects';

    public const MAX_PEOPLE = 10;

    public function __construct(private readonly DeskSync $sync) {}

    /**
     * @param  array{name:string, territory:string, address?:?string, source?:?string, notes?:?string, people?:array}  $d
     */
    public function save(User $rep, ?SalesProspect $prospect, array $d): SalesProspect
    {
        if ($prospect && (int) $prospect->user_id !== (int) $rep->id) {
            throw ValidationException::withMessages(['desk' => 'Only the rep who added this prospect can change it. You can still log a call.']);
        }

        $people = array_values(array_filter((array) ($d['people'] ?? []), fn ($p) => is_array($p) && filled($p['name'] ?? null)));
        if (count($people) > self::MAX_PEOPLE) {
            throw ValidationException::withMessages(['desk' => 'A prospect holds at most ' . self::MAX_PEOPLE . ' people.']);
        }

        // a territory is written like a place (the app's rule); one the prospect already has is never questioned again
        $territory = self::territory(RepTerritories::clean(DeskSync::text($d['territory'] ?? '', 100)));
        if ((! $prospect || SalesProspect::keyOf($territory) !== SalesProspect::keyOf($prospect->territory)) && ($problem = RepTerritories::problem($territory))) {
            throw ValidationException::withMessages(['desk' => $problem]);
        }

        $uuid = $prospect?->uuid ?? (string) Str::uuid();
        $this->sync->one($rep, self::AREA, 'prospects', [
            'uuid' => $uuid,
            'name' => DeskSync::text($d['name'] ?? '', 150),
            'territory' => $territory,
            'address' => DeskSync::text($d['address'] ?? '', 200),
            'source' => filled($d['source'] ?? null) ? $d['source'] : null,
            'notes' => DeskSync::text($d['notes'] ?? '', 1000),
            'people' => array_map(fn (array $p) => [
                'name' => DeskSync::text($p['name'], 150),
                'position' => DeskSync::text($p['position'] ?? '', 100),
                'phone' => filled($p['phone'] ?? null) ? (PhoneNumber::normalize((string) $p['phone']) ?? (string) $p['phone']) : '',
                'email' => Str::lower(DeskSync::text($p['email'] ?? '', 150)),
                'linkedin' => DeskSync::text($p['linkedin'] ?? '', 300),
            ], $people),
            'updated_at' => DeskSync::stamp($prospect?->client_updated_at),
        ]);

        return SalesProspect::where('uuid', $uuid)->with('people')->firstOrFail();
    }

    // ---- one contact person at a time (the prospect page): the whole record is sent again, like the phone does -----------------

    /** @param array{name:string, position?:?string, phone?:?string, email?:?string, linkedin?:?string} $person */
    public function addPerson(User $rep, SalesProspect $prospect, array $person): SalesProspect
    {
        $people = $this->peopleOf($prospect);
        if (count($people) >= self::MAX_PEOPLE) {
            throw ValidationException::withMessages(['desk' => 'A prospect holds at most ' . self::MAX_PEOPLE . ' people.']);
        }
        $people[] = $person;

        return $this->save($rep, $prospect, $this->detailsOf($prospect) + ['people' => $people]);
    }

    /**
     * $personId is the id of the row the rep saw. The people are stored again on every save (new ids), so an id that is gone means the
     * list changed meanwhile (the phone, another tab): nothing is changed then, rather than the wrong person.
     */
    public function updatePerson(User $rep, SalesProspect $prospect, int $personId, array $person): SalesProspect
    {
        [$people, $index] = $this->find($prospect, $personId);
        $people[$index] = $person;

        return $this->save($rep, $prospect, $this->detailsOf($prospect) + ['people' => $people]);
    }

    public function removePerson(User $rep, SalesProspect $prospect, int $personId): SalesProspect
    {
        [$people, $index] = $this->find($prospect, $personId);
        array_splice($people, $index, 1);

        return $this->save($rep, $prospect, $this->detailsOf($prospect) + ['people' => $people]);
    }

    /** @return array{0: list<array>, 1: int} the people and the place of $personId among them */
    private function find(SalesProspect $prospect, int $personId): array
    {
        $rows = $prospect->people()->get()->values();
        $index = $rows->search(fn ($x) => (int) $x->id === $personId);
        if ($index === false) {
            throw ValidationException::withMessages(['desk' => 'The list of people changed meanwhile. Reload the page and try again.']);
        }

        return [$this->peopleOf($prospect), (int) $index];
    }

    /** @return array{name:string, territory:string, address:?string, source:?string, notes:?string} */
    private function detailsOf(SalesProspect $p): array
    {
        return ['name' => $p->name, 'territory' => $p->territory, 'address' => $p->address, 'source' => $p->source, 'notes' => $p->notes];
    }

    /** @return list<array> the stored people, in their order */
    private function peopleOf(SalesProspect $p): array
    {
        return $p->people()->get()
            ->map(fn ($x) => ['name' => $x->name, 'position' => $x->position, 'phone' => $x->phone, 'email' => $x->email, 'linkedin' => $x->linkedin])
            ->values()->all();
    }

    public function delete(User $rep, SalesProspect $prospect): void
    {
        if ((int) $prospect->user_id !== (int) $rep->id) {
            throw ValidationException::withMessages(['desk' => 'Only the rep who added this prospect can delete it.']);
        }

        $this->sync->delete($rep, self::AREA, 'prospects', $prospect->uuid);
    }

    /** One call to a prospect, by any rep: a log line, never changed afterwards. */
    public function logCall(User $rep, SalesProspect $prospect, string $outcome, ?string $remark, ?string $nextFollowupAt): SalesProspectNote
    {
        if (! array_key_exists($outcome, SalesProspectNote::OUTCOMES)) {
            throw ValidationException::withMessages(['desk' => 'Pick how the call went.']);
        }

        $next = filled($nextFollowupAt) ? CarbonImmutable::parse($nextFollowupAt, config('sales.timezone'))->utc() : null;
        if ($next && ! SalesProspectNote::OUTCOMES[$outcome][1]) {
            $next = null;   // a closed outcome (not interested, wrong number ...) needs no follow-up
        }
        if ($next && $next->lt(now()->subMinutes(5))) {
            throw ValidationException::withMessages(['desk' => 'Pick a follow-up from now on.']);
        }

        $uuid = (string) Str::uuid();
        $now = DeskSync::stamp();
        $this->sync->one($rep, self::AREA, 'prospect_notes', [
            'uuid' => $uuid,
            'prospect_uuid' => $prospect->uuid,
            'outcome' => $outcome,
            'remark' => DeskSync::text($remark, 300),
            'next_followup_at' => DeskSync::iso($next),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return SalesProspectNote::where('uuid', $uuid)->firstOrFail();
    }

    /** One spelling per team: "bagru" typed by a rep becomes the team's "Bagru" (the app's canonicalTerritory). */
    public static function territory(string $typed): string
    {
        $known = SalesProspect::query()->where('territory_key', SalesProspect::keyOf($typed))->orderBy('id')->value('territory');

        return $known ?? $typed;
    }

    /** @return array<string,string> the team's territories, A to Z */
    public static function territories(): array
    {
        $list = SalesProspect::query()->select('territory')->distinct()->orderBy('territory')->pluck('territory')->all();

        return array_combine($list, $list) ?: [];
    }
}
