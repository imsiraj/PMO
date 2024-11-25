<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SmartMail extends Mailable
{
    use Queueable, SerializesModels;

    public $replyName;
    public $fromEmail;
    public $emailData;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emailData)
    {
        $this->replyName = env('MAIL_FROM_NAME');
        $this->fromEmail = env('MAIL_FROM_ADDRESS');
        $this->emailData = $emailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->replyTo($this->fromEmail, $this->replyName)->subject($this->emailData['subject'])->html($this->emailData['body']);
        
    }
}