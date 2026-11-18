<?php

namespace App\Http\Controllers;

use App\Mail\RatificacionMultipleMail;
use App\Models\RatificacionLote;
use App\Models\Recepcion;
use App\Support\AgendaRatificacionMultiple;
use App\Support\CitaPatronal;
use App\Support\FolioCita;
use App\Support\RatificacionMultiple;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Solicitud patronal de ratificación múltiple (formato institucional), en
 * línea y sin sesión:
 *
 *   formulario()  datos de la solicitud, el folio de representación (obligatorio),
 *                 el Excel y la declaración bajo protesta.
 *   revisar()     lee el Excel (App\Support\RatificacionMultiple), propone la
 *                 agenda (App\Support\AgendaRatificacionMultiple) y lo enseña.
 *   confirmar()   vuelve a revisar y agenda: folio maestro, una cita por
 *                 trabajador y el acuse por correo.
 *   exito() / acuse()  la confirmación y el acuse en PDF (liga firmada).
 *
 * Entre revisar y confirmar, la solicitud y el archivo quedan en la sesión.
 */
class RatificacionMultipleController extends Controller
{
    private const SESION = 'ratificacion_multiple';

    private const CARPETA = 'ratificaciones_multiples';

    /** El formato vacío que se descarga desde la página. */
    public const FORMATO = 'assets/formatos/Formato_ratificaciones_multiples.xlsx';

    public function formulario()
    {
        if (! config('app.citas_en_linea')) {
            return redirect()->route('citas.fuera_servicio');
        }

        return view('ratificacion_multiple.formulario', [
            'sedes'   => array_keys(FolioCita::SEDES),
            'motivos' => RatificacionLote::MOTIVOS,
            'minimo'  => RatificacionMultiple::MINIMO_TRABAJADORES,
        ]);
    }

    public function formato()
    {
        return response()->download(public_path(self::FORMATO), 'Formato para ratificaciones múltiples.xlsx');
    }

    public function revisar(Request $request)
    {
        if (! config('app.citas_en_linea')) {
            return redirect()->route('citas.fuera_servicio');
        }

        $datos = $request->validate([
            'poder_folio'             => ['required', 'digits_between:1,10'],
            'delegacion'              => ['required', Rule::in(array_keys(FolioCita::SEDES))],
            'motivo'                  => ['required', Rule::in(array_keys(RatificacionLote::MOTIVOS))],
            'fecha_propuesta'         => ['required', 'date_format:Y-m-d', 'after:today', 'before:'.now()->addDays(60)->toDateString()],
            'trabajadores_declarados' => ['required', 'integer', 'min:'.RatificacionMultiple::MINIMO_TRABAJADORES, 'max:'.RatificacionMultiple::MAXIMO_TRABAJADORES],
            'solicitante'             => ['required', 'string', 'max:150'],
            'correo'                  => ['required', 'email', 'max:150', 'confirmed'],
            'telefono'                => ['required', 'regex:/^\d{10}$/'],
            'observaciones'           => ['nullable', 'string', 'max:1000'],
            'archivo'                 => ['required', 'file', 'mimes:xlsx', 'max:10240'],
            'constancia'              => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'declaracion'             => ['accepted'],
            'acepta_privacidad'       => ['accepted'],
        ], [
            'poder_folio.required'      => 'Escribe tu folio de representación patronal. Si no lo tienes, primero registra tu poder.',
            'fecha_propuesta.after'     => 'La fecha propuesta debe ser a partir de mañana.',
            'fecha_propuesta.before'    => 'La fecha propuesta no puede ser a más de 60 días.',
            'trabajadores_declarados.min' => 'La ratificación múltiple es para '.RatificacionMultiple::MINIMO_TRABAJADORES.' trabajadores o más.',
            'correo.confirmed'          => 'Los dos correos no coinciden.',
            'telefono.regex'            => 'El teléfono debe tener 10 dígitos.',
            'archivo.required'          => 'Adjunta el formato de ratificaciones múltiples en Excel.',
            'archivo.mimes'             => 'El formato debe ser un archivo de Excel (.xlsx).',
            'constancia.mimes'          => 'La constancia de situación fiscal debe ser PDF.',
            'declaracion.accepted'      => 'Debes aceptar la declaración bajo protesta de decir verdad.',
            'acepta_privacidad.accepted' => 'Debes aceptar el aviso de privacidad.',
        ]);

        [$poder, $error] = CitaPatronal::poderElegible($datos['poder_folio']);
        if ($error) {
            return back()->withInput()->withErrors(['poder_folio' => $error]);
        }

        // El archivo se guarda aparte (no público) mientras se revisa.
        $token = (string) Str::uuid();
        $excel = $request->file('archivo')->storeAs(self::CARPETA.'/pendientes', $token.'.xlsx', 'local');
        $constancia = $request->hasFile('constancia')
            ? $request->file('constancia')->storeAs(self::CARPETA.'/pendientes', $token.'-constancia.pdf', 'local')
            : null;

        $leido = RatificacionMultiple::leer(Storage::disk('local')->path($excel), (string) $poder->idAbogado);
        $n = count($leido['trabajadores']);
        if ($n && $n !== (int) $datos['trabajadores_declarados']) {
            $leido['errores'][] = 'Declaraste '.$datos['trabajadores_declarados'].' trabajadores, pero el Excel trae '.$n.'. Corrige uno de los dos.';
        }
        foreach (AgendaRatificacionMultiple::yaRegistrados($poder, $leido['trabajadores']) as $i => $aviso) {
            $leido['trabajadores'][$i]['avisos'][] = $aviso;
        }

        $valido = RatificacionMultiple::valido($leido);
        $agenda = $valido ? AgendaRatificacionMultiple::proponer($datos['delegacion'], $datos['fecha_propuesta'], $n) : null;
        $alcanza = $agenda && count($agenda['citas']) >= $n;

        unset($datos['archivo'], $datos['constancia'], $datos['correo_confirmation']);
        if ($valido && $alcanza) {
            session([self::SESION => [
                'token'      => $token,
                'datos'      => $datos,
                'excel'      => $excel,
                'nombre'     => $request->file('archivo')->getClientOriginalName(),
                'constancia' => $constancia,
                'en'         => now()->timestamp,
            ]]);
        } else {
            Storage::disk('local')->delete(array_filter([$excel, $constancia]));
        }

        return view('ratificacion_multiple.revisar', [
            'datos'   => $datos,
            'poder'   => $poder,
            'leido'   => $leido,
            'valido'  => $valido,
            'agenda'  => $agenda,
            'alcanza' => $alcanza,
            'token'   => $token,
            'motivos' => RatificacionLote::MOTIVOS,
        ]);
    }

    public function confirmar(Request $request)
    {
        $pendiente = session(self::SESION);
        if (! $pendiente || $request->input('token') !== $pendiente['token'] || now()->timestamp - $pendiente['en'] > 3600) {
            return redirect()->route('ratificacion_multiple')->with('error', 'La revisión ya venció. Vuelve a subir tu formato.');
        }

        $datos = $pendiente['datos'];
        [$poder, $error] = CitaPatronal::poderElegible($datos['poder_folio']);
        if ($error) {
            return redirect()->route('ratificacion_multiple')->with('error', $error);
        }
        $leido = RatificacionMultiple::leer(Storage::disk('local')->path($pendiente['excel']), (string) $poder->idAbogado);
        if (! RatificacionMultiple::valido($leido)) {
            return redirect()->route('ratificacion_multiple')->with('error', 'El formato ya no pasa la revisión. Vuelve a subirlo.');
        }

        // Los archivos pasan a la carpeta del lote.
        $destino = self::CARPETA.'/'.now()->format('Y').'/'.$pendiente['token'];
        Storage::disk('local')->move($pendiente['excel'], $destino.'/relacion_trabajadores.xlsx');
        $constancia = null;
        if ($pendiente['constancia']) {
            Storage::disk('local')->move($pendiente['constancia'], $constancia = $destino.'/constancia_situacion_fiscal.pdf');
        }

        // Si no se agenda, el archivo regresa a pendientes (por si se elige otra fecha).
        $regresar = function () use ($destino, $constancia, $pendiente) {
            Storage::disk('local')->move($destino.'/relacion_trabajadores.xlsx', $pendiente['excel']);
            if ($constancia) {
                Storage::disk('local')->move($constancia, $pendiente['constancia']);
            }
        };

        try {
            $lote = AgendaRatificacionMultiple::crear($datos, $leido, $poder, [
                'excel'        => $destino.'/relacion_trabajadores.xlsx',
                'excel_nombre' => $pendiente['nombre'],
                'constancia'   => $constancia,
            ], $request->ip(), $request->userAgent());
        } catch (\Throwable $e) {
            $regresar();
            throw $e;
        }

        if (is_string($lote)) {
            $regresar();

            return redirect()->route('ratificacion_multiple')->withInput($datos)->with('error', $lote);
        }

        session()->forget(self::SESION);

        try {
            Mail::to($lote->correo)->send(new RatificacionMultipleMail($lote, $this->pdf($lote)));
        } catch (\Throwable $e) {
            Log::error('No se envió el acuse de ratificación múltiple', ['lote' => $lote->folio, 'error' => $e->getMessage()]);
        }

        return redirect()->route('ratificacion_multiple.exito')->with('lote', $lote->id);
    }

    public function exito()
    {
        $lote = session('lote') ? RatificacionLote::with('renglones')->find(session('lote')) : null;
        if (! $lote) {
            return redirect()->route('ratificacion_multiple');
        }

        return view('ratificacion_multiple.exito', [
            'lote'  => $lote,
            'citas' => $this->citas($lote),
            'acuse' => URL::temporarySignedRoute('ratificacion_multiple.acuse', now()->addDays(30), ['lote' => $lote->id]),
        ]);
    }

    /** Acuse en PDF (liga firmada que va en la página de éxito y en el correo). */
    public function acuse(RatificacionLote $lote)
    {
        return $this->pdf($lote)->stream('Acuse '.$lote->folio.'.pdf');
    }

    public function pdf(RatificacionLote $lote)
    {
        return Pdf::loadView('PDF.ratificacion_multiple_acuse', [
            'lote'      => $lote->loadMissing('renglones'),
            'citas'     => $this->citas($lote),
            'motivos'   => RatificacionLote::MOTIVOS,
            'direccion' => \App\Support\HorarioCitas::DIRECCIONES_SEDES[$lote->delegacion] ?? '',
        ])->setPaper('letter');
    }

    /** Las citas del lote, en el orden de los trabajadores. */
    private function citas(RatificacionLote $lote)
    {
        $porId = Recepcion::whereIn('id', $lote->renglones->pluck('recepcion_id')->filter())->get()->keyBy('id');

        return $lote->renglones->map(fn ($r) => ['trabajador' => $r, 'cita' => $porId->get($r->recepcion_id)])
            ->sortBy(fn ($x) => $x['cita'] ? $x['cita']->fecha->format('Y-m-d').' '.$x['cita']->hora->format('H:i').' '.str_pad($x['trabajador']->numero, 3, '0', STR_PAD_LEFT) : 'z')
            ->values();
    }
}
