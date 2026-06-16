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
                                <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('seer.mostar')}}" target="_blank">
                                    @csrf
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label multiple for="name">Tipo de reporte</label>
                                                <select id="reporte" class="form-control" name="tipo_reporte" required>
                                                    <option value="">Seleccione</option>
                                                    <option value="Audiencias">Audiencias</option>
                                                    <option value="Cumplimientos">Cumplimientos</option>
                                                    <option value="Ratificaciones">Ratificaciones</option>
                                                    <option value="Notificaciones">Notificaciones</option>
                                                    <option value="Solicitudes">Solicitudes</option>
                                                    <option value="Convenios">Convenios</option>
                                                    <option value="CumplimientosGrafica">Graficas</option>
                                                    <option value="Productividad">Productividad</option>
                                                    <option value="EstadisticaMexico">INEGI</option> 
                                                    <option value="Motivos">Motivos</option>
                                                    <!--<option value="Concentrado">General</option>-->
                                                    <option value="GeneralSede">General por Sede</option>
                                                    <option value="AudienciaConciliador">Audiencias por Conciliador</option>
                                                    <option value="CumplimientosProgramados">Cumplimientos Programados</option>
                                                    @if($userRole == "Super Usuario" || $userRole == "Estadisticas" || $userRole == "Administrador" )
                                                        <option value="SeguroSocial">Seguro Social</option>
                                                        <option value="ReporteMunicipio">Reporte por Municipios</option>
                                                        <option value="ReporteActividad">Reporte por Actividad</option>
                                                    @endif
                                                </select>
                                                <div class="invalid-feedback">
                                                    Debes seleccionar un tipo de reporte.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Fecha Inicial</label>
                                                <input type="date" class="form-control" name="fecha_inicial" required>
                                                <div class="invalid-feedback">
                                                    La fecha inicial es obligatorio.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Fecha Final</label>
                                                <input type="date" class="form-control" name="fecha_final" required>
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
                                                <label for="name">Sede</label>
                                                <select class="form-control" name="sede" required>
                                                    <option value="">Seleccione</option>
                                                    @if($userRole == "Super Usuario" || $userRole == "Estadisticas")
                                                        <option value="Todos">Todos</option>
                                                    @elseif($userRole == "Delegado" || $userRole == "Enlace")
                                                        <option value="TodosDelegado">Todos</option>
                                                    @endif
                                                    @foreach($estadisticas as $aSport)
                                                        <option value="{{$aSport['nombre']}}">{{$aSport['nombre']}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div id="reporte-notificador"  style="display:none">
                                        {{--
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Auxiliar</label>
                                                    <select class="form-control" name="auxiliar">
                                                        <option value="Todos">Todos</option>
                                                        @foreach($usuariosauxiliares as $aux)
                                                            <option value="{{$aux['id']}}">{{$aux['name']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        --}}
                                        <input type="hidden" name="auxiliar" value="Todos">
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Notificador</label>
                                                    <select class="form-control" name="notificador">
                                                        <option value="Todos">Todos</option>
                                                        @foreach($usuariosnotificadores as $not)
                                                            <option value="{{$not['id']}}">{{$not['name']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="reporte-audiencias"  style="display:none">
                                            <input type="hidden" name="conciliador" value="Todos">
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Conciliador</label>
                                                    <select class="form-control" name="conciliador">
                                                        <option value="Todos">Todos</option>
                                                        @foreach($usuariosconciliadores as $conc)
                                                            <option value="{{$conc['id']}}">{{$conc['name']}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="Excel-PDF" style="display:none">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <button type="submit" name="tipo" value="1" class="btn btn-primary">PDF</button>
                                                <button type="submit" name="tipo" value="2" class="btn btn-success">Excel</button>
                                            </div>
                                        </div>
                                        <div id="PDF" style="display:none">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <button type="submit" name="tipo" value="1" class="btn btn-primary">PDF</button>
                                            </div>
                                        </div>
                                        <div id="Excel" style="display:none">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <button type="submit" name="tipo" value="2" class="btn btn-success">Excel</button>
                                            </div>
                                        </div>
                                        <div id="Grafica" style="display:none">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <button type="submit" name="tipo" value="3" class="btn btn-success">Grafica</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            
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
    <script src="../public/assets/js/estadistica/estadistica.js"></script>
    <script>
        $('#reporte').change(function(){
            var valorCambiado =$(this).val();
            if((valorCambiado == 'Cumplimientos')){
                $('#PDF').css('display','none');
                $('#Excel').css('display','none');
                $('#Excel-PDF').css('display','block');
                $('#reporte-notificador').css('display','none');
                 $('#reporte-audiencias').css('display','none');
                $('#Grafica').css('display','none');
            }
            else if(valorCambiado == "Productividad" || valorCambiado == "ReporteMunicipio" || valorCambiado == "ReporteActividad"){
                $('#PDF').css('display','block');
                $('#Excel').css('display','none');
                $('#Excel-PDF').css('display','none');
                $('#reporte-notificador').css('display','none');
                 $('#reporte-audiencias').css('display','none');
                $('#Grafica').css('display','none');
            }
            else if(valorCambiado == "CumplimientosGrafica"){
                $('#PDF').css('display','none');
                $('#Excel').css('display','none');
                $('#Excel-PDF').css('display','none');
                $('#reporte-notificador').css('display','none');
                 $('#reporte-audiencias').css('display','none');
                $('#Grafica').css('display','block');
            }
            else if(valorCambiado == "Ratificaciones" || valorCambiado == "Convenios" || valorCambiado == "AudienciaConciliador" || valorCambiado == "Motivos" || valorCambiado == "CumplimientosProgramados" || valorCambiado == "SeguroSocial"){
                $('#PDF').css('display','none');
                $('#Excel').css('display','block');
                $('#Excel-PDF').css('display','none');
                $('#reporte-notificador').css('display','none');
                 $('#reporte-audiencias').css('display','none');
                $('#Grafica').css('display','none');
            }
            else if(valorCambiado == "RatificacionesUsuario"){
                $('#PDF').css('display','block');
                $('#Excel').css('display','none');
                $('#Excel-PDF').css('display','none');
                $('#reporte-notificador').css('display','none');
                 $('#reporte-audiencias').css('display','none');
                $('#Grafica').css('display','none');
            }
            else if(valorCambiado == "RatificacionesGraficas"){
                $('#PDF').css('display','none');
                $('#Excel').css('display','none');
                $('#Excel-PDF').css('display','none');
                $('#reporte-notificador').css('display','none');
                 $('#reporte-audiencias').css('display','none');
                $('#Grafica').css('display','block');
            }
            else if(valorCambiado == "Notificaciones"){
                $('#PDF').css('display','none');
                $('#Excel').css('display','block');
                $('#Excel-PDF').css('display','none');
                $('#reporte-notificador').css('display','block');
                 $('#reporte-audiencias').css('display','none');
                $('#Grafica').css('display','none');
            }
            else if(valorCambiado == "Solicitudes"){
                $('#PDF').css('display','none');
                $('#Excel').css('display','block');
                $('#Excel-PDF').css('display','none');
                $('#reporte-notificador').css('display','none');
                 $('#reporte-audiencias').css('display','none');
                $('#Grafica').css('display','none');
            }
            else if(valorCambiado == "Concentrado" || valorCambiado == "Detallado" 
            || valorCambiado == "EstadisticaMexico" 
            || valorCambiado == "RatificacionesDias"
            || valorCambiado == "Solicitudes"
            || valorCambiado == "SolicitudesResumen"
            || valorCambiado == "GeneralSede"){
                $('#PDF').css('display','block');
                $('#Excel').css('display','none');
                $('#Excel-PDF').css('display','none');
                $('#reporte-notificador').css('display','none');
                 $('#reporte-audiencias').css('display','none');
                $('#Grafica').css('display','none');
            }
            else if(valorCambiado == "SolicitudesGraficas"){
                $('#PDF').css('display','none');
                $('#Excel').css('display','none');
                $('#Excel-PDF').css('display','none');
                $('#reporte-notificador').css('display','none');
                $('#reporte-audiencias').css('display','none');
                $('#Grafica').css('display','block');
            }
            else if((valorCambiado == 'Audiencias')){
                $('#PDF').css('display','none');
                $('#Excel').css('display','block');
                $('#Excel-PDF').css('display','none');
                $('#reporte-notificador').css('display','none');
                $('#reporte-audiencias').css('display','block');
                $('#Grafica').css('display','none');
                
            }
        });
    </script>
@endsection