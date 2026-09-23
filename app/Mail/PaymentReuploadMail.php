<?php

namespace App\Mail;

use App\Models\StudentApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * "Please upload a new transfer receipt" — sent only when the employee keeps
 * «إبلاغ الطالب بالبريد» on in «طلب إعادة رفع الإيصال».
 */
class PaymentReuploadMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public StudentApplication $application, public string $reason)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'نحتاج إيصال تحويل جديد — ' . $this->application->reference);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-reupload');
    }
}
