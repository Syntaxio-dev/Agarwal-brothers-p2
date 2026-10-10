<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Tells a team member that an enquiry is now theirs. */
class EnquiryAssigned extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public Enquiry $enquiry, public ?string $assignedBy = null)
    {
    }

    public function build()
    {
        return $this->subject('Enquiry assigned to you: ' . $this->enquiry->name . ' (' . $this->enquiry->reference() . ')')
            ->view('emails.enquiry-assigned');
    }
}
