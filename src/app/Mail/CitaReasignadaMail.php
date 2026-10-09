<?php

namespace App\Mail;

use App\Models\Recepcion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Acuse actualizado cuando el Super Usuario mueve una cita en línea de módulo. */
class CitaReasignadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $pdf, public Recepcion $cita, public ?string $moduloAnterior)
    {
    }

    public function build()
    {
        return $this->subject('Tu cita cambió de módulo')
            ->view('emails.cita_reasignada')
            ->attachData($this->pdf->output(), 'Acuse de cita.pdf', ['mime' => 'application/pdf']);
    }
}
