@extends('layouts.app_editar1')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Mis capacitaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @if (\Session::has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <ul>
                                        <li>{!! \Session::get('success') !!}</li>
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <a class="btn btn-info" href="{{  route('miscapacitaciones.edit', $modulos->id_cap) }}"> Regresar</a>
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <label for="name">Nombre del modulo.</label>
                                    {!! Form::text('nombre', $modulos->nombre , array('class'=>'form-control', 'readonly')) !!}
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <label for="name">Introducción.</label>
                                    <textarea type="text" class="form-control" name="" readonly>{{ $modulos->introduccion }}</textarea>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <label for="name">Desarrollo.</label>
                                    <textarea type="text" class="form-control" name="" readonly>{{ $modulos->desarrollo }}</textarea>
                                </div>
                            </div>
                            <div class="row">.</div>
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <label for="name">Documentación del módulo.</label><br>
                                    @php
                                        if($modulos->anexo1 == null){
                                            echo "";
                                        }else{ 
                                            echo "$modulos->anexo1 : <a target='_blank' class='btn btn-info' href='../../../storage/app/documentos_modulo/$modulos->anexo1'>Descargar</a><br>";
                                        }
                                        if($modulos->anexo2 == null){
                                            echo "";
                                        }else{ 
                                            echo "$modulos->anexo2 : <a target='_blank' class='btn btn-info' href='../../../storage/app/documentos_modulo/$modulos->anexo2'>Descargar</a><br>";
                                        }
                                        if($modulos->anexo3 == null){
                                            echo "";
                                        }else{ 
                                            echo "$modulos->anexo3 : <a target='_blank' class='btn btn-info' href='../../../storage/app/documentos_modulo/$modulos->anexo3'>Descargar</a><br>";
                                        }
                                        if($modulos->anexo4 == null){
                                            echo "";
                                        }else{ 
                                            echo "$modulos->anexo4 : <a target='_blank' class='btn btn-info' href='../../../storage/app/documentos_modulo/$modulos->anexo4'>Descargar</a><br>";
                                        }
                                        if($modulos->anexo5 == null){
                                            echo "";
                                        }else{ 
                                            echo "$modulos->anexo5 : <a target='_blank' class='btn btn-info' href='../../../storage/app/documentos_modulo/$modulos->anexo5'>Descargar</a><br>";
                                        }
                                    @endphp
                                </div>
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

<div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>


@section('scripts')
    <script src="../../../public/js/estadistica/estadistica.js"></script>
@endsection