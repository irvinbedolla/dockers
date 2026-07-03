@extends('layouts.app_editar')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Estadisticas</h3>
        </div>
        <div class="section-body">
            <?php $fecha_actual = date('d-m-Y');?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Editar Solicitud</h3>
                            
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
                            {!! Form::model($userRole, ['method' => 'PATCH', 'files' => true, 'route' => ['persona.update', $persona ,$persona->id], 'class' => 'needs-validation','novalidate' ]) !!}
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Número unico de identificación</label>
                                        <input type="text" class="form-control" value="{{ $persona->NUE }}" name="NUE" oninput="this.value = this.value.toUpperCase()" required>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Solicitante</label>
                                        <input type="text" class="form-control" value="{{ $persona->solicitante }}" name="solicitante"  oninput="this.value = this.value.toUpperCase()" required>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="">Actividad economica</label>
                                        <input type="text" class="form-control" value="{{ $persona->actividad_economica }}" name="actividad_economica"  oninput="this.value = this.value.toUpperCase()" required>
                                    </div>
                                </div>                        

                                
                                                                 
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                {!! Form::close() !!}
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
    <script src="../../public/js/estadistica/estadistica.js"></script>
@endsection

