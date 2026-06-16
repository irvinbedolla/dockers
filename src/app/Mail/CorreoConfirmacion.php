<?php

// app/Mail/CorreoAcuseConfirmacion.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CorreoAcuseConfirmacion extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfContent;
    public $datosMensaje; // Opcional: Para pasar datos dinámicos al cuerpo del mensaje

    /**
     * Crear una nueva instancia del mensaje.
     */
    public function __construct($pdfContent, $datosMensaje)
    {
        $this->pdfContent = $pdfContent;
        $this->datosMensaje = $datosMensaje;
    }

    /**
     * Construir el mensaje (Cuerpo + PDF Adjunto).
     * @return $this
     */
    public function build()
    {
        return $this->subject('Constancia de Asistencia al Tercer Encuentro de la Conciliación y la Justicia Laboral')
                    
                    // 1. Define el cuerpo del correo (el mensaje)
                    // Pasa los datos dinámicos a la vista del email
                    ->view('emails.constancia') 
                    ->with([
                        'solicitante' => $this->datosMensaje['nombre_solicitante'],
                        'fecha' => $this->datosMensaje['fecha_envio'],
                    ])
                    
                    // 2. Adjunta el PDF generado en memoria
                    ->attachData($this->pdfContent, 'Constancia General.pdf', [
                        'mime' => 'application/pdf', 
                    ]);
    }
}