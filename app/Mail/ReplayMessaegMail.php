<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReplayMessaegMail extends Mailable
{
    use Queueable, SerializesModels;

    private $title;
    private $body;
    private $image;

    /**
     * Create a new message instance.
     */
    public function __construct($title,$body,$image)
    {
        $this->title = $title;
        $this->body = $body;
        $this->image = $image;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $pathIMage = 'https://fluent.sa/public/storage/' . $this->image;

        if($this->image != null)
        {
            return $this->subject($this->title)
            ->attach($pathIMage)
                ->view('emails.ReplayMessaegMail')
                ->with([
                    "body" => $this->body,
                ]);
        }
        else
        {
            return $this->subject($this->title)
            ->view('emails.ReplayMessaegMail')
                ->with([
                    "body" => $this->body,
                ]);
        }


    }

}
