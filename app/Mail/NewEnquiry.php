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
        $this->enquiry->loadMissing('items', 'product.category.brand');
        $count = $this->enquiry->items->count();

        $subject = $count
            ? "New group enquiry: {$count} " . ($count === 1 ? 'product' : 'products') . ' from ' . $this->enquiry->name
            : 'New enquiry: ' . ($this->enquiry->product?->name ?? 'General') . ' from ' . $this->enquiry->name;

        return $this->subject($subject)
            ->replyTo($this->enquiry->email, $this->enquiry->name)
            ->view('emails.new-enquiry');
    }
}