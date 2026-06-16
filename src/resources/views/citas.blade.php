<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Si concilio</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 5.3.3 -->
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>

    <!-- Ionicons -->
    <link rel="icon" href="public/assets/images/ccl-r.png" type="image/x-icon">
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="public/assets/css/all.css" rel="stylesheet" type="text/css">
    <link href="public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
    
    <!-- Agregados para los Select del Formulario Personas-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

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
    @livewireStyles

    @yield('page_css')
    <!-- Template CSS <img src="public/assets_seer/images/ccl.png" width="180" height="90" style="position: absolute; left: 100px; top: 10px; right:0px;"/>  -->
    <link rel="stylesheet" href="public/assets/css/components.css">
    @yield('page_css')
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="">
        <img src="public/assets/images/Logos 2.png" class="img" width="260" height="90">
    </div> 
</nav>
<body onload="validarcheckfolio()">
    <main>
        <div class="container">
            <br><br><br><br>
        </div>
        <div id="app">  
            <section class="section"> 
                <div class="section-body">
                    <div class="row"> 
                        <div class="col-lg-12" >
                            <div class="card">
                                <div class="card-body">
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
                                            <h3 class="text-center" style="color:black">Genera tu cita para ratificación</h3>
                                        </div>    
                                        <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                        <form class="needs-validation" novalidate method="POST" action="{{route('turnos.publico')}}" enctype="multipart/form-data" onsubmit="return validacionCamposInput()">
                                            @csrf
                                            <br><br>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label>En caso de no contar con un Folio Interno, puede registrarse en la siguiente liga (Para tramites posteriores) 
                                                        <a href="{{ route('poder-crear'); }}" target="_black" class="btn btn-primary">Registrar</a></label><br>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="">Folio Interno de Registro <span style="color:red;">(*)</span></label>
                                                        <input type="number"  name="folio" id="folio_input" class="form-control" required> 
                                                        <div class="invalid-feedback">
                                                            El folio es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="abogado_info" class="mt-2"></div>

                                                <div id="datos_formulario" class="row" style="display:none; width:100%;">
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:30px;">
                                                    <div class="form-group">
                                                        <h4 class="text-center">Datos del Trabajador</h4>
                                                    </div>
                                                </div>
                                                

                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primero_trabajador" class="form-control soloLetras" oninput="this.value = this.value.toUpperCase()" required> 
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="segundo_trabajador" id ="segundo_apellido" class="form-control soloLetras" oninput="this.value = this.value.toUpperCase()" required> 
                                                        
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Nombre(s) <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="trabajador" class="form-control soloLetras" oninput="this.value = this.value.toUpperCase()" required> 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-2">
                                                    <div class="form-group">
                                                        <label for="name">Nacionalidad <span style="color:red;">(*)</span></label>
                                                        <select name="nacionalidad" class="form-control" id="nacionalidad" required>
                                                            <option value="">SELECCIONE</option>
                                                            <option value="MEXICANA">MEXICANA</option>
                                                            <option value="EXTRANJERA">EXTRANJERA</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo nacionalidad es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div  class="col-xs-12 col-sm-12 col-md-1">
                                                    <div class="form-group">
                                                        <label for="name">Edad <span style="color:red;">(*)</span></label>
                                                        <input type="number" name="trabajador_edad" class="form-control soloNumeros" required> 
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
                                                            <option value="H">Hombre</option>
                                                            <option value="M">Mujer</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo sexo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-5">
                                                    <div class="form-group">
                                                        <label for="name">CURP del trabajador <span style="color:red;">(*)</span></span></label>
                                                        <input type="text" name="trabajador_curp"  oninput="validarInput(this, 'resultado_curp_trabajador')" class="form-control" required> 
                                                        <pre id="resultado_curp_trabajador" class="resultado"></pre>
                                                        <div class="invalid-feedback">
                                                            El campo curp es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div  class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Documento de la CURP (Opcional)</span></label>
                                                        <input type="file" id="documentoCurp" name="documentoCurp" class="form-control" accept=".pdf"> 
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
                                                            <option value="Credencial de elector">Credencial de Elector</option>
                                                            <option value="Pasaporte">Pasaporte</option>
                                                            <option value="Cédula profesional">Cédula Profesional</option>
                                                            <option value="Licencia de conducir">Licencia de Conducir</option>
                                                            <option value="Credencial de inapam">Credencial de INAPAM</option>
                                                            <option value="Cartilla militar">Cartilla Militar</option>
                                                            <option value="Documento migratorio">Documento Migratorio</option>
                                                            <option value="Constancia de identidad">Constancia de Identidad</option>
                                                            <option value="Otro">Otros</option>
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
                                                        <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                        <input type="text" name="num_identificacion" maxlength="13" minlength="6" class="form-control" oninput="this.value = this.value.toUpperCase()" placeholder="De 3 a 13 caracteres" required> 
                                                        <div class="invalid-feedback">
                                                            El campo núm. de identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div  class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Subir Identificación Oficial <span style="color:red;">(*)</span></label>
                                                        <input type="file" id="documentoidentificacion" name="documentoidentificacion" class="form-control" accept=".pdf" required> 
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
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
                                                                <option value="{{$est['id']}}">{{$est['nombre']}}</option>
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
                                                                <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
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
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo vialidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre de la Vialidad <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="vialidad_calle" id="vialidad_calle" class="form-control" placeholder="*Nombre vialidad" oninput="this.value = this.value.toUpperCase()" required> 
                                                        <div class="invalid-feedback">
                                                            El campo vialidad o calle es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Colonia <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Colonia" name="colonia" id="colonia" oninput="this.value = this.value.toUpperCase()" required>
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Ext. <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Núm. exterior" name="N_Ext" id="N_Ext" oninput="this.value = this.value.toUpperCase()" required>
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
            
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Int.(Opcional)</label>
                                                        <input type="text" class="form-control" placeholder="Núm. interior" name="N_Int" id="N_Int" oninput="this.value = this.value.toUpperCase()">
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
            
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Código postal <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Código postal" name="cp" id="cp" oninput="this.value = this.value.toUpperCase()" required>
                                                        <div class="invalid-feedback">
                                                            El domicilio es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div  class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">¿Existe procedimiento previo en la junta local de conciliación y arbitraje? <span style="color:red;">(*)</span></label>
                                                        <select name="JLCA" class="form-control"  required>
                                                            <option value="">Seleccione</option>
                                                            <option value="Si">Si</option>
                                                            <option value="No">No</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-2"> 
                                                    <div class="form-group">
                                                        <label for="fecha_inicio">Fecha de inicio de la relación laboral <span style="color:red;">(*)</span></label>
                                                        <input type="date" name="fecha_inicio" class="form-control" required> 
                                                        <div class="invalid-feedback">
                                                            El campo edad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="fecha_termino">Fecha de término de la relación laboral <span style="color:red;">(*)</span></label>
                                                        <input type="date" name="fecha_termino" class="form-control" > 
                                                        <div class="invalid-feedback">
                                                            El campo edad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div  class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Categoría o Puesto que desempeña <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="categoria" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
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
                                                            <option value="Diario">Diario</option>
                                                            <option value="Semanal">Semanal</option>
                                                            <option value="Quincenal">Quincenal</option>
                                                            <option value="Mensual">Mensual</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Este campo frecuencia es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Salario <span style="color:red;">(*)</span></label><br>
                                                        <input type="text" name="salario" placeholder="$" class="form-control soloMontos" class="myInput" required> 
                                                        <div class="invalid-feedback">
                                                            Este campo salario es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Días a la semana trabajados <span style="color:red;">(*)</span></label>
                                                        <input type="number" name="dias" class="form-control soloNumeros" required> 
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
                                                            <option value="Pago de prestaciones">Pago de prestaciones</option>
                                                            <option value="Terminación voluntaria de la relación de trabajo">Terminación voluntaria de la relación de trabajo</option>
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
                                                        <input type="text" name="monto" class="form-control soloMontos" required> 
                                                        <div class="invalid-feedback">
                                                            El campo edad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                            
                                                <div class="col-xs-12 col-sm-6 col-md-2">
                                                    <div class="form-group">
                                                        <label for="name"><br>
                                                        <center><a href="https://cclmichoacan.gob.mx/Calculadora.html" target="_blank">* Calcula el monto aproximado del convenio.</a></center>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Forma de pago <span style="color:red;">(*)</span></label>
                                                        <select name="tipo_pago" class="form-control"  required>
                                                            <option value="">Seleccione el tipo de pago</option>
                                                            <option value="Efectivo">Efectivo</option>
                                                            <option value="Transferencia">Transferencia</option>
                                                            <option value="Cheque">Cheque</option>
                                                            <option value="Cheque Electrónico">Cheque Electrónico</option>
                                                            <option value="Orden de Pago">Orden de Pago</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo edad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Sube tu cuantificación (Opcional)</label>
                                                        <input type="file" id="cuantificacion" name="cuantificacion" class="form-control" accept=".pdf"> 
                                                        <div class="invalid-feedback">
                                                            El campo edad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                             
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Sedes <span style="color:red;">(*)</span></label>
                                                        <select id="sede" name="sede" class="form-control" onchange="modalCalendar();" required>
                                                            <option value="">Seleccione la sede</option>
                                                            <option value="Morelia">Morelia</option>
                                                            <option value="Zitácuaro">Zitácuaro</option>
                                                            <option value="Uruapan">Uruapan</option>
                                                            <option value="Lázaro Cárdenas">Lázaro Cárdenas</option>
                                                            <option value="Zamora">Zamora</option>
                                                            <option value="Sahuayo">Sahuayo</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            La sede es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                

                                                <input type="hidden" name="fecha" id="fechaSeleccionada" required>
                                                <input type="hidden" name="hora" id="horaSeleccionada" required>
                                                    
                                                <!-- Botón para abrir el modal -->
                                                   
                                                <div style="display: flex; align-items: center; justify-content: center;">
                                                    <button type="button" id="botonCalendar" class="btn btn-lg btn-custom-morado" data-bs-toggle="modal" data-bs-target="#calendarModal" disabled>
                                                        Seleccionar Fecha y Horario
                                                    </button>
                                                </div>
                                                    

                                                <div class="modal fade" id="calendarModal" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-xl">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Seleccionar Fecha y Horario</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div id="calendar"></div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="button" class="btn btn-primary" id="confirmarSeleccion">Confirmar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="resumenCita" class="col-xs-12 col-sm-12 col-md-12" style="margin-top: 10px; display: none;">
                                                <div class="alert alert-info">
                                                    <strong>Cita seleccionada:</strong> <span id="fechaResumen"></span> a las <span id="horaResumen"></span>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div align="center">
                                                    <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color: #CEA845">Guardar</button>
                                                    <a href="{{ route('publico'); }}" class="btn btn-primary" style=" background-color:#CEA845; border-color: #CEA845">Regresar</a>    
                                                </div>
                                            </div>    
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal para la captura de la ine-->
                <div class="modal fade" id="helpModal" aria-labelledby="helpModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-height: 80vh;">
                      <div class="modal-content" style="height: 100%;">
                        <div class="modal-header">
                          <h5 class="modal-title" id="helpModalLabel">Ubicación de núm. de identificación</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body text-center">
                          <img src="./public/assets/images/capturaIne.png" alt="Instrucciones" class="img-fluid">
                        </div>
                      </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script>
        //dependiendo del folio ingresado para el abogado indica el nombre del representante y la empresa, o una leyenda en caso de no existir
        document.addEventListener('DOMContentLoaded', function () {
            const folioInput = document.getElementById('folio_input');
            const abogadoInfoDiv = document.getElementById('abogado_info');
            const datosFormulario = document.getElementById('datos_formulario');
            let timeout = null;

            const baseUrl = "{{ url('/validar_folio_abogado') }}";

            folioInput.addEventListener('keyup', function () {
                clearTimeout(timeout);
                const folio = this.value.trim();
                if (folio === '') {
                    abogadoInfoDiv.textContent = '';
                    abogadoInfoDiv.classList.remove('alert', 'alert-success', 'alert-danger');
                    if (datosFormulario) datosFormulario.style.display = 'none';
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
                        abogadoInfoDiv.classList.remove('alert-success', 'alert-danger');

                        const status = data && data.status ? data.status : null;
                        const msg = data && data.message ? data.message : '';

                        if (status === 'elegible') {
                            abogadoInfoDiv.textContent = `Representante: ${data.nombre} — ${msg}`;
                            abogadoInfoDiv.classList.add('alert', 'alert-success');
                            if (datosFormulario) datosFormulario.style.display = '';
                        } else if (status === 'sin_vigencia' || status === 'requiere_validacion') {
                            abogadoInfoDiv.textContent = `Representante: ${data.nombre} — ${msg}`;
                            abogadoInfoDiv.classList.add('alert', 'alert-danger');
                            if (datosFormulario) datosFormulario.style.display = 'none';
                        } else {
                            abogadoInfoDiv.textContent = `Representante: ${data.nombre}`;
                            abogadoInfoDiv.classList.add('alert', 'alert-success');
                            if (datosFormulario) datosFormulario.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        abogadoInfoDiv.classList.remove('alert-success');
                        abogadoInfoDiv.classList.add('alert', 'alert-danger');
                        abogadoInfoDiv.textContent = (error.message === 'Folio no encontrado')
                            ? 'El folio no existe. Por favor, verifica el número.'
                            : 'Ocurrió un error al buscar. Inténtalo de nuevo.';
                        if (datosFormulario) datosFormulario.style.display = 'none';
                        console.error('Error:', error);
                    });
                }, 500);
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<div id="crear_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="public/assets/js/validaciones-ratificacion.js"></script> 
    <script src="public/assets/js/poderes/general.js"></script>
@endsection

    <script src="public/assets/js/jquery.min.js"></script>
    <script src="public/assets/js/popper.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/sweetalert.min.js"></script>
    <script src="public/assets/js/select2.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales-all.min.js"></script>

    <!-- Template JS File -->
    <script src="public/assets/js/stisla.js"></script>
    <script src="public/assets/js/scripts.js"></script>
    <script src="public/assets/js/profile.js"></script>
    <script src="public/assets/js/custom.js"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <!--script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script-->
    @yield('page_js')


    @yield('scripts')
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


        function sedes(){
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
        }

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
        
        //Tipo de persona física o moral
        document.addEventListener('DOMContentLoaded', function () {
            const selectTipo = document.getElementById('tipo_persona');
            const nombreDiv = document.getElementById('tipoPersona_nombre');
            const razonDiv = document.getElementById('tipoPersona_razon');

            function actualizarTipoPersona() {
                const valor = selectTipo.value;

                // Oculta ambos inicialmente
                nombreDiv.style.display = 'none';
                razonDiv.style.display = 'none';


                if (valor === 'Fisica') {
                    nombreDiv.style.display = 'block';
                } else if (valor === 'Moral') {
                    razonDiv.style.display = 'block';
                }
            }

            if (selectTipo) {
                selectTipo.addEventListener('change', actualizarTipoPersona);
                // Ejecutar al cargar por si ya tiene valor
                actualizarTipoPersona();
            }
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridWeek',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                },
                validRange: {
                    start: (() => {
                        const now = new Date();
                        return new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().split('T')[0];
                    })(),
                    end: (() => {
                        const now = new Date();
                        return new Date(now.getFullYear(), now.getMonth() + 3, 0).toISOString().split('T')[0];
                    })()
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    // Obtener sede seleccionada
                    var sede = document.getElementById('sede').value;
                    
                    // Hacer petición AJAX con parámetro sede
                    $.ajax({
                        url: 'api/obtenerEventos',
                        method: 'GET',
                        data: {
                            sede: sede,
                            start: fetchInfo.startStr,
                            end: fetchInfo.endStr
                        },
                        success: function(data) {
                            successCallback(data);
                        },
                        error: function() {
                            failureCallback('Error al cargar eventos');
                        }
                    });
                },

                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    //second: '2-digit',
                    //hour12: false
                },

                eventClick: function(info) {
                    // Solo permitir selección de horarios disponibles
                    let ahora = new Date();
                    let slotDate = new Date(info.event.start);

                    if (info.event.extendedProps.estado === 'disponible' && slotDate > ahora) {
                        // Deseleccionar evento anterior
                        document.querySelectorAll('.fc-event-selected').forEach(el => {
                            el.classList.remove('fc-event-selected');
                        });
                        
                        // Seleccionar este evento
                        info.el.classList.add('fc-event-selected');
                        window.selectedEvent = info.event;
                    } else {
                        alert('Este horario no está disponible. Por favor seleccione otro.');
                    }
                },
                eventDidMount: function(info) {
                    // Añade clases CSS según el tipo de evento
                    if (info.event.extendedProps.estado === 'disponible') {
                        info.el.classList.add('fc-event-disponible');
                    } else if (info.event.extendedProps.estado === 'expirado') {
                        info.el.classList.add('fc-event-expirado');
                    } else if (info.event.extendedProps.estado === 'inhabil') {
                        info.el.classList.add('fc-event-inhabil');
                    } else {
                        info.el.classList.add('fc-event-ocupado');
                    }
                },
            });

            calendar.render();

            $('#calendarModal').on('shown.bs.modal', function () {
                calendar.refetchEvents();
                calendar.updateSize();
            });

            // Confirmar selección
            document.getElementById('confirmarSeleccion').addEventListener('click', function() {
                if (window.selectedEvent) {
                    const fechaHora = new Date(window.selectedEvent.start);
                    const fecha = fechaHora.toISOString().split('T')[0];
                    const hora = fechaHora.toTimeString().substring(0, 8);
                    
                    // Guardar en campos ocultos
                    document.getElementById('fechaSeleccionada').value = fecha;
                    document.getElementById('horaSeleccionada').value = hora;
                    
                    // Mostrar resumen al usuario
                    document.getElementById('fechaResumen').textContent = fecha;
                    document.getElementById('horaResumen').textContent = hora;
                    document.getElementById('resumenCita').style.display = 'block';
                    
                    // Cerrar modal
                    $('#calendarModal').modal('hide');
                } else {
                    alert('Por favor selecciona un horario disponible');
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function cargarMunicipiosSolicitante(estadoId) {
                var $municipio = $('#municipio_rat');
                if (!$municipio.length) return;
                $municipio.html('<option value="">Cargando...</option>');
                if (!estadoId) {
                    $municipio.html('<option value="">Seleccione</option>');
                    return;
                }
                $.get(base_url + '/api/munSolicitante/' + estadoId, function (data) {
                    var html = '<option value="">Seleccione</option>';
                    data.forEach(function (m) {
                        html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                    });
                    $municipio.html(html);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    $.get(base_url + '/munSolicitante/' + estadoId, function (data) {
                        var html = '<option value="">Seleccione</option>';
                        data.forEach(function (m) {
                            html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                        });
                        $municipio.html(html);
                    }).fail(function (jq2, t2, e2) {
                        $municipio.html('<option value="">Error cargando municipios</option>');
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({
                                title: 'Error',
                                message: 'No se pudieron cargar los municipios. HTTP: ' + (jqXHR.status || jq2.status || 'N/A') + ' - ' + (errorThrown || e2 || textStatus),
                                position: 'topRight'
                            });
                        } else {
                            alert('No se pudieron cargar los municipios.');
                        }
                    });
                });
            }

            var $estadoSolicitante = $('select[name="estado_rat"]');
            var base_url = "{{ url('') }}";

            if ($estadoSolicitante.length) {
                $estadoSolicitante.on('change', function () {
                    cargarMunicipiosSolicitante(this.value);
                });
                var inicial = $estadoSolicitante.val();
                if (inicial) cargarMunicipiosSolicitante(inicial);
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectNacionalidad = document.getElementById('nacionalidad');
            const apellido = document.getElementById('segundo_apellido');
            
            function actualizarApellido() {
                if (!selectNacionalidad) return;
                const valor = selectNacionalidad.value;
                const shouldBeRequiredByDefault = apellido.hasAttribute('data-required-default') || apellido.hasAttribute('required');
                    
                    if (valor === 'MEXICANA' || valor === '') {
                        apellido.setAttribute('required', '');

                    } else if (valor === 'EXTRANJERA') {
                        
                        apellido.removeAttribute('required');
                    }
            }
            if (selectNacionalidad) selectNacionalidad.addEventListener('change', actualizarApellido);
            actualizarApellido();
        });
    </script>

    <div id="crear_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>