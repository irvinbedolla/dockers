{{--
    Hojas del Excel de estadísticas de turnos sobre quién agenda las citas en
    línea (App\Support\AgendadoresCitas::armar con todo). $hoja: ag_ip,
    ag_correo, ag_telefono, ag_citas o ag_bloqueos. La tabla empieza en la
    fila 6, como las demás hojas.
--}}
@php
    $th = 'background-color: #496163; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #3A4E50;';
    $td = 'border: 1px solid #D5DDDD; vertical-align: top;';
    $num = $td.' text-align: center;';
    $titulosAg = [
        'ag_ip'       => 'Citas en línea por conexión (IP)',
        'ag_correo'   => 'Citas en línea por correo',
        'ag_telefono' => 'Citas en línea por teléfono',
        'ag_citas'    => 'Citas en línea: de dónde se agendó cada una',
        'ag_bloqueos' => 'Intentos detenidos por el límite de citas',
    ];
    $grupo = ['ag_ip' => ['porIp', 'IP'], 'ag_correo' => ['porCorreo', 'Correo'], 'ag_telefono' => ['porTelefono', 'Teléfono']][$hoja] ?? null;
    $ancho = 14;
@endphp
<table>
    <tr><td colspan="{{ $ancho }}" style="font-size: 16px; font-weight: bold; color: #354647; height: 28px;">{{ $titulo }} · Centro de Conciliación Laboral del Estado de Michoacán</td></tr>
    <tr><td colspan="{{ $ancho }}" style="font-size: 12px; font-weight: bold; color: #496163;">{{ $titulosAg[$hoja] }} · {{ $periodo }}</td></tr>
    <tr><td colspan="{{ $ancho }}" style="color: #5F6B6C;">{{ $filtrosTexto }} · sólo citas en línea, sin las de prueba · frecuente: {{ $ag['frecuente'] ?? 3 }} citas o más</td></tr>
    <tr><td colspan="{{ $ancho }}" style="color: #8A9899;">Generado el {{ $generado->format('d/m/Y H:i') }}{{ $por ? ' por '.$por : '' }}. Varias personas pueden compartir una IP: confírmalo con el correo, el teléfono y las personas distintas.</td></tr>
    <tr><td colspan="{{ $ancho }}"></td></tr>

    @if (empty($ag['migrado']))
        <tr><td colspan="{{ $ancho }}" style="color: #8A9899;">Todavía no se guarda desde qué conexión se agenda (falta la migración).</td></tr>
    @elseif ($grupo)
        @php [$clave, $col] = $grupo; @endphp
        <tr>
            <th width="26" style="{{ $th }} text-align: left;">{{ $col }}</th>
            <th width="9" style="{{ $th }}">Citas</th>
            <th width="9" style="{{ $th }}">% del total</th>
            <th width="10" style="{{ $th }}">Personas distintas</th>
            <th width="9" style="{{ $th }}">Correos</th>
            <th width="9" style="{{ $th }}">Teléfonos</th>
            <th width="9" style="{{ $th }}">IP</th>
            <th width="10" style="{{ $th }}">Máx. en 1 hora</th>
            <th width="10" style="{{ $th }}">Atendidas</th>
            <th width="10" style="{{ $th }}">No acudieron</th>
            <th width="34" style="{{ $th }}">Sedes</th>
            <th width="30" style="{{ $th }}">Trámites</th>
            <th width="17" style="{{ $th }}">Primera</th>
            <th width="17" style="{{ $th }}">Última</th>
            <th width="10" style="{{ $th }}">Detenidos</th>
        </tr>
        @forelse ($ag[$clave] ?? [] as $r)
            <tr>
                <td style="{{ $td }} font-weight: bold;">{{ $r['clave'] }}</td>
                <td style="{{ $num }} font-weight: bold;">{{ $r['citas'] }}</td>
                <td style="{{ $num }}">{{ $r['pct'] }}</td>
                <td style="{{ $num }}">{{ $r['personas'] }}</td>
                <td style="{{ $num }}">{{ $r['correos'] }}</td>
                <td style="{{ $num }}">{{ $r['telefonos'] }}</td>
                <td style="{{ $num }}">{{ $r['ips'] }}</td>
                <td style="{{ $num }}">{{ $r['maxHora'] }}</td>
                <td style="{{ $num }}">{{ $r['atendidas'] }}</td>
                <td style="{{ $num }}">{{ $r['noAcudieron'] }}</td>
                <td style="{{ $td }}">{{ $r['sedesTexto'] }}</td>
                <td style="{{ $td }}">{{ $r['tramitesTexto'] }}</td>
                <td style="{{ $num }}">{{ $r['primera'] }}</td>
                <td style="{{ $num }}">{{ $r['ultima'] }}</td>
                <td style="{{ $num }}">{{ $r['bloqueos'] }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $ancho }}" style="color: #8A9899;">Ninguno con 2 citas o más en este periodo.</td></tr>
        @endforelse
    @elseif ($hoja === 'ag_citas')
        <tr>
            <th width="17" style="{{ $th }}">Registrada</th>
            <th width="16" style="{{ $th }}">Folio</th>
            <th width="17" style="{{ $th }}">Cita</th>
            <th width="15" style="{{ $th }}">Sede</th>
            <th width="14" style="{{ $th }}">Trámite</th>
            <th width="30" style="{{ $th }} text-align: left;">Nombre</th>
            <th width="22" style="{{ $th }}">CURP</th>
            <th width="30" style="{{ $th }}">Correo</th>
            <th width="14" style="{{ $th }}">Teléfono</th>
            <th width="18" style="{{ $th }}">IP</th>
            <th width="20" style="{{ $th }}">Navegador</th>
            <th width="12" style="{{ $th }}">Estatus</th>
            <th width="11" style="{{ $th }}">Excepción</th>
        </tr>
        @forelse ($ag['todas'] ?? [] as $d)
            <tr>
                <td style="{{ $num }}">{{ $d['registro'] }}</td>
                <td style="{{ $num }}">{{ $d['folio'] }}</td>
                <td style="{{ $num }}">{{ $d['cita'] }}</td>
                <td style="{{ $num }}">{{ $d['sede'] }}</td>
                <td style="{{ $num }}">{{ $d['tramite'] }}</td>
                <td style="{{ $td }}">{{ $d['nombre'] }}</td>
                <td style="{{ $num }}">{{ $d['curp'] }}</td>
                <td style="{{ $td }}">{{ $d['correo'] }}</td>
                <td style="{{ $num }}">{{ $d['telefono'] }}</td>
                <td style="{{ $num }}">{{ $d['ip'] }}</td>
                <td style="{{ $td }}">{{ $d['navegador'] }}</td>
                <td style="{{ $num }}">{{ $d['estatus'] }}</td>
                <td style="{{ $num }}">{{ $d['excepcion'] ? 'Sí' : '' }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $ancho }}" style="color: #8A9899;">No hay citas en línea en este periodo.</td></tr>
        @endforelse
    @else
        <tr>
            <th width="17" style="{{ $th }}">Cuándo</th>
            <th width="16" style="{{ $th }}">Por</th>
            <th width="26" style="{{ $th }}">Llevaba</th>
            <th width="18" style="{{ $th }}">IP</th>
            <th width="30" style="{{ $th }}">Correo</th>
            <th width="14" style="{{ $th }}">Teléfono</th>
            <th width="30" style="{{ $th }} text-align: left;">Nombre</th>
            <th width="22" style="{{ $th }}">CURP</th>
            <th width="15" style="{{ $th }}">Sede</th>
            <th width="14" style="{{ $th }}">Trámite</th>
            <th width="17" style="{{ $th }}">Pedía</th>
            <th width="20" style="{{ $th }}">Navegador</th>
        </tr>
        @forelse ($ag['bloqueos']['lista'] ?? [] as $b)
            <tr>
                <td style="{{ $num }}">{{ $b['cuando'] }}</td>
                <td style="{{ $num }}">{{ $b['motivo'] }}</td>
                <td style="{{ $td }}">{{ $b['regla'] }}</td>
                <td style="{{ $num }}">{{ $b['ip'] }}</td>
                <td style="{{ $td }}">{{ $b['correo'] }}</td>
                <td style="{{ $num }}">{{ $b['telefono'] }}</td>
                <td style="{{ $td }}">{{ $b['nombre'] }}</td>
                <td style="{{ $num }}">{{ $b['curp'] }}</td>
                <td style="{{ $num }}">{{ $b['sede'] }}</td>
                <td style="{{ $num }}">{{ $b['tramite'] }}</td>
                <td style="{{ $num }}">{{ $b['pedia'] }}</td>
                <td style="{{ $td }}">{{ $b['navegador'] }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $ancho }}" style="color: #8A9899;">Ningún intento detenido en este periodo.</td></tr>
        @endforelse
    @endif
</table>
