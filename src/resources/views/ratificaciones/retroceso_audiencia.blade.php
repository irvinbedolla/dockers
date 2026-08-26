@extends('layouts.app')
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Retroceso de Audiencias</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-4"><br>
                                    <button type="button" class="btn btn-primary open-modal" data-bs-toggle="modal" data-bs-target="#ModalRetroceso">
                                        <i class="bi bi-search"></i> Buscar solicitud
                                    </button>
                                </div>
                                <div class="col-xs-12 col-sm-6 col-md-4"><br>
                                    <a href="{{ route('index_retroceso') }}" class="btn btn-secondary">Regresar</a>
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
                                                    <th class="text-center text-white" style="color: white;">Solicitante</th>
                                                    <th class="text-center text-white" style="color: white;">Citados</th>
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
                                                            <td class="text-center align-middle">{{ $folio['solicitante'] ?: 'N/A' }}</td>
                                                            <td class="text-center align-middle">
                                                                @if (count($folio['citados_lista']))
                                                                    {{ implode(', ', $folio['citados_lista']) }}
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                {{ $folio['fecha'] ? \Carbon\Carbon::parse($folio['fecha'])->format('d/m/Y') : 'N/A' }}
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <span class="badge" style="background-color: #ffc107; color: black;">{{ $folio['estatus'] }}</span>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                @if (!$folio['retrocedible'])
                                                                    <span class="badge bg-light text-dark">No aplica</span>
                                                                @elseif ($folio['audiencia_eliminada'] || $folio['citados_eliminar'] > 0 || $folio['conceptos_eliminar'] > 0 || $folio['deducciones_eliminar'] > 0 || $folio['pagos_eliminar'] > 0)
                                                                    @if ($folio['audiencia_eliminada'])
                                                                        <span class="badge bg-danger">1 audiencia (registro completo)</span>
                                                                    @endif
                                                                    @if ($folio['citados_eliminar'] > 0)
                                                                        <span class="badge bg-secondary">{{ $folio['citados_eliminar'] }} citado(s)</span>
                                                                    @endif
                                                                    @if ($folio['conceptos_eliminar'] > 0)
                                                                        <span class="badge bg-secondary">{{ $folio['conceptos_eliminar'] }} concepto(s)</span>
                                                                    @endif
                                                                    @if ($folio['deducciones_eliminar'] > 0)
                                                                        <span class="badge bg-secondary">{{ $folio['deducciones_eliminar'] }} deducción(es)</span>
                                                                    @endif
                                                                    @if ($folio['pagos_eliminar'] > 0)
                                                                        <span class="badge bg-secondary">{{ $folio['pagos_eliminar'] }} cumplimiento(s)</span>
                                                                    @endif
                                                                @else
                                                                    Cambio de estatus a <strong>Pendiente</strong>
                                                                @endif
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                @if ($folio['retrocedible'])
                                                                    <form method="POST"
                                                                          action="{{ route('retroceso_audiencia_aplicar', $folio['id']) }}"
                                                                          class="d-inline form-retroceso"
                                                                          data-nue="{{ $folio['NUE'] }}"
                                                                          data-audiencia-eliminada="{{ $folio['audiencia_eliminada'] ? 1 : 0 }}"
                                                                          data-citados="{{ $folio['citados_eliminar'] }}"
                                                                          data-conceptos="{{ $folio['conceptos_eliminar'] }}"
                                                                          data-deducciones="{{ $folio['deducciones_eliminar'] }}"
                                                                          data-pagos="{{ $folio['pagos_eliminar'] }}">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-sm btn-danger text-white shadow-sm">
                                                                            <i class="bi bi-arrow-counterclockwise"></i> Retroceso
                                                                        </button>
                                                                    </form>
                                                                @else
                                                                    <button class="btn btn-sm btn-secondary shadow-sm" disabled
                                                                            title="Solo se puede retroceder una solicitud con audiencias registradas.">
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
                <h5 class="modal-title" id="retrocesoModalLabel">Buscar solicitud por NUE</h5>
            </div>
            <form class='needs-validation novalidate' id='form_retroceso_buscar' method='POST' action="{{ route('retroceso_audiencia_buscar') }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Capture las partes del NUE tal como aparece en el expediente.
                        Ejemplo: <code>MOR/SOL/{{ date('Y') }}/00576</code>
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
                preview.value = delegacion.value + '/SOL/' + anio.value + '/' +
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

                var nue               = form.dataset.nue;
                var audienciaEliminada = form.dataset.audienciaEliminada === '1';
                var citados           = parseInt(form.dataset.citados || '0', 10);
                var conceptos         = parseInt(form.dataset.conceptos || '0', 10);
                var deducciones       = parseInt(form.dataset.deducciones || '0', 10);
                var pagos             = parseInt(form.dataset.pagos || '0', 10);

                var detalles = [];
                if (audienciaEliminada) { detalles.push('• El registro completo de la última audiencia'); }
                if (citados > 0)        { detalles.push('• ' + citados     + ' citado(s)'); }
                if (conceptos > 0)      { detalles.push('• ' + conceptos   + ' concepto(s) de pago'); }
                if (deducciones > 0)    { detalles.push('• ' + deducciones + ' deducción(es)'); }
                if (pagos > 0)          { detalles.push('• ' + pagos      + ' cumplimiento(s)'); }

                var texto = detalles.length
                    ? 'Se eliminará de forma permanente:\n' + detalles.join('\n') + '\n\n'
                    : 'No se eliminará ningún registro, solo se ajustará el estatus.\n\n';

                texto += 'La solicitud regresará a un estatus previo para poder capturarse nuevamente.';

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
