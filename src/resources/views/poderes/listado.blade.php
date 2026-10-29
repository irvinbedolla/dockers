@extends('layouts.app')
@section('title', $vigencia === 'vigentes' ? 'Poderes vigentes' : 'Poderes sin vigencia')

@php
    $hoy = now()->toDateString();
    $esSuperUsuario = auth()->user()->hasRole('Super Usuario');
    $tituloVigencia = $vigencia === 'vigentes' ? 'Poderes vigentes' : 'Poderes sin vigencia';
    $claseEstatus = fn (?string $e) => $e === 'Validado' ? 'tn-estatus--confirmada' : 'tn-estatus--pendiente';
    // La vigencia, el apartado y la búsqueda se filtran en el servidor (PoderController::listado);
    // cada liga conserva búsqueda y apartado.
    $urlCon = fn (array $cambios, ?string $otraVigencia = null) => route('poderes.listado', $otraVigencia ?? $vigencia).'?'.http_build_query(array_filter(
        array_merge(request()->only(['buscar', 'estatus']), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $apartadosVista = [
        'Pendiente' => ['Pendientes', 'bi-hourglass-split', 'pd-i-pendiente'],
        'Validado'  => ['Validados', 'bi-patch-check', 'pd-i-validado'],
    ];
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        /* Modal "Historial de cambios" */
        .hp-total { font-size: 12.5px; color: var(--tn-tenue); margin: 0 0 4px; }
        .hp-evento { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--tn-borde); }
        .hp-evento:last-child { border-bottom: 0; }
        .hp-punto { width: 11px; height: 11px; border-radius: 50%; margin-top: 5px; flex: 0 0 auto; }
        .hp-cuerpo { flex: 1 1 auto; min-width: 0; }
        .hp-cab { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 2px 12px; }
        .hp-accion { font-weight: 800; color: var(--tn-tinta); font-size: 14px; }
        .hp-fecha { font-size: 12.5px; color: var(--tn-suave); font-variant-numeric: tabular-nums; white-space: nowrap; }
        .hp-quien { font-size: 12.5px; color: var(--tn-tenue); }
        .hp-cambios { margin: 6px 0 0; padding-left: 18px; font-size: 13px; color: var(--tn-suave); }
        .hp-cambios li { margin-bottom: 2px; overflow-wrap: anywhere; }
        .rt-cifras { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; max-width: 640px; }
        .rt-cifra {
            display: flex; align-items: center; gap: 12px; padding: 14px 16px; text-decoration: none;
            background: #fff; border: 1px solid var(--tn-borde); border-radius: 12px;
            transition: border-color .15s, box-shadow .15s;
        }
        .rt-cifra:hover { border-color: var(--tn-verde-claro); }
        .rt-cifra.is-activa { border-color: var(--tn-verde); box-shadow: 0 0 0 3px rgba(73, 97, 99, .12); }
        .rt-cifra__icono { width: 40px; height: 40px; border-radius: 10px; display: grid; place-items: center; font-size: 19px; flex: 0 0 auto; }
        .rt-cifra__num { display: block; font-size: 22px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; font-variant-numeric: tabular-nums; }
        .rt-cifra__txt { font-size: 12.5px; color: var(--tn-suave); }
        .pd-i-pendiente { background: #EEF0F3; color: #5F6B7A; }
        .pd-i-validado { background: #E5F3EA; color: #1E7B3C; }
        @media (max-width: 640px) { .rt-cifras { grid-template-columns: 1fr; } }

        .rt-filtros { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; align-items: end; }
        @media (max-width: 640px) { .rt-filtros { grid-template-columns: 1fr; } }
        .rt-filtros label { display: block; font-size: 11.5px; font-weight: 600; color: var(--tn-tenue); margin-bottom: 4px; }
        .rt-buscar { position: relative; }
        .rt-buscar i { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--tn-tenue); }
        .rt-buscar input { padding-right: 36px; }

        .rt-atajos { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; }
        .rt-atajo {
            padding: 4px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff;
            color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none;
        }
        .rt-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .rt-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

        .rt-encabezado-dia { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 4px; }
        .rt-encabezado-dia h4 { margin: 0; font-size: 18px; font-weight: 700; color: var(--tn-tinta); }
        .rt-encabezado-dia span { font-size: 13px; color: var(--tn-tenue); }

        /* Encabezado oscuro de Todos los turnos. Con .tn delante para ganarle a
           .table:not(.table-sm) thead th de style.css. */
        .tn-tabla { margin: 0; }
        .tn .table.tn-tabla > :not(caption) > * > th,
        .tn .table.tn-tabla > :not(caption) > * > td { padding: 10px 12px; height: auto; }
        .tn .table.tn-tabla thead th {
            background: #354647; --bs-table-bg: #354647; color: #fff !important;
            font-size: 12px; font-weight: 600; letter-spacing: .03em; white-space: nowrap; border: 0;
        }
        .tn-tabla td { vertical-align: middle; font-size: 13.5px; color: var(--tn-tinta); }

        .rt-nue { font-weight: 700; color: var(--tn-verde); white-space: nowrap; }
        .rt-principal { display: block; font-weight: 700; color: var(--tn-tinta); line-height: 1.25; }
        .rt-secundario { display: block; font-size: 12.5px; color: var(--tn-tenue); overflow-wrap: anywhere; }
        .rt-vence { display: block; margin-top: 4px; font-size: 12px; color: var(--tn-tenue); white-space: nowrap; }
        .rt-vence--vencido { color: #B4530F; }

        .rt-acciones { display: flex; flex-direction: column; gap: 6px; align-items: stretch; min-width: 130px; }
        .tn-tabla .btn-sm { white-space: nowrap; font-weight: 600; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('poderes') }}" class="small text-decoration-none" style="color: var(--tn-tenue);">
                    <i class="bi bi-arrow-left"></i> Poderes
                </a>
                <h3 class="page__heading mb-0">{{ $tituloVigencia }}</h3>
            </div>
            @can('poderes_crear')
                <a class="btn tn-boton-dorado" href="{{ route('poder-crear') }}" target="_blank">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo poder
                </a>
            @endcan
        </div>

        <div class="section-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="rt-cifras">
                @foreach ($apartadosVista as $clave => [$texto, $icono, $tono])
                    {{-- Un segundo clic sobre el apartado activo lo quita. --}}
                    <a class="rt-cifra {{ $estatus === $clave ? 'is-activa' : '' }}"
                       href="{{ $urlCon(['estatus' => $estatus === $clave ? null : $clave]) }}"
                       @if ($estatus === $clave) aria-current="true" @endif>
                        <span class="rt-cifra__icono {{ $tono }}"><i class="bi {{ $icono }}"></i></span>
                        <span><span class="rt-cifra__num">{{ number_format($conteo[$clave]) }}</span><span class="rt-cifra__txt">{{ $texto }}</span></span>
                    </a>
                @endforeach
            </div>

            <div class="tn-tarjeta">
                {{-- GET: la URL guarda la vista y se puede compartir. --}}
                <form action="{{ url()->current() }}" method="GET">
                    @if ($estatus)
                        <input type="hidden" name="estatus" value="{{ $estatus }}">
                    @endif

                    <div class="rt-filtros">
                        <div>
                            <label for="pdBuscar">Buscar</label>
                            <div class="rt-buscar">
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <input type="search" id="pdBuscar" name="buscar" class="form-control" placeholder="Nombre / razón social, representante legal o folio" value="{{ request('buscar') }}">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                            @if (request('buscar') || $estatus)
                                <a href="{{ url()->current() }}" class="btn btn-outline-secondary" title="Quitar filtros">Limpiar</a>
                            @endif
                        </div>
                    </div>

                    <div class="rt-atajos">
                        <a class="rt-atajo {{ $vigencia === 'vigentes' ? 'is-activo' : '' }}" href="{{ $urlCon([], 'vigentes') }}">Vigentes</a>
                        <a class="rt-atajo {{ $vigencia === 'sin-vigencia' ? 'is-activo' : '' }}" href="{{ $urlCon([], 'sin-vigencia') }}">Sin vigencia</a>
                    </div>
                </form>
            </div>

            <div class="tn-tarjeta mt-3">
                <div class="rt-encabezado-dia" aria-live="polite">
                    <h4>{{ $estatus ? $apartadosVista[$estatus][0] : 'Todos los estatus' }}</h4>
                    <span>
                        {{ number_format($poderes->total()) }} {{ $poderes->total() === 1 ? 'poder' : 'poderes' }}@if(request('buscar')) que coinciden con "{{ request('buscar') }}"@endif
                    </span>
                </div>

                <div class="table-responsive mt-2">
                    <table class="table table-hover tn-tabla w-100">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Nombre / Razón Social</th>
                                <th>RFC</th>
                                <th>Representante Legal</th>
                                <th class="text-center">Estatus</th>
                                @can('poderes_ver_expediente')
                                    <th class="text-center">Expediente Digital</th>
                                @endcan
                                <th class="text-center">Acciones</th>
                                @can('poderes_borrar')
                                    <th class="text-center">Eliminar</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($poderes as $poder)
                                <tr>
                                    <td><span class="rt-nue">{{ $poder->idAbogado }}</span></td>
                                    <td>
                                        <span class="rt-principal">{{ $poder->nombre_patronal_combinado }}</span>
                                        @if ($poder->email_patronal)
                                            <span class="rt-secundario">{{ $poder->email_patronal }}</span>
                                        @endif
                                    </td>
                                    <td style="white-space: nowrap;">{{ $poder->rfc_patronal ?? 'N/A' }}</td>
                                    <td>{{ $poder->nombre_representante_combinado }}</td>
                                    <td class="text-center">
                                        <span class="tn-estatus {{ $claseEstatus($poder->estatus) }}">{{ $poder->estatus }}</span>
                                        @if (is_null($poder->fechaVigencia))
                                            <span class="rt-vence">Sin fecha de vencimiento</span>
                                        @elseif ($poder->fechaVigencia < $hoy)
                                            <span class="rt-vence rt-vence--vencido">Venció {{ \Carbon\Carbon::parse($poder->fechaVigencia)->format('d-m-Y') }}</span>
                                        @else
                                            <span class="rt-vence">Vence {{ \Carbon\Carbon::parse($poder->fechaVigencia)->format('d-m-Y') }}</span>
                                        @endif
                                    </td>
                                    @can('poderes_ver_expediente')
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-secondary btn-sm btn-ver-expediente"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalExpedienteDigital"
                                                data-abogado="{{ $poder->nombre_representante_combinado }}"
                                                data-tipo="{{ $poder->tipo }}"
                                                data-ine="{{ $poder->url_ine }}"
                                                data-anexo="{{ $poder->url_anexo }}"
                                                data-representacion="{{ $poder->url_representacion }}"
                                                data-cartapoder="{{ $poder->url_cartapoder }}"
                                                data-registro="{{ $poder->url_registro }}">
                                                <i class="bi bi-folder2-open me-1"></i> Ver expediente
                                            </button>
                                        </td>
                                    @endcan
                                    <td class="text-center">
                                        <div class="rt-acciones">
                                            @can('poderes_editar')
                                                <a class="btn btn-outline-success btn-sm" href="{{ route('poderes.edit', $poder->idAbogado) }}" onclick="editar_poder();">
                                                    <i class="bi bi-pencil me-1"></i> Editar
                                                </a>
                                            @endcan
                                            @can('poderes_ver_historial')
                                                @if ($esSuperUsuario)
                                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                                        data-bs-toggle="modal" data-bs-target="#modalHistorialPoder"
                                                        data-id="{{ $poder->idAbogado }}"
                                                        data-nombre="{{ $poder->nombre_patronal_combinado }}"
                                                        data-detalle="{{ route('poderes.history', $poder->idAbogado) }}">
                                                        <i class="bi bi-clock-history me-1"></i> Historial
                                                    </button>
                                                @endif
                                            @endcan
                                            @can('poderes_agregar_representante')
                                                <a href="#" class="btn tn-boton-dorado btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal1" data-id="{{ $poder->idAbogado }}" data-tipo="{{ $poder->tipo }}">
                                                    <i class="bi bi-person-plus me-1"></i> Agregar
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                    @can('poderes_borrar')
                                        <td class="text-center">
                                            @if ($esSuperUsuario)
                                                <form method="POST" action="{{ route('poderes.destroy', $poder->idAbogado) }}" class="form-eliminar-poder">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm" type="submit"><i class="bi bi-trash me-1"></i> Borrar</button>
                                                </form>
                                            @endif
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4" style="color: var(--tn-tenue);">No hay poderes con estos filtros.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($poderes->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $poderes->onEachSide(1)->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Modal Agregar Representante -->
    <div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form class="needs-validation" novalidate method="POST" action="{{ route('poderes.agregar_representante') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="idAbogado" id="idAbogado_input" value="">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Agregar Representante</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-center" style="color:#CEA845">Información del Representante Legal</h5>
                                <h5 class="text-center">Datos de identificación</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre_representante_pF">Nombre(s) del representante <span style="color:red;">(*)</span></label>
                                    <input type="text" maxlength="50" name="nombre_representante_pF" id="nombre_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primer_representante_pF">Primer apellido <span style="color:red;">(*)</span></label>
                                    <input type="text" maxlength="50" name="primer_representante_pF" id="primer_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
                                    <div class="invalid-feedback">El primer apellido es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="segundo_representante_pF">Segundo apellido <span style="color:red;">(*)</span></label>
                                    <input type="text" maxlength="50" name="segundo_representante_pF" id="segundo_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
                                    <div class="invalid-feedback">El segundo apellido es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="curp_representante_pF">CURP<span style="color:red;"> (*)</span></label>
                                    <input type="text" class="form-control" aria-label="CURP" name="curp_representante_pF" id="curp_representante_pF" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" required>
                                    <div class="invalid-feedback">La CURP es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sexo_representante_pF">Sexo <span style="color:red;">(*)</span></label>
                                    <select name="sexo_representante_pF" id="sexo_representante_pF" class="form-control" required>
                                        <option value="">Seleccione</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Prefiero no responder">Prefiero no responder</option>
                                    </select>
                                    <div class="invalid-feedback">El sexo es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <h5 class="text-center">Datos de contacto</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="correo_representante_pF">Correo electrónico <span style="color:red;">(*)</span></label>
                                    <input type="email" maxlength="50" class="form-control" name="correo_representante_pF" id="correo_representante_pF" required>
                                    <div class="invalid-feedback">El correo electrónico es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefono_representante_pF">Teléfono <span style="color:red;">(*)</span></label>
                                    <input type="number" class="form-control" name="telefono_representante_pF" id="telefono_representante_pF" maxlength="10" pattern="[0-9]+" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required>
                                    <div class="invalid-feedback">El teléfono es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <h5 class="text-center" style="color:#CEA845">Datos de la documentación que acredite la personería</h5>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_documento_pF">Tipo de documento <span style="color:red;">(*)</span></label>
                                    <select name="tipo_documento_pF" id="tipo_documento_pF" class="form-control" required>
                                        <option value="">Seleccione</option>
                                        <option value="Carta Poder">Carta Poder</option>
                                        <option value="Instrumento Notarial">Instrumento Notarial</option>
                                    </select>
                                    <div class="invalid-feedback">El campo es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_expedicion_pF">Fecha expedición <span style="color:red;">(*)</span></label>
                                    <input type="date" class="form-control" name="fecha_expedicion_pF" id="fecha_expedicion_pF" required>
                                    <div class="invalid-feedback">La fecha es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check mt-4">
                                    <input name="sin_fecha_vigencia_pF" type="checkbox" class="form-check-input" id="check_vigencia" autocomplete="off">
                                    <label class="form-check-label" for="check_vigencia">Sin fecha de vigencia</label>
                                </div>
                            </div>
                            <div class="col-md-3" id="fecha_vigencia_pF_container">
                                <div class="form-group">
                                    <label for="fecha_vigencia_pF">Fecha vigencia</label>
                                    <input type="date" class="form-control" name="fecha_vigencia_pF" id="fecha_vigencia_pF" min="{{ $hoy }}">
                                    <div class="invalid-feedback">La fecha es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="descripcion_pF">Descripción del documento que acredite la personería <span style="color:red;">(*)</span></label>
                                    <textarea class="form-control" name="descripcion_pF" id="descripcion_pF"
                                    placeholder="Ejemplo: Carta poder simple de fecha___, firmada ante dos testigos, suscrita a favor del compareciente por el (C., Lic., Ing., etc.,)_____, en cuanto ___ de la moral citada, personalidad que acredite en terminos de___ número(45 Cuarenta y Cinco), de fecha___, pasada ante la fe del(Lic., Mtro., etc.,)___, Notario Público Número ___, del Estado de ____, y cuyas facultades no han sido revocadas ni mofificadas a la fecha." required></textarea>
                                    <div class="invalid-feedback">La descripción es obligatoria.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_identificacion_pFCR">Identificación Oficial <span style="color:red;">(*)</span></label>
                                    <select id="tipo_identificacion_pFCR" name="tipo_identificacion_pFCR" class="form-control" required>
                                        <option value="">Seleccione el tipo de indentificación</option>
                                        <option value="Credencial de elector">Credencial de Elector</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                        <option value="Cédula profesional">Cédula Profesional</option>
                                        <option value="Licencia de conducir">Licencia de Conducir</option>
                                        <option value="Credencial de inapam">Credencial de INAPAM</option>
                                        <option value="Cartilla militar">Cartilla Militar</option>
                                        <option value="Documento migratorio">Documento Migratorio</option>
                                        <option value="Constancia de identidad">Constancia de Identidad</option>
                                        <option value="Otro">Otros</option>
                                    </select>
                                    <div class="invalid-feedback">Este campo identificación es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="num_identificacion_pFCR">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                    <input type="text" maxlength="20" name="num_identificacion_pFCR" id="num_identificacion_pFCR" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
                                    <div class="invalid-feedback">El campo núm. de identificación es obligatorio.</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <h5 class="text-center" style="color:#CEA845">Cargar Documentos</h5>
                            </div>

                            <div class="col-md-6" id="div_acta_constitutiva" style="display:none;">
                                <div class="form-group">
                                    <label for="documentoActa_Moral"><span style="color:red;">*</span>Acta Constitutiva</label>
                                    <input type="file" name="documentoActa_Moral" id="documentoActa_Moral" class="form-control" accept=".pdf">
                                    <div class="invalid-feedback">El acta constitutiva es obligatoria para personas morales.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="documentoRepresentacion_pF"><span style="color:red;">*</span>Identificación del Representante Legal</label>
                                    <input type="file" name="documentoRepresentacion_pF" id="documentoRepresentacion_pF" class="form-control" accept=".pdf" required>
                                    <div class="invalid-feedback">El documento de representación es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="documentoPoder_pF"><span style="color:red;">*</span>Documento que acredite la personería</label>
                                    <input type="file" name="documentoPoder_pF" id="documentoPoder_pF" class="form-control" accept=".pdf" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="documentoAnexo_pF">Anexo (Documentos Complementarios)</label>
                                    <input type="file" name="documentoAnexo_pF" id="documentoAnexo_pF" class="form-control" accept=".pdf">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn tn-boton-dorado">Guardar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Expediente Digital -->
    <div class="modal fade" id="modalExpedienteDigital" tabindex="-1" aria-labelledby="modalExpedienteLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #354647; color: white;">
                    <h5 class="modal-title" id="modalExpedienteLabel">Documentos del Representante</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3"><strong>Representante:</strong> <span id="expediente_nombre_abogado" class="text-muted"></span></p>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-envelope-paper text-primary"></i> Carta Poder</span>
                            <div id="wrapper_cartapoder"></div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-paperclip text-primary"></i> Anexo</span>
                            <div id="wrapper_anexo"></div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" id="li_registro">
                            <span><i class="bi bi-file-check text-success"></i> Constancia de Registro Oficial</span>
                            <div id="wrapper_registro"></div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-file-earmark-text text-primary"></i> Documento de Representación</span>
                            <div id="wrapper_representacion"></div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-card-heading text-primary"></i> <span id="etq_ine">Identificación Oficial</span></span>
                            <div id="wrapper_ine"></div>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Historial de cambios del poder: resumen de cada movimiento (PoderController::historialResumen). --}}
    <div class="modal fade" id="modalHistorialPoder" tabindex="-1" aria-labelledby="hpTitulo" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #354647; color: white;">
                    <div>
                        <h5 class="modal-title" id="hpTitulo"><i class="bi bi-clock-history me-1"></i> Historial de cambios</h5>
                        <small id="hpSub" style="color: #D6E0E0;"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="hpCuerpo"></div>
                <div class="modal-footer">
                    <a href="#" id="hpDetalle" class="btn btn-outline-secondary btn-sm me-auto" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i> Ver cada versión completa</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('body_end')
<div id="nuevo_poder" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush

@section('scripts')
    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>
    <script src="{{ asset('assets/js/poderes/limite_archivos.js') }}"></script>
    <script>
        // Historial: al abrir el modal se pide el resumen de cambios del folio.
        (function () {
            var modal = document.getElementById('modalHistorialPoder');
            if (!modal) { return; }
            document.body.appendChild(modal); // igual que el expediente: evita que la página se quede gris
            var BASE = {!! json_encode(url('poderes/history/resumen')) !!};
            var cuerpo = document.getElementById('hpCuerpo');
            var sub = document.getElementById('hpSub');
            var detalle = document.getElementById('hpDetalle');
            var COLOR = { alta: '#496163', en_linea: '#829A9C', edicion: '#CEA845', validacion: '#1E7B3C', representante: '#5B7DB1', baja: '#B4532A' };
            var pedido = 0;

            function esc(t) {
                return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function pintar(d) {
                if (!d.eventos || !d.eventos.length) {
                    cuerpo.innerHTML = '<p class="text-center py-4 mb-0" style="color: var(--tn-tenue);">Este poder todavía no tiene movimientos registrados.</p>';
                    return;
                }
                cuerpo.innerHTML =
                    (d.borrado ? '<div class="alert alert-warning py-2">Este poder ya fue borrado; aquí queda lo que se le hizo.</div>' : '') +
                    '<p class="hp-total">' + d.eventos.length + (d.eventos.length === 1 ? ' movimiento' : ' movimientos') + ', del más reciente al más antiguo</p>' +
                    d.eventos.map(function (e) {
                        return '<div class="hp-evento">' +
                            '<span class="hp-punto" style="background:' + (COLOR[e.clave] || '#8A9899') + '"></span>' +
                            '<div class="hp-cuerpo">' +
                                '<div class="hp-cab"><span class="hp-accion">' + esc(e.accion) + '</span><span class="hp-fecha">' + esc(e.fecha) + '</span></div>' +
                                '<div class="hp-quien"><i class="bi bi-person"></i> ' + esc(e.usuario) + '</div>' +
                                '<ul class="hp-cambios">' + e.cambios.map(function (c) { return '<li>' + esc(c) + '</li>'; }).join('') + '</ul>' +
                            '</div>' +
                        '</div>';
                    }).join('');
            }

            modal.addEventListener('show.bs.modal', function (evento) {
                var boton = evento.relatedTarget;
                if (!boton) { return; }
                var id = boton.getAttribute('data-id');
                var este = ++pedido;
                sub.textContent = 'Folio ' + id + ' · ' + (boton.getAttribute('data-nombre') || '');
                detalle.href = boton.getAttribute('data-detalle') || '#';
                cuerpo.innerHTML = '<div class="text-center py-5" style="color: var(--tn-tenue);"><span class="spinner-border spinner-border-sm me-2"></span> Cargando el historial…</div>';
                fetch(BASE + '/' + encodeURIComponent(id), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
                    .then(function (d) { if (este === pedido) { pintar(d); } })
                    .catch(function () {
                        if (este === pedido) {
                            cuerpo.innerHTML = '<div class="alert alert-warning mb-0">No se pudo cargar el historial. Intenta de nuevo.</div>';
                        }
                    });
            });
        })();
    </script>
    <script>
        var exampleModal1 = document.getElementById('exampleModal1');
        if (exampleModal1) {
            exampleModal1.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                exampleModal1.querySelector('#idAbogado_input').value = button.getAttribute('data-id');

                // El acta constitutiva sólo se pide a personas morales.
                var divActa = document.getElementById('div_acta_constitutiva');
                var inputActa = document.getElementById('documentoActa_Moral');
                if (button.getAttribute('data-tipo') === 'Moral') {
                    divActa.style.display = 'block';
                    inputActa.setAttribute('required', 'required');
                } else {
                    divActa.style.display = 'none';
                    inputActa.removeAttribute('required');
                    inputActa.value = '';
                }

                var checkVigencia = document.getElementById('check_vigencia');
                var divFechaVigencia = document.getElementById('fecha_vigencia_pF_container');
                var inputFechaVigencia = document.getElementById('fecha_vigencia_pF');

                function toggleFechaVigencia() {
                    if (checkVigencia.checked) {
                        divFechaVigencia.style.display = 'none';
                        inputFechaVigencia.value = '';
                    } else {
                        divFechaVigencia.style.display = 'block';
                    }
                }

                checkVigencia.checked = false;
                toggleFechaVigencia();
                checkVigencia.onchange = toggleFechaVigencia;
            });

            const limiteArchivo = 10 * 1024 * 1024;
            exampleModal1.querySelectorAll('input[type="file"]').forEach(function (inputDocumento) {
                inputDocumento.addEventListener('change', function (e) {
                    const archivo = e.target.files[0];
                    if (archivo && archivo.size > limiteArchivo) {
                        alert("El archivo no puede pasar de 10 Megas");
                        this.value = "";
                    }
                });
            });
        }

        $(document).ready(function () {
            // Mover el modal al final del body para evitar conflictos de opacidad (Página en gris)
            if ($('#modalExpedienteDigital').length) {
                $('#modalExpedienteDigital').appendTo("body");
            }

            $(document).on('click', '.btn-ver-expediente', function (e) {
                e.preventDefault();

                // Los data-* ya vienen como URLs firmadas (PoderController::urlDocumentoPoder); aquí solo se envuelven en el link.
                function buildLink(url, fallbackText = 'S/D') {
                    if (!url || url === '') return '<span class="text-muted fw-semibold">' + fallbackText + '</span>';
                    if (url === 'S/A') return '<span class="text-muted fw-semibold">S/A</span>';
                    return '<a href="' + url + '" class="btn btn-xs btn-outline-danger py-0 px-2" target="_blank"><i class="bi bi-file-pdf"></i> PDF</a>';
                }

                var registro = $(this).data('registro');
                $('#expediente_nombre_abogado').text($(this).data('abogado'));
                $('#wrapper_ine').html(buildLink($(this).data('ine')));
                // En personas morales ese documento es el acta constitutiva.
                $('#etq_ine').text($(this).data('tipo') === 'Moral' ? 'Acta Constitutiva' : 'Identificación Oficial del Empleador');
                $('#wrapper_anexo').html(buildLink($(this).data('anexo')));
                $('#wrapper_representacion').html(buildLink($(this).data('representacion')));
                $('#wrapper_cartapoder').html(buildLink($(this).data('cartapoder')));

                if (registro && registro !== '') {
                    $('#li_registro').show();
                    $('#wrapper_registro').html('<a href="' + registro + '" class="btn btn-xs btn-success py-0 px-2" target="_blank"><i class="bi bi-printer"></i> Imprimir</a>');
                } else {
                    $('#li_registro').hide();
                }
            });

            $(document).on('submit', '.form-eliminar-poder', function (e) {
                e.preventDefault();
                var formulario = this;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Está seguro de eliminar este poder?',
                        text: "Esta acción no se puede deshacer y eliminará el registro del abogado del sistema.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, borrar',
                        cancelButtonText: 'Cancelar'
                    }).then(function (result) {
                        if (result.isConfirmed) formulario.submit();
                    });
                } else if (confirm("¿Está seguro de que desea eliminar este poder? Esta acción no se puede revertir.")) {
                    formulario.submit();
                }
            });
        });
    </script>
@endsection
