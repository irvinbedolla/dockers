<?php

namespace App\Mail;

use App\Models\Recepcion;
use App\Support\CitaCorreo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Acuse nuevo cuando Administración mueve una cita a otro día u hora
 * (Cancelación/Reagenda de citas). Es la misma confirmación de siempre,
 * más la fecha y el módulo anteriores. Plantilla: emails.cita_reagendada.
 */
class CitaReagendadaMail extends Mailable
{
    use Queueable, SerializesModels;

    // Fecha y módulo anteriores protegidos: si fueran públicos, Laravel los
    // pasaría tal cual a la vista y pisarían los ya formateados en build().
    public function __construct(public $pdf, public Recepcion $cita, protected string $fechaAnterior, protected ?string $moduloAnterior)
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
            ->subject("Tu cita cambió de fecha · {$datos['tipo']} · Folio {$datos['folio']}")
            ->view('emails.cita_reagendada', [
                'd'              => $datos,
                'fechaAnterior'  => $this->fechaAnterior,
                'moduloAnterior' => $this->moduloAnterior ? str_replace('Modulo', 'Módulo', $this->moduloAnterior) : null,
            ])
            ->attachData($this->pdf->output(), "Acuse de cita {$datos['folio']}.pdf", ['mime' => 'application/pdf']);
    }
}
