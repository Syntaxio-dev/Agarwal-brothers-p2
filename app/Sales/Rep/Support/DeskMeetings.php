<?php

namespace App\Sales\Rep\Support;

use App\Models\User;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesProspect;
use App\Sales\Models\SalesRoute;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Meeting plans from the web desk: schedule a visit (to a client the rep may see, or to a prospect), change or move a planned one, mark it
 * missed, cancel it, and plan a route (several visits of one day). A visit is logged as DONE only on the phone, because "done" needs the
 * check-in selfie at the place (proof of visit). The rules are the phone's (store.saveMeeting / saveRoute): a plan is today up to 90 days
 * ahead, a route is 2 to 10 stops of one day, a stop moved to another day leaves its route, the last stop leaving deletes the route.
 */
final class DeskMeetings
{
    public const AREA = 'meetings';

    public const ROUTE_MINUTES = 90;   // the phone's spacing between two stops

    public const MAX_STOPS = 10;

    public const PURPOSES = ['Price talk' => 'Price talk', 'Demo' => 'Demo', 'Payment' => 'Payment', 'Service' => 'Service'];

    public function __construct(private readonly DeskSync $sync) {}

    /** @param array{party:string, at:mixed, purpose?:?string, contact_uuids?:array} $d  party = "client:<uuid>" or "prospect:<uuid>" */
    public function schedule(User $rep, array $d): SalesMeeting
    {
        $at = $this->plannedAt($d['at'] ?? null);
        $uuid = (string) Str::uuid();

        $this->sync->one($rep, self::AREA, 'meetings', $this->party($rep, (string) ($d['party'] ?? ''), $d['contact_uuids'] ?? []) + [
            'uuid' => $uuid,
            'meeting_at' => DeskSync::iso($at),
            'purpose' => DeskSync::text($d['purpose'] ?? '', 200),
            'discussion' => '',
            'outcome' => '',
            'next_followup_at' => null,
            'status' => 'planned',
            'scheduled_at' => DeskSync::iso($at),
            'ended_at' => null,
            'route_uuid' => null,
            'result' => null,
            'moved_from' => null,
            'time_manual' => true,
            'updated_at' => DeskSync::stamp(),
        ]);

        return SalesMeeting::where('uuid', $uuid)->firstOrFail();
    }

    /** Change a planned visit: time, purpose, people. A new day takes it out of its route. */
    public function update(User $rep, SalesMeeting $m, array $d): void
    {
        $this->mustBeOpen($rep, $m);
        $at = $this->plannedAt($d['at'] ?? null, $m);

        $people = $m->client_uuid ? $this->people($rep, $m->client_uuid, $d['contact_uuids'] ?? []) : [];
        $this->writeWithRoute($rep, $m, [
            'meeting_at' => DeskSync::iso($at),
            'scheduled_at' => DeskSync::iso($at),
            'purpose' => DeskSync::text($d['purpose'] ?? '', 200),
            'contact_uuids' => $people,
            'time_manual' => true,
        ], $at);
    }

    /** "Move": the visit happens another time; it keeps where it was planned first (moved_from), like the phone's Move. */
    public function move(User $rep, SalesMeeting $m, mixed $to): void
    {
        $this->mustBeOpen($rep, $m);
        $at = $this->plannedAt($to);

        $this->writeWithRoute($rep, $m, [
            'status' => 'moved',
            'meeting_at' => DeskSync::iso($at),
            'scheduled_at' => DeskSync::iso($at),
            'moved_from' => DeskSync::iso($m->scheduled_at ?? $m->meeting_at),
            'time_manual' => true,
            'result' => null,
        ], $at);
    }

    public function missed(User $rep, SalesMeeting $m): void
    {
        $this->mustBeOpen($rep, $m);
        $planned = $m->scheduled_at ?? $m->meeting_at;

        $this->sync->one($rep, self::AREA, 'meetings', $this->wire($m, [
            'status' => 'missed',
            'meeting_at' => DeskSync::iso($planned),
            'scheduled_at' => DeskSync::iso($planned),
            'result' => null,
        ]));
    }

    /** Cancel = the planned visit is deleted (the phone's "Cancel meeting"); its route loses the stop. */
    public function cancel(User $rep, SalesMeeting $m): void
    {
        $this->mustBeOpen($rep, $m);

        $this->sync->push($rep, self::AREA, array_merge_recursive(['deleted' => ['meetings' => [$m->uuid]]], $this->leaveRoute($m)));
    }

    /**
     * One route: the day, the first visit's time, 2 to 10 stops in order. Every stop becomes a planned visit, ROUTE_MINUTES apart.
     * @param list<string> $stops  "client:<uuid>" / "prospect:<uuid>"
     */
    public function planRoute(User $rep, string $date, string $startTime, array $stops, ?string $area = null): SalesRoute
    {
        $stops = array_values(array_unique(array_filter($stops, 'is_string')));
        if (count($stops) < 2 || count($stops) > self::MAX_STOPS) {
            throw ValidationException::withMessages(['desk' => 'A route needs 2 to ' . self::MAX_STOPS . ' stops.']);
        }
        if (! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $startTime)) {
            throw ValidationException::withMessages(['desk' => 'Pick the time of the first visit.']);
        }

        $tz = config('sales.timezone');
        $first = CarbonImmutable::parse($date . ' ' . $startTime, $tz);
        $this->plannedAt($first);
        if (! $first->addMinutes(self::ROUTE_MINUTES * (count($stops) - 1))->isSameDay($first)) {
            throw ValidationException::withMessages(['desk' => 'All the stops must fit in that day: start earlier or take fewer stops.']);
        }

        $routeUuid = (string) Str::uuid();
        $meetings = [];
        $order = [];
        foreach ($stops as $i => $party) {
            $at = $first->addMinutes(self::ROUTE_MINUTES * $i);
            $uuid = (string) Str::uuid();
            $order[] = $uuid;
            $meetings[] = $this->party($rep, $party, []) + [
                'uuid' => $uuid,
                'meeting_at' => DeskSync::iso($at),
                'purpose' => '', 'discussion' => '', 'outcome' => '', 'next_followup_at' => null,
                'status' => 'planned',
                'scheduled_at' => DeskSync::iso($at),
                'ended_at' => null,
                'route_uuid' => $routeUuid,
                'result' => null,
                'moved_from' => null,
                'time_manual' => false,
                'updated_at' => DeskSync::stamp(),
            ];
        }

        $area = DeskSync::text($area, 100);
        $this->sync->push($rep, self::AREA, [
            'meetings' => $meetings,
            'routes' => [[
                'uuid' => $routeUuid,
                'date' => $first->toDateString(),
                'title' => mb_substr($area !== '' ? $area . ' run' : 'Route', 0, 60),
                'area' => $area,
                'start_time' => $startTime,
                'overnight' => false,
                'stops_order' => $order,
                'updated_at' => DeskSync::stamp(),
            ]],
        ]);

        return SalesRoute::where('uuid', $routeUuid)->firstOrFail();
    }

    /**
     * @return array<string,string> the clients this rep may see and the open prospects, as "client:<uuid>" / "prospect:<uuid>" => label
     */
    public static function parties(User $rep, string $search = '', int $limit = 40): array
    {
        $like = '%' . addcslashes($search, '%_\\') . '%';

        $clients = SalesClient::query()->visibleTo($rep)
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('city', 'like', $like)))
            ->orderBy('name')->limit($limit)->get(['uuid', 'name', 'city'])
            ->mapWithKeys(fn ($c) => ['client:' . $c->uuid => $c->name . ($c->city ? ' · ' . $c->city : '')]);

        $closed = \App\Sales\Models\SalesProspectNote::query()->whereIn('outcome', ['do_not_call', 'converted', 'wrong_number'])->select('prospect_uuid');
        $prospects = SalesProspect::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('territory', 'like', $like)))
            ->whereNotIn('uuid', $closed)
            ->orderBy('name')->limit($limit)->get(['uuid', 'name', 'territory'])
            ->mapWithKeys(fn ($p) => ['prospect:' . $p->uuid => $p->name . ' · ' . $p->territory . ' (prospect)']);

        return $clients->all() + $prospects->all();
    }

    public static function partyLabel(User $rep, ?string $value): ?string
    {
        [$kind, $uuid] = array_pad(explode(':', (string) $value, 2), 2, '');

        return match ($kind) {
            'client' => SalesClient::query()->visibleTo($rep)->where('uuid', $uuid)->value('name'),
            'prospect' => ($name = SalesProspect::where('uuid', $uuid)->value('name')) ? $name . ' (prospect)' : null,
            default => null,
        };
    }

    // ---- inside ---------------------------------------------------------------------------------------------------------------------

    /** @return array{client_uuid:?string, prospect_uuid:?string, client_name:string, contact_uuids:list<string>} */
    private function party(User $rep, string $party, array $contacts): array
    {
        [$kind, $uuid] = array_pad(explode(':', $party, 2), 2, '');

        if ($kind === 'client') {
            $client = SalesClient::query()->visibleTo($rep)->where('uuid', $uuid)->first();
            if ($client) {
                return ['client_uuid' => $client->uuid, 'prospect_uuid' => null, 'client_name' => $client->name, 'contact_uuids' => $this->people($rep, $client->uuid, $contacts)];
            }
        }
        if ($kind === 'prospect') {
            $prospect = SalesProspect::where('uuid', $uuid)->first();
            if ($prospect) {
                return ['client_uuid' => null, 'prospect_uuid' => $prospect->uuid, 'client_name' => $prospect->name, 'contact_uuids' => []];
            }
        }

        throw ValidationException::withMessages(['desk' => 'Pick one of your clients or a prospect.']);
    }

    /** @return list<string> only people of that client that this rep may see */
    private function people(User $rep, string $clientUuid, mixed $wanted): array
    {
        $wanted = array_values(array_filter((array) $wanted, 'is_string'));
        if ($wanted === []) {
            return [];
        }

        return SalesContact::query()->visibleTo($rep)->where('client_uuid', $clientUuid)->whereIn('uuid', $wanted)->limit(20)->pluck('uuid')->all();
    }

    /** A planned time: today (business time zone) up to 90 days ahead. An unchanged time of an overdue plan may stay (the phone's rule). */
    private function plannedAt(mixed $value, ?SalesMeeting $existing = null): CarbonImmutable
    {
        if (! filled($value)) {
            throw ValidationException::withMessages(['desk' => 'Pick the day and time of the visit.']);
        }

        $tz = config('sales.timezone');
        $at = ($value instanceof \DateTimeInterface ? CarbonImmutable::instance($value) : CarbonImmutable::parse($value, $tz))->utc()->startOfMinute();

        if ($existing && $existing->meeting_at && $at->equalTo(CarbonImmutable::instance($existing->meeting_at)->utc()->startOfMinute())) {
            return CarbonImmutable::instance($existing->meeting_at)->utc();
        }

        $today = CarbonImmutable::now($tz)->startOfDay();
        $local = $at->timezone($tz);
        if ($local->lt($today)) {
            throw ValidationException::withMessages(['desk' => 'That day has passed. Pick today or a later day.']);
        }
        if ($local->gt($today->addDays(SalesMeeting::PLAN_DAYS)->endOfDay())) {
            throw ValidationException::withMessages(['desk' => 'A visit can be planned at most ' . SalesMeeting::PLAN_DAYS . ' days ahead.']);
        }

        return $at;
    }

    private function mustBeOpen(User $rep, SalesMeeting $m): void
    {
        abort_unless((int) $m->user_id === (int) $rep->id, 404);

        if (! in_array($m->status ?? 'done', SalesMeeting::OPEN, true)) {
            throw ValidationException::withMessages(['desk' => 'Only a planned visit can be changed here. A visit that happened is written up on the phone.']);
        }
    }

    /** Write the meeting; a stop whose day changed leaves its route in the same push. */
    private function writeWithRoute(User $rep, SalesMeeting $m, array $changes, CarbonImmutable $at): void
    {
        $route = $m->route_uuid ? SalesRoute::where('uuid', $m->route_uuid)->first() : null;
        $sameDay = $route && $route->route_date->toDateString() === $at->timezone(config('sales.timezone'))->toDateString();

        if ($route && ! $sameDay) {
            $changes['route_uuid'] = null;
            $this->sync->push($rep, self::AREA, array_merge_recursive(['meetings' => [$this->wire($m, $changes)]], $this->leaveRoute($m)));

            return;
        }

        $this->sync->one($rep, self::AREA, 'meetings', $this->wire($m, $changes));
    }

    /** @return array the push part that takes this meeting out of its route (the route deleted when it was the last stop) */
    private function leaveRoute(SalesMeeting $m): array
    {
        $route = $m->route_uuid ? SalesRoute::where('uuid', $m->route_uuid)->first() : null;
        if (! $route) {
            return [];
        }

        $order = array_values(array_filter($route->stops_order ?? [], fn ($s) => $s !== $m->uuid));
        if ($order === []) {
            return ['deleted' => ['routes' => [$route->uuid]]];
        }

        return ['routes' => [[
            'uuid' => $route->uuid,
            'date' => $route->route_date->toDateString(),
            'title' => $route->title,
            'area' => $route->area ?? '',
            'start_time' => $route->start_time,
            'overnight' => (bool) $route->overnight,
            'stops_order' => $order,
            'updated_at' => DeskSync::stamp($route->client_updated_at),
        ]]];
    }

    /** The whole meeting in the wire format (the sync replaces the plain fields, so nothing may be left out), with the changes on top. */
    private function wire(SalesMeeting $m, array $changes): array
    {
        return array_merge([
            'uuid' => $m->uuid,
            'client_uuid' => $m->client_uuid,
            'prospect_uuid' => $m->prospect_uuid,
            'client_name' => $m->client_name,
            'contact_uuids' => $m->contact_uuids ?? [],
            'meeting_at' => DeskSync::iso($m->meeting_at),
            'purpose' => $m->purpose ?? '',
            'discussion' => $m->discussion ?? '',
            'outcome' => $m->outcome ?? '',
            'next_followup_at' => DeskSync::iso($m->next_followup_at),
            'status' => $m->status ?? 'done',
            'scheduled_at' => DeskSync::iso($m->scheduled_at),
            'ended_at' => DeskSync::iso($m->ended_at),
            'route_uuid' => $m->route_uuid,
            'result' => $m->result,
            'moved_from' => DeskSync::iso($m->moved_from),
            'time_manual' => (bool) $m->time_manual,
            'updated_at' => DeskSync::stamp($m->client_updated_at),
        ], $changes);
    }
}
