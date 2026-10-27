{{--
    /estadisticas/turnos → ¿Quién agenda las citas en línea? (App\Support\AgendadoresCitas):
    desde qué conexiones (IP), correos y teléfonos se registran, cuántas
    personas distintas hay detrás y qué intentos detuvo el límite
    (Administración → Módulos). Espera $ag, $f (filtros) y $url.
--}}
@php
    use App\Support\AgendadoresCitas as AG;
    use App\Support\LimiteCitas;
    $pctAg = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';
    $limiteAg = $ag['limite'];
    $reglasAg = array_filter([
        $limiteAg->por_ip ? $limiteAg->por_ip.' por IP' : null,
        $limiteAg->por_correo ? $limiteAg->por_correo.' por correo' : null,
        $limiteAg->por_telefono ? $limiteAg->por_telefono.' por teléfono' : null,
    ]);
    $tablas = [
        ['porIp', 'masIps', 'Por conexión (IP)', 'IP', 'Personas distintas que agendaron desde la misma conexión a internet.'],
        ['porCorreo', 'masCorreos', 'Por correo', 'Correo', 'Un mismo correo con citas de varias personas suele ser de quien agenda por otros.'],
        ['porTelefono', 'masTelefonos', 'Por teléfono', 'Teléfono', 'Un mismo teléfono con citas de varias personas.'],
    ];
@endphp

<div class="tn-tarjeta et-bloque" id="agendadores">
    <div class="et-cabeza">
        <div>
            <h4 class="et-titulo"><i class="bi bi-router" aria-hidden="true"></i> ¿Quién agenda las citas en línea?</h4>
            <span class="et-sub">Conexión (IP), correo y teléfono de las citas en línea con fecha en el periodo · {{ $f['sede'] ?: 'Todas las sedes' }}{{ $f['tipo'] ? ' · '.$f['tipo'] : '' }}</span>
        </div>
        @if ($ag['migrado'])
            <form method="GET" action="{{ route('estadisticas.turnos') }}#agendadores" class="d-flex align-items-center gap-2">
                @foreach (array_filter(['desde' => $f['desde'], 'hasta' => $f['hasta'], 'agrupar' => $f['agrupar'], 'sede' => $f['sede'], 'tipo' => $f['tipo'], 'origen' => $f['origen'], 'minutos' => $f['minutos']]) as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <label for="agFrecuente" class="et-sub mb-0">Frecuente desde</label>
                <select id="agFrecuente" name="frecuente" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    @foreach (AG::FRECUENTE as $n)
                        <option value="{{ $n }}" @selected($ag['frecuente'] === $n)>{{ $n }} citas</option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    @if (! $ag['migrado'])
        <div class="et-vacio">Todavía no se guarda desde qué conexión se agenda: falta correr la migración (<code>php artisan migrate</code>).</div>
    @elseif ($ag['citas'] === 0)
        <div class="et-vacio">No hay citas en línea en este periodo con esos filtros.{{ $ag['soloLinea'] ? ' (Esta sección sólo cuenta citas en línea; el filtro de origen está en «Ventanilla».)' : '' }}</div>
    @else
        @if ($ag['soloLinea'])
            <div class="alert alert-info py-2"><i class="bi bi-info-circle"></i> Esta sección sólo cuenta citas en línea, aunque el filtro de origen esté en «Ventanilla».</div>
        @endif

        <div class="et-cifras mb-3">
            <div class="et-cifra"><b>{{ number_format($ag['citas']) }}</b><span>Citas en línea</span><small>{{ number_format($ag['personas']) }} personas distintas</small></div>
            <div class="et-cifra"><b>{{ number_format($ag['ips']) }}</b><span>Conexiones (IP)</span><small>{{ number_format($ag['conIp']) }} citas con IP{{ $ag['sinIp'] ? ' · '.number_format($ag['sinIp']).' sin IP' : '' }}</small></div>
            <div class="et-cifra {{ $ag['ipsFrecuentes'] ? 'et-cifra--pend' : '' }}"><b>{{ number_format($ag['ipsFrecuentes']) }}</b><span>IP frecuentes</span><small>{{ $ag['frecuente'] }} citas o más</small></div>
            <div class="et-cifra {{ ($ag['pctFrecuentes'] ?? 0) >= 20 ? 'et-cifra--no' : '' }}"><b>{{ $pctAg($ag['pctFrecuentes']) }}</b><span>Citas de IP frecuentes</span><small>{{ number_format($ag['citasFrecuentes']) }} de las que tienen IP</small></div>
            <div class="et-cifra"><b>{{ $ag['topIp'] ? number_format($ag['topIp']['citas']) : '—' }}</b><span>La IP con más citas</span><small>{{ $ag['topIp'] ? $ag['topIp']['clave'].' · '.$ag['topIp']['personas'].' personas' : 'ninguna repite' }}</small></div>
            <div class="et-cifra"><b>{{ $pctAg($ag['pctTop10']) }}</b><span>Las 10 IP con más citas</span><small>de las citas con IP</small></div>
            <div class="et-cifra {{ $ag['correosFrecuentes'] + $ag['telefonosFrecuentes'] ? 'et-cifra--pend' : '' }}"><b>{{ number_format($ag['correosFrecuentes']) }} / {{ number_format($ag['telefonosFrecuentes']) }}</b><span>Correos / teléfonos frecuentes</span><small>{{ $ag['frecuente'] }} citas o más</small></div>
            <div class="et-cifra {{ $ag['bloqueos']['total'] ? 'et-cifra--no' : '' }}"><b>{{ number_format($ag['bloqueos']['total']) }}</b><span>Intentos detenidos</span><small>{{ $reglasAg ? 'límite: '.implode(', ', $reglasAg) : 'sin límite activo' }}</small></div>
        </div>

        @if ($ag['sinIp'] && $ag['sinIp'] === $ag['citas'])
            <div class="alert alert-warning py-2"><i class="bi bi-clock-history"></i> Ninguna de estas citas tiene IP: se registraron antes de que se empezara a guardar. Elige un periodo con citas registradas después del cambio.</div>
        @endif

        <div class="row g-3 mb-2">
            <div class="col-12 col-lg-8">
                <span class="et-etq-f">Hora del día en que se registran <span style="font-weight:600;color:#7A5D10">(en dorado, de 22:00 a 6:59)</span></span>
                <div class="et-grafica" style="height:200px"><canvas id="agHoras" role="img" aria-label="Citas en línea por hora de registro"></canvas></div>
            </div>
            <div class="col-12 col-lg-4">
                <span class="et-etq-f">Día de la semana en que se registran</span>
                <div class="et-grafica" style="height:200px"><canvas id="agDias" role="img" aria-label="Citas en línea por día de registro"></canvas></div>
            </div>
        </div>

        @foreach ($tablas as [$clave, $mas, $titulo, $col, $ayuda])
            <h5 class="et-titulo mt-4 mb-1" style="font-size:14.5px">{{ $titulo }}</h5>
            <p class="et-sub mb-2">{{ $ayuda }} Sólo los que tienen 2 citas o más{{ $ag[$mas] ? '; se muestran los '.AG::TOP.' con más citas (hay '.$ag[$mas].' más)' : '' }}.</p>
            @if (empty($ag[$clave]))
                <div class="et-vacio py-2">Ninguno con 2 citas o más.</div>
            @else
                <div class="table-responsive et-scroll">
                    <table class="table table-hover et-tabla ag-tabla mb-0">
                        <thead>
                            <tr>
                                <th>{{ $col }}</th>
                                <th class="et-num">Citas</th>
                                <th class="et-num">% del total</th>
                                <th class="et-num">Personas</th>
                                @if ($clave !== 'porCorreo')<th class="et-num">Correos</th>@endif
                                @if ($clave !== 'porTelefono')<th class="et-num">Teléfonos</th>@endif
                                @if ($clave !== 'porIp')<th class="et-num">IP</th>@endif
                                <th class="et-num" title="Lo más que se registró en una misma hora">Máx. en 1 hora</th>
                                <th class="et-num">No acudieron</th>
                                <th>Sedes</th>
                                <th>Primera / última</th>
                                @if ($clave === 'porIp')<th>Navegador</th>@endif
                                <th class="et-num">Detenidos</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ag[$clave] as $i => $r)
                                @php $frec = $r['citas'] >= $ag['frecuente']; @endphp
                                <tr class="{{ $frec ? 'ag-frecuente' : '' }}">
                                    <td class="et-etq"><code>{{ $r['clave'] }}</code></td>
                                    <td class="et-num"><b>{{ number_format($r['citas']) }}</b></td>
                                    <td class="et-num">{{ $pctAg($r['pct']) }}</td>
                                    <td class="et-num">{{ $r['personas'] }}</td>
                                    @if ($clave !== 'porCorreo')<td class="et-num">{{ $r['correos'] }}</td>@endif
                                    @if ($clave !== 'porTelefono')<td class="et-num">{{ $r['telefonos'] }}</td>@endif
                                    @if ($clave !== 'porIp')<td class="et-num">{{ $r['ips'] }}</td>@endif
                                    <td class="et-num">{{ $r['maxHora'] }}</td>
                                    <td class="et-num">{{ $r['noAcudieron'] }}@if ($r['pctNoAcudieron'] !== null)<small>{{ $pctAg($r['pctNoAcudieron']) }}</small>@endif</td>
                                    <td style="white-space:normal;min-width:150px">{{ $r['sedesTexto'] }}</td>
                                    <td><small>{{ $r['primera'] }}<br>{{ $r['ultima'] }}</small></td>
                                    @if ($clave === 'porIp')<td><small>{{ $r['navegador'] }}{{ $r['navegadores'] > 1 ? ' +'.($r['navegadores'] - 1) : '' }}</small></td>@endif
                                    <td class="et-num" style="{{ $r['bloqueos'] ? 'color:#9A3A22;font-weight:700' : '' }}">{{ $r['bloqueos'] }}</td>
                                    <td><button type="button" class="btn btn-sm btn-outline-secondary ag-ver" data-ag="{{ $clave }}-{{ $i }}" aria-expanded="false">Ver citas</button></td>
                                </tr>
                                <tr class="ag-detalle" id="ag-{{ $clave }}-{{ $i }}" hidden>
                                    <td colspan="20">
                                        <table class="table table-sm mb-0 ag-sub">
                                            <thead><tr><th>Registrada</th><th>Folio</th><th>Cita</th><th>Sede</th><th>Trámite</th><th>Nombre</th><th>CURP</th><th>Correo</th><th>Teléfono</th><th>IP</th><th>Navegador</th><th>Estatus</th></tr></thead>
                                            <tbody>
                                                @foreach ($r['detalle'] as $d)
                                                    <tr>
                                                        <td>{{ $d['registro'] }}</td>
                                                        <td><b>{{ $d['folio'] }}</b>@if ($d['excepcion']) <span class="et-chip-exc">Excepción</span>@endif</td>
                                                        <td>{{ $d['cita'] }}</td>
                                                        <td>{{ $d['sede'] }}</td>
                                                        <td>{{ $d['tramite'] }}</td>
                                                        <td>{{ $d['nombre'] }}</td>
                                                        <td><code>{{ $d['curp'] }}</code></td>
                                                        <td>{{ $d['correo'] }}</td>
                                                        <td>{{ $d['telefono'] }}</td>
                                                        <td><code>{{ $d['ip'] }}</code></td>
                                                        <td>{{ $d['navegador'] }}</td>
                                                        <td>{{ $d['estatus'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @if ($r['citas'] > count($r['detalle']))
                                            <p class="et-sub mb-0 mt-1">Se muestran las primeras {{ count($r['detalle']) }} de {{ $r['citas'] }}; el Excel trae todas las de las primeras {{ AG::TOP }}.</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach

        @if (! empty($ag['porDominio']))
            <h5 class="et-titulo mt-4 mb-1" style="font-size:14.5px">Dominios de correo propios</h5>
            <p class="et-sub mb-2">Correos que no son de Gmail, Hotmail, Outlook, Yahoo, etc. Suelen ser de empresas o despachos.</p>
            <div class="table-responsive">
                <table class="table table-hover et-tabla mb-0">
                    <thead><tr><th>Dominio</th><th class="et-num">Citas</th><th class="et-num">Personas</th><th class="et-num">Correos</th><th class="et-num">IP</th><th>Sedes</th><th>Primera / última</th></tr></thead>
                    <tbody>
                        @foreach ($ag['porDominio'] as $r)
                            <tr>
                                <td class="et-etq"><code>{{ $r['clave'] }}</code></td>
                                <td class="et-num"><b>{{ $r['citas'] }}</b></td>
                                <td class="et-num">{{ $r['personas'] }}</td>
                                <td class="et-num">{{ $r['correos'] }}</td>
                                <td class="et-num">{{ $r['ips'] }}</td>
                                <td style="white-space:normal">{{ $r['sedesTexto'] }}</td>
                                <td><small>{{ $r['primera'] }} · {{ $r['ultima'] }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    @if ($ag['migrado'])
        <h5 class="et-titulo mt-4 mb-1" style="font-size:14.5px">Intentos detenidos por el límite</h5>
        <p class="et-sub mb-2">
            Citas que no se registraron porque la conexión, el correo o el teléfono ya tenía el máximo
            ({{ $reglasAg ? implode(', ', $reglasAg).' '.LimiteCitas::PERIODOS[$limiteAg->periodo] : 'hoy no hay límite activo' }}; se cambia en Administración → Módulos).
            Por la fecha que pedían.
        </p>
        @if (empty($ag['bloqueos']['lista']))
            <div class="et-vacio py-2">Ningún intento detenido en este periodo.</div>
        @else
            <div class="d-flex flex-wrap gap-2 mb-2">
                @foreach ($ag['bloqueos']['porMotivo'] as $motivo => $n)
                    <span class="md-chip-ag">{{ LimiteCitas::MOTIVOS[$motivo] ?? $motivo }}: <b>{{ $n }}</b></span>
                @endforeach
            </div>
            <div class="table-responsive et-scroll">
                <table class="table table-hover et-tabla mb-0">
                    <thead><tr><th>Cuándo</th><th>Por</th><th>Llevaba</th><th>IP</th><th>Correo</th><th>Teléfono</th><th>Nombre</th><th>CURP</th><th>Sede</th><th>Trámite</th><th>Pedía</th><th>Navegador</th></tr></thead>
                    <tbody>
                        @foreach ($ag['bloqueos']['lista'] as $b)
                            <tr>
                                <td>{{ $b['cuando'] }}</td>
                                <td><b>{{ $b['motivo'] }}</b></td>
                                <td><small>{{ $b['regla'] }}</small></td>
                                <td><code>{{ $b['ip'] }}</code></td>
                                <td>{{ $b['correo'] }}</td>
                                <td>{{ $b['telefono'] }}</td>
                                <td>{{ $b['nombre'] }}</td>
                                <td><code>{{ $b['curp'] }}</code></td>
                                <td>{{ $b['sede'] }}</td>
                                <td>{{ $b['tramite'] }}</td>
                                <td>{{ $b['pedia'] }}</td>
                                <td><small>{{ $b['navegador'] }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($ag['bloqueos']['total'] > count($ag['bloqueos']['lista']))
                <p class="et-sub mt-1 mb-0">Se muestran los {{ count($ag['bloqueos']['lista']) }} más recientes de {{ number_format($ag['bloqueos']['total']) }}.</p>
            @endif
        @endif

        <p class="et-sub mt-3 mb-0">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            Varias personas pueden compartir una IP (la red de una empresa, una escuela o un mismo proveedor de celular). Tómala como pista y confírmala con el correo, el teléfono y cuántas personas distintas (CURP) hay detrás.
        </p>
    @endif
</div>
