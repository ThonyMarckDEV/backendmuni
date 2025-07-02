<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $template;
    public $data;
    public $subject;
    public $fromEmail;
    public $fromName;

    public function __construct(string $template, array $data, string $subject, ?string $fromEmail = null, ?string $fromName = null)
    {
        $this->template = $template;
        $this->data = $data;
        $this->subject = $subject;
        $this->fromEmail = $fromEmail ?? config('mail.from.address');
        $this->fromName = $fromName ?? config('mail.from.name');
    }

    public function build()
    {
        return $this->from($this->fromEmail, $this->fromName)
                    ->subject($this->subject)
                    ->view($this->template)
                    ->with($this->data);
    }
}