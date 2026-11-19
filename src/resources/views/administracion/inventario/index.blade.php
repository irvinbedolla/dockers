@extends('layouts.app')
@section('title', 'Inventario')

{{--
    Administración → Inventario (sólo Super Usuario). La tabla se corrige en el
    momento: un renglón sin check es editable y se guarda con el botón ✓ (o
    Enter); al ponerle el check se guarda lo pendiente, se verifica y queda
    bloqueado. Para volver a corregirlo hay que quitarle el check.
    Servidor: App\Http\Controllers\InventarioController.
--}}

@php
    use App\Models\InventarioDispositivo;
    $estados = InventarioDispositivo::ESTADOS;
    $total = $dispositivos->count();
    $cuentas = [
        'todos'       => $total,
        'pendientes'  => $dispositivos->where('verificado', false)->count(),
        'verificados' => $dispositivos->where('verificado', true)->count(),
        'revisar'     => $dispositivos->where('revisar', true)->count(),
        'malos'       => $dispositivos->where('estado_uso', 'malo')->count(),
    ];
    $filtros = [
        'todos'       => 'Todos',
        'pendientes'  => 'Pendientes',
        'verificados' => 'Verificados',
        'revisar'     => 'Para revisión',
        'malos'       => 'En mal estado',
    ];
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .inv-barra { display: flex; flex-wrap: wrap; gap: 12px 18px; align-items: center; justify-content: space-between; }
        .inv-progreso { min-width: 220px; flex: 1 1 260px; max-width: 420px; }
        .inv-progreso-texto { font-size: 13px; color: var(--tn-suave); margin-bottom: 5px; }
        .inv-progreso-texto strong { color: var(--tn-tinta); }
        .inv-progreso .progress { height: 8px; background: var(--tn-fondo); }
        .inv-progreso .progress-bar { background: var(--tn-verde); }

        .inv-filtros { display: flex; flex-wrap: wrap; gap: 6px; }
        .inv-filtro {
            padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde);
            background: #fff; color: var(--tn-suave); font-size: 12.5px; font-weight: 600;
        }
        .inv-filtro:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .inv-filtro.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
        .inv-filtro .inv-cuenta { opacity: .75; font-variant-numeric: tabular-nums; margin-left: 3px; }
        .inv-buscar { max-width: 280px; }

        .inv-tabla-caja { margin-top: 14px; padding: 0; overflow: hidden; }
        .inv-scroll { overflow-x: auto; max-height: calc(100vh - 260px); }
        .inv-tabla { margin: 0; font-size: 13px; min-width: 1680px; }
        .inv-tabla thead th {
            position: sticky; top: 0; z-index: 2; background: var(--tn-fondo); color: var(--tn-suave);
            font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
            white-space: nowrap; border-bottom: 1px solid var(--tn-borde); padding: 10px 6px;
        }
        .inv-tabla td { padding: 6px; vertical-align: top; border-color: var(--tn-borde); }
        .inv-tabla .inv-col-check { position: sticky; left: 0; z-index: 1; background: #fff; text-align: center; width: 44px; }
        .inv-tabla thead .inv-col-check { z-index: 3; background: var(--tn-fondo); }
        .inv-col-check .form-check-input { width: 1.3em; height: 1.3em; margin: 6px 0 0; cursor: pointer; }
        .inv-col-num { color: var(--tn-tenue); font-variant-numeric: tabular-nums; padding-top: 12px !important; width: 40px; }

        .inv-tabla .form-control, .inv-tabla .form-select { font-size: 13px; padding: 4px 7px; min-height: 31px; }
        .inv-tabla textarea.form-control { resize: vertical; field-sizing: content; min-height: 31px; max-height: 140px; }
        .inv-w-s  { min-width: 110px; }
        .inv-w-m  { min-width: 150px; }
        .inv-w-l  { min-width: 200px; }

        /* Renglón verificado: los campos se ven como texto, no como formulario. */
        .inv-fila.is-verificado td { background: #F3F8F4; }
        .inv-fila.is-verificado .inv-col-check { background: #F3F8F4; }
        .inv-fila.is-verificado .form-control:disabled,
        .inv-fila.is-verificado .form-select:disabled {
            background: transparent; border-color: transparent; color: var(--tn-tinta); opacity: 1;
            -webkit-appearance: none; appearance: none; padding-left: 0;
        }
        /* Con cambios sin guardar. */
        .inv-fila.is-sucio td { background: #FFF9E6; }
        .inv-fila.is-sucio .inv-col-check { background: #FFF9E6; }
        .inv-in.is-cambiado { border-color: #CEA845; box-shadow: 0 0 0 .15rem rgba(206, 168, 69, .2); }
        /* Marcado para revisión: franja a la izquierda. */
        .inv-fila.is-revisar .inv-col-check { box-shadow: inset 4px 0 0 #D9822B; }
        .inv-fila.is-guardando { opacity: .6; pointer-events: none; }
        .inv-fila.is-nuevo td { animation: inv-nuevo 2.4s ease-out; }
        @keyframes inv-nuevo { from { background: #DDEFE2; } }

        .inv-estado-malo { color: #A3162E; font-weight: 600; }
        .inv-estado-regular { color: #8A6A12; font-weight: 600; }
        .inv-razon { margin-top: 4px; }
        .inv-razon[hidden] { display: none; }

        .inv-acciones { white-space: nowrap; text-align: right; width: 1%; }
        .inv-acciones .btn { padding: 3px 8px; }
        .inv-guardar-grupo { display: none; }
        .inv-fila.is-sucio .inv-guardar-grupo { display: inline-flex; gap: 4px; }
        .inv-btn-bandera.is-activa { color: #D9822B; }
        .inv-meta { display: block; font-size: 11px; color: var(--tn-tenue); white-space: normal; margin-top: 3px; text-align: right; max-width: 190px; margin-left: auto; }
        .inv-meta--revisar { color: #B4530F; }

        .inv-vacio { text-align: center; color: var(--tn-tenue); padding: 30px !important; }

        /* Historial */
        .inv-mov { border-left: 3px solid var(--tn-borde); padding: 6px 0 10px 12px; margin-bottom: 4px; }
        .inv-mov--alta { border-left-color: #1E7B3C; }
        .inv-mov--edicion { border-left-color: #CEA845; }
        .inv-mov--verificacion { border-left-color: #496163; }
        .inv-mov--revision { border-left-color: #D9822B; }
        .inv-mov-cabeza { font-size: 13px; color: var(--tn-tinta); }
        .inv-mov-cabeza small { color: var(--tn-tenue); }
        .inv-mov ul { margin: 4px 0 0; padding-left: 18px; font-size: 12.5px; color: var(--tn-suave); }
        .inv-antes { color: #A3162E; text-decoration: line-through; }
        .inv-despues { color: #1E7B3C; }

        /* Avisos */
        .inv-avisos { position: fixed; right: 18px; bottom: 18px; z-index: 2000; display: flex; flex-direction: column; gap: 8px; }
        .inv-aviso {
            padding: 10px 14px; border-radius: 10px; font-size: 13px; color: #fff; background: var(--tn-verde);
            box-shadow: 0 6px 18px rgba(0, 0, 0, .15); max-width: 360px;
        }
        .inv-aviso--error { background: #A3162E; }

        @media (prefers-reduced-motion: reduce) { .inv-fila.is-nuevo td { animation: none; } }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('configuracion') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Administración
                </a>
                <h3 class="page__heading mb-0">Inventario</h3>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-light js-historial" data-titulo="Historial del inventario">
                    <i class="bi bi-clock-history"></i> Historial
                </button>
                <a href="{{ route('inventario.exportar') }}" class="btn btn-light">
                    <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inv-modal-nuevo">
                    <i class="bi bi-plus-lg"></i> Nuevo dispositivo
                </button>
            </div>
        </div>

        <div class="section-body">
            <div class="tn-tarjeta inv-barra">
                <div class="inv-progreso">
                    <div class="inv-progreso-texto">
                        <strong id="inv-verificados">{{ $cuentas['verificados'] }}</strong> de <strong id="inv-total">{{ $total }}</strong> verificados
                    </div>
                    <div class="progress" role="progressbar" aria-label="Avance de la verificación"
                         aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $total ? round($cuentas['verificados'] * 100 / $total) : 0 }}">
                        <div class="progress-bar" id="inv-barra" style="width: {{ $total ? $cuentas['verificados'] * 100 / $total : 0 }}%"></div>
                    </div>
                </div>
                <div class="inv-filtros" role="group" aria-label="Filtrar">
                    @foreach ($filtros as $clave => $texto)
                        <button type="button" class="inv-filtro {{ $clave === 'todos' ? 'is-activo' : '' }}" data-filtro="{{ $clave }}" aria-pressed="{{ $clave === 'todos' ? 'true' : 'false' }}">
                            {{ $texto }}<span class="inv-cuenta" data-cuenta="{{ $clave }}">{{ $cuentas[$clave] }}</span>
                        </button>
                    @endforeach
                </div>
                <input type="search" class="form-control inv-buscar" id="inv-buscar" placeholder="Buscar marca, serie, resguardante…" aria-label="Buscar en el inventario">
            </div>

            <div class="tn-tarjeta inv-tabla-caja">
                <div class="inv-scroll">
                    <table class="table inv-tabla" id="inv-tabla">
                        <thead>
                            <tr>
                                <th class="inv-col-check" title="Verificado"><i class="bi bi-check2-square"></i></th>
                                <th>#</th>
                                <th>Descripción</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>No. de serie</th>
                                <th>Estado de uso</th>
                                <th>Ubicación física</th>
                                <th>Resguardante</th>
                                <th>Puesto</th>
                                <th>Características</th>
                                <th>Observaciones</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dispositivos as $d)
                                @php $bloqueado = $d->verificado; $fila = $d->paraTabla(); @endphp
                                <tr class="inv-fila {{ $bloqueado ? 'is-verificado' : '' }} {{ $d->revisar ? 'is-revisar' : '' }}"
                                    id="inv-{{ $d->id }}"
                                    data-orig="{{ json_encode($fila) }}"
                                    data-url-actualizar="{{ route('inventario.actualizar', $d->id) }}"
                                    data-url-verificar="{{ route('inventario.verificar', $d->id) }}"
                                    data-url-revision="{{ route('inventario.revision', $d->id) }}">
                                    <td class="inv-col-check">
                                        <input type="checkbox" class="form-check-input js-verificar" @checked($bloqueado)
                                               aria-label="Verificado: {{ $d->descripcion }} {{ $d->marca }} {{ $d->modelo }}"
                                               title="{{ $bloqueado ? $fila['verificado_texto'] : 'Marcar como verificado' }}">
                                    </td>
                                    <td class="inv-col-num">{{ $d->id }}</td>
                                    <td><input type="text" class="form-control inv-in inv-w-m" data-campo="descripcion" value="{{ $d->descripcion }}" list="inv-descripciones" maxlength="120" @disabled($bloqueado) aria-label="Descripción" placeholder="¿Qué es?"></td>
                                    <td><input type="text" class="form-control inv-in inv-w-s" data-campo="marca" value="{{ $d->marca }}" maxlength="80" @disabled($bloqueado) aria-label="Marca"></td>
                                    <td><input type="text" class="form-control inv-in inv-w-m" data-campo="modelo" value="{{ $d->modelo }}" maxlength="120" @disabled($bloqueado) aria-label="Modelo"></td>
                                    <td><input type="text" class="form-control inv-in inv-w-m" data-campo="numero_serie" value="{{ $d->numero_serie }}" maxlength="120" @disabled($bloqueado) aria-label="Número de serie" placeholder="Sin serie"></td>
                                    <td>
                                        <select class="form-select inv-in inv-w-s js-estado inv-estado-{{ $d->estado_uso }}" data-campo="estado_uso" @disabled($bloqueado) aria-label="Estado de uso">
                                            @foreach ($estados as $valor => $texto)
                                                <option value="{{ $valor }}" @selected($d->estado_uso === $valor)>{{ $texto }}</option>
                                            @endforeach
                                        </select>
                                        <textarea class="form-control inv-in inv-razon inv-w-s" data-campo="razon_malo" rows="1" maxlength="1000"
                                                  placeholder="¿Por qué está en mal estado?" aria-label="Razón del mal estado"
                                                  @disabled($bloqueado) @if ($d->estado_uso !== 'malo') hidden @endif>{{ $d->razon_malo }}</textarea>
                                    </td>
                                    <td><input type="text" class="form-control inv-in inv-w-m" data-campo="ubicacion" value="{{ $d->ubicacion }}" list="inv-ubicaciones" maxlength="150" @disabled($bloqueado) aria-label="Ubicación física"></td>
                                    <td>
                                        <input type="text" class="form-control inv-in inv-w-l js-resguardante" data-campo="resguardante_nombre" value="{{ $d->resguardante_nombre }}"
                                               list="inv-usuarios" maxlength="191" @disabled($bloqueado) aria-label="Resguardante" placeholder="Elegir usuario o escribir">
                                        <input type="hidden" class="inv-in" data-campo="resguardante_id" value="{{ $d->resguardante_id }}">
                                    </td>
                                    <td><input type="text" class="form-control inv-in inv-w-m js-puesto" data-campo="resguardante_puesto" value="{{ $d->resguardante_puesto }}" maxlength="150" @disabled($bloqueado) aria-label="Puesto del resguardante"></td>
                                    <td><textarea class="form-control inv-in inv-w-l" data-campo="caracteristicas" rows="1" maxlength="2000" @disabled($bloqueado) aria-label="Características específicas">{{ $d->caracteristicas }}</textarea></td>
                                    <td><textarea class="form-control inv-in inv-w-l" data-campo="observaciones" rows="1" maxlength="2000" @disabled($bloqueado) aria-label="Observaciones adicionales">{{ $d->observaciones }}</textarea></td>
                                    <td class="inv-acciones">
                                        <span class="inv-guardar-grupo">
                                            <button type="button" class="btn btn-sm btn-success js-guardar" title="Guardar cambios (Enter)"><i class="bi bi-check-lg"></i></button>
                                            <button type="button" class="btn btn-sm btn-light js-descartar" title="Descartar cambios (Esc)"><i class="bi bi-arrow-counterclockwise"></i></button>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-light inv-btn-bandera js-revision {{ $d->revisar ? 'is-activa' : '' }}"
                                                title="{{ $d->revisar ? 'Marcado para revisión' : 'Marcar para revisión' }}">
                                            <i class="bi {{ $d->revisar ? 'bi-flag-fill' : 'bi-flag' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light js-historial" title="Historial de este dispositivo"
                                                data-dispositivo="{{ $d->id }}" data-titulo="Historial · {{ $d->descripcion }} {{ $d->marca }} {{ $d->modelo }}">
                                            <i class="bi bi-clock-history"></i>
                                        </button>
                                        <span class="inv-meta inv-meta--revisar js-nota-revision" @if (!$d->revisar || !$d->revisar_nota) hidden @endif>{{ $d->revisar_nota }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="13" class="inv-vacio">Aún no hay dispositivos. Agrega el primero con «Nuevo dispositivo».</td></tr>
                            @endforelse
                            <tr id="inv-sin-resultados" hidden><td colspan="13" class="inv-vacio">Ningún dispositivo coincide con el filtro.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <datalist id="inv-usuarios">
            @foreach ($usuarios as $u)
                <option value="{{ mb_strtoupper($u->name) }}" data-id="{{ $u->id }}">{{ $u->delegacion }}</option>
            @endforeach
        </datalist>
        <datalist id="inv-descripciones">
            @foreach ($descripciones as $descripcion)
                <option value="{{ $descripcion }}"></option>
            @endforeach
        </datalist>
        <datalist id="inv-ubicaciones">
            @foreach ($ubicaciones as $ubicacion)
                <option value="{{ $ubicacion }}"></option>
            @endforeach
        </datalist>
    </section>

    {{-- Nuevo dispositivo --}}
    <div class="modal fade" id="inv-modal-nuevo" tabindex="-1" aria-labelledby="inv-nuevo-titulo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content" id="inv-form-nuevo" action="{{ route('inventario.guardar') }}" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="inv-nuevo-titulo">Nuevo dispositivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="n-descripcion">Descripción * <span class="text-muted fw-normal">(qué es: computadora, laptop, router…)</span></label>
                            <input type="text" class="form-control" id="n-descripcion" name="descripcion" list="inv-descripciones" maxlength="120" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="n-marca">Marca *</label>
                            <input type="text" class="form-control" id="n-marca" name="marca" maxlength="80" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="n-modelo">Modelo *</label>
                            <input type="text" class="form-control" id="n-modelo" name="modelo" maxlength="120" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="n-serie">Número de serie</label>
                            <input type="text" class="form-control" id="n-serie" name="numero_serie" maxlength="120">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="n-estado">Estado de uso *</label>
                            <select class="form-select js-estado" id="n-estado" name="estado_uso" required>
                                @foreach ($estados as $valor => $texto)
                                    <option value="{{ $valor }}">{{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="n-ubicacion">Ubicación física *</label>
                            <input type="text" class="form-control" id="n-ubicacion" name="ubicacion" list="inv-ubicaciones" maxlength="150" required>
                        </div>
                        <div class="col-12 inv-razon" id="n-razon-caja" hidden>
                            <label class="form-label" for="n-razon">¿Por qué está en mal estado? *</label>
                            <textarea class="form-control" id="n-razon" name="razon_malo" rows="2" maxlength="1000"></textarea>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="n-resguardante">Resguardante</label>
                            <input type="text" class="form-control js-resguardante" id="n-resguardante" name="resguardante_nombre" list="inv-usuarios" maxlength="191" placeholder="Elegir usuario o escribir">
                            <input type="hidden" name="resguardante_id">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="n-puesto">Puesto del resguardante</label>
                            <input type="text" class="form-control js-puesto" id="n-puesto" name="resguardante_puesto" maxlength="150">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="n-caracteristicas">Características específicas</label>
                            <textarea class="form-control" id="n-caracteristicas" name="caracteristicas" rows="2" maxlength="2000" placeholder="Opcional · p. ej. 16 GB RAM, SSD 512 GB"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="n-observaciones">Observaciones adicionales</label>
                            <textarea class="form-control" id="n-observaciones" name="observaciones" rows="2" maxlength="2000"></textarea>
                        </div>
                    </div>
                    <div class="alert alert-danger mt-3 mb-0" id="inv-nuevo-error" hidden></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bandera de revisión --}}
    <div class="modal fade" id="inv-modal-revision" tabindex="-1" aria-labelledby="inv-revision-titulo" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inv-revision-titulo"><i class="bi bi-flag-fill" style="color:#D9822B"></i> Marcar para revisión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2" id="inv-revision-equipo"></p>
                    <label class="form-label" for="inv-revision-nota">¿Qué hay que revisar? (opcional)</label>
                    <textarea class="form-control" id="inv-revision-nota" rows="3" maxlength="500"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-success me-auto" id="inv-revision-quitar" hidden><i class="bi bi-flag"></i> Ya se revisó, quitar bandera</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="inv-revision-guardar">Marcar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quitar el check --}}
    <div class="modal fade" id="inv-modal-desverificar" tabindex="-1" aria-labelledby="inv-desverificar-titulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inv-desverificar-titulo">¿Quitar la verificación?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1" id="inv-desverificar-equipo"></p>
                    <p class="small text-muted mb-0" id="inv-desverificar-quien"></p>
                    <p class="small mb-0 mt-2">El renglón volverá a ser editable y el cambio quedará en el historial.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="inv-desverificar-si">Quitar check</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Historial --}}
    <div class="modal fade" id="inv-modal-historial" tabindex="-1" aria-labelledby="inv-historial-titulo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inv-historial-titulo">Historial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="inv-historial-cuerpo"></div>
            </div>
        </div>
    </div>

    <div class="inv-avisos" id="inv-avisos" aria-live="polite"></div>
@endsection

@section('scripts')
    <script>
    (function () {
        'use strict';

        var CAMPOS = ['descripcion', 'marca', 'modelo', 'numero_serie', 'estado_uso', 'razon_malo', 'ubicacion',
            'resguardante_id', 'resguardante_nombre', 'resguardante_puesto', 'caracteristicas', 'observaciones'];
        var URL_HISTORIAL = @json(route('inventario.historial'));
        var PUESTOS = @json($puestos);
        var TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var tabla = document.getElementById('inv-tabla');
        var filtroActual = 'todos';

        // Nombre → id de los usuarios del datalist.
        var USUARIOS = {};
        document.querySelectorAll('#inv-usuarios option').forEach(function (o) {
            if (!(o.value in USUARIOS)) USUARIOS[o.value] = o.dataset.id;
        });

        /* ---------- utilidades ---------- */

        /* Todo lo que se escribe pasa a mayúsculas al momento, sin mover el cursor. El servidor lo repite al guardar. */
        function aMayusculas(e) {
            var t = e.target;
            if (!t.matches('input[type="text"], textarea')) return;
            var mayus = t.value.toUpperCase();
            if (t.value === mayus) return;
            var ini = t.selectionStart, fin = t.selectionEnd;
            t.value = mayus;
            try { t.setSelectionRange(ini, fin); } catch (_) {}
        }
        // En captura, para que corra antes de revisar si el renglón cambió.
        tabla.addEventListener('input', aMayusculas, true);
        document.getElementById('inv-form-nuevo').addEventListener('input', aMayusculas, true);

        function pedir(url, metodo, cuerpo) {
            return fetch(url, {
                method: metodo,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': TOKEN
                },
                body: cuerpo ? JSON.stringify(cuerpo) : undefined
            }).then(function (r) {
                return r.json().catch(function () { return {}; }).then(function (j) {
                    if (!r.ok) {
                        var errores = j.errors || {};
                        var primero = Object.keys(errores).length ? errores[Object.keys(errores)[0]][0] : null;
                        var e = new Error(primero || j.message || 'No se pudo guardar; intenta de nuevo.');
                        e.errores = errores;
                        throw e;
                    }
                    return j;
                });
            });
        }

        function avisar(texto, error) {
            var div = document.createElement('div');
            div.className = 'inv-aviso' + (error ? ' inv-aviso--error' : '');
            div.setAttribute('role', error ? 'alert' : 'status');
            div.textContent = texto;
            document.getElementById('inv-avisos').appendChild(div);
            setTimeout(function () { div.remove(); }, error ? 6000 : 3000);
        }

        function texto(v) { return v === null || v === undefined ? '' : String(v); }

        function campo(tr, nombre) { return tr.querySelector('[data-campo="' + nombre + '"]'); }

        function orig(tr) { return JSON.parse(tr.dataset.orig); }

        function nombreEquipo(tr) {
            var o = orig(tr);
            return [o.descripcion, o.marca, o.modelo, o.numero_serie ? '· ' + o.numero_serie : ''].join(' ').trim();
        }

        function valores(tr) {
            var datos = {};
            CAMPOS.forEach(function (c) { datos[c] = campo(tr, c).value.trim(); });
            return datos;
        }

        /* Resguardante: si el nombre coincide con un usuario se guarda su id y se propone su último puesto. */
        function enlazarResguardante(nombreInput, idInput, puestoInput) {
            var id = USUARIOS[nombreInput.value.trim().toUpperCase()] || '';
            idInput.value = id;
            if (id && puestoInput && !puestoInput.value.trim() && PUESTOS[id]) {
                puestoInput.value = PUESTOS[id];
                puestoInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        function pintarEstado(select) {
            select.classList.remove('inv-estado-bueno', 'inv-estado-regular', 'inv-estado-malo');
            select.classList.add('inv-estado-' + select.value);
        }

        /* ---------- renglones ---------- */

        function revisarSucio(tr) {
            var o = orig(tr);
            var sucio = false;
            CAMPOS.forEach(function (c) {
                var el = campo(tr, c);
                var cambiado = el.value.trim() !== texto(o[c]).trim();
                if (el.type !== 'hidden') el.classList.toggle('is-cambiado', cambiado);
                if (cambiado) sucio = true;
            });
            tr.classList.toggle('is-sucio', sucio);
            return sucio;
        }

        function limpiarErrores(tr) {
            tr.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); el.removeAttribute('title'); });
        }

        function marcarErrores(tr, errores) {
            Object.keys(errores || {}).forEach(function (c) {
                var el = campo(tr, c);
                if (!el) return;
                if (c === 'razon_malo') el.hidden = false;
                el.classList.add('is-invalid');
                el.title = errores[c][0];
            });
            var primero = tr.querySelector('.is-invalid');
            if (primero) primero.focus();
        }

        /* Deja el renglón como lo dice el servidor. */
        function pintarFila(tr, d) {
            tr.dataset.orig = JSON.stringify(d);
            CAMPOS.forEach(function (c) {
                var el = campo(tr, c);
                el.value = texto(d[c]);
                el.disabled = d.verificado && el.type !== 'hidden';
                el.classList.remove('is-cambiado');
            });
            var razon = campo(tr, 'razon_malo');
            razon.hidden = d.estado_uso !== 'malo';
            pintarEstado(campo(tr, 'estado_uso'));

            var check = tr.querySelector('.js-verificar');
            check.checked = !!d.verificado;
            check.title = d.verificado ? d.verificado_texto : 'Marcar como verificado';
            tr.classList.toggle('is-verificado', !!d.verificado);
            tr.classList.toggle('is-revisar', !!d.revisar);
            tr.classList.remove('is-sucio');

            var bandera = tr.querySelector('.js-revision');
            bandera.classList.toggle('is-activa', !!d.revisar);
            bandera.title = d.revisar ? 'Marcado para revisión' : 'Marcar para revisión';
            bandera.querySelector('i').className = 'bi ' + (d.revisar ? 'bi-flag-fill' : 'bi-flag');
            var nota = tr.querySelector('.js-nota-revision');
            nota.textContent = d.revisar_nota || '';
            nota.hidden = !(d.revisar && d.revisar_nota);

            var hist = tr.querySelector('.js-historial');
            hist.dataset.titulo = 'Historial · ' + texto(d.descripcion) + ' ' + texto(d.marca) + ' ' + texto(d.modelo);

            limpiarErrores(tr);
            contar();
            filtrar();
        }

        function guardarFila(tr) {
            if (!revisarSucio(tr)) return Promise.resolve();
            limpiarErrores(tr);
            tr.classList.add('is-guardando');
            return pedir(tr.dataset.urlActualizar, 'PUT', valores(tr))
                .then(function (j) { pintarFila(tr, j.dispositivo); avisar(j.message); })
                .catch(function (e) { marcarErrores(tr, e.errores); avisar(e.message, true); e.avisado = true; throw e; })
                .finally(function () { tr.classList.remove('is-guardando'); });
        }

        function descartarFila(tr) {
            pintarFila(tr, orig(tr));
        }

        /* ---------- conteos y filtros ---------- */

        function filas() { return Array.prototype.slice.call(tabla.querySelectorAll('tr.inv-fila')); }

        function contar() {
            var f = filas();
            var c = {
                todos: f.length,
                pendientes: f.filter(function (tr) { return !tr.classList.contains('is-verificado'); }).length,
                verificados: f.filter(function (tr) { return tr.classList.contains('is-verificado'); }).length,
                revisar: f.filter(function (tr) { return tr.classList.contains('is-revisar'); }).length,
                malos: f.filter(function (tr) { return orig(tr).estado_uso === 'malo'; }).length
            };
            Object.keys(c).forEach(function (k) {
                var el = document.querySelector('[data-cuenta="' + k + '"]');
                if (el) el.textContent = c[k];
            });
            document.getElementById('inv-verificados').textContent = c.verificados;
            document.getElementById('inv-total').textContent = c.todos;
            var pct = c.todos ? Math.round(c.verificados * 100 / c.todos) : 0;
            document.getElementById('inv-barra').style.width = pct + '%';
            document.getElementById('inv-barra').parentNode.setAttribute('aria-valuenow', pct);
        }

        function filtrar() {
            var q = document.getElementById('inv-buscar').value.trim().toLowerCase();
            var visibles = 0;
            filas().forEach(function (tr) {
                // Una fila con cambios sin guardar nunca se esconde.
                var ok = tr.classList.contains('is-sucio');
                if (!ok) {
                    var o = orig(tr);
                    ok = filtroActual === 'todos'
                        || (filtroActual === 'pendientes' && !o.verificado)
                        || (filtroActual === 'verificados' && o.verificado)
                        || (filtroActual === 'revisar' && o.revisar)
                        || (filtroActual === 'malos' && o.estado_uso === 'malo');
                    if (ok && q) {
                        var hay = [o.id, o.descripcion, o.marca, o.modelo, o.numero_serie, o.ubicacion, o.resguardante_nombre,
                            o.resguardante_puesto, o.caracteristicas, o.observaciones].map(texto).join(' ').toLowerCase();
                        ok = hay.indexOf(q) !== -1;
                    }
                }
                tr.hidden = !ok;
                if (ok) visibles++;
            });
            document.getElementById('inv-sin-resultados').hidden = visibles > 0 || filas().length === 0;
        }

        document.querySelectorAll('.inv-filtro').forEach(function (b) {
            b.addEventListener('click', function () {
                filtroActual = b.dataset.filtro;
                document.querySelectorAll('.inv-filtro').forEach(function (x) {
                    x.classList.toggle('is-activo', x === b);
                    x.setAttribute('aria-pressed', x === b ? 'true' : 'false');
                });
                filtrar();
            });
        });
        document.getElementById('inv-buscar').addEventListener('input', filtrar);

        /* ---------- edición en la tabla ---------- */

        tabla.addEventListener('input', function (e) {
            var tr = e.target.closest('tr.inv-fila');
            if (!tr || !e.target.classList.contains('inv-in')) return;
            if (e.target.classList.contains('js-resguardante')) {
                enlazarResguardante(e.target, campo(tr, 'resguardante_id'), campo(tr, 'resguardante_puesto'));
            }
            e.target.classList.remove('is-invalid');
            revisarSucio(tr);
        });

        tabla.addEventListener('change', function (e) {
            var tr = e.target.closest('tr.inv-fila');
            if (!tr) return;

            if (e.target.classList.contains('js-estado')) {
                pintarEstado(e.target);
                var razon = campo(tr, 'razon_malo');
                razon.hidden = e.target.value !== 'malo';
                if (e.target.value === 'malo' && !razon.value.trim()) razon.focus();
                revisarSucio(tr);
            }

            if (e.target.classList.contains('js-verificar')) {
                var check = e.target;
                if (check.checked) {
                    // El check dice "así está bien": primero se guarda lo pendiente.
                    check.checked = false;
                    guardarFila(tr)
                        .then(function () {
                            tr.classList.add('is-guardando');
                            return pedir(tr.dataset.urlVerificar, 'PATCH', { verificado: true });
                        })
                        .then(function (j) { pintarFila(tr, j.dispositivo); avisar(j.message); })
                        .catch(function (err) { if (!err.avisado) avisar(err.message, true); })
                        .finally(function () { tr.classList.remove('is-guardando'); });
                } else {
                    check.checked = true;
                    abrirDesverificar(tr);
                }
            }
        });

        tabla.addEventListener('keydown', function (e) {
            var tr = e.target.closest('tr.inv-fila');
            if (!tr || !e.target.classList.contains('inv-in')) return;
            if (e.key === 'Enter' && e.target.tagName === 'INPUT') { e.preventDefault(); guardarFila(tr).catch(function () {}); }
            if (e.key === 'Escape') { e.preventDefault(); descartarFila(tr); }
        });

        tabla.addEventListener('click', function (e) {
            var tr = e.target.closest('tr.inv-fila');
            if (!tr) return;
            if (e.target.closest('.js-guardar')) guardarFila(tr).catch(function () {});
            if (e.target.closest('.js-descartar')) descartarFila(tr);
            if (e.target.closest('.js-revision')) abrirRevision(tr);
        });

        window.addEventListener('beforeunload', function (e) {
            if (tabla.querySelector('tr.is-sucio')) { e.preventDefault(); e.returnValue = ''; }
        });

        /* ---------- quitar el check ---------- */

        var modalDesverificar = new bootstrap.Modal(document.getElementById('inv-modal-desverificar'));
        var filaDesverificar = null;

        function abrirDesverificar(tr) {
            filaDesverificar = tr;
            document.getElementById('inv-desverificar-equipo').textContent = nombreEquipo(tr);
            document.getElementById('inv-desverificar-quien').textContent = orig(tr).verificado_texto;
            modalDesverificar.show();
        }

        document.getElementById('inv-desverificar-si').addEventListener('click', function () {
            var tr = filaDesverificar;
            var boton = this;
            boton.disabled = true;
            pedir(tr.dataset.urlVerificar, 'PATCH', { verificado: false })
                .then(function (j) {
                    pintarFila(tr, j.dispositivo);
                    avisar(j.message);
                    modalDesverificar.hide();
                    var primero = campo(tr, 'marca');
                    if (primero) primero.focus();
                })
                .catch(function (err) { avisar(err.message, true); })
                .finally(function () { boton.disabled = false; });
        });

        /* ---------- bandera de revisión ---------- */

        var modalRevision = new bootstrap.Modal(document.getElementById('inv-modal-revision'));
        var filaRevision = null;

        function abrirRevision(tr) {
            filaRevision = tr;
            var o = orig(tr);
            document.getElementById('inv-revision-equipo').textContent = nombreEquipo(tr);
            document.getElementById('inv-revision-nota').value = o.revisar_nota || '';
            document.getElementById('inv-revision-quitar').hidden = !o.revisar;
            document.getElementById('inv-revision-guardar').textContent = o.revisar ? 'Actualizar nota' : 'Marcar';
            modalRevision.show();
        }

        function enviarRevision(revisar) {
            var tr = filaRevision;
            pedir(tr.dataset.urlRevision, 'PATCH', { revisar: revisar, nota: document.getElementById('inv-revision-nota').value.trim() || null })
                .then(function (j) { pintarFila(tr, j.dispositivo); avisar(j.message); modalRevision.hide(); })
                .catch(function (err) { avisar(err.message, true); });
        }

        document.getElementById('inv-revision-guardar').addEventListener('click', function () { enviarRevision(true); });
        document.getElementById('inv-revision-quitar').addEventListener('click', function () { enviarRevision(false); });

        /* ---------- historial ---------- */

        var modalHistorial = new bootstrap.Modal(document.getElementById('inv-modal-historial'));
        var cuerpoHistorial = document.getElementById('inv-historial-cuerpo');

        function el(etiqueta, clase, contenido) {
            var n = document.createElement(etiqueta);
            if (clase) n.className = clase;
            if (contenido !== undefined) n.textContent = contenido;
            return n;
        }

        function pintarHistorial(movs, general) {
            cuerpoHistorial.innerHTML = '';
            if (!movs.length) {
                cuerpoHistorial.appendChild(el('p', 'text-muted text-center my-4', 'Sin movimientos todavía.'));
                return;
            }
            movs.forEach(function (m) {
                var caja = el('div', 'inv-mov inv-mov--' + m.tipo);
                var cabeza = el('div', 'inv-mov-cabeza');
                cabeza.appendChild(el('strong', '', m.movimiento));
                if (general) cabeza.appendChild(document.createTextNode(' · ' + m.dispositivo));
                cabeza.appendChild(document.createTextNode(' '));
                cabeza.appendChild(el('small', '', m.cuando + ' · ' + m.quien));
                caja.appendChild(cabeza);
                if (m.nota) caja.appendChild(el('div', 'small text-muted', m.nota));
                if (m.cambios.length) {
                    var ul = el('ul');
                    m.cambios.forEach(function (c) {
                        var li = el('li');
                        li.appendChild(el('span', '', c.campo + ': '));
                        if (!m.alta) {
                            li.appendChild(el('span', 'inv-antes', c.antes));
                            li.appendChild(document.createTextNode(' → '));
                        }
                        li.appendChild(el('span', m.alta ? '' : 'inv-despues', c.despues));
                        ul.appendChild(li);
                    });
                    caja.appendChild(ul);
                }
                cuerpoHistorial.appendChild(caja);
            });
        }

        document.addEventListener('click', function (e) {
            var b = e.target.closest('.js-historial');
            if (!b) return;
            var id = b.dataset.dispositivo;
            document.getElementById('inv-historial-titulo').textContent = b.dataset.titulo || 'Historial';
            cuerpoHistorial.innerHTML = '<div class="text-center my-4"><div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Cargando…</span></div></div>';
            modalHistorial.show();
            pedir(URL_HISTORIAL + (id ? '?dispositivo=' + encodeURIComponent(id) : ''), 'GET')
                .then(function (movs) { pintarHistorial(movs, !id); })
                .catch(function (err) { cuerpoHistorial.innerHTML = ''; cuerpoHistorial.appendChild(el('div', 'alert alert-danger', err.message)); });
        });

        /* ---------- nuevo dispositivo ---------- */

        var form = document.getElementById('inv-form-nuevo');
        var errorNuevo = document.getElementById('inv-nuevo-error');

        form.querySelector('.js-estado').addEventListener('change', function () {
            document.getElementById('n-razon-caja').hidden = this.value !== 'malo';
        });
        form.querySelector('.js-resguardante').addEventListener('input', function () {
            enlazarResguardante(this, form.elements.resguardante_id, form.elements.resguardante_puesto);
        });
        form.addEventListener('input', function (e) { e.target.classList.remove('is-invalid'); });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorNuevo.hidden = true;
            form.querySelectorAll('.is-invalid').forEach(function (x) { x.classList.remove('is-invalid'); });
            var datos = {};
            CAMPOS.forEach(function (c) { datos[c] = (form.elements[c].value || '').trim(); });
            var boton = form.querySelector('[type="submit"]');
            boton.disabled = true;
            pedir(form.getAttribute('action'), 'POST', datos)
                .then(function (j) {
                    window.location.hash = 'inv-' + j.id;
                    window.location.reload();
                })
                .catch(function (err) {
                    Object.keys(err.errores || {}).forEach(function (c) {
                        if (form.elements[c]) form.elements[c].classList.add('is-invalid');
                        if (c === 'razon_malo') document.getElementById('n-razon-caja').hidden = false;
                    });
                    errorNuevo.textContent = err.message;
                    errorNuevo.hidden = false;
                })
                .finally(function () { boton.disabled = false; });
        });

        document.getElementById('inv-modal-nuevo').addEventListener('shown.bs.modal', function () {
            document.getElementById('n-descripcion').focus();
        });

        /* Al volver de dar de alta, se resalta el nuevo. */
        if (location.hash.indexOf('#inv-') === 0) {
            var nuevo = document.getElementById(location.hash.slice(1));
            if (nuevo) {
                nuevo.classList.add('is-nuevo');
                nuevo.scrollIntoView({ block: 'center' });
                history.replaceState(null, '', location.pathname + location.search);
            }
        }
    })();
    </script>
@endsection
