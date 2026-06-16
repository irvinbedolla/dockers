<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Ing. ISBM">
        <title>Si Concilio</title>
    <!-- Bootstrap 5.3.3 -->
    <link href="../public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>

    <!-- Ionicons -->
    <link rel="icon" href="../public/assets/images/ccl-r.png" type="image/x-icon">
    <link href="//fonts.googleapis.com/css?family=Lato&display=swap" rel="stylesheet">
    <link href="../public/assets/css/all.css" rel="stylesheet" type="text/css">
    <link href="../public/assets/css/iziToast.min.css" rel="stylesheet">
    <link href="../public/assets/css/sweetalert.css" rel="stylesheet" type="text/css"/>
    <link href="../public/assets/css/select2.min.css" rel="stylesheet" type="text/css"/>
    
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
            background: url('../public/assets/images/pageLoader.gif') 50% 50% no-repeat rgb(249,249,249);
           /* background-color: #6A0F49;/*<p style="color: #CEA845*/
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

        select[name="vialidad"] option {
            text-transform: uppercase;
        }
        select[name="estado_citado"] option {
            text-transform: uppercase;
        }
        select[name="municipio_citado"] option {
            text-transform: uppercase;
        }
        .btn-disabled {
            pointer-events: none;
            opacity: 0.65;
            cursor: not-allowed;
        }
    </style>

   
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="">
        <img src="../public/assets/images/Logos 2.png" class="img" style="" width="250" height="90"></a>&nbsp;&nbsp;
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
<body>
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
                                        <h3 class="text-center" style="color:black">Ingresa los datos del citado</h3>
                                    </div>    
                                    <!--p><span style="color:red;">*</span> Debes capturar al menos un citado</p-->

                                    <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                                    <form class="needs-validation" novalidate method="POST" action="{{ route('guardar.citado.patronal',$id) }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $id }}">
                                        <div class="row" id="div_datos_citado">
                                            <!--div class="col-xs-12 col-sm-12 col-md-2">
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
                                            </div-->

                                            {{-- Campo oculto para enviar siempre el valor "Fisica" --}}
                                            <input type="hidden" name="tipo" value="Fisica">

                                            {{-- Se elimina el select de tipo de persona --}}
                                            {{-- <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Tipo de persona <span style="color:red;">(*)</span></label>
                                                    <select name="tipo" id="tipo" class="form-control" required>
                                                        <option value="">Seleccione</option>
                                                        <option value="Fisica">Física</option>
                                                    </select>
                                                </div>
                                            </div> --}}

                                            <div class="col-xs-12 col-sm-12 col-md-2" id="campo_curp">
                                                <div class="form-group">
                                                    <label for="name">CURP (Opcional)</label>
                                                    <input type="text" name="curp" maxlength="18" id="curp_input" oninput="validarInput(this)" class="form-control"> 
                                                    <pre id="resultado"></pre>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-3" id="tipoPersona_razon" style="display:none;">
                                                <!--<div class="form-group">
                                                    <label for="name">Razón social <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="razon" id="razon" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                    <div class="invalid-feedback">
                                                        La razón social es obligatorio.
                                                    </div>
                                                </div>-->
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">RFC (Opcional)</label>
                                                    <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()">   
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <spam for="btncheck1">¿Requiere Traductor?</spam>
                                                <input type="checkbox" class="btn-check" id="check_lenguaje" name="traductor" autocomplete="off">
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-4" id="lenguaje_señas">
                                                <div class="form-group">
                                                    <label for="name">¿Qué tipo de lenguaje require?</label>
                                                    <input type="text" name="lenguaje" class="form-control" id="lenguajeRequerido" oninput="this.value = this.value.toUpperCase()">
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12" id="tipoPersona_nombre" style="display:none;">
                                                <div class="row">
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <div class="form-group">
                                                            <label for="name">Nombre(s) <span style="color:red;">(*)</span></label>
                                                            <input type="text" name="nombre" id="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                            <div class="invalid-feedback">
                                                                El nombre es obligatorio.
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <div class="form-group">
                                                            <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                            <input type="text" name="primer_apellido" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                            <div class="invalid-feedback">
                                                                El primer apellido es obligatorio.
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <div class="form-group">
                                                            <label for="name">Segundo apellido</label>
                                                            <input type="text" name="segundo_apellido" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                            <!--div class="invalid-feedback">
                                                                El segundo apellido es obligatorio.
                                                            </!--div-->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <div class="row"> 
                                            <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; width:100%; height:30px;">
                                                <div class="form-group">
                                                    <h4 class="text-center">Dirección de la fuente de empleo</h4>
                                                </div>
                                            </div>    

                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">¿Quién entregará los citatorios? <span style="color:red;">(*)</span></label>
                                                    <select name="notificacion" class="form-control" required>
                                                        <option value="">SELECCIONE</option>
                                                        <!--<option value="Trabajador">Yo</option>-->
                                                        <option value="Centro">Centro de conciliación Laboral</option>
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo ¿quién entregará los citatorios? es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Tipo de vialidad <span style="color:red;">(*)</span></label>
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
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El campo vialidad es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Nombre de la vialidad <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="calle" maxlength="100" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo calle es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-1">
                                                <div class="form-group">
                                                    <label for="text">Núm. ext. <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="exterior" min="0" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El núm. exterior es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-1">
                                                <div class="form-group">
                                                    <label for="name">Núm. int.</label>
                                                    <input type="text" name="interior" min="0" maxlength="50" class="form-control"  oninput="this.value = this.value.toUpperCase()"> 
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Colonia <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="colonia" maxlength="100" class="form-control" oninput="this.value = this.value.toUpperCase()" required> 
                                                    <div class="invalid-feedback">
                                                        El campo colonia es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-1">
                                                <div class="form-group">
                                                    <label for="name">Código postal <span style="color:red;">(*)</span></label>
                                                    <input type="text" name="cp" class="form-control soloNumeros" minlength="5" maxlength="5" required> 
                                                    <div class="invalid-feedback">
                                                        El campo Código Postal es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Entre calle (Opcional)</label>
                                                    <input type="text" name="calle1" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                    <div class="invalid-feedback">
                                                        El campo entre calle es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">y calle (Opcional)</label>
                                                    <input type="text" name="calle2" maxlength="50" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                    <div class="invalid-feedback">
                                                        El campo y calle es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <div class="col-xs-12 col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Estado <span style="color:red;">(*)</span></label>
                                                    <select id="estado_citado" class="form-control" name="estado_citado" required>
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

                                            <div class="col-xs-12 col-sm-12 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Municipio o Alcaldía <span style="color:red;">(*)</span></label>
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
                                                <label for="floatingTextarea">Referencias del domicilio <span style="color:red;">(*)</span></label>
                                                    <textarea class="form-control" placeholder="Ingresa alguna referencia de como llegar" name="referencia" style="height: 100px;" oninput="this.value = this.value.toUpperCase()" required></textarea>
                                                    <div class="invalid-feedback">
                                                        El campo referencias es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <label for="name">Ubica tu domicilio laboral y adjunta una captura.</label>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-2"><br>
                                                <div class="form-group">
                                                    <a class="btn btn-primary" 
                                                        style="background-color:blue; border-color:blue; display: flex; align-items: center; justify-content: center; gap: 8px;" 
                                                        href="https://www.google.com.mx/maps/@19.6837376,-101.1712,14z?entry=ttu&g_ep=EgoyMDI1MDgzMC4wIKXMDSoASAFQAw%3D%3D" 
                                                        target="_blank">
                                                            <img src="https://www.gstatic.com/images/branding/product/1x/maps_64dp.png" alt="Google Maps" style="width:20px; height:20px;">
                                                            Google Maps
                                                    </a>
                                                </div>
                                            </div>                                     
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Referencia 1 <span style="color:red;">(*)</span></label>
                                                    <input type="file" class="form-control" name="foto1" accept="image/*" required>
                                                    <div class="invalid-feedback">
                                                        El campo Referencia 1 es obligatorio.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Referencia 2 (Opcional)</label>
                                                    <input type="file" class="form-control" name="foto2" accept="image/*">
                                                </div>
                                            </div>
                                        </div>

                                        <!--<div class="col-xs-12 col-sm-12 col-md-6">
                                            <spam for="btncheck1">Datos adicionales del Patrón/Empresa</spam>
                                            <input type="checkbox" class="btn-check" id="check_datos" autocomplete="off">
                                        </div>
                                            
                                        <div class="row" id="div_datos_citado" style="display:none">
                                            <div class="col-xs-12 col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Tipo de persona</label>
                                                    <select name="tipo" class="form-control">
                                                        <option value="">Seleccione</option>
                                                        <option value="Fisica">Física</option>
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
                                                        El primer apellido es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Segundo apellido *</label>
                                                    <input type="text" name="segundo_apellido" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                    <div class="invalid-feedback">
                                                        El segundo apellido es obligatorio.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name">RFC</label>
                                                    <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()">   
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <spam for="btncheck1">¿Requiere Traductor?</spam>
                                                <input type="checkbox" class="btn-check" id="check_lenguaje" name="traductor" autocomplete="off">
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-6" id="lenguaje_señas">
                                                <div class="form-group">
                                                    <label for="name">¿Qué tipo de lenguaje require?</label>
                                                    <input type="text" name="lenguaje" class="form-control">
                                                </div>
                                            </div>
                                        </div>-->

                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div style="display:flex; justify-content:flex-end; gap:12px; align-items:center; width:100%;">
                                                    <!--div>
                                                        @if($citados == 0)
                                                            <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Agregar citado</button>
                                                        @endif
                                                        @if($citados > 0)
                                                            <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar citado</button>
                                                        @endif
                                                    </div-->
                                                    <div style="display:flex; flex-direction:column; align-items:flex-end;">
                                                        <button type="submit" id="btn-conclude" class="btn btn-primary" style=" background-color:#CEA845;border-color:#CEA845;">Concluir solicitud</button>
                                                        <!--div id="conclude-warning" class="text-danger" style="display:none; margin-top:6px;">Guarde el citado antes de concluir</div-->
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
    </div>
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
    </script>
    <script>
        // Deshabilitar el botón "Concluir solicitud" hasta que todos los campos obligatorios estén llenos
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form.needs-validation');
            const conclude = document.getElementById('btn-conclude');
            //const concludeWarning = document.getElementById('conclude-warning');
            if (!form || !conclude) return;

            function updateConcludeState() {
                try {
                    // Obtener todos los campos obligatorios (required)
                    const requiredFields = form.querySelectorAll('[required]');
                    let allFilled = true;

                    requiredFields.forEach(function (field) {
                        if (!field) return;
                        
                        const fieldType = (field.type || '').toLowerCase();
                        const tagName = field.tagName.toLowerCase();

                        if (tagName === 'select') {
                            if (!field.value || field.value === '') {
                                allFilled = false;
                            }
                        } else if (fieldType === 'checkbox' || fieldType === 'radio') {
                            if (!field.checked) {
                                allFilled = false;
                            }
                        } else if (fieldType === 'file') {
                            if (!field.files || field.files.length === 0) {
                                allFilled = false;
                            }
                        } else if (tagName === 'textarea') {
                            if (!field.value || field.value.trim() === '') {
                                allFilled = false;
                            }
                        } else {
                            if (!field.value || field.value.trim() === '') {
                                allFilled = false;
                            }
                        }
                    });

                    // Si NO todos los campos están llenos, deshabilitar el botón
                    if (!allFilled) {
                        conclude.classList.add('btn-disabled');
                        conclude.setAttribute('aria-disabled', 'true');
                        //conclude.style.pointerEvents = 'none';
                        //conclude.style.opacity = '0.5';
                    } else {
                        conclude.classList.remove('btn-disabled');
                        conclude.removeAttribute('aria-disabled');
                        //conclude.style.pointerEvents = 'auto';
                        //conclude.style.opacity = '1';
                    }
                } catch (err) { console.warn('updateConcludeState', err); }
            }

            updateConcludeState();

            form.addEventListener('input', updateConcludeState);
            form.addEventListener('change', updateConcludeState);

            conclude.addEventListener('click', function (e) {
                if (conclude.getAttribute('aria-disabled') === 'true') {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
    <script>
        // Carga dinámica de municipios según el estado seleccionado (citados)
        document.addEventListener('DOMContentLoaded', function () {
            function cargarMunicipiosCitado(estadoId) {
                var $municipio = $('#municipio_citado');
                if (!$municipio.length) return;
                $municipio.html('<option value="">Cargando...</option>');
                if (!estadoId) {
                    $municipio.html('<option value="">Seleccione</option>');
                    return;
                }
                // Intentar la ruta API primero (con base_url), si falla intentar la ruta web
                $.get(base_url + '/api/munCitado/' + estadoId, function (data) {
                    var html = '<option value="">Seleccione</option>';
                    data.sort(function(a, b) {
                        return a.nombre.localeCompare(b.nombre, 'es', { sensitivity: 'base' });
                    });
                    data.forEach(function (m) {
                        html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                    });
                    $municipio.html(html);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    // Intentar ruta sin prefijo /api
                    $.get(base_url + '/munCitado/' + estadoId, function (data) {
                        var html = '<option value="">Seleccione</option>';
                        data.sort(function(a, b) {
                            return a.nombre.localeCompare(b.nombre, 'es', { sensitivity: 'base' });
                        });
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

            var base_url = "{{ url('') }}";

            var $estadoCitado = $('#estado_citado');
            if ($estadoCitado.length) {
                $estadoCitado.on('change', function () {
                    cargarMunicipiosCitado(this.value);
                });
                // Si ya viene seleccionado (edición/old), cargar municipios al inicio
                var inicial = $estadoCitado.val();
                if (inicial) cargarMunicipiosCitado(inicial);
            }
        });
    </script>
</body>
    <div id="crear_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>

@section('scripts')
    <script src="../public/assets/js/poderes/general.js"></script>
@endsection

    <script src="../public/assets/js/jquery.min.js"></script>
    <script src="../public/assets/js/popper.min.js"></script>
    <script src="../public/assets/js/bootstrap.min.js"></script>
    <script src="../public/assets/js/sweetalert.min.js"></script>
    <script src="../public/assets/js/select2.min.js"></script>
    <script src="../public/assets/js/jquery.nicescroll.js"></script>
    <script src="../public/assets/js/moment.js"></script>

    <!-- Template JS File -->
    <script src="../public/assets/js/stisla.js"></script>
    <script src="../public/assets/js/scripts.js"></script>
    <script src="../public/assets/js/profile.js"></script>
    <script src="../public/assets/js/custom.js"></script>

    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.bootstrap4.js"></script>
    @yield('page_js')


    @yield('scripts')
    <script src="../public/assets/js/validaciones.js"></script> 
    <script>
        
       /* $(function(){
            $('#check_datos').on('change', mostrarDatos);
            console.log("llego");
        })

        function mostrarDatos(){
            var check = document.getElementById("div_datos_citado").style.display;
            console.log(check);
            if (check == "none") {
                document.getElementById("div_datos_citado").style.display = "block";
            }
            else{
                document.getElementById("div_datos_citado").style.display = "none";
            }
        }*/

        document.addEventListener('DOMContentLoaded', function () {
            //const selectTipo = document.getElementById('tipo');
            const nombreDiv = document.getElementById('tipoPersona_nombre');
            //const razonDiv = document.getElementById('tipoPersona_razon');
            const curpDiv = document.getElementById('campo_curp');

            //const valor = selectTipo.value;

            if (nombreDiv) nombreDiv.style.display = 'block';
            if (curpDiv) curpDiv.style.display = 'block';
            //razonDiv.style.display = 'none';

            // Reset required state
            const inputNombre = document.querySelector('input[name="nombre"]');
            const inputPrimer = document.querySelector('input[name="primer_apellido"]');
            const inputSegundo = document.querySelector('input[name="segundo_apellido"]');
            const inputRazon = document.querySelector('input[name="razon"]');

            // Persona física: pedir nombre y primer apellido (segundo opcional), mostrar CURP
            if (inputNombre) inputNombre.required = true;
            if (inputPrimer) inputPrimer.required = true;
            if (inputSegundo) inputSegundo.required = false;
            if (inputRazon) inputRazon.required = false;

            /*if (valor === 'Fisica') {
                // Persona física: pedir nombre y primer apellido (segundo opcional), mostrar CURP
                nombreDiv.style.display = 'block';
                curpDiv.style.display = 'block';
                if (inputNombre) inputNombre.required = true;
                if (inputPrimer) inputPrimer.required = true;
                if (inputSegundo) inputSegundo.required = false;
                if (inputRazon) inputRazon.required = false;
            } else if (valor === 'Moral') {
                // Persona moral: pedir razón social, ocultar campos de nombre
                razonDiv.style.display = 'block';
                if (inputRazon) inputRazon.required = true;
                if (inputNombre) inputNombre.required = false;
                if (inputPrimer) inputPrimer.required = false;
                if (inputSegundo) inputSegundo.required = false;
            }*/

            // Se elimina el listener del select ya que no existe más

            /*if (selectTipo) {
                selectTipo.addEventListener('change', actualizarTipoPersona);
                // Ejecutar al cargar por si ya tiene valor
                actualizarTipoPersona();
            }*/

            const form = document.querySelector('form.needs-validation');
            form.addEventListener('submit', function(e) {

                const checkLanguage = document.getElementById('check_lenguaje');
                if (checkLanguage.checked) {
                    const languageRequired = document.getElementById('lenguajeRequerido');
                    languageRequired.required = true;
                }
                else {
                    const languageRequired = document.getElementById('lenguajeRequerido');
                    languageRequired.required = false;
                }
            });
        });
        
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
    </script>
    
    <div id="crear_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>