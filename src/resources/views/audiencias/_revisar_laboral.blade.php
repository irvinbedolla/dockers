{{--
    Relación laboral cuando solicita el patrón (tipo 2): se guarda en el
    solicitante (sol[0][...]) y ahora sale una sola vez, no dentro de cada
    citado. Espera $solicitante, $i y $sfx.
--}}

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
        <textarea class="form-control" name="sol[{{ $i }}][descripcionSolicitud]" required>{{ $solicitante["descripcionSolicitud"] ?? '' }}</textarea>
        <div class="invalid-feedback">
            El campo descripción del motivo de la solicitud es obligatorio.
        </div>
    </div>
</div>
