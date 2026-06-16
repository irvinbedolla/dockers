<?php
// Archivo: app/Mail/WelcomeMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailAceptacion extends Mailable
{
     use Queueable, SerializesModels;
    
    public $pdfContent;
    public $user; // Propiedad para pasar datos a la vista

    public function __construct($pdfContent, $user)
    {
        $this->pdfContent = $pdfContent;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Actualizacion de solicitud')
                    
            // Pasa los datos dinámicos a la vista del email
            ->view('emails.confirmacion') 
            ->with([
                'user' => $this->user,
            ])
                    
            // 2. Adjunta el PDF generado en memoria
            ->attachData($this->pdfContent, 'Documento.pdf', [
                'mime' => 'application/pdf', 
            ]);
    }
}