@extends('layouts.app_editar')

@section('content')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Capacitación Módulo</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Agregar Módulo</h3>

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

                            <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                            <form class="needs-validation novalidate" method="POST" action="{{route('capacitaciones.crear_modulo')}}" enctype='multipart/form-data'>
                            @csrf
                            <input type="hidden" name="cap" value="<?=$capacitacion->id?>">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Nombre</label>
                                            <input type="text" class="form-control" name="nombre" required>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <label for="">Introducción</label>
                                            <textarea class="form-control" name="introduccion" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <label for="">Desarrollo</label>
                                            <textarea class="form-control" name="desarrollo" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">  
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label>Documento Anexo</label><br>
                                            <input type="file" name="anexo1" class="form-control" accept=".pdf" required>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label>Documento Anexo</label><br>
                                            <input type="file" name="anexo2" class="form-control" accept=".pdf" required>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label>Documento Anexo</label><br>
                                            <input type="file" name="anexo3" class="form-control" accept=".pdf" required>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label>Documento Anexo</label><br>
                                            <input type="file" name="anexo4" class="form-control" accept=".pdf" required>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label>Documento Anexo</label><br>
                                            <input type="file" name="anexo5" class="form-control" accept=".pdf" required>
                                        </div>
                                    </div>
                                </div>      

                                <div class="row">
                                    <div id="agregar_pregunta"   class="btn btn-secondary" onclick="agregar_pregunta()">Añade Pregunta</div>
                                </div>


                                <div class="row">
                                    <div id="contenedor">
                                        <div id="obra-social-1" class="obra-social">
                                        </div>
                                    </div>
                                </div>                              
                                    <button type="submit" class="btn btn-primary" style="background-color: #6A0F49">Guardar</button>
                            </form>        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <div id="crear_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>

    <script src="../../js/personas/crear.js"></script>
    <script>
         function agregar_pregunta(){
            var contador = $('.obra-social').length + 1;
            var bloque = '<div id="obra-social-' + contador + '" class="row">'+
                '<div class="col-xs-12 col-sm-12 col-md-6"><div class="form-group">'+
                    '<label for="">Pregunta</label>'+
                    '<input type="text" class="form-control" name="pregunta[]" required>'+
                '</div>'+
                '</div>'+
                '<div class="col-xs-12 col-sm-12 col-md-6">'+
                    '<div class="form-group">'+
                        '<label for="">Respuesta 1</label>'+
                        '<input type="text" class="form-control" name="respuesta1[]" required>'+
                    '</div>'+
                '</div>'+
                '<div class="col-xs-12 col-sm-12 col-md-6">'+
                    '<div class="form-group">'+
                        '<label for="">Respuesta 2</label>'+
                        '<input type="text" class="form-control" name="respuesta2[]" required>'+
                    '</div>'+
                '</div>'+
                '<div class="col-xs-12 col-sm-12 col-md-6">'+
                    '<div class="form-group">'+
                        '<label for="">Respuesta 3</label>'+
                        '<input type="text" class="form-control" name="respuesta3[]" required>'+
                    '</div>'+
                '</div>'+
                '<div class="col-xs-12 col-sm-12 col-md-6">'+
                    '<div class="form-group">'+
                        '<label for="">Respuesta 4</label>'+
                        '<input type="text" class="form-control" name="respuesta4[]" required>'+
                    '</div>'+
                '</div>'+
                '<div class="col-xs-12 col-sm-12 col-md-6">'+
                    '<div class="form-group">'+
                        '<label for="">Numero de respuesta correcta</label>'+
                        '<input type="number" class="form-control" name="correcta[]" required>'+
                    '</div>'+
                '</div>';
            $('#contenedor').append(bloque);
        }
    </script>

    <script src="../../public/js/poderes/general.js"></script>
@endsection


