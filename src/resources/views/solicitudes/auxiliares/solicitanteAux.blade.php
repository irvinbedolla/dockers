@extends('layouts.app_1')
    <style>
        .loader {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
            opacity: .8;
        }
        #resultado {
            background-color: red;
            color: white;
            font-weight: bold;
        }
        #resultado.ok {
            background-color: green;
        }
        .form-control {
            border: 1px solid #ced4da !important;
            box-shadow: none !important;
        }

        .form-control:focus {
            border-color: #80bdff !important;
            outline: 0 !important;
        }
    </style>
    @section('content')
        <section class="section">
            <div class="section-header">
                <h3 class="page__heading">Solicitud</h3>
            </div>
            <div class="section-body">
                <div class="row"> 
                    <div class="col-lg-12" >
                        <!--<div class="card">-->
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
                                    <div style="background-color:#D2D3D5; width:100%; height:40px;">
                                        <h3 class="text-center" style="color:black">Datos del Solicitante</h3>
                                    </div>    
                                    <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                    <form class="needs-validation" novalidate method="POST" action="{{route('guardaSolicitanteA')}}" enctype='multipart/form-data'>
                                        @csrf
                                        <input type="hidden" name="id" value="{{$id}}">
                                        <div class="row">
                                            <input type="hidden" name="tipo" value="Fisica">
                                            <!--<div class="col-xs-12 col-sm-12 col-md-4">
                                                <label for="name">Tipo de Persona (*)</label>
                                                <select name="tipo" class="form-control" required>
                                                    <option value="">SELECCIONE</option>
                                                    <option value="Fisica">FÍSICA</option>
                                                    <option value="Moral">MORAL</option>
                                                </select>
                                            </div>-->
                                            <div class="col-xs-12 col-sm-12 col-md-8">
                                                <div class="form-group">
                                                    <label for="name">Nombre(s) y Apellidos del Solicitante <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="nombre" maxlength="150" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo nombre es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">CURP/No. de Migración <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="curp" maxlength="18" id="curp_input" oninput="validarInput(this)"class="form-control" required> 
                                                    <pre id="resultado"></pre>
                                                    <div class="invalid-feedback">
                                                        El campo CURP es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="div1" class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Fecha de Nacimiento <span style="color:red;">(*)</span></label>
                                                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" onchange="validarfechaNacimiento(this)" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El campo fecha de nacimiento es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="div1" class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Edad<span style="color:red;">(*)</span></label>
                                                    <input type="number" min="0" name="edad" class="form-control" id="años_edad" required> 
                                                    <div class="invalid-feedback">
                                                        El campo edad es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">RFC del Solicitante (Campo opcional)</label>
                                                    <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()"> 
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                                    <select name="genero" class="form-control" required>
                                                        <option value="">SELECCIONE</option>
                                                        <option value="H">HOMBRE</option>
                                                        <option value="M">MUJER</option>
                                                        <option value="NC">PREFIERO NO CONTESTAR</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo sexo es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Nacionalidad <span style="color:red;">(*)</span></label>
                                                    <select name="nacionalidad" class="form-control" required>
                                                        <option value="">SELECCIONE</option>
                                                        <option value="Mexicana">MEXICANA</option>
                                                        <option value="Otra">OTRA</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo nacionalidad es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Entidad Federativa de Nacimiento <span style="color:red;">(*)</span></label>
                                                    <select id="estado_nacimiento" name="estado_nacimiento" class="form-control" required>
                                                        <option value="">Seleccione</option>
                                                        @foreach($estados as $est)
                                                            <option value="{{$est['id']}}">{{$est['nombre']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo entidad federativa de nacimiento es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-12 col-md-3"><br>
                                                <label for="btncheck1">¿Requiere traductor?</label>
                                                <input type="checkbox" id="check_lenguaje" name="traductor" autocomplete="off">
                                            </div>
                                            <div class="col-xs-6 col-sm-12 col-md-6" id="lenguaje_señas" style="display:none">
                                                <div class="form-group">
                                                    <label for="name">¿Qué tipo de lenguaje require?</label>
                                                    <input type="text" name="lenguaje" class="form-control" id="lenguajeRequerido" oninput="this.value = this.value.toUpperCase()">
                                                    <div class="invalid-feedback">
                                                        Debe especificar el idioma o lengua requerida.
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="col-xs-6 col-sm-12 col-md-3"><br>
                                                <label for="btncheck1">¿Tiene alguna discapacidad?</label>
                                                <input type="checkbox" id="check_discapacidad" name="discapacidad" autocomplete="off">
                                            </div>   
                                            <div class="col-xs-6 col-sm-12 col-md-6" id="discapacidad" style="display:none">
                                                <div class="form-group">
                                                    <label for="name">¿Cuál es su discapacidad?</label>
                                                    <input type="text" name="tipo_discapacidad" class="form-control" id="discapacidadRequerida" oninput="this.value = this.value.toUpperCase()">
                                                    <div class="invalid-feedback">
                                                        Debe especificar la discapacidad.
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:40px;">
                                                <h3 class="text-center" style="color:black">Contacto</h3>
                                            </div>  
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Teléfono Celular <span style="color:red;">(*)</span></label>
                                                    <input type="number" name="telefono1" minlength="10" maxlength="10" class="form-control numeroTelefonico" required>
                                                    <div class="invalid-feedback">
                                                        El campo teléfono es obligatorio. Debe tener 10 dígitos
                                                    </div>
                                                </div>   
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Teléfono Fijo (Campo opcional)</label>
                                                    <input type="number" name="telefono2" minlength="10" maxlength="10" class="form-control numeroTelefonico"> 
                                                </div>
                                                 <div class="invalid-feedback">
                                                        El teléfono fijo debe tener 10 dígitos
                                                </div>  
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Correo Electrónico <span style="color:red;">(*)</span></label>
                                                    <input type="email" name="correo" maxlength="50" class="form-control correoElectronico" required> 
                                                    <div class="invalid-feedback">
                                                        El campo correo electrónico es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:40px;">
                                                <h3 class="text-center" style="color:black">Domicilio</h3>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Tipo de Vialidad <span style="color:red;">(*)</span></label><br>
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
                                                        <option value="PRIVADA">Privada</option>
                                                        <option value="RETORNO">Retorno</option>
                                                        <option value="VIADUCTO">Viaducto</option>
                                                        <option value="PASEO">Paseo</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo vialidad es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-8">
                                                <div class="form-group">
                                                    <label for="name">Nombre de la Vialidad <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="vialidad_calle" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo vialidad o calle es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Número Exterior <span style="color:red;">(*)</span></label><br>
                                                    <input type="text" name="numExt" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo número exterior es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Número Interior (Campo opcional)</label>
                                                    <input type="text" name="numInt" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                </div>
                                            </div>      
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Colonia <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="colonia_solicitante" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo colonia es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Estado <span style="color:red;">(*)</span></label>
                                                    <select id="estado_solicitante" class="form-control" name="estado_solicitante" required>
                                                        <option value="">Seleccione</option>
                                                        @foreach($estados as $est)
                                                            <option value="{{$est['id']}}">{{$est['nombre']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo entidad federativa es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Municipio o Alcaldía <span style="color:red;">(*)</span></label>
                                                    <select id="municipio_solicitante" class="form-control" name="municipio_solicitante" required>
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

                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Código Postal <span style="color:red;">(*)</span></label>
                                                    <input type="number" name="cp" id="cp" class="form-control soloNumeros" maxlength="5" required>
                                                    <!--<input type="number" name="cp" class="form-control soloNumeros" minlength="5" maxlength="5" required>--> 
                                                    <div class="invalid-feedback">
                                                        El campo código postal es obligatorio. Debe tener 5 dígitos
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Entre calle (Opcional)</span></label>
                                                    <input type="text" name="calle1" maxlength="30" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                    <div class="invalid-feedback">
                                                        El campo entre calle es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name">y calle (Opcional)</label>
                                                    <input type="text" name="calle2" maxlength="30" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                    <div class="invalid-feedback">
                                                        El campo calle es obligatoria.
                                                    </div>                                    
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <label for="name">Referencias (Opcional)</label>
                                                    <textarea class="form-control" placeholder="Ingresa alguna referencia de como llegar" name="referencias"></textarea>
                                                    <div class="invalid-feedback">
                                                        El campo referencia es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:40px;">
                                                <h3 class="text-center" style="color:black">Datos laborales</h3>
                                            </div>  
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Número de Seguro Social (Opcional)</label>
                                                    <input type="number" name="seguro" minlength="11" maxlength="12" class="form-control soloNumeros"> 
                                                    <div class="invalid-feedback">
                                                        Debe tener 12 dígitos su número de seguridad social
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Puesto <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="puesto" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo puesto es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Frecuencia de Pago <span style="color:red;">(*)</span></label>
                                                    <select name="periodo_pago" class="form-control" required>
                                                        <option value="">SELECCIONE</option>
                                                        <option value="Diario">DIARIO</option>
                                                        <option value="Semanal">SEMANAL</option>
                                                        <option value="Quincenal">QUINCENAL</option>
                                                        <option value="Mensual">MENSUAL</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo frecuencia de pago es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Salario <span style="color:red;">(*)</span></label>
                                                    <input type="number" step="0.001" name="pago" class="form-control soloMontos" required> 
                                                    <div class="invalid-feedback">
                                                        El campo salario es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Total de horas trabajadas por semana <span style="color:red;">(*)</span></label>
                                                    <input type="number" name="horas" min="0" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El campo cantidad de horas trabajadas es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-1">
                                                <div class="form-group">
                                                    <label for="btncheck1">¿Laboras actualmente?</label><br>
                                                    <input name="labora" type="checkbox" id="check_fecha" autocomplete="off"/>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Horario laboral <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="jornada" maxlength="200" class="form-control" placeholder="Ejemplo: De lunes a viernes de 9AM a 5PM y Sábados de 9 AM a 2 PM" required>
                                                    <div class="invalid-feedback">
                                                        El campo horario laboral es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-2"> 
                                                <div class="form-group">
                                                    <label for="name">Fecha de Ingreso <span style="color:red;">(*)</span></label>
                                                    <input type="date" name="fecha_ingreso" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El campo fecha de ingreso es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-2" id="fecha_fin">
                                                <div class="form-group">
                                                    <label for="name">Fecha de Salida</label>
                                                    <input type="date" name="fecha_salida" class="form-control"> 
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-10">
                                                <div class="form-group">
                                                    <label for="name">Describe brevemente el motivo de tu solicitud <span style="color:red;">(*)</span></label>
                                                    <textarea class="form-control" name="descripcionSolicitud" required></textarea>
                                                    <div class="invalid-feedback">
                                                        El campo descripción del motivo de la solicitud es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:40px;">
                                                <h3 class="text-center" style="color:black">Documentos</h3>
                                            </div>
                                            
                                            <!--<div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label>CURP/No. de Migración <span style="color:red;">(*)</span></label>
                                                    <input type="file" name="documentoCurp" class="form-control" accept=".pdf" required>
                                                    <div class="invalid-feedback">
                                                        El campo curp es obligatorio.
                                                    </div>
                                                </div>
                                            </div>-->
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">En caso de ser mayor de edad subir su identificación y en caso de ser menor su identificación es su Acta de Nacimiento</h4>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Tipo de identificación <span style="color:red;">(*)</span></label>
                                                    <select name="identificacion" class="form-control" required>
                                                        <option value="">SELECCIONE</option>
                                                        <option value="Credencial de elector">CREDENCIAL DE ELECTOR</option>
                                                        <option value="Pasaporte">PASAPORTE</option>
                                                        <option value="Cédula profesional">CÉDULA PROFESIONAL</option>
                                                        <option value="Licencia de conducir">LICENCIA DE CONDUCIR</option>
                                                        <option value="Credencial de inapam">CREDENCIAL DE INAPAM</option>
                                                        <option value="Cartilla militar">CARTILLA MILITAR</option>
                                                        <option value="Documento migratorio">DOCUMENTO MIGRATORIO</option>
                                                        <option value="Constancia de identidad">CONSTANCIA DE IDENTIDAD</option>
                                                        <option value="Otro">OTROS</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El tipo de identificaión es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                    <input type="text" name="num_identificacion" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo núm. de identificación es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label>Subir Identificación oficial <span style="color:red;">(*)</span></label>
                                                    <input type="file" id="documentoIdentificacion" name="documentoIdentificacion" class="form-control" accept=".pdf" required>
                                                    <div class="invalid-feedback">
                                                        El documento con la identificación es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <!--<div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">En caso de ser menor de edad Acta de nacimiento</h4>
                                                </div>
                                            </div>
                                            <div id="documentacionMenor" class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label>Acta de nacimiento</label>
                                                    <input type="file" name="documentoActa" class="form-control" accept=".pdf">
                                                    <div class="invalid-feedback">
                                                        La Identificación es obligatoria.
                                                    </div>
                                                </div>
                                            </div>-->
                                            <div class="col-xs-12 col-sm-12 col-md-4" style="display:none;">
                                                <label for="excepcion">Posible caso de excepción <span style="color:red;">(*)</span>
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                                        ?
                                                    </button>
                                                </label>
                                                
                                                <input type="hidden" name="excepcion" value="No">
                                                <!--
                                                <select name="excepcion" class="form-control" onchange="cambiaExcepcion(this)" required>
                                                    <option value="">Seleccione</option>
                                                    <option value="Si">Si</option>
                                                    <option value="No">No</option>
                                                </select>
                                                -->
                                                <div class="invalid-feedback">
                                                    El campo posible caso de excepción es obligatorio.
                                                </div>
                                            </div>

                                            <!--<div id="tipoPersona_razon" class="row" style="margin-top:20px; width:100%;">
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="frecuencia_hechos">Frecuencia con la que han sucedido los hechos <span style="color:red;">(*)</span></label>
                                                        <select name="frecuencia_hechos" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Una vez">Una vez</option>
                                                            <option value="Varias veces">Varias veces</option>
                                                            <option value="De manera continua, hasta la fecha actual">De manera continua, hasta la fecha actual</option>
                                                            <div class="invalid-feedback">
                                                                El campo es obligatorio.
                                                            </div>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="cambios_situacionL">Cambios que se dieron en su situación laboral después de los hechos <span style="color:red;">(*)</span></label>
                                                        <select name="cambios_situacionL" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Sigue igual">Menores de edad</option>
                                                            <option value="Tensión, estrés e incomodidad en el área de trabajo">Adultos mayores</option>
                                                            <option value="Le cambiarón de área">Personas con discapacidad</option>
                                                            <option value="Otro">Población indígena</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="comunico_hechos">¿La persona afectada comunicó los hechos a alguien más de su área de trabajo?<br>Describir a quién o a quiénes <span style="color:red;">(*)</span></label>
                                                        <textarea name="comunico_hechos" class="form-control"></textarea>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group"><br>
                                                        <label for="descripcion_conducta">Descripción de las conductas manifestadas <span style="color:red;">(*)</span></label>
                                                        <textarea name="descripcion_conducta" class="form-control"></textarea>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>                                                 
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="responsable_cargo">¿Quién o quiénes ejercieron los actos de acoso y hostigamiento sexual o laboral, discriminación y violencia laboral?<br>Especificar cargo y 
                                                            nombres <span style="color:red;">(*)</span></label>
                                                        <textarea name="responsable_cargo" class="form-control"></textarea>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group"><br>
                                                        <label for="actos_cometidos">¿Qué actos se cometieron? <span style="color:red;">(*)</span></label>
                                                        <textarea name="actos_cometidos" class="form-control"></textarea>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="momento_hechos">¿Cuándo sucedieron los hechos? <span style="color:red;">(*)</span></label>
                                                        <textarea name="momento_hechos" class="form-control"></textarea>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>                                        
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="lugar_hechos">¿Donde ocurrieron los actos de acoso y hostigamiento sexual o laboral, discriminación y violencia laboral? <span style="color:red;">(*)</span></label>
                                                        <textarea name="lugar_hechos" class="form-control"></textarea>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>                                         
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="constancia_hechos">¿Los actos han ocurrido anteriormente o de manera reiterada? <span style="color:red;">(*)</span></label>
                                                        <textarea name="constancia_hechos" class="form-control"></textarea>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-5">
                                                    <div class="form-group">
                                                        <label for="solicito_apoyo">
                                                            ¿Ha acudido a su respectivo sindicato, o alguna unidad administrativa en búsqueda de apoyo? 
                                                            <span style="color:red;">(*)</span>
                                                        </label>

                                                        <select name="solicito_apoyo" id="solicito_apoyo" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Si">Si</option>
                                                            <option value="No">No</option>
                                                        </select>

                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>                                       
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3" id="resultado_container">
                                                    <div class="form-group">
                                                        <label for="continuacion_solicto_apoyo">
                                                            ¿Qué resultado obtuvo? <span style="color:red;">(*)</span>
                                                        </label>

                                                        <textarea name="continuacion_solicto_apoyo" id="continuacion_solicto_apoyo" class="form-control"></textarea>

                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-8">
                                                    <div class="form-group">
                                                        <label for="incidencia_directa">¿Los hechos ocurridos han incidido en su centro de trabajo de manera directa(sobrecarga de trabajo, humillaciones, tratos indignos, negación de 
                                                            prestaciones, entre otros)? <span style="color:red;">(*)</span></label>
                                                        <textarea name="incidencia_directa" class="form-control"></textarea>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>                                      
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="recibio_atencion">Derivado de la problemática, ¿Ha recibido atención médica o de algún otro tipo? <span style="color:red;">(*)</span></label>
                                                        <textarea name="recibio_atencion" class="form-control"></textarea>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>                                             
                                                </div>
                                            </div>
                                        </div>-->
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div align="center">
                                                <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>   
                                            </div>
                                        </div>     
                                    </form>
                                </div>
                            </div>
                        <!--</div>-->
                    </div>
                </div>
            </section>
            <script>
                // Función genérica para convertir todo el texto a mayúsculas
                function convertirAMayusculas() {
                    const elementos = document.querySelectorAll('input[type="text"], textarea');

                    elementos.forEach(elemento => {
                        elemento.addEventListener('input', function() {
                            this.value = this.value.toUpperCase();
                        });
                        if (elemento.value) {
                            elemento.value = elemento.value.toUpperCase();
                        }
                    });
                }

                // Ejecutar la función cuando el DOM esté completamente cargado
                document.addEventListener('DOMContentLoaded', (event) => {
                    convertirAMayusculas();
                    (function () {
                        'use strict'
                        var forms = document.querySelectorAll('.needs-validation')
                        Array.prototype.slice.call(forms)
                            .forEach(function (form) {
                                form.addEventListener('submit', function (event) {
                                    if (!form.checkValidity()) {
                                        event.preventDefault()
                                        event.stopPropagation()
                                    }
                                    form.classList.add('was-validated')
                                }, false)
                            })
                    })()
                });
                //Validacion de documentos
                document.querySelector('input[name="foto1"]').addEventListener('change', function () {
                    const file = this.files[0];
                    if (file && !file.type.startsWith('image/')) {
                        alert('Solo se permiten imágenes');
                        this.value = '';
                    }
                });
                //Valida required, minlength, maxlength, Muestra .invalid-feedback, Evita el envío si hay errores
                (() => {
                    'use strict';

                    const forms = document.querySelectorAll('.needs-validation');

                    Array.from(forms).forEach(form => {
                        form.addEventListener('submit', event => {

                            // Validación manual adicional
                            validarTipos();

                            if (!form.checkValidity()) {
                                event.preventDefault();
                                event.stopPropagation();
                            }

                            form.classList.add('was-validated');
                        }, false);
                    });
                })();
            </script>
        @endsection
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

            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Posibles Casos</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            La Ley Federal del Trabajo en el articulo 685-Ter establece que no estas obligado a agotar la etapa conciliatoria en estos supuestos<br>
                            - Discriminación<br>
                            - Acoso u hostigamiento sexual<br>
                            - Designación de beneficiarios<br>
                            - Prestaciones de Seguridad Social
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

    <!--<script>
        document.getElementById("tipoPersona_razon").style.display="none";
        
        function cambiaExcepcion(elemento){
            var valor = elemento.value;
            if(valor == "Si"){
                document.getElementById("tipoPersona_razon").style.display="flex";
            }
            else{
                document.getElementById("tipoPersona_razon").style.display="none";
            }
        }

        // Casos de excepción "Oculta el campo que resultado obtuvo al solicitar apoyo, cuando se elige la opción no"
        document.addEventListener('DOMContentLoaded', function () {
            const selectApoyo = document.getElementById('solicito_apoyo');
            const resultadoContainer = document.getElementById('resultado_container');
            const resultadoField = document.getElementById('continuacion_solicto_apoyo');

            resultadoContainer.style.display = 'none';
            selectApoyo.addEventListener('change', function () {
                if (this.value === 'Si') {
                    resultadoContainer.style.display = 'block';
                    resultadoField.setAttribute('required', 'required');
                } else {
                    resultadoContainer.style.display = 'none';
                    resultadoField.value = '';
                    resultadoField.removeAttribute('required');
                }
            });

        });
    </script>-->
    <script src="public/assets/js/poderes/general.js"></script>

    <script src="public/assets/js/jquery.min.js"></script>
    <script src="public/assets/js/popper.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/sweetalert.min.js"></script>
    <script src="public/assets/js/select2.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.js"></script>
    <script src="public/assets/js/moment.js"></script>

    <!-- Template JS File -->
    <script src="public/assets/js/stisla.js"></script>
    <script src="public/assets/js/scripts.js"></script>
    <script src="public/assets/js/profile.js"></script>
    <script src="public/assets/js/custom.js"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
    @yield('page_js')


    @yield('scripts')
    <script src="./public/assets/js/validaciones.js"></script>
    <script> 
        function sedes(){
            document.getElementById("fecha").removeAttribute("disabled");
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
        //Fechas inicio y fin
        document.addEventListener("DOMContentLoaded", function () {
            const inicio = document.querySelector('input[name="fecha_ingreso"]');
            const termino = document.querySelector('input[name="fecha_salida"]');

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
                if (fechaInicioStr === fechaHoyStr) {
                    swal("Error", "La fecha de ingreso no puede ser la fecha actual.", "error");
                    inicio.value = "";
                    return;
                }

                if (fechaInicio > fechaHoy) {
                    swal("Error", "La fecha de ingreso no puede ser mayor a la fecha actual.", "error");
                    inicio.value = "";
                    return;
                }

                if (fechaTerminoStr && fechaTermino > fechaHoy) {
                    swal("Error", "La fecha de término no puede ser mayor a la fecha actual.", "error");
                    termino.value = "";
                    return;
                }

                if (fechaInicioStr && fechaTerminoStr && fechaInicio > fechaTermino) {
                    swal("Error", "La fecha de ingreso no puede ser mayor que la fecha de término.", "error");
                    termino.value = "";
                    return;
                }
            }

            inicio.addEventListener("blur", validarFechas);
            termino.addEventListener("blur", validarFechas);

            //NUEVAS VALIDACIONES
            const form = document.querySelector('form.needs-validation');
                form.addEventListener('submit', function (e) {
                    let valid = true; 

                    const tel1 = form.querySelector('input[name="telefono1"]');
                    if (tel1) {
                        const celular = tel1.value.replace(/\D/g, '');
                        if (celular.length !== 10) {
                            swal("Error", "El teléfono celular debe tener exactamente 10 dígitos.", "error");
                            tel1.focus();
                            tel1.classList.add('is-invalid');
                            valid = false;
                        } else {
                            tel1.classList.remove('is-invalid');
                        }
                    }

                    const tel2 = form.querySelector('input[name="telefono2"]');
                    if (tel2 && tel2.value) {
                        const fijo = tel2.value.replace(/\D/g, '');
                        if (fijo.length !== 10) {
                            swal("Error", "El teléfono fijo debe tener exactamente 10 dígitos.", "error");
                            tel2.focus();
                            tel2.classList.add('is-invalid');
                            valid = false;
                        } else {
                            tel2.classList.remove('is-invalid');
                        }
                    }

                    const cp = form.querySelector('input[name="cp"]');
                    if (cp) {
                        const codigoPostal = cp.value.replace(/\D/g, '');
                        if (codigoPostal.length !== 5) {
                            swal("Error", "El código postal debe tener exactamente 5 dígitos.", "error");
                            cp.focus();
                            cp.classList.add('is-invalid');
                            valid = false;
                        } else {
                            cp.classList.remove('is-invalid');
                        }
                    }

                    const correo = form.querySelector('input[name="correo"]');
                    if (correo) {
                        const correoVal = correo.value.trim();
                        const correoRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                        if (!correoVal || !correoRegex.test(correoVal)) {
                            swal("Error", "Debe ingresar un correo válido.", "error");
                            correo.focus();
                            correo.classList.add('is-invalid');
                            valid = false;
                        } else {
                            correo.classList.remove('is-invalid');
                        }
                    }

                    if (!valid) {
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                        form.classList.add('was-validated');
                    }
                });
            });

            //FIN NUEVAS VALIDACIONES
            
            /*const form = document.querySelector('form.needs-validation');
            form.addEventListener('submit', function(e) {
                let tel1 = form.querySelector('input[name="telefono1"]');
                let tel2 = form.querySelector('input[name="telefono2"]');
                let valid = true;
                // Validar teléfono celular (obligatorio)
                if (tel1 && tel1.value.replace(/\D/g, '').length !== 10) {
                    swal("Error", "El teléfono celular debe tener exactamente 10 dígitos.", "error");
                    tel1.focus();
                    valid = false;
                }
                // Validar teléfono fijo (opcional, solo si tiene valor)
                if (tel2 && tel2.value && tel2.value.replace(/\D/g, '').length !== 10) {
                    swal("Error", "El teléfono fijo debe tener exactamente 10 dígitos.", "error");
                    tel2.focus();
                    valid = false;
                }
                
                /*const checkLanguage = document.getElementById('check_lenguaje');
                if (checkLanguage.checked) {
                    const languageRequired = document.getElementById('lenguajeRequerido');
                    languageRequired.required = true;
                }
                else {
                    const languageRequired = document.getElementById('lenguajeRequerido');
                    languageRequired.required = false;
                }

                const checkDisability = document.getElementById('check_discapacidad');
                if (checkDisability.checked) {
                    const languageRequired = document.getElementById('discapacidadRequerida');
                    languageRequired.required = true;
                }
                else {
                    const languageRequired = document.getElementById('discapacidadRequerida');
                    languageRequired.required = false;
                }*/
                
                /*if (!valid) {
                    e.preventDefault();
                }*/
            //});
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function cargarMunicipiosSolicitante(estadoId) {
                var $municipio = $('#municipio_solicitante');
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

            var $estadoSolicitante = $('#estado_solicitante');
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
        // Esperamos a que el DOM esté listo para evitar el error "Cannot read properties of null"
        document.addEventListener('DOMContentLoaded', function() {
            const inputDocumento = document.querySelector('input[name="documentoIdentificacion"]');
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
        function validarfechaNacimiento(){
            var fechaNacimiento = document.getElementById("fecha_nacimiento").value;
            if(!fechaNacimiento){
                $('#años_edad').val('');
                return;
            }

            var hoy = new Date();
            var nac = new Date(fechaNacimiento + 'T00:00:00');
            var anios = hoy.getFullYear() - nac.getFullYear();
            var m = hoy.getMonth() - nac.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) {
                anios--;
            }
            
            //document.getElementById("documentacionAdulto").style.display = "none";
            //document.getElementById("documentacionMenor").style.display = "none";
        
            //document.getElementById("años_edad").value = edad;
            //Si la fecha de nacimiento es menos a 15 años
            if(anios <= 15) {
            alert("Requieres tener al menos 15 años de edad. Debes presentarte con tu tutor legal.");
            }
            if(anios > 15 && anios < 18){
            alert("Debes presentarte con tu tutor legal.");
            //document.getElementById("documentacionMenor").style.display = "block";
            }
            else{
            //document.getElementById("documentacionAdulto").style.display = "block";
            }
            $('#años_edad').val(anios);
        }
    </script>