@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Vista Previa</h3>
        </div>
        <div class="section-body">
            <?php $fecha_actual = date('d-m-Y');?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('seer.cambioEstatus') }}" class="needs-validation" novalidate enctype='multipart/form-data'>
                                @csrf
                                <input type="hidden" name="id" value="{{$registro->id}}">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">NUE</label>
                                            <input type="text" name="nue" class="form-control" value="<?=$NUE;?>" readonly> 
                                            <div class="invalid-feedback">
                                                El campo nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Nombre del Citado</label>
                                            <input type="text" name="nombre_citado" class="form-control" value="{{ trim($registro->nombre . ' ' . ($registro->primer_apellido ?? '') . ' ' . ($registro->segundo_apellido ?? '')) }}" readonly> 
                                            <div class="invalid-feedback">
                                                El campo nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Dirección del citado</label>
                                            <input type="text" name="nombre_citado" class="form-control" value="{{ trim($registro->tipo_vialidad . ' ' . ($registro->calle ?? '') . ', ' . ($registro->n_ext ?? '') . ', ' . ($registro->n_int ?? '') . ', ' . ($registro->colonia ?? '') . ', ' . ($registro->cp ?? '') . ', ' . ($nombre_municipio ?? '') . ', ' . ($nombre_estado ?? '')) . '.' }}" readonly> 
                                            <div class="invalid-feedback">
                                                El campo nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                                        <label for="name">Referencia 1</label><br>
                                        @if (!empty($registro->imagen_domicilio1) && $registro->imagen_domicilio1 !== 'Sin documento')
                                            <a target='_blank' href="{{ asset('storage/app/documentosSolicitud/'.$registro->imagen_domicilio1) }}">VER IMAGEN</a>
                                        @else
                                            <span class="text-muted">No se subió imagen</span>
                                        @endif
                                        <input type="hidden" name="imagen_domicilio1" value="{{ $registro->imagen_domicilio1 }}">
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 mb-3">
                                        <label for="name">Referencia 2</label><br>
                                        @if (!empty($registro->imagen_domicilio2) && $registro->imagen_domicilio2 !== 'Sin documento')
                                            <a target='_blank' href="{{ asset('storage/app/documentosSolicitud/'.$registro->imagen_domicilio2) }}">VER IMAGEN</a><br>
                                        @else
                                            <span class="text-muted">No se subió imagen</span>
                                        @endif
                                        <input type="hidden" name="imagen_domicilio2" value="{{ $registro->imagen_domicilio2 }}">
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:25px;">
                                        <h5 class="text-center" style="color:black">Expediente</h5>
                                    </div>
                                </div>    
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tipo de guardado <span style="color:red;">(*)</span></label>
                                            <select class="form-control" name="tipo_llenado" required>
                                                <option value="">Seleccione</option>
                                                <option value="1" {{ $tipo_llenado == '1' ? 'selected' : '' }}>Actualizar unicamente esta notificicación</option>
                                                <option value="2" {{ $tipo_llenado == '2' ? 'selected' : '' }}>Actualizar todo el Expediente</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label>¿Quién atiende? <span style="color:red;">(*)</span></label>
                                            <select name="quien_atiende" id="quien_atiende" class="form-control" required>
                                                <option value="">Selecciona</option>
                                                <option value="CITADO O REPRESENTANTE" {{ $registro->quien_atiende == 'CITADO O REPRESENTANTE' ? 'selected' : '' }}>El citado o representante legal</option>
                                                <option value="OTRA PERSONA" {{ $registro->quien_atiende == 'OTRA PERSONA' ? 'selected' : '' }}>Otra persona</option>
                                                <option value="NADIE" {{ $registro->quien_atiende == 'NADIE' ? 'selected' : '' }}>Nadie</option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback">
                                            El campo es obligatorio.
                                        </div>
                                    </div>
                                    <div class='row mcd-group'>
                                        <div class="col-xs-12 col-sm-12 col-md-12 " style="background-color:#D2D3D5; width:100%; height:25px;">
                                            <h5 class="text-center" style="color:black">Medios de cercioramiento de domicilio</h5>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6 ">
                                            <div class="form-group">
                                                <label>Medio <span style="color:red;">(*)</span></label>
                                                @php
                                                    // Preparamos el arreglo de medios seleccionados igual que en la primera vista
                                                    $medioSeleccionado = old('medio', $registro->medio ?? []);
                                                    if (is_string($medioSeleccionado)) {
                                                        $medioSeleccionado = array_filter(array_map('trim', preg_split('/[;,\n\r]+/', $medioSeleccionado)));
                                                    }
                                                    if (!is_array($medioSeleccionado)) {
                                                        $medioSeleccionado = [];
                                                    }
                                                @endphp
                                                <select name="medio[]" id="medio" class="form-select select2" multiple required>
                                                    <option value="">Selecciona</option>
                                                    <option value="PLACAS OFICIALES" {{ in_array('PLACAS OFICIALES', $medioSeleccionado, true) ? 'selected' : '' }}>Placas oficiales</option>
                                                    <option value="NÚMERO VISIBLE" {{ in_array('NÚMERO VISIBLE', $medioSeleccionado, true) ? 'selected' : '' }}>Número visible</option>
                                                    <option value="NUMERACIÓN CONSISTENTE" {{ in_array('NUMERACIÓN CONSISTENTE', $medioSeleccionado, true) ? 'selected' : '' }}>Numeración consistente</option>
                                                    <option value="INFORMES DE VECINOS" {{ in_array('INFORMES DE VECINOS', $medioSeleccionado, true) ? 'selected' : '' }}>Informes de vecinos</option>
                                                    <option value="RÓTULOS VISIBLES" {{ in_array('RÓTULOS VISIBLES', $medioSeleccionado, true) ? 'selected' : '' }}>Rótulos visibles</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-xs-12 col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <label>Tipo de vialidad <span style="color:red;">(*)</span></label>
                                                <select name="vialidad_notificacion" class="form-control" required>
                                                    <option value="">Selecciona</option>
                                                    <option value="AMPLIACION" {{ $registro->vialidad_notificacion == 'AMPLIACION' ? 'selected' : '' }}>Ampliación </option>
                                                    <option value="ANDADOR" {{ $registro->vialidad_notificacion == 'ANDADOR' ? 'selected' : '' }}>Andador</option>
                                                    <option value="AUTOPISTA" {{ $registro->vialidad_notificacion == 'AUTOPISTA' ? 'selected' : '' }}>Autopista</option>
                                                    <option value="AVENIDA" {{ $registro->vialidad_notificacion == 'AVENIDA' ? 'selected' : '' }}>Avenida</option>
                                                    <option value="BOULEVARD" {{ $registro->vialidad_notificacion == 'BOULEVARD' ? 'selected' : '' }}>Boulevard</option>
                                                    <option value="CALLE" {{ $registro->vialidad_notificacion == 'CALLE' ? 'selected' : '' }}>Calle</option>
                                                    <option value="CALLEJÓN" {{ $registro->vialidad_notificacion == 'CALLEJÓN' ? 'selected' : '' }}>Callejón</option>
                                                    <option value="CALZADA" {{ $registro->vialidad_notificacion == 'CALZADA' ? 'selected' : '' }}>Calzada</option>
                                                    <option value="CARRETERA" {{ $registro->vialidad_notificacion == 'CARRETERA' ? 'selected' : '' }}>Carretera</option>
                                                    <option value="CERRADA" {{ $registro->vialidad_notificacion == 'CERRADA' ? 'selected' : '' }}>Cerrada</option>
                                                    <option value="CIRCUITO" {{ $registro->vialidad_notificacion == 'CIRCUITO' ? 'selected' : '' }}>Circuito</option>
                                                    <option value="CIRCUNVALACIÓN" {{ $registro->vialidad_notificacion == 'CIRCUNVALACIÓN' ? 'selected' : '' }}>Circunvalación</option>
                                                    <option value="CONTINUACIÓN" {{ $registro->vialidad_notificacion == 'CONTINUACIÓN' ? 'selected' : '' }}>Continuación</option>
                                                    <option value="CORREDOR" {{ $registro->vialidad_notificacion == 'CORREDOR' ? 'selected' : '' }}>Corredor</option>
                                                    <option value="DIAGONAL" {{ $registro->vialidad_notificacion == 'DIAGONAL' ? 'selected' : '' }}>Diagonal</option>
                                                    <option value="EJE VIAL" {{ $registro->vialidad_notificacion == 'EJE VIAL' ? 'selected' : '' }}>Eje vial</option>
                                                    <option value="PERIFÉRICO" {{ $registro->vialidad_notificacion == 'PERIFÉRICO' ? 'selected' : '' }}>Periférico</option>
                                                    <option value="PROLONGACIÓN" {{ $registro->vialidad_notificacion == 'PROLONGACIÓN' ? 'selected' : '' }}>Prolongación</option>
                                                    <option value="PRIVADA" {{ $registro->vialidad_notificacion == 'PRIVADA' ? 'selected' : '' }}>Privada</option>
                                                    <option value="RETORNO" {{ $registro->vialidad_notificacion == 'RETORNO' ? 'selected' : '' }}>Retorno</option>
                                                    <option value="VIADUCTO" {{ $registro->vialidad_notificacion == 'VIADUCTO' ? 'selected' : '' }}>Viaducto</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6 ">
                                            <div class="form-group">
                                                <label for="name">Abundar área <span style="color:red;">(*)</span></label>
                                                <textarea class="form-control" name="abundar_area" rows="4" required>{{ $registro->abundar_area }}</textarea>
                                                <div class="invalid-feedback">
                                                    El campo abundar área es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Abundar inmueble <span style="color:red;">(*)</span></label>
                                                <textarea class="form-control" name="abundar_inmueble" rows="4" required>{{ $registro->abundar_inmueble }}</textarea>
                                                <div class="invalid-feedback">
                                                    El campo abundar inmueble es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class='row dqr-group'>
                                        <div class="col-xs-12 col-sm-12 col-md-12 " style="background-color:#D2D3D5; width:100%; height:25px;">
                                            <h5 class="text-center" style="color:black">Datos de quien recibe</h5>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6 ">
                                            <div class="form-group">
                                                <label for="name">Nombre</label>
                                                <input type="text" name="nombre_notificacion" class="form-control" value="{{ old('name', $registro->nombre_notificacion) }}" oninput="this.value = this.value.toUpperCase()" > 
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6 ">
                                            <div class="form-group">
                                                <label for="name">Relación (respecto al domicilio)</label>
                                                <select name="relacion_notificacion" class="form-control">
                                                    <option value="">Selecciona</option>
                                                    <option value="RESIDE" {{ old('relacion_notificacion', $registro->relacion_notificacion) == 'RESIDE' ? 'selected' : '' }}>Reside</option>
                                                    <option value="TRABAJA" {{ old('relacion_notificacion', $registro->relacion_notificacion) == 'TRABAJA' ? 'selected' : '' }}>Trabaja</option>
                                                </select> 
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6 ">
                                            <div class="form-group">
                                                <label for="name">Puesto </label>
                                                <input type="text" name="puesto" class="form-control" value="{{ old('name', $registro->puesto) }}" oninput="this.value = this.value.toUpperCase()"> 
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Identificación </label>
                                                <select name="identificacion_notificacion" id="identificacion_notificacion" class="form-control">
                                                    <option value="">Selecciona</option>
                                                    <option value="NO PROPORCIONA" {{ $registro->identificacion_notificacion == 'NO PROPORCIONA' ? 'selected' : '' }} >No proporciona</option>
                                                    <option value="NO ATIENDE PRESENCIALMENTE" {{ $registro->identificacion_notificacion == 'NO ATIENDE PRESENCIALMENTE' ? 'selected' : '' }} >No atiende presencialmente (Persona no visible)</option>
                                                    <option value="CREDENCIAL PARA VOTAR" {{ $registro->identificacion_notificacion == 'CREDENCIAL PARA VOTAR' ? 'selected' : '' }} >Credencial para votar</option>
                                                    <option value="LICENCIA O PERMISO PARA CONDUCIR" {{ $registro->identificacion_notificacion == 'LICENCIA O PERMISO PARA CONDUCIR' ? 'selected' : '' }} >Licencia o permiso para conducir</option>
                                                    <option value="CREDENCIAL DE IDENTIFICACION LABORAL" {{ $registro->identificacion_notificacion == 'CREDENCIAL DE IDENTIFICACION LABORAL' ? 'selected' : '' }} >Credencial de identificación laboral</option>
                                                    <option value="CREDENCIAL DE INSTITUCIÓN DE SALUD" {{ $registro->identificacion_notificacion == 'CREDENCIAL DE INSTITUCIÓN DE SALUD' ? 'selected' : '' }} >Credencial de institución de salud</option>
                                                    <option value="CREDENCIAL DE INSTITUCIÓN ESCOLAR" {{ $registro->identificacion_notificacion == 'CREDENCIAL DE INSTITUCIÓN ESCOLAR' ? 'selected' : '' }} >Credencial de institución de escolar</option>
                                                    <option value="CARTILLA DE SERVICIO MILITAR" {{ $registro->identificacion_notificacion == 'CARTILLA DE SERVICIO MILITAR' ? 'selected' : '' }}>Cartilla de servicio militar</option>
                                                    <option value="PASAPORTE" {{ $registro->identificacion_notificacion == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                                                    <option value="CÉDULA PROFESIONAL" {{ $registro->identificacion_notificacion == 'CÉDULA PROFESIONAL' ? 'selected' : '' }}>Cédula profesional</option>
                                                    <option value="RFC" {{ $registro->identificacion_notificacion == 'RFC' ? 'selected' : '' }}>RFC</option>
                                                    <option value="OTRA IDENTIFICACIÓN" {{ $registro->identificacion_notificacion == 'OTRA IDENTIFICACIÓN' ? 'selected' : '' }}>Otra identificación</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div id="num_identificacion" class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <label for="numero_identificacion">Número de identificación</label>
                                                <input type="text" id="numero_identificacion" name="num_identificacion" class="form-control" value="{{ old('num_identificacion', $registro->num_identificacion ?? '') }}" oninput="this.value = this.value.toUpperCase()">
                                            </div>
                                        </div>
                                        <div id="motivo_identificacion" class="col-xs-12 col-sm-12 col-md-12 " style="display:none;">
                                            <div class="form-group">
                                                <label for="name">Motivo de la no identificación</label>
                                                <input type="text" name="motivo_identificacion" class="form-control" value="{{ old('name', $registro->motivo_identificacion) }}" oninput="this.value = this.value.toUpperCase()"> 
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="media-filiacion" class='row mf-group' style="display:none;">
                                        <div class="col-xs-12 col-sm-12 col-md-12 " style="background-color:#D2D3D5; width:100%; height:25px;">
                                            <h5 class="text-center" style="color:black">Media filiación de persona que recibe</h5>
                                        </div>
                                        <div class="row">                                      
                                            <div class="col-xs-12 col-sm-12 col-md-6 ">
                                                <div class="form-group">
                                                    <label for="name">Género </label>
                                                    <select name="genero" class="form-control" >
                                                        <option value="">Selecciona</option>
                                                        <option value="MASCULINO" {{ $registro->genero == 'MASCULINO' ? 'selected' : '' }}>MASCULINO</option>
                                                        <option value="FEMENINO" {{ $registro->genero == 'FEMENINO' ? 'selected' : '' }}>FEMENINO</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo genero es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6 ">
                                                <div class="form-group">
                                                    <label for="name">Tez </label>
                                                    <input type="text" name="tez" class="form-control" value="{{ old('name', $registro->tez) }}" oninput="this.value = this.value.toUpperCase()" > 
                                                    <div class="invalid-feedback">
                                                        El campo tez es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6 ">
                                                <div class="form-group">
                                                    <label for="name">Edad </label>
                                                    <input type="text" name="edad_filiacion" class="form-control" value="{{ old('name', $registro->edad_filiacion) }}" oninput="this.value = this.value.toUpperCase()" > 
                                                    <div class="invalid-feedback">
                                                        El campo edad es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6 ">
                                                <div class="form-group">
                                                    <label for="name">Altura</label>
                                                    <input type="text" name="altura" class="form-control" value="{{ old('name', $registro->altura) }}" oninput="this.value = this.value.toUpperCase()" > 
                                                    <div class="invalid-feedback">
                                                        El campo altura es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6 ">
                                                <div class="form-group">
                                                    <label for="name">Complexión </label>
                                                    <input type="text" name="complexion" class="form-control" value="{{ old('name', $registro->complexion) }}" oninput="this.value = this.value.toUpperCase()" > 
                                                    <div class="invalid-feedback">
                                                        El campo complexión es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6 ">
                                                <div class="form-group">
                                                    <label for="name">Cabello </label>
                                                    <input type="text" name="cabello" class="form-control" value="{{ old('name', $registro->cabello) }}" oninput="this.value = this.value.toUpperCase()"> 
                                                    <div class="invalid-feedback">
                                                        El campo cabello es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3 ">
                                                <div class="form-group">
                                                    <label for="name">Ojos </label>
                                                    <input type="text" name="ojos" class="form-control" value="{{ old('name', $registro->ojos) }}" oninput="this.value = this.value.toUpperCase()" > 
                                                    <div class="invalid-feedback">
                                                        El campo ojos es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-9 ">
                                                <div class="form-group">
                                                    <label for="name">Señas particulares </label>
                                                    <input type="text" name="particulares" class="form-control" value="{{ old('name', $registro->particulares) }}" oninput="this.value = this.value.toUpperCase()" > 
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='row de-group'>
                                        
                                        <div class="col-xs-12 col-sm-12 col-md-12 " style="background-color:#D2D3D5; width:100%; height:25px;">
                                            <h5 class="text-center" style="color:black">Finalización</h5>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Giro comercial <span style="color:red;">(*)</span> </label>
                                                <input type="text" name="giro_comercial" class="form-control" value="{{ old('name', $registro->giro_comercial) }}" oninput="this.value = this.value.toUpperCase()" required> 
                                                <div class="invalid-feedback">
                                                    El campo giro comercial es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label>Finalización de diligencia <span style="color:red;">(*)</span></label>
                                                <select name="estatus" id="estatus" class="form-control" required>
                                                    <option value="">Selecciona</option>
                                                    <option value="Finalizado exitosamente" {{ $estatusSeleccionado == 'Finalizado exitosamente' ? 'selected' : '' }}>Finalizado exitosamente (persona)</option>
                                                    <option value="Exitosa por Instructivo" {{ $estatusSeleccionado == 'Exitosa por Instructivo' ? 'selected' : '' }}>Exitoso por instructivo (fijado en puerta)</option>
                                                    <option value="No exitosa se constituye" {{ $estatusSeleccionado == 'No exitosa se constituye' ? 'selected' : '' }}>No exitoso, se constituye</option>
                                                    <option value="No exitosa no se constituye" {{ $estatusSeleccionado == 'No exitosa no se constituye' ? 'selected' : '' }}>No exitoso, no se constituye (amparo)</option>
                                                    <option value="Notificada" {{ $estatusSeleccionado == 'Notificada' ? 'selected' : '' }}>Notificado</option>
                                                    <option value="Recibe pero no firma" {{ $estatusSeleccionado == 'Recibe pero no firma' ? 'selected' : '' }}>Recibe pero no firma</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2" id="firma" style="display:none;">
                                            <div class="form-group">
                                                <label>Firma </label>
                                                <select name="firma" class="form-control" >
                                                    <option value="">Selecciona</option>
                                                    <option value="NO FIRMA" {{ $registro->firma == 'NO FIRMA' ? 'selected' : '' }}>No firma</option>
                                                    <option value="FIRMA" {{ $registro->firma == 'FIRMA' ? 'selected' : '' }}>Firma</option>
                                                    <option value="SELLA" {{ $registro->firma == 'SELLA' ? 'selected' : '' }}>Sella</option>
                                                    <option value="FIRMA Y SELLA" {{ $registro->firma == 'FIRMA Y SELLA' ? 'selected' : '' }}>Firma y sella</option>
                                                    <option value="NO APLICA" {{ $registro->firma == 'NO APLICA' ? 'selected' : '' }}>No aplica</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El campo firma es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4 " id="tipo_problema1" style="display:none;">
                                            <div class="form-group">
                                                <label for="name">Tipo de problema</label>
                                                <select id="problema1" name="problema_diligencia" class="form-control">
                                                    <option value="">Selecciona</option>
                                                        <optgroup label="Domicilio">
                                                            <option value="CERRADO" {{ $registro->problema_diligencia == 'CERRADO' ? 'selected' : '' }}>Cerrado</option>
                                                            <option value="NO ACCESO AL INMUEBLE" {{ $registro->problema_diligencia == 'NO ACCESO AL INMUEBLE' ? 'selected' : '' }}>No acceso al inmueble</option>
                                                        <optgroup label="Número">
                                                            <option value="NO SEÑALA INTERIOR" {{ $registro->problema_diligencia == 'NO SEÑALA INTERIOR' ? 'selected' : '' }}>No señala interior</option>
                                                            <option value="NÚMERO INTERIOR SEÑALADO NO SE LOCALIZÓ EN DOMICILIO" {{ $registro->problema_diligencia == 'NÚMERO INTERIOR SEÑALADO NO SE LOCALIZÓ EN DOMICILIO' ? 'selected' : '' }}>Número interior señalado no se localizó en domicilio</option>
                                                            <option value="NO LOGRO LOCALIZAR EL NÚMERO" {{ $registro->problema_diligencia == 'NO LOGRO LOCALIZAR EL NÚMERO' ? 'selected' : '' }}>No logro localizar el número</option>
                                                            <option value="NO SE LOCALIZA EL INMUEBLE CON NÚMERO, MANZANA, LOTE, ETC. SEALADOS" {{ $registro->problema_diligencia == 'NO SE LOCALIZA EL INMUEBLE CON NÚMERO, MANZANA, LOTE, ETC. SEALADOS' ? 'selected' : '' }}>No se localiza el inmueble con número, manzana, lote, etc. señalados</option>
                                                        <optgroup label="Calle">
                                                            <option value="NO EXISTE EN COLONIA" {{ $registro->problema_diligencia == 'NO EXISTE EN COLONIA' ? 'selected' : '' }}>No existe en colonia</option>
                                                        <optgroup label="Colonia">
                                                            <option value="NO EXISTE EN MUNICIPIO" {{ $registro->problema_diligencia == 'NO EXISTE EN MUNICIPIO' ? 'selected' : '' }}>No existe en municipio</option>
                                                        <optgroup label="Alguien atiende">
                                                            <option value="RAZÓN SOCIAL DIVERSA" {{ $registro->problema_diligencia == 'RAZÓN SOCIAL DIVERSA' ? 'selected' : '' }}>Razón social diversa</option>
                                                        <optgroup label="Otros">
                                                            <option value="OTROS" {{ $registro->problema_diligencia == 'OTROS' ? 'selected' : '' }}>Otros</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Cuando es no existoso, no se contituye, mostrar estas opciones en (Tipo de problema)-->
                                        <div class="col-xs-12 col-sm-6 col-md-4" id="tipo_problema2" style="display:none;">
                                            <div class="form-group">
                                                <label for="name">Tipo de problema</label>
                                                <select id="problema2" name="problema_diligencia" class="form-control">
                                                    <option value="">Selecciona</option>
                                                        <optgroup label="Domicilio incompleto">
                                                            <option value="OMITE NÚMERO" {{ $registro->problema_diligencia == 'OMITE NÚMERO' ? 'selected' : '' }}>Omite número</option>
                                                            <option value="OMITE VIALIDAD" {{ $registro->problema_diligencia == 'OMITE VIALIDAD' ? 'selected' : '' }}>Omite vialidad</option>
                                                            <option value="OMITE COLONIA" {{ $registro->problema_diligencia == 'OMITE COLONIA' ? 'selected' : '' }}>Omite colonia</option>
                                                            <option value="OMITE MUNICIPIO" {{ $registro->problema_diligencia == 'OMITE MUNICIPIO' ? 'selected' : '' }}>Omite municipio</option>
                                                        <optgroup label="Domicilio">
                                                            <option value="FUERA DE LA JURISDICCIÓN" {{ $registro->problema_diligencia == 'FUERA DE LA JURISDICCIÓN' ? 'selected' : '' }}>Fuera de la jurisdicción</option>
                                                        <optgroup label="Copias">
                                                            <option value="NO HAY COPIAS SUFICIENTES" {{ $registro->problema_diligencia == 'NO HAY COPIAS SUFICIENTES' ? 'selected' : '' }}>No hay copias suficientes</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-6" id="abundar_motivo" style="display:none;">
                                            <div class="form-group">
                                                <label for="name"><!--Especificar en caso de que tenga un problema-->Abundar motivo</label>
                                                <textarea class="form-control" name="especificar" rows="4" oninput="this.value = this.value.toUpperCase()">{{ $registro->especificar }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-4 ">
                                            <div class="form-group">
                                                <label for="name">Imagen 1 <span style="color:red;">(*)</span></label><br>
                                                <input type="file" class="form-control" name="foto" accept="image/*" {{ (!empty($registro->documento) && $registro->documento !== 'Sin documento') ? '' : 'required' }}>
                                                @if(!empty($registro->documento) && $registro->documento !== 'Sin documento')
                                                    <a target='_blank' href="{{ asset('storage/app/documentos_notificacion/'.$registro->documento) }}" class="badge badge-info mb-2">VER IMAGEN ACTUAL</a>
                                                    <input type="hidden" name="documento_actual" value="{{ $registro->documento }}">
                                                @endif
                                                <small class="text-muted">Selecciona un archivo solo si deseas reemplazar la imagen actual.</small>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Imagen 2</label><br>
                                                <input type="file" class="form-control" name="foto1" accept="image/*">
                                                @if(!empty($registro->documento1) && $registro->documento1 !== 'Sin documento')
                                                    <a target='_blank' href="{{ asset('storage/app/documentos_notificacion/'.$registro->documento1) }}" class="badge badge-info mb-2">VER IMAGEN ACTUAL</a>
                                                    <input type="hidden" name="documento1_actual" value="{{ $registro->documento1 }}">
                                                @endif
                                                <small class="text-muted">Selecciona un archivo solo si deseas reemplazar la imagen actual.</small>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Imagen 3</label><br>
                                                <input type="file" class="form-control" name="foto2" accept="image/*">
                                                @if(!empty($registro->documento2) && $registro->documento2 !== 'Sin documento')
                                                    <a target='_blank' href="{{ asset('storage/app/documentos_notificacion/'.$registro->documento2) }}" class="badge badge-info mb-2">VER IMAGEN ACTUAL</a>
                                                    <input type="hidden" name="documento2_actual" value="{{ $registro->documento2 }}">
                                                @endif
                                                <small class="text-muted">Selecciona un archivo solo si deseas reemplazar la imagen actual.</small>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-12" id="especificar_motivo">
                                            <div class="form-group">
                                                <label for="name">Especificar motivo <span style="color:red;">(*)</span></label>
                                                <textarea class="form-control" name="observaciones"  id="observaciones" rows="4" required>{{ $registro->observaciones }} </textarea>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Fecha de notificación <span style="color:red;">(*)</span></label>
                                                <input type="date" class="form-control" name="fecha_notificacion" value="{{ $fecha_formateada }}" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Hora de notificación <span style="color:red;">(*)</span></label>
                                            <input type="time" class="form-control" name="hora_notificacion" value="{{ $hora_formateada }}" required> 
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <a href="{{ route('seer') }}" class="btn btn-secondary">Cancelar</a>
                                        <button type="submit" class="btn btn-primary" name="vista_previa" value="0">Guardar</button>
                                        <button type="submit" class="btn btn-primary" name="vista_previa" value="1">Vista previa</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<script>
        document.getElementById("motivo_identificacion").style.display = "none";
        document.addEventListener('DOMContentLoaded', function () {
            const btnVistaPrevia = document.querySelector('button[name="vista_previa"][value="1"]');
            const selectQuienAtiende = document.querySelector('select[name="quien_atiende"]');
            const mediaFiliacionDiv = document.getElementById('media-filiacion');
            const dqrGroups = Array.from(document.querySelectorAll('.dqr-group'));
            const mcdGroups = Array.from(document.querySelectorAll('.mcd-group'));
            const odGroups = Array.from(document.querySelectorAll('.od-group'));
            const mfGroups = Array.from(document.querySelectorAll('.mf-group'));
            const deGroups = Array.from(document.querySelectorAll('.de-group'));


            function setRequiredInGroups(groups, enabled) {
                groups.forEach(sectionEl => {
                    const fields = sectionEl.querySelectorAll('input, select, textarea');
                    fields.forEach(el => {
                        const shouldBeRequiredByDefault = el.hasAttribute('data-required-default');
                        if (enabled) {
                            if (shouldBeRequiredByDefault) el.setAttribute('required', '');
                        } else {
                            el.removeAttribute('required');
                        }
                    });
                });
            }
            function actualizarBotonVistaPrevia(valor) {
                if (!btnVistaPrevia) return; // Seguridad por si el botón no existe en esta vista

                if (valor === 'EXHORTO') {
                    btnVistaPrevia.disabled = true;
                    btnVistaPrevia.style.opacity = '0.5';
                    btnVistaPrevia.style.cursor = 'not-allowed';
                } else {
                    btnVistaPrevia.disabled = false;
                    btnVistaPrevia.style.opacity = '1';
                    btnVistaPrevia.style.cursor = 'pointer';
                }
            }

            if (selectQuienAtiende) {
                selectQuienAtiende.addEventListener('change', function () {
                    console.log("Cambio:", this.value);
                    actualizarBotonVistaPrevia(this.value);
                    mfGroups.forEach(el => el.style.display = 'none');
                    setRequiredInGroups(mfGroups, false);
                    
                    if(this.value === 'NADIE'){
                        dqrGroups.forEach(el => el.style.display = 'none');
                        setRequiredInGroups(dqrGroups, false);
                        odGroups.forEach(el => el.style.display = 'none');
                        setRequiredInGroups(odGroups, false);
                        mfGroups.forEach(el => el.style.display = 'none');
                        setRequiredInGroups(mfGroups, false);
                        

                    }
                    else if(this.value === 'EXHORTO'){
                        mcdGroups.forEach(el => el.style.display = 'none');
                        setRequiredInGroups(mcdGroups, false);
                        dqrGroups.forEach(el => el.style.display = 'none');
                        setRequiredInGroups(dqrGroups, false);
                        odGroups.forEach(el => el.style.display = 'none');
                        setRequiredInGroups(odGroups, false);
                        mfGroups.forEach(el => el.style.display = 'none');
                        setRequiredInGroups(mfGroups, false);
                        deGroups.forEach(el => el.style.display = 'none');
                        setRequiredInGroups(deGroups, false);

                    } 
                    else {
                        mcdGroups.forEach(el => el.style.display = '');
                        setRequiredInGroups(mcdGroups, true)
                        dqrGroups.forEach(el => el.style.display = '');
                        setRequiredInGroups(dqrGroups, true);
                        odGroups.forEach(el => el.style.display = '');
                        setRequiredInGroups(odGroups, true);
                        mfGroups.forEach(el => el.style.display = '');
                        setRequiredInGroups(mfGroups, true);
                        deGroups.forEach(el => el.style.display = '');
                        setRequiredInGroups(deGroups, true);
                        
                    }

                    /*if (this.value === 'OTRA PERSONA') {
                        /*mediaFiliacionDiv.style.display = 'block';
                        setRequiredInGroups([mediaFiliacionDiv], true);*/
                        
                        
                    /*} else {
                        
                        /*mediaFiliacionDiv.style.display = 'none';
                        setRequiredInGroups([mediaFiliacionDiv], false);*/
                    //}
                });
                const initial = selectQuienAtiende.value;
                actualizarBotonVistaPrevia(initial);

                if(initial === 'NADIE'){
                    dqrGroups.forEach(el => el.style.display = 'none');
                    setRequiredInGroups(dqrGroups, false);
                    odGroups.forEach(el => el.style.display = 'none');
                    setRequiredInGroups(odGroups, false);
                    mfGroups.forEach(el => el.style.display = 'none');
                    setRequiredInGroups(mfGroups, false);

                    }
                else if(initial === 'EXHORTO'){
                    mcdGroups.forEach(el => el.style.display = 'none');
                    setRequiredInGroups(mcdGroups, false);
                    dqrGroups.forEach(el => el.style.display = 'none');
                    setRequiredInGroups(dqrGroups, false);
                    odGroups.forEach(el => el.style.display = 'none');
                    setRequiredInGroups(odGroups, false);
                    mfGroups.forEach(el => el.style.display = 'none');
                    setRequiredInGroups(mfGroups, false);
                    deGroups.forEach(el => el.style.display = 'none');
                    setRequiredInGroups(deGroups, false);
                } 
                else {
                    dqrGroups.forEach(el => el.style.display = '');
                    setRequiredInGroups(dqrGroups, true);
                    odGroups.forEach(el => el.style.display = '');
                    setRequiredInGroups(odGroups, true);
                    mfGroups.forEach(el => el.style.display = '');
                    setRequiredInGroups(mfGroups, true);
                    deGroups.forEach(el => el.style.display = '');
                    setRequiredInGroups(deGroups, true);
                        
                }
            } else {
                console.warn("No se encontró el select[name='quien_atiende']");
            }  

        });
        document.addEventListener('DOMContentLoaded', function () {
            const selectEstatus = document.getElementById('estatus');
            const problema1Div = document.getElementById('tipo_problema1');
            const problema2Div = document.getElementById('tipo_problema2');
            const abundarmotivoDiv = document.getElementById('abundar_motivo'); //Cuando se trata de tipo_problea1 o tipo_problema2, mostrar campo para describir el motivo
            const firmaDiv = document.getElementById('firma'); //Cuando se trata de tipo_problema2, no mostrar el apartado de firma
            const especificarDiv = document.getElementById('especificar_motivo'); // Campo de observaciones para hacer obligatorio cuando se seleccione Exitoso por instructivo
            function actualizarTipoProblema() {
                const valor = selectEstatus.value;

                // Oculta ambos divs inicialmente
                problema1Div.style.display = 'none';
                problema2Div.style.display = 'none';
                abundarmotivoDiv.style.display = 'none';
                firmaDiv.style.display = 'block';
                especificarDiv.style.display = 'none';

                // Deshabilita AMBOS selects para que no se envíen en el POST
                document.getElementById('problema1').disabled = true;
                document.getElementById('problema2').disabled = true;                
                especificarDiv.disabled = true; // Deshabilita el campo de observaciones al inicio
                document.getElementById('observaciones').disabled = true; // Asegura que el textarea de observaciones también esté deshabilitado al inicio

                if (valor === 'No exitosa se constituye') {
                    problema1Div.style.display = 'block';
                    abundarmotivoDiv.style.display = 'block';
                    firmaDiv.style.display = 'none';
                    // Habilita solo el select que se va a usar
                    document.getElementById('problema1').disabled = false; 
                } else if (valor === 'No exitosa no se constituye') {
                    problema2Div.style.display = 'block';
                    abundarmotivoDiv.style.display = 'block';
                    firmaDiv.style.display = 'none'; //
                    // Habilita solo el select que se va a usar
                    document.getElementById('problema2').disabled = false; 
                }
                else if(valor === 'Exitosa por Instructivo'){
                    document.getElementById('observaciones').value = ''; // Limpia el campo de observaciones al inicio
                    especificarDiv.style.display = 'block'; // Muestra el campo de observaciones
                    document.getElementById('observaciones').disabled = false; // Habilita el campo de observaciones   
                    document.getElementById('observaciones').setAttribute('required', ''); // Hacer obligatorio el campo de observaciones                 
                }
            }

            if (selectEstatus) {
                selectEstatus.addEventListener('change', actualizarTipoProblema);
                // Ejecutar al cargar por si ya tiene valor
                actualizarTipoProblema();
            }
        });
        //Ocultar media filiación cuando si se presenta una identificación
        document.addEventListener('DOMContentLoaded', function () {
            const selectQuienAtiende = document.querySelector('select[name="quien_atiende"]');
            const selectIdentificacion = document.getElementById('identificacion_notificacion');
            const mediaFiliacionDiv = document.getElementById('media-filiacion');
            const motivoIdenDiv = document.getElementById('motivo_identificacion');
            const numIdenDiv = document.getElementById('num_identificacion');
            const dqrGroups = Array.from(document.querySelectorAll('.dqr-group'));

            function setRequiredInGroups(groups, enabled) {
                groups.forEach(sectionEl => {
                    if (!sectionEl) return;
                    const fields = sectionEl.querySelectorAll('input, select, textarea');
                    fields.forEach(el => {
                        const shouldBeRequiredByDefault = el.hasAttribute('data-required-default') || el.hasAttribute('required');
                        if (enabled) {
                            if (shouldBeRequiredByDefault) el.setAttribute('required', '');
                        } else {
                            el.removeAttribute('required');
                        }
                    });
                });
            }
            function actualizarFormulario() {
                const quienAtiende = selectQuienAtiende ? selectQuienAtiende.value : '';
                const idenValor = selectIdentificacion ? selectIdentificacion.value : '';
                const numIdenValor = numIdenDiv ? numIdenDiv.value : '';

                if (idenValor === 'NO PROPORCIONA' || idenValor === 'NO ATIENDE PRESENCIALMENTE') {
                    motivoIdenDiv.style.display = "block";
                    numIdenDiv.style.display = "none";
                } else {
                    motivoIdenDiv.style.display = "none";
                    numIdenDiv.style.display = "block";
                }

                const personasQuePuedenRecibir = ['OTRA PERSONA', 'CITADO O REPRESENTANTE'];
                const sinIdentificacionValida = ['NO PROPORCIONA'];

                if (personasQuePuedenRecibir.includes(quienAtiende) && sinIdentificacionValida.includes(idenValor)) {
                    mediaFiliacionDiv.style.display = 'block';
                    setRequiredInGroups([mediaFiliacionDiv], true);
                } else {
                    mediaFiliacionDiv.style.display = 'none';
                    setRequiredInGroups([mediaFiliacionDiv], false);
                }
            }

            if (selectQuienAtiende) {
                selectQuienAtiende.addEventListener('change', actualizarFormulario);
            }
            if (selectIdentificacion) {
                selectIdentificacion.addEventListener('change', actualizarFormulario);

            }
            if (numIdenDiv) {
                numIdenDiv.addEventListener('input', actualizarFormulario);
            }

            actualizarFormulario();
        });
        /*const tipo_iden = document.getElementById('identificacion_notificacion');
        tipo_iden.addEventListener('change', function() {
            const valorSeleccionado = this.value;
            // Realiza la validación o acciones necesarias
            if (valorSeleccionado === 'NO PROPORCIONA') {
                document.getElementById('motivo_identificacion').style.display = "block";
            }
            else {
                document.getElementById('motivo_identificacion').style.display = "none";
            }
        });*/
    </script>
    @if(!empty($url_pdf))
    <script>
        window.addEventListener('load', function() {
            window.open('{!! $url_pdf !!}', '_blank');
        });
    </script>
    @endif
@endsection

<div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="../../public/assets/js/estadistica/estadistica.js"></script>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Inicializar select2 si aplica
            $('#medio').select2({
                placeholder: "Selecciona uno o más medios",
                allowClear: true
            });
        });
    </script>
@endpush