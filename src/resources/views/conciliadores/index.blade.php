@extends('layouts.app')
@section('title', 'Usuarios')

@section('content')
    <section class="section">
        

        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="page__heading mb-0">Conciliadores</h3>
            
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                                <div class="table-responsive">
                                    @can('conciliadores_permisos')
                                        <table id="example" class="table-striped" style="width:100%">
                                            <thead style="background-color: #354647;">
                                                <th style="display: none;">ID</th>
                                                <th class="text-center text-white" style="color: #ffffff !important;">Nombre</th>
                                                <th class="text-center text-white" style="color: #ffffff !important;">Acciones</th>
                                            </thead>
                                            <tbody class="contenidobusqueda">
                                                @foreach($conciliadores as $usuario)
                                                    <tr>
                                                        <td style="display: none;">{{$usuario->id}}</td>
                                                        <td>{{$usuario->name}}</td>
                                                        <td class="text-center text-white">
                                                            @php
                                                                $permisoUsuario = $permisos->firstWhere('id_conciliador', $usuario->id);
                                                            @endphp
                                                            @can('conciliadores_permisos_consultar')<button type="button" class="btn btn-success open-modal btn-sm" data-id="{{ $usuario->id }}" data-permiso="{{ $permisoUsuario ? json_encode($permisoUsuario) : '{}' }}" data-bs-toggle="modal" data-bs-target="#modalRevisarPermisos"><i class="bi bi-eye-fill"></i> Revisar Permisos</button>@endcan                                                            
                                                            @can('conciliadores_permisos_editar')<button type="button" class="btn btn-info open-modal btn-sm" data-id="{{ $usuario->id }}" data-permiso="{{ $permisoUsuario ? json_encode($permisoUsuario) : '{}' }}" data-bs-toggle="modal" data-bs-target="#modalAgregarCitados"><i class="bi bi-pencil"></i> Editar Permisos</button>@endcan                                                                                                
                                                            @can('conciliadores_permisos_crear')<button type="button" class="btn btn-primary open-modal btn-sm"  data-id="{{ $usuario->id }}" style="background-color: #CEA845; border-color: #CEA845; color: #fff;" data-bs-toggle="modal" data-bs-target="#modalAgregarCitados"><i class="bi bi-plus-lg"></i> Crear Permisos</button>@endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endcan
                                </div>
                            <!-- Centramos la paginación a la derecha-->
                            <div class="pagination justify-content-end">
                               
                            </div>                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

    <div id="nuevo_usuario" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>
    <div class="modal fade" id="modalAgregarCitados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form class='needs-validation novalidate'  method='POST' action="{{route('conciliadores_permisos')}}">
            @csrf
            <input type="hidden" name="id" id="modal-id" value="">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Permisos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <h4>Tipo</h4>
                            <div class="col-xs-12 col-sm-12 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo" id="radioTipoPresencial" value="Precencial">
                                    <label class="form-check-label" for="checkDefault">
                                        Precencial
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo" id="radioTipoVirtual" value="Virtual">
                                    <label class="form-check-label" for="checkDefault">
                                        Virtual
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo" id="radioTipoAmbos" value="Ambos">
                                    <label class="form-check-label" for="checkDefault">
                                        Ambos
                                    </label>
                                </div>
                            </div>
                            <h4>Horario</h4>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input form-contro" type="checkbox" name="Lunes">
                                    <label class="form-check-label" for="checkDefault">
                                        Lunes
                                    </label>
                                </div>
                                <label>Inicio</label>
                                <input type="time" name="horario_lunes_inicio" class="form-control">
                                <label>Final</label>
                                <input type="time" name="horario_lunes_final" class="form-control">
                            </div><br>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input form-contro" type="checkbox" name="Martes">
                                    <label class="form-check-label" for="checkDefault">
                                        Martes
                                    </label>
                                </div>
                                <label>Inicio</label>
                                <input type="time" name="horario_martes_inicio" class="form-control">
                                <label>Final</label>
                                <input type="time" name="horario_martes_final" class="form-control">
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input form-contro" type="checkbox"  name="Miercoles">
                                    <label class="form-check-label" for="checkDefault">
                                        Miercoles
                                    </label>
                                </div>
                                <label>Inicio</label>
                                <input type="time" name="horario_miercoles_inicio" class="form-control">
                                <label>Final</label>
                                <input type="time" name="horario_miercoles_final" class="form-control">
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input form-contro" type="checkbox" name="Jueves">
                                    <label class="form-check-label" for="checkDefault">
                                        Jueves
                                    </label>
                                </div>
                                <label>Inicio</label>
                                <input type="time" name="horario_jueves_inicio" class="form-control">
                                <label>Final</label>
                                <input type="time" name="horario_jueves_final" class="form-control">
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input form-contro" type="checkbox" name="Viernes">
                                    <label class="form-check-label" for="checkDefault">
                                        Viernes
                                    </label>
                                </div>
                                <label>Inicio</label>
                                <input type="time" name="horario_viernes_inicio" class="form-control">
                                <label>Final</label>
                                <input type="time" name="horario_viernes_final" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <!-- Modal para REVISAR Permisos (Solo lectura) -->
<div class="modal fade" id="modalRevisarPermisos" tabindex="-1" aria-labelledby="revisarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #354647; color: white;">
                <h5 class="modal-title" id="revisarModalLabel">Detalle de Permisos y Horario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h5 class="text-primary">Tipo de Atención: <span id="view-tipo" class="badge bg-secondary">Sin asignar</span></h5>
                </div>
                
                <h5 class="mb-3">Horario Asignado</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Día</th>
                                <th>¿Asignado?</th>
                                <th>Hora Inicio</th>
                                <th>Hora Final</th>
                            </tr>
                        </thead>
                        <tbody id="view-horarios-body">
                            <!-- El contenido se inyectará con JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            //Script del modal editar
            const modalEditar = document.getElementById('modalAgregarCitados');
            if (modalEditar) {
                modalEditar.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const id = button?.getAttribute('data-id') || '';
                    const permisoData = button?.getAttribute('data-permiso'); 
                    
                    const inputId = document.getElementById('modal-id');
                    if (inputId) inputId.value = id;

                    const form = modalEditar.querySelector('form');
                    if(form) form.reset();

                    if (permisoData && permisoData !== '{}') {
                        const permiso = JSON.parse(permisoData);

                        if (permiso.tipo) {
                            const tipoRadio = form.querySelector(`input[name="tipo"][value="${permiso.tipo}"]`);
                            if (tipoRadio) tipoRadio.checked = true;
                        }

                        const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                        dias.forEach(dia => {
                            const diaCapitalizado = dia.charAt(0).toUpperCase() + dia.slice(1);
                            const checkbox = form.querySelector(`input[name="${diaCapitalizado}"]`);
                            if (checkbox) checkbox.checked = (permiso[dia] === 'Si');

                            const inputInicio = form.querySelector(`input[name="horario_${dia}_inicio"]`);
                            const inputFinal = form.querySelector(`input[name="horario_${dia}_final"]`);
                            if (inputInicio) inputInicio.value = permiso[`${dia}_inicio`] || '';
                            if (inputFinal) inputFinal.value = permiso[`${dia}_final`] || '';
                        });
                    }
                });
            }

            //Script par modal revisar
            const modalRevisar = document.getElementById('modalRevisarPermisos');
            if (modalRevisar) {
                modalRevisar.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const permisoData = button?.getAttribute('data-permiso');
                    
                    const viewTipo = document.getElementById('view-tipo');
                    const tbody = document.getElementById('view-horarios-body');
                    
                    // Limpiar la tabla antes de cargar
                    tbody.innerHTML = '';
                    viewTipo.textContent = 'Sin asignar';
                    viewTipo.className = 'badge bg-secondary';

                    if (permisoData && permisoData !== '{}') {
                        const permiso = JSON.parse(permisoData);
                        
                        // Mostrar el tipo
                        if (permiso.tipo) {
                            viewTipo.textContent = permiso.tipo;
                            viewTipo.className = 'badge bg-success'; // Color verde si hay dato
                        }

                        // Días de la semana para iterar
                        const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                        
                        dias.forEach(dia => {
                            const diaCapitalizado = dia.charAt(0).toUpperCase() + dia.slice(1);
                            const asignado = permiso[dia] === 'Si';
                            
                            
                            const inicio = permiso[`${dia}_inicio`] ? permiso[`${dia}_inicio`] : '--:--';
                            const final = permiso[`${dia}_final`] ? permiso[`${dia}_final`] : '--:--';
                            
                            // Crear la fila 
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td class="fw-bold">${diaCapitalizado}</td>
                                <td>
                                    ${asignado 
                                        ? '<span class="badge bg-primary">Sí</span>' 
                                        : '<span class="badge bg-danger">No</span>'}
                                </td>
                                <td>${asignado ? inicio : '--:--'}</td>
                                <td>${asignado ? final : '--:--'}</td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        // Si no hay permisos en la BD para este usuario
                        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">El usuario aún no tiene permisos u horarios registrados.</td></tr>`;
                    }
                });
            }
        });
    </script>
    <script src="../public/js/usuarios/usuarios.js"></script>
@endsection