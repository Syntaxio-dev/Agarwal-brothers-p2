<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** A plain-text message (automatic acknowledgement or a reply written in the admin panel) in the company's e-mail layout. */
class CustomerMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(
        public string $mailSubject,
        public string $body,
        public ?string $replyToEmail = null,
        public ?string $replyToName = null,
    ) {
    }

    public function build()
    {
        $mail = $this->subject($this->mailSubject)
            ->view('emails.customer-message')
            ->text('emails.customer-message-text');

        if ($this->replyToEmail) {
            $mail->replyTo($this->replyToEmail, $this->replyToName);
        }

        return $mail;
    }
}
