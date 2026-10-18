{{--
    Formato de atención para casos previstos en el art. 685 Ter LFT y
    situaciones de violencia laboral (CasosExcepcionController::pdf).
    Copia del formato del Departamento de Conciliación, Género e Igualdad,
    con lo que haya en el sistema; lo que falte sale en línea para llenarse a
    mano. Fondo: el membrete del propio formato (formato_685ter_fondo.jpg).

    Espera $datos (FormatoExcepcion::datos) y $atendio (nombre).
--}}
@php
    $CX = \App\Support\CasosExcepcion::class;
    $e  = fn ($v) => e((string) $v);
    // Valor sobre la línea; vacío, la línea sola para escribir a mano.
    $val   = fn ($v) => $v !== null && $v !== '' ? e((string) $v) : '&nbsp;';
    // Texto corrido con línea (dentro de un párrafo).
    $linea = fn ($v, $ancho = '100pt') => '<span class="v" style="min-width:'.$ancho.'">'.$val($v).'</span>';
    // Renglón "Etiqueta: ______" que ocupa el ancho disponible.
    $campo = fn (string $etq, $v) => '<td class="l">'.$etq.'</td><td class="lv">'.$val($v).'</td>';
    $caja  = fn (bool $si) => '<span class="cb">'.($si ? '&#9746;' : '&#9744;').'</span>';
    $fecha = ! empty($datos['fecha']) ? \Illuminate\Support\Carbon::parse($datos['fecha'])->locale('es')->isoFormat('D [de] MMMM [de] YYYY') : null;
    $tipos = (array) ($datos['tipos'] ?? []);
    $inst  = (array) ($datos['instituciones'] ?? []);
    $resultado = $datos['resultado'] ?? null;
    $titulo = fn ($t) => $t ? \Illuminate\Support\Str::title(mb_strtolower($t)) : null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Formato 685 Ter</title>
    <style>
        @page { margin: 76pt 42pt 58pt 42pt; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 8.7pt; color: #111; line-height: 1.3; }
        .fondo { position: fixed; top: -76pt; left: -42pt; width: 595.3pt; height: 841.9pt; z-index: -1000; }
        h1 { font-size: 9.6pt; text-align: center; margin: 0 0 8pt; line-height: 1.35; }
        h2 { font-size: 9.6pt; text-align: center; margin: 12pt 0 8pt; }
        p { margin: 0 0 5pt; text-align: justify; }
        ul { margin: 0 0 4pt 14pt; padding: 0; }
        li { margin: 0; text-align: justify; }
        .v { display: inline-block; border-bottom: 0.6pt solid #333; padding: 0 3pt; }
        table.c { width: 100%; border-collapse: collapse; margin: 0 0 7pt; }
        table.c td { padding: 0; vertical-align: bottom; }
        table.c td.l { font-weight: bold; white-space: nowrap; padding-right: 4pt; width: 1%; }
        table.c td.lv { border-bottom: 0.6pt solid #333; padding: 0 3pt; }
        table.c td.sep { width: 14pt; }
        table.c td.t { white-space: nowrap; width: 1%; padding-right: 4pt; }
        .cb { font-family: "DejaVu Sans", sans-serif; font-size: 9.5pt; }
        .fila { margin: 0 0 9pt; }
        .datos { margin: 0 14pt; }
        .preg { margin: 0 0 5pt; line-height: 1.22; }
        .preg b.n { display: inline-block; width: 14pt; }
        .op { margin: 0 0 0 14pt; }
        .texto { margin: 1pt 0 0 14pt; border-bottom: 0.6pt solid #333; min-height: 10pt; padding: 0 3pt 1pt; white-space: pre-line; }
        .texto + .texto { margin-top: 0; }
        .nota { font-size: 7.9pt; line-height: 1.25; margin-bottom: 4pt; }
        .decision { border: 0.8pt solid #496163; border-radius: 4pt; padding: 4pt 8pt; margin: 4pt 0 4pt; }
        .decision b.t { color: #354647; }
        .firmas { width: 100%; margin-top: 6pt; page-break-inside: avoid; }
        .firmas td { text-align: center; vertical-align: bottom; font-size: 8.4pt; font-weight: bold; padding: 0 10pt; }
        .firma { border-top: 0.7pt solid #111; padding-top: 3pt; }
        .acepto { text-align: center; font-weight: bold; letter-spacing: 4pt; margin: 2pt 0 20pt; }
        .salto { page-break-before: always; }
    </style>
</head>
<body>
    <img class="fondo" src="{{ public_path('assets/images/formato_685ter_fondo.jpg') }}" alt="">

    <h1>DEPARTAMENTO DE CONCILIACIÓN, GÉNERO E IGUALDAD<br>
        FORMATO DE ATENCIÓN PARA CASOS PREVISTOS EN EL ARTÍCULO 685 TER DE LA LEY FEDERAL DEL TRABAJO Y SITUACIONES DE VIOLENCIA LABORAL</h1>
    <p style="text-align:center; margin-bottom: 10pt;">Fecha: {!! $linea($fecha, '140pt') !!} &nbsp;&nbsp;&nbsp;&nbsp; Folio: {!! $linea($datos['expediente'] ?? null, '140pt') !!}</p>

    <h2 style="margin-top:4pt">AVISO DE PRIVACIDAD:</h2>
    <p>El <b>Centro de Conciliación Laboral del Estado de Michoacán de Ocampo</b>, con domicilio en Boulevard García de León número 1575, Colonia Chapultepec Oriente, Código Postal 58260, en la ciudad de Morelia, Michoacán, en su carácter de sujeto obligado y responsable del tratamiento de los datos personales que se recaben a través del presente <b>Formato de Atención para Casos Previstos en el Artículo 685 Ter de la Ley Federal del Trabajo y Situaciones de Violencia Laboral</b>, se compromete a proteger dicha información conforme a lo dispuesto en los artículos 97 y 101 de la Ley de Transparencia, Acceso a la Información Pública y Protección de Datos Personales del Estado de Michoacán de Ocampo, así como demás normativa aplicable.<br>
    <b>Datos personales recabados:</b> Los datos personales que se recaban de la persona solicitante incluyen: nombre completo, edad, sexo, puesto, área de trabajo, número telefónico, correo electrónico, así como datos relacionados con la empresa o patrón, nombre y cargo del jefe inmediato, y en su caso, cualquier otra información proporcionada de manera voluntaria para la atención del caso.<br>
    <b>Finalidad del tratamiento:</b> Los datos personales recabados serán utilizados exclusivamente para:</p>
    <ul>
        <li>Brindar atención, orientación y registro de casos relacionados con violencia laboral o situaciones previstas en el artículo 685 Ter de la Ley Federal del Trabajo.</li>
        <li>Dar seguimiento a los hechos manifestados por la persona solicitante.</li>
        <li>Canalizar, en su caso, a las áreas competentes o iniciar el procedimiento de conciliación correspondiente.</li>
        <li>Integrar expedientes y generar estadísticas internas que permitan mejorar los servicios institucionales.</li>
    </ul>
    <p><b>Confidencialidad y transferencia de datos:</b> La información proporcionada será tratada de manera confidencial y no será compartida con terceros ajenos al procedimiento, salvo en los casos en que sea necesario atender requerimientos de autoridad competente, conforme a la legislación aplicable. En ningún caso se realizará la transferencia de datos personales sin el consentimiento del titular, salvo las excepciones previstas en la ley.<br>
    <b>Consentimiento:</b> De conformidad con lo dispuesto en el artículo 101 de la Ley de Transparencia, el titular de los datos personales otorga su consentimiento para el tratamiento de los mismos al proporcionar su información a través del presente formato.</p>

    @php $conoce = isset($datos['conoce_jefe']) ? (string) $datos['conoce_jefe'] : null; @endphp
    <h2>DATOS DE LA PERSONA SOLICITANTE:</h2>
    <div class="datos">
        <table class="c"><tr>{!! $campo('Nombre completo:', $datos['nombre'] ?? null) !!}</tr></table>
        <table class="c"><tr>
            {!! $campo('Edad:', $datos['edad'] ?? null) !!}<td class="sep" style="width:120pt"></td>
            <td class="l">Género:</td>
            <td class="t">{!! $caja(($datos['genero'] ?? null) === 'M') !!} M &nbsp; {!! $caja(($datos['genero'] ?? null) === 'F') !!} F &nbsp; {!! $caja(($datos['genero'] ?? null) === 'Otro') !!} Otro:</td>
            <td class="lv" style="width:90pt">{!! $val(($datos['genero'] ?? null) === 'Otro' ? ($datos['genero_otro'] ?? null) : null) !!}</td>
        </tr></table>
        <table class="c"><tr>
            {!! $campo('Teléfono de contacto:', $datos['telefono'] ?? null) !!}<td class="sep"></td>
            {!! $campo('Correo electrónico:', $datos['correo'] ?? null) !!}
        </tr></table>
    </div>

    <h2>DATOS DE LA FUENTE DE EMPLEO:</h2>
    <div class="datos">
        <table class="c"><tr>{!! $campo('Nombre de la empresa o persona empleadora:', $datos['empresa'] ?? null) !!}</tr></table>
        <table class="c"><tr>{!! $campo('Área de adscripción:', $datos['area_adscripcion'] ?? null) !!}</tr></table>
        <table class="c"><tr>{!! $campo('Puesto:', $datos['puesto'] ?? null) !!}</tr></table>
        <table class="c"><tr><td class="l">¿Conoce el nombre de su jefe inmediato?</td>
            <td>{!! $caja($conoce === '1') !!} Sí &nbsp;&nbsp; {!! $caja($conoce === '0') !!} No</td></tr></table>
        <table class="c"><tr><td class="t">En caso afirmativo, señálelo:</td><td class="lv">{!! $val($conoce === '1' ? ($datos['jefe_inmediato'] ?? null) : null) !!}</td></tr></table>
    </div>

    <div class="salto"></div>

    <div class="datos" style="margin-top: 0">
        <div class="preg"><b class="n">1.</b><b>Tipo de situación que enfrenta</b> <i>(marque la opción que corresponda)</i>:
            <div class="op">{!! $caja(in_array('discriminacion', $tipos, true)) !!} He sido objeto de discriminación. Señale el motivo: {!! $linea(in_array('discriminacion', $tipos, true) ? ($datos['motivos'] ?? null) : null, '180pt') !!}</div>
            <div class="op">{!! $caja(in_array('acoso_laboral', $tipos, true)) !!} He sido objeto de acoso / hostigamiento laboral (violencia laboral).</div>
            <div class="op">{!! $caja(in_array('acoso_sexual', $tipos, true)) !!} He recibido acoso sexual por parte de un superior jerárquico o de un compañero de trabajo.</div>
            <div class="op">{!! $caja(in_array('riesgo_trabajo', $tipos, true)) !!} He sufrido un riesgo o accidente de trabajo. En caso de ser afirmativo, señale:</div>
            <div class="op" style="margin-left: 28pt">{!! $caja(($datos['riesgo_lugar'] ?? null) === 'dentro') !!} <i>Dentro del centro de trabajo.</i> &nbsp;&nbsp;&nbsp; {!! $caja(($datos['riesgo_lugar'] ?? null) === 'fuera') !!} <i>Fuera del centro de trabajo.</i></div>
            <div class="op">{!! $caja(in_array('violencia_laboral', $tipos, true)) !!} He sido objeto de malos tratos o violencia laboral.</div>
        </div>

        <div class="preg"><b class="n">2.</b><b>Frecuencia con la que han sucedido los hechos:</b>
            @foreach ($CX::FRECUENCIAS as $valor => $texto)
                <div class="op">{!! $caja(($datos['frecuencia'] ?? null) === $valor) !!} {{ $texto }}</div>
            @endforeach
        </div>

        <div class="preg"><b class="n">3.</b><b>Cambios que se dieron en su situación laboral después de los hechos:</b>
            @foreach ($CX::SITUACIONES as $valor => $texto)
                <div class="op">{!! $caja(($datos['situacion_laboral'] ?? null) === $valor) !!} {{ $texto }}@if ($valor === 'otro'): {!! $linea(($datos['situacion_laboral'] ?? null) === 'otro' ? ($datos['situacion_otro'] ?? null) : null, '280pt') !!}@endif</div>
            @endforeach
        </div>

        <div class="preg"><b class="n">4.</b><b>¿La persona afectada comunicó los hechos a alguien más de su área de trabajo?</b>
            <div class="op">Describa a quién o a quiénes:</div>
            <div class="texto">{{ $datos['descripcion_persona'] ?? '' }}&nbsp;</div>
            @if (empty($datos['descripcion_persona']))<div class="texto">&nbsp;</div>@endif
        </div>

        <div class="preg"><b class="n">5.</b><b>Descripción de las conductas manifestadas:</b>
            <div class="texto">{{ $datos['descripcion_conductas'] ?? '' }}&nbsp;</div>
            @if (empty($datos['descripcion_conductas']))<div class="texto">&nbsp;</div><div class="texto">&nbsp;</div>@endif
        </div>

        <div class="preg"><b class="n">6.</b><b>Observaciones y comentarios:</b>
            <div class="texto">{{ $datos['observaciones'] ?? '' }}&nbsp;</div>
            @if (empty($datos['observaciones']))<div class="texto">&nbsp;</div>@endif
        </div>
    </div>

    <p class="nota"><b>Nota:</b> En caso de advertirse que la persona usuaria se encuentra dentro de alguno de los supuestos de excepción establecidos en el Artículo 685 Ter de la LFT, se le informará que no se encuentra obligada a agotar la instancia conciliatoria; asimismo, se hará constar que manifiesta no haber recibido orientación jurídica previa, que reconoce encontrarse en un caso de excepción y que, con pleno conocimiento de ello, expresa su voluntad de continuar con el procedimiento de conciliación; de igual forma, se asentará que le fue leído el Decálogo de Derechos y Obligaciones de las y los Usuarios, quedando debidamente enterada de su contenido.</p>
    <p class="nota">En caso de que la persona usuaria determine no continuar con el procedimiento, y una vez proporcionada la asesoría jurídica correspondiente, autoriza al Centro de Conciliación para ser canalizada ante el Departamento de la Procuraduría Local de la Defensa del Trabajo, ubicado en {!! $linea($datos['domicilio_procuraduria'] ?? null, '230pt') !!}, a efecto de que le brinden la representación legal que corresponda. Asimismo, en caso de advertirse la posible comisión de un delito, se procederá a su canalización ante las dependencias competentes, tales como la Comisión de Atención a Víctimas, la SEIMUJER y la COEPREDV con el propósito de garantizar una atención integral.</p>

    <div class="decision nota">
        <b class="t">Decisión de la persona usuaria:</b>
        &nbsp; {!! $caja($resultado === 'continua') !!} Continúa con el procedimiento de conciliación.
        &nbsp; {!! $caja($resultado === 'canaliza') !!} No continúa con el procedimiento.
        <div style="margin-top: 3pt"><b class="t">Se canaliza para atención integral a:</b></div>
        <table style="width:100%; margin-top: 3pt; border-collapse: collapse;">
            @foreach (array_chunk($CX::INSTITUCIONES, 2, true) as $par)
                <tr>
                    @foreach ($par as $clave => $nombre)
                        <td style="width:50%; padding: 0;">{!! $caja(in_array($clave, $inst, true)) !!} {{ $nombre }}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </div>

    <div class="acepto">A C E P T O</div>
    <table class="firmas">
        <tr>
            <td style="width: 25%"></td>
            <td style="width: 50%"><div class="firma">{{ $titulo($datos['nombre'] ?? null) ?: ' ' }}<br>Nombre y firma de la persona interesada</div></td>
            <td style="width: 25%"></td>
        </tr>
    </table>
    <table class="firmas" style="margin-top: 10pt">
        <tr>
            <td style="width: 50%"><div class="firma">{{ $titulo($atendio) }}<br>Nombre y firma del servidor público del CCL que atendió</div></td>
            <td style="width: 50%"><div style="margin-bottom: 18pt">Vo. Bo.</div><div class="firma">Jefa del Departamento de Conciliación, Género e Igualdad</div></td>
        </tr>
    </table>
</body>
</html>
