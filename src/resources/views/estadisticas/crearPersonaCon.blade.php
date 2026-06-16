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
                                @if($userRole[0] == "Auxiliar" || $userRole[0] == "Conciliador")
                                    <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                    <form method="POST" action="{{ route('seer.conciliador_persona') }}" class="needs-validation novalidate">
                                        @csrf
                                        <div class="row">
                                            <input type="hidden" name="id" value="<?=$general["id"]?>">
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Número unico de identificación</label>
                                                    <input type="text" class="form-control" name="NUE" minlength="18" maxlength="18"  oninput="this.value = this.value.toUpperCase()" value="<?=$general["NUE"];?>" >
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="email">Solicitante</label>
                                                    <input type="text" class="form-control" name="solicitante" oninput="this.value = this.value.toUpperCase()" value="<?=$general["solicitante"];?>" >
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Estado del solicitante</label>
                                                    <select id="estado_solicitante" class="form-control" name="estado_solicitante" required>
                                                        <option value="">Seleccione</option>
                                                        @foreach($estados as $est)
                                                            <option value="{{$est->id}}"  @php if($est->id === $general->estado_solicitante) echo "selected"  @endphp  >{{$est['nombre']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El Estado es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Municipio del solicitante</label>
                                                    <select id="municipio_solicitante" name="mun_solicitante" class="form-control" required>
                                                        @foreach($municipios as $mun)
                                                            <option value="{{$mun->id}}"  @php if($mun->id === $general->mun_solicitante) echo "selected"  @endphp  >{{$mun['nombre']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El Municipio es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="confirm-password">Motivo Solicitud</label>
                                                    <select class="form-control" name="motivo" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Despido" @php if($auxiliar->motivo === "Despido") echo "selected"  @endphp >Despido</option>
                                                        <option value="Pago de prestaciones" @php if($auxiliar->motivo === "Pago de prestaciones") echo "selected"  @endphp>Pago de prestaciones</option>
                                                        <option value="Recision de la relación laboral" @php if($auxiliar->motivo === "Recision de la relación laboral") echo "selected"  @endphp>Recision de la relación laboral</option>
                                                        <option value="Derecho de preferencia" @php if($auxiliar->motivo === "Derecho de preferencia") echo "selected"  @endphp>Derecho de preferencia</option>
                                                        <option value="Derecho de antiguedad" @php if($auxiliar->motivo === "Derecho de antiguedad") echo "selected"  @endphp>Derecho de antiguedad</option>
                                                        <option value="Derecho de ascesnso" @php if($auxiliar->motivo === "Derecho de ascesnso") echo "selected"  @endphp>Derecho de ascesnso</option>
                                                        <option value="Terminación voluntaria de relación laboral" @php if($auxiliar->motivo === "Terminación voluntaria de relación laboral") echo "selected"  @endphp>Terminación voluntaria de relación laboral</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El motivo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="confirm-password">Notificación</label>
                                                    <input type="text" class="form-control" value="<?=$auxiliar["notificacion"];?>" >
                                                </div>
                                            </div>

                                            

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="confirm-password">Notificación</label>
                                                    <select class="form-control" name="notificacion" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Trabajador" @php if($auxiliar->notificacion === "Trabajador") echo "selected"  @endphp>Por el trabajador</option>
                                                        <option value="Centro" @php if($auxiliar->notificacion === "Centro") echo "selected"  @endphp>Por el centro</option>
                                                        <option value="Ambos" @php if($auxiliar->notificacion === "Ambos") echo "selected"  @endphp>Ambos</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            @if(isset($audiencia))
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h4 class="text-center">Citado(s)</h4>
                                                    </div>
                                                </div>
                                                @foreach($citados as $citado)
                                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <label for="confirm-password">Citado</label>
                                                            <input type="text" class="form-control" name="citado[]" value="<?=$citado["citado"];?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <label for="password">Dirección</label>
                                                            <input type="text" class="form-control" name="direccion[]" value="<?=$citado["direccion"];?>" >   
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                           
                                           
                                            @if(isset($audiencia))
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h4 class="text-center">Audiencias Anteriores</h4>
                                                    </div>
                                                </div>
                                                @foreach($audiencia as $audi)
                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="email">Folio de la audiencia</label>
                                                            <input type="text" class="form-control" value="<?=$audi["numero_audiencia"];?>">
                                                        </div>
                                                    </div> 
                                                    
                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="email">Número de audiencia</label>
                                                            <input type="number" class="form-control" name="numero_audiencias" value="<?=$audi["numero_audiencias"];?>">
                                                        </div>
                                                    </div>

                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="confirm-password">Estatus de audiencias</label>
                                                            <input type="text" class="form-control" value="<?=$audi["estatus_conciliacion"];?>">
                                                        </div>
                                                    </div>

                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="confirm-password">Monto del convenio</label>
                                                            <input step="any" type="number" class="form-control" value="<?=$audi["monto"];?>">
                                                        </div>
                                                    </div>

                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="confirm-password">Multa</label>
                                                            <input step="any" type="number" class="form-control" value="<?=$audi["multa"];?>">
                                                        </div>
                                                    </div>

                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="confirm-password">Tipo</label>
                                                            <input type="text" class="form-control" value="<?=$audi["tipo"];?>">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif


                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Audiencia</h4>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="email">Actividad economica del citado</label>
                                                    <input type="text" class="form-control" name="actividad_economica" value="<?=$auxiliar["actividad_economica"];?>" >
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">RFC del patron</label>
                                                    <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>  
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="email">Folio de la audiencia</label>
                                                    <input type="text" class="form-control" name="numero_audiencia" >
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-2">
                                                <div class="form-group">
                                                    <label for="email">Número de audiencia</label>
                                                    <input type="number" class="form-control" name="numero_audiencias" required>
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-2">
                                                <div class="form-group">
                                                    <label for="email">Cuenta con NSS</label>
                                                    <select class="form-control" name="NSS" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Si">Si</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Tipo de solicitud</label>
                                                    <select class="form-control" name="solicitud" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Presencial">Presencial</option>
                                                        <option value="Linea">Linea</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="confirm-password">Estatus de audiencias</label>
                                                    <select id="estatus" class="form-control" name="estatus" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Conciliacion">Convenio</option>
                                                        <option value="No conciliacion">No Conciliación</option>
                                                        <option value="Regenerada">Reagendada</option>
                                                        <option value="Archivada">Archivada</option>
                                                        <option value="Archivado por incomparecencia">Archivado por incomparecencia</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="Archivadas" style="display:none" class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="confirm-password">Motivo archivardo</label>
                                                    <select class="form-control" name="motivo_archivo">
                                                        <option value="Falta de interes">Falta de interes</option>
                                                        <option value="Incompetencia">Incompetencia</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="Reprogramada" style="display:none" class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="confirm-password">Fecha programación</label>
                                                    <input type="date" name="fecha_reprogracion" class="form-control">
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Monto del convenio</label>
                                                    <input type="number" step="0.01" name="monto" class="form-control" >   
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Multa</label>
                                                    <select id="multa" class="form-control" name="multa" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Si">Si</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="montoMulta" style="display:none" class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="confirm-password">Monto de la multa</label>
                                                    <input type="number" step="0.01" name="monto_multa" class="form-control">
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                            </div>
                                                
                                        </div>
                                    </form>
                                @endif
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
    <script>
        $('#estatus').change(function(){
            var valorCambiado = $('#estatus').val();
            if((valorCambiado == 'Archivada')){
                $('#Archivadas').css('display','block');
                $('#Reprogramada').css('display','none');
            }
            else if((valorCambiado == 'Regenerada')){
                $('#Archivadas').css('display','none');
                $('#Reprogramada').css('display','block');
            }
            else{
                $('#Archivadas').css('display','none');
                $('#Reprogramada').css('display','none');
            }
        });

        $('#multa').change(function(){
            var valor = $('#multa').val();
            if((valor == 'Si')){
                $('#montoMulta').css('display','block');
            }
            else{
                $('#montoMulta').css('display','none');
            }
        });
    </script>
@endsection

