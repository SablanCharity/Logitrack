<?php

namespace App\Mail;

use App\Models\CapstoneProject;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CapstoneProjectReturned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CapstoneProject $capstone) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Capstone returned for correction: '.$this->capstone->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.capstone-project-returned',
            with: ['capstone' => $this->capstone],
        );
    }
}
