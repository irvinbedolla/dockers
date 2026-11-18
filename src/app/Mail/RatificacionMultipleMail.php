<?php

namespace App\Mail;

use App\Models\RatificacionLote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * Acuse de una solicitud de ratificación múltiple: folio maestro, cuántos
 * trabajadores y sus citas, con el acuse en PDF adjunto.
 * Plantilla: emails.ratificacion_multiple.
 */
class RatificacionMultipleMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RatificacionLote $lote, public $pdf)
    {
    }

    public function build()
    {
        $lote = $this->lote->loadMissing('renglones');
        $citas = \App\Models\Recepcion::whereIn('id', $lote->renglones->pluck('recepcion_id')->filter())
            ->orderBy('fecha')->orderBy('hora')->get(['fecha', 'hora']);
        $dias = $citas->groupBy(fn ($c) => $c->fecha->format('Y-m-d'))
            ->map(fn ($g) => ['fecha' => $g->first()->fecha, 'desde' => $g->first()->hora->format('H:i'), 'hasta' => $g->last()->hora->format('H:i'), 'n' => $g->count()]);

        return $this->from(config('mail.from.address'), 'SíConcilio | CCL Michoacán')
            ->subject("Solicitud de ratificación múltiple recibida · Folio {$lote->folio}")
            ->view('emails.ratificacion_multiple', [
                'lote'  => $lote,
                'dias'  => $dias,
                'acuse' => URL::temporarySignedRoute('ratificacion_multiple.acuse', now()->addDays(30), ['lote' => $lote->id]),
            ])
            ->attachData($this->pdf->output(), "Acuse {$lote->folio}.pdf", ['mime' => 'application/pdf']);
    }
}
