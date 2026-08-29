@extends('layouts.app')
@section('title', 'Retroceso de Solicitudes')
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Retroceso de Solicitudes</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-4"><br>
                                    <button type="button" class="btn btn-primary open-modal" data-bs-toggle="modal" data-bs-target="#ModalRetrocesoSolicitud">
                                        <i class="bi bi-search"></i> Buscar solicitud
                                    </button>
                                </div>
                                <div class="col-xs-12 col-sm-6 col-md-4"><br>
                                    <a href="{{ route('solicitudes_index') }}" class="btn btn-secondary">Regresar</a>
                                </div>
                            </div>
                            <br>

                            @if(session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Retroceso aplicado.</strong>
                                    {{ session()->get('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if (session('message'))
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <strong>Expediente localizado.</strong>
                                    {{ session()->get('message') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="row">
                                    <div class="table-responsive">
                                        <table id="example" class="table table-striped table-hover mt-3 border shadow-sm">
                                            <thead style="background-color: #354647; color: white;">
                                                <tr>
                                                    <th class="text-center text-white" style="color: white;">NUE</th>
                                                    <th class="text-center text-white" style="color: white;">Delegación</th>
                                                    <th class="text-center text-white" style="color: white;">Solicitante</th>
                                                    <th class="text-center text-white" style="color: white;">Estatus</th>
                                                    <th class="text-center text-white" style="color: white;">Audiencia a retroceder</th>
                                                    <th class="text-center text-white" style="color: white;">Montos</th>
                                                    <th class="text-center text-white" style="color: white;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (session('resultados_retroceso_solicitud'))
                                                    @foreach (session('resultados_retroceso_solicitud') as $folio)
                                                        <tr>
                                                            <td class="text-center align-middle"><strong>{{ $folio['NUE'] ?: 'Sin NUE' }}</strong></td>
                                                            <td class="text-center align-middle">
                                                                <span class="badge" style="background-color: #6c757d;">{{ $folio['delegacion'] ?? 'N/A' }}</span>
                                                            </td>
                                                            <td class="text-center align-middle">{{ $folio['solicitante'] }}</td>
                                                            <td class="text-center align-middle">
                                                                <span class="badge" style="background-color: #ffc107; color: black;">{{ $folio['estatus'] }}</span>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                @if ($folio['ultima_audiencia'])
                                                                    <strong>Audiencia {{ $folio['ultima_audiencia']['numero'] ?: '—' }}</strong>
                                                                    de {{ $folio['total_audiencias'] }}<br>
                                                                    <small>
                                                                        {{ $folio['ultima_audiencia']['fecha'] ?? 'sin fecha' }} ·
                                                                        {{ $folio['ultima_audiencia']['estatus'] ?: 'sin estatus' }}
                                                                    </small>
                                                                @else
                                                                    <span class="text-muted">Sin audiencias</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <span class="badge bg-secondary">{{ $folio['conceptos'] }} conceptos</span>
                                                                <span class="badge bg-secondary">{{ $folio['deducciones'] }} deducciones</span>
                                                                <span class="badge bg-secondary">{{ $folio['pagos'] }} pagos</span>
                                                                @if ($folio['pagados'] > 0)
                                                                    <br>
                                                                    <span class="badge bg-danger mt-1">
                                                                        <i class="bi bi-exclamation-triangle"></i>
                                                                        {{ $folio['pagados'] }} pago(s) ya cobrado(s)
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                @if ($folio['retrocedible'])
                                                                    <form method="POST"
                                                                          action="{{ route('retroceso_solicitud_aplicar', $folio['id']) }}"
                                                                          class="d-inline form-retroceso-solicitud"
                                                                          data-nue="{{ $folio['NUE'] ?: ('consecutivo ' . $folio['consecutivo']) }}"
                                                                          data-numero="{{ $folio['ultima_audiencia']['numero'] }}"
                                                                          data-total="{{ $folio['total_audiencias'] }}"
                                                                          data-conceptos="{{ $folio['conceptos'] }}"
                                                                          data-deducciones="{{ $folio['deducciones'] }}"
                                                                          data-pagos="{{ $folio['pagos'] }}"
                                                                          data-pagados="{{ $folio['pagados'] }}">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-sm btn-danger text-white shadow-sm">
                                                                            <i class="bi bi-arrow-counterclockwise"></i> Retroceso
                                                                        </button>
                                                                    </form>
                                                                @else
                                                                    <button class="btn btn-sm btn-secondary shadow-sm" disabled
                                                                            title="La última audiencia ya está Pendiente o la solicitud no tiene audiencias.">
                                                                        <i class="bi bi-dash-circle"></i> No aplica
                                                                    </button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<div class="modal fade" id="ModalRetrocesoSolicitud" tabindex="-1" role="dialog" aria-labelledby="retrocesoSolicitudLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="retrocesoSolicitudLabel">Buscar solicitud</h5>
            </div>
            <form class='needs-validation novalidate' id='form_retroceso_solicitud' method='POST' action="{{ route('retroceso_solicitud_buscar') }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Capture la delegación, el año y el consecutivo del expediente.
                        Ejemplo: <code>LAZ/SOL/{{ date('Y') }}/00384</code>
                    </p>
                    <div class="row">
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Delegación <span class="text-danger">*</span></label>
                            <select class="form-control" name="delegacion" id="delegacion_sol" required>
                                <option value="">Seleccione</option>
                                @foreach ($delegaciones as $deleg)
                                    <option value="{{ $deleg }}" {{ old('delegacion') == $deleg ? 'selected' : '' }}>{{ $deleg }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Debe seleccionar la delegación.</div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Año <span class="text-danger">*</span></label>
                            <select class="form-control" name="anio" id="anio_sol" required>
                                <option value="">Seleccione</option>
                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ old('anio') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un año.</div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Consecutivo <span class="text-danger">*</span></label>
                            <input type="number" placeholder="Ej. 384" class="form-control" name="consecutivo"
                                   id="consecutivo_sol" min="1" step="1" required value="{{ old('consecutivo') }}"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <div class="invalid-feedback">Ingrese un consecutivo válido.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Validación del formulario de búsqueda
        var formBuscar = document.getElementById('form_retroceso_solicitud');
        if (formBuscar) {
            formBuscar.addEventListener('submit', function (e) {
                var delegacion  = document.getElementById('delegacion_sol');
                var anio        = document.getElementById('anio_sol');
                var consecutivo = document.getElementById('consecutivo_sol');
                var errores = [];

                if (!delegacion.value) {
                    errores.push('Debe seleccionar la delegación.');
                }
                if (!anio.value) {
                    errores.push('Debe seleccionar el año.');
                }
                if (!consecutivo.value || consecutivo.value <= 0 || !Number.isInteger(Number(consecutivo.value))) {
                    errores.push('El consecutivo debe ser un número entero positivo.');
                }

                if (errores.length > 0) {
                    e.preventDefault();
                    e.stopPropagation();
                    // SweetAlert 1.x — misma API que usa el resto del sistema.
                    if (typeof swal === 'function') {
                        swal({
                            title: 'Campos incompletos',
                            text: errores.join('\n'),
                            type: 'warning'
                        });
                    } else {
                        alert(errores.join('\n'));
                    }
                }

                formBuscar.classList.add('was-validated');
            });
        }

        // Confirmación antes de aplicar el retroceso
        document.querySelectorAll('.form-retroceso-solicitud').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                var nue     = form.dataset.nue;
                var numero  = form.dataset.numero;
                var total   = form.dataset.total;
                var pagados = parseInt(form.dataset.pagados || '0', 10);

                var texto = 'Se retrocederá la audiencia ' + numero + ' de ' + total + '.\n\n' +
                            'La audiencia regresará a Pendiente y la solicitud a Confirmado.\n\n' +
                            'Se eliminarán de forma permanente:\n' +
                            '• ' + form.dataset.conceptos   + ' concepto(s) de pago\n' +
                            '• ' + form.dataset.deducciones + ' deducción(es)\n' +
                            '• ' + form.dataset.pagos       + ' pago(s)\n';

                if (pagados > 0) {
                    texto += '\nATENCIÓN: ' + pagados + ' de esos pagos ya figuran como cobrados.\n';
                }

                // SweetAlert 1.x — misma API que usa el resto del sistema.
                if (typeof swal === 'function') {
                    swal({
                        title: 'Confirmar retroceso de ' + nue,
                        text: texto,
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Sí, aplicar retroceso',
                        cancelButtonText: 'Cancelar',
                        closeOnConfirm: true
                    }, function (isConfirm) {
                        if (isConfirm) { form.submit(); }
                    });
                } else {
                    if (confirm(texto + '\n¿Proceder?')) { form.submit(); }
                }
            });
        });

    });
</script>
@endsection
