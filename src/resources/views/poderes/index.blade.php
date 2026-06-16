@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Poderes</h3>
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
                                @can('crear-abogado')
                                    <a class="btn btn-warning" href="{{ route('poder-crear') }}" target="_blank">Nuevo</a>
                                @endcan

                                <form action="{{ url()->current() }}" method="GET" style="width: 400px; margin: 0;">
                                    <div class="input-group">
                                        <input type="text" name="buscar" class="form-control" placeholder="Buscar por Folio o Nombre..." value="{{ request('buscar') }}">
                                        <button class="btn btn-primary" type="submit" style="background-color: #4A001F; border-color: #4A001F;">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                        @if(request('buscar'))
                                            <a href="{{ url()->current() }}" class="btn btn-secondary">Limpiar</a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                            
                            @can('ver-abogado')
                                <div class="table-responsive">
                                    <table id="tablaPoderesEstatica" class="table table-striped mt-2" style="width:100%">
                                        <thead style="background-color: #4A001F;">
                                            <tr>
                                                <th style="color: #fff;">Folio</th>
                                                <th style="color: #fff;">Nombre / Razón Social</th>
                                                <th style="color: #fff;">RFC</th>
                                                <th style="color: #fff;">Representante Legal</th>
                                                <th style="color: #fff;">Estatus</th>
                                                <th style="color: #fff;">Expediente Digital</th>
                                                <th style="color: #fff;">Acciones</th>
                                                <th style="color: #fff;">Eliminar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($poderesIniciales as $poder)
                                                @php
                                                    $esSuperUsuario = (isset($userRole[0]) && $userRole[0] === "Super Usuario");
                                                @endphp
                                                <tr>
                                                    <td>{{ $poder->idAbogado }}</td>
                                                    <td>{{ $poder->nombre_patronal_combinado }}</td>
                                                    <td>{{ $poder->rfc_patronal ?? 'N/A' }}</td>
                                                    <td>{{ $poder->nombre_representante_combinado }}</td>
                                                    <td>{!! $poder->estatus_badge !!}</td>
                                                    <td>{!! $poder->documentos_modal_btn !!}</td>
                                                    <td>
                                                        <div class="d-flex gap-1 align-items-center">
                                                            <div class="d-flex flex-column gap-1">
                                                                <a class="btn btn-sm btn-warning" href="{{ route('poderes.edit', $poder->idAbogado) }}" onclick="editar_poder();"><i class="bi bi-pencil"></i> Editar</a>
                                                                @if($esSuperUsuario)
                                                                    <a class="btn btn-sm btn-secondary" href="{{ route('poderes.history', $poder->idAbogado) }}"><i class="bi bi-clock-history"></i> Historial</a>
                                                                @endif
                                                            </div>
                                                            @if (auth()->user()->can('editar-abogado'))
                                                                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal1" data-id="{{ $poder->idAbogado }}" data-tipo="{{ $poder->tipo }}"><i class="bi bi-person-plus"></i> Agregar</a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($esSuperUsuario)
                                                            @can('borrar-abogado')
                                                                <form method="POST" action="{{ route('poderes.destroy', $poder->idAbogado) }}" class="form-eliminar-poder">
                                                                    @csrf
                                                                    <input type="hidden" name="_method" value="DELETE">
                                                                    <button class="btn btn-sm btn-danger" type="submit">Borrar</button>
                                                                </form>
                                                            @endcan
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form class='needs-validation novalidate' method='POST' action="{{ route('poderes.agregar_representante') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="idAbogado" id="idAbogado_input" value="">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Agregar Representante</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-xs-12 col-sm-12 col-md-12" id="Conrepresentante">
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Información del Representante Legal</h5>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center">Datos de identificación</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Nombre(s) del representante <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="nombre_representante_pF" id="nombre_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primer_representante_pF" id="primer_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="segundo_representante_pF" id="segundo_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El segundo apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">CURP<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control"  aria-label="CURP" name="curp_representante_pF" id="curp_representante_pF" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La CURP es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                                        <select name="sexo_representante_pF" id="sexo_representante_pF" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Femenino">Femenino</option>
                                                            <option value="Masculino">Masculino</option>
                                                            <option value="Prefiero no responder">Prefiero no responder</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                   <div class="form-group">
                                                        <h5 class="text-center">Datos de contacto</h5>
                                                    </div>
                                                </div> 

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Correo electrónico <span style="color:red;">(*)</span></label>
                                                        <input type="email" class="form-control" name="correo_representante_pF" id="correo_representante_pF" >
                                                        <div class="invalid-feedback">
                                                            El Correo electrónico es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Teléfono <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control"  name="telefono_representante_pF" id="telefono_representante_pF" maxlength="10" pattern="[0-9]+" >
                                                        <div class="invalid-feedback">
                                                            El telefono es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Datos de la documentación que acredite la personeria</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-4">  
                                                    <div class="form-group">
                                                        <label for="name">Tipo de documento <span style="color:red;">(*)</span></label>
                                                        <select name="tipo_documento_pF" id="tipo_documento_pF" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Carta Poder">Carta Poder</option>
                                                                <option value="Instrumento Notarial">Instrumento Notarial</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Fecha expedición <span style="color:red;">(*)</span></label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_expedicion_pF" id="fecha_expedicion_pF" >
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-2">
                                                    <div class="form-check mt-4">
                                                        <input name="sin_fecha_vigencia_pF" type="checkbox" class="form-check-input" id="check_vigencia" autocomplete="off">
                                                        <label class="form-check-label" for="check_vigencia">Sin fecha de vigencia</label>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3" id="fecha_vigencia_pF_container">
                                                    <div class="form-group">
                                                        <label for="fecha_vigencia_pF">Fecha vigencia</label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_vigencia_pF" id="fecha_vigencia_pF" min="<?= date("Y-m-d") ?>" >
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label for="">Descripción del documento que acredite la personaria <span style="color:red;">(*)</span></label>
                                                        <textarea class="form-control" aria-describedby="basic-addon1" name="descripcion_pF" id="descripcion_pF" 
                                                        placeholder="Ejemplo: Carta poder simple de fecha___, firmada ante dos testigos, suscrita a favor del compareciente por el (C., Lic., Ing., etc.,)_____, en cuanto ___ de la moral citada, personalidad que acredite en terminos de___ número(45 Cuarenta y Cinco), de fecha___, pasada ante la fe del(Lic., Mtro., etc.,)___, Notario Público Número ___, del Estado de ____, y cuyas facultades no han sido revocadas ni mofificadas a la fecha."></textarea>
                                                        <div class="invalid-feedback">
                                                            La descripción es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Identificación Oficial  <span style="color:red;">(*)</span></label>
                                                        <select id="tipo_identificacion_pFCR" name="tipo_identificacion_pFCR" class="form-control">
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
                                                        <div class="invalid-feedback">
                                                            Este campo identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                    <div class="form-group">
                                                        <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                        <input type="text" name="num_identificacion_pFCR" id="num_identificacion_pFCR" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                        <div class="invalid-feedback">
                                                            El campo núm. de identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Cargar Documentos</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6" id="div_acta_constitutiva" style="display:none;">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Acta Constitutiva</label><br>
                                                        <input type="file" name="documentoActa_Moral" id="documentoActa_Moral" class="form-control" accept=".pdf" >
                                                        <div class="invalid-feedback">
                                                            El acta constitutiva es obligatoria para personas morales.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Identificación del Representante Legal</label><br>
                                                        <input type="file" name="documentoRepresentacion_pF" id="documentoRepresentacion_pF" class="form-control" accept=".pdf" >
                                                        <div class="invalid-feedback">
                                                            El documento de representación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Documento que acredite la personería</label><br>
                                                        <input type="file" name="documentoPoder_pF" id="documentoPoder_pF" class="form-control" accept=".pdf">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>Anexo (Documentos Complementarios)</label><br>
                                                        <input type="file" name="documentoAnexo_pF" id="documentoAnexo_pF" class="form-control" accept=".pdf">
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div align="center">
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
    <div class="modal fade" id="modalExpedienteDigital" tabindex="-1" aria-labelledby="modalExpedienteLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content" style="box-shadow: 0 5px 15px rgba(0,0,0,.5);">
                <div class="modal-header" style="background-color: #4A001F; color: white;">
                    <h5 class="modal-title" id="modalExpedienteLabel">Documentos del Representante</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3"><strong>Representante:</strong> <span id="expediente_nombre_abogado" class="text-muted"></span></p>
                    
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-envelope-paper text-primary"></i> Carta Poder</span>
                            <div id="wrapper_cartapoder"></div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-journal-bookmark text-primary"></i> Cédula Profesional</span>
                            <div id="wrapper_cedula"></div>
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
                            <span><i class="bi bi-card-heading text-primary"></i> Identificación Oficial</span>
                            <div id="wrapper_ine"></div>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

<div id="nuevo_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="../public/js/poderes/general.js"></script>
    <script>
        var exampleModal1 = document.getElementById('exampleModal1')
        if (exampleModal1) {
            exampleModal1.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget
                
                var idAbogadoReq = button.getAttribute('data-id')
                var tipoAbogadoReq = button.getAttribute('data-tipo')
                
                var modalBodyInput = exampleModal1.querySelector('#idAbogado_input')
                modalBodyInput.value = idAbogadoReq
                
                var divActa = document.getElementById('div_acta_constitutiva');
                var inputActa = document.getElementById('documentoActa_Moral');
                
                if(tipoAbogadoReq === 'Moral') {
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

                if (checkVigencia && divFechaVigencia && inputFechaVigencia) {
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
                }
            })
        }
        $(document).ready(function() {
            // Mover el modal al final del body para evitar conflictos de opacidad (Página en gris)
            if ($('#modalExpedienteDigital').length) {
                $('#modalExpedienteDigital').appendTo("body");
            }

            if ($.fn.DataTable.isDataTable('#tablaPoderesServerSide')) {
                $('#tablaPoderesServerSide').DataTable().destroy();
            }

            $('#tablaPoderesServerSide').DataTable({
                "processing": true,
                "serverSide": true,
                "pageLength": 10,
                "searching": true,
                "ordering": false,
                "deferLoading": 10,
                "ajax": {
                    "url": "{{ route('poderes.index.ajax') }}",
                    "type": "GET"
                },
                "columnDefs": [
                    {
                        "targets": [4, 5, 6, 7],
                        "render": function (data, type, row) {
                            return data ? data : ''; 
                        }
                    }
                ],
                "language": {
                    "processing": "Consultando base de datos...",
                    "search": "Buscar Abogado o Razón Social:"
                }
            });

            $(document).on('click', '.btn-ver-expediente', function(e) {
                e.preventDefault();
                
                // 1. Recuperamos el ID del abogado del botón clickeado
                let idAbogado = $(this).data('id') || $(this).attr('data-id'); 
                let abogado = $(this).data('abogado');
                let ine = $(this).data('ine');
                let cedula = $(this).data('cedula');
                let rep = $(this).data('representacion');
                let carta = $(this).data('cartapoder');
                let registro = $(this).data('registro');

                $('#expediente_nombre_abogado').text(abogado);

                // 2. Modificamos la función constructora para inyectar la subcarpeta del ID si no viene completa
                function buildLink(url, fallbackText = 'S/D') {
                    if (!url || url === '') return '<span class="text-muted fw-semibold">' + fallbackText + '</span>';
                    if (url === 'S/A') return '<span class="text-muted fw-semibold">S/A</span>';
                    
                    let finalUrl = url;
                    // Si la URL no contiene ya la subcarpeta con el ID, la concatenamos dinámicamente
                    // Esto asume que 'url' es solo el nombre del archivo o una ruta base modificable
                    if (idAbogado && !url.includes('/' + idAbogado + '/')) {
                        // Ajusta 'documento_abogados' por el nombre de tu directorio si difiere en la URL pública
                        finalUrl = `/ver-documento-abogado/${idAbogado}/${url}`;
                        //finalUrl = `../storage/documento_abogados/${idAbogado}/${url}`; 
                    }

                    return '<a href="' + finalUrl + '" class="btn btn-xs btn-outline-danger py-0 px-2" target="_blank"><i class="bi bi-file-pdf"></i> PDF</a>';
                }

                $('#wrapper_ine').html(buildLink(ine));
                $('#wrapper_cedula').html(buildLink(cedula));
                $('#wrapper_representacion').html(buildLink(rep));
                $('#wrapper_cartapoder').html(buildLink(carta, 'S/D'));

                if (registro && registro !== '') {
                    let finalRegistro = registro;
                    if (idAbogado && !registro.includes('/' + idAbogado + '/')) {
                        finalRegistro = `../storage/documento_abogados/${idAbogado}/${registro}`;
                    }
                    $('#li_registro').show();
                    $('#wrapper_registro').html('<a href="' + finalRegistro + '" class="btn btn-xs btn-success py-0 px-2" target="_blank"><i class="bi bi-printer"></i> Imprimir</a>');
                } else {
                    $('#li_registro').hide();
                }
            });

            $(document).on('submit', '.form-eliminar-poder', function(e) {
                e.preventDefault(); // Detiene el envío inmediato del formulario
                
                let formulario = this;

                // Opción A: Si utilizas SweetAlert2 en el proyecto
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
                    }).then((result) => {
                        if (result.isConfirmed) {
                            formulario.submit(); // Envía el formulario real a la ruta destroy
                        }
                    });
                } 
                // Opción B: Respaldo nativo si no está cargado SweetAlert2
                else {
                    if (confirm("¿Está seguro de que desea eliminar este poder? Esta acción no se puede revertir.")) {
                        formulario.submit();
                    }
                }
            });
        });
    </script>
@endsection
