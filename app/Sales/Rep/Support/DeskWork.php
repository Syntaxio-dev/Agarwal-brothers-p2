<?php

namespace App\Sales\Rep\Support;

use App\Models\User;
use App\Sales\Models\SalesMemo;
use App\Sales\Models\SalesTask;
use App\Sales\Models\SalesTodo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The rep's tasks (from the manager), to-do list and notes, from the web desk. The same records as on the phone, through DeskSync:
 * a task only ever gets the rep's status and note (the manager's fields never come from a rep), to-dos and notes are the rep's own.
 */
final class DeskWork
{
    public function __construct(private readonly DeskSync $sync) {}

    // ---- tasks ---------------------------------------------------------------------------------------------------------------------

    public function updateTask(User $rep, SalesTask $task, string $status, ?string $note): void
    {
        if ((int) $task->assigned_to !== (int) $rep->id) {
            abort(404);
        }
        if (! in_array($status, SalesTask::REP_STATUSES, true)) {
            throw ValidationException::withMessages(['desk' => 'Pick Started, Done or Cannot do.']);
        }

        $note = DeskSync::text($note, 500);
        if ($status === 'blocked' && $note === '') {
            throw ValidationException::withMessages(['desk' => 'Tell the manager why you cannot do it.']);
        }

        $this->sync->one($rep, 'tasks', 'tasks', [
            'uuid' => $task->uuid,
            'status' => $status,
            'rep_note' => $note,
            'rep_updated_at' => DeskSync::stamp($task->rep_updated_at),
        ]);
    }

    // ---- to-do -----------------------------------------------------------------------------------------------------------------------

    public function saveTodo(User $rep, ?SalesTodo $todo, string $title, ?string $dueDate): SalesTodo
    {
        $this->mine($rep, $todo);
        $uuid = $todo?->uuid ?? (string) Str::uuid();

        $this->sync->one($rep, 'notes', 'todos', [
            'uuid' => $uuid,
            'title' => DeskSync::text($title, 200),
            'done' => (bool) $todo?->done,
            'done_at' => DeskSync::iso($todo?->done_at),
            'due_date' => filled($dueDate) ? CarbonImmutable::parse($dueDate)->toDateString() : null,
            'updated_at' => DeskSync::stamp($todo?->client_updated_at),
        ]);

        return SalesTodo::where('uuid', $uuid)->firstOrFail();
    }

    public function setTodoDone(User $rep, SalesTodo $todo, bool $done): void
    {
        $this->mine($rep, $todo);

        $this->sync->one($rep, 'notes', 'todos', [
            'uuid' => $todo->uuid,
            'title' => $todo->title,
            'done' => $done,
            'done_at' => $done ? DeskSync::stamp() : null,
            'due_date' => $todo->due_date?->format('Y-m-d'),
            'updated_at' => DeskSync::stamp($todo->client_updated_at),
        ]);
    }

    public function deleteTodo(User $rep, SalesTodo $todo): void
    {
        $this->mine($rep, $todo);
        $this->sync->delete($rep, 'notes', 'todos', $todo->uuid);
    }

    // ---- notes -----------------------------------------------------------------------------------------------------------------------

    public function saveMemo(User $rep, ?SalesMemo $memo, ?string $title, string $body): SalesMemo
    {
        $this->mine($rep, $memo);
        $uuid = $memo?->uuid ?? (string) Str::uuid();

        $this->sync->one($rep, 'notes', 'memos', [
            'uuid' => $uuid,
            'title' => DeskSync::text($title, 120),
            // a note keeps its line breaks; only other control characters go
            'body' => mb_substr(trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $body) ?? ''), 0, 5000),
            'updated_at' => DeskSync::stamp($memo?->client_updated_at),
        ]);

        return SalesMemo::where('uuid', $uuid)->firstOrFail();
    }

    public function deleteMemo(User $rep, SalesMemo $memo): void
    {
        $this->mine($rep, $memo);
        $this->sync->delete($rep, 'notes', 'memos', $memo->uuid);
    }

    /** A to-do or a note of another rep does not exist for this one. */
    private function mine(User $rep, SalesTodo|SalesMemo|null $record): void
    {
        if ($record && (int) $record->user_id !== (int) $rep->id) {
            abort(404);
        }
    }
}
