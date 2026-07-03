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

                            @if($estatus == "En curso" || $estatus == "En prueba")
                                {!! Form::open(array('route'=>'miscapacitaciones.guardar_respuestas', 'method'=>'POST','class' => 'needs-validation','novalidate')) !!}
                                    <input type="hidden" name="cap" value="{{$capacitacion->id}}">
                                    <input type="hidden" name="mod" value="{{$mod}}">
                                    <div class="row">
                                        @php $contador = 0; @endphp
                                        @foreach($encuestas as $encuesta)
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <label for="name">Pregunta.</label>
                                                {!! Form::text('nombre', $encuesta->pregunta , array('class'=>'form-control', 'readonly')) !!}
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="respuesta{{$contador}}" id="flexRadioDefault1" value="1">
                                                    <label class="form-check-label" for="flexRadioDefault1">
                                                        {{ $encuesta->respuesta1 }}
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="respuesta{{$contador}}" id="flexRadioDefault1" value="2">
                                                    <label class="form-check-label" for="flexRadioDefault1">
                                                        {{ $encuesta->respuesta2 }}
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="respuesta{{$contador}}" id="flexRadioDefault1" value="3">
                                                    <label class="form-check-label" for="flexRadioDefault1">
                                                        {{ $encuesta->respuesta3 }}
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="respuesta{{$contador}}" id="flexRadioDefault1" value="4">
                                                    <label class="form-check-label" for="flexRadioDefault1">
                                                        {{ $encuesta->respuesta4 }}
                                                    </label>
                                                </div>
                                            </div>
                                            @php $contador++; @endphp
                                            <br>
                                        @endforeach
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div>

                                {!! Form::close() !!}
                            @endif
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