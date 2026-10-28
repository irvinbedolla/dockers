<?php

namespace App\Mail;

use App\Models\Recepcion;
use App\Support\CitaCorreo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso de que Administración canceló la cita (Cancelación/Reagenda de
 * citas), con la liga para generar una nueva. Sin acuse: ya no sirve.
 * Plantilla: emails.cita_cancelada.
 */
class CitaCanceladaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Recepcion $cita)
    {
    }

    public function build()
    {
        $datos = CitaCorreo::datos($this->cita);

        // Remitente fijo para la ciudadanía, aunque el .env del servidor
        // todavía diga otra cosa en MAIL_FROM_NAME.
        return $this->from(config('mail.from.address'), 'SíConcilio | CCL Michoacán')
            ->subject("Tu cita fue cancelada · Folio {$datos['folio']}")
            ->view('emails.cita_cancelada', ['d' => $datos, 'nuevaCita' => route('citas')]);
    }
}
