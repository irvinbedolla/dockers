{{--
    Formato de aceptación de canalización (casos de excepción, art. 685 Ter
    LFT). CasosExcepcionController::aceptacionPdf. Uno por institución; lo
    firma la persona usuaria antes de generar el oficio. Firmas y "medio de
    manifestación de voluntad" quedan en blanco para llenarse a mano.

    Espera $oficio (renglón de excepcion_oficios), $institucion, $folio,
    $persona y $atendio.
--}}
@php
    $fecha = \Illuminate\Support\Carbon::parse($oficio->fecha)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    $lugar = $oficio->lugar ?: 'Morelia, Michoacán';
    $val = fn ($v) => ($v !== null && trim((string) $v) !== '') ? e($v) : '&nbsp;';
    $titulo = fn ($t) => $t ? \Illuminate\Support\Str::title(mb_strtolower(trim($t))) : null;
    $atiende = trim(implode(', ', array_filter([$titulo($atendio), $oficio->cargo_atiende ?? null])));
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Aceptación de canalización</title>
    <style>
        @page { margin: 82pt 56pt 60pt 56pt; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 9.6pt; color: #111; line-height: 1.3; }
        .fondo { position: fixed; top: -82pt; left: -56pt; width: 595.3pt; height: 841.9pt; z-index: -1000; }
        h1 { font-size: 10.4pt; text-align: center; margin: 0 0 10pt; line-height: 1.4; }
        p { margin: 0 0 5pt; text-align: justify; }
        table.c { width: 100%; border-collapse: collapse; margin: 0 0 6pt; }
        table.c td { padding: 0; vertical-align: bottom; }
        table.c td.l { font-weight: bold; white-space: nowrap; padding-right: 5pt; width: 1%; }
        table.c td.v { border-bottom: 0.6pt solid #333; padding: 0 3pt; }
        .derecha { text-align: right; }
        .bloque { margin: 6pt 0 6pt 18pt; }
        .sep { border-top: 0.6pt solid #999; margin: 6pt 0; }
        .titulo-firma { text-align: center; font-weight: bold; margin: 8pt 0 6pt; }
        .firmas { page-break-inside: avoid; }
    </style>
</head>
<body>
    <img class="fondo" src="{{ public_path('assets/images/formato_685ter_fondo.jpg') }}" alt="">

    <h1>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO<br>
        DEPARTAMENTO DE CONCILIACIÓN, GÉNERO E IGUALDAD<br>
        FORMATO DE ACEPTACIÓN DE CANALIZACIÓN</h1>

    <table class="c" style="width: 48%; margin-left: 52%;"><tr><td class="l">Folio de atención:</td><td class="v">{!! $val($folio) !!}</td></tr></table>
    <p class="derecha" style="margin-bottom: 10pt;">{{ $lugar }}, a {{ $fecha }}.</p>

    <p>Quien suscribe, <span style="border-bottom: 0.6pt solid #333; padding: 0 3pt;">{!! $persona ? e($persona) : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}</span>, por mi propio derecho, manifiesto que, con motivo de la atención y entrevista brindada por personal del Departamento de Conciliación, Género e Igualdad del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, se me informó sobre la posibilidad de recibir atención y orientación por parte de otra institución o instancia especializada, en virtud de las necesidades identificadas durante dicha entrevista.</p>

    <p>Por lo anterior, manifiesto libre y expresamente mi aceptación para ser canalizado(a) a la siguiente institución o instancia:</p>

    <div class="bloque">
        <table class="c"><tr><td class="l">Institución receptora:</td><td class="v">{{ $institucion }}</td></tr></table>
        <table class="c"><tr><td class="l">Área o servicio al que se canaliza:</td><td class="v">{!! $val($oficio->area_servicio ?? null) !!}</td></tr></table>
        <table class="c"><tr><td class="l">Motivo general de la canalización:</td><td class="v">{!! $val($oficio->motivo ?? null) !!}</td></tr></table>
    </div>

    <div class="sep"></div>

    <p>Declaro que recibí información clara y comprensible sobre el objeto de la canalización, su finalidad y la institución a la que se propone mi derivación; asimismo, tuve oportunidad de plantear mis dudas y estas fueron atendidas.</p>

    <p>De igual manera, manifiesto que mi aceptación es voluntaria y que la canalización tiene por objeto facilitar mi acceso a los servicios de orientación, atención o apoyo que resulten pertinentes, sin que ello implique garantía de admisión, atención o resolución por parte de la institución receptora.</p>

    <p>En materia de protección de datos personales, autorizo que se comuniquen a la institución receptora únicamente los datos personales estrictamente necesarios para gestionar la canalización y dar continuidad a la atención solicitada, conforme a la normativa aplicable y al aviso de privacidad correspondiente. Cuando se requiera el tratamiento de datos personales sensibles o una autorización específica, esta deberá recabarse en los términos legalmente procedentes.</p>

    <p>Asimismo, se me informa que la canalización no sustituye ni condiciona el ejercicio de mis derechos laborales, ni implica por sí misma la conclusión de cualquier trámite que, en su caso, corresponda ante el Centro de Conciliación Laboral o ante otra autoridad competente.</p>

    <p>Finalmente, manifiesto que comprendo el alcance de esta aceptación y que recibí información sobre los pasos siguientes para dar continuidad a la canalización.</p>

    <div class="firmas">
        <div class="titulo-firma">ATENTAMENTE</div>
        <table class="c"><tr><td class="l">Nombre de la persona usuaria:</td><td class="v">{!! $val($persona) !!}</td></tr></table>
        <table class="c" style="margin-top: 12pt;"><tr><td class="l">Firma o medio de manifestación de voluntad:</td><td class="v">&nbsp;</td></tr></table>
        <table class="c" style="width: 45%; margin-top: 8pt;"><tr><td class="l">Fecha:</td><td class="v">&nbsp;</td></tr></table>
    </div>
    <div class="firmas">
        <div class="titulo-firma" style="margin-top: 10pt;">PERSONAL QUE BRINDA LA ATENCIÓN</div>
        <table class="c"><tr><td class="l">Nombre y cargo:</td><td class="v">{!! $val($atiende) !!}</td></tr></table>
        <table class="c" style="width: 60%; margin-top: 12pt;"><tr><td class="l">Firma:</td><td class="v">&nbsp;</td></tr></table>
    </div>
</body>
</html>
