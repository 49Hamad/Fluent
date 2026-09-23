<?php

namespace App\Mail;

use App\Models\StudentApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Optional e-mail to a student about their application status.
 * Only sent when an employee ticks "إبلاغ الطالب بالبريد" in Filament.
 */
class StudentApplicationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public StudentApplication $application, public ?string $extraMessage = null)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تحديث على طلبك في Fluent — ' . $this->application->reference,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.student-application-status');
    }
}
