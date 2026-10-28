<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Several meetings of one day as one trip (for example "Bhiwadi run"): the stops in the order the rep will go, and where the day starts.
 * A rep's own; the manager reads every rep's. It only ever lists meetings of the same rep.
 */
class SalesRoute extends Model
{
    use HasPhoneTime, SoftDeletes;

    protected $table = 'sales_routes';

    protected $fillable = [
        'uuid', 'user_id', 'route_date', 'title', 'area', 'start_time', 'overnight', 'stops_order', 'client_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'route_date' => 'date',
            'overnight' => 'boolean',
            'stops_order' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The meetings of this route in the route's order. Only this rep's own meetings that point back at the route count: a stop
     * that is missing from the order comes last (by time), the way the phone shows it.
     *
     * @return Collection<int, SalesMeeting>
     */
    public function stops(): Collection
    {
        $mine = SalesMeeting::where('user_id', $this->user_id)->where('route_uuid', $this->uuid)->get();
        $order = array_flip($this->stops_order ?? []);

        return $mine->sortBy(fn (SalesMeeting $m) => sprintf('%010d-%012d', $order[$m->uuid] ?? 9999999999, $m->plannedOrActualAt()->getTimestamp()))->values();
    }

    /** "2 of 3 done": the stops that are no longer to happen (done or missed) out of all of them. */
    public function progress(?Collection $stops = null): array
    {
        $stops ??= $this->stops();

        return ['finished' => $stops->filter(fn (SalesMeeting $m) => ! $m->isOpen())->count(), 'total' => $stops->count()];
    }
}
