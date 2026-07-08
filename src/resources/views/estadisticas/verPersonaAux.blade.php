@extends('layouts.app')

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
                            <h3 class="text-center">Solicitud</h3>
                            
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

                            @can('crear-seer')
                                
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Número unico de identificación</label>
                                                <input type="text" class="form-control" value="<?=$general["NUE"];?>" readonly >
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="email">Solicitante</label>
                                                <input type="text" class="form-control" value="<?=$general["solicitante"];?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="password">Estado del solicitante</label>
                                                <input type="text" class="form-control" value="<?=$estado_citado["nombre"];?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="password">Municipio del solicitante</label>
                                                <input type="text" class="form-control" value="<?=$mun_citado["nombre"];?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="confirm-password">Motivo de Solicitud</label>
                                                <input type="text" class="form-control" value="<?=$auxiliar["motivo"];?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="confirm-password">Capturo</label>
                                                <input type="text" class="form-control" value="<?=$registro["name"];?>" readonly>
                                            </div>
                                        </div>


                                        <!-- Comienzo de citados -->                                        
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Citado</h4>
                                                </div>
                                            </div>

                                            @foreach($citados as $citado)
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="confirm-password">Citado</label>
                                                        <input type="text" class="form-control" name="citado" value="<?=$citado["nombre"];?>" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="password">Dirección</label>
                                                        <input type="text" class="form-control" name="direccion" value="<?=$citado["direccion"];?>" readonly>   
                                                    </div>
                                                </div>
                                            @endforeach

                                        <!-- Comienzo de notificadores -->                                        
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center">Notificación</h4>
                                            </div>
                                        </div>

                                        @foreach($notificadores as $noti)
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="confirm-password">Notificó</label>
                                                    <input type="text" class="form-control" value="<?=$noti["notificador"];?>" readonly>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="confirm-password">Fecha</label>
                                                    <input type="text" class="form-control" value="<?=$noti["created_at"];?>" readonly>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="confirm-password">Citado</label>
                                                    <input type="text" class="form-control" value="<?=$noti["citado"];?>" readonly>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="confirm-password">Estatus</label>
                                                    <input type="text" class="form-control" value="<?=$noti["estatus"];?>" readonly>
                                                </div>
                                            </div>
                                        @endforeach

                                        

                                    <!-- Comienzo de audiencia -->                                        
                                         <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Audiencia</h4>
                                                </div>
                                        </div>

                                                @foreach($audiencia as $audi)
                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="email">Folio de la audiencia</label>
                                                            <input type="text" class="form-control" value="<?=$audi["numero_audiencia"];?>" readonly>
                                                        </div>
                                                    </div> 
                                                    
                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="email">Número de audiencia</label>
                                                            <input type="number" class="form-control" name="numero_audiencias" value="<?=$audi["numero_audiencias"];?>" readonly>
                                                        </div>
                                                    </div>

                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="confirm-password">Estatus de audiencias</label>
                                                            <input type="text" class="form-control" value="<?=$audi["estatus_conciliacion"];?>" readonly>
                                                        </div>
                                                    </div>

                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="confirm-password">Monto del convenio</label>
                                                            <input type="text" class="form-control" value="<?php echo "$".(number_format($audi["monto"],2)) ?>" readonly>
                                                        </div>
                                                    </div>

                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="confirm-password">Multa</label>
                                                            <input type="number" step="0.01" class="form-control" value="<?=$audi["multa"];?>" readonly>
                                                        </div>
                                                    </div>

                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="confirm-password">Tipo</label>
                                                            <input type="text" class="form-control" value="<?=$audi["tipo"];?>" readonly>
                                                        </div>
                                                    </div>

                                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                                        <div class="form-group">
                                                            <label for="confirm-password">Observaciones</label>
                                                            <input type="text" class="form-control" value="<?=$audi["observaciones"];?>" readonly>
                                                        </div>
                                                    </div>
                                                @endforeach

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="password">Conciliador</label>
                                                <input type="text" class="form-control" value="<?=$conciliador["name"];?>" readonly>   
                                            </div>
                                        </div>


                                        @if($auxiliar["tipo_solicitud"] == "Ratificación")
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Monto</label>
                                                    <input type="text" class="form-control" value="<?=number_format($auxiliar["monto"],2);?>" readonly>   
                                                </div>
                                            </div><div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Estatus</label>
                                                    <input type="text" class="form-control" value="<?=$auxiliar["estatus"];?>" readonly>   
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <a class="btn btn-primary" href="{{ route('seer') }}">Regresar</a>
                                        </div>
                                            
                                    </div>
                               
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
    <script src="../../public/js/estadistica/estadistica.js"></script>
@endsection

