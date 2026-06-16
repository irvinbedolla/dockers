<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailRechazo extends Mailable
{
    use Queueable, SerializesModels;

    public $user; // Propiedad para pasar datos a la vista

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Actualizacion de solicitud',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rechazo',
        );
    }
    
}