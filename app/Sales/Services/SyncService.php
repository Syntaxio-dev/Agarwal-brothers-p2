<?php

namespace App\Sales\Services;

use App\Models\User;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesMeeting;
use App\Sales\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * The server side of the phone's sync. Rules that make it safe to call again and again:
 *  - every record has the UUID the phone made, so the same record never exists twice (idempotent);
 *  - a rep can only ever touch their own records (every query is filtered by user_id), and an UUID that belongs to
 *    somebody else is refused without saying so;
 *  - the phone can never set an expense's status: only a manager approves or rejects, and a reviewed expense is locked;
 *  - every field is checked again here with the same limits the app uses; a bad record is rejected on its own and never
 *    stops the others.
 * Each record gets one answer: accepted (new or changed), duplicate (nothing newer than what is stored) or rejected (with a code).
 */
class SyncService
{
    public const KINDS = ['expenses', 'clients', 'meetings'];

    /** @return array{results: array<string, list<array>>, counts: array{received: int, accepted: int, duplicates: int, rejected: int}, samples: list<array>} */
    public function push(User $user, array $payload): array
    {
        $results = ['expenses' => [], 'clients' => [], 'meetings' => [], 'deleted' => ['expenses' => [], 'clients' => [], 'meetings' => []]];

        foreach (['expenses' => 'upsertExpense', 'clients' => 'upsertClient', 'meetings' => 'upsertMeeting'] as $kind => $method) {
            foreach (array_values((array) ($payload[$kind] ?? [])) as $index => $record) {
                $results[$kind][] = $this->guarded(fn () => $this->{$method}($user, (array) $record), $record, $index);
            }
        }

        foreach (self::KINDS as $kind) {
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
            report($e);

            return ['uuid' => $uuid, 'index' => $index, 'status' => 'rejected', 'code' => 'SERVER_ERROR', 'message' => 'This record could not be saved.'];
        }
    }

    // ---- expenses ---------------------------------------------------------------

    private function upsertExpense(User $user, array $r): array
    {
        $invalid = $this->check($r, [
            'uuid' => ['required', 'uuid'],
            'category' => ['required', Rule::in(config('sales.expense_categories'))],
            'amount_paise' => ['required', 'integer', 'min:1', 'max:' . (int) config('sales.max_expense_rupees') * 100],
            'expense_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2020-01-01', 'before_or_equal:' . $this->latestDay()],
            'note' => ['nullable', 'string', 'max:1000'],
            'has_bill' => ['sometimes', 'boolean'],
            'updated_at' => ['required', 'date'],
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

    // ---- clients ----------------------------------------------------------------

    private function upsertClient(User $user, array $r): array
    {
        $invalid = $this->check($r, [
            'uuid' => ['required', 'uuid'],
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'company' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30', $this->phoneRule()],
            'city' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'updated_at' => ['required', 'date'],
        ]);
        if ($invalid) {
            return $invalid;
        }

        $model = SalesClient::withTrashed()->where('uuid', $r['uuid'])->first();

        if ($early = $this->earlyAnswer($model, $user, $r['updated_at'])) {
            return $early;
        }

        $model ??= new SalesClient(['uuid' => $r['uuid'], 'user_id' => $user->id]);
        $model->fill([
            'name' => trim($r['name']),
            'company' => $this->text($r['company'] ?? null),
            'phone' => $this->text($r['phone'] ?? null),
            'city' => $this->text($r['city'] ?? null),
            'notes' => $this->text($r['notes'] ?? null),
            'client_updated_at' => $this->time($r['updated_at']),
        ])->save();

        return ['status' => 'accepted'];
    }

    // ---- meetings ---------------------------------------------------------------

    private function upsertMeeting(User $user, array $r): array
    {
        $latest = $this->latestMoment();

        $invalid = $this->check($r, [
            'uuid' => ['required', 'uuid'],
            'client_uuid' => ['nullable', 'uuid'],
            'client_name' => ['required', 'string', 'min:1', 'max:150'],
            'meeting_at' => ['required', 'date', 'after_or_equal:2020-01-01', 'before_or_equal:' . $latest->toIso8601String()],
            'purpose' => ['nullable', 'string', 'max:200'],
            'discussion' => ['nullable', 'string', 'max:2000'],
            'outcome' => ['nullable', 'string', 'max:200'],
            'next_followup_at' => ['nullable', 'date', 'after_or_equal:meeting_at', 'before_or_equal:' . now()->addYears(2)->toIso8601String()],
            'updated_at' => ['required', 'date'],
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
            'meeting_at' => $this->time($r['meeting_at']),
            'purpose' => $this->text($r['purpose'] ?? null),
            'discussion' => $this->text($r['discussion'] ?? null),
            'outcome' => $this->text($r['outcome'] ?? null),
            'next_followup_at' => filled($r['next_followup_at'] ?? null) ? $this->time($r['next_followup_at']) : null,
            'client_updated_at' => $this->time($r['updated_at']),
        ])->save();

        return ['status' => 'accepted'];
    }

    // ---- deletes ----------------------------------------------------------------

    private function delete(User $user, string $kind, string $uuid): array
    {
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid)) {
            return $this->rejected('INVALID', 'Not a valid id.');
        }

        $class = ['expenses' => SalesExpense::class, 'clients' => SalesClient::class, 'meetings' => SalesMeeting::class][$kind];
        $model = $class::withTrashed()->where('uuid', $uuid)->where('user_id', $user->id)->first();

        // Unknown here (never reached the server) or already deleted: the wish is already true.
        if (! $model || $model->trashed()) {
            return ['status' => 'accepted'];
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

    /** What changed for this rep since `$since` (all of it when null): the phone merges it. */
    public function pull(User $user, ?CarbonImmutable $since): array
    {
        $startedAt = CarbonImmutable::now('UTC');
        $limit = (int) config('sales.max_pull_records');
        $capped = [];
        $out = [];

        $kinds = [
            'expenses' => [SalesExpense::class, fn (SalesExpense $e) => $this->expensePayload($e)],
            'clients' => [SalesClient::class, fn (SalesClient $c) => $this->clientPayload($c)],
            'meetings' => [SalesMeeting::class, fn (SalesMeeting $m) => $this->meetingPayload($m)],
        ];

        foreach ($kinds as $kind => [$class, $shape]) {
            $rows = $class::withTrashed()
                ->where('user_id', $user->id)
                ->when($since, fn ($q) => $q->where('updated_at', '>=', $since))
                ->orderBy('updated_at')->orderBy('id')
                ->limit($limit + 1)
                ->get();

            if ($rows->count() > $limit) {
                $rows = $rows->take($limit);
                $capped[] = $rows->last()->updated_at;
            }

            $out[$kind] = $rows->map($shape)->all();
        }

        // Not everything fit: the next pull must start no later than the last row of a capped kind (>= makes the overlap harmless).
        $cursor = $capped ? min($capped) : $startedAt;

        return $out + [
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
            'meeting_at' => $this->iso($m->meeting_at),
            'purpose' => $m->purpose ?? '',
            'discussion' => $m->discussion ?? '',
            'outcome' => $m->outcome ?? '',
            'next_followup_at' => $m->next_followup_at ? $this->iso($m->next_followup_at) : null,
            'updated_at' => $this->iso($m->client_updated_at),
            'deleted' => $m->trashed(),
        ];
    }

    // ---- small helpers ----------------------------------------------------------

    /** Answers that need no write: somebody else's id, a deleted record, or nothing newer than what is stored. */
    private function earlyAnswer(mixed $model, User $user, string $updatedAt): ?array
    {
        if (! $model) {
            return null;
        }
        if ((int) $model->user_id !== (int) $user->id) {
            return $this->rejected('UUID_TAKEN', 'This id cannot be used.');
        }
        if ($model->trashed()) {
            return ['status' => 'duplicate'];
        }

        return $model->client_updated_at && $model->client_updated_at->gte($this->time($updatedAt)) ? ['status' => 'duplicate'] : null;
    }

    /** @return ?array a rejected answer when the record breaks a rule */
    private function check(array $record, array $rules): ?array
    {
        $validator = Validator::make($record, $rules);

        if (! $validator->fails()) {
            return null;
        }

        return $this->rejected('INVALID', (string) $validator->errors()->first(), $validator->errors()->toArray());
    }

    private function rejected(string $code, string $message, array $errors = []): array
    {
        return ['status' => 'rejected', 'code' => $code, 'message' => $message] + ($errors ? ['errors' => $errors] : []);
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

    /** The latest moment a meeting may have started: the end of that same last day. */
    private function latestMoment(): CarbonImmutable
    {
        return CarbonImmutable::now(config('sales.timezone'))->addDays((int) config('sales.max_future_days'))->endOfDay()->utc();
    }
}
