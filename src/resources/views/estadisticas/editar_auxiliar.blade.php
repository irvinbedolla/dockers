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
                                @if($userRole[0] == "Auxiliar")
                                    <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                    <form method="POST" action="{{ route('update_auxiliar') }}" class="needs-validation novalidate">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $general->id }}">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Número unico de identificación</label>
                                                    <input type="text" class="form-control" name="NUE" minlength="18" maxlength="18"  oninput="this.value = this.value.toUpperCase()" required value="{{ $general->NUE }}">
                                                    <div class="invalid-feedback">
                                                        El Número unico de identificación es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="confirm-password">Fecha de confirmación de la solicitud</label>
                                                    <input type="date" class="form-control" name="fecha_confirmacion" required value="{{ $general->fecha_confirmacion }}">
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="email">Solicitante</label>
                                                    <input type="text" class="form-control" name="solicitante"  oninput="this.value = this.value.toUpperCase()" required value="{{ $general->solicitante }}">
                                                    <div class="invalid-feedback">
                                                        El Solicitante es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="email">Actividad economica</label>
                                                    <input type="text" class="form-control" name="actividad_economica"  oninput="this.value = this.value.toUpperCase()" required value="{{ $auxiliar->actividad_economica }}">
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="confirm-password">Sexo</label>
                                                    <select class="form-control" name="sexo" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="H" @php if($auxiliar->sexo === "H") echo "selected"  @endphp >Hombre</option>
                                                        <option value="M" @php if($auxiliar->sexo === "M") echo "selected"  @endphp >Mujer</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Debes seleccionar al menos un Sexo.
                                                    </div>
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
                                                        <input type="text" class="form-control" name="citado[]" value="<?=$citado["nombre"];?>">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="password">Dirección</label>
                                                        <input type="text" class="form-control" name="direccion[]" value="<?=$citado["direccion"];?>">   
                                                    </div>
                                                </div>
                                            @endforeach

                                            <div class="col-xs-12 col-sm-12 col-md-2"><BR>
                                                <button id="addRow" type="button" class="btn btn-info">Agregar Citado</button>
                                                <div id="newRow" ></div>
                                            </div>
                                           

                                            


                                            

                                            
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Motivo de Solicitud</h4>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-3">
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

                                            <div class="col-xs-12 col-sm-6 col-md-3">
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

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Conciliador</label>
                                                    <select class="form-control" name="conciliador_id" required>
                                                        <option value="">Seleccione</option>
                                                        @foreach($conciliadores as $con)
                                                            <option value="{{$con['id']}}" @php if($conciliador->id === $con->id) echo "selected"  @endphp>{{$con['name']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El conciliador es obligatorio.
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
<script>
    $( document ).ready(function() {
        // agregar registro
        $("#addRow").click(function () {
            var html = '';
            html += '<div id="inputFormRow" class="row">';

                //NOMBRE CITADO
                html +='<div class="col-xs-12 col-sm-12 col-md-12">';
                html +='<div class="form-group">';
                html +='<label for="confirm-password">Citado</label>';
                html +='<input type="text" class="form-control" name="citado[]"  oninput="this.value = this.value.toUpperCase()" required>';
                html +='</div> </div>';                                
                
                //DIRECCION
                html += '<div class="col-xs-12 col-sm-12 col-md-12">';
                html += '<div class="form-group">';
                html += '<label for="password">Dirección del citado</label>';
                html +='<input type="text" class="form-control" name="direccion[]"  oninput="this.value = this.value.toUpperCase()" required>';
                html += '<div class="invalid-feedback">';
                html += 'La Dirección es obligatoria.';
                html += '</div> </div> </div>';
                
                //TIPO DE PERSONA
                html +='<div class="col-xs-12 col-sm-12 col-md-12">';
                html +='<div class="form-group">';
                html +='<label for="confirm-password">Tipo persona</label>';
                html +='<select class="form-control" name="tipo_persona[]" required>';
                html +='<option value="">Seleccione</option>';
                html +='<option value="Fisica">Fisica</option>';
                html +='<option value="Moral">Moral</option>';
                html +='</select>';
                html +='<div class="invalid-feedback">';
                html +='El tipo de persona es obligatorio.';
                html += '</div> </div> </div>';                                    
                
            html += '<div class="input-group-append">';
            html += '<button id="removeRow" type="button" class="btn btn-danger">Borrar</button>';
            html += '</div>';
            html += '</div>';

            $('#newRow').append(html);
        });
        
        // borrar registro
        $(document).on('click', '#removeRow', function () {
            $(this).closest('#inputFormRow').remove();
        });
    });
</script>
    <script src="../public/assets/js/estadistica/estadistica.js"></script>
@endsection

