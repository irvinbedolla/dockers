@extends('layouts.app')
@section('title', 'Oficialia de Partes')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Oficialia de Partes</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                @if ($userRole == 'Turnos')
                                    <a href="#" class="btn btn-warning shadow-sm" data-bs-toggle="modal" data-id="{{ $id }}" data-bs-target="#oficialiaModal" style="background-color: #CEA845; border-color: #CEA845; color: #fff;">
                                        <i class="bi bi-plus-lg me-1"></i> Agregar
                                    </a>
                                @endif

                            </div>
                            
                            
                                
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped mt-2" style="width:100%">
                                        <thead style="background-color: #354647;">
                                            <tr>
                                                
                                                <th class="text-center text-white" style="width: 10%; color: #fff;">Fecha de Registro</th>
                                                <th class="text-center text-white" style="width: 10%; color: #fff;">Fecha de Termino</th>
                                                <th class="text-center text-white" style="width: 15%; color: #fff;">Núm. Oficio</th>
                                                <th class="text-center text-white" style="color: #fff;">Tipo de Tramite</th>
                                                <th class="text-center text-white" style="color: #fff;">Área Turno</th>
                                                <th class="text-center text-white" style="color: #fff;">Usuario Responsable</th>
                                                <th class="text-center text-white" style="color: #fff;">Estado</th>
                                                <th class="text-center text-white" style="width: 20%; color: #fff;">Conclusiones</th>
                                                <th style="width: 15%; color: #fff;"></th>
                                                <th style="width: 15%; color: #fff;"></th>
                                                <th class="text-white" style="width: 15%; color: #fff;">Documento</th>
                                                <th class="text-center text-white" style="width: 15%; color: #fff;">Acciones</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($oficialias as $oficialia)
                                                <tr>
                                                    <td>{{ $oficialia->fecha_registro }} {{ $oficialia->hora_registro}}</td>
                                                    <td>{{ $oficialia->fecha_termino }} {{ $oficialia->hora_termino}}</td>
                                                    <td>{{ $oficialia->oficio }}</td>
                                                    <td>{{ $oficialia->tipo_tramite }}</td>
                                                    <td>{{ $oficialia->area_turno }}</td>
                                                    <td>{{ strtoupper($oficialia->usuarioResponsable->name )}}</td>
                                                    <td>@if($oficialia->estatus == 'creado')Pendiente @elseif ($oficialia->estatus == 'turnado') Turnado @else Concluido @endif</td>
                                                    <td>@if($oficialia->conclusion){{ $oficialia->conclusion }} @endif</td>
                                                    <td>
                                                        @if($oficialia->estatus == 'creado' && $oficialia->usuario_responsable == $id && $userRole != 'Turnos')
                                                        <a href="#" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-id="{{ $oficialia->id }}" data-bs-target="#concluirModal"><i class="bi bi-check-lg"></i> Concluir</a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($oficialia->estatus == 'creado' && $oficialia->usuario_responsable == $id)
                                                            <a href="#" class="btn btn-success btn-sm" data-bs-toggle="modal" data-id="{{ $oficialia->id }}" data-bs-target="#turnarModal"><i class="bi bi-file-person"></i> Turnar</a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                        
                                                            @if (!empty($oficialia->ruta_oficio) )
                                                                <a target="_blank" class="btn btn-primary mt-1" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'oficialia', 'id' =>  $oficialia->id, 'archivo' => $oficialia->ruta_oficio]) }}"><i class="bi bi-file-earmark-pdf"></i> Oficio</a>
                                                            @else
                                                                <span class="text-muted">No se subió oficio</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-oficialia='@json($oficialia)' data-bs-target="#detallesModal"><i class="bi bi-card-text"></i> Detalles</a>
                                                            <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalHistorial-{{ $oficialia->oficio_id }}"><i class="bi bi-card-list"></i> Historial</a>
                                                        </div>
                                                    </td>
                                                    

                                                </tr>
                                                
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                      
                            <div class="d-flex justify-content-end mt-2">
                                {{ $oficialias->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @foreach($oficialias as $oficialia)
        <div class="modal fade" id="modalHistorial-{{ $oficialia->oficio_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Historial de Turnos - Oficio {{ $oficialia->oficio }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <table class="table table-striped mt-1" style="width:100%">
                            <thead style="background-color: #354647;">
                                <tr>
                                    <th></th>
                                    <th style="color: #fff;">Fecha y Hora</th>
                                    <th style="color: #fff;">Fecha y Hora Turno</th>
                                    <th style="color: #fff;">Usuario Responsable</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($historial[$oficialia->oficio_id]))
                                    @foreach($historial[$oficialia->oficio_id] as $index => $registro)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($registro->fecha_termino)
                                                    {{ $registro->fecha_termino }} <br> {{ $registro->hora_termino }}
                                                @elseif($registro->fecha)
                                                    {{ $registro->fecha }} <br> {{ $registro->hora }}
                                                @else
                                                    S/F
                                                @endif
                                            </td>
                                            <td>

                                                @if($registro->fecha_turno)
                                                    {{ $registro->fecha_turno }} <br> {{ $registro->hora_turno }}
                                                @elseif($registro->fecha_termino)
                                                    Concluida
                                                @else
                                                    Sin turnar
                                                @endif
                                            </td>
                                            <td>
                                                @if($registro->usuarioResponsable)
                                                    {{ strtoupper($registro->usuarioResponsable->name) }}
                                                @else
                                                    <span>Sin asignar</span>
                                                @endif
                                            </td>
                                            
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3">Sin historial</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>

                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="oficialiaModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form class='needs-validation novalidate' method='POST' action="{{ route('generar_oficialia') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="oficialia_id" id="oficialia_id_input" value="">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Agregar Oficialia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-xs-12 col-sm-12 col-md-12" id="Conrepresentante">
                            <div class="row">
                                
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <h5 class="text-center">Datos de identificación</h5>
                                    </div>
                                </div>

                                <!--div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Nombre(s) del representante <span style="color:red;">(*)</span></label>
                                        <input type="text" name="nombre_representante_pF" id="nombre_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                        <div class="invalid-feedback">
                                            El nombre es obligatorio.
                                        </div>
                                    </div>
                                </div-->
                                <div id="div1" class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label for="name">Fecha de registro <span style="color:red;">(*)</span></label>
                                        <input type="date" id="fecha_registro" name="fecha_registro"  class="form-control" required> 
                                        <div class="invalid-feedback">
                                            El campo fecha de nacimiento es obligatoria.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label for="name">Hora de registro<span style="color:red;">(*)</span></label>
                                        <input type="time" id="hora_registro" name="hora_registro" class="form-control">
                                        <div class="invalid-feedback">
                                            El campo es obligatorio.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label for="name">Tipo de Tramite <span style="color:red;">(*)</span></label>
                                        <input type="text" name="tipo_tramite" id="tipo_tramite" class="form-control" oninput="this.value = this.value.toUpperCase()" required > 
                                        <div class="invalid-feedback">
                                            El tipo de tramite es obligatorio.
                                        </div>
                                    </div>
                                </div>
                                
                                                          
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Area de Turno <span style="color:red;">(*)</span></label>
                                        <select name="area_turno" id="area_turno" class="form-control" required>
                                            <option value="">Seleccione</option>
                                            <option value="Dirección general">Delegación General</option>
                                            <option value="Unidad Jurídica">Unidad de Asuntos Jurídicos</option>
                                            <option value="Dirección Administrativa">Delegación Administrativa</option>
                                            <option value="Delegación Morelia">Delegación Morelia</option>
                                            <option value="Delegación Uruapan">Delegación Uruapan</option>
                                            <option value="Delegación Zamora">Delegación Zamora</option>
                                            
                                        </select>
                                        <div class="invalid-feedback">
                                            El area de turno es obligatorio.
                                        </div>
                                    </div>
                                </div>       
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Dependencia de Procendencia <span style="color:red;">(*)</span></label>
                                        <input type="text" name="precedencia" id="precedencia" class="form-control" oninput="this.value = this.value.toUpperCase()" maxlength="20" required > 
                                        
                                        <div class="invalid-feedback">
                                            La dependencia de precedencia es obligatoria.
                                        </div>
                                    </div>
                                </div>      
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Núm. de oficio <span style="color:red;">(*)</span></label>
                                        <input type="text" name="oficio" id="oficio" class="form-control" oninput="this.value = this.value.toUpperCase()" maxlength="20" required > 
                                        <div class="invalid-feedback">
                                            El segundo apellido es obligatorio.
                                        </div>
                                    </div>
                                </div>  
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label>Documento de Oficio</label><br>
                                        <input type="file" name="documento_oficio" id="documento_oficio" class="form-control" accept=".pdf" >

                                    </div>
                                </div>
                                <!--div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label>Hubo termino</label><br>
                                        <input type="checkbox" id="check_termino" name="termino" value="1">
                                    </div>
                                </div-->
                                <div class="col-xs-12 col-sm-12 col-md-4 d-none" id="contenedor_input_extra">
                                    <div class="form-group">
                                        <label for="name">Fecha de término <span style="color:red;">(*)</span></label>
                                        <input type="date" id="fecha_termino" name="fecha_termino" class="form-control"> 
                                        <div class="invalid-feedback">
                                            El campo fecha de término es obligatoria.
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-4 d-none" id="contenedor_input_hora">
                                    <div class="form-group">
                                        <label for="name">hora de término <span style="color:red;">(*)</span></label>
                                        <input type="time" id="hora_termino" name="hora_termino" class="form-control"> 
                                        <div class="invalid-feedback">
                                            El campo hora de término es obligatoria.
                                        </div>
                                    </div>
                                    
                                </div>
                                          
                            </div>
                        
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
<div class="modal fade" id="turnarModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Turnar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <form method="POST" action="{{ route('turnar_oficialia') }} ">
                    @csrf
                    <input type="hidden" id="modal-id" name="oficialia_id" value="">
                    <input type="hidden" name="origen" value="previa">
                    <table id="tablaUsuariosTurnar" class="table table-striped mt-2" style="width:100%">
                        <thead style="background-color: #354647;">   
                            <!--<th style="display: none;">ID</th>-->
                            
                            <th style="color: #fff;">Nombre</th>
                            <th style="color: #fff;">Acciones</th>
                        </thead>
                        <tbody class="contenidobusqueda">
                            
                            <tr>
                                <td>Delegación General </td> 
                                <td><button class="btn btn-info btn-sm" onclick="editar_rol()" type="submit" name="usuario_responsable" value="220"><i class="bi bi-check-square"></i> Seleccionar</button></td>
                            </tr>
                            <tr>
                                <td>Unidad de Asuntos Jurídicos</td>
                                <td><button class="btn btn-info btn-sm" onclick="editar_rol()" type="submit" name="usuario_responsable" value="2925"><i class="bi bi-check-square"></i> Seleccionar</button></td>
                            </tr>
                            <tr>
                                <td>Delegación Administrativa</td>
                                <td><button class="btn btn-info btn-sm" onclick="editar_rol()" type="submit" name="usuario_responsable" value="2980"><i class="bi bi-check-square"></i> Seleccionar</button></td>
                            </tr>
                            <tr>
                                <td>Delegación Morelia</td>
                                <td><button class="btn btn-info btn-sm" onclick="editar_rol()" type="submit" name="usuario_responsable" value="11"><i class="bi bi-check-square"></i> Seleccionar</button></td>
                            </tr>
                            <tr>
                                <td>Delegación Uruapan</td>
                                <td><button class="btn btn-info btn-sm" onclick="editar_rol()" type="submit" name="usuario_responsable" value="33"><i class="bi bi-check-square"></i> Seleccionar</button></td>
                            </tr>
                            <tr>
                                <td>Delegación Zamora</td>
                                <td><button class="btn btn-info btn-sm" onclick="editar_rol()" type="submit" name="usuario_responsable" value="26"><i class="bi bi-check-square"></i> Seleccionar</button></td>
                            </tr>

        
                        </tbody>
                    </table>
                </form>
                </div>
            </div>
            <div class="modal-footer">                
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="concluirModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form class='needs-validation novalidate' method='POST' action="{{ route('concluir_oficialia') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="oficialia_id" id="oficialia_id_input" value="">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Finalización del Proceso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-xs-12 col-sm-12 col-md-12" id="Conrepresentante">
                            <div class="row">
                                
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <h5 class="text-center">Conclusión</h5>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <label for="name">Motivo de Conclusión <span style="color:red;">(*)</span></label>
                                        <textarea name="conclusion" id="conclusion" class="form-control" oninput="this.value = this.value.toUpperCase()" > </textarea>
                                        <div class="invalid-feedback">
                                            La conclusion es obligatoria.
                                        </div>
                                    </div>
                                </div>
                                
                                
                                
                            </div>
                        </div>
                        
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="detallesModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Detalles</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-xs-12 col-sm-12 col-md-12" id="Conrepresentante">
                            <div class="row">
                                
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <h6>Núm. Oficio:
                                            <span id="detalleOficio"></span>
                                        </h6>

                                        <h6>Fecha de registro:
                                            <span id="detalleFecha"></span>
                                        </h6>
                                        
                                        <h6>Fecha término:
                                            <span id="detalleTermino"></span>
                                        </h6>

                                        <h6>Tipo de trámite:
                                            <span id="detalleTipo"></span>
                                        </h6>

                                        <h6>Área de Turno:
                                            <span id="detalleArea"></span>
                                        </h6>

                                        <h6>Dependencia:
                                            <span id="detallePrecedencia"></span>
                                        </h6>
                                    </div>
                                </div>
                                          
                            </div>
                        </div>
                        
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                    </div>
                </div>
            </div>
        </div>

    
@endsection

@push('body_end')
<div id="nuevo_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush

@section('scripts')
    <script src="../public/js/poderes/general.js"></script>
    <script>
        // Inicializar DataTable solo cuando el modal se muestre
        let tablaTurnar;
        document.addEventListener('DOMContentLoaded', function () {
            // === Inicialización del DataTable en modal ===
            const tablaTurnos = document.getElementById('turnarModal');
            
            if (tablaTurnos) {
                tablaTurnos.addEventListener('shown.bs.modal', function () {
                    if (!tablaTurnar) {
                        tablaTurnar = $('#tablaUsuariosTurnar').DataTable({
                            "language": {
                                "search": "Buscar:",
                                "lengthMenu": "Mostrar _MENU_ registros",
                                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                                "infoEmpty": "No hay registros disponibles",
                                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                                "zeroRecords": "No se encontraron resultados",
                                "paginate": {
                                    "first": "Primero",
                                    "last": "Último",
                                    "next": "Siguiente",
                                    "previous": "Anterior"
                                }
                            },
                            "pageLength": 10,
                            "responsive": true,
                            "order": [[0, "asc"]],
                            "columnDefs": [
                                { "orderable": false, "targets": 1 }
                            ]
                        });
                    } else {
                        tablaTurnar.columns.adjust().responsive.recalc();
                    }
                });
            }
            //modal de generar oficialia
            var oficialiaModal = document.getElementById('oficialiaModal');
            if (oficialiaModal) {
                document.body.appendChild(oficialiaModal);

                oficialiaModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var oficialia_id= button.getAttribute('data-id');
                    var modalBodyInput = oficialiaModal.querySelector('#oficialia_id_input');
                    if (modalBodyInput) {
                        modalBodyInput.value = oficialia_id;
                    }
                });
            }
             //modal turnar
            var turnarModal = document.getElementById('turnarModal');
            if (turnarModal) {
                
                document.body.appendChild(turnarModal);

                turnarModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    
                    var oficialia_id = button.getAttribute('data-id');
                    
                    var modalInputId = turnarModal.querySelector('#modal-id');
                    if (modalInputId) {
                        modalInputId.value = oficialia_id;
                    }
                });
            }

            //modal conclusión
            var concluirModal = document.getElementById('concluirModal');
            if (concluirModal) {
                document.body.appendChild(concluirModal);

                concluirModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var oficialia_id= button.getAttribute('data-id');
                    var modalBodyInput = concluirModal.querySelector('#oficialia_id_input');
                    if (modalBodyInput) {
                        modalBodyInput.value = oficialia_id;
                    }
                });
            }
            
            const checkTermino = document.getElementById('check_termino');
            const contenedorInputExtra = document.getElementById('contenedor_input_extra');
            const contenedorInputHora = document.getElementById('contenedor_input_hora');
            const fechaTermino = document.getElementById('fecha_termino');
            const horaTermino = document.getElementById('hora_termino');

            if (checkTermino && contenedorInputExtra && fechaTermino) {
                checkTermino.addEventListener('change', function() {
                    if (this.checked) {
                        // Si esta marcado el check
                        contenedorInputExtra.classList.remove('d-none');
                        fechaTermino.setAttribute('required', 'required');
                        
                    } else {
                        // Si se desmarca
                        contenedorInputExtra.classList.add('d-none');
                        fechaTermino.removeAttribute('required');
                        fechaTermino.value = ''; 
                        
                    }
                });
            }
            if (checkTermino && contenedorInputHora&& horaTermino) {
                checkTermino.addEventListener('change', function() {
                    if (this.checked) {
                        // Si esta marcado el check
                        
                        contenedorInputHora.classList.remove('d-none');
                        horaTermino.setAttribute('required', 'required');
                    } else {
                        // Si se desmarca
                        
                        contenedorInputHora.classList.add('d-none');
                        horaTermino.removeAttribute('required');
                        horaTermino.value = ''; 
                    }
                });
            }

        });  
        
    document.addEventListener('DOMContentLoaded', function () {

        const detallesModal = document.getElementById('detallesModal');

        if (detallesModal) {

            document.body.appendChild(detallesModal);

            detallesModal.addEventListener('show.bs.modal', function (event) {

                const button = event.relatedTarget;

                const oficialia = JSON.parse(button.dataset.oficialia);
                console.log(oficialia);
                document.getElementById('detalleOficio').textContent = oficialia.oficio;
                document.getElementById('detalleFecha').textContent = oficialia.fecha_registro ? oficialia.fecha_registro + ' ' + oficialia.hora_registro: 'Sin fecha';
                document.getElementById('detalleTermino').textContent = oficialia.fecha_termino ? oficialia.fecha_termino + ' ' + oficialia.hora_termino : 'Sin término';
                document.getElementById('detalleTipo').textContent = oficialia.tipo_tramite;
                document.getElementById('detalleArea').textContent = oficialia.area_turno;
                document.getElementById('detallePrecedencia').textContent = oficialia.precedencia;
                
                
                
            });

        }

    });
    </script>
    
@endsection