{{--
    Datos de un solicitante en Revisar audiencia. Cada solicitante manda sus
    campos como sol[i][campo] con su id (antes los campos no eran arreglo y,
    con varios solicitantes, sólo llegaba el último). Espera $solicitante,
    $i (posición) y $sfx (sufijo de los id: '' el primero, _1, _2…).
--}}
<input type="hidden" name="sol[{{ $i }}][id]" value="{{ $solicitante->id }}">
@if ($solicitantes->count() > 1)
    <div class="col-12">
        <h5 class="ra-sol__titulo"><span class="ra-parte__num">{{ $i + 1 }}</span> {{ \Illuminate\Support\Str::title(mb_strtolower($solicitante->nombre ?? 'Solicitante')) }}</h5>
    </div>
@endif
<div class="col-xs-12 col-sm-6 col-md-12">
    <div class="form-group">
        <label for="password">Nombre<span style="color:red;"> (*)</span></label>
        <input type="text" class="form-control" name="sol[{{ $i }}][nombre_solicitante]" value="<?=$solicitante["nombre"];?>" required>
        <div class="invalid-feedback">
            El campo nombre es obligatorio.
        </div>   
    </div>                                                    
</div>
<div class="col-xs-12 col-sm-6 col-md-3" hidden>
    <div class="form-group">
        <label for="confirm-password">Tipo de persona<span style="color:red;"> (*)</span></label>
        <select name="sol[{{ $i }}][tipo_persona_solicitante]" class="form-control" required>
            <option value="Fisica" {{ $solicitante["tipo_persona"] == 'Fisica' ? "selected" : '' }}>Física</option>
            <option value="Moral"  {{ $solicitante['tipo_persona'] == 'Moral' ? "selected" : '' }}>Moral</option>
        </select>
        <div class="invalid-feedback">
            El campo tipo de persona es obligatorio.
        </div>
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-6">
    <div class="form-group">
        <label for="confirm-password">CURP<span style="color:red;"> (*)</span></label>
        <input type="text" class="form-control" name="sol[{{ $i }}][curp_solicitante]" value="<?=$solicitante["curp"];?>" required>
        <div class="invalid-feedback">
            El campo curp es obligatorio.
        </div>
    </div>
</div>

<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">RFC</label>
        <input type="text" class="form-control" name="sol[{{ $i }}][rfc_solicitante]" value="<?=$solicitante["rfc"];?>">   
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="confirm-password">Sexo<span style="color:red;"> (*)</span></label>
        <select name="sol[{{ $i }}][sexo_solicitante]" class="form-control" required>
            <option value="H" {{ $solicitante["sexo"] == 'H' ? "selected" : '' }}>Hombre</option>
            <option value="M"  {{ $solicitante['sexo'] == 'M' ? "selected" : '' }}>Mujer</option>
            <option value="NB"  {{ $solicitante['sexo'] == 'NB' ? "selected" : '' }}>No Binario</option>
            <option value="LGBTTTIQ"  {{ $solicitante['sexo'] == 'LGBTTTIQ' ? "selected" : '' }}>LGBTTTIQ+</option>
        </select>
        <div class="invalid-feedback">
            El campo curp es obligatorio.
        </div>
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="confirm-password">Nacionalidad<span style="color:red;"> (*)</span></label>
        <select name="sol[{{ $i }}][nacionalidad_solicitante]" class="form-control" required>
            <option value="Mexicana" {{ $solicitante["sexo"] == 'Mexicana' ? "selected" : '' }}>Mexicana</option>
            <option value="Otra"  {{ $solicitante['sexo'] == 'Otra' ? "selected" : '' }}>Otra</option>
        </select>
        <div class="invalid-feedback">
            El campo nacionalidad es obligatorio.
        </div>
    </div>
</div>

<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Email<span style="color:red;"> (*)</span></label>
        <input type="email" class="form-control" name="sol[{{ $i }}][email_solicitante]" value="<?=$solicitante["email"];?>" required>
        <div class="invalid-feedback">
            El campo email es obligatorio.
        </div>   
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Fecha nacimiento<span style="color:red;"> (*)</span></label>
        <input type="date" class="form-control" name="sol[{{ $i }}][fecha_nacimiento_solicitante]" value="<?=$solicitante["fecha_nacimiento"];?>" required>
        <div class="invalid-feedback">
            El fecha nacimiento es obligatorio.
        </div>   
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Edad<span style="color:red;"> (*)</span></label>
        <input type="text" class="form-control" name="sol[{{ $i }}][edad_solicitante]" value="<?=$solicitante["edad"];?>" required>
        <div class="invalid-feedback">
            El campo edad es obligatorio.
        </div>   
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Teléfono<span style="color:red;"> (*)</span></label>
        <input type="number" id="telefono1_solicitante{{ $sfx }}" class="form-control" name="sol[{{ $i }}][telefono1_solicitante]" value="<?=$solicitante["telefono1"];?>" required inputmode="numeric" maxlength="10">
        <div class="invalid-feedback">
            El teléfono es obligatorio y debe contener 10 dígitos.
        </div>   
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
            <label for="password">Teléfono (Opcional)</label>
            <input type="number" id="telefono2_solicitante{{ $sfx }}" class="form-control" name="sol[{{ $i }}][telefono2_solicitante]" value="<?=$solicitante["telefono2"];?>" inputmode="numeric" maxlength="10">   
        </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="confirm-password">Requiere traductor<span style="color:red;"> (*)</span></label>
        <select name="sol[{{ $i }}][traductor_solicitante]" id="needsLanguageSolicitante{{ $sfx }}" class="form-control" required>
            <option value="Si" {{ $solicitante["traductor"] == 'Si' ? "selected" : '' }}>SI</option>
            <option value="No"  {{ $solicitante['traductor'] == 'No' ? "selected" : '' }}>NO</option>
        </select>
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3" id="languageRequired{{ $sfx }}" hidden>
    <div class="form-group">
        <label for="password">Lenguaje requerido<span style="color:red;"> (*)</span></label>
        <input type="text" class="form-control" name="sol[{{ $i }}][lenguaje_solicitante]" id="languageValueSolicitante{{ $sfx }}" value="<?=$solicitante["lenguaje"];?>">   
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="confirm-password">Tiene discapacidad<span style="color:red;"> (*)</span></label>
        <select name="sol[{{ $i }}][discapacidad_solicitante]" id="hasDisabilitySolicitante{{ $sfx }}" class="form-control" required>
            <option value="Si" {{ $solicitante["discapacidad"] == 'Si' ? "selected" : '' }}>SI</option>
            <option value="No"  {{ $solicitante['discapacidad'] == 'No' ? "selected" : '' }}>NO</option>
        </select>
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3" id="disabilityRequired{{ $sfx }}" hidden>
    <div class="form-group">
        <label for="password">Discapacidad<span style="color:red;"> (*)</span></label>
        <input type="text" class="form-control" name="sol[{{ $i }}][disc_solicitante]" id="disabilityValueSolicitante{{ $sfx }}" value="<?=$solicitante["tipo_discapacidad"];?>">   
    </div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
    <div class="form-group">
        <h4 class="text-center">Dirección del solicitante</h4>
    </div>
</div>

<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Entidad Federativa<span style="color:red;"> (*)</span></label>
        <select id="estado_solicitante{{ $sfx }}" class="form-control" name="sol[{{ $i }}][estado_solicitante]" required>
            @foreach($estados as $est)
                <option value="{{$est['id']}}" {{ $solicitante['estado'] == $est['id'] ? "selected" : '' }}>{{$est['nombre']}}</option>
            @endforeach
        </select>
        <div class="invalid-feedback">
            El campo entidad federativa es obligatorio.
        </div>
    </div>
</div>

<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Municipio<span style="color:red;"> (*)</span></label>
        <select class="form-control" name="sol[{{ $i }}][municipio_solicitante]" required>
            @foreach($municipios as $mun)
                <option value="{{$mun['id']}}" {{ $solicitante['municipio_domicilio'] == $mun['id'] ? "selected" : '' }}>{{$mun['nombre']}}</option>
            @endforeach
        </select>
        <div class="invalid-feedback">
            El campo municipio es obligatorio.
        </div>
    </div>
</div>

<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Tipo de vialidad<span style="color:red;"> (*)</span></label>
        <!--input type="text" class="form-control" name="sol[{{ $i }}][tipo_vialidad]" value="<?=$solicitante["tipo_vialidad"];?>" required-->
        <select name="sol[{{ $i }}][tipo_vialidad]" class="form-control" required>
            <option value="">SELECCIONE</option>
            <option value="CALLE"          {{ $solicitante['tipo_vialidad'] == 'CALLE' ? "selected" : '' }}   >Calle</option>
            <option value="AVENIDA"        {{ $solicitante['tipo_vialidad'] == 'AVENIDA' ? "selected" : '' }} >Avenida</option>
            <option value="CALZADA"        {{ $solicitante['tipo_vialidad'] == 'CALZADA' ? "selected" : '' }} >Calzada</option>
            <option value="BOULEVARD"      {{ $solicitante['tipo_vialidad'] == 'BOULEVARD' ? "selected" : '' }} >Boulevard</option>
            <option value="AMPLIACIÓN"     {{ $solicitante['tipo_vialidad'] == 'AMPLIACIÓN' ? "selected" : '' }} >Ampliación</option>
            <option value="ANDADOR"        {{ $solicitante['tipo_vialidad'] == 'ANDADOR' ? "selected" : '' }} >Andador</option>
            <option value="AUTOPISTA"      {{ $solicitante['tipo_vialidad'] == 'AUTOPISTA' ? "selected" : '' }} >Autopista</option>
            <option value="CALLEJÓN"       {{ $solicitante['tipo_vialidad'] == 'CALLEJÓN' ? "selected" : '' }}>Callejón</option>
            <option value="CARRETERA"      {{ $solicitante['tipo_vialidad'] == 'CARRETERA' ? "selected" : '' }}   >Carretera</option>
            <option value="CERRADA"        {{ $solicitante['tipo_vialidad'] == 'CERRADA' ? "selected" : '' }} >Cerrada</option>
            <option value="CIRCUITO"       {{ $solicitante['tipo_vialidad'] == 'CIRCUITO' ? "selected" : '' }} >Circuito</option>
            <option value="CIRCUNVALACIÓN" {{ $solicitante['tipo_vialidad'] == 'CIRCUNVALACIÓN' ? "selected" : '' }} >Circunvalación</option>
            <option value="CONTINUACIÓN"   {{ $solicitante['tipo_vialidad'] == 'CONTINUACIÓN' ? "selected" : '' }} >Continuación</option>
            <option value="CORREDOR"       {{ $solicitante['tipo_vialidad'] == 'CORREDOR' ? "selected" : '' }} >Corredor</option>
            <option value="DIAGONAL"       {{ $solicitante['tipo_vialidad'] == 'DIAGONAL' ? "selected" : '' }} >Diagonal</option>
            <option value="EJE VIAL"       {{ $solicitante['tipo_vialidad'] == 'EJE VIAL' ? "selected" : '' }}>Eje vial</option>
            <option value="PERIFÉRICO"     {{ $solicitante['tipo_vialidad'] == 'PERIFÉRICO' ? "selected" : '' }}   >Periférico</option>
            <option value="PRIVADA"        {{ $solicitante['tipo_vialidad'] == 'PRIVADA' ? "selected" : '' }} >Privada</option>
            <option value="PROLONGACIÓN"   {{ $solicitante['tipo_vialidad'] == 'PROLONGACIÓN' ? "selected" : '' }} >Prolongación</option>
            <option value="RETORNO"        {{ $solicitante['tipo_vialidad'] == 'RETORNO' ? "selected" : '' }} >Retorno</option>
            <option value="VIADUCTO"       {{ $solicitante['tipo_vialidad'] == 'VIADUCTO' ? "selected" : '' }} >Viaducto</option>
            <option value="PASEO"       {{ $solicitante['tipo_vialidad'] == 'PASEO' ? "selected" : '' }} >Paseo</option>
        </select>
        <div class="invalid-feedback">
            El campo tipo de vialidad es obligatorio.
        </div>   
    </div>
</div>

<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Nombre de la vialidad<span style="color:red;"> (*)</span></label>
        <input type="text" class="form-control" name="sol[{{ $i }}][calle_solicitante]" value="<?=$solicitante["calle"];?>" required>
        <div class="invalid-feedback">
            El campo nombre de la vialidad es obligatorio.
        </div>   
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Núm. Ext.<span style="color:red;"> (*)</span></label>
        <input type="text" class="form-control" name="sol[{{ $i }}][num_ext_solicitante]" value="<?=$solicitante["num_ext"];?>" required>
        <div class="invalid-feedback">
            El campo Núm. Ext. es obligatorio.
        </div>   
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Núm. Int.</label>
        <input type="text" class="form-control" name="sol[{{ $i }}][num_int_solicitante]" value="<?=$solicitante["num_int"];?>">   
    </div>
</div>

<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Colonia<span style="color:red;"> (*)</span></label>
        <input type="text" class="form-control" name="sol[{{ $i }}][colonia_solicitante]" value="<?=$solicitante["colonia"];?>" required>
        <div class="invalid-feedback">
            El campo colonia es obligatorio.
        </div>   
    </div>
</div>

<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="form-group">
        <label for="password">Código postal<span style="color:red;"> (*)</span></label>
        <input type="text" class="form-control" name="sol[{{ $i }}][codigo_postal_solicitante]" value="<?=$solicitante["codigo_postal"];?>" required>
        <div class="invalid-feedback">
            El campo código postal es obligatorio.
        </div>   
    </div>
</div>

<div class="col-xs-12 col-sm-6 col-md-4">
    <div class="form-group">
        <label for="password">Referencia</label>
        <input type="text" class="form-control" name="sol[{{ $i }}][referencia_solicitante]" value="<?=$solicitante["referencia"];?>">   
    </div>
</div>

<div class="col-xs-12 col-sm-6 col-md-4">
    <div class="form-group">
        <label for="password">Entre calle</label>
        <input type="text" class="form-control" name="sol[{{ $i }}][calle2_solicitante]" value="<?=$solicitante["calle2"];?>">   
    </div>
</div>
<div class="col-xs-12 col-sm-6 col-md-4">
    <div class="form-group">
        <label for="password">Y calle</label>
        <input type="text" class="form-control" name="sol[{{ $i }}][calle3_solicitante]" value="<?=$solicitante["calle3"];?>">   
    </div>
</div>

@if($general->tipo_solicitud == 2)
    @include('audiencias._revisar_rep_resumen')
@endif

@if ($general->tipo_solicitud == 1) 
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <h4 class="text-center">Datos Laborales</h4>
        </div>
    </div>
        
    <div class="col-xs-12 col-sm-6 col-md-3">
        <div class="form-group">
            <label for="password">Seguro Social</label>
            <input type="text" class="form-control" name="sol[{{ $i }}][nss]" value="<?=$solicitante["nss"];?>">   
        </div>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-3">
        <div class="form-group">
            <label for="password">Puesto<span style="color:red;"> (*)</span></label>
            <input type="text" class="form-control" name="sol[{{ $i }}][puesto]" value="<?=$solicitante["puesto"];?>" required>
            <div class="invalid-feedback">
                El campo puesto es obligatorio.
            </div>   
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-3">
        <div class="form-group">
            <label for="name">Periodo de pago<span style="color:red;"> (*)</span></label>
            <select name="sol[{{ $i }}][periodo_pago]" class="form-control" required>
                <option value="">SELECCIONE</option>
                <option value="Semanal"      {{ $solicitante['periodo_pago'] == 'Semanal' ? "selected" : '' }}>SEMANAL</option>
                <option value="Quincenal"   {{ $solicitante['periodo_pago'] == 'Quincenal' ? "selected" : '' }}>QUINCENAL</option>
                <option value="Mensual"     {{ $solicitante['periodo_pago'] == 'Mensual' ? "selected" : '' }}>MENSUAL</option>
                <option value="Diario"      {{ $solicitante['periodo_pago'] == 'Diario' ? "selected" : '' }}>DIARIO</option>
            </select>
            <div class="invalid-feedback">
                El campo periodo de pago es obligatorio.
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-3">
        <div class="form-group">
            <label for="password">Sueldo<span style="color:red;"> (*)</span></label>
            <input type="text" class="form-control" name="sol[{{ $i }}][pago]" value="<?=$solicitante["pago"];?>" required>
            <div class="invalid-feedback">
                El campo sueldo es obligatorio.
            </div>   
        </div>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-2">
        <div class="form-group">
            <label for="password">Fecha de ingreso<span style="color:red;"> (*)</span></label>
            <input type="date" class="form-control" name="sol[{{ $i }}][fecha_ingreso]" value="<?=$solicitante["fecha_ingreso"];?>" required>
            <div class="invalid-feedback">
                El campo fecha de ingreso es obligatorio.
            </div>   
        </div>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-2" id="fechaSalida{{ $sfx }}" hidden>
        <div class="form-group">
            <label for="password">Fecha de salida</label>
            <input type="date" class="form-control" name="sol[{{ $i }}][fecha_salida]" value="<?=$solicitante["fecha_salida"];?>">   
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4">
        <div class="form-group">
            <label for="name">Horario laboral<span style="color:red;"> (*)</span></label>
            <input type="text" name="sol[{{ $i }}][jornada]" class="form-control" value="<?=$solicitante["jornada"];?>" required>
            <div class="invalid-feedback">
                El campo horario laboral es obligatoria.
            </div>
        </div>
    </div>
    
    <div class="col-xs-12 col-sm-6 col-md-2">
        <div class="form-group">
            <label for="password">Horas trabajadas a la semana<span style="color:red;"> (*)</span></label>
            <input type="number" class="form-control" min="0" name="sol[{{ $i }}][horas_semana]" value="<?=$solicitante["horas_semana"];?>" required>
            <div class="invalid-feedback">
                El campo horas trabajadas es obligatorio.
            </div>   
        </div>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-2">
        <div class="form-group">
            <label for="password">Labora actualmente<span style="color:red;"> (*)</span></label>
            <!--input type="text" class="form-control" name="sol[{{ $i }}][labora]" id="laboraActualmenteValue{{ $sfx }}" value="<?=$solicitante["labora"];?>" required-->
            <select name="sol[{{ $i }}][labora]" id="laboraActualmenteValue{{ $sfx }}" class="form-control" required>
                <option value="Si" {{ $solicitante["labora"] == 'Si' ? "selected" : '' }}>SI</option>
                <option value="No"  {{ $solicitante['labora'] == 'No' ? "selected" : '' }}>NO</option>
            </select>
            <div class="invalid-feedback">
                El campo labora actualmente es obligatorio.
            </div>   
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <label for="name">Describe brevemente el motivo de tu solicitud <span style="color:red;">(*)</span></label>
            <textarea class="form-control js-autoalto" name="sol[{{ $i }}][descripcionSolicitud]" rows="3" required>{{ $solicitante["descripcionSolicitud"] ?? '' }}</textarea>
            <div class="invalid-feedback">
                El campo descripción del motivo de la solicitud es obligatorio.
            </div>
        </div>
    </div>
@endif

