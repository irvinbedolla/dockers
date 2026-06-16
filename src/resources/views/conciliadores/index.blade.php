@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Usuarios</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example" class="table-striped" style="width:100%">
                                        <thead style="background-color: #4A001F;">
                                            <th style="display: none;">ID</th>
                                            <th style="color: #fff;">Nombre</th>
                                            <th style="color: #fff;">Acciones</th>
                                        </thead>
                                        <tbody class="contenidobusqueda">
                                            @foreach($conciliadores as $usuario)
                                                <tr>
                                                    <td style="display: none;">{{$usuario->id}}</td>
                                                    <td>{{$usuario->name}}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-info open-modal" data-id="{{ $usuario->id }}" data-bs-toggle="modal" data-bs-target="#modalAgregarCitados">Permisos</button>                                                        
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('modalAgregarCitados');
            if (!modalEl) return;

            modalEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button?.getAttribute('data-id') || '';
                const input = document.getElementById('modal-id');
                if (input) input.value = id;
            });
        });
    </script>
    <script src="../public/js/usuarios/usuarios.js"></script>
@endsection