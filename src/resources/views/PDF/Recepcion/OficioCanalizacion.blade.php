{{--
    Oficio de canalización institucional para atención integral (casos de
    excepción, art. 685 Ter LFT). CasosExcepcionController::oficioPdf,
    App\Support\OficioCanalizacion. Uno por institución; el anexo es el
    formato 685 Ter. Si falta el titular o el cargo, sale la línea en blanco.

    Espera $oficio (renglón de excepcion_oficios) e $institucion (nombre).
--}}
@php
    $fecha = \Illuminate\Support\Carbon::parse($oficio->fecha)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    $lugar = $oficio->lugar ?: 'Morelia, Michoacán';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Oficio {{ $oficio->numero }}</title>
    <style>
        @page { margin: 84pt 56pt 62pt 56pt; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10pt; color: #111; line-height: 1.36; }
        .fondo { position: fixed; top: -84pt; left: -56pt; width: 595.3pt; height: 841.9pt; z-index: -1000; }
        .encabezado { width: 100%; border-collapse: collapse; margin-bottom: 10pt; }
        .encabezado td { padding: 0; vertical-align: top; }
        .ref { font-size: 9.4pt; line-height: 1.4; padding: 0 0 1pt; }
        .ref.l { font-weight: bold; white-space: nowrap; padding-right: 6pt; width: 1%; }
        .lugar { text-align: right; margin: 0 0 12pt; }
        .destino { margin: 0 0 12pt; font-weight: bold; line-height: 1.3; }
        .linea { display: inline-block; border-bottom: 0.6pt solid #333; width: 230pt; height: 11pt; }
        p { margin: 0 0 7pt; text-align: justify; }
        .firma { margin-top: 14pt; text-align: center; page-break-inside: avoid; }
        .firma .raya { width: 240pt; border-top: 0.7pt solid #111; margin: 36pt auto 4pt; }
        .anexo { margin-top: 14pt; font-size: 8.6pt; }
    </style>
</head>
<body>
    <img class="fondo" src="{{ public_path('assets/images/formato_685ter_fondo.jpg') }}" alt="">

    <table class="encabezado">
        <tr><td rowspan="2"></td><td class="ref l">OFICIO NÚM.:</td><td class="ref" style="width: 1%; white-space: nowrap;">{{ $oficio->numero }}</td></tr>
        <tr><td class="ref l">ASUNTO:</td><td class="ref" style="white-space: nowrap;">Canalización institucional para atención integral.</td></tr>
    </table>

    <p class="lugar">{{ $lugar }}, a {{ $fecha }}.</p>

    <div class="destino">
        @if ($oficio->destinatario){{ mb_strtoupper($oficio->destinatario) }}@else<span class="linea"></span>@endif<br>
        @if ($oficio->cargo){{ mb_strtoupper($oficio->cargo) }}@else<span class="linea"></span>@endif<br>
        {{ mb_strtoupper($institucion) }}<br>
        P R E S E N T E.
    </div>

    <p>Con fundamento en los artículos 1° y 17 de la Constitución Política de los Estados Unidos Mexicanos; 685 Ter de la Ley Federal del Trabajo, en lo que resulte aplicable a la naturaleza de los hechos expuestos; así como en el procedimiento P-CCL-UJDCGI-05, denominado “Atención a casos de excepción para agotar la conciliación”, del Manual de Procedimientos del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, me permito solicitar su colaboración institucional para la atención del caso que se describe a continuación.</p>

    <p>Lo anterior, en virtud de que, derivado de la entrevista realizada por el Departamento de Conciliación, Género e Igualdad de este Centro, la persona usuaria expuso una problemática de naturaleza laboral y, adicionalmente, manifestó su interés y solicitó expresamente ser canalizada para recibir atención integral, de acuerdo con las necesidades que refirió durante dicha entrevista.</p>

    <p>En ese sentido, y atendiendo a la solicitud expresa de la persona usuaria, agradeceré que, en el ámbito de sus atribuciones y competencias, se realice la valoración correspondiente y, de resultar procedente, se le proporcione la orientación, atención especializada y acompañamiento institucional que requiera, conforme a los servicios disponibles y a la normativa aplicable.</p>

    <p>Para tal efecto, se adjunta el formato que contiene la narración de los hechos realizada por la propia persona usuaria, con la finalidad de aportar elementos para la valoración del caso y facilitar la determinación de las acciones que correspondan, sin que la presente canalización implique pronunciamiento alguno respecto de la veracidad de los hechos narrados o de la existencia de responsabilidades.</p>

    <p>Asimismo, se solicita que la información y documentación remitidas sean tratadas con la debida confidencialidad y que los datos personales se protejan conforme a las disposiciones aplicables, procurando una atención digna, oportuna, libre de revictimización y con perspectiva de género cuando corresponda.</p>

    <p>Agradezco de antemano su colaboración y apoyo para favorecer la atención integral solicitada por la persona usuaria.</p>

    <p>Sin otro particular, reciba un cordial saludo.</p>

    <div class="firma">
        <b>ATENTAMENTE</b>
        <div class="raya"></div>
        <b>Titular del Departamento de Conciliación, Género e Igualdad</b><br>
        Centro de Conciliación Laboral del Estado de Michoacán de Ocampo
    </div>

    <p class="anexo"><b>Anexo:</b> Formato de narración de hechos elaborado por la persona usuaria.</p>
</body>
</html>
