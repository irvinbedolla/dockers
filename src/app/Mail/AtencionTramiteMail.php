<?php

namespace App\Mail;

use App\Support\AtencionTramite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Invitación a contestar el formulario de atención del trámite de
 * conciliación, con el folio y la liga. Plantilla: emails.atencion_tramite.
 */
class AtencionTramiteMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array{parte: string, correo: string, nombre: ?string} $destinatario */
    public function __construct(public object $general, public array $destinatario)
    {
    }

    public function build()
    {
        $r = AtencionTramite::resumen($this->general);

        return $this->from(config('mail.from.address'), 'SíConcilio | CCL Michoacán')
            ->subject('Tu opinión es importante · Folio '.$r['folio'])
            ->view('emails.atencion_tramite', [
                'd' => $r + [
                    'nombre' => $this->destinatario['nombre'],
                    'parte'  => AtencionTramite::PARTES[$this->destinatario['parte']] ?? null,
                    'liga'   => AtencionTramite::liga($r['folio'], $this->destinatario['parte']),
                    'ligaSola' => route('atencion.formulario'),
                ],
            ]);
    }
}
