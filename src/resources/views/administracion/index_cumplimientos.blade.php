@extends('layouts.app')
@section('title', 'Administración')
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
                            @if (auth()->user()->hasRole('Super Usuario'))
                                <div class="text-end mb-2">
                                    <a href="{{ route('administracion_historial', 'borrar_cumplimiento') }}" class="btn btn-outline-primary">
                                        <i class="bi bi-clock-history"></i> Historial
                                    </a>
                                </div>
                            @endif
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('borrar_cumplimeinto')}}">
                                @csrf
                                <div class="modal-body" id="modal-body-content">
                                    <div class="row">  
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <label>Tipo de cumplimiento</label>
                                            <select class="form-control" name="tipo">
                                                <option value="">Seleccione</option>
                                                <option value="Audiencia">Audiencia</option>
                                                <option value="Ratificación">Ratificación</option>
                                            </select>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <label>Folio</label>
                                            <input type="number" class="form-control" name="folio">
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <label>Año</label>
                                            <input type="number" class="form-control" name="año">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="btn btn-primary">Consultar</button>
                                </div>
                            </form>


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
                                        <table id="example" class="table table-striped mt-1">
                                            @if (session('tipo') == "Audiencia")
                                                <thead style="background-color: #354647;">
                                                    <tr>
                                                        <th style="color: #fff;">NUE</th>
                                                        <th style="color: #fff; text-align: center;">Fecha</th>
                                                        <th style="color: #fff; text-align: center;">Descripción</th>
                                                        <th style="color: #fff; text-align: center;">Estatus</th>
                                                        <th style="color: #fff; text-align: center;">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if (session('folios_generados'))
                                                        @foreach (session('folios_generados') as $folio)
                                                            <tr>
                                                                <td style="text-align: center;">{{ $folio['NUE'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['fecha'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['descripcion'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['estatus'] }}</td> 
                                                                <td style="text-align: center;">
                                                                    <form method="POST" action="{{ route('borrar_cumplimeintoA', $folio['id']) }}" class="form-borrar-cumplimiento" data-nue="{{ $folio['NUE'] }}" data-descripcion="{{ $folio['descripcion'] }}">
                                                                        @csrf
                                                                        <input type="hidden" name="_method" value="DELETE">
                                                                        <input type="hidden" name="motivo">
                                                                        @can('cumplimientos_borrar_cumplimiento')
                                                                            <button class="btn btn-danger" type="submit">Borrar cumplimeinto</button>
                                                                        @endcan
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            @endif
                                            @if (session('tipo') == "Ratificación")
                                                <thead style="background-color: #354647;">
                                                    <tr>
                                                        <th style="color: #fff;">NUE</th>
                                                        <th style="color: #fff; text-align: center;">Fecha</th>
                                                        <th style="color: #fff; text-align: center;">Descripción</th>
                                                        <th style="color: #fff; text-align: center;">Estatus</th>
                                                        <th style="color: #fff; text-align: center;">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if (session('folios_generados'))
                                                        @foreach (session('folios_generados') as $folio)
                                                            <tr>
                                                                <<td style="text-align: center;">{{ $folio['NUE'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['fecha'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['descripcion'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['estatus'] }}</td> 
                                                                <td style="text-align: center;">
                                                                    <form method="POST" action="{{ route('borrar_cumplimeintoA', $folio['id']) }}" class="form-borrar-cumplimiento" data-nue="{{ $folio['NUE'] }}" data-descripcion="{{ $folio['descripcion'] }}">
                                                                        @csrf
                                                                        <input type="hidden" name="_method" value="DELETE">
                                                                        <input type="hidden" name="motivo">
                                                                        @can('cumplimientos_borrar_cumplimiento')
                                                                            <button class="btn btn-danger" type="submit">Borrar cumplimeinto</button>
                                                                        @endcan
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            @endif
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



@push('body_end')
<div id="nuevo_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Confirmación y motivo obligatorio antes de borrar un cumplimiento.
        document.querySelectorAll('.form-borrar-cumplimiento').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                var detalle = document.createElement('div');
                detalle.innerHTML = '<p>Se borrará el cumplimiento <strong></strong> del expediente <strong></strong>.</p>';
                detalle.querySelectorAll('strong')[0].textContent = form.dataset.descripcion || '';
                detalle.querySelectorAll('strong')[1].textContent = form.dataset.nue || 'sin NUE';

                Swal.fire({
                    title: 'Confirmar borrado', html: detalle, icon: 'warning',
                    input: 'textarea', inputLabel: 'Motivo del borrado (obligatorio)',
                    inputPlaceholder: 'Ej. Cumplimiento capturado por error', inputAttributes: { maxlength: 1000 },
                    inputValidator: function (valor) { if (!valor || !valor.trim()) return 'El motivo es obligatorio.'; },
                    showCancelButton: true, confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Sí, borrar', cancelButtonText: 'Cancelar', reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.querySelector('[name=motivo]').value = result.value.trim();
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection