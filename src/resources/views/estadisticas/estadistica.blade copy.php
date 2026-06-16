@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Estadísticas</h3>
        </div>
        <div class="section-body">
            <?php $fecha_actual = date('d-m-Y');?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Obligatorios</h3>
                            
                            @can('ver-reporte-estadistica')
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
                                <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('seer.mostar')}}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label multiple for="name">Tipo de reporte</label>
                                                <select id="reporte" class="form-control" name="tipo_reporte" required>
                                                    <option value="">Seleccione</option>
                                                    <option value="Cuantificaciones">Resumido</option>
                                                    <option value="Detallado">Detallado</option>
                                                    <option value="Concentrado">Concentrado</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Debes seleccionar un tipo de reporte.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Fecha Inicial</label>
                                                <input type="date" class="form-control" name="fecha_inicial" >
                                                <div class="invalid-feedback">
                                                    La fecha inicial es obligatorio.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Fecha Final</label>
                                                <input type="date" class="form-control" name="fecha_final" >
                                                <div class="invalid-feedback">
                                                    La fecha final es obligatorio.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center">Filtros solicitud</h4>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label multiple for="name">Auxiliar</label>
                                                <select class="form-control" name="auxiliar">
                                                    <option value="">Todos</option>
                                                    @foreach($usuariosauxiliares as $user)
                                                        <option value="{{$user['id']}}">{{$user['name']}}</option>
                                                    @endforeach
                                                </select>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Tipo solicitud</label>
                                                <select  class="form-control" name="tipo_solicitud">
                                                    <option value="">Todos</option>
                                                    <option value="Solictudes">Solictudes</option>
                                                    <option value="Ratificaciones">Ratificaciones</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Tipo de persona</label>
                                                <select  class="form-control" name="tipo_persona">
                                                    <option value="">Todos</option>
                                                    <option value="Solictudes">Física</option>
                                                    <option value="Ratificaciones">Moral</option>
                                                    </select>
                                            </div>
                                        </div>
                                            
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Motivo</label>
                                                <select  class="form-control" name="motivo">
                                                    <option value="">Todos</option>
                                                    <option value="Despido">Despido</option>
                                                    <option value="Pago de prestaciones">Pago de prestaciones</option>
                                                    <option value="Recision de la relación laboral">Recisión de la relación laboral</option>
                                                    <option value="Derecho de preferencia">Derecho de preferencia</option>
                                                    <option value="Derecho de antiguedad">Derecho de antigüedad</option>
                                                    <option value="Derecho de ascesnso">Derecho de ascesnso</option>
                                                    <option value="Terminación voluntaria de relación laboral">Terminación voluntaria de relación laboral</option>  
                                                    <option value="Falta de acuerdo">Falta de acuerdo</select>
                                            </div>
                                        </div>
                                            
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Estatus</label>
                                                <select  class="form-control" name="estatus">
                                                    <option value="">Todos</option>
                                                    <option value="Pendiente">Pendiente</option>
                                                    <option value="Parcial">Parcial</option>
                                                    <option value="Cumplido">Cumplido</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Notificación</label>
                                                <select  class="form-control" name="centro">
                                                    <option value="">Todos</option>
                                                    <option value="Centro">Centro</option>
                                                    <option value="Trabajador">Trabajador</option>
                                                    <option value="Ambos">Ambos</option>
                                                    <option value="Exhorto">Exhorto</option>
                                                </select>
                                            </div>
                                        </div>
                                            


                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center">Filtros audiencias</h4>
                                            </div>
                                        </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Conciliador</label>
                                                    <select  class="form-control" name="conciliador">
                                                        <option value="">Todos</option>
                                                        @foreach($usuariosconciliador as $user)
                                                            <option value="{{$user['id']}}">{{$user['name']}}</option>
                                                        @endforeach
                                                </select>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label multiple for="name">Tipo estatus</label>
                                                    <select  class="form-control" name="tipo_audiencia">
                                                        <option value="">Todos</option>
                                                        <option value="Conciliacion">Convenio</option>
                                                        <option value="No conciliacion">No conciliación</option>
                                                        <option value="Archivado">Archivado</option>
                                                        <option value="Archivado por incomparecencia">Archivado por incomparecencia</option>
                                                        <option value="Regenerada">Reagendada</option>
                                                        <option value="Incompetencia">Incompetencia</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label multiple for="name">Tipo de audiencia</label>
                                                    <select  class="form-control" name="tipo_audiencia">
                                                        <option value="">Todos</option>
                                                        <option value="Presencial">Presencial</option>
                                                        <option value="Via remota">Via remota</option>
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Filtros notificador</h4>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Notificadores</label>
                                                    <select  class="form-control" name="notificador">
                                                        <option value="">Todos</option>
                                                        @foreach($usuariosconciliador as $user)
                                                            <option value="{{$user['id']}}">{{$user['name']}}</option>
                                                        @endforeach
                                                </select>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Filtros generales</h4>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Sede</label>
                                                    <select class="form-control" name="sede">
                                                        <option value="">Todos</option>
                                                        @foreach($estadisticas as $aSport)
                                                            <option value="{{$aSport['id']}}">{{$aSport['nombre']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Número de expediente</label>
                                                    <input type="text" name="nuc" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label multiple for="name">Sexo del solicitante</label>
                                                    <select  class="form-control" name="sexo">
                                                        <option value="">Todos</option>
                                                        <option value="H">Hombres</option>
                                                        <option value="M">Mujeres</option>
                                                        <option value="No Binario">No Binario</option>
                                                        <option value="Prefiero no decir">Prefiero no decir</option>
                                                        <option value="Otro">Otro</option>
                                                    </select>
                                                </div>
                                            </div>

                                            

                                            <div class="col-xs-12 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="password">Estado del solicitante</label>
                                                    <select class="form-control" name="estado_solicitante">
                                                        <option value="">Todos</option>
                                                        @foreach($estados as $est)
                                                            <option value="{{$est['id']}}">{{$est['nombre']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                                
                                            <div class="col-xs-12 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="password">Municipio del solicitante</label>
                                                    <select name="mun_solicitante" class="form-control">
                                                        <option value="">Todos</option>
                                                        @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                                            @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="password">Estado del citado</label>
                                                    <select class="form-control" name="estado_citado">
                                                        <option value="">Todos</option>
                                                        @foreach($estados as $est)
                                                            <option value="{{$est['id']}}">{{$est['nombre']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                                
                                            <div class="col-xs-12 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="password">Municipio del citado</label>
                                                    <select name="mun_citado" class="form-control">
                                                        <option value="">Todos</option>
                                                        @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                                            @endforeach
                                                    </select>
                                                </div>
                                            </div>    
                                        </div>
                                        
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <button type="submit" class="btn btn-primary">PDF</button>
                                        </div>
                                    </div>
                                </form>
                            @endcan
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
    <script src="../public/js/estadistica/estadistica.js"></script>
    <script>
        $('#reporte').change(function(){
            var valorCambiado =$(this).val();
            if((valorCambiado == 'Concentrado')){
                $('#resu_detallado').css('display','none');
                $('#concentrado').css('display','block');
            }
            else{
                $('#resu_detallado').css('display','block');
                $('#concentrado').css('display','none');
            }
        });
    </script>
@endsection