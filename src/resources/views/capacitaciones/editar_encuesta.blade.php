@extends('layouts.app_editar1')

@section('content')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Capacitación</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Editar Resultados</h3>

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
                            <form class="needs-validation novalidate" method="POST" action="{{route('crear_capacitacion')}}">
                                @csrf
                                <input type="hidden" name="cot" value="<?=$modulo->id_cap?>">
                                <input type="hidden" name="mod" value="<?=$modulo->id_modulo?>">
                                @foreach($encuestas as $encuesta)
                                    <div class="row">
                                        <label for="">Cuestionario</label>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-6"><div class="form-group">
                                            <label for="">Pregunta</label>
                                            <input type="text" class="form-control" name="pregunta[]" value="{{$encuesta->pregunta}}" required>
                                        </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">Respuesta 1</label>
                                                <input type="text" class="form-control" name="respuesta1[]" value="{{$encuesta->respuesta1}}" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">Respuesta 2</label>
                                                <input type="text" class="form-control" name="respuesta2[]" value="{{$encuesta->respuesta2}}" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">Respuesta 3</label>
                                                <input type="text" class="form-control" name="respuesta3[]" value="{{$encuesta->respuesta3}}" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">Respuesta 4</label>
                                                <input type="text" class="form-control" name="respuesta4[]" value="{{$encuesta->respuesta4}}" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">Numero de respuesta correcta</label>
                                                <input type="number" class="form-control" name="correcta[]" value="{{$encuesta->correcta}}" required>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach


                                <div class="row">
                                    <div id="contenedor">
                                        <div id="obra-social-1" class="obra-social">
                                        </div>
                                    </div>
                                </div>

                                <div id="agregar_pregunta"   class="btn btn-secondary" onclick="agregar_pregunta()">Añade Pregunta</div>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <td><a class="btn btn-info" href="{{ route('capacitaciones.modulos', $modulo->id_cap)}}">Regresar</a></td>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="/js/personas/crear.js"></script>
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
@endsection