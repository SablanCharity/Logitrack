<?php

namespace App\Mail;

use App\Models\StudentCapstoneSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentCapstoneSubmissionStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public StudentCapstoneSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Capstone submission status: '.ucfirst($this->submission->status),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-capstone-submission-status-updated',
            with: ['submission' => $this->submission],
        );
    }
}
