@extends('layouts.app')

<style>
    /* Style the tab */
    .tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #f1f1f1;
    }
    
    /* Style the buttons that are used to open the tab content */
    .tab button {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
    }
    
    /* Change background color of buttons on hover */
    .tab button:hover {
        background-color: #ddd;
    }
    
    /* Create an active/current tablink class */
    .tab button.active {
        background-color: #ccc;
    }
    
    /* Style the tab content */
    .tabcontent {
        display: none;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-top: none;
    }
    
body {font-family: Arial;}

/* Style the tab */
.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}

/* Style the buttons inside the tab */
.tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
  font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
}
select[name="vialidad"] option {
    text-transform: uppercase;
}
select[name="estado_citado"] option {
    text-transform: uppercase;
}
select[name="municipio_citado"] option {
    text-transform: uppercase;
}

.was-validated .form-control:invalid ~ .invalid-feedback,
.was-validated select.form-control:invalid ~ .invalid-feedback,
.was-validated .form-select:invalid ~ .invalid-feedback {
    display: block;
}
</style>

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Revisar Solicitud</h3>
        </div>
        <div class="section-body">
            <?php $fecha_actual = date('d-m-Y');?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Detalles de la Solicitud</h3>
                            @if(session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>¡Registro correcto!</strong>
                                    {{ session()->get('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

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

                                <form class="needs-validation" novalidate method="POST" action="{{route('confirmar_solicitud')}}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$id}}">
                                    <input type="hidden" name="consecutivo" value="{{ $general['consecutivo'] ?? '' }}">
                                    <div class="tab">
                                        <a class="btn btn-info" onclick="openCity(event, 'detalles')">Detalles</a>
                                        <a class="btn btn-info" onclick="openCity(event, 'solicitante')">Solicitante</a>
                                        <a class="btn btn-info" onclick="openCity(event, 'documentos')">Citado(s)</a>
                                        <a class="btn btn-info" onclick="openCity(event, 'observaciones')">Observaciones</a>
                                        <a class="btn btn-info" onclick="openCity(event, 'citados')">Documentos</a>
                                        <a class="btn btn-success" onclick="openCity(event, 'confirmacion')">Acciones</a>
                                    </div>

                                    <div id="detalles" class="tabcontent">
                                        <div id="tabla_detalles" class="row">
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Fecha de registro</label>
                                                    <input type="date" class="form-control" value="<?=$general["fecha"];?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-9">
                                                <div class="form-group">
                                                    <label for="password">Actividad económica<span style="color:red;"> (*)</span></label>
                                                    <input type="text" name="actividad_economica" class="form-control" value="<?=$general["actividad"];?>" required>
                                                    <div class="invalid-feedback">
                                                        La actividad económica es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <label for="name">Rama industrial del negocio<span style="color:red;"> (*)</span></label>
                                                    <select class="form-control" name="ramaIndustrial" required>
                                                        <option value="">Seleccione</option>
                                                        @foreach($ramas as $rama)
                                                            <option value="{{$rama['id']}}" {{ $rama["id"] == $general["id_rama"] ? "selected" : '' }} >{{$rama['rama_industrial']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El rama industrial es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                        

                                            <div class="col-xs-12 col-sm-12 col-md-12"><br>
                                                <table  class="table table-striped mt-1" style="margin: 0 center; text-align:center;">
                                                    <thead style="background-color: #D2D3D5;">
                                                        <th style="color: black;">Motivo capturado</th>
                                                        <th style="color: black;">Acción</th>
                                                    </thead>
                                                    <tbody>
                                                         @foreach($motivos as $motivo)
                                                            <tr>
                                                                <td>
                                                                    <option value="{{$motivo['id']}}">{{$motivo['motivo']}}</option>
                                                                </td>  
                                                                                     <td>
                                                                                         <a href="{{ route('eliminar_motivo_solicitud', ['id' => $id, 'id_motivo' => $motivo->id] ) }}" class="eliminar btn btn-danger btn-sm">Eliminar</a>
                                                                                     </td>   
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                       
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Agregar otro motivo a la solicitud</label>
                                                    <select  class="form-control" id="motivo_solicitud">
                                                        <option value="">Seleccione</option>
                                                        @foreach($mostrarMotivos as $motivo)
                                                            <option value="{{$motivo['id']}}">{{$motivo['motivo']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El objeto de solicitud es obligatoria.
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="div1"  class="col-xs-12 col-sm-12 col-md-12"><br>
                                                <table id="tabla" name="motivo_solicitud[]" class="table table-striped mt-1" style="margin: 0 center; text-align:center;">
                                                    <thead style="background-color: #D2D3D5;">
                                                        <th style="color: black;">Objeto de la Solicitud</th>
                                                        <th style="color: black;">Acción</th>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="solicitante" class="tabcontent">
                                        <div id="tabla_solicitante" class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Solicitante</h4>
                                                </div>
                                            </div>
                                            @foreach($solicitantes as $solicitante)
                                                <div class="col-xs-12 col-sm-6 col-md-12">
                                                    <div class="form-group">
                                                        <label for="password">Nombre<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="nombre_solicitante" value="<?=$solicitante["nombre"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo nombre es obligatorio.
                                                        </div>   
                                                    </div>                                                    
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3" hidden>
                                                    <div class="form-group">
                                                        <label for="confirm-password">Tipo de persona<span style="color:red;"> (*)</span></label>
                                                        <select name="tipo_persona_solicitante" class="form-control" required>
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
                                                        <input type="text" class="form-control" name="curp_solicitante" value="<?=$solicitante["curp"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo curp es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">RFC</label>
                                                        <input type="text" class="form-control" name="rfc_solicitante" value="<?=$solicitante["rfc"];?>">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="confirm-password">Sexo<span style="color:red;"> (*)</span></label>
                                                        <select name="sexo_solicitante" class="form-control" required>
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
                                                        <select name="nacionalidad_solicitante" class="form-control" required>
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
                                                        <input type="email" class="form-control" name="email_solicitante" value="<?=$solicitante["email"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo email es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Fecha nacimiento<span style="color:red;"> (*)</span></label>
                                                        <input type="date" class="form-control" name="fecha_nacimiento_solicitante" value="<?=$solicitante["fecha_nacimiento"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El fecha nacimiento es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Edad<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="edad_solicitante" value="<?=$solicitante["edad"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo edad es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Teléfono<span style="color:red;"> (*)</span></label>
                                                        <input type="tel" id="telefono1_solicitante" class="form-control" name="telefono1_solicitante" value="<?=$solicitante["telefono1"];?>" required inputmode="numeric" maxlength="14">
                                                        <div class="invalid-feedback">
                                                            El teléfono es obligatorio y debe contener 10 dígitos.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Teléfono (Opcional)</label>
                                                        <input type="tel" id="telefono2_solicitante" class="form-control" name="telefono2_solicitante" value="<?=$solicitante["telefono2"];?>" inputmode="numeric" maxlength="14">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="confirm-password">Requiere traductor<span style="color:red;"> (*)</span></label>
                                                        <select name="traductor_solicitante" id="needsLanguageSolicitante" class="form-control" required>
                                                            <option value="Si" {{ $solicitante["traductor"] == 'Si' ? "selected" : '' }}>SI</option>
                                                            <option value="No"  {{ $solicitante['traductor'] == 'No' ? "selected" : '' }}>NO</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3" id="languageRequired" hidden>
                                                    <div class="form-group">
                                                        <label for="password">Lenguaje requerido<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="lenguaje_solicitante" id="languageValueSolicitante" value="<?=$solicitante["lenguaje"];?>">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="confirm-password">Tiene discapacidad<span style="color:red;"> (*)</span></label>
                                                        <select name="discapacidad_solicitante" id="hasDisabilitySolicitante" class="form-control" required>
                                                            <option value="Si" {{ $solicitante["discapacidad"] == 'Si' ? "selected" : '' }}>SI</option>
                                                            <option value="No"  {{ $solicitante['discapacidad'] == 'No' ? "selected" : '' }}>NO</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3" id="disabilityRequired" hidden>
                                                    <div class="form-group">
                                                        <label for="password">Discapacidad<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="disc_solicitante" id="disabilityValueSolicitante" value="<?=$solicitante["tipo_discapacidad"];?>">   
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
                                                        <select id="estado_solicitante" class="form-control" name="estado_solicitante" required>
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
                                                        <select class="form-control" name="municipio_solicitante" required>
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
                                                        <!--input type="text" class="form-control" name="tipo_vialidad" value="<?=$solicitante["tipo_vialidad"];?>" required-->
                                                        <select name="tipo_vialidad" class="form-control" required>
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
                                                        <input type="text" class="form-control" name="calle_solicitante" value="<?=$solicitante["calle"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo nombre de la vialidad es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Núm. Ext.<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="num_ext_solicitante" value="<?=$solicitante["num_ext"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo Núm. Ext. es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Núm. Int.</label>
                                                        <input type="text" class="form-control" name="num_int_solicitante" value="<?=$solicitante["num_int"];?>">   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Colonia<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="colonia_solicitante" value="<?=$solicitante["colonia"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo colonia es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Código postal<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="codigo_postal_solicitante" value="<?=$solicitante["codigo_postal"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo código postal es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Referencia</label>
                                                        <input type="text" class="form-control" name="referencia_solicitante" value="<?=$solicitante["referencia"];?>">   
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Entre calle</label>
                                                        <input type="text" class="form-control" name="calle2_solicitante" value="<?=$solicitante["calle2"];?>">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Y calle</label>
                                                        <input type="text" class="form-control" name="calle3_solicitante" value="<?=$solicitante["calle3"];?>">   
                                                    </div>
                                                </div>

                                                @if($general->tipo_solicitud == 2)
                                                    @if(optional($solicitante->poder)->reprecentante == 'Si')
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h4 class="text-center">Datos del representante</h4>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                        <div class="form-group">
                                                            <h5 class="text-center" style="color:#CEA845">Información del Representante Legal</h5>
                                                        </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h5 class="text-center">Datos de identificación</h5>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="name">Nombre(s) del representante<span style="color:red;">(*)</span></label>
                                                                <input type="text" name="nombre_representante_pF" value="{{ $solicitante->poder->nombre_representante }}" class="form-control" oninput="this.value = this.value.toUpperCase()" disabled> 
                                                                <div class="invalid-feedback">
                                                                    El nombre es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                                <input type="text" name="primer_representante_pF" value="{{ $solicitante->poder->primer_apellido_representante }}" class="form-control" oninput="this.value = this.value.toUpperCase()" disabled> 
                                                                <div class="invalid-feedback">
                                                                    El primer apellido es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                                <input type="text" name="segundo_representante_pF" value="{{ $solicitante->poder->segundo_apellido_representante }}" class="form-control" oninput="this.value = this.value.toUpperCase()" disabled> 
                                                                <div class="invalid-feedback">
                                                                    El segundo apellido es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>  
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="">CURP</label>
                                                                <input type="text" class="form-control" name="curp_representante_pF" value="{{$solicitante->poder->curp_representante }}" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" disabled>
                                                                <div class="invalid-feedback">
                                                                    La CURP es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                                                <select name="sexo_representante_pF" id="sexo_representante_pF" class="form-control" disabled>
                                                                    <option value="">Seleccione</option>
                                                                    <option value="Femenino"    {{ $solicitante->poder["sexo_representante"] == "Femenino" ? "selected" : '' }}>Femenino</option>
                                                                    <option value="Masculino"   {{ $solicitante->poder["sexo_representante"] == "Masculino" ? "selected" : '' }}>Masculino</option>
                                                                    <option value="Prefiero no responder">Prefiero no responder</option>
                                                                </select>
                                                                <div class="invalid-feedback">
                                                                    El tipo de persona es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                        <div class="form-group">
                                                                <h5 class="text-center">Datos de contacto</h5>
                                                            </div>
                                                        </div> 

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="">Correo electrónico <span style="color:red;">(*)</span></label>
                                                                <input type="email" class="form-control" name="correo_representante_pF" value="{{$solicitante->poder->correo_representante }}" disabled>
                                                                <div class="invalid-feedback">
                                                                    El Correo electrónico es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="">Teléfono <span style="color:red;">(*)</span></label>
                                                                <input type="text" class="form-control"  name="telefono_representante_pF" value="{{$solicitante->poder->numero_representante }}" maxlength="10" pattern="[0-9]+" disabled>
                                                                <div class="invalid-feedback">
                                                                    El telefono es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                                <div class="form-group">
                                                                    <label for="name">Identificación Oficial<span style="color:red;">(*)</span></label>
                                                                    <select  name="tipo_identificacion_pFCR" class="form-control" disabled>
                                                                        <option value="">Seleccione el tipo de indentificación</option>
                                                                        <option value="Credencial de elector" {{ $solicitante->poder["tipo_identificacion"] == "Credencial de elector" ? "selected" : '' }} >Credencial de Elector</option>
                                                                        <option value="Pasaporte" {{ $solicitante->poder["tipo_identificacion"] == "Pasaporte" ? "selected" : '' }}>Pasaporte</option>
                                                                        <option value="Cédula profesional" {{ $solicitante->poder["tipo_identificacion"] == "Cédula profesional" ? "selected" : '' }}>Cédula Profesional</option>
                                                                        <option value="Licencia de conducir" {{ $solicitante->poder["tipo_identificacion"] == "Licencia de conducir" ? "selected" : '' }}>Licencia de Conducir</option>
                                                                        <option value="Credencial de inapam" {{ $solicitante->poder["tipo_identificacion"] == "Credencial de inapam" ? "selected" : '' }}>Credencial de INAPAM</option>
                                                                        <option value="Cartilla militar" {{ $solicitante->poder["tipo_identificacion"] == "Cartilla militar" ? "selected" : '' }}>Cartilla Militar</option>
                                                                        <option value="Documento migratorio" {{ $solicitante->poder["tipo_identificacion"] == "Documento migratorio" ? "selected" : '' }}>Documento Migratorio</option>
                                                                        <option value="Constancia de identidad" {{ $solicitante->poder["tipo_identificacion"] == "Constancia de identidad" ? "selected" : '' }}>Constancia de Identidad</option>
                                                                        <option value="Otro" {{ $solicitante->poder["tipo_identificacion"] == "Otro" ? "selected" : '' }}>Otros</option>
                                                                    </select>
                                                                    <div class="invalid-feedback">
                                                                        Este campo identificación es obligatorio.
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                                <div class="form-group">
                                                                    <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                                    <input type="text" name="num_identificacion_pFCR" class="form-control" oninput="this.value = this.value.toUpperCase()"  value="{{$solicitante->poder->num_identificacion }}" disabled> 
                                                                    <div class="invalid-feedback">
                                                                        El campo núm. de identificación es obligatorio.
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h5 class="text-center" style="color:#CEA845">Datos de la documentación que acredite la personeria</h5>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-4">  
                                                            <div class="form-group">
                                                                <label for="name">Tipo de documento <span style="color:red;">(*)</span></label>
                                                                <select name="tipo_documento_pF" id="tipo_documento_pF" class="form-control" disabled>
                                                                    <option value="">Seleccione</option>
                                                                    <option value="Carta Poder" {{ $solicitante->poder["tipo_documento_representante"] == "Carta Poder" ? "selected" : '' }}>Carta Poder</option>
                                                                    <option value="Instrumento Notarial" {{ $solicitante->poder["tipo_documento_representante"] == "Instrumento Notarial" ? "selected" : '' }}>Instrumento Notarial</option>
                                                                </select>
                                                                <div class="invalid-feedback">
                                                                    El campo es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                                            <div class="form-group">
                                                                <label for="">Fecha expedición <span style="color:red;">(*)</span></label>
                                                                <input type="date" class="form-control" name="fecha_expedicion_pF" value="{{$solicitante->poder->fechaRegistro }}" disabled>
                                                                <div class="invalid-feedback">
                                                                    La fecha es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                                            <div class="form-group">
                                                                <label for="">Fecha vigencia</label>
                                                                <input type="date" class="form-control" name="fecha_vigencia_pF" value="{{$solicitante->poder->fechaVigencia }}" disabled>
                                                                <div class="invalid-feedback">
                                                                    La fecha es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>  
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Descripción del documento que acredite la personaria</label>
                                                                <textarea class="form-control" aria-describedby="basic-addon1" name="descripcion_pF" disabled>{{$solicitante->poder->descipcion_poder}}</textarea>
                                                                <div class="invalid-feedback">
                                                                    La descripción es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h5 class="text-center" style="color:#CEA845">Documentos del Solicitante</h5>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                @if($solicitante->poder->tipo == 'Fisica')
                                                                    <label>*Identificación del Empleador</label><br>
                                                                @else
                                                                    <label>*Acta Constitutiva</label><br>
                                                                @endif
                                                                <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->idAbogado}}/{{$solicitante->poder->ineDocumento}}">Existente</a>
                                                                <div class="invalid-feedback">
                                                                    La Identificación es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Identificación del Representante Legal</label><br>
                                                                @if($solicitante->poder->representacionDocumento != NULL)
                                                                    <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->idAbogado}}/{{$solicitante->poder->representacionDocumento}}">Existente</a>
                                                                @endif
                                                                <div class="invalid-feedback">
                                                                    El documento de representación es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Documento que acredite la personería</label><br>
                                                                @if($solicitante->poder->cedulaDocumento != NULL)
                                                                    <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->idAbogado}}/{{$solicitante->poder->cedulaDocumento}}">Existente</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>Anexo (Documentos Complementarios)</label><br>
                                                                @if($solicitante->poder->anexo_documeto != "Sin anexo")
                                                                    <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->idAbogado}}/{{$solicitante->poder->anexo_documeto}}">Existente</a>
                                                                @else
                                                                    <a class="btn btn-secondary disabled" href="#" tabindex="-1" aria-disabled="true">Sin anexo</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h4 class="text-center">Documentos del representante</h4>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Identificación del Empleador</label><br>
                                                                <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->idAbogado}}/{{$solicitante->poder->ineDocumento}}">Existente</a>
                                                                <div class="invalid-feedback">
                                                                    La Identificación es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>Anexo (Documentos Complementarios)</label><br>
                                                                @if($solicitante->poder->anexo_documeto != "Sin anexo")
                                                                    <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->idAbogado}}/{{$solicitante->poder->anexo_documeto}}">Existente</a>
                                                                @else
                                                                    <a class="btn btn-secondary disabled" href="#" tabindex="-1" aria-disabled="true">Sin anexo</a>
                                                                @endif
                                                            </div>
                                                        </div>

                                                    @endif
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
                                                            <input type="text" class="form-control" name="nss" value="<?=$solicitante["nss"];?>">   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-3">
                                                        <div class="form-group">
                                                            <label for="password">Puesto<span style="color:red;"> (*)</span></label>
                                                            <input type="text" class="form-control" name="puesto" value="<?=$solicitante["puesto"];?>" required>
                                                            <div class="invalid-feedback">
                                                                El campo puesto es obligatorio.
                                                            </div>   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <div class="form-group">
                                                            <label for="name">Periodo de pago<span style="color:red;"> (*)</span></label>
                                                            <select name="periodo_pago" class="form-control" required>
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
                                                            <input type="text" class="form-control" name="pago" value="<?=$solicitante["pago"];?>" required>
                                                            <div class="invalid-feedback">
                                                                El campo sueldo es obligatorio.
                                                            </div>   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-2">
                                                        <div class="form-group">
                                                            <label for="password">Fecha de ingreso<span style="color:red;"> (*)</span></label>
                                                            <input type="date" class="form-control" name="fecha_ingreso" value="<?=$solicitante["fecha_ingreso"];?>" required>
                                                            <div class="invalid-feedback">
                                                                El campo fecha de ingreso es obligatorio.
                                                            </div>   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-2" id= "fechaSalida" hidden>
                                                        <div class="form-group">
                                                            <label for="password">Fecha de salida</label>
                                                            <input type="date" class="form-control" name="fecha_salida" value="<?=$solicitante["fecha_salida"];?>">   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <div class="form-group">
                                                            <label for="name">Horario laboral<span style="color:red;"> (*)</span></label>
                                                            <input type="text" name="jornada" class="form-control" value="<?=$solicitante["jornada"];?>" required>
                                                            <div class="invalid-feedback">
                                                                El campo horario laboral es obligatoria.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-xs-12 col-sm-6 col-md-2">
                                                        <div class="form-group">
                                                            <label for="password">Horas trabajadas a la semana<span style="color:red;"> (*)</span></label>
                                                            <input type="number" class="form-control" min="0" name="horas_semana" value="<?=$solicitante["horas_semana"];?>" required>
                                                            <div class="invalid-feedback">
                                                                El campo horas trabajadas es obligatorio.
                                                            </div>   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-2">
                                                        <div class="form-group">
                                                            <label for="password">Labora actualmente<span style="color:red;"> (*)</span></label>
                                                            <!--input type="text" class="form-control" name="labora" id="laboraActualmenteValue" value="<?=$solicitante["labora"];?>" required-->
                                                            <select name="labora" id="laboraActualmenteValue" class="form-control" required>
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
                                                            <textarea class="form-control" name="descripcionSolicitud" required>{{ $solicitante["descripcionSolicitud"] ?? '' }}</textarea>
                                                            <div class="invalid-feedback">
                                                                El campo descripción del motivo de la solicitud es obligatorio.
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                            <!--div class="col-xs-12 col-sm-12 col-md-2" style="margin-top: 20px;">
                                                <button type="submit" class="btn btn-info btn-block">Actualizar datos del solicitante</button>
                                            </div-->
                                            @endforeach
                                        </div>
                                    </div>
                                    <div id="documentos" class="tabcontent">
                                        <div id="tabla_documentos" class="row"><br>
                                            
                                            @if ($general->tipo_solicitud == 1)
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <a type="button" class="btn btn-warning open-modal mb-3" data-bs-toggle="modal" 
                                                        data-bs-target="#exampleModal1" data-id="{{ $id }}">Agregar Citado</a>
                                                    <a type="button" class="btn btn-warning open-modal mb-3" data-bs-toggle="modal" 
                                                        data-bs-target="#exampleModal2" data-id="{{ $id }}">Borrar Citado</a>
                                                </div>
                                            @endif

                                            @foreach($citados as $citado)
                                                @php
                                                    $esResulte = isset($citado['resulte_responsable']) ? $citado['resulte_responsable'] : (isset($citado->resulte_responsable) ? $citado->resulte_responsable : 'No');
                                                @endphp
                                                <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:30px;">
                                                    <div class="form-group">
                                                        <h4 class="text-center">Citado</h4>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group mt-2">
                                                        <h4 class="text-center">Datos personales del citado</h4>
                                                    </div>
                                                </div>
                                                <br>
                                                <div class="col-xs-12 col-sm-6 col-md-4" id="nombre_wrap_{{$loop->index}}">
                                                    <div class="form-group">
                                                        <label for="password">Nombre<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="nombre_citado[]" value="{{ $citado['nombre'] ?? '' }}" required>
                                                        <input type="hidden" name="resulte_responsable[]" value="{{ $esResulte }}">
                                                        <div class="invalid-feedback">
                                                            El campo nombre es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-4" id="primer_wrap_{{$loop->index}}" style="{{ $esResulte == 'Si' ? 'display:none;' : '' }}">
                                                        <div class="form-group">
                                                           <label for="password">Primer apellido<span style="color:red;"> (*)</span></label>
                                                           <input type="text" class="form-control" name="primer_apellido[]" value="{{ $citado['primer_apellido'] ?? '' }}" @if($esResulte == 'Si') @else required @endif>
                                                           <div class="invalid-feedback">
                                                                El campo primer apellido es obligatorio.
                                                        </div>   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-4" id="segundo_wrap_{{$loop->index}}" style="{{ $esResulte == 'Si' ? 'display:none;' : '' }}">
                                                        <div class="form-group">
                                                           <label for="password">Segundo apellido</label>
                                                           <input type="text" class="form-control" name="segundo_apellido[]" value="{{ $citado['segundo_apellido'] ?? '' }}">
                                                           <div class="invalid-feedback">
                                                                El campo segundo apellido es obligatorio.
                                                            </div>   
                                                        </div>
                                                    </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de persona<span style="color:red;"> (*)</span></label>
                                                        <select name="tipo_persona_citado[]" class="form-control" resulte_flag="{{ $esResulte }}" required>
                                                            <option value="">SELECCIONE</option>
                                                            <option value="Fisica" {{ $citado['tipo_persona'] == 'Fisica' ? "selected" : '' }}>Física</option>
                                                            <option value="Moral"  {{ $citado['tipo_persona'] == 'Moral' ? "selected" : '' }}>Moral</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                @if(!empty($citado['curp']))
                                                    <div class="col-xs-12 col-sm-6 col-md-4" id="campo_curp">
                                                        <div class="form-group">
                                                           <label for="password">CURP</label>
                                                           <input type="text" class="form-control" name="curp_citado[]" value="{{ $citado['curp'] ?? '' }}" maxlength="18">   
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">RFC</label>
                                                        <input type="text" class="form-control" name="rfc_citado[]" value="{{ $citado['rfc'] ?? '' }}">   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                        <div class="form-group">
                                                            <label for="name">¿Requiere traductor?<span style="color:red;"> (*)</span></label>
                                                            <select name="traductor[]" id="traductor_{{$loop->index}}" class="form-control" required>
                                                                <option value="No" {{ (isset($citado['traductor']) ? ($citado['traductor'] ? '' : 'selected') : (isset($citado->traductor) && !$citado->traductor ? 'selected' : '')) }}>No</option>
                                                                <option value="Si" {{ (isset($citado['traductor']) ? ($citado['traductor'] ? 'selected' : '') : (isset($citado->traductor) && $citado->traductor ? 'selected' : '')) }}>Si</option>
                                                            </select>
                                                            <div class="invalid-feedback">Este campo es obligatorio.</div>
                                                        </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3" id="lenguaje_wrap_{{$loop->index}}">
                                                    <div class="form-group">
                                                        <label for="name">¿Qué tipo de lenguaje requiere?<span style="color:red;"> (*)</span></label>
                                                        <input type="text" name="lenguaje" id="lenguaje_{{$loop->index}}" class="form-control" value="{{ $citado['lenguaje'] ?? ($citado->lenguaje ?? '') }}" oninput="this.value = this.value.toUpperCase()">
                                                        <div class="invalid-feedback">El lenguaje es obligatorio cuando requiere traductor.</div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h4 class="text-center">Dirección del citado</h4>
                                                    </div>
                                                </div><br>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Entidad Federativa<span style="color:red;"> (*)</span></label>
                                                        <select class="form-control" name="estado_citado[]">
                                                            @foreach($estados as $est)
                                                                <option value="{{$est['id']}}" {{ $citado['estado_citado'] == $est['id'] ? "selected" : '' }}>{{$est['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El Estado es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Municipio<span style="color:red;"> (*)</span></label>
                                                        <select class="form-control" name="municipio_citado[]">
                                                            @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}" {{ $citado['municipio_citado'] == $mun['id'] ? "selected" : '' }}>{{$mun['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El Municipio es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de vialidad<span style="color:red;"> (*)</span></label>
                                                        <select name="vialidad_citado[]" class="form-control" required>
                                                            <option value="">SELECCIONE</option>
                                                            <option value="CALLE"          {{ $citado['tipo_vialidad'] == 'CALLE' ? "selected" : '' }}   >Calle</option>
                                                            <option value="AVENIDA"        {{ $citado['tipo_vialidad'] == 'AVENIDA' ? "selected" : '' }} >Avenida</option>
                                                            <option value="CALZADA"        {{ $citado['tipo_vialidad'] == 'CALZADA' ? "selected" : '' }} >Calzada</option>
                                                            <option value="BOULEVARD"      {{ $citado['tipo_vialidad'] == 'BOULEVARD' ? "selected" : '' }} >Boulevard</option>
                                                            <option value="AMPLIACIÓN"     {{ $citado['tipo_vialidad'] == 'AMPLIACIÓN' ? "selected" : '' }} >Ampliación</option>
                                                            <option value="ANDADOR"        {{ $citado['tipo_vialidad'] == 'ANDADOR' ? "selected" : '' }} >Andador</option>
                                                            <option value="AUTOPISTA"      {{ $citado['tipo_vialidad'] == 'AUTOPISTA' ? "selected" : '' }} >Autopista</option>
                                                            <option value="CALLEJÓN"       {{ $citado['tipo_vialidad'] == 'CALLEJÓN' ? "selected" : '' }}>Callejón</option>
                                                            <option value="CARRETERA"      {{ $citado['tipo_vialidad'] == 'CARRETERA' ? "selected" : '' }}   >Carretera</option>
                                                            <option value="CERRADA"        {{ $citado['tipo_vialidad'] == 'CERRADA' ? "selected" : '' }} >Cerrada</option>
                                                            <option value="CIRCUITO"       {{ $citado['tipo_vialidad'] == 'CIRCUITO' ? "selected" : '' }} >Circuito</option>
                                                            <option value="CIRCUNVALACIÓN" {{ $citado['tipo_vialidad'] == 'CIRCUNVALACIÓN' ? "selected" : '' }} >Circunvalación</option>
                                                            <option value="CONTINUACIÓN"   {{ $citado['tipo_vialidad'] == 'CONTINUACIÓN' ? "selected" : '' }} >Continuación</option>
                                                            <option value="CORREDOR"       {{ $citado['tipo_vialidad'] == 'CORREDOR' ? "selected" : '' }} >Corredor</option>
                                                            <option value="DIAGONAL"       {{ $citado['tipo_vialidad'] == 'DIAGONAL' ? "selected" : '' }} >Diagonal</option>
                                                            <option value="EJE VIAL"       {{ $citado['tipo_vialidad'] == 'EJE VIAL' ? "selected" : '' }}>Eje vial</option>
                                                            <option value="PERIFÉRICO"     {{ $citado['tipo_vialidad'] == 'PERIFÉRICO' ? "selected" : '' }}   >Periférico</option>
                                                            <option value="PRIVADA"       {{ $citado['tipo_vialidad'] == 'PRIVADA' ? "selected" : '' }} >Privada</option>
                                                            <option value="PROLONGACIÓN"   {{ $citado['tipo_vialidad'] == 'PROLONGACIÓN' ? "selected" : '' }} >Prolongación</option>
                                                            <option value="RETORNO"        {{ $citado['tipo_vialidad'] == 'RETORNO' ? "selected" : '' }} >Retorno</option>
                                                            <option value="VIADUCTO"       {{ $citado['tipo_vialidad'] == 'VIADUCTO' ? "selected" : '' }} >Viaducto</option>
                                                            <option value="PASEO"       {{ $citado['tipo_vialidad'] == 'PASEO' ? "selected" : '' }} >Paseo</option>                                                           
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo vialidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Nombre de la vialidad<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="calle_citado[]" value="{{ $citado['calle'] ?? '' }}" required>
                                                        <div class="invalid-feedback">
                                                            El campo nombre de la vialidad es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>                                                
                                                
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">N° Ext.<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="n_ext_citado[]" value="{{ $citado['n_ext'] ?? '' }}" required>
                                                        <div class="invalid-feedback">
                                                            El campo Núm. Ext. es obligatorio.
                                                        </div>     
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">N° Int.</label>
                                                        <input type="text" class="form-control" name="n_int_citado[]" value="{{ $citado['n_int'] ?? '' }}">   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Colonia<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="colonia_citado[]" value="{{ $citado['colonia'] ?? '' }}" required>
                                                        <div class="invalid-feedback">
                                                            El campo colonia es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Código postal<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="cp_citado[]" value="{{ $citado['cp'] ?? '' }}" required>
                                                        <div class="invalid-feedback">
                                                            El campo código postal es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Referencia<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="referencia_citado[]" value="{{ $citado['referencia'] ?? '' }}" required>
                                                        <div class="invalid-feedback">
                                                            El campo referencia es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Entre Calle</label>
                                                        <input type="text" class="form-control" name="calle1_citado[]" value="{{ $citado['calle1'] ?? '' }}">   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Y calle</label>
                                                        <input type="text" class="form-control" name="calle2_citado[]" value="{{ $citado['calle2'] ?? '' }}">   
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-2">
                                                    <div class="form-group">
                                                        <label for="name">¿Quién entregará las notificaciones?<span style="color:red;"> (*)</span></label>
                                                        <select name="notificacion[]" class="form-control" required>
                                                            <option value="Trabajador"  {{ $citado['notificacion'] == 'Trabajador' ? "selected" : '' }}>Solicitante</option>
                                                            <option value="Centro"      {{ $citado['notificacion'] == 'Centro' ? "selected" : '' }}>Centro de conciliación Laboral</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-5">
                                                    <label for="password">Referencia Imagen 1<span style="color:red;"> (*)</span></label><br>
                                                    @php
                                                        $hasRefImg1 = (!empty($citado->imagen_domicilio1) && $citado->imagen_domicilio1 !== 'Sin documento');
                                                    @endphp
                                                    @if ($hasRefImg1)
                                                        <a target='_blank' href="../storage/app/documentosSolicitud/{{$citado->imagen_domicilio1}}">VER IMAGEN</a><br>
                                                    @else
                                                        <span class="text-muted">No se subió imagen</span>
                                                    @endif
                                                    <input type="file" name="foto1[]" accept="image/*" class="form-control" {{ $hasRefImg1 ? '' : 'required' }}>
                                                    <div class="invalid-feedback">Debe subir la Referencia Imagen 1 para poder confirmar.</div>
                                                    <input type="hidden" name="imagen_domicilio1[]" value="{{ $citado->imagen_domicilio1 }}">
                                                    <input type="hidden" class="ref-img1-exists" value="{{ $hasRefImg1 ? '1' : '0' }}">
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-5">
                                                    <label for="password">Referencia Imagen 2</label><br>
                                                    @if (!empty($citado->imagen_domicilio2) && $citado->imagen_domicilio2 !== 'Sin documento')
                                                        <a target='_blank' href="../storage/app/documentosSolicitud/{{$citado->imagen_domicilio2}}">VER IMAGEN</a><br>
                                                    @else
                                                        <span class="text-muted">No se subió imagen</span>
                                                    @endif
                                                    <input type="file" name="foto2[]" accept="image/*" class="form-control">
                                                    <input type="hidden" name="imagen_domicilio2[]" value="{{ $citado->imagen_domicilio2 }}">
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12"><br></div>

                                                @if ($general->tipo_solicitud == 2)
                                                    @foreach($solicitantes as $solicitante)
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h4 class="text-center">Datos laborales del citado</h4>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-xs-12 col-sm-6 col-md-3">
                                                            <div class="form-group">
                                                                <label for="password">Seguro Social</label>
                                                                <input type="text" class="form-control" name="nss" value="<?=$solicitante["nss"];?>">   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-6 col-md-3">
                                                            <div class="form-group">
                                                                <label for="password">Puesto<span style="color:red;"> (*)</span></label>
                                                                <input type="text" class="form-control" name="puesto" value="<?=$solicitante["puesto"];?>" required>
                                                                <div class="invalid-feedback">
                                                                    El campo puesto es obligatorio.
                                                                </div>   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                                            <div class="form-group">
                                                                <label for="name">Periodo de pago<span style="color:red;"> (*)</span></label>
                                                                <select name="periodo_pago" class="form-control" required>
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
                                                                <input type="text" class="form-control" name="pago" value="<?=$solicitante["pago"];?>" required>
                                                                <div class="invalid-feedback">
                                                                    El campo sueldo es obligatorio.
                                                                </div>   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-6 col-md-2">
                                                            <div class="form-group">
                                                                <label for="password">Fecha de ingreso<span style="color:red;"> (*)</span></label>
                                                                <input type="date" class="form-control" name="fecha_ingreso" value="<?=$solicitante["fecha_ingreso"];?>" required>
                                                                <div class="invalid-feedback">
                                                                    El campo fecha de ingreso es obligatorio.
                                                                </div>   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-6 col-md-2" id= "fechaSalida" hidden>
                                                            <div class="form-group">
                                                                <label for="password">Fecha de salida</label>
                                                                <input type="date" class="form-control" name="fecha_salida" value="<?=$solicitante["fecha_salida"];?>">   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                                            <div class="form-group">
                                                                <label for="name">Horario laboral<span style="color:red;"> (*)</span></label>
                                                                <input type="text" name="jornada" class="form-control" value="<?=$solicitante["jornada"];?>" required>
                                                                <div class="invalid-feedback">
                                                                    El campo horario laboral es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    
                                                        <div class="col-xs-12 col-sm-6 col-md-2">
                                                            <div class="form-group">
                                                                <label for="password">Horas trabajadas a la semana<span style="color:red;"> (*)</span></label>
                                                                <input type="number" class="form-control" min="0" name="horas_semana" value="<?=$solicitante["horas_semana"];?>" required>
                                                                <div class="invalid-feedback">
                                                                    El campo horas trabajadas es obligatorio.
                                                                </div>   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-6 col-md-2">
                                                            <div class="form-group">
                                                                <label for="password">Labora actualmente<span style="color:red;"> (*)</span></label>
                                                                <!--input type="text" class="form-control" name="labora" id="laboraActualmenteValue" value="<?=$solicitante["labora"];?>" required-->
                                                                <select name="labora" id="laboraActualmenteValue" class="form-control" required>
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
                                                                <textarea class="form-control" name="descripcionSolicitud" required>{{ $solicitante["descripcionSolicitud"] ?? '' }}</textarea>
                                                                <div class="invalid-feedback">
                                                                    El campo descripción del motivo de la solicitud es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                            
                                        </div>
                                    </div>

                                    <div id="citados" class="tabcontent">
                                        <div id="tabla_citados" class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Documentos</h4>
                                                </div>
                                            </div><br>
                                                @if($general->tipo_solicitud == 1)
                                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                                        <label for="password">Identificación Oficial <span style="color:red;">(*)</span></label><br>
                                                        <a class="btn btn-info" target="_blank" href="{{ route('documento_identificacion_solicitante_ver', $id) }}">Visualizar</a>
                                                    </div>
    
                                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                                        <label for="password">Reemplazar Identificación Oficial</label><br>
                                                        <input type="file" name="documentoIdentificacion" accept=".pdf" class="form-control">
                                                    </div>
                                                    <br>
                                                @else
                                                    @if($solicitante->poder->reprecentante == 'Si')
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                @if($solicitante->poder->tipo == 'Fisica')
                                                                    <label>*Identificación del Empleador</label><br>
                                                                @else
                                                                    <label>*Acta Constitutiva</label><br>
                                                                @endif
                                                                <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->idAbogado}}/{{$solicitante->poder->ineDocumento}}">Existente</a>
                                                                <div class="invalid-feedback">
                                                                    La Identificación es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Identificación del Representante Legal</label><br>
                                                                @if($solicitante->poder->representacionDocumento != NULL)
                                                                    <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->idAbogado}}/{{$solicitante->poder->representacionDocumento}}">Existente</a>
                                                                @endif
                                                                <div class="invalid-feedback">
                                                                    El documento de representación es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Documento que acredite la personería</label><br>
                                                                @if($solicitante->poder->cedulaDocumento != NULL)
                                                                    <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->idAbogado}}/{{$solicitante->poder->cedulaDocumento}}">Existente</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>Anexo (Documentos Complementarios)</label><br>
                                                                @if($solicitante->poder->anexo_documeto != "Sin anexo")
                                                                    <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->idAbogado}}/{{$solicitante->poder->anexo_documeto}}">Existente</a>
                                                                @else
                                                                    <a class="btn btn-secondary disabled" href="#" tabindex="-1" aria-disabled="true">Sin anexo</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h4 class="text-center">Documentos del representante</h4>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Identificación del Empleador</label><br>
                                                                <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->idAbogado}}/{{$solicitante->poder->ineDocumento}}">Existente</a>
                                                                <div class="invalid-feedback">
                                                                    La Identificación es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>Anexo (Documentos Complementarios)</label><br>
                                                                @if($solicitante->poder->anexo_documeto != "Sin anexo")
                                                                    <a target="_blank" class="btn btn-primary" href="../storage/app/documentos_abogados/{{$solicitante->poder->anexo_documeto}}">Existente</a>
                                                                @else
                                                                    <a class="btn btn-secondary disabled" href="#" tabindex="-1" aria-disabled="true">Sin anexo</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif

                                            @foreach ($solicitantes as $solicitante)
                                               <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de Identificación<span style="color:red;" ></span> (*)</span></label>
                                                        <select name="tipoIdentificacion" class="form-control" required @if ($general->tipo_solicitud == 2 && $solicitante['identificacion'] != NULL) disabled @endif>
                                                            <option value="">SELECCIONE</option>
                                                            <option value="Credencial de elector"          {{ $solicitante['identificacion'] == 'Credencial de elector' ? "selected" : '' }}   >Credencial de elector</option>
                                                            <option value="Pasaporte"        {{ $solicitante['identificacion'] == 'Pasaporte' ? "selected" : '' }} >Pasaporte</option>
                                                            <option value="Cédula profesional"        {{ $solicitante['identificacion'] == 'Cédula profesional' ? "selected" : '' }} >Calzada</option>
                                                            <option value="Licencia de conducir"          {{ $solicitante['identificacion'] == 'Licencia de conducir' ? "selected" : '' }}   >Licencia de conducir</option>
                                                            <option value="Otro"        {{ $solicitante['identificacion'] == 'Otro' ? "selected" : '' }} >Otros</option>
                                                            <option value="Credencial de inapam"        {{ $solicitante['identificacion'] == 'Credencial de inapam' ? "selected" : '' }} >Credencial de inapam</option>
                                                            <option value="Cartilla militar"          {{ $solicitante['identificacion'] == 'Cartilla militar' ? "selected" : '' }}   >Cartilla militar</option>
                                                            <option value="Documento migratorio"        {{ $solicitante['identificacion'] == 'Documento migratorio' ? "selected" : '' }} >Documento migratorio</option>
                                                            <option value="Constancia de identidad"        {{ $solicitante['identificacion'] == 'Constancia de identidad' ? "selected" : '' }} >Constancia de identidad</option>                                                 
                                                        </select>
                                                        @if ($general->tipo_solicitud == 2 && $solicitante['identificacion'] != NULL)
                                                            <input type="hidden" name="tipoIdentificacion" value="{{ $solicitante['identificacion'] }}">
                                                        @endif
                                                        <div class="invalid-feedback">
                                                            El campo Tipo de Identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
                                                    <div class="form-group">
                                                        <label for="name">Número de identificación<span style="color:red;"> (*)</span></label>
                                                        <input type="text" name="numeroIdentificacion" maxlength="20" class="form-control" value="{{ $solicitante['num_identificacion'] ?? '' }}" required @if ($general->tipo_solicitud == 2 && $solicitante['num_identificacion'] != NULL) readonly @endif>
                                                            
                                                        <div class="invalid-feedback">
                                                            El campo Número de Identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                        </div>
                                    </div>

                                    <div id="observaciones" class="tabcontent">
                                        <div id="tabla_citados" class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Observaciones</h4>
                                                </div>
                                            </div><br>

                                            <div class="col-xs-12 col-sm-6 col-md-12">
                                                <div class="form-group">
                                                    <label for="email">Observaciones</label>
                                                    <input type="text" class="form-control" name="observaciones" value="<?=$general["observaciones"];?>" readonly>
                                                </div>
                                            </div>
          
                                            <!--div class="col-xs-12 col-sm-12 col-md-12"><br>
                                                <a class="btn btn-primary" href="{{ url()->previous() }}">Regresar</a>
                                            </!--div-->
                                        </div>
                                    </div>

                                    <div id="confirmacion" class="tabcontent">
                                        <div id="tabla_confirmar" class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12"><br>
                                                @php
                                                    $citadosCount = isset($citados)
                                                        ? (is_countable($citados) ? count($citados) : (method_exists($citados, 'count') ? $citados->count() : 0))
                                                        : 0;
                                                @endphp
                                                @if($general['estatus'] == 'Prevencion' || $general['estatus'] == 'Pendiente')
                                                    @if($citadosCount > 0)
                                                        <button id="btnConfirmarSolicitud" type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;" name="toquen" value="1">Confirmar</button>
                                                        <div id="msgRefImg1" class="text-muted mt-2" style="display:none;">Debe subir la <strong>Referencia Imagen 1</strong> de todos los citados para poder confirmar.</div>
                                                    @else
                                                        <button type="button" class="btn btn-secondary" disabled title="Agregue al menos un citado para poder guardar."  name="toquen" value="1">Confirmar</button>
                                                        <div class="text-muted mt-2">Debe agregar al menos un citado para poder guardar.</div>
                                                    @endif
                                                    <button type="button" class="btn btn-danger open-modal" data-bs-toggle="modal" data-bs-target="#exampleModal" data-id="{{ $general->id }}"> Prevención </button>
                                                @endif
                                                @if($general['estatus'] != 'Prevencion' && $general['estatus'] != 'Pendiente')
                                                    <button type="submit" class="btn btn-primary" name="toquen" value="2">Guardar</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>

        //No toquen esto, funciona :)
        
        // Manejador para mostrar feedback de validación en formularios con pestañas.
        document.addEventListener('DOMContentLoaded', function () {
            var forms = document.querySelectorAll('form.needs-validation');
            if (!forms || forms.length === 0) return;

            forms.forEach(function(form) {
                // Capturar el evento invalid en fase de captura para abrir la pestaña
                // que contiene el control inválido antes de que el navegador intente enfocarlo.
                form.addEventListener('invalid', function (e) {
                    try { e.preventDefault(); e.stopPropagation(); } catch (err) {}
                    var invalidEl = e.target;
                    var tabContainer = invalidEl.closest('.tabcontent');
                    if (tabContainer && tabContainer.id) {
                        var selector = '.tab a[onclick*="' + tabContainer.id + '"]';
                        var tabBtn = document.querySelector(selector);
                        if (tabBtn) {
                            try { tabBtn.click(); } catch (err) { /* ignore */ }
                        } else if (typeof openCity === 'function') {
                            try { openCity(new Event('click'), tabContainer.id); } catch (err) { /* ignore */ }
                        }
                    }
                    setTimeout(function () { try { invalidEl.focus(); } catch (err) { /* ignore */ } }, 80);
                }, true);

                form.addEventListener('submit', function (e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        form.classList.add('was-validated');

                        // Mostrar SweetAlert (fallback a alert si no está disponible)
                        if (typeof swal === 'function') {
                            swal("Error", "Por favor corrija los campos obligatorios.", "error");
                        } else {
                            try { alert('Por favor corrija los campos obligatorios.'); } catch (err) { /* ignore */ }
                        }

                        var firstInvalid = form.querySelector(':invalid');
                        if (firstInvalid) {
                            var tabContainer = firstInvalid.closest('.tabcontent');
                            if (tabContainer && tabContainer.id) {
                                var selector = '.tab a[onclick*="' + tabContainer.id + '"]';
                                var tabBtn = document.querySelector(selector);
                                if (tabBtn) {
                                    try { tabBtn.click(); } catch (err) { /* ignore */ }
                                } else if (typeof openCity === 'function') {
                                    try { openCity(new Event('click'), tabContainer.id); } catch (err) { /* ignore */ }
                                }
                            }
                            setTimeout(function () { try { firstInvalid.focus(); } catch (err) { /* ignore */ } }, 80);
                        }
                    }
                }, false);
            });
        });
        </script>

    </section>
@endsection


<!-- Modal -->
<div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('agregar_citado_edicion')}}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{$id}}">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Citado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">                                    
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="name">Agregar "Quien resulte responsable" <span style="color:red;">(*)</span></label>
                                <select name="responsable" id="responsable" class="form-control" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="Si">Si</option>
                                    <option value="No">No</option>
                                </select>
                                <div class="invalid-feedback">
                                    El campo es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="name">¿Quién entregará las notificaciones? <span style="color:red;">(*)</span></label>
                                <select name="notificacion" class="form-control" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="Trabajador">Solicitante</option>
                                    <option value="Centro">Centro de conciliación Laboral</option>
                                </select>
                                <div class="invalid-feedback">
                                    El campo es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-2">
                                <div class="form-group">
                                    <label for="name">Tipo de persona <span style="color:red;">(*)</span></label>
                                    <select name="tipo" id="tipo" class="form-control" required>
                                        <option value="">Seleccione</option>
                                        <option value="Fisica">Física</option>
                                        <option value="Moral">Moral</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        El tipo de persona es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-4" id="campo_curp">
                                <div class="form-group">
                                    <label for="name">CURP (Opcional)</label>
                                    <input type="text" name="curp" id="curp_input" oninput="validarInput(this)" class="form-control"> 
                                    <pre id="resultado"></pre>
                                    <div class="invalid-feedback">
                                        El nombre es obligatorio.
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-4" id="tipoPersona_razon" style="display:none;">
                                <div class="form-group">
                                    <label for="name">Razón social <span style="color:red;">(*)</span></label>
                                    <input type="text" name="razon" id="razon" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                    <div class="invalid-feedback">
                                        La razón social es obligatorio.
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="name">RFC (Opcional)</label>
                                    <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()"> 
                                    <div class="invalid-feedback">
                                         El campo conflicto es obligatorio.
                                    </div>
                                </div>
                            </div>
    
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="name">¿Requiere algún traductor? <span style="color:red;">(*)</span></label>
                                        <select name="traductor" id="traductor_modal" class="form-control" required>
                                       <option value="">SELECCIONE</option>
                                       <option value="Si">Si</option>
                                       <option value="No">No</option>
                                    </select>
                                </div>
                            </div>
                                <div class="col-xs-12 col-sm-12 col-md-3" id="lenguaje_modal_wrap" style="display:none;">
                                <div class="form-group">
                                    <label for="name">¿Qué tipo de lenguaje require? <span style="color:red;">(*)</span></label>
                                        <input type="text" name="lenguaje" id="lenguaje_modal" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">
                                        La nacionalidad es obligatoria.
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12" id="tipoPersona_nombre" style="display:none;">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Nombre(s) <span style="color:red;">(*)</span></label>
                                            <input type="text" name="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                            <input type="text" name="primer_apellido" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Segundo apellido</label>
                                            <input type="text" name="segundo_apellido" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>    
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; color:black; width:100%; height:30px;">
                            <div class="form-group">
                                <h4 class="text-center">Dirección de la fuente de empleo</h4>
                            </div>
                        </div>   
                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                            <div class="form-group">
                                <label for="name">Tipo de vialidad <span style="color:red;">(*)</span></label>
                                <select name="vialidad" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                                    <option value="">SELECCIONE</option>
                                    <option value="AMPLIACIÓN">Ampliación</option>
                                    <option value="ANDADOR">Andador</option>
                                    <option value="AUTOPISTA">Autopista</option>
                                    <option value="AVENIDA">Avenida</option>
                                    <option value="BOULEVARD">Boulevard</option>
                                    <option value="CALLE">Calle</option>
                                    <option value="CALLEJÓN">Callejón</option>
                                    <option value="CALZADA">Calzada</option>
                                    <option value="CARRETERA">Carretera</option>
                                    <option value="CERRADA">Cerrada</option>
                                    <option value="CIRCUITO">Circuito</option>
                                    <option value="CIRCUNVALACIÓN">Circunvalación</option>
                                    <option value="CONTINUACIÓN">Continuación</option>
                                    <option value="CORREDOR">Corredor</option>
                                    <option value="DIAGONAL">Diagonal</option>
                                    <option value="EJE VIAL">Eje vial</option>
                                    <option value="PERIFÉRICO">Periférico</option>
                                    <option value="PRIVADA">Privada</option>
                                    <option value="PROLONGACIÓN">Prolongación</option>
                                    <option value="RETORNO">Retorno</option>
                                    <option value="VIADUCTO">Viaducto</option>
                                    <option value="PASEO">Paseo</option>
                                </select>
                                <div class="invalid-feedback">
                                    El campo vialidad es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                            <div class="form-group">
                                <label for="name">Nombre de la vialidad <span style="color:red;">(*)</span></label>
                                <input type="text" name="calle" class="form-control" required oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo calle es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                            <div class="form-group">
                                <label for="name">Colonia <span style="color:red;">(*)</span></label>
                                <input type="text" name="colonia" class="form-control" required oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo colonia es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                            <div class="form-group">
                                <label for="name">Entre calle (Opcional)</label>
                                <input type="text" name="calle1" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo calle es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="name">y calle (Opcional)</label>
                                <input type="text" name="calle2" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo calle es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-2">
                            <div class="form-group">
                                <label for="name">Núm. Ext. <span style="color:red;">(*)</span></label>
                                <input type="text" name="exterior" class="form-control" required oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo núm. ext. es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-2">
                            <div class="form-group">
                                <label for="name">Núm. Int. (Opcional)</label>
                                <input type="text" name="interior" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo núm. int. es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-2">
                            <div class="form-group">
                                <label for="name">Código postal <span style="color:red;">(*)</span></label>
                                <input type="text" name="cp" class="form-control" minlength="5" maxlength="5" required> 
                                <div class="invalid-feedback">
                                    El campo Código Postal es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="name">Estado <span style="color:red;">(*)</span></label>
                                <select id="estado_citado" class="form-control" name="estado_citado" required oninput="this.value = this.value.toUpperCase()">
                                    <option value="">Seleccione</option>
                                    @foreach($estados as $es)
                                        <option value="{{$es['id']}}">{{$es['nombre']}}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    El campo Estado es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="name">Municipio o Alcaldía <span style="color:red;">(*)</span></label>
                                <select id="municipio_citado" class="form-control" name="municipio_citado" required oninput="this.value = this.value.toUpperCase()">
                                    <option value="">SELECCIONE</option>
                                    @foreach($municipios as $mun)
                                        <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    El campo municipio o alcaldía es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-9">
                            <div class="form-group">
                            <label for="floatingTextarea">Referencias del domicilio <span style="color:red;">(*)</span></label>
                                <textarea class="form-control" placeholder="Ingresa alguna referencia de como llegar" name="referencia" required oninput="this.value = this.value.toUpperCase()"></textarea>
                                <div class="invalid-feedback">
                                    El campo referencias es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-6">
                            <div class="form-group">
                                <label for="name">Referencia 1 <span style="color:red;">(*)</span></label>
                                <input type="file" class="form-control" name="foto1" accept="image/*" required>
                                <div class="invalid-feedback">
                                    El campo imagen 1 es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-6">
                            <div class="form-group">
                                <label for="name">Referencia 2 (Opcional)</label>
                                <input type="file" class="form-control" name="foto2" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>


<!-- Modal -->
    <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        @csrf
        <input type="hidden" name="id" value="{{$id}}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Motivo de rechazo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped mt-2">
                            <thead style="background-color: #4A001F;">
                                <th style="color: #fff;">Nombre</th>
                                <th style="color: #fff;">CURP</th>
                                <th style="color: #fff;">Dirección</th>
                                <th style="color: #fff;">Acciones</th>
                            </thead>
                            <tbody>
                                @foreach($citados as $citado)
                                <tr>
                                    <td>{{$citado->nombre}} {{$citado->primer_apellido}} {{$citado->segundo_apellido}}</td>
                                    <td>{{$citado->curp}}</td>
                                    <td>{{$citado->colonia}}." ".{{$citado->calle}}</td>
                                    <td>
                                        <form method="POST" action="{{ route('borrar_citado_edicion') }} ">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <input type="hidden" name="id" value="{{$id}}">
                                            <input type="hidden" name="borrar" value="{{$citado->id}}">
                                            <button class="btn btn-danger" onclick=editar_rol(); type="submit">Eliminar</button>
                                        </form>
                                    </td>   
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

<div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

<!-- Modal Rechazo de solicitud-->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('rechazar_solicitud')}}">
        @csrf
        <input type="hidden" id="modal-id" name="id" value="">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Motivo de la prevención</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea name="observaciones" style="width:100%"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </div>
        </div>
    </form>
</div>

@section('scripts')
    <script src="../public/assets/js/estadistica/estadisticaConfirmar.js"></script>

    <script>
        (function () {
            function syncConfirmButtonByRefImg1() {
                var btn = document.getElementById('btnConfirmarSolicitud');
                if (!btn) return;

                var msg = document.getElementById('msgRefImg1');
                var rows = document.querySelectorAll('.ref-img1-exists');
                var inputs = document.querySelectorAll('input[type="file"][name="foto1[]"]');

                var missing = false;
                for (var i = 0; i < rows.length; i++) {
                    var exists = rows[i].value === '1';
                    if (exists) continue;

                    var fileInput = inputs[i];
                    var hasFile = !!(fileInput && fileInput.files && fileInput.files.length > 0);
                    if (!hasFile) {
                        missing = true;
                        break;
                    }
                }

                btn.disabled = missing;
                if (msg) msg.style.display = missing ? 'block' : 'none';
            }

            document.addEventListener('change', function (e) {
                if (e.target && e.target.matches('input[type="file"][name="foto1[]"]')) {
                    syncConfirmButtonByRefImg1();
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                syncConfirmButtonByRefImg1();
            });

            window.addEventListener('click', function (e) {
                if (e.target && e.target.closest && e.target.closest('.tab')) {
                    setTimeout(syncConfirmButtonByRefImg1, 50);
                }
            });
        })();
    </script>
        <script>
            $(function(){
                $('#motivo_solicitud').on('change', validarcheckfolio);
            })

            let motivosSeleccionados = [];

                //Traductor por citado
                function syncLenguajeRequired(index) {
                    var sel = document.getElementById('traductor_' + index);
                    var wrap = document.getElementById('lenguaje_wrap_' + index);
                    var input = document.getElementById('lenguaje_' + index);
                    if (!sel || !wrap || !input) return;
                    if (sel.value === 'Si') {
                        wrap.style.display = '';
                        input.required = true;
                    } else {
                        wrap.style.display = 'none';
                        input.required = false;
                        try { input.value = ''; } catch (err) {}
                    }
                }
        
                var traductores = document.querySelectorAll('[id^="traductor_"]');
                traductores.forEach(function(sel){
                    var idx = sel.id.replace('traductor_', '');
                    syncLenguajeRequired(idx);
                    sel.addEventListener('change', function(){ syncLenguajeRequired(idx); });
                });

                //Traductor en modal Agregar Citado
                var tradModal = document.getElementById('traductor_modal');
                var langWrapModal = document.getElementById('lenguaje_modal_wrap');
                var langInputModal = document.getElementById('lenguaje_modal');
                function syncModalLenguaje(){
                    if (!tradModal || !langWrapModal || !langInputModal) return;
                    if (tradModal.value === 'Si') {
                        langWrapModal.style.display = '';
                        langInputModal.required = true;
                    } else {
                        langWrapModal.style.display = 'none';
                        langInputModal.required = false;
                        try { langInputModal.value = ''; } catch (err) {}
                    }
                }
                if (tradModal) {
                    syncModalLenguaje();
                    tradModal.addEventListener('change', syncModalLenguaje);
                }

            function validarcheckfolio(){
                var opcionSeleccionada = $(this).val();
                var opcionTexto = $("#motivo_solicitud option:selected").text();

                // Verifica si ya fue agregado ese motivo
                if (motivosSeleccionados.includes(opcionSeleccionada)) {
                    alert('Este motivo ya ha sido seleccionado.');
                    $(this).val('');
                    return;
                }

                motivosSeleccionados.push(opcionSeleccionada);

                $('#tabla tbody').append(
                    '<tr data-id="' + opcionSeleccionada + '">' +
                        '<td>' + opcionTexto + '</td>' +
                        '<td><button type="button" class="eliminar btn btn-danger btn-sm">Eliminar</button></td>' +
                    '</tr>'
                );

                $('#div1').append(
                    '<input type="hidden" name="motivo_solicitud[]" value="' + opcionSeleccionada + '" id="input-motivo-' + opcionSeleccionada + '">'
                );

                // Actualiza el estado del botón Confirmar/Guardar cuando se agrega un motivo
                try { updateMotivosState(); } catch (err) { /* ignore */ }

                // Reinicia el select
                $(this).val('');
            }

            //Tipo de persona
            document.addEventListener('DOMContentLoaded', function () {
                const selectTipo = document.getElementById('tipo');
                const nombreDiv = document.getElementById('tipoPersona_nombre');
                const razonDiv = document.getElementById('tipoPersona_razon');
                const curpDiv = document.getElementById('campo_curp');

                function actualizarTipoPersona() {
                    const valor = selectTipo.value;

                    nombreDiv.style.display = 'none';
                    razonDiv.style.display = 'none';
                    curpDiv.style.display = 'none';

                    if (valor === 'Fisica') {
                        nombreDiv.style.display = 'block';
                        curpDiv.style.display = 'block';
                    } else if (valor === 'Moral') {
                        razonDiv.style.display = 'block';
                    }
                }

                if (selectTipo) {
                    selectTipo.addEventListener('change', actualizarTipoPersona);
                    // Ejecutar al cargar por si ya tiene valor
                    actualizarTipoPersona();
                }

                const needsLanguage = document.getElementById('needsLanguageSolicitante');
                const languageDiv = document.getElementById('languageRequired');
                const languageInput = document.getElementById('languageValueSolicitante');

                const hasDisability = document.getElementById('hasDisabilitySolicitante');
                const disabilityDiv = document.getElementById('disabilityRequired');
                const disabilityInput = document.getElementById('disabilityValueSolicitante');

                const laboraActualmente = document.getElementById('laboraActualmenteValue');
                const fechaSalida = document.getElementById('fechaSalida')

                function updateLanguageVisibility() {
                    if (!languageDiv || !needsLanguage) return;
                    if (needsLanguage.value === 'Si') {
                        languageDiv.hidden = false;
                        if (languageInput) languageInput.required = true;
                    } else {
                        languageDiv.hidden = true;
                        if (languageInput) languageInput.required = false;
                    }
                }

                function updateDisabilityVisibility() {
                    if (!disabilityDiv || !hasDisability) return;
                    if (hasDisability.value === 'Si') {
                        disabilityDiv.hidden = false;
                        if (disabilityInput) disabilityInput.required = true;
                    } else {
                        disabilityDiv.hidden = true;
                        if (disabilityInput) disabilityInput.required = false;
                    }
                }

                function updateFechaSalidaVisibility() {
                    if (!laboraActualmente || !fechaSalida) return;
                    if (laboraActualmente.value === 'Si') {
                        fechaSalida.hidden = true;
                    } else {
                        fechaSalida.hidden = false;
                    }
                }

                if (needsLanguage) needsLanguage.addEventListener('change', updateLanguageVisibility);
                if (hasDisability) hasDisability.addEventListener('change', updateDisabilityVisibility);
                if (laboraActualmente) laboraActualmente.addEventListener('change', updateFechaSalidaVisibility)

                updateLanguageVisibility();
                updateDisabilityVisibility();
                updateFechaSalidaVisibility();


                const forms = document.querySelectorAll('form.needs-validation');
                if (forms && forms.length) {
                    // Función para validar teléfonos en tiempo real
                    function updateTelefonoState(form){
                        try{
                            var tel1 = form.querySelector('input[name="telefono1_solicitante"]');
                            var tel2 = form.querySelector('input[name="telefono2_solicitante"]');
                            var validTel1 = true;
                            var validTel2 = true;

                            if(tel1){
                                var d1 = (tel1.value || '').replace(/\D/g, '');
                                if(d1.length !== 10){
                                    tel1.classList.add('is-invalid');
                                    tel1.classList.remove('is-valid');
                                    try { tel1.setCustomValidity('El teléfono celular debe tener 10 dígitos'); } catch(e){}
                                    validTel1 = false;
                                } else {
                                    tel1.classList.remove('is-invalid');
                                    tel1.classList.add('is-valid');
                                    try { tel1.setCustomValidity(''); } catch(e){}
                                    validTel1 = true;
                                }
                            }

                            if(tel2){
                                var d2 = (tel2.value || '').replace(/\D/g, '');
                                if(tel2.value && d2.length !== 10){
                                    tel2.classList.add('is-invalid');
                                    tel2.classList.remove('is-valid');
                                    try { tel2.setCustomValidity('El teléfono fijo debe tener 10 dígitos'); } catch(e){}
                                    validTel2 = false;
                                } else {
                                    tel2.classList.remove('is-invalid');
                                    if(d2.length === 10) tel2.classList.add('is-valid'); else tel2.classList.remove('is-valid');
                                    try { tel2.setCustomValidity(''); } catch(e){}
                                    validTel2 = true;
                                }
                            }

                            var confirmArea = document.getElementById('tabla_confirmar');
                            if(confirmArea){
                                var existing = document.getElementById('telefono-warning');
                                if(!(validTel1 && validTel2)){
                                    if(!existing){
                                        var div = document.createElement('div');
                                        div.id = 'telefono-warning';
                                        div.className = 'text-muted mt-2';
                                        div.innerText = 'Por favor ingrese teléfonos válidos (10 dígitos) antes de guardar.';
                                        confirmArea.appendChild(div);
                                    }
                                } else { if(existing) existing.remove(); }
                            }
                        }catch(err){ console.warn('updateTelefonoState', err); }
                    }

                    forms.forEach(function(form) {
                        // Escuchar inputs de teléfono
                        var tel1input = form.querySelector('input[name="telefono1_solicitante"]');
                        var tel2input = form.querySelector('input[name="telefono2_solicitante"]');
                        if(tel1input){ tel1input.addEventListener('input', function(){ updateTelefonoState(form); }); tel1input.addEventListener('blur', function(){ updateTelefonoState(form); }); }
                        if(tel2input){ tel2input.addEventListener('input', function(){ updateTelefonoState(form); }); tel2input.addEventListener('blur', function(){ updateTelefonoState(form); }); }

                        form.addEventListener('submit', function(e) {
                            var valid = true;
                            var tel1 = form.querySelector('input[name="telefono1_solicitante"]');
                            var tel2 = form.querySelector('input[name="telefono2_solicitante"]');
                            if (tel1 && tel1.value.replace(/\D/g, '').length !== 10) {
                                if (typeof swal === 'function') { swal("Error", "El teléfono celular debe tener exactamente 10 dígitos.", "error"); } else { try { alert('El teléfono celular debe tener exactamente 10 dígitos.'); } catch(e){} }
                                try { tel1.focus(); } catch(e){}
                                valid = false;
                            }
                            if (tel2 && tel2.value && tel2.value.replace(/\D/g, '').length !== 10) {
                                if (typeof swal === 'function') { swal("Error", "El teléfono fijo debe tener exactamente 10 dígitos.", "error"); } else { try { alert('El teléfono fijo debe tener exactamente 10 dígitos.'); } catch(e){} }
                                try { tel2.focus(); } catch(e){}
                                valid = false;
                            }

                            try { updateTelefonoState(form); } catch(e){}

                            if (!valid) {
                                e.preventDefault();
                                e.stopPropagation();
                                form.classList.add('was-validated');
                            }
                        });
                    });
                }
            });

            // Eliminar fila e input hidden
            $(document).on('click', '.eliminar', function() {
                var fila = $(this).closest('tr');
                var idMotivo = fila.attr('data-id');

                // Elimina input y fila
                $('#input-motivo-' + idMotivo).remove();
                fila.remove();

                // Actualiza la lista de los motivos seleccionados
                motivosSeleccionados = motivosSeleccionados.filter(id => id !== idMotivo);

                // Actualiza el estado del botón al eliminar un motivo
                try { updateMotivosState(); } catch (err) { /* ignore */ }
            });
        
            $('#tabla_detalles').show();
            //$('#tabla_solicitante').show();
            $('#tabla_citados').show();
            $('#tabla_documentos').show();
            $('#tabla_observaciones').show();
            $('#tabla_confirmar').show();
       
        $('.open-modal').click(function() {
            //console.log("hola");
            const id = $(this).data('id'); // Obtiene el valor de data-id
            //console.log(id);
            document.getElementById('modal-id').value = id;
        });
        
        // Verifica si hay al menos un motivo y habilita/deshabilita los botones de confirmar/guardar
        function updateMotivosState(){
            try{
                // hidden inputs añadidos en esta sesión
                var hidden = document.querySelectorAll('input[name="motivo_solicitud[]"]');

                // buscar tabla con motivos ya capturados en servidor (thead contiene 'Motivo capturado')
                var existingRows = 0;
                var tables = document.querySelectorAll('table');
                tables.forEach(function(t){
                    try{
                        var th = t.querySelector('thead th');
                        if(!th) return;
                        var txt = th.textContent || th.innerText || '';
                        if(txt.trim().toLowerCase().indexOf('motivo capturado') !== -1){
                            var tb = t.tBodies[0];
                            if(tb) existingRows = tb.rows.length;
                        }
                    }catch(e){}
                });

                var totalMotivos = (hidden ? hidden.length : 0) + (existingRows ? existingRows : 0);
                var hasMotivo = totalMotivos > 0;

                var confirmArea = document.getElementById('tabla_confirmar');
                if(!confirmArea) return;
                // seleccionar botones relevantes dentro del área de confirmación
                var buttons = confirmArea.querySelectorAll('button[type="submit"], button[name="toquen"]');
                buttons.forEach(function(btn){
                    if(btn.hasAttribute('data-ignore-motivo')) return;
                    btn.disabled = !hasMotivo;
                });

                var existing = document.getElementById('motivo-warning');
                if(!hasMotivo){
                    if(!existing){
                        var div = document.createElement('div');
                        div.id = 'motivo-warning';
                        div.className = 'text-muted mt-2';
                        div.innerText = 'Debe agregar al menos un motivo para poder guardar/confirmar.';
                        confirmArea.appendChild(div);
                    }
                } else {
                    if(existing) existing.remove();
                }
            }catch(err){ console.warn('updateMotivosState', err); }
        }

        // Ejecutar al cargar la página
        try { document.addEventListener('DOMContentLoaded', updateMotivosState); } catch (err) {}
    </script>
    <script>
        function syncTipoCitado(index){
            try {
                var sel = document.querySelectorAll('select[name="tipo_persona_citado[]"]')[index];
                if(!sel) return;
                var nombreWrap = document.getElementById('nombre_wrap_' + index);
                var primerWrap = document.getElementById('primer_wrap_' + index);
                var nombreInput = document.querySelectorAll('input[name="nombre_citado[]"]')[index];
                var primerInput = document.querySelectorAll('input[name="primer_apellido[]"]')[index];
                var segundoWrap = document.getElementById('segundo_wrap_' + index);
                var segundoInput = document.querySelectorAll('input[name="segundo_apellido[]"]')[index];

                if (sel.value === 'Moral') {
                    // Tipo de persona moral solo puede visualizar el nombre
                    if (nombreWrap) nombreWrap.style.display = '';
                    if (primerWrap) primerWrap.style.display = 'none';
                    if (segundoWrap) segundoWrap.style.display = 'none';
                    if (nombreInput) nombreInput.required = true;
                    if (primerInput) primerInput.required = false;
                    if (segundoInput) segundoInput.required = false;
                } else {
                    // Tipo de persona física visualiza los 3
                    if (nombreWrap) nombreWrap.style.display = '';
                    if (primerWrap) primerWrap.style.display = '';
                    if (segundoWrap) segundoWrap.style.display = '';
                    if (nombreInput) nombreInput.required = true;
                    if (primerInput) primerInput.required = true;
                    if (segundoInput) segundoInput.required = false;
                }
                // Si este citado es el 'quien resulte'
                try {
                    var resulteFlag = sel.getAttribute('resulte_flag');
                    if (resulteFlag === 'Si') {
                        if (primerWrap) primerWrap.style.display = 'none';
                        if (segundoWrap) segundoWrap.style.display = 'none';
                        if (primerInput) primerInput.required = false;
                        if (segundoInput) segundoInput.required = false;
                    }
                } catch (err) {}
            } catch (err) {
                console.warn('syncTipoCitado error', err);
            }
        }

        document.addEventListener('DOMContentLoaded', function(){
            var selects = document.querySelectorAll('select[name="tipo_persona_citado[]"]');
            selects.forEach(function(sel, idx){
                syncTipoCitado(idx);
                sel.addEventListener('change', function(){ syncTipoCitado(idx); });
            });
        });
    </script>
    
@endsection


