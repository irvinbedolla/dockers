<?php

namespace App\Mail;

use App\Models\Recepcion;
use App\Support\CitaCorreo;
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
        $datos = CitaCorreo::datos($this->cita);
        // QR en PNG para mostrarlo dentro del correo (Gmail y Outlook no muestran SVG).
        $datos['qrPng'] = $datos['qrUrl'] ? \App\Support\QrPng::generar($datos['qrUrl']) : null;

        // Remitente fijo para la ciudadanía, aunque el .env del servidor
        // todavía diga otra cosa en MAIL_FROM_NAME.
        return $this->from(config('mail.from.address'), 'SíConcilio | CCL Michoacán')
            ->subject("Tu cita cambió de módulo · Folio {$datos['folio']}")
            ->view('emails.cita_reasignada', [
                'd'              => $datos,
                'moduloAnterior' => $this->moduloAnterior ? str_replace('Modulo', 'Módulo', $this->moduloAnterior) : null,
            ])
            ->attachData($this->pdf->output(), "Acuse de cita {$datos['folio']}.pdf", ['mime' => 'application/pdf']);
    }
}
