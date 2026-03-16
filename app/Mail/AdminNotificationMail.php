<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $recipient,
        public string $adminSubject,
        public string $adminMessage
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->adminSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-notification');
    }
}
