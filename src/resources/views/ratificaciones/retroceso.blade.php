@extends('layouts.app')
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Retroceso de Ratificaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-4"><br>
                                    <button type="button" class="btn btn-primary open-modal" data-bs-toggle="modal" data-bs-target="#ModalRetroceso">
                                        <i class="bi bi-search"></i> Buscar ratificación
                                    </button>
                                </div>
                                <div class="col-xs-12 col-sm-6 col-md-4"><br>
                                    <a href="{{ route('index_ratificacion') }}" class="btn btn-secondary">Regresar</a>
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
                                            <thead style="background-color: #4A001F; color: white;">
                                                <tr>
                                                    <th class="text-center text-white" style="color: white;">NUE</th>
                                                    <th class="text-center text-white" style="color: white;">Delegación</th>
                                                    <th class="text-center text-white" style="color: white;">Trabajador</th>
                                                    <th class="text-center text-white" style="color: white;">Empresa</th>
                                                    <th class="text-center text-white" style="color: white;">Fecha</th>
                                                    <th class="text-center text-white" style="color: white;">Estatus</th>
                                                    <th class="text-center text-white" style="color: white;">Se eliminará</th>
                                                    <th class="text-center text-white" style="color: white;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (session('resultados_retroceso'))
                                                    @foreach (session('resultados_retroceso') as $folio)
                                                        <tr>
                                                            <td class="text-center align-middle"><strong>{{ $folio['NUE'] }}</strong></td>
                                                            <td class="text-center align-middle">
                                                                <span class="badge" style="background-color: #6c757d;">{{ $folio['delegacion'] ?? 'N/A' }}</span>
                                                            </td>
                                                            <td class="text-center align-middle">{{ $folio['trabajador'] ?: 'N/A' }}</td>
                                                            <td class="text-center align-middle">{{ $folio['empresa'] ?: 'N/A' }}</td>
                                                            <td class="text-center align-middle">
                                                                {{ $folio['fecha'] ? \Carbon\Carbon::parse($folio['fecha'])->format('d/m/Y') : 'N/A' }}
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <span class="badge" style="background-color: #ffc107; color: black;">{{ $folio['estatus'] }}</span>
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
                                                                          action="{{ route('retroceso_ratificacion_aplicar', $folio['id']) }}"
                                                                          class="d-inline form-retroceso"
                                                                          data-nue="{{ $folio['NUE'] }}"
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
                                                                            title="Solo se puede retroceder una ratificación concluida.">
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

<div class="modal fade" id="ModalRetroceso" tabindex="-1" role="dialog" aria-labelledby="retrocesoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="retrocesoModalLabel">Buscar ratificación por NUE</h5>
            </div>
            <form class='needs-validation novalidate' id='form_retroceso_buscar' method='POST' action="{{ route('retroceso_ratificacion_buscar') }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Capture las partes del NUE tal como aparece en el expediente.
                        Ejemplo: <code>MOR/RAT/{{ date('Y') }}/00576</code>
                    </p>
                    <div class="row">
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Delegación <span class="text-danger">*</span></label>
                            <select class="form-control" name="delegacion" id="delegacion_retroceso" required>
                                <option value="">Seleccione</option>
                                @foreach ($delegaciones as $prefijo => $nombre)
                                    <option value="{{ $prefijo }}" {{ old('delegacion') == $prefijo ? 'selected' : '' }}>
                                        {{ $prefijo }} — {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Debe seleccionar la delegación.</div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Año <span class="text-danger">*</span></label>
                            <select class="form-control" name="anio" id="anio_retroceso" required>
                                <option value="">Seleccione</option>
                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ old('anio') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un año.</div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Consecutivo <span class="text-danger">*</span></label>
                            <input type="number" placeholder="Ej. 576" class="form-control" name="consecutivo"
                                   id="consecutivo_retroceso" min="1" step="1" required value="{{ old('consecutivo') }}"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <div class="invalid-feedback">Ingrese un consecutivo válido.</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="text-muted">NUE a buscar</label>
                            <input type="text" class="form-control" id="nue_preview" readonly value="—">
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

        // Vista previa del NUE mientras se capturan las partes
        var delegacion  = document.getElementById('delegacion_retroceso');
        var anio        = document.getElementById('anio_retroceso');
        var consecutivo = document.getElementById('consecutivo_retroceso');
        var preview     = document.getElementById('nue_preview');

        function armarNue() {
            if (!preview) return;
            if (delegacion.value && anio.value && consecutivo.value) {
                preview.value = delegacion.value + '/RAT/' + anio.value + '/' +
                                String(consecutivo.value).padStart(5, '0');
            } else {
                preview.value = '—';
            }
        }

        [delegacion, anio, consecutivo].forEach(function (el) {
            if (el) { el.addEventListener('change', armarNue); el.addEventListener('input', armarNue); }
        });
        armarNue();

        // Validación del formulario de búsqueda
        var formBuscar = document.getElementById('form_retroceso_buscar');
        if (formBuscar) {
            formBuscar.addEventListener('submit', function (e) {
                var errores = [];

                if (!delegacion.value) {
                    errores.push('Debe seleccionar la delegación del NUE.');
                }
                if (!anio.value) {
                    errores.push('Debe seleccionar el año del NUE.');
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
        document.querySelectorAll('.form-retroceso').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                var nue     = form.dataset.nue;
                var pagados = parseInt(form.dataset.pagados || '0', 10);

                var texto = 'Se eliminará de forma permanente:\n' +
                            '• ' + form.dataset.conceptos   + ' concepto(s) de pago\n' +
                            '• ' + form.dataset.deducciones + ' deducción(es)\n' +
                            '• ' + form.dataset.pagos       + ' pago(s) programado(s)\n' +
                            '• Las manifestaciones capturadas\n\n';

                if (pagados > 0) {
                    texto += 'ATENCIÓN: ' + pagados + ' de esos pagos ya figuran como cobrados.\n\n';
                }

                texto += 'La ratificación regresará al estatus Confirmado.';

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
                    if (confirm(texto + '\n\n¿Proceder?')) { form.submit(); }
                }
            });
        });

    });
</script>
@endsection
