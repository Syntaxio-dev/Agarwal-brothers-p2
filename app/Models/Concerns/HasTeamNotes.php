<?php

namespace App\Models\Concerns;

use App\Models\Note;

/** Internal notes + activity log. (Named teamNotes because job applications already have a "notes" column.) */
trait HasTeamNotes
{
    public function teamNotes()
    {
        return $this->morphMany(Note::class, 'noteable')->latest('id');
    }

    /** Add an automatic log line, e.g. "Status changed from New to Contacted". */
    public function logActivity(string $text, ?int $userId = null): Note
    {
        return $this->teamNotes()->create([
            'user_id' => $userId ?? auth()->id(),
            'kind' => Note::KIND_SYSTEM,
            'body' => $text,
        ]);
    }
}
