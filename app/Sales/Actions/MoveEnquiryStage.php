<?php

namespace App\Sales\Actions;

use App\Models\User;
use App\Sales\Models\SalesEnquiry;
use App\Support\ActivityLogger;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * The office moves an enquiry to another stage (the manager drags a card). The rep owns the enquiry, so this changes the least it can:
 * `status`, `closed_at` / `next_followup_at` when it closes, ONE timeline entry, and `client_updated_at` (so every phone sees the office's
 * change as newer on its next pull). Nothing else. The row is locked while it is checked and written, and the write is refused when the
 * enquiry changed since the board loaded it (the rep synced in between): the newer version wins, nothing is half written.
 */
class MoveEnquiryStage
{
    /** The phone keeps the last 100 timeline entries and 500 characters per text: the office keeps to the same limits. */
    public const MAX_TIMELINE = 100;

    public const MAX_TEXT = 500;

    /**
     * @param  string|null  $expectedFrom  the stage the card was dragged from (defaults to the stage of `$enquiry`)
     * @return array{result: string, undo: ?array}  result: moved | same | closed | conflict | gone | invalid
     */
    public function handle(SalesEnquiry $enquiry, string $to, User $by, ?string $note, CarbonInterface $expectedVersion, ?string $expectedFrom = null): array
    {
        if (! array_key_exists($to, SalesEnquiry::STATUSES)) {
            return ['result' => 'invalid', 'undo' => null];
        }

        $expectedFrom ??= $enquiry->status;

        return DB::transaction(function () use ($enquiry, $to, $by, $note, $expectedVersion, $expectedFrom) {
            /** @var SalesEnquiry|null $row */
            $row = SalesEnquiry::whereKey($enquiry->getKey())->lockForUpdate()->first();

            if (! $row) {
                return ['result' => 'gone', 'undo' => null];
            }
            if (in_array($row->status, SalesEnquiry::CLOSED, true)) {
                return ['result' => 'closed', 'undo' => null];   // reopening is the rep's decision, on the phone
            }
            if ($row->status === $to) {
                return ['result' => 'same', 'undo' => null];
            }
            if ($row->status !== $expectedFrom || ($row->client_updated_at && $row->client_updated_at->gt(CarbonImmutable::instance($expectedVersion)))) {
                return ['result' => 'conflict', 'undo' => null];
            }

            $now = CarbonImmutable::now('UTC');
            // strictly newer than what is stored, even inside the same millisecond
            if ($row->client_updated_at && ! $now->gt($row->client_updated_at)) {
                $now = $row->client_updated_at->addMillisecond();
            }

            $old = ['status' => $row->status, 'closed_at' => $row->closed_at, 'next_followup_at' => $row->next_followup_at];
            $closing = in_array($to, SalesEnquiry::CLOSED, true);

            $text = 'Moved to ' . SalesEnquiry::STATUSES[$to] . ' by ' . $by->name . ' (office)';
            $note = trim((string) $note);
            if ($note !== '') {
                $text .= ': ' . $note;
            }
            $entry = ['at' => $now->format('Y-m-d\TH:i:s\Z'), 'text' => mb_substr($text, 0, self::MAX_TEXT), 'status' => $to];

            $timeline = array_values($row->timeline ?? []);
            $dropped = null;
            if (count($timeline) >= self::MAX_TIMELINE) {
                $dropped = array_shift($timeline);   // the oldest goes, as on the phone
            }
            $timeline[] = $entry;

            $row->status = $to;
            $row->closed_at = $closing ? $now : null;
            $row->next_followup_at = $closing ? null : $row->next_followup_at;
            $row->timeline = $timeline;
            $row->client_updated_at = $now;
            $row->save();

            ActivityLogger::record('updated', $row, ['status' => ['old' => $old['status'], 'new' => $to]], $by);

            return ['result' => 'moved', 'undo' => [
                'uuid' => $row->uuid,
                'from' => $old['status'],
                'to' => $to,
                'closed_at' => $old['closed_at']?->toIso8601String(),
                'next_followup_at' => $old['next_followup_at']?->toIso8601String(),
                'entry' => $entry,
                'dropped' => $dropped,
                'version' => $now->format('Y-m-d H:i:s.v'),
            ]];
        });
    }

    /**
     * Take the move back: only while nobody (the phone included) changed the enquiry since the move wrote its version. Restores the stage, the
     * close time and the follow-up, removes exactly the entry the move added (and puts back the one it pushed out), and writes a new version.
     *
     * @param  array  $undo  the `undo` part of a `moved` answer
     * @return string  undone | changed | gone
     */
    public function undo(array $undo, User $by): string
    {
        return DB::transaction(function () use ($undo, $by) {
            $row = SalesEnquiry::where('uuid', (string) ($undo['uuid'] ?? ''))->lockForUpdate()->first();

            if (! $row) {
                return 'gone';
            }

            $timeline = array_values($row->timeline ?? []);
            $last = end($timeline) ?: null;

            if (! $row->client_updated_at || $row->client_updated_at->format('Y-m-d H:i:s.v') !== ($undo['version'] ?? null) || $last !== ($undo['entry'] ?? null)) {
                return 'changed';
            }

            array_pop($timeline);
            if (! empty($undo['dropped'])) {
                array_unshift($timeline, $undo['dropped']);
            }

            $now = CarbonImmutable::now('UTC');
            if (! $now->gt($row->client_updated_at)) {
                $now = $row->client_updated_at->addMillisecond();
            }

            $row->status = $undo['from'];
            $row->closed_at = filled($undo['closed_at'] ?? null) ? CarbonImmutable::parse($undo['closed_at']) : null;
            $row->next_followup_at = filled($undo['next_followup_at'] ?? null) ? CarbonImmutable::parse($undo['next_followup_at']) : null;
            $row->timeline = $timeline ?: null;
            $row->client_updated_at = $now;
            $row->save();

            ActivityLogger::record('updated', $row, ['status' => ['old' => $undo['to'], 'new' => $undo['from']]], $by);

            return 'undone';
        });
    }
}
