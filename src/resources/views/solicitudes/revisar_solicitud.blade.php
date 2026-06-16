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

    <style>
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
    .span {
        width: 100%;
        height: 50px;
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
                            <h3 class="text-center">Solicitud</h3>
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
                            <form class="needs-validation novalidate" method="POST" action="{{route('correccion_solicitante')}}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{$id}}">
                                <div class="tab">
                                    <a class="btn btn-info" onclick="openCity(event, 'detalles')">Detalles</a>
                                    <a class="btn btn-info" onclick="openCity(event, 'solicitante')">Solicitante</a>
                                    <a class="btn btn-info" onclick="openCity(event, 'documentos')">Citado(s)</a>
                                    <a class="btn btn-info" onclick="openCity(event, 'observaciones')">Observaciones</a>
                                    <a class="btn btn-info" onclick="openCity(event, 'citados')">Documentos</a>
                                    
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
                                                <label for="password">Actividad económica</label>
                                                <input type="text" name="actividad_economica" class="form-control" value="<?=$general["actividad"];?>">
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <label for="name">Rama industrial del negocio</label>
                                                <select class="form-control" name="ramaIndustrial">
                                                    <option value="">Seleccione</option>
                                                    @foreach($ramas as $rama)
                                                        <option value="{{$rama['id']}}" {{ $rama["id"] == $general["id_rama"] ? "selected" : '' }} >{{$rama['rama_industrial']}}</option>
                                                    @endforeach
                                                </select>
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
                                                               <a href="{{ route('eliminar_motivo', ['id' => $id, 'id_motivo' => $motivo->id] ) }}" class="eliminar btn btn-danger btn-sm">Eliminar</button>
                                                            </td>   
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                   
                                        <div class="col-xs-6 col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Agregar Otro Motivo a la Solicitud</label>
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
                                                    <label for="password">Nombre</label>
                                                    <input type="text" class="form-control" name="nombre_solicitante" value="<?=$solicitante["nombre"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="confirm-password">Tipo de Persona</label>
                                                    <select name="tipo_persona_solicitante" class="form-control">
                                                        <option value="Fisica" {{ $solicitante["tipo_persona"] == 'Fisica' ? "selected" : '' }}>Física</option>
                                                        <option value="Moral"  {{ $solicitante['tipo_persona'] == 'Moral' ? "selected" : '' }}>Moral</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="confirm-password">CURP</label>
                                                    <input type="text" class="form-control" name="curp_solicitante" value="<?=$solicitante["curp"];?>">
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
                                                    <label for="confirm-password">Sexo</label>
                                                    <select name="sexo_solicitante" class="form-control">
                                                        <option value="H" {{ $solicitante["sexo"] == 'H' ? "selected" : '' }}>Hombre</option>
                                                        <option value="M"  {{ $solicitante['sexo'] == 'M' ? "selected" : '' }}>Mujer</option>
                                                        <option value="NB"  {{ $solicitante['sexo'] == 'NB' ? "selected" : '' }}>No Binario</option>
                                                        <option value="LGBTTTIQ"  {{ $solicitante['sexo'] == 'LGBTTTIQ' ? "selected" : '' }}>LGBTTTIQ+</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="confirm-password">Nacionalidad</label>
                                                    <select name="nacionalidad_solicitante" class="form-control">
                                                        <option value="Mexicana" {{ $solicitante["sexo"] == 'Mexicana' ? "selected" : '' }}>Mexicana</option>
                                                        <option value="Otra"  {{ $solicitante['sexo'] == 'Otra' ? "selected" : '' }}>Otra</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Estado del solicitante</label>
                                                    <select id="estado_solicitante" class="form-control" name="estado_solicitante">
                                                        @foreach($estados as $est)
                                                            <option value="{{$est['id']}}" {{ $solicitante['estado'] == $est['id'] ? "selected" : '' }}>{{$est['nombre']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El Estado es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Municipio del solicitante</label>
                                                    <select class="form-control" name="municipio_solicitante">
                                                        @foreach($municipios as $mun)
                                                            <option value="{{$mun['id']}}" {{ $solicitante['municipio_domicilio'] == $mun['id'] ? "selected" : '' }}>{{$mun['nombre']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El Municipio es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Email</label>
                                                    <input type="email" class="form-control" name="email_solicitante" value="<?=$solicitante["email"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Fecha Nacimiento</label>
                                                    <input type="date" class="form-control" name="fecha_nacimiento_solicitante" value="<?=$solicitante["fecha_nacimiento"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Edad</label>
                                                    <input type="text" class="form-control" name="edad_solicitante" value="<?=$solicitante["edad"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Teléfono</label>
                                                    <input type="text" class="form-control" name="telefono1_solicitante" value="<?=$solicitante["telefono1"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Teléfono (Opcional)</label>
                                                    <input type="text" class="form-control" name="telefono2_solicitante" value="<?=$solicitante["telefono2"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="confirm-password">Requiere Traductor</label>
                                                    <select name="traductor_solicitante" class="form-control">
                                                        <option value="Si" {{ $solicitante["traductor"] == 'Si' ? "selected" : '' }}>SI</option>
                                                        <option value="No"  {{ $solicitante['traductor'] == 'No' ? "selected" : '' }}>NO</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Lenguaje requerido</label>
                                                    <input type="text" class="form-control" name="lenguaje_solicitante" value="<?=$solicitante["lenguaje"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="confirm-password">Tiene Discapacidad</label>
                                                    <select name="discapacidad_solicitante" class="form-control">
                                                        <option value="Si" {{ $solicitante["discapacidad"] == 'Si' ? "selected" : '' }}>SI</option>
                                                        <option value="No"  {{ $solicitante['discapacidad'] == 'No' ? "selected" : '' }}>NO</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Discapacidad</label>
                                                    <input type="text" class="form-control" name="disc_solicitante" value="<?=$solicitante["tipo_discapacidad"];?>">   
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Dirección</h4>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Tipo de Vialidad</label>
                                                    <input type="text" class="form-control" name="tipo_vialidad" value="<?=$solicitante["tipo_vialidad"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-9">
                                                <div class="form-group">
                                                    <label for="password">Calle</label>
                                                    <input type="text" class="form-control" name="calle_solicitante" value="<?=$solicitante["calle"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Núm Ext.</label>
                                                    <input type="text" class="form-control" name="num_ext_solicitante" value="<?=$solicitante["num_ext"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Núm Int.</label>
                                                    <input type="text" class="form-control" name="num_int_solicitante" value="<?=$solicitante["num_int"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Código postal</label>
                                                    <input type="text" class="form-control" name="codigo_postal_solicitante" value="<?=$solicitante["codigo_postal"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Referencia</label>
                                                    <input type="text" class="form-control" name="referencia_solicitante" value="<?=$solicitante["referencia"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Colonia</label>
                                                    <input type="text" class="form-control" name="colonia_solicitante" value="<?=$solicitante["colonia"];?>">   
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
                                                    <label for="password">Y entre calle</label>
                                                    <input type="text" class="form-control" name="calle3_solicitante" value="<?=$solicitante["calle3"];?>">   
                                                </div>
                                            </div>
                                            
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Datos del trabajo</h4>
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
                                                    <label for="password">Puesto</label>
                                                    <input type="text" class="form-control" name="puesto" value="<?=$solicitante["puesto"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Periodo de pago</label>
                                                    <select name="periodo_pago" class="form-control">
                                                        <option value="">SELECCIONE</option>
                                                        <option value="Semanal"      {{ $solicitante['periodo_pago'] == 'Semanal' ? "selected" : '' }}>SEMANAL</option>
                                                        <option value="Quincenal"   {{ $solicitante['periodo_pago'] == 'Quincenal' ? "selected" : '' }}>QUINCENAL</option>
                                                        <option value="Mensual"     {{ $solicitante['periodo_pago'] == 'Mensual' ? "selected" : '' }}>MENSUAL</option>
                                                        <option value="Diario"      {{ $solicitante['periodo_pago'] == 'Diario' ? "selected" : '' }}>DIARIO</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Sueldo</label>
                                                    <input type="text" class="form-control" name="pago" value="<?=$solicitante["pago"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-2">
                                                <div class="form-group">
                                                    <label for="password">Fecha de Ingreso</label>
                                                    <input type="date" class="form-control" name="fecha_ingreso" value="<?=$solicitante["fecha_ingreso"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-2">
                                                <div class="form-group">
                                                    <label for="password">Fecha de Salida</label>
                                                    <input type="date" class="form-control" name="fecha_salida" value="<?=$solicitante["fecha_salida"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Jornada Laboral(Día(s) y hora(s))</label>
                                                    <input type="text" name="jornada" class="form-control" value="<?=$solicitante["jornada"];?>">
                                                    <div class="invalid-feedback">
                                                        El campo jornada laboral es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <div class="col-xs-12 col-sm-6 col-md-2">
                                                <div class="form-group">
                                                    <label for="password">Horas trabajadas a la semana</label>
                                                    <input type="text" class="form-control" name="horas_semana" value="<?=$solicitante["horas_semana"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-2">
                                                <div class="form-group">
                                                    <label for="password">Labora Actualmente</label>
                                                    <input type="text" class="form-control" name="labora" value="<?=$solicitante["labora"];?>">   
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <label for="name">Describe brevemente el motivo de tu solicitud</label>
                                                    <textarea class="form-control" name="descripcionSolicitud"><?=$solicitante["descripcionSolicitud"];?></textarea>
                                                    <div class="invalid-feedback">
                                                        El campo descripción del motivo de la solicitud es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div id="documentos" class="tabcontent">
                                    <div id="tabla_documentos" class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center">Datos Citado(s)</h4>
                                            </div>
                                        </div><br>

                                        @foreach($citados as $citado)
                                                <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:30px;">
                                                    <div class="form-group">
                                                        <h4 class="text-center">Citado</h4>
                                                    </div>
                                                </div><br>
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Nombre</label>
                                                        <input type="text" class="form-control" name="nombre_citado[]" value="<?=$citado["nombre"];?>">   
                                                    </div>
                                                </div>
                                                @if(!empty($citado['primer_apellido']))
                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="password">Primer apellido</label>
                                                            <input type="text" class="form-control" name="primer_apellido[]" value="<?=$citado["primer_apellido"];?>">   
                                                        </div>
                                                    </div>
                                                @endif
                                                @if(!empty($citado['segundo_apellido']))
                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="password">Segundo apellido</label>
                                                            <input type="text" class="form-control" name="segundo_apellido[]" value="<?=$citado["segundo_apellido"];?>">   
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de persona</label>
                                                        <select name="tipo_persona_citado[]" class="form-control">
                                                            <option value="">SELECCIONE</option>
                                                            <option value="Fisica" {{ $citado['tipo_persona'] == 'Fisica' ? "selected" : '' }}>Física</option>
                                                            <option value="Moral"  {{ $citado['tipo_persona'] == 'Moral' ? "selected" : '' }}>Moral</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                @if(!empty($citado['primer_apellido']))
                                                    <div class="col-xs-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="password">CURP</label>
                                                            <input type="text" class="form-control" name="curp_citado[]" value="<?=$citado["curp"];?>" maxlength="18">   
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">RFC</label>
                                                        <input type="text" class="form-control" name="rfc_citado[]" value="<?=$citado["rfc"];?>">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h4 class="text-center">Dirección</h4>
                                                    </div>
                                                </div><br>
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Colonia</label>
                                                        <input type="text" class="form-control" name="colonia_citado[]" value="<?=$citado["colonia"];?>" required>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de vialidad</label>
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
                                                            <option value="PROLONGACIÓN"   {{ $citado['tipo_vialidad'] == 'PROLONGACIÓN' ? "selected" : '' }} >Prolongación</option>
                                                            <option value="RETORNO"        {{ $citado['tipo_vialidad'] == 'RETORNO' ? "selected" : '' }} >Retorno</option>
                                                            <option value="VIADUCTO"       {{ $citado['tipo_vialidad'] == 'VIADUCTO' ? "selected" : '' }} >Viaducto</option>
                                                            <option value="PRIVADA"       {{ $citado['tipo_vialidad'] == 'PRIVADA' ? "selected" : '' }} >Privada</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo vialidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Código postal</label>
                                                        <input type="text" class="form-control" name="cp_citado[]" value="<?=$citado["cp"];?>" required>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Calle</label>
                                                        <input type="text" class="form-control" name="calle_citado[]" value="<?=$citado["calle"];?>" required>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Entre Calle</label>
                                                        <input type="text" class="form-control" name="calle1_citado[]" value="<?=$citado["calle1"];?>">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Y calle</label>
                                                        <input type="text" class="form-control" name="calle2_citado[]" value="<?=$citado["calle2"];?>">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-1">
                                                    <div class="form-group">
                                                        <label for="password">N° Ext.</label>
                                                        <input type="text" class="form-control" name="n_ext_citado[]" value="<?=$citado["n_ext"];?>" required>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-1">
                                                    <div class="form-group">
                                                        <label for="password">N° Int.</label>
                                                        <input type="text" class="form-control" name="n_int_citado[]" value="<?=$citado["n_int"];?>">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-2">
                                                    <div class="form-group">
                                                        <label for="password">Estado</label>
                                                        <select class="form-control" name="estado_citado[]" id="estado_citado">
                                                            @foreach($estados as $est)
                                                                <option value="{{$est['id']}}" {{ $citado['estado_citado'] == $est['id'] ? "selected" : '' }}>{{$est['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El Estado es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-2">
                                                    <div class="form-group">
                                                        <label for="password">Municipio</label>
                                                        <select class="form-control" name="municipio_citado[]" id="municipio_citado">
                                                            @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}" {{ $citado['municipio_citado'] == $mun['id'] ? "selected" : '' }}>{{$mun['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El Municipio es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="password">Referencia</label>
                                                        <input type="text" class="form-control" name="referencia_citado[]" value="<?=$citado["referencia"];?>" required>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <label for="password">Referencia 1</label><br>
                                                    @if (!empty($citado->imagen_domicilio1) && $citado->imagen_domicilio1 !== 'Sin documento')
                                                        <a target='_blank' href="../storage/app/documentosSolicitud/{{$citado->imagen_domicilio1}}">VER IMAGEN</a><br>
                                                    @else
                                                        <span class="text-muted">No se subió imagen</span>
                                                    @endif
                                                    <input type="file" name="foto1[]" accept="image/*" class="form-control">
                                                    <input type="hidden" name="imagen_domicilio1[]" value="{{ $citado->imagen_domicilio1 }}">
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <label for="password">Referencia 2</label><br>
                                                    @if (!empty($citado->imagen_domicilio2) && $citado->imagen_domicilio2 !== 'Sin documento')
                                                        <a target='_blank' href="../storage/app/documentosSolicitud/{{$citado->imagen_domicilio2}}">VER IMAGEN</a><br>
                                                    @else
                                                        <span class="text-muted">No se subió imagen</span>
                                                    @endif
                                                    <input type="file" name="foto2[]" accept="image/*" class="form-control">
                                                    <input type="hidden" name="imagen_domicilio2[]" value="{{ $citado->imagen_domicilio2 }}">
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12"><br></div>
                                            @endforeach
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <a type="button" class="btn btn-warning open-modal" data-bs-toggle="modal" 
                                                data-bs-target="#exampleModal1" data-id="{{ $id }}">Agregar Citado</a>
                                            <a type="button" class="btn btn-warning open-modal" data-bs-toggle="modal" 
                                                data-bs-target="#exampleModal2" data-id="{{ $id }}">Borrar Citado</a>
                                        </div>
                                    </div>
                                </div>

                                <div id="citados" class="tabcontent">
                                    <div id="tabla_citados" class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center">Documentos</h4>
                                            </div>
                                        </div><br>

                                        <!--<div class="col-xs-12 col-sm-12 col-md-6">
                                            <label for="password">CURP</label><br>
                                            <a target='_blank' href="../storage/app/documentosSolicitud/{{$solicitante->documentoCurp}}">PDF</a><br>
                                            <input type="file" name="documentoCurp" accept=".pdf" class="form-control">
                                        </div>-->

                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <label for="password">Identificación Oficial</label><br>
                                            <a target='_blank' href="../storage/app/documentosSolicitud/{{$solicitante->documentoIdentificacion}}">PDF</a><br>
                                            <input type="file" name="documentoIdentificacion" accept=".pdf" class="form-control">
                                        </div>
                                        <br>
                                        
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-12"><br>
                                        @if($general['estatus'] != 'Concluida' || $general['estatus'] != 'Incompetencia' || $general['estatus'] != 'Incomparecencia' || $general['estatus'] != 'Conciliacion'
                                            || $general['estatus'] != 'Reagendada' || $general['estatus'] != 'Archivada' || $general['estatus'] != 'No conciliacion' || $general['estatus'] != 'Incumplimiento')
                                            <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>
                                        @endif
                                        <a class="btn btn-primary" href="{{ route('solicitudes_index') }}">Regresar</a>
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


<!-- Modal -->
    <div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form class='needs-validation novalidate'  method='POST' action="{{route('agregar_citado_edicion')}}">
            @csrf
            <input type="hidden" name="id" value="{{$id}}">
            <div class="modal-dialog  modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Agregar Citado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <h4 class="text-center">Dirección del citado</h4>
                                </div>
                            </div>                                        

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Agregar "Quien resulte responsable"</label>
                                    <select name="responsable" class="form-control" required>
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
                                    <label for="name">Tipo de Vialidad del Citado *</label>
                                    <select name="vialidad" class="form-control" required>
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
                                        <option value="PROLONGACIÓN">Prolongación</option>
                                        <option value="RETORNO">Retorno</option>
                                        <option value="VIADUCTO">Viaducto</option>
                                        <option value="PRIVADA">Privada</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        El campo vialidad es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Calle del citado *</label>
                                    <input type="text" name="calle" class="form-control" required> 
                                    <div class="invalid-feedback">
                                        El campo calle es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Colonia del Citado *</label>
                                    <input type="text" name="colonia" class="form-control" required> 
                                    <div class="invalid-feedback">
                                        El campo colonia es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Código Postal del citado *</label>
                                    <input type="text" name="cp" class="form-control" minlength="5" maxlength="5" required> 
                                    <div class="invalid-feedback">
                                        El campo Código Postal es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Entre calle del domicilio del citado *</label>
                                    <input type="text" name="calle1" class="form-control"> 
                                    <div class="invalid-feedback">
                                        El campo calle es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">y calle del domicilio del citado *</label>
                                    <input type="text" name="calle2" class="form-control"> 
                                    <div class="invalid-feedback">
                                        El campo calle es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Núm. ext. del citado *</label>
                                    <input type="text" name="exterior" class="form-control" required> 
                                    <div class="invalid-feedback">
                                        El campo c
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Núm. int. del citado</label>
                                    <input type="text" name="interior" class="form-control" > 
                                    <div class="invalid-feedback">
                                        El campo calle es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="name">Nombre del Municipio o Alcaldía del citado *</label>
                                    <select id="municipio_citado" class="form-control" name="municipio_citado" required>
                                        <option value="">Seleccione</option>
                                        @foreach($municipios as $mun)
                                            <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        El campo municipio o alcaldía es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                <label for="floatingTextarea">Referencias del domicilio del citado *</label>
                                    <textarea class="form-control" placeholder="Ingresa alguna referencia de como llegar" name="referencia"></textarea>
                                    <div class="invalid-feedback">
                                        El campo referencias es obligatorio.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Tipo de personas</label>
                                    <select name="tipo" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option value="Fisica">Fisica</option>
                                        <option value="Moral">Moral</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        El tipo de persona es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">CURP</label>
                                    <input type="text" name="curp" id="curp_input" oninput="validarInput(this)" class="form-control"> 
                                    <pre id="resultado"></pre>
                                    <div class="invalid-feedback">
                                        La CURP es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre(s) *</label>
                                    <input type="text" name="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                    <div class="invalid-feedback">
                                        El nombre es obligatorio.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Primer apellido *</label>
                                    <input type="text" name="primer_apellido" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                    <div class="invalid-feedback">
                                        El Primer apellido es obligatorio.
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Segundo apellido *</label>
                                    <input type="text" name="segundo_apellido" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                    <div class="invalid-feedback">
                                        El Segundo apellido es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">RFC</label>
                                    <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13" > 
                                    <div class="invalid-feedback">
                                        El campo conflicto es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <label for="name">Requiere algun lenguaje</label>
                                <select name="lenguaje" class="form-control" required>
                                    <option value="">SELECCIONE</option>
                                    <option value=" ">Si</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6" id="lenguaje_señas">
                                <div class="form-group">
                                    <label for="name">Que tipo de lenguaje require</label>
                                    <input type="text" name="lenguaje" class="form-control">
                                    <div class="invalid-feedback">
                                        La nacionalidad es obligatoria.
                                    </div>
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
                                    <td>{{$citado->nombre}}</td>
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


@section('scripts')
    <script src="../public/assets/js/estadistica/estadistica.js"></script>
        <script>
            $(function(){
                $('#motivo_solicitud').on('change', validarcheckfolio);
            })


            let motivosSeleccionados = [];

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

                // Reinicia el select
                $(this).val('');
            }

            // Eliminar fila e input hidden
            $(document).on('click', '.eliminar', function() {
                var fila = $(this).closest('tr');
                var idMotivo = fila.attr('data-id');

                // Elimina input y fila
                $('#input-motivo-' + idMotivo).remove();
                fila.remove();

                // Actualiza la lista de los motivos seleccionados
                motivosSeleccionados = motivosSeleccionados.filter(id => id !== idMotivo);
            });
        
            $('#tabla_detalles').show();
            $('#tabla_solicitante').show();
            $('#tabla_citados').show();
            $('#tabla_documentos').show();
    </script>
@endsection

