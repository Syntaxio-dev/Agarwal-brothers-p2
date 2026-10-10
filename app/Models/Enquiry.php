<?php

namespace App\Models;

use App\Mail\EnquiryAssigned;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\HasTeamNotes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Enquiry extends Model
{
    use LogsActivity;
    use HasFactory;
    use HasTeamNotes;

    /** Workflow, in the order an enquiry normally moves through. */
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'quoted' => 'Quote sent',
        'closed' => 'Closed',
    ];

    /** Statuses that still need someone's attention. */
    public const OPEN = ['new', 'contacted', 'quoted'];

    protected $fillable = ['product_id', 'name', 'email', 'phone', 'company', 'budget', 'order_location', 'message', 'status', 'assigned_to'];

    protected static function booted(): void
    {
        // Keep a readable log for the team: who changed the status or the owner, and tell a new owner.
        static::updated(function (Enquiry $enquiry) {
            if ($enquiry->wasChanged('status')) {
                $from = static::STATUSES[$enquiry->getOriginal('status')] ?? ucfirst((string) $enquiry->getOriginal('status'));
                $to = static::STATUSES[$enquiry->status] ?? ucfirst((string) $enquiry->status);
                $enquiry->logActivity("Status changed from {$from} to {$to}");
            }

            if ($enquiry->wasChanged('assigned_to')) {
                $enquiry->unsetRelation('assignee');   // the owner may have changed since it was first loaded
                $assignee = $enquiry->assignee;
                $enquiry->logActivity($assignee ? "Assigned to {$assignee->name}" : 'Assignment removed');

                // E-mail the new owner, unless they assigned it to themselves.
                if ($assignee && $assignee->email && $assignee->id !== auth()->id() && $assignee->isStaff()) {
                    try {
                        Mail::to($assignee->email)->queue(new EnquiryAssigned($enquiry, auth()->user()?->name));
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(EnquiryItem::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** True when this came from the multi-product enquiry list. */
    public function isGroup(): bool
    {
        return $this->items()->exists();
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', static::OPEN);
    }

    /** "ENQ-000123": the number customers and staff can quote. */
    public function reference(): string
    {
        return 'ENQ-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /** Day-to-day work on these is written to their own team notes; only removal is added to the activity log. */
    public function activityEvents(): array
    {
        return ['deleted'];
    }
}
