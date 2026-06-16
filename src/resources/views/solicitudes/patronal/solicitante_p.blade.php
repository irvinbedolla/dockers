<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title></title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 5.3.3 -->
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
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
           /* background-color: #6A0F49;/<p style="color: #CEA845 */
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
    </style>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="">
            <img src="public/assets/images/Logos 2.png" class="img" style="" width="250" height="90"></a>&nbsp;&nbsp;
        </div> 
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent" >
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('publico') }}" style="color: black;">INICIO<span class="sr-only"></span></a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <br><br><br><br>
    </div>
</head>
<body>
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
                                        <h3 class="text-center" style="color:black">Datos del Solicitante Patronal</h3>
                                    </div>    
                                    <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                    <form id="form-solicitante" novalidate method="POST" action="{{route('solicitantePatronal')}}" enctype='multipart/form-data'>
                                        @csrf
                                        <input type="hidden" name="id" value="{{$id}}"><br>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <label>En caso de no contar con un Folio Interno, puede registrarse en la siguiente liga 
                                                    <a href="{{ route('poder-crear'); }}" target="_black" class="btn btn-primary">Registrar</a></label><br>
                                                </div>
                                            </div>
                                                
                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="">Folio Interno de Registro <span style="color:red;">(*)</span></label>
                                                    <input type="number"  name="folio" id="folio_input" class="form-control" required> 
                                                    <div class="invalid-feedback">
                                                        El folio es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="abogado_info" class="mt-2"></div>
                                            <!--<div id="div1" class="col-xs-12 col-sm-12 col-md-2">
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
                                            </div>-->
                                            <!--<input type="hidden" name="tipo" value="Fisica">-->
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <!--<label for="name">Tipo de Persona (*)</label>-->
                                                <select name="tipo" class="form-control" hidden>
                                                    <option value="">SELECCIONE</option>
                                                    <option value="Fisica">FÍSICA</option>
                                                    <option value="Moral">MORAL</option>
                                                </select>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <!--<label for="name">Nombre(s) y Apellidos del Solicitante <span style="color:red;">(*)</span></label>-->
                                                    <input type="text" name="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()" hidden> 
                                                    <div class="invalid-feedback">
                                                        El campo nombre es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <!--<label for="name">CURP/No. de Migración <span style="color:red;">(*)</span></label>-->
                                                    <input type="text" name="curp" id="curp_input" oninput="validarInput(this)"class="form-control" hidden> 
                                                    <pre id="resultado"></pre>
                                                    <div class="invalid-feedback">
                                                        El campo curp es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <!--<div id="div1" class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">-->
                                                    <!--<label for="name">Fecha de Nacimiento <span style="color:red;">(*)</span></label>-->
                                                    <!--<input type="date" id="fecha_nacimiento" name="fecha_nacimiento" onchange="validarfechaNacimiento(this)" class="form-control" style="display:none;"> 
                                                    <div class="invalid-feedback">
                                                        El campo fecha de nacimiento es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="div1" class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">-->
                                                   <!-- <label for="name">Edad<span style="color:red;">(*)</span></label>-->
                                                    <!--<input type="number" min="0" name="edad" class="form-control" id="años_edad" style="display:none;"> 
                                                    <div class="invalid-feedback">
                                                        El campo edad es obligatoria.
                                                    </div>
                                                </div>
                                            </div>-->
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <!--<label for="name">RFC del Solicitante (Campo opcional)</label>-->
                                                    <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()" hidden> 
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <!--<label for="name">Sexo <span style="color:red;">(*)</span></label>-->
                                                    <select name="genero" class="form-control" style="display:none;">
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
                                            <!--<div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">-->
                                                    <!--<label for="name">Nacionalidad <span style="color:red;">(*)</span></label>-->
                                                   <!-- <select name="nacionalidad" class="form-control" style="display:none;">
                                                        <option value="">SELECCIONE</option>
                                                        <option value="Mexicana">MEXICANA</option>
                                                        <option value="Otra">OTRA</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo nacionalidad es obligatoria.
                                                    </div>
                                                </div>
                                            </div>-->
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <!--<label for="name">Entidad Federativa de Nacimiento <span style="color:red;">(*)</span></label>-->
                                                    <select id="estado_nacimiento" name="estado_nacimiento" class="form-control" style="display:none;">
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
                                                <!--<spam for="btncheck1">¿Requiere traductor?</spam>-->
                                                <input type="checkbox" class="btn-check" id="check_lenguaje" name="traductor" autocomplete="off" hidden>
                                            </div>
                                            <div class="col-xs-6 col-sm-12 col-md-6" id="lenguaje_señas">
                                                <div class="form-group">
                                                   <!-- <label for="name">¿Qué tipo de lenguaje require?</label>-->
                                                    <input type="text" name="lenguaje" class="form-control" id="lenguajeRequerido" oninput="this.value = this.value.toUpperCase()" hidden>
                                                    <div class="invalid-feedback">
                                                        Debe especificar el idioma o lengua requerida.
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="col-xs-6 col-sm-12 col-md-3"><br>
                                                <!--<spam for="btncheck1">¿Tiene alguna discapacidad?</spam>-->
                                                <input type="checkbox" class="btn-check" id="check_discapacidad" name="discapacidad" autocomplete="off" hidden>
                                            </div>   
                                            <div class="col-xs-6 col-sm-12 col-md-6" id="discapacidad">
                                                <div class="form-group">
                                                    <!--<label for="name">¿Cuál es su discapacidad?</label>-->
                                                    <input type="text" name="tipo_discapacidad" class="form-control" id="discapacidadRequerida" oninput="this.value = this.value.toUpperCase()" hidden>
                                                    <div class="invalid-feedback">
                                                        Debe especificar la discapacidad.
                                                    </div>
                                                </div>
                                            </div> 
                                            <!--<div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:40px;">
                                                <h3 class="text-center" style="color:black">Contacto</h3>
                                            </div>  -->
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <!--<label for="name">Teléfono Celular <span style="color:red;">(*)</span></label>-->
                                                    <input type="text" name="telefono1" class="form-control numeroTelefonico" style="display:none;">
                                                    <div class="invalid-feedback">
                                                        El campo teléfono es obligatorio.
                                                    </div>
                                                </div>   
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <!--<label for="name">Teléfono Fijo (Campo opcional)</label>-->
                                                    <input type="text" name="telefono2" class="form-control numeroTelefonico" hidden> 
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <!--<label for="name">Correo Electrónico <span style="color:red;">(*)</span></label>-->
                                                    <input type="mail" name="correo" class="form-control correoElectronico" style="display:none;"> 
                                                    <div class="invalid-feedback">
                                                        El campo correo electrónico es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <!--<div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:40px;">
                                                <h3 class="text-center" style="color:black">Domicilio</h3>
                                            </div>-->
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <!--<label for="password">Estado <span style="color:red;">(*)</span></label>-->
                                                    <select id="estado_solicitante" class="form-control" name="estado_solicitante" style="display:none;">
                                                        <option value="">Seleccione</option>
                                                       {{-- @foreach($estados as $est)
                                                            <option value="{{$est['id']}}">{{$est['nombre']}}</option>
                                                        @endforeach--}}
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo entidad federativa es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                   <!-- <label for="name">Municipio o Alcaldía <span style="color:red;">(*)</span></label>-->
                                                    <select id="municipio_solicitante" class="form-control" name="municipio_solicitante" style="display:none;">
                                                        <option value="">Seleccione</option>
                                                       {{-- @foreach($municipios as $mun)
                                                            <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                                        @endforeach--}}
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo municipio o alcaldía es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                   <!-- <label for="name">Tipo de Vialidad <span style="color:red;">(*)</span></label>--><br>
                                                    <select name="vialidad" class="form-control" style="display:none;">
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
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo vialidad es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <!--<label for="name">Nombre de la Vialidad <span style="color:red;">(*)</span></label>-->
                                                    <input type="text" name="vialidad_calle" class="form-control" oninput="this.value = this.value.toUpperCase()" style="display:none;"> 
                                                    <div class="invalid-feedback">
                                                        El campo vialidad o calle es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <!--<label for="name">Colonia <span style="color:red;">(*)</span></label>-->
                                                    <input type="text" name="colonia_solicitante" class="form-control" oninput="this.value = this.value.toUpperCase()" style="display:none;"> 
                                                    <div class="invalid-feedback">
                                                        El campo colonia es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <!--<label for="name">Número Exterior <span style="color:red;">(*)</span></label>--><br>
                                                    <input type="text" name="numExt" class="form-control" oninput="this.value = this.value.toUpperCase()" style="display:none;"> 
                                                    <div class="invalid-feedback">
                                                        El campo número exterior es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <!--<label for="name">Número Interior (Campo opcional)</label>-->
                                                    <input type="text" name="numInt" class="form-control" oninput="this.value = this.value.toUpperCase()" hidden> 
                                                </div>
                                            </div>
                                            <div id="div1"  class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <!--<label for="name">Código Postal <span style="color:red;">(*)</span></label>-->
                                                    <input type="text" name="cp" class="form-control soloNumeros" minlength="5" maxlength="5" style="display:none;"> 
                                                    <div class="invalid-feedback">
                                                        El campo código postal es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                   <!-- <label for="name">Entre calle (Opcional)</span></label>-->
                                                    <input type="text" name="calle1" class="form-control" oninput="this.value = this.value.toUpperCase()" hidden> 
                                                    <div class="invalid-feedback">
                                                        El campo entre calle es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <!--<label for="name">y calle (Opcional)</label>-->
                                                    <input type="text" name="calle2" class="form-control" oninput="this.value = this.value.toUpperCase()" hidden> 
                                                    <div class="invalid-feedback">
                                                        El campo calle es obligatoria.
                                                    </div>                                    
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <!--<label for="name">Referencias (Opcional)</label>-->
                                                    <textarea class="form-control" placeholder="Ingresa alguna referencia de como llegar" name="referencias" hidden></textarea>
                                                    <div class="invalid-feedback">
                                                        El campo referencia es obligatorio.
                                                    </div>
                                                </div>
                                            </div>



                                            <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:40px;">
                                                <h3 class="text-center" style="color:black">Datos laborales del trabajador</h3>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Nacionalidad <span style="color:red;">(*)</span></label>
                                                    <select name="nacionalidad" class="form-control" required>
                                                        <option value="">SELECCIONE</option>
                                                        <option value="MEXICANA">MEXICANA</option>
                                                        <option value="OTRA">OTRA</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo nacionalidad es obligatorio.
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
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Número de Seguro Social (Opcional)</label>
                                                    <input type="text" name="seguro" minlength="11" maxlength="12" class="form-control soloNumeros"> 
                                                    <div class="invalid-feedback">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Puesto <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="puesto" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo puesto es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Frecuencia de pago</label>
                                                    <select name="periodo_pago" class="form-control">
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
                                                    <label for="name">Salario diario <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="pago" class="form-control soloMontos" required> 
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
                                            <div class="col-xs-6 col-sm-12 col-md-3"><br>
                                                <spam for="btncheck1">¿Requiere traductor?</spam>
                                                <input type="checkbox" class="btn-check" id="check_len" name="traductor" autocomplete="off">
                                            </div>
                                            <div class="col-xs-6 col-sm-12 col-md-6" id="lenguaje_señ" style="display:none;">
                                                <div class="form-group">
                                                    <label for="name">¿Qué tipo de lenguaje require?</label>
                                                    <input type="text" name="lenguaje" class="form-control" id="lenguajeRequerido" oninput="this.value = this.value.toUpperCase()">
                                                    <div class="invalid-feedback">
                                                        Debe especificar el idioma o lengua requerida.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <spam for="btncheck1">¿Tiene discapacidad?</spam>
                                                <input type="checkbox" class="btn-check" id="check_disc" name="discapacidad" value="Si" autocomplete="off">
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4" id="disc" style="display:none;">
                                                <div class="form-group">
                                                    <label for="name">¿Cuál es su discapacidad?</label>
                                                    <input type="text" name="tipo_discapacidad" class="form-control" id="discapacidadRequerida" oninput="this.value = this.value.toUpperCase()">
                                                    <div class="invalid-feedback">
                                                        Debe especificar la discapacidad.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-1">
                                                <div class="form-group">
                                                    <label for="btncheck1">¿Laboras actualmente?</label><br>
                                                    <input name="labora" type="checkbox" class="btn-check" id="check_fecha" autocomplete="off"/>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Horario laboral <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="jornada" class="form-control" placeholder="Ejemplo: De lunes a viernes de 9AM a 5PM y Sábados de 9 AM a 2 PM" required>
                                                    <div class="invalid-feedback">
                                                        El campo jornada laboral es obligatoria.
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
                                            <!--<div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:40px;">
                                                <h3 class="text-center" style="color:black">Documentos</h3>
                                            </div>-->
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <!--<label>CURP/No. de Migración <span style="color:red;">(*)</span></label>-->
                                                    <input type="file" name="documentoCurp" class="form-control" accept=".pdf" required hidden>
                                                    <div class="invalid-feedback">
                                                        El campo curp es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                           <!-- <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">En caso de ser mayor de edad subir su identificación y en caso de ser menor su identificación es su Acta de Nacimiento</h4>
                                                </div>
                                            </div>-->
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <!--<label for="name">Tipo de identificación <span style="color:red;">(*)</span></label>-->
                                                    <select name="identificacion" class="form-control" style="display:none;">
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
                                                    <!--<label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>-->
                                                    <input type="text" name="num_identificacion" class="form-control" oninput="this.value = this.value.toUpperCase()" style="display:none;"> 
                                                    <div class="invalid-feedback">
                                                        El campo núm. de identificación es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <!--<label>Subir Identificación oficial <span style="color:red;">(*)</span></label>-->
                                                    <input type="file" id="documentoIdentificacion" name="documentoIdentificacion" class="form-control" accept=".pdf" style="display:none;">
                                                    <div class="invalid-feedback">
                                                        La Identificación es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <!--<div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">En caso de ser menor de edad Acta de nacimiento</h4>
                                                </div>
                                            </div>-->
                                            <!--<div id="documentacionMenor" class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">-->
                                                    <!--<label>Acta de nacimiento</label>-->
                                                    <!--<input type="file" name="documentoActa" class="form-control" accept=".pdf" hidden>
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
                                                    El campo es obligatorio.
                                                </div>
                                            </div>

                                            <div id="tipoPersona_razon" class="row" style="margin-top:20px; width:100%;">
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
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div align="center">
                                                <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>   
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
        </section>
    </div>
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
        document.getElementById("tipoPersona_razon").style.display="none";
        
        function cambiaExcepcion(elemento){
            // Intencionalmente no mostramos los campos adicionales cuando se selecciona 'Si'
            // El flujo requiere solo seleccionar Si o No; los campos extras permanecen ocultos.
            var el = document.getElementById("tipoPersona_razon");
            if(el) el.style.display = "none";
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
    </script>
</body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <div id="crear_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>

    @section('scripts')
        <script src="public/assets/js/poderes/general.js"></script>
    @endsection

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
        $(function(){
            $('#check_len').on('change', validarcheckseñales);
        })

        function validarcheckseñales(){
            const check = document.getElementById("check_len");
            const divLenguaje = document.getElementById("lenguaje_señ");
            const inputLenguaje = document.getElementById("lenguajeRequerido");

            // Si el checkbox está marcado (checked es true)
            if (check.checked) {
                // Muestra el div y haz el input requerido
                divLenguaje.style.display = "block";
                inputLenguaje.required = true;
            } else {
                // Si no está marcado, oculta el div, quita el required y limpia el valor
                divLenguaje.style.display = "none";
                inputLenguaje.required = false;
                inputLenguaje.value = '';
            }
        }

        $(function(){
            $('#check_disc').on('change', validarcheckdiscapacidad);
        })

        function validarcheckdiscapacidad(){
            const check = document.getElementById("check_disc");
            const divDisc = document.getElementById("disc");
            const inputDiscapacidad = document.getElementById("discapacidadRequerida");

            // Si el checkbox está marcado (checked es true)
            if (check.checked) {
                // Muestra el div y haz el input requerido
                divDisc.style.display = "block";
                inputDiscapacidad.required = true;
            } else {
                // Si no está marcado, oculta el div, quita el required y limpia el valor
                divDisc.style.display = "none";
                inputDiscapacidad.required = false;
                inputDiscapacidad.value = '';
            }
        }

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

            const form = document.querySelector('form#form-solicitante');

            function getFeedback(input) {
                const group = input.closest('.form-group');
                if (!group) return null;
                return group.querySelector('.invalid-feedback');
            }

            function markInvalid(input, message) {
                const fb = getFeedback(input);
                if (fb) {
                    fb.textContent = message || fb.textContent || 'Campo obligatorio';
                    fb.style.display = 'block';
                }
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
            }

            function markValid(input) {
                const fb = getFeedback(input);
                if (fb) {
                    fb.style.display = 'none';
                }
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            }

            function requiredFilled(input) {
                return input && input.value && input.value.trim() !== '';
            }

            function validateEmail(input) {
                const val = input.value.trim();
                if (!val) return false;
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(val);
            }

            function validateCurp(input) {
                const val = input.value.trim();
                return val.length === 18;
            }

            function validateCP(input) {
                const val = input.value.trim();
                return val.length === 5 && /^\d{5}$/.test(val);
            }

            function validateTelefono(input) {
                const val = input.value.trim();
                return val.length === 10 && /^\d{10}$/.test(val);
            }
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                let ok = true;
                let firstInvalid = null;

                function checkAndMark(input, validator, message) {
                    if (!validator(input)) {
                        ok = false;
                        markInvalid(input, message);
                        if (!firstInvalid) firstInvalid = input;
                    } else {
                        markValid(input);
                    }
                }
                /*
                const curp = form.querySelector('input[name="curp"]');
                checkAndMark(curp, validateCurp, 'La CURP debe tener 18 caracteres.');

                const nombre = form.querySelector('input[name="nombre"]');
                checkAndMark(nombre, requiredFilled);

                const fechaN = form.querySelector('input[name="fecha_nacimiento"]');
                checkAndMark(fechaN, requiredFilled);

                const edad = form.querySelector('input[name="edad"]');
                checkAndMark(edad, requiredFilled);

                const genero = form.querySelector('select[name="genero"]');
                checkAndMark(genero, requiredFilled);

                const nacionalidad = form.querySelector('select[name="nacionalidad"]');
                checkAndMark(nacionalidad, requiredFilled);

                const estadoN = form.querySelector('select[name="estado_nacimiento"]');
                checkAndMark(estadoN, requiredFilled);

                const telefono1 = form.querySelector('input[name="telefono1"]');
                checkAndMark(telefono1, validateTelefono, 'El teléfono debe tener exactamente 10 dígitos.');

                const correo = form.querySelector('input[name="correo"]');
                checkAndMark(correo, validateEmail, 'Debe ingresar un correo válido.');

                const estadoDom = form.querySelector('select[name="estado_solicitante"]');
                checkAndMark(estadoDom, requiredFilled);

                const municipio = form.querySelector('select[name="municipio_solicitante"]');
                checkAndMark(municipio, requiredFilled);

                const vialidad = form.querySelector('select[name="vialidad"]');
                checkAndMark(vialidad, requiredFilled);

                const calle = form.querySelector('input[name="vialidad_calle"]');
                checkAndMark(calle, requiredFilled);

                const numExt = form.querySelector('input[name="numExt"]');
                checkAndMark(numExt, requiredFilled);

                const colonia = form.querySelector('input[name="colonia_solicitante"]');
                checkAndMark(colonia, requiredFilled);

                const cp = form.querySelector('input[name="cp"]');
                checkAndMark(cp, validateCP, 'El código postal debe tener 5 dígitos.');
                */
                const puesto = form.querySelector('input[name="puesto"]');
                checkAndMark(puesto, requiredFilled);

                const periodoPago = form.querySelector('select[name="periodo_pago"]');
                checkAndMark(periodoPago, requiredFilled);

                const pago = form.querySelector('input[name="pago"]');
                checkAndMark(pago, requiredFilled);

                const horas = form.querySelector('input[name="horas"]');
                checkAndMark(horas, requiredFilled);

                const fechaIngreso = form.querySelector('input[name="fecha_ingreso"]');
                checkAndMark(fechaIngreso, requiredFilled);

                const jornada = form.querySelector('input[name="jornada"]');
                checkAndMark(jornada, requiredFilled);

                /*const identificacion = form.querySelector('select[name="identificacion"]');
                checkAndMark(identificacion, requiredFilled);

                const numIdent = form.querySelector('input[name="num_identificacion"]');
                checkAndMark(numIdent, requiredFilled);

                const docIdent = form.querySelector('input[name="documentoIdentificacion"]');
                if (!docIdent || !docIdent.files || docIdent.files.length === 0) {
                    ok = false;
                    markInvalid(docIdent, 'La Identificación es obligatoria.');
                    if (!firstInvalid) firstInvalid = docIdent;
                } else {
                    markValid(docIdent);
                }*/

                const descripcion = form.querySelector('textarea[name="descripcionSolicitud"]');
                checkAndMark(descripcion, requiredFilled);

                // const excepcion = form.querySelector('select[name="excepcion"]');
                // checkAndMark(excepcion, requiredFilled);

                // Nota: no validamos ni mostramos campos adicionales de excepción.
                // El requisito actual es únicamente seleccionar Si o No en el campo 'excepcion'.

                const solicitoApoyo = form.querySelector('select[name="solicito_apoyo"]');
                const requiereApoyo = solicitoApoyo && solicitoApoyo.value === 'Si';
                if (requiereApoyo) {
                    const incidencia = form.querySelector('textarea[name="incidencia_directa"]');
                    checkAndMark(incidencia, requiredFilled);
                }

                const checkLanguage = document.getElementById('check_lenguaje');
                console.log(checkLanguage);
                const lenguajeInput = document.getElementById('lenguajeRequerido');
                if (checkLanguage && checkLanguage.checked) {
                    console.log("Validando campo de lenguaje requerido");
                    checkAndMark(lenguajeInput, requiredFilled);
                } else if (lenguajeInput) {
                    markValid(lenguajeInput);
                }

                const checkDisability = document.getElementById('check_discapacidad');
                const discapacidadInput = document.getElementById('discapacidadRequerida');
                if (checkDisability && checkDisability.checked) {
                    checkAndMark(discapacidadInput, requiredFilled);
                } else if (discapacidadInput) {
                    markValid(discapacidadInput);
                }

                if (!ok) {
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({behavior: 'smooth', block: 'center'});
                    }
                } else {
                    $('#crear_poder').show();
                    form.submit();
                }
            });
        });
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
    