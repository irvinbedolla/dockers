@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Administración</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="col-xs-4 col-sm-4 col-md-2"><br>
                                <button type="button" class="btn btn-primary open-modal" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    Consultar
                                </button>
                            </div>

                            @if(session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Cambio de estatus Correcto.</strong>
                                    {{ session()->get('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if (session('message'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Expediente Localizado</strong>
                                    {{ session()->get('success') }}
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
                                                    <th class="text-center text-white" style="color: white;">Fecha</th>
                                                    <th class="text-center text-white" style="color: white;">Estatus</th>
                                                    <th class="text-center text-white" style="color: white;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (session('folios_generados'))
                                                    @foreach (session('folios_generados') as $folio)
                                                        <tr>
                                                            <td class="text-center align-middle"><strong>{{ $folio['NUE'] }}</strong></td>
                                                            <td class="text-center align-middle">
                                                                <span class="badge" style="background-color: #6c757d;">{{ $folio['delegacion'] ?? 'N/A' }}</span>
                                                            </td>
                                                            <td class="text-center align-middle">{{ $folio['solicitante'] ?? 'N/A' }}</td>
                                                            <td class="text-center align-middle">{{ \Carbon\Carbon::parse($folio['fecha'])->format('d/m/Y') }}</td>
                                                            <td class="text-center align-middle">
                                                                <span class="badge" style="background-color: #ffc107; color: black;">{{ $folio['estatus'] }}</span>
                                                            </td> 
                                                            <td class="text-center align-middle">
                                                                @if (session('tipo') == "Cumplimiento")
                                                                    <a class="btn btn-sm btn-danger text-white shadow-sm" href="{{ route('accion_retrocesoC', $folio['id'] )}}" onclick="return confirm('¿Confirma que desea retroceder el estatus de este cumplimiento a Pendiente/Concluir?');">
                                                                        <i class="bi bi-arrow-counterclockwise"></i> Retroceso
                                                                    </a>
                                                                @elseif (session('tipo') == "Ratificación")
                                                                    <a class="btn btn-sm btn-danger text-white shadow-sm" href="{{ route('accion_retrocesoR', $folio['id'] )}}" onclick="return confirm('ATENCIÓN: Al realizar el retroceso se borrarán las Prestaciones, deducciones y días de cumplimiento. ¿Está seguro de proceder?');">
                                                                        <i class="bi bi-arrow-counterclockwise"></i> Retroceso
                                                                    </a>
                                                                @else
                                                                    <button class="btn btn-sm btn-secondary shadow-sm" disabled><i class="bi bi-dash-circle"></i> No aplica</button>
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
                            <!--Se realiza la validación de campos para ver si dejó alguno vacío-->
                            @if ($errors->any())
                                <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                            <!--<span class="badge badge-danger">{{ $error }}</span>-->
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="resultadoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultadoModalLabel">Genera retroceso</h5>
            </div>
            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('generar_retroceso')}}">
                @csrf
                <div class="modal-body" id="modal-body-content">
                    <div class="row">  
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Tipo de retroceso <span class="text-danger">*</span></label>
                            <select class="form-control" name="tipo" id="tipo_retroceso" required>
                                <option value="">Seleccione</option>
                                <option value="Cumplimiento">Cumplimiento</option>
                                <option value="Ratificación">Ratificación</option>
                                <option value="Solicitudes">Solicitudes</option>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un tipo de retroceso.</div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Folio <span class="text-danger">*</span></label>
                            <input type="number" placeholder="Ingrese el folio" class="form-control" name="folio" id="folio_retroceso" min="1" step="1" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <div class="invalid-feedback">Ingrese un folio válido (número positivo).</div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4">
                            <label>Año <span class="text-danger">*</span></label>
                            <select class="form-control" name="año" id="año_retroceso" required>
                                <option value="">Seleccione</option>
                                @for ($i = date('Y'); $i >= date('Y') - 3; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un año.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Generar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="nuevo_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var formRetroceso = document.getElementById('form_roles');
        if (formRetroceso) {
            formRetroceso.addEventListener('submit', function (e) {
                var tipo  = document.getElementById('tipo_retroceso');
                var folio = document.getElementById('folio_retroceso');
                var año   = document.getElementById('año_retroceso');
                var errores = [];

                if (!tipo.value) {
                    errores.push('Debe seleccionar un tipo de retroceso.');
                }
                if (!folio.value || folio.value <= 0 || !Number.isInteger(Number(folio.value))) {
                    errores.push('El folio debe ser un número entero positivo.');
                }
                if (!año.value) {
                    errores.push('Debe seleccionar un año.');
                }

                if (errores.length > 0) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Campos incompletos',
                            html: '<ul style="text-align:left;">' + errores.map(function(err) { return '<li>' + err + '</li>'; }).join('') + '</ul>',
                        });
                    } else {
                        alert(errores.join('\n'));
                    }
                }

                formRetroceso.classList.add('was-validated');
            });
        }
    });
</script>
@endpush