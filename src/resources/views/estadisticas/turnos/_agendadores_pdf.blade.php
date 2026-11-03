{{--
    PDF e imagen de /estadisticas/turnos: ¿quién agenda las citas en línea?
    (App\Support\AgendadoresCitas). Sólo tablas y CSS sencillo; usa las
    clases de _contenido. Espera $ag.
--}}
@php
    $pctP = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';
    $topP = \App\Support\AgendadoresCitas::TOP_PDF;
@endphp
@if (! empty($ag['migrado']) && $ag['citas'] > 0)
    <div class="et-sec">¿Quién agenda las citas en línea?</div>
    <table class="et-cifras">
        <tr>
            <td><b>{{ number_format($ag['citas']) }}</b><span>Citas en línea</span><small>{{ number_format($ag['personas']) }} personas distintas</small></td>
            <td><b>{{ number_format($ag['ips']) }}</b><span>Conexiones (IP)</span><small>{{ number_format($ag['conIp']) }} con IP{{ $ag['sinIp'] ? ' · '.$ag['sinIp'].' sin IP' : '' }}</small></td>
            <td><b>{{ number_format($ag['ipsFrecuentes']) }}</b><span>IP frecuentes</span><small>{{ $ag['frecuente'] }} citas o más</small></td>
            <td><b>{{ $pctP($ag['pctFrecuentes']) }}</b><span>Citas de IP frecuentes</span></td>
            <td><b>{{ $ag['topIp'] ? $ag['topIp']['citas'] : '—' }}</b><span>La IP con más citas</span><small>{{ $ag['topIp']['clave'] ?? '' }}</small></td>
            <td><b>{{ $pctP($ag['pctTop10']) }}</b><span>Las 10 IP con más citas</span></td>
            <td><b>{{ $ag['correosFrecuentes'] }} / {{ $ag['telefonosFrecuentes'] }}</b><span>Correos / teléfonos frecuentes</span></td>
            <td><b style="color:#9A3A22">{{ number_format($ag['bloqueos']['total']) }}</b><span>Intentos detenidos</span><small>por el límite</small></td>
        </tr>
    </table>

    @foreach ([['porIp', 'Por conexión (IP)', 'IP'], ['porCorreo', 'Por correo', 'Correo'], ['porTelefono', 'Por teléfono', 'Teléfono']] as [$clave, $titulo, $col])
        @if (! empty($ag[$clave]))
            <p class="et-nota" style="font-weight:bold;color:#354647;margin-top:6px">{{ $titulo }} · los {{ min($topP, count($ag[$clave])) }} con más citas</p>
            <table class="et-t">
                <thead>
                    <tr>
                        <th class="i">{{ $col }}</th><th>Citas</th><th>%</th><th>Personas</th>
                        <th>Correos</th><th>Teléfonos</th><th>IP</th><th>Máx. en 1 h</th><th>No acudieron</th>
                        <th class="i">Sedes</th><th>Primera</th><th>Última</th><th>Detenidos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (array_slice($ag[$clave], 0, $topP) as $r)
                        <tr>
                            <td class="i"><b>{{ $r['clave'] }}</b></td>
                            <td><b>{{ $r['citas'] }}</b></td>
                            <td>{{ $pctP($r['pct']) }}</td>
                            <td>{{ $r['personas'] }}</td>
                            <td>{{ $r['correos'] }}</td>
                            <td>{{ $r['telefonos'] }}</td>
                            <td>{{ $r['ips'] }}</td>
                            <td>{{ $r['maxHora'] }}</td>
                            <td>{{ $r['noAcudieron'] }}</td>
                            <td class="i">{{ $r['sedesTexto'] }}</td>
                            <td>{{ $r['primera'] }}</td>
                            <td>{{ $r['ultima'] }}</td>
                            <td>{{ $r['bloqueos'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    @if ($ag['bloqueos']['total'])
        <p class="et-nota">
            Intentos detenidos por el límite:
            {{ implode(' · ', array_map(fn ($m, $n) => (\App\Support\LimiteCitas::MOTIVOS[$m] ?? $m).': '.$n, array_keys($ag['bloqueos']['porMotivo']), $ag['bloqueos']['porMotivo'])) }}.
        </p>
    @endif
    <p class="et-nota">Varias personas pueden compartir una IP (empresa, escuela o proveedor de celular): es una pista que se confirma con el correo, el teléfono y las personas distintas.</p>
@endif
