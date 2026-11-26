<?php

namespace App\Sales\Rep\Support;

use App\Models\User;
use App\Sales\Services\SyncService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The web desk writes like one more phone of the rep: every change becomes a record in the phone's own wire format and goes through
 * SyncService::push. So the web obeys exactly the same rules as the app (one number once, one company once per territory, only the adder
 * changes a prospect, the meeting windows, the task rules ...), there is no second copy of those rules to drift apart, and the phone takes
 * the change on its next pull like it takes a change from another phone. The stamp is strictly newer than what is stored, so the phone's
 * older copy can never win over it.
 *
 * Before anything is written the area must be open for this rep (RepDesk). A refused record becomes a form error with the office's message.
 */
final class DeskSync
{
    public function __construct(private readonly SyncService $sync) {}

    /**
     * Push records of several kinds in ONE go (all or nothing). $records = ['meetings' => [...], 'routes' => [...], 'deleted' => ['meetings' => [uuid]]].
     * @return array the push results
     */
    public function push(User $rep, string $area, array $records): array
    {
        RepDesk::mustAllow($rep, $area);

        return DB::transaction(function () use ($rep, $records) {
            $answer = $this->sync->push($rep, $records);

            foreach (SyncService::KINDS as $kind) {
                foreach ($answer['results'][$kind] ?? [] as $one) {
                    $this->refuseIfRejected($one);
                }
            }
            foreach ($answer['results']['deleted'] ?? [] as $list) {
                foreach ($list as $one) {
                    $this->refuseIfRejected($one);
                }
            }

            return $answer;
        });
    }

    /** One record of one kind. */
    public function one(User $rep, string $area, string $kind, array $record): void
    {
        $this->push($rep, $area, [$kind => [$record]]);
    }

    public function delete(User $rep, string $area, string $kind, string $uuid): void
    {
        $this->push($rep, $area, ['deleted' => [$kind => [$uuid]]]);
    }

    /** A time strictly after the stored version (and after now), in the wire format with milliseconds. */
    public static function stamp(?CarbonInterface $stored = null): string
    {
        $now = CarbonImmutable::now('UTC');
        if ($stored && ! $now->gt($stored)) {
            $now = CarbonImmutable::instance($stored)->utc()->addMillisecond();
        }

        return self::iso($now);
    }

    public static function iso(?CarbonInterface $time): ?string
    {
        return $time ? CarbonImmutable::instance($time)->utc()->format('Y-m-d\TH:i:s.v\Z') : null;
    }

    /** Text as the phone keeps it: no control characters, trimmed. */
    public static function text(mixed $value, int $max): string
    {
        return mb_substr(trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $value) ?? ''), 0, $max);
    }

    private function refuseIfRejected(array $one): void
    {
        if (($one['status'] ?? null) !== 'rejected') {
            return;
        }

        $message = match ($one['code'] ?? null) {
            'NOT_ALLOWED' => (string) ($one['message'] ?? 'Only the person who added it, or a manager, can change this.'),
            'UUID_TAKEN', 'SERVER_ERROR' => 'This could not be saved. Please try again.',
            default => (string) ($one['message'] ?? 'This could not be saved.'),
        };

        throw ValidationException::withMessages(['desk' => $message]);
    }
}
