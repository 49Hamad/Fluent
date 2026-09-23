<?php

namespace App\Mail;

use App\Models\StudentLoginCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** The 6-digit sign-in code for "مساحتي في Fluent". */
class StudentLoginCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $minutes = StudentLoginCode::TTL_MINUTES;

    public function __construct(public string $name, public string $code)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'رمز الدخول إلى مساحتك في Fluent: ' . $this->code);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.student-login-code');
    }
}
