<?php

namespace App\Sales\Support;

use App\Models\User;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesEnquiry;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Everything a rep changes about HIS enquiries on the desktop. The rules are the phone's (products, quantity, budget, follow-up at most two
 * years ahead, closing clears the follow-up, at most 100 timeline lines of 500 characters), and every change gets a version that is strictly
 * newer than the stored one, so the phone takes it on its next sync and a stale push from the phone is answered "the office is newer".
 * Only the rep's own enquiries can be touched; a client or a person must be one the rep may see.
 */
class RepEnquiryService
{
    public const MAX_TIMELINE = 100;

    public const MAX_TEXT = 500;

    /**
     * @param  array{client_uuid:string, contact_uuid?:?string, status?:?string, source?:?string, items:array, next_followup_at?:mixed, note?:?string}  $d
     */
    public function create(User $rep, array $d): SalesEnquiry
    {
        $this->mustBeRep($rep);
        $status = $d['status'] ?? 'qualification';
        $this->check($d, $status);

        [$client, $contact] = $this->clientAndContact($rep, $d);

        return DB::transaction(function () use ($rep, $d, $status, $client, $contact) {
            $now = CarbonImmutable::now('UTC');
            $note = $this->text($d['note'] ?? null);
            $closing = in_array($status, SalesEnquiry::CLOSED, true);

            return SalesEnquiry::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $rep->id,
                'client_uuid' => $client->uuid,
                'client_name' => $client->name,
                'contact_uuid' => $contact?->uuid,
                'contact_name' => (string) $contact?->name,
                'status' => $status,
                'source' => $this->source($d['source'] ?? null),
                'lead_status' => 'new',
                'items' => $this->items($d['items']),
                'opened_at' => $now,
                'next_followup_at' => $closing ? null : $this->followupAt($d['next_followup_at'] ?? null),
                'closed_at' => $closing ? $now : null,
                'timeline' => [$this->entry($now, $note !== '' ? $note . ' (desktop)' : 'Enquiry opened (desktop)', $status)],
                'client_updated_at' => $now,
            ]);
        });
    }

    /** The edit form: details, and (when the stage changed or a note was typed) a line on the timeline. */
    public function update(SalesEnquiry $enquiry, User $rep, array $d): SalesEnquiry
    {
        $status = $d['status'] ?? $enquiry->status;
        $this->check($d, $status);
        [$client, $contact] = $this->clientAndContact($rep, $d, $enquiry);
        $note = $this->text($d['note'] ?? null);

        return $this->write($enquiry, $rep, function (SalesEnquiry $row) use ($d, $status, $client, $contact) {
            $closing = in_array($status, SalesEnquiry::CLOSED, true);
            $row->fill([
                'client_uuid' => $client->uuid,
                'client_name' => $client->name,
                'contact_uuid' => $contact?->uuid,
                'contact_name' => (string) $contact?->name,
                'status' => $status,
                'source' => $this->source($d['source'] ?? null),
                'items' => $this->items($d['items']),
                'next_followup_at' => $closing ? null : $this->followupAt($d['next_followup_at'] ?? null),
            ]);
        }, $status, $note, $enquiry->status !== $status ? 'Moved to ' . SalesEnquiry::STATUSES[$status] . ' (desktop)' : null);
    }

    public function move(SalesEnquiry $enquiry, User $rep, string $to, ?string $note = null): SalesEnquiry
    {
        $this->check([], $to);

        return $this->write($enquiry, $rep, function (SalesEnquiry $row) use ($to) {
            $row->status = $to;
            if (in_array($to, SalesEnquiry::CLOSED, true)) {
                $row->next_followup_at = null;
            }
        }, $to, $this->text($note), $enquiry->status !== $to ? 'Moved to ' . SalesEnquiry::STATUSES[$to] . ' (desktop)' : null);
    }

    /** A new follow-up date (null = none) with an optional note. A closed enquiry has no follow-up. */
    public function followup(SalesEnquiry $enquiry, User $rep, mixed $at, ?string $note = null): SalesEnquiry
    {
        $when = $this->followupAt($at);

        return $this->write($enquiry, $rep, function (SalesEnquiry $row) use ($when) {
            $row->next_followup_at = in_array($row->status, SalesEnquiry::CLOSED, true) ? null : $when;
        }, null, $this->text($note), $when ? 'Follow-up set for ' . static::local($when) . ' (desktop)' : 'Follow-up removed (desktop)');
    }


    /**
     * One follow-up of a lead (= this enquiry), the same thing the phone does: the lead status, what the client said (remark) and the next
     * reminder. Deal done / Deal lost finish the enquiry (stage Closed Won / Closed Lost, no reminder); every other status needs a next
     * reminder ($at) unless $noReminder is true. A finished enquiry takes no more follow-ups.
     */
    public function leadFollowup(SalesEnquiry $enquiry, User $rep, string $leadStatus, ?string $remark, mixed $at, bool $noReminder = false): SalesEnquiry
    {
        if (! array_key_exists($leadStatus, SalesEnquiry::LEAD_STATUSES)) {
            throw ValidationException::withMessages(['lead_status' => 'Pick one of the follow-up statuses.']);
        }

        $remark = $this->text($remark);
        if (mb_strlen($remark) > SalesEnquiry::LEAD_REMARK_MAX) {
            throw ValidationException::withMessages(['lead_remark' => 'Keep the remark under ' . SalesEnquiry::LEAD_REMARK_MAX . ' characters.']);
        }

        $closesTo = SalesEnquiry::LEAD_CLOSES[$leadStatus] ?? null;
        $when = $closesTo ? null : $this->followupAt($at);
        if (! $closesTo && $when === null && ! $noReminder) {
            throw ValidationException::withMessages(['next_followup_at' => 'Set the next reminder, so this lead is not forgotten.']);
        }
        if (! $closesTo && $when !== null && $when->lt(now()->subDay())) {
            throw ValidationException::withMessages(['next_followup_at' => 'Pick a reminder from now on.']);
        }

        $label = SalesEnquiry::LEAD_STATUSES[$leadStatus];

        return $this->write($enquiry, $rep, function (SalesEnquiry $row) use ($leadStatus, $remark, $closesTo, $when) {
            if (in_array($row->status, SalesEnquiry::CLOSED, true) && ! $closesTo) {
                throw ValidationException::withMessages(['lead_status' => 'This enquiry is finished. Move it back to a stage first.']);
            }

            $row->lead_status = $leadStatus;
            $row->lead_remark = $remark !== '' ? $remark : null;
            $row->last_followup_at = CarbonImmutable::now('UTC');
            $row->next_followup_at = $closesTo ? null : $when;
            if ($closesTo) {
                $row->status = $closesTo;
            }
        }, $enquiry->status, $remark, 'Follow-up: ' . $label . ' (desktop)');
    }

    public function note(SalesEnquiry $enquiry, User $rep, string $note): SalesEnquiry
    {
        $note = $this->text($note);
        if ($note === '') {
            throw ValidationException::withMessages(['note' => 'Write the note first.']);
        }

        return $this->write($enquiry, $rep, fn () => null, null, $note, null);
    }

    // ---- inside ---------------------------------------------------------------------------------------------------------------

    /**
     * One locked write: `$change` edits the row, then the timeline gets `$auto` (what the system did) and/or the rep's `$note`, and the row
     * gets a version strictly newer than the stored one.
     */
    private function write(SalesEnquiry $enquiry, User $rep, Closure $change, ?string $status, string $note, ?string $auto): SalesEnquiry
    {
        $this->mustOwn($rep, $enquiry);

        return DB::transaction(function () use ($enquiry, $change, $status, $note, $auto) {
            $row = SalesEnquiry::whereKey($enquiry->getKey())->lockForUpdate()->firstOrFail();

            $now = CarbonImmutable::now('UTC');
            if ($row->client_updated_at && ! $now->gt($row->client_updated_at)) {
                $now = $row->client_updated_at->addMillisecond();
            }

            $before = $row->status;
            $change($row);

            $timeline = array_values($row->timeline ?? []);
            $lines = [];
            if ($auto !== null) {
                $lines[] = $this->entry($now, $auto . ($note !== '' ? ': ' . $note : ''), $row->status !== $before || $status !== null ? $row->status : null);
            } elseif ($note !== '') {
                $lines[] = $this->entry($now, $note . ' (desktop)', null);
            }
            foreach ($lines as $line) {
                $timeline[] = $line;
            }
            $row->timeline = array_slice($timeline, -self::MAX_TIMELINE);

            if ($row->status !== $before) {
                $row->closed_at = in_array($row->status, SalesEnquiry::CLOSED, true) ? $now : null;   // closing sets it, reopening clears it
            }

            $row->client_updated_at = $now;
            $row->save();

            return $row;
        });
    }

    private function entry(CarbonInterface $at, string $text, ?string $status): array
    {
        return ['at' => $at->utc()->format('Y-m-d\TH:i:s\Z'), 'text' => mb_substr($text, 0, self::MAX_TEXT), 'status' => $status];
    }

    private function mustBeRep(User $rep): void
    {
        abort_unless($rep->is_active && $rep->role === \App\Sales\Models\SalesProfile::ROLE, 403);
    }

    private function mustOwn(User $rep, SalesEnquiry $enquiry): void
    {
        $this->mustBeRep($rep);
        abort_unless((int) $enquiry->user_id === (int) $rep->id, 403);
    }

    private function text(?string $text): string
    {
        return mb_substr(trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', (string) $text)), 0, self::MAX_TEXT);
    }

    private function source(?string $source): ?string
    {
        return filled($source) && array_key_exists($source, SalesEnquiry::SOURCES) ? $source : null;
    }

    private function check(array $d, string $status): void
    {
        if (! array_key_exists($status, SalesEnquiry::STATUSES)) {
            throw ValidationException::withMessages(['status' => 'Pick one of the stages.']);
        }
        if (filled($d['source'] ?? null) && ! array_key_exists($d['source'], SalesEnquiry::SOURCES)) {
            throw ValidationException::withMessages(['source' => 'Pick how the enquiry came through, or leave it empty.']);
        }
        if (array_key_exists('items', $d)) {
            $this->items($d['items']);
        }
    }

    /** @return array<int, array{product:string, brands:string, quantity:int, budget_paise:?int}> */
    private function items(mixed $items): array
    {
        $items = array_values(array_filter((array) $items, 'is_array'));
        if ($items === [] || count($items) > 10) {
            throw ValidationException::withMessages(['items' => 'Add one to ten products.']);
        }

        return array_map(function (array $i) {
            $product = $this->text($i['product'] ?? '');
            $quantity = (int) ($i['quantity'] ?? 1);
            $budget = filled($i['budget_paise'] ?? null) ? (int) $i['budget_paise'] : null;

            if (mb_strlen($product) < 2 || mb_strlen($product) > 150 || $quantity < 1 || $quantity > 100000 || ($budget !== null && ($budget < 0 || $budget > 100000000000))) {
                throw ValidationException::withMessages(['items' => 'Each product needs a name (2 to 150 letters), a quantity of 1 to 100,000 and a sensible budget.']);
            }

            return ['product' => $product, 'brands' => mb_substr($this->text($i['brands'] ?? ''), 0, 150), 'quantity' => $quantity, 'budget_paise' => $budget];
        }, $items);
    }

    /** @return array{0: SalesClient, 1: ?SalesContact} a client the rep may see, and a person of that client (or none) */
    private function clientAndContact(User $rep, array $d, ?SalesEnquiry $existing = null): array
    {
        $uuid = (string) ($d['client_uuid'] ?? $existing?->client_uuid);
        $client = SalesClient::query()->visibleTo($rep)->where('uuid', $uuid)->first();
        if (! $client) {
            throw ValidationException::withMessages(['client_uuid' => 'Pick one of your clients.']);
        }

        $contact = null;
        if (filled($d['contact_uuid'] ?? null)) {
            $contact = SalesContact::query()->visibleTo($rep)->where('uuid', $d['contact_uuid'])->where('client_uuid', $client->uuid)->first();
            if (! $contact) {
                throw ValidationException::withMessages(['contact_uuid' => 'Pick a person of this client.']);
            }
        }

        return [$client, $contact];
    }

    /** A follow-up: any moment from 2020 up to two years ahead (the same cap as the phone and the sync). */
    private function followupAt(mixed $at): ?CarbonImmutable
    {
        if (! filled($at)) {
            return null;
        }

        $moment = CarbonImmutable::parse($at, config('sales.timezone'))->utc();
        if ($moment->lt(CarbonImmutable::parse('2020-01-01')) || $moment->gt(now()->addYears(2))) {
            throw ValidationException::withMessages(['next_followup_at' => 'Pick a follow-up within the next two years.']);
        }

        return $moment;
    }

    private static function local(CarbonInterface $when): string
    {
        return CarbonImmutable::instance($when)->timezone(config('sales.timezone'))->format('d M Y, h:i A');
    }
}
