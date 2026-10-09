<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewEnquiry extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public Enquiry $enquiry)
    {
    }

    public function build()
    {
        return $this->subject('New Enquiry: ' . $this->enquiry->product?->name)
            ->view('emails.new-enquiry');
    }
}