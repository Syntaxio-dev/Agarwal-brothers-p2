<?php

namespace App\Sales\Services;

use App\Models\User;
use App\Sales\Models\SalesCall;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesDailyReport;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesPosition;
use App\Sales\Models\SalesProductRequest;
use App\Sales\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * The server side of the phone's sync. Rules that make it safe to call again and again:
 *  - every record has the UUID the phone made, so the same record never exists twice (idempotent);
 *  - PRIVATE records (expenses, meetings, calls) can only ever be touched by the rep who made them (every query is filtered by
 *    user_id), and an UUID that belongs to somebody else is refused without saying so;
 *  - SHARED records (the client pool: clients and their contacts, plus the position list) are visible to every rep and can be
 *    improved by every rep (the newest change wins), but only the rep who added one can delete it (managers can in the admin);
 *  - the phone can never set an expense's status or who a client is assigned to: those belong to the office;
 *  - every field is checked again here with the same limits the app uses; a bad record is rejected on its own and never
 *    stops the others.
 * Each record gets one answer: accepted (new or changed), duplicate (nothing newer than what is stored) or rejected (with a code).
 */
class SyncService
{
    /** Kinds that carry records, in the order they are pushed (a client before the people at it). */
    public const KINDS = ['expenses', 'clients', 'contacts', 'meetings', 'enquiries', 'calls', 'reports', 'product_requests'];

    /** Kinds the phone may delete (a call is a log, a report is a record: never). */
    public const DELETABLE = ['expenses', 'clients', 'contacts', 'meetings', 'enquiries'];

    private const MAX_POSITIONS = 300;

    /** @return array{results: array<string, mixed>, counts: array{received: int, accepted: int, duplicates: int, rejected: int}, samples: list<array>} */
    public function push(User $user, array $payload): array
    {
        $results = array_fill_keys(self::KINDS, []) + ['positions' => [], 'deleted' => array_fill_keys(self::DELETABLE, [])];

        $methods = [
            'expenses' => 'upsertExpense', 'clients' => 'upsertClient', 'contacts' => 'upsertContact', 'meetings' => 'upsertMeeting',
            'enquiries' => 'upsertEnquiry', 'calls' => 'upsertCall', 'reports' => 'upsertReport', 'product_requests' => 'upsertProductRequest',
        ];

        foreach ($methods as $kind => $method) {
            foreach (array_values((array) ($payload[$kind] ?? [])) as $index => $record) {
                $results[$kind][] = $this->guarded(fn () => $this->{$method}($user, (array) $record), $record, $index);
            }
        }

        foreach (array_values((array) ($payload['positions'] ?? [])) as $index => $name) {
            try {
                $results['positions'][] = $this->addPosition($user, $name, $index);
            } catch (\Throwable $e) {
                $results['positions'][] = ['uuid' => null, 'index' => $index, 'name' => is_string($name) ? $name : '', 'status' => 'duplicate'];
            }
        }

        foreach (self::DELETABLE as $kind) {
            foreach (array_values((array) ($payload['deleted'][$kind] ?? [])) as $uuid) {
                $results['deleted'][$kind][] = $this->guarded(fn () => $this->delete($user, $kind, (string) $uuid), ['uuid' => $uuid], 0);
            }
        }

        $counts = ['received' => 0, 'accepted' => 0, 'duplicates' => 0, 'rejected' => 0];
        $samples = [];
        $walk = function (array $list) use (&$counts, &$samples) {
            foreach ($list as $one) {
                $counts['received']++;
                match ($one['status']) {
                    'accepted' => $counts['accepted']++,
                    'duplicate' => $counts['duplicates']++,
                    default => $counts['rejected']++,
                };
                if ($one['status'] === 'rejected' && count($samples) < 20) {
                    $samples[] = ['uuid' => $one['uuid'] ?? null, 'code' => $one['code'] ?? null];
                }
            }
        };
        foreach (self::KINDS as $kind) {
            $walk($results[$kind]);
        }
        $walk($results['positions']);
        foreach (self::DELETABLE as $kind) {
            $walk($results['deleted'][$kind]);
        }

        return ['results' => $results, 'counts' => $counts, 'samples' => $samples];
    }

    /** One record's answer; a crash on one record must not lose the answers for the others. */
    private function guarded(callable $work, mixed $record, int $index): array
    {
        $uuid = is_array($record) && is_string($record['uuid'] ?? null) ? $record['uuid'] : null;

        try {
            return ['uuid' => $uuid, 'index' => $index] + $work();
        } catch (\Throwable $e) {
            // only what went wrong, never the record: a database error message carries the values (names, numbers)
            Log::warning('Sales sync: a record could not be saved', ['exception' => $e::class, 'code' => $e->getCode(), 'uuid' => $uuid]);

            return ['uuid' => $uuid, 'index' => $index, 'status' => 'rejected', 'code' => 'SERVER_ERROR', 'message' => 'This record could not be saved.'];
        }
    }

    // ---- expenses (private) ---------------------------------------------------------

    private function upsertExpense(User $user, array $r): array
    {
        $invalid = $this->check($r, [
            'uuid' => ['required', 'uuid'],
            'category' => ['required', Rule::in(config('sales.expense_categories'))],
            'amount_paise' => ['required', 'integer', 'min:1', 'max:' . (int) config('sales.max_expense_rupees') * 100],
            'expense_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2020-01-01', 'before_or_equal:' . $this->latestDay()],
            'note' => ['nullable', 'string', 'max:1000'],
            'has_bill' => ['sometimes', 'boolean'],
            'updated_at' => ['required', 'date', $this->clockRule()],
        ]);
        if ($invalid) {
            return $invalid;
        }

        $model = SalesExpense::withTrashed()->where('uuid', $r['uuid'])->first();

        if ($early = $this->earlyAnswer($model, $user, $r['updated_at'])) {
            return $early;
        }
        if ($model && ! $model->isPending()) {
            return $this->rejected('LOCKED', 'This expense was already reviewed and can no longer be changed.');
        }

        $model ??= new SalesExpense(['uuid' => $r['uuid'], 'user_id' => $user->id]);
        $model->fill([
            'category' => $r['category'],
            'amount_paise' => $r['amount_paise'],
            'expense_date' => $r['expense_date'],
            'note' => filled($r['note'] ?? null) ? trim($r['note']) : null,
            'client_updated_at' => $this->time($r['updated_at']),
        ]);

        // The phone says it has no bill photo (any more): drop the stored one.
        if (! filter_var($r['has_bill'] ?? false, FILTER_VALIDATE_BOOLEAN) && $model->bill_path) {
            Storage::disk('local')->delete($model->bill_path);
            $model->bill_path = null;
            $model->bill_mime = null;
        }

        $model->save();

        return ['status' => 'accepted'];
    }

    // ---- clients (shared pool) ------------------------------------------------------

    private function upsertClient(User $user, array $r): array
    {
        $invalid = $this->check($r, [
            'uuid' => ['required', 'uuid'],
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'company' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30', $this->phoneRule()],
            'city' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'updated_at' => ['required', 'date', $this->clockRule()],
        ]);
        if ($invalid) {
            return $invalid;
        }

        $model = SalesClient::withTrashed()->where('uuid', $r['uuid'])->first();

        if ($early = $this->earlyAnswer($model, $user, $r['updated_at'], shared: true)) {
            return $early;
        }
        if ($taken = $this->numberTaken($r['phone'] ?? null, exceptClient: $r['uuid'])) {
            return $this->rejected('INVALID', "This number is already saved under {$taken}.");
        }

        // A client the rep adds is worked by that rep; who it is assigned to is only ever changed in the admin.
        $model ??= new SalesClient(['uuid' => $r['uuid'], 'user_id' => $user->id, 'assigned_to' => $user->id]);
        $model->fill([
            'name' => trim($r['name']),
            'company' => $this->text($r['company'] ?? null),
            'phone' => $this->text($r['phone'] ?? null),
            'city' => $this->text($r['city'] ?? null),
            'notes' => $this->text($r['notes'] ?? null),
            'updated_by' => $user->id,
            'client_updated_at' => $this->time($r['updated_at']),
        ])->save();

        return ['status' => 'accepted'];
    }

    // ---- contacts: the people at a client (shared pool) -------------------------------

    private function upsertContact(User $user, array $r): array
    {
        $invalid = $this->check($r, [
            'uuid' => ['required', 'uuid'],
            'client_uuid' => ['required', 'uuid'],
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'position' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30', $this->phoneRule()],
            'email' => ['nullable', 'string', 'max:150', 'email'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'updated_at' => ['required', 'date', $this->clockRule()],
        ]);
        if ($invalid) {
            return $invalid;
        }

        $model = SalesContact::withTrashed()->where('uuid', $r['uuid'])->first();

        if ($early = $this->earlyAnswer($model, $user, $r['updated_at'], shared: true)) {
            return $early;
        }
        if ($taken = $this->numberTaken($r['phone'] ?? null, exceptContact: $r['uuid'])) {
            return $this->rejected('INVALID', "This number is already saved under {$taken}.");
        }

        $model ??= new SalesContact(['uuid' => $r['uuid'], 'user_id' => $user->id]);
        $model->fill([
            'client_uuid' => $r['client_uuid'],
            'name' => trim($r['name']),
            'position' => $this->text($r['position'] ?? null),
            'phone' => $this->text($r['phone'] ?? null),
            'email' => $this->text($r['email'] ?? null),
            'notes' => $this->text($r['notes'] ?? null),
            'updated_by' => $user->id,
            'client_updated_at' => $this->time($r['updated_at']),
        ])->save();

        // A position typed on the contact joins the shared list, so the next rep finds it in the dropdown.
        if (filled($model->position)) {
            try {
                $this->addPosition($user, $model->position, 0);
            } catch (\Throwable) {
                // a parallel push added the same one: nothing to do
            }
        }

        return ['status' => 'accepted'];
    }

    // ---- positions (shared list) ----------------------------------------------------

    private function addPosition(User $user, mixed $name, int $index): array
    {
        $name = is_string($name) ? trim(preg_replace('/\s+/u', ' ', $name) ?? '') : '';

        // Letters, digits and a few separators only: the list ends up in a dropdown and in the admin.
        if (mb_strlen($name) < 2 || mb_strlen($name) > 60 || ! preg_match('/^[\p{L}\p{N} &\/().,\'-]+$/u', $name)) {
            return ['uuid' => null, 'index' => $index, 'name' => $name, 'status' => 'rejected', 'code' => 'INVALID', 'message' => 'A position is 2 to 60 letters or digits.'];
        }

        if (SalesPosition::query()->where('name', $name)->exists()) {   // the column is case-insensitive
            return ['uuid' => null, 'index' => $index, 'name' => $name, 'status' => 'duplicate'];
        }
        if (SalesPosition::query()->count() >= self::MAX_POSITIONS) {
            return ['uuid' => null, 'index' => $index, 'name' => $name, 'status' => 'rejected', 'code' => 'INVALID', 'message' => 'The position list is full. Ask your manager.'];
        }

        SalesPosition::create(['name' => $name, 'is_preset' => false, 'created_by' => $user->id]);

        return ['uuid' => null, 'index' => $index, 'name' => $name, 'status' => 'accepted'];
    }

    // ---- meetings (private) -----------------------------------------------------------

    private function upsertMeeting(User $user, array $r): array
    {
        $latest = $this->latestMoment();

        $invalid = $this->check($r, [
            'uuid' => ['required', 'uuid'],
            'client_uuid' => ['nullable', 'uuid'],
            'client_name' => ['required', 'string', 'min:1', 'max:150'],
            'contact_uuids' => ['nullable', 'array', 'max:20'],
            'contact_uuids.*' => ['uuid'],
            'meeting_at' => ['required', 'date', 'after_or_equal:2020-01-01', 'before_or_equal:' . $latest->toIso8601String()],
            'purpose' => ['nullable', 'string', 'max:200'],
            'discussion' => ['nullable', 'string', 'max:2000'],
            'outcome' => ['nullable', 'string', 'max:200'],
            'next_followup_at' => ['nullable', 'date', 'after_or_equal:meeting_at', 'before_or_equal:' . now()->addYears(2)->toIso8601String()],
            'updated_at' => ['required', 'date', $this->clockRule()],
        ]);
        if ($invalid) {
            return $invalid;
        }

        $model = SalesMeeting::withTrashed()->where('uuid', $r['uuid'])->first();

        if ($early = $this->earlyAnswer($model, $user, $r['updated_at'])) {
            return $early;
        }

        $model ??= new SalesMeeting(['uuid' => $r['uuid'], 'user_id' => $user->id]);
        $model->fill([
            'client_uuid' => $r['client_uuid'] ?? null,
            'client_name' => trim($r['client_name']),
            'contact_uuids' => array_values(array_unique($r['contact_uuids'] ?? [])) ?: null,
            'meeting_at' => $this->time($r['meeting_at']),
            'purpose' => $this->text($r['purpose'] ?? null),
            'discussion' => $this->text($r['discussion'] ?? null),
            'outcome' => $this->text($r['outcome'] ?? null),
            'next_followup_at' => filled($r['next_followup_at'] ?? null) ? $this->time($r['next_followup_at']) : null,
            'client_updated_at' => $this->time($r['updated_at']),
        ])->save();

        return ['status' => 'accepted'];
    }

    // ---- calls (private, a log) ----------------------------------------------------------

    private function upsertCall(User $user, array $r): array
    {
        $invalid = $this->check($r, [
            'uuid' => ['required', 'uuid'],
            'direction' => ['required', Rule::in(SalesCall::DIRECTIONS)],
            'number' => ['required', 'string', 'regex:/^\+[0-9]{8,15}$/'],
            'client_uuid' => ['nullable', 'uuid'],
            'contact_uuid' => ['nullable', 'uuid'],
            'client_name' => ['nullable', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'started_at' => ['required', 'date', 'after_or_equal:2020-01-01', 'before_or_equal:' . $this->latestMoment()->toIso8601String()],
            'duration_seconds' => ['required', 'integer', 'min:0', 'max:86400'],
            'updated_at' => ['required', 'date', $this->clockRule()],
        ]);
        if ($invalid) {
            return $invalid;
        }

        // The promise to the reps: a call that is not with a saved client number never reaches the office. The phone keeps that
        // promise, and the office checks it too (a number that was saved once and deleted later still counts).
        if (! SalesCall::query()->where('uuid', $r['uuid'])->exists() && ! $this->inPool($r['number'])) {
            return $this->rejected('INVALID', 'This number is not a saved client number.');
        }

        $model = SalesCall::query()->where('uuid', $r['uuid'])->first();

        // A call never changes: the second time it is just a repeat.
        if ($model) {
            return (int) $model->user_id === (int) $user->id ? ['status' => 'duplicate'] : $this->rejected('UUID_TAKEN', 'This id cannot be used.');
        }

        SalesCall::create([
            'uuid' => $r['uuid'],
            'user_id' => $user->id,
            'direction' => $r['direction'],
            'number' => $r['number'],
            'client_uuid' => $r['client_uuid'] ?? null,
            'contact_uuid' => $r['contact_uuid'] ?? null,
            'client_name' => $this->text($r['client_name'] ?? null),
            'contact_name' => $this->text($r['contact_name'] ?? null),
            'started_at' => $this->time($r['started_at']),
            'duration_seconds' => (int) $r['duration_seconds'],
            'client_updated_at' => $this->time($r['updated_at']),
        ]);

        return ['status' => 'accepted'];
    }

    // ---- product guide: a rep asks for a note (private, a log: sent once, never changed) ----------------

    private const MAX_OPEN_PRODUCT_REQUESTS = 30;

    private function upsertProductRequest(User $user, array $r): array
    {
        $invalid = $this->check($r, [
            'uuid' => ['required', 'uuid'],
            'product_uuid' => ['nullable', 'uuid'],
            'product_name' => ['required', 'string', 'min:2', 'max:200'],
            'body' => ['required', 'string', 'min:3', 'max:500'],
            'updated_at' => ['required', 'date', $this->clockRule()],
        ]);
        if ($invalid) {
            return $invalid;
        }

        $model = SalesProductRequest::query()->where('uuid', $r['uuid'])->first();
        if ($model) {
            return (int) $model->user_id === (int) $user->id ? ['status' => 'duplicate'] : $this->rejected('UUID_TAKEN', 'This id cannot be used.');
        }

        if (SalesProductRequest::query()->where('user_id', $user->id)->where('status', 'open')->count() >= self::MAX_OPEN_PRODUCT_REQUESTS) {
            return $this->rejected('INVALID', 'Too many requests are waiting for your manager. Please wait for answers first.');
        }

        SalesProductRequest::create([
            'uuid' => $r['uuid'],
            'user_id' => $user->id,
            'product_uuid' => $r['product_uuid'] ?? null,
            'product_name' => trim($r['product_name']),
            'body' => trim($r['body']),
            'client_updated_at' => $this->time($r['updated_at']),
        ]);

        return ['status' => 'accepted'];
    }

    // ---- enquiries (private) -----------------------------------------------------------

    private function upsertEnquiry(User $user, array $r): array
    {
        $statuses = array_keys(SalesEnquiry::STATUSES);

        $invalid = $this->check($r, [
            'uuid' => ['required', 'uuid'],
            'client_uuid' => ['nullable', 'uuid'],
            'client_name' => ['required', 'string', 'min:2', 'max:150'],
            'contact_uuid' => ['nullable', 'uuid'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'status' => ['required', Rule::in($statuses)],
            'items' => ['required', 'array', 'min:1', 'max:10'],
            'items.*.product' => ['required', 'string', 'min:2', 'max:150'],
            'items.*.brands' => ['nullable', 'string', 'max:150'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.budget_paise' => ['nullable', 'integer', 'min:0', 'max:100000000000'],
            'opened_at' => ['required', 'date', 'after_or_equal:2020-01-01', 'before_or_equal:' . $this->latestMoment()->toIso8601String()],
            'next_followup_at' => ['nullable', 'date', 'after_or_equal:2020-01-01', 'before_or_equal:' . now()->addYears(2)->toIso8601String()],
            'closed_at' => ['nullable', 'date'],
            'timeline' => ['nullable', 'array', 'max:100'],
            'timeline.*.at' => ['required', 'date'],
            'timeline.*.text' => ['nullable', 'string', 'max:500'],
            'timeline.*.status' => ['nullable', Rule::in($statuses)],
            'updated_at' => ['required', 'date', $this->clockRule()],
        ]);
        if ($invalid) {
            return $invalid;
        }

        $model = SalesEnquiry::withTrashed()->where('uuid', $r['uuid'])->first();

        if ($early = $this->earlyAnswer($model, $user, $r['updated_at'])) {
            return $early;
        }

        $closed = in_array($r['status'], SalesEnquiry::CLOSED, true);

        $model ??= new SalesEnquiry(['uuid' => $r['uuid'], 'user_id' => $user->id]);
        $model->fill([
            'client_uuid' => $r['client_uuid'] ?? null,
            'client_name' => trim($r['client_name']),
            'contact_uuid' => $r['contact_uuid'] ?? null,
            'contact_name' => $this->text($r['contact_name'] ?? null),
            'status' => $r['status'],
            // only the known keys, trimmed: nothing else the phone sends is stored
            'items' => collect($r['items'])->map(fn ($i) => [
                'product' => trim($i['product']),
                'brands' => $this->text($i['brands'] ?? null),
                'quantity' => (int) $i['quantity'],
                'budget_paise' => isset($i['budget_paise']) ? (int) $i['budget_paise'] : null,
            ])->values()->all(),
            'opened_at' => $this->time($r['opened_at']),
            // a finished enquiry has nothing left to follow up
            'next_followup_at' => $closed || blank($r['next_followup_at'] ?? null) ? null : $this->time($r['next_followup_at']),
            'closed_at' => $closed ? (filled($r['closed_at'] ?? null) ? $this->time($r['closed_at']) : now()->utc()) : null,
            'timeline' => collect($r['timeline'] ?? [])->map(fn ($t) => [
                'at' => $this->time($t['at'])->format('Y-m-d\TH:i:s\Z'),
                'text' => $this->text($t['text'] ?? null),
                'status' => $t['status'] ?? null,
            ])->values()->all() ?: null,
            'client_updated_at' => $this->time($r['updated_at']),
        ])->save();

        return ['status' => 'accepted'];
    }

    // ---- daily reports (private, one per rep per day) ---------------------------------------

    private const FIGURES = ['calls', 'calls_missed', 'talk_seconds', 'meetings', 'enquiries_new', 'enquiries_worked', 'expenses_paise'];

    private function upsertReport(User $user, array $r): array
    {
        $figureRules = collect(self::FIGURES)->mapWithKeys(fn ($k) => ["figures.$k" => ['nullable', 'integer', 'min:0', 'max:1000000000000']])->all();

        $invalid = $this->check($r, [
            'uuid' => ['required', 'uuid'],
            'report_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:' . CarbonImmutable::now(config('sales.timezone'))->subDays(31)->toDateString(), 'before_or_equal:' . $this->latestDay()],
            'notes' => ['nullable', 'string', 'max:2000'],
            'tomorrow_plan' => ['nullable', 'string', 'max:1000'],
            'issues' => ['nullable', 'string', 'max:1000'],
            'figures' => ['nullable', 'array'],
            'submitted_at' => ['nullable', 'date'],
            'updated_at' => ['required', 'date', $this->clockRule()],
        ] + $figureRules);
        if ($invalid) {
            return $invalid;
        }

        $model = SalesDailyReport::where('uuid', $r['uuid'])->first();

        if ($early = $this->earlyAnswer($model, $user, $r['updated_at'])) {
            return $early;
        }

        // one report per rep per day: a second one for the same day (another id) is refused, the phone keeps the first
        if (! $model && SalesDailyReport::where('user_id', $user->id)->whereDate('report_date', $r['report_date'])->exists()) {
            return $this->rejected('INVALID', 'A report for this day was already sent.');
        }

        $model ??= new SalesDailyReport(['uuid' => $r['uuid'], 'user_id' => $user->id]);
        $model->fill([
            'report_date' => $r['report_date'],
            'notes' => $this->text($r['notes'] ?? null),
            'tomorrow_plan' => $this->text($r['tomorrow_plan'] ?? null),
            'issues' => $this->text($r['issues'] ?? null),
            'figures' => collect(self::FIGURES)->mapWithKeys(fn ($k) => [$k => (int) ($r['figures'][$k] ?? 0)])->all(),
            'submitted_at' => filled($r['submitted_at'] ?? null) ? $this->time($r['submitted_at']) : null,
            'client_updated_at' => $this->time($r['updated_at']),
        ])->save();

        return ['status' => 'accepted'];
    }

    /** Who already has this number in the pool ("Rohit (JK Cement)"), or null. */
    private function numberTaken(?string $phone, ?string $exceptContact = null, ?string $exceptClient = null): ?string
    {
        $normal = filled($phone) ? PhoneNumber::normalize($phone) : null;

        return $normal === null ? null : \App\Sales\Support\ClientImporter::numberOwner($normal, $exceptContact, $exceptClient);
    }

    private function inPool(string $number): bool
    {
        return SalesContact::withTrashed()->where('phone_normalized', $number)->exists()
            || SalesClient::withTrashed()->where('phone_normalized', $number)->exists();
    }

    // ---- deletes ----------------------------------------------------------------

    private function delete(User $user, string $kind, string $uuid): array
    {
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid)) {
            return $this->rejected('INVALID', 'Not a valid id.');
        }

        $class = ['expenses' => SalesExpense::class, 'clients' => SalesClient::class, 'contacts' => SalesContact::class, 'meetings' => SalesMeeting::class, 'enquiries' => SalesEnquiry::class][$kind];
        $model = $class::withTrashed()->where('uuid', $uuid)->first();

        // Unknown here (never reached the server) or already deleted: the wish is already true.
        if (! $model || $model->trashed()) {
            return ['status' => 'accepted'];
        }

        // Somebody else's private record is invisible to this rep; a shared record that somebody else added is not theirs to delete.
        if ((int) $model->user_id !== (int) $user->id) {
            return in_array($kind, ['clients', 'contacts'], true)
                ? $this->rejected('NOT_ALLOWED', 'Only the person who added it, or a manager, can delete this.')
                : ['status' => 'accepted'];
        }

        if ($model instanceof SalesExpense) {
            if (! $model->isPending()) {
                return $this->rejected('LOCKED', 'This expense was already reviewed and cannot be deleted.');
            }
            if ($model->bill_path) {
                Storage::disk('local')->delete($model->bill_path);
            }
        }

        $model->delete();

        return ['status' => 'accepted'];
    }

    // ---- pull -------------------------------------------------------------------

    /** What changed since `$since` (all of it when null): this rep's own records, the shared pool, and the position list. */
    public function pull(User $user, ?CarbonImmutable $since): array
    {
        $startedAt = CarbonImmutable::now('UTC');
        $limit = (int) config('sales.max_pull_records');
        $capped = [];
        $out = [];

        // [class, shape, shared?]
        $kinds = [
            'expenses' => [SalesExpense::class, fn (SalesExpense $e) => $this->expensePayload($e), false],
            'clients' => [SalesClient::class, fn (SalesClient $c) => $this->clientPayload($c), true],
            'contacts' => [SalesContact::class, fn (SalesContact $c) => $this->contactPayload($c), true],
            'meetings' => [SalesMeeting::class, fn (SalesMeeting $m) => $this->meetingPayload($m), false],
            'enquiries' => [SalesEnquiry::class, fn (SalesEnquiry $e) => $this->enquiryPayload($e), false],
            'calls' => [SalesCall::class, fn (SalesCall $c) => $this->callPayload($c), false],
            'reports' => [SalesDailyReport::class, fn (SalesDailyReport $d) => $this->reportPayload($d), false],
        ];

        foreach ($kinds as $kind => [$class, $shape, $shared]) {
            $query = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($class), true) ? $class::withTrashed() : $class::query();

            if ($kind === 'clients') {
                $query->with(['owner.salesProfile', 'assignee.salesProfile']);
            }
            if ($kind === 'contacts') {
                $query->with('addedBy.salesProfile');
            }

            $base = fn () => (clone $query)
                ->when(! $shared, fn ($q) => $q->where('user_id', $user->id))
                ->orderBy('updated_at')->orderBy('id');

            $rows = $base()
                ->when($since, fn ($q) => $q->where('updated_at', '>=', $since))
                ->limit($limit + 1)
                ->get();

            if ($rows->count() > $limit) {
                $rows = $rows->take($limit);
                $lastAt = $rows->last()->updated_at;

                // Take everything else stamped with the same second, so the next page can start after it: with thousands of rows in
                // one second (a CSV import, "give to a rep") a cursor that restarts AT that second would return the same page for ever.
                $rows = $rows->concat($base()->where('updated_at', $lastAt)->whereNotIn('id', $rows->pluck('id'))->get());
                $capped[] = $lastAt->lt(now()->subSeconds(3)) ? $lastAt->copy()->addSecond() : $lastAt;
            }

            $out[$kind] = $rows->map($shape)->all();
        }

        // Not everything fit: the next pull must start no later than the last row of a capped kind (>= makes the overlap harmless).
        $cursor = $capped ? min($capped) : $startedAt;

        return $out + [
            'positions' => SalesPosition::names(),
            'has_more' => (bool) $capped,
            'cursor' => CarbonImmutable::instance($cursor)->utc()->format('Y-m-d\TH:i:s\Z'),
            'server_time' => $startedAt->format('Y-m-d\TH:i:s\Z'),
        ];
    }

    private function expensePayload(SalesExpense $e): array
    {
        return [
            'uuid' => $e->uuid,
            'category' => $e->category,
            'amount_paise' => $e->amount_paise,
            'expense_date' => $e->expense_date->toDateString(),
            'note' => $e->note ?? '',
            'has_bill' => $e->hasBill(),
            'status' => $e->status,
            'reviewed_at' => $e->reviewed_at?->utc()->format('Y-m-d\TH:i:s\Z'),
            'reject_reason' => $e->reject_reason,
            'updated_at' => $this->iso($e->client_updated_at),
            'deleted' => $e->trashed(),
        ];
    }

    private function clientPayload(SalesClient $c): array
    {
        return [
            'uuid' => $c->uuid,
            'name' => $c->name,
            'company' => $c->company ?? '',
            'phone' => $c->phone ?? '',
            'city' => $c->city ?? '',
            'notes' => $c->notes ?? '',
            // set by the office only; the phone shows them and never sends them back
            'assigned_to_code' => $c->assignee?->salesProfile?->employee_code,
            'assigned_to_name' => $c->assignee?->name,
            'created_by_code' => $c->owner?->salesProfile?->employee_code,
            'created_by_name' => $c->owner?->name,
            'updated_at' => $this->iso($c->client_updated_at),
            'deleted' => $c->trashed(),
        ];
    }

    private function contactPayload(SalesContact $c): array
    {
        return [
            'uuid' => $c->uuid,
            'client_uuid' => $c->client_uuid,
            'name' => $c->name,
            'position' => $c->position ?? '',
            'phone' => $c->phone ?? '',
            'email' => $c->email ?? '',
            'notes' => $c->notes ?? '',
            'created_by_code' => $c->addedBy?->salesProfile?->employee_code,
            'updated_at' => $this->iso($c->client_updated_at),
            'deleted' => $c->trashed(),
        ];
    }

    private function meetingPayload(SalesMeeting $m): array
    {
        return [
            'uuid' => $m->uuid,
            'client_uuid' => $m->client_uuid,
            'client_name' => $m->client_name,
            'contact_uuids' => $m->contact_uuids ?? [],
            'meeting_at' => $this->iso($m->meeting_at),
            'purpose' => $m->purpose ?? '',
            'discussion' => $m->discussion ?? '',
            'outcome' => $m->outcome ?? '',
            'next_followup_at' => $m->next_followup_at ? $this->iso($m->next_followup_at) : null,
            'updated_at' => $this->iso($m->client_updated_at),
            'deleted' => $m->trashed(),
        ];
    }

    private function enquiryPayload(SalesEnquiry $e): array
    {
        return [
            'uuid' => $e->uuid,
            'client_uuid' => $e->client_uuid,
            'client_name' => $e->client_name,
            'contact_uuid' => $e->contact_uuid,
            'contact_name' => $e->contact_name,
            'status' => $e->status,
            'items' => $e->items ?? [],
            'opened_at' => $this->iso($e->opened_at),
            'next_followup_at' => $e->next_followup_at ? $this->iso($e->next_followup_at) : null,
            'closed_at' => $e->closed_at ? $this->iso($e->closed_at) : null,
            'timeline' => $e->timeline ?? [],
            'updated_at' => $this->iso($e->client_updated_at),
            'deleted' => $e->trashed(),
        ];
    }

    private function reportPayload(SalesDailyReport $d): array
    {
        return [
            'uuid' => $d->uuid,
            'report_date' => $d->report_date->toDateString(),
            'notes' => $d->notes ?? '',
            'tomorrow_plan' => $d->tomorrow_plan ?? '',
            'issues' => $d->issues ?? '',
            'figures' => $d->figures ?? [],
            'submitted_at' => $d->submitted_at ? $this->iso($d->submitted_at) : null,
            'updated_at' => $this->iso($d->client_updated_at),
            'deleted' => false,
        ];
    }

    private function callPayload(SalesCall $c): array
    {
        return [
            'uuid' => $c->uuid,
            'direction' => $c->direction,
            'number' => $c->number,
            'client_uuid' => $c->client_uuid,
            'contact_uuid' => $c->contact_uuid,
            'client_name' => $c->client_name,
            'contact_name' => $c->contact_name,
            'started_at' => $this->iso($c->started_at),
            'duration_seconds' => $c->duration_seconds,
            'updated_at' => $this->iso($c->client_updated_at),
            'deleted' => false,
        ];
    }

    // ---- small helpers ----------------------------------------------------------

    /**
     * Answers that need no write: somebody else's private id, a deleted record, or nothing newer than what is stored.
     * A shared record may be changed by any rep, so it is never "taken".
     */
    private function earlyAnswer(mixed $model, User $user, string $updatedAt, bool $shared = false): ?array
    {
        if (! $model) {
            return null;
        }
        if (! $shared && (int) $model->user_id !== (int) $user->id) {
            return $this->rejected('UUID_TAKEN', 'This id cannot be used.');
        }
        if (method_exists($model, 'trashed') && $model->trashed()) {
            return ['status' => 'duplicate'];
        }

        return $model->client_updated_at && $model->client_updated_at->gte($this->time($updatedAt)) ? ['status' => 'duplicate'] : null;
    }

    /** @return ?array a rejected answer when the record breaks a rule */
    private function check(array $record, array $rules): ?array
    {
        $validator = Validator::make($record, $rules, [
            'updated_at.before_or_equal' => "This phone's clock is ahead of the office's. Set the right date and time on the phone.",
        ]);

        if (! $validator->fails()) {
            return null;
        }

        return $this->rejected('INVALID', (string) $validator->errors()->first(), $validator->errors()->toArray());
    }

    private function rejected(string $code, string $message, array $errors = []): array
    {
        return ['status' => 'rejected', 'code' => $code, 'message' => $message] + ($errors ? ['errors' => $errors] : []);
    }

    /** A change stamped later than this is a wrong phone clock: it would win every later edit for ever. */
    private function clockRule(): string
    {
        return 'before_or_equal:' . now()->addMinutes(10)->toIso8601String();
    }

    private function phoneRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (filled($value) && PhoneNumber::normalize((string) $value) === null) {
                $fail('The phone number is not valid.');
            }
        };
    }

    private function text(mixed $value): ?string
    {
        return filled($value) ? trim((string) $value) : null;
    }

    private function time(string $iso): CarbonImmutable
    {
        return CarbonImmutable::parse($iso)->utc();
    }

    private function iso(mixed $time): string
    {
        return CarbonImmutable::instance($time)->utc()->format('Y-m-d\TH:i:s.v\Z');
    }

    /** The last calendar day an expense may carry: today in the business timezone plus the allowed days ahead. */
    private function latestDay(): string
    {
        return CarbonImmutable::now(config('sales.timezone'))->addDays((int) config('sales.max_future_days'))->toDateString();
    }

    /** The latest moment a meeting or call may have started: the end of that same last day. */
    private function latestMoment(): CarbonImmutable
    {
        return CarbonImmutable::now(config('sales.timezone'))->addDays((int) config('sales.max_future_days'))->endOfDay()->utc();
    }
}
