@extends('layouts.app')
    @php
        $fechaActual = date('Y-m-d');
        $contador = 0;
    @endphp
    <style>
        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
           /* background-color: #6A0F49;/*<p style="color: #CEA845*/
            opacity: .8;
        }
        .resultado {
            background-color: red;
            color: white;
            font-weight: bold;
        }
        .resultado.ok {
            background-color: green;
        }

        .fc-event {
            padding: 3px 6px !important;
            border-radius: 4px !important;
            font-size: 12px !important;
            cursor: pointer;
        }

        #calendar {
            width: 100%;
            min-height: 500px;
        }

        .fc-event-disponible {
            color: #ffff !important;
            background-color: #00CE1C !important;
            border-color: #00CE1C !important;
            cursor: pointer;
        }

        .fc-event-expirado {
            color: #ffff !important;
            /*background-color: #F0DF24 !important;
            border-color: #F0DF24 !important;*/
            background-color: #F59727 !important;
            border-color: #F59727 !important;
            cursor: not-allowed;
        }

        .fc-event-inhabil {
            color: #ffff !important;
            background-color: #3B78DB !important;
            border-color: #3B78DB !important;
            cursor: not-allowed;
        }

        .fc-event-ocupado {
            color: #ffff !important;
            background-color: #DA0909 !important;
            border-color: #DA0909 !important;
            cursor: not-allowed;
        }

        .fc-event-selected {
            border: 2px solid #FFD700 !important;
            box-shadow: 0 0 8px #FFD700;
        }

        .modal-xl {
            max-width: 95% !important;
        }

        .modal-content {
            height: 90vh;
        }

        .modal-body {
            overflow-y: auto;
        }

        .btn-custom-morado {
            height: 50px;
            font-size: 12px;
            padding: 5px 10px;
            background-color: #6A0F49 !important;
            color: #fff !important;
            border: none;
        }
        .btn-custom-morado:hover, .btn-custom-morado:focus {
            background-color: #530c3a !important;
            color: #fff !important;
        }
    </style>
@section('content')
    <body onload="validarcheckfolio()">
    <main>
        <div id="app">  
            <section class="section"> 
                <div class="section-body">
                    <div class="row"> 
                        <div class="col-lg-12" >
                            <div class="card">
                                <div class="card-body">
                                    <!--Se realiza la validación de campos para ver si dejó alguno vacío-->
                                    @if (session()->has('error'))
                                        <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                            <strong>¡Revise los campos!</strong>
                                            {{ session()->get('error') }}
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                    <div style="background-color:#D2D3D5; width:100%; height:40px;">
                                        <h3 class="text-center" style="color:black">Edición ratificación</h3>
                                    </div>    
                                    <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                    <form class='needs-validation novalidate'  method='POST' action="{{route('guardarEdicion_citas')}}" enctype="multipart/form-data" onsubmit="return validacionCamposInput()">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $idSolicitud }}">
                                        <br><br>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="">Folio Interno de Registro <span style="color:red;">(*)</span></label>
                                                    <input type="number" name="folio" id="folio_input" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["idAbogado"];?>" required>  
                                
                                                    <div class="invalid-feedback">
                                                        El folio es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-9">
                                                <div id="abogado_info" class="mt-2"></div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:30px;">
                                                <div class="form-group">
                                                    <h4 class="text-center">Datos del Trabajador</h4>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="primero" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["primero_trabajador"];?>" required>  
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="segundo" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["segundo_trabajador"];?>" required>                                                         <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Nombre(s) <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["trabajador"];?>" required> 
                                                    <div class="invalid-feedback">
                                                        El campo nombre es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div  class="col-xs-12 col-sm-12 col-md-1">
                                                <div class="form-group">
                                                    <label for="name">Edad <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="edad" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["edad"];?>" required> 
                                                    <div class="invalid-feedback">
                                                        El campo edad es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div   class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                                    <select name="trabajador_sexo" class="form-control" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="H" {{ $solicitud['sexo'] == 'H' ? "selected" : '' }}>Hombre</option>
                                                        <option value="M" {{ $solicitud['sexo'] == 'M' ? "selected" : '' }}>Mujer</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo sexo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-5">
                                                <div class="form-group">
                                                    <label for="name">CURP del trabajador <span style="color:red;">(*)</span></span></label>
                                                    <input type="text" name="curp" id="curp_input" oninput="validarInput(this)"class="form-control" value="<?=$solicitud["trabajador_curp"];?>" required> 
                                                    <pre id="resultado"></pre>
                                                    <!--<pre id="resultado_curp_trabajador" class="resultado"></pre>-->
                                                    <div class="invalid-feedback">
                                                        El campo curp es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div  class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Documento de la CURP (Opcional)</span></label>
                                                    <input type="file" id="documentoCurp" name="documentoCurp" class="form-control" accept=".pdf"> 
                                                    <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_ratificacion/{{$solicitud->documentoCurp}}">Existente</a>
                                                    <div class="invalid-feedback">
                                                        El campo edad es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Identificación Oficial <span style="color:red;">(*)</span></label>
                                                    <select id="tipo_identificacion" name="tipo_identificacion" class="form-control"  required>
                                                        <option value="">Seleccione el tipo de indentificación</option>
                                                        <option value="Credencial de elector" {{ $solicitud['tipo_identificacion'] == 'Credencial de elector' ? "selected" : '' }}>Credencial de Elector</option>
                                                        <option value="Pasaporte" {{ $solicitud['tipo_identificacion'] == 'Pasaporte' ? "selected" : '' }}>Pasaporte</option>
                                                        <option value="Cédula profesional" {{ $solicitud['tipo_identificacion'] == 'Cédula profesional' ? "selected" : '' }}>Cédula Profesional</option>
                                                        <option value="Licencia de conducir" {{ $solicitud['tipo_identificacion'] == 'Licencia de conducir' ? "selected" : '' }}>Licencia de Conducir</option>
                                                        <option value="Credencial de inapam" {{ $solicitud['tipo_identificacion'] == 'Credencial de inapam' ? "selected" : '' }}>Credencial de INAPAM</option>
                                                        <option value="Cartilla militar" {{ $solicitud['tipo_identificacion'] == 'Cartilla militar' ? "selected" : '' }}>Cartilla Militar</option>
                                                        <option value="Documento migratorio" {{ $solicitud['tipo_identificacion'] == 'Documento migratorio' ? "selected" : '' }}>Documento Migratorio</option>
                                                        <option value="Constancia de identidad" {{ $solicitud['tipo_identificacion'] == 'Constancia de identidad' ? "selected" : '' }}>Constancia de Identidad</option>
                                                        <option value="Otro" {{ $solicitud['tipo_identificacion'] == 'Otro' ? "selected" : '' }}>Otros</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Este campo identificación es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="espesificar_tipo_identificacion" class="col-xs-12 col-sm-12 col-md-4" style="display:none">
                                                <div class="form-group">
                                                    <label for="name">Especificar</label>
                                                    <input type="text" name="tipo_otros" class="form-control" > 
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4"> 
                                                <div class="form-group">
                                                    <label for="name">Núm de identificación <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="num_identificacion" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["num_identificacion"];?>" required> 
                                                    <div class="invalid-feedback">
                                                        El campo núm. de identificación es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div  class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Subir Identificación Oficial <span style="color:red;">(*)</span></label>
                                                    <input type="file" id="documentoidentificacion" name="documentoidentificacion" class="form-control" accept=".pdf"> 
                                                    <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_ratificacion/{{$solicitud->documentoidentificacion}}">Existente</a>
                                                    <div class="invalid-feedback">
                                                        El campo identificación es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                                
                                            <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:30px;">
                                                <div class="form-group">
                                                    <h4 class="text-center">Datos de la Relación Laboral</h4>
                                                </div>
                                            </div>
                                                

                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Estado <span style="color:red;">(*)</span></label>
                                                        <select class="form-control" name="estado_rat" required>
                                                            <option value="">Seleccione</option>
                                                            @foreach($estados as $est)
                                                                <option value="{{ $est['id'] }}" {{ $solicitud['estado_rat'] ==  $est['id'] ? "selected" : '' }} >{{$est['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo estado es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Municipio o Alcaldía <span style="color:red;">(*)</span></label>
                                                        <select id="municipio_rat" class="form-control" name="municipio_rat" placeholder="*Municipio" required>
                                                            <option value="">Seleccione</option>
                                                            @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}" {{ $solicitud['municipio_rat'] ==  $mun['id'] ? "selected" : '' }}>{{$mun['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo municipio o alcaldía es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de Vialidad <span style="color:red;">(*)</span></label>
                                                        <select name="tipo_vialidad" id="tipo_vialidad" class="form-control" placeholder="*Vialidad" required>
                                                            <option value="">SELECCIONE</option>
                                                            <option value="AMPLIACIÓN" {{ $solicitud["tipo_vialidad"] == 'AMPLIACIÓN' ? "selected" : '' }}>Ampliación</option>
                                                            <option value="ANDADOR" {{ $solicitud["tipo_vialidad"] == 'ANDADOR' ? "selected" : '' }}>Andador</option>
                                                            <option value="AUTOPISTA" {{ $solicitud["tipo_vialidad"] == 'AUTOPISTA' ? "selected" : '' }}>Autopista</option>
                                                            <option value="AVENIDA" {{ $solicitud["tipo_vialidad"] == 'AVENIDA' ? "selected" : '' }}>Avenida</option>
                                                            <option value="BOULEVARD" {{ $solicitud["tipo_vialidad"] == 'BOULEVARD' ? "selected" : '' }}>Boulevard</option>
                                                            <option value="CALLE" {{ $solicitud["tipo_vialidad"] == 'CALLE' ? "selected" : '' }}>Calle</option>
                                                            <option value="CALLEJÓN" {{ $solicitud["tipo_vialidad"] == 'CALLEJÓN' ? "selected" : '' }}>Callejón</option>
                                                            <option value="CALZADA" {{ $solicitud["tipo_vialidad"] == 'CALZADA' ? "selected" : '' }}>Calzada</option>
                                                            <option value="CARRETERA" {{ $solicitud["tipo_vialidad"] == 'CARRETERA' ? "selected" : '' }}>Carretera</option>
                                                            <option value="CERRADA" {{ $solicitud["tipo_vialidad"] == 'CERRADA' ? "selected" : '' }}>Cerrada</option>
                                                            <option value="CIRCUITO" {{ $solicitud["tipo_vialidad"] == 'CIRCUITO' ? "selected" : '' }}>Circuito</option>
                                                            <option value="CIRCUNVALACIÓN" {{ $solicitud["tipo_vialidad"] == 'CIRCUNVALACIÓN' ? "selected" : '' }}>Circunvalación</option>
                                                            <option value="CONTINUACIÓN" {{ $solicitud["tipo_vialidad"] == 'CONTINUACIÓN' ? "selected" : '' }}>Continuación</option>
                                                            <option value="CORREDOR" {{ $solicitud["tipo_vialidad"] == 'CORREDOR' ? "selected" : '' }}>Corredor</option>
                                                            <option value="DIAGONAL" {{ $solicitud["tipo_vialidad"] == 'DIAGONAL' ? "selected" : '' }}>Diagonal</option>
                                                            <option value="EJE VIAL" {{ $solicitud["tipo_vialidad"] == 'EJE VIAL' ? "selected" : '' }}>Eje vial</option>
                                                            <option value="PERIFÉRICO" {{ $solicitud["tipo_vialidad"] == 'PERIFÉRICO' ? "selected" : '' }}>Periférico</option>
                                                            <option value="PRIVADA" {{ $solicitud["tipo_vialidad"] == 'PRIVADA' ? "selected" : '' }}>Privada</option>
                                                            <option value="PROLONGACIÓN" {{ $solicitud["tipo_vialidad"] == 'PROLONGACIÓN' ? "selected" : '' }}>Prolongación</option>
                                                            <option value="RETORNO" {{ $solicitud["tipo_vialidad"] == 'RETORNO' ? "selected" : '' }}>Retorno</option>
                                                            <option value="VIADUCTO" {{ $solicitud["tipo_vialidad"] == 'VIADUCTO' ? "selected" : '' }}>Viaducto</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo vialidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre de la Vialidad <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="vialidad_calle" id="vialidad_calle" class="form-control" placeholder="*Nombre vialidad" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["calle"];?>" required> 
                                                        <div class="invalid-feedback">
                                                            El campo vialidad o calle es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Colonia <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Colonia" name="colonia" id="colonia" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["colonia"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Ext. <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Núm. exterior" name="N_Ext" id="N_Ext" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["num_ext"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
            
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Int.(Opcional)</label>
                                                        <input type="text" class="form-control" placeholder="Núm. interior" name="N_Int" id="N_Int" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["num_int"];?>"> 
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
            
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Código postal <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Código postal" name="cp" id="cp" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["codigo_postal"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>



                                            <div class="col-xs-12 col-sm-12 col-md-2"> 
                                                <div class="form-group">
                                                    <label for="fecha_inicio">Fecha de inicio de la relación laboral <span style="color:red;">(*)</span></label>
                                                    <input type="date" name="fecha_inicio" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["fecha_inicio"];?>" required>
                                                    <div class="invalid-feedback">
                                                        El campo fecha de inicio es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="fecha_termino">Fecha de término de la relación laboral</label>
                                                    <input type="date" name="fecha_termino" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["fecha_termino"];?>">
                                                </div>
                                            </div>
                                            <div  class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Categoría o Puesto que desempeña <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="categoria" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["categoria"];?>" required>
                                                    <div class="invalid-feedback">
                                                        El campo categoría es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Frecuencia de pago <span style="color:red;">(*)</span></label>
                                                    <select name="frecuencia" class="form-control"  required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Diario" {{ $solicitud['frecuencia'] == 'Diario' ? "selected" : '' }}>Diario</option>
                                                        <option value="Semanal" {{ $solicitud['frecuencia'] == 'Semanal' ? "selected" : '' }}>Semanal</option>
                                                        <option value="Quincenal" {{ $solicitud['frecuencia'] == 'Quincenal' ? "selected" : '' }}>Quincenal</option>
                                                        <option value="Mensual" {{ $solicitud['frecuencia'] == 'Mensual' ? "selected" : '' }}>Mensual</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        Este campo frecuencia es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Salario <span style="color:red;">(*)</span></label><br>
                                                    <input type="text" name="salario" class="form-control soloMontos" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["salario"];?>" required>
                                                    <div class="invalid-feedback">
                                                        Este campo salario es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Días a la semana trabajados <span style="color:red;">(*)</span></label>
                                                    <input type="number" name="dias" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["dias"];?>" required>
                                                    <div class="invalid-feedback">
                                                        Este campo días a la semana trabajados es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                                
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Motivo de la conciliación <span style="color:red;">(*)</span></label>
                                                    <select id="motivo" name="motivo" class="form-control" type="number"  required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Pago de prestaciones" {{ $solicitud['motivo'] == 'Pago de prestaciones' ? "selected" : '' }}>Pago de prestaciones</option>
                                                        <option value="Terminación voluntaria de la relación de trabajo" {{ $solicitud['motivo'] == 'Terminación voluntaria de la relación de trabajo' ? "selected" : '' }}>Terminación voluntaria de la relación de trabajo</option>
                                                        <option value="PTU" {{ $solicitud['motivo'] == 'PTU' ? "selected" : '' }}>PTU</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo motivo de la conciliación es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="motivo_pago" class="col-xs-12 col-sm-12 col-md-2" style="display:none">
                                                <div class="form-group">
                                                    <label for="name">Selecciona las casillas correspondientes <span style="color:red;">(*)</span></label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="Aguinaldo">
                                                        <label class="form-check-label" for="flexCheckDefault">
                                                            Aguinaldo
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="Vacaciones">
                                                        <label class="form-check-label" for="flexCheckDefault">
                                                            Vacaciones
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="PrimaVacacional">
                                                        <label class="form-check-label" for="flexCheckDefault">
                                                            Prima Vacacional
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="PagoPTU">
                                                        <label class="form-check-label" for="flexCheckDefault">
                                                            Pago de PTU
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="Gratificación">
                                                        <label class="form-check-label" for="flexCheckDefault">
                                                            Gratificación
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="PrimaAntigüedad">
                                                        <label class="form-check-label" for="flexCheckDefault">
                                                            Prima de Antigüedad
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="Otras" id="otras">
                                                        <label class="form-check-label" for="flexCheckDefault">
                                                            Otras
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="div_otras" class="col-xs-12 col-sm-12 col-md-3" style="display:none">
                                                <div class="form-group">
                                                    <label for="name">Especifique</label>
                                                    <input type="text" name="Especifique" class="form-control" > 
                                                    <div class="invalid-feedback">
                                                        El campo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                                
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Monto total del convenio a pagar <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="monto" class="form-control soloMontos" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["monto"];?>" required>
                                                    <div class="invalid-feedback">
                                                        El campo monto es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Forma de pago <span style="color:red;">(*)</span></label>
                                                    <select name="tipo_pago" class="form-control"  required>
                                                        <option value="">Seleccione el tipo de pago</option>
                                                        <option value="Efectivo" {{ $solicitud['tipo_pago'] == 'Efectivo' ? "selected" : '' }}>Efectivo</option>
                                                        <option value="Transferencia" {{ $solicitud['tipo_pago'] == 'Transferencia' ? "selected" : '' }}>Transferencia</option>
                                                        <option value="Cheque" {{ $solicitud['tipo_pago'] == 'Cheque' ? "selected" : '' }}>Cheque</option>
                                                        <option value="Cheque Electrónico" {{ $solicitud['tipo_pago'] == 'Cheque Electrónico' ? "selected" : '' }}>Cheque Electrónico</option>
                                                        <option value="Orden de Pago" {{ $solicitud['tipo_pago'] == 'Orden de Pago' ? "selected" : '' }}>Orden de Pago</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo edad es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Sube tu cuantificación (Opcional)</label>
                                                    <input type="file" id="cuantificacion" name="cuantificacion" class="form-control" accept=".pdf"> 
                                                    <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_ratificacion/{{$solicitud->documentoCuanti}}">Existente</a>
                                                    <div class="invalid-feedback">
                                                        El campo edad es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Sedes <span style="color:red;">(*)</span></label>
                                                    <select name="sede" class="form-control" required>
                                                        <option value="">Seleccione la sede</option>
                                                        <option value="Morelia" {{ $solicitud['delegacion'] == 'Morelia' ? "selected" : '' }}>Morelia</option>
                                                        <option value="Zitácuaro" {{ $solicitud['delegacion'] == 'Zitácuaro' ? "selected" : '' }}>Zitácuaro</option>
                                                        <option value="Uruapan" {{ $solicitud['delegacion'] == 'Uruapan' ? "selected" : '' }}>Uruapan</option>
                                                        <option value="Lázaro Cárdenas" {{ $solicitud['delegacion'] == 'Lázaro Cárdenas' ? "selected" : '' }}>Lázaro Cárdenas</option>
                                                        <option value="Zamora" {{ $solicitud['delegacion'] == 'Zamora' ? "selected" : '' }}>Zamora</option>
                                                        <option value="Sahuayo" {{ $solicitud['delegacion'] == 'Sahuayo' ? "selected" : '' }}>Sahuayo</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        La sede es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-2"> 
                                                <div class="form-group">
                                                    <label for="fecha_inicio">Fecha de ratificación <span style="color:red;">(*)</span></label>
                                                    <input type="date" name="fecha" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["fecha"];?>" required>
                                                    <div class="invalid-feedback">
                                                        El campo fecha de inicio es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Hora de ratificación <span style="color:red;">(*)</span></label>
                                                    <input type="time" class="form-control" name="hora" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitud["hora"];?>"required> 
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-primary" onclick="window.history.back()">Regresar</button>
                                                <button type="submit" class="btn btn-success">Guardar edición</button>
                                                @if($solicitud['estatus'] != "Pendiente")    
                                                    <a class="btn btn-danger" href="{{ route('vista_previa_ratificacion', $solicitud->id) }}"  target="_blank">Editar finalización de ratificación</a>                          
                                                @endif
                                                
                                            </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@endsection
    <script>
        //dependiendo del folio ingresado para el abogado indica el nombre del representante y la empresa, o una leyenda en caso de no existir
        document.addEventListener('DOMContentLoaded', function () {
            const folioInput = document.getElementById('folio_input');
            const abogadoInfoDiv = document.getElementById('abogado_info');
            let timeout = null;

            const baseUrl = "{{ url('/validar_folio_abogado') }}";

            folioInput.addEventListener('keyup', function () {
                clearTimeout(timeout);
                const folio = this.value.trim();
                if (folio === '') {
                    abogadoInfoDiv.textContent = '';
                    abogadoInfoDiv.classList.remove('alert', 'alert-success', 'alert-danger');
                    return;
                }
                timeout = setTimeout(() => {
                    const finalUrl = `${baseUrl}/${folio}`;
                    fetch(finalUrl, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 404) throw new Error('Folio no encontrado');
                            throw new Error('Error en la petición');
                        }
                        return response.json();
                    })
                    .then(data => {
                        abogadoInfoDiv.classList.remove('alert-danger');
                        abogadoInfoDiv.textContent = `Representante: ${data.nombre}`;
                        abogadoInfoDiv.classList.add('alert', 'alert-success');
                    })
                    .catch(error => {
                        abogadoInfoDiv.classList.remove('alert-success');
                        abogadoInfoDiv.classList.add('alert', 'alert-danger');
                        abogadoInfoDiv.textContent = (error.message === 'Folio no encontrado')
                            ? 'El folio no existe. Por favor, verifica el número.'
                            : 'Ocurrió un error al buscar. Inténtalo de nuevo.';
                        console.error('Error:', error);
                    });
                }, 500);
            });
        });
    </script>
    <script>
        /*document.getElementById("folio").style.display = "none";
        document.getElementById("agregar_persona").style.display = "block";
        document.getElementById("empresa").style.display = "block";
        document.getElementById("primero").style.display = "block";
        document.getElementById("segundo").style.display = "block";
        document.getElementById("nombre").style.display = "block";
        document.getElementById("edad").style.display = "block";
        document.getElementById("sexo").style.display = "block";
        document.getElementById("ine").style.display = "block";
        document.getElementById("acta").style.display = "block";*/


        /*function sedes(){
            document.getElementById("fecha").removeAttribute("disabled");
        }

        function modalCalendar(){
            document.getElementById("botonCalendar").removeAttribute("disabled");
        }

        function diaSemana() {
            var dia_semana  = document.getElementById("fecha").value;
            var sede        = document.getElementById("sede").value;

            $.get('api/obtenerHorario/'+dia_semana+'/'+sede, function (data){
                var html_select = '<option value="">--Seleccione un horario --</option>';  
                for(var i=0; i<data.length; ++i)
                    html_select += '<option value= "'+data[i].hora+'">'+data[i].hora+'</option>';
                    $('#horarios').html(html_select);

            });
        }*/

       /* $(function(){
            $('#check_folio').on('change', validarcheckfolio);
        })*/

        function validarcheckfolio(){
            tipo = document.getElementById("folio").style.display;

            const camposEmpresa = {
                
                "primero_empresa": "soloLetras",
                "segundo_empresa": "soloLetras",
                "nombre_empresa": "soloLetras",
                "email": "correoElectronico",
                "telefono": "numeroTelefonico"
            };
            
            if (tipo == "none") {
                //document.getElementById("folio").style.display = "block";
                //document.getElementById("agregar_persona").style.display = "none";
                /*document.getElementById("razon").style.display = "none";
                document.getElementById("empresa").style.display = "none";
                document.getElementById("primero").style.display = "none";
                document.getElementById("segundo").style.display = "none";
                document.getElementById("nombre").style.display = "none";
                document.getElementById("edad").style.display = "none";
                document.getElementById("sexo").style.display = "none";
                document.getElementById("ine").style.display = "none";
                document.getElementById("acta").style.display = "none";
                document.getElementById("curp_empresa").style.display = "none";*/
                
                // Quitar atributos y clases de validación
                for (const [id, clase] of Object.entries(camposEmpresa)) {
                    const campo = document.getElementById(id);
                    if (campo) {
                        campo.removeAttribute("required");
                        campo.classList.remove(clase);
                    }
                }
            }
            else{
                //document.getElementById("folio").style.display = "none";
                //document.getElementById("agregar_persona").style.display = "block";
                /*document.getElementById("empresa").style.display = "block";
                document.getElementById("primero").style.display = "block";
                document.getElementById("segundo").style.display = "block";
                document.getElementById("nombre").style.display = "block";
                document.getElementById("edad").style.display = "block";
                document.getElementById("sexo").style.display = "block";
                document.getElementById("ine").style.display = "block";
                document.getElementById("acta").style.display = "block";
                document.getElementById("curp_empresa").style.display = "block";*/

                // Agregar atributos y clases de validación
                for (const [id, clase] of Object.entries(camposEmpresa)) {
                    const campo = document.getElementById(id);
                    if (campo) {
                        campo.setAttribute("required", "");
                        campo.classList.add(clase);
                    }
                }
            }
        }

        const tipo_iden = document.getElementById('tipo_identificacion');
        tipo_iden.addEventListener('change', function() {
            const valorSeleccionado = this.value;
            // Realiza la validación o acciones necesarias
            if (valorSeleccionado === 'Otro') {
                document.getElementById('espesificar_tipo_identificacion').style.display = "block";
            } else {
                document.getElementById('espesificar_tipo_identificacion').style.display = "none";
            }
        });

        const motivo = document.getElementById('motivo');
        motivo.addEventListener('change', function() {
            const valorSeleccionado = this.value;
            // Realiza la validación o acciones necesarias
            if (valorSeleccionado === 'Pago de prestaciones') {
                document.getElementById('motivo_pago').style.display = "block";
            } else {
                document.getElementById('motivo_pago').style.display = "none";
            }
        });        
        
        const otras = document.getElementById('otras');
        otras.addEventListener('click', function() {
            const valorSeleccionado = this.value;
                document.getElementById('div_otras').style.display = "block";
        });
        
        //Fechas inicio y fin
        document.addEventListener("DOMContentLoaded", function () {
            const inicio = document.querySelector('input[name="fecha_inicio"]');
            const termino = document.querySelector('input[name="fecha_termino"]');

            // Función para obtener hoy en formato 'YYYY-MM-DD'
            function obtenerFechaHoyFormato() {
                const hoy = new Date();
                const año = hoy.getFullYear();
                const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                const dia = String(hoy.getDate()).padStart(2, '0');
                return `${año}-${mes}-${dia}`;
            }

            function esFechaValida(fechaStr) {
                return /^\d{4}-\d{2}-\d{2}$/.test(fechaStr) && !isNaN(new Date(fechaStr).getTime());
            }
            function validarFechas() {
                const fechaHoyStr = obtenerFechaHoyFormato();
                const fechaHoy = new Date(fechaHoyStr);
                const fechaInicioStr = inicio.value;
                const fechaTerminoStr = termino.value;

                if (!esFechaValida(fechaInicioStr) && fechaInicioStr !== "") return;
                if (!esFechaValida(fechaTerminoStr) && fechaTerminoStr !== "") return;

                const fechaInicio = new Date(fechaInicioStr);
                const fechaTermino = new Date(fechaTerminoStr);
                // Validar que fecha inicio no sea la fecha de hoy
                if (inicio.value === fechaHoyStr) {
                    alert("La fecha de inicio no puede ser la fecha actual.");
                    inicio.value = "";
                    return;
                }

                // Validar que fecha inicio no sea mayor a hoy
                if (inicio.value && new Date(inicio.value) > new Date(fechaHoyStr)) {
                    alert("La fecha de inicio no puede ser mayor a la fecha actual.");
                    inicio.value = "";
                    return;
                }

                if (termino.value && new Date(termino.value) > new Date(fechaHoyStr)) {
                    alert("La fecha de término no puede ser mayor a la fecha actual.");
                    termino.value = "";
                    return;
                }

                // Validar que fecha inicio no sea mayor que fecha término
                if (inicio.value && termino.value && new Date(inicio.value) > new Date(termino.value)) {
                    alert("La fecha de inicio no puede ser mayor que la fecha de término.");
                    termino.value = "";
                    return;
                }
            }
            inicio.addEventListener("blur", validarFechas);
            termino.addEventListener("blur", validarFechas);

        });
    </script>
    <script>
        // Esperamos a que el DOM esté listo para evitar el error "Cannot read properties of null"
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="cuantificacion"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoCurp"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoidentificacion"]');
            if (inputDocumento) {
                inputDocumento.addEventListener('change', function(e) {
                    // Accedemos al archivo cargado
                    const archivo = e.target.files[0];

                    if (archivo) {
                        // Aquí puedes ejecutar tu validación de 10MB
                        const limite = 10 * 1024 * 1024;
                        if (archivo.size > limite) {
                            alert("El archivo no puede pasar de 10 Megas");
                            this.value = ""; // Limpiar el input
                        }
                    }
                });
            }
        });
    </script>
    
    <div id="nuevo_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>
